<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class VendorInvoice extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'uuid', 'tenant_id', 'vendor_id', 'event_id', 'vendor_contract_id', 'vendor_event_assignment_id',
        'invoice_number', 'title', 'issue_date', 'due_date',
        'amount', 'tax_amount', 'discount_amount', 'total_amount',
        'status', 'notes', 'attachment_path', 'created_by',
    ];

    protected $casts = [
        'issue_date'       => 'date',
        'due_date'         => 'date',
        'amount'           => 'decimal:2',
        'tax_amount'       => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'total_amount'     => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (VendorInvoice $invoice) {
            if (empty($invoice->uuid)) {
                $invoice->uuid = Str::uuid();
            }
            if (empty($invoice->created_by)) {
                $invoice->created_by = auth()->id();
            }
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber($invoice->tenant_id);
            }
        });

        static::saved(function (VendorInvoice $invoice) {
            $invoice->syncBudgetItem();
            $invoice->syncAssignmentAmount();
        });

        static::deleted(function (VendorInvoice $invoice) {
            BudgetItem::where('vendor_invoice_id', $invoice->id)->delete();
        });
    }

    /**
     * Keep the linked VendorEventAssignment's amount_agreed in sync
     * so both the Assignment view and Invoice view always agree.
     */
    public function syncAssignmentAmount(): void
    {
        if (!$this->vendor_event_assignment_id) return;

        $assignment = VendorEventAssignment::find($this->vendor_event_assignment_id);
        if (!$assignment) return;

        $assignment->updateQuietly([
            'amount_agreed' => $this->total_amount,
            'amount_paid'   => $this->totalPaid(),
        ]);
    }

    public static function generateInvoiceNumber(int $tenantId): string
    {
        $count = static::withoutGlobalScope('tenant')->where('tenant_id', $tenantId)->count() + 1;
        return 'INV-' . now()->format('Ym') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(VendorContract::class, 'vendor_contract_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(VendorEventAssignment::class, 'vendor_event_assignment_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(VendorInvoicePayment::class)->orderByDesc('paid_on');
    }

    public function budgetItem(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BudgetItem::class);
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function balance(): float
    {
        return round((float) $this->total_amount - $this->totalPaid(), 2);
    }

    public function isPaid(): bool
    {
        return $this->balance() <= 0;
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && !$this->isPaid()
            && !in_array($this->status, ['cancelled']);
    }

    public function recalculateStatus(): void
    {
        if ($this->status === 'cancelled') return;
        if ($this->status === 'draft') return; // draft stays draft until explicitly sent

        $paid = $this->totalPaid();

        if ($paid <= 0) {
            $newStatus = $this->isOverdue() ? 'overdue' : 'sent';
        } elseif ($paid < (float) $this->total_amount) {
            $newStatus = 'partially_paid';
        } else {
            $newStatus = 'paid';
        }

        if ($newStatus !== $this->status) {
            $this->update(['status' => $newStatus]);
        }
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'sent'            => 'Sent',
            'partially_paid'  => 'Partially Paid',
            'paid'            => 'Paid',
            'overdue'         => 'Overdue',
            'cancelled'       => 'Cancelled',
            default           => 'Draft',
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'sent'           => '#3B82F6',
            'partially_paid' => '#F59E0B',
            'paid'           => '#10B981',
            'overdue'        => '#EF4444',
            'cancelled'      => '#78716C',
            default          => '#A8A29E',
        };
    }

    /**
     * Auto-create/update a corresponding budget_item so this invoice
     * reflects automatically in the event's budget breakdown.
     */
    public function syncBudgetItem(): void
    {
        if (!$this->event_id) return; // general invoices (no event) don't sync to a budget

        $budget = Budget::where('event_id', $this->event_id)->first();
        if (!$budget) {
            $budget = Budget::create([
                'tenant_id'    => $this->tenant_id,
                'event_id'     => $this->event_id,
                'total_amount' => 0,
                'client_paid'  => 0,
                'currency'     => \App\Helpers\CurrencyHelper::forTenant() ? auth()->user()?->tenant?->billing_currency ?? 'NGN' : 'NGN',
            ]);
        }

        BudgetItem::updateOrCreate(
            ['vendor_invoice_id' => $this->id],
            [
                'tenant_id'  => $this->tenant_id,
                'budget_id'  => $budget->id,
                'category'   => ($this->vendor?->name ?? 'Vendor') . ' — ' . ($this->title ?: $this->invoice_number),
                'estimated'  => $this->total_amount,
                'actual'     => $this->total_amount,
                'paid'       => $this->totalPaid(),
                'notes'      => 'Auto-synced from ' . $this->invoice_number,
                'source'     => 'vendor_invoice',
            ]
        );
    }
}