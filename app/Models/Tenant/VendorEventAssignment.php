<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorEventAssignment extends Model
{
    use BelongsToTenant;

    protected $table = 'vendor_event_assignments';

    protected $fillable = [
        'tenant_id',
        'vendor_id',
        'event_id',
        'amount_agreed',
        'amount_paid',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount_agreed' => 'decimal:2',
        'amount_paid'   => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'confirmed'  => '#10B981',
            'cancelled'  => '#EF4444',
            default      => '#F59E0B',
        };
    }

    public function statusBadge(): string
    {
        return match($this->status) {
            'confirmed'  => 'krd-badge-green',
            'cancelled'  => 'krd-badge-red',
            default      => 'krd-badge-amber',
        };
    }

    public function balance(): float
    {
        return (float)$this->amount_agreed - (float)$this->amount_paid;
    }
}