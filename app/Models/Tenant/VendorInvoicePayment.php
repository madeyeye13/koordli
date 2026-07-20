<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorInvoicePayment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'vendor_invoice_id', 'amount', 'paid_on',
        'payment_method', 'reference', 'notes', 'receipt_path', 'recorded_by',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_on' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (VendorInvoicePayment $payment) {
            if (empty($payment->recorded_by)) {
                $payment->recorded_by = auth()->id();
            }
        });

        static::saved(function (VendorInvoicePayment $payment) {
            $payment->invoice?->syncBudgetItem();
            $payment->invoice?->syncAssignmentAmount();
            $payment->invoice?->recalculateStatus();
        });

        static::deleted(function (VendorInvoicePayment $payment) {
            $payment->invoice?->syncBudgetItem();
            $payment->invoice?->syncAssignmentAmount();
            $payment->invoice?->recalculateStatus();
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(VendorInvoice::class, 'vendor_invoice_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function methodLabel(): string
    {
        return match($this->payment_method) {
            'bank_transfer' => 'Bank Transfer',
            'card'          => 'Card',
            'cash'          => 'Cash',
            default         => 'Other',
        };
    }
}