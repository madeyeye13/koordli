<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class VendorContract extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'uuid', 'signing_token', 'tenant_id', 'vendor_id', 'event_id', 'vendor_event_assignment_id', 'template_id',
        'title', 'content', 'contract_amount', 'payment_schedule',
        'status', 'unsigned_file_path', 'signed_file_path',
        'expires_at', 'sent_at', 'signed_at', 'cancelled_at', 'created_by',
        'planner_signature_type', 'planner_signature_data', 'planner_signature_name', 'planner_signed_at', 'planner_signed_ip',
        'vendor_signature_type', 'vendor_signature_data', 'vendor_signature_name', 'vendor_signed_at', 'vendor_signed_ip',
    ];

    protected $casts = [
        'contract_amount'   => 'decimal:2',
        'expires_at'        => 'date',
        'sent_at'           => 'datetime',
        'signed_at'         => 'datetime',
        'cancelled_at'      => 'datetime',
        'planner_signed_at' => 'datetime',
        'vendor_signed_at'  => 'datetime',
    ];
    protected static function booted(): void
    {
        static::creating(function (VendorContract $contract) {
            if (empty($contract->uuid)) {
                $contract->uuid = Str::uuid();
            }
            if (empty($contract->signing_token)) {
                $contract->signing_token = Str::random(40);
            }
            if (empty($contract->created_by)) {
                $contract->created_by = auth()->id();
            }
        });
    }

    public function isFullySigned(): bool
    {
        return $this->planner_signed_at !== null && $this->vendor_signed_at !== null;
    }

    public function isSigningLinkExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function checkAndUpdateSignedStatus(): void
    {
        if ($this->isFullySigned() && $this->status !== 'signed') {
            $this->changeStatus('signed', 'Both parties signed electronically.');
            event(new \App\Events\ContractFullySigned($this));
        }
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(VendorEventAssignment::class, 'vendor_event_assignment_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(VendorContractTemplate::class, 'template_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(VendorContractStatusHistory::class)->orderByDesc('created_at');
    }

    public function changeStatus(string $newStatus, ?string $note = null): void
    {
        $oldStatus = $this->status;

        VendorContractStatusHistory::create([
            'tenant_id'          => $this->tenant_id,
            'vendor_contract_id' => $this->id,
            'from_status'        => $oldStatus,
            'to_status'          => $newStatus,
            'changed_by'         => auth()->id(),
            'note'               => $note,
        ]);

        $this->status = $newStatus;

        if ($newStatus === 'sent' && !$this->sent_at)      $this->sent_at = now();
        if ($newStatus === 'signed' && !$this->signed_at)   $this->signed_at = now();
        if ($newStatus === 'cancelled')                     $this->cancelled_at = now();

        $this->save();
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'sent'      => 'Sent',
            'signed'    => 'Signed',
            'expired'   => 'Expired',
            'cancelled' => 'Cancelled',
            default     => 'Draft',
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'sent'      => '#3B82F6',
            'signed'    => '#10B981',
            'expired'   => '#F59E0B',
            'cancelled' => '#EF4444',
            default     => '#78716C',
        };
    }

    public function isExpiringSoon(): bool
    {
        return $this->expires_at
            && $this->status !== 'signed'
            && $this->status !== 'cancelled'
            && $this->expires_at->isFuture()
            && $this->expires_at->diffInDays(now()) <= 14;
    }
}