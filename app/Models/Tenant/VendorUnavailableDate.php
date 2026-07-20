<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorUnavailableDate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'vendor_id', 'date_from', 'date_to', 'reason', 'notes',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to'   => 'date',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function reasonLabel(): string
    {
        return match($this->reason) {
            'vacation' => 'Vacation',
            'holiday'  => 'Holiday',
            'other'    => 'Other',
            default    => 'Personal',
        };
    }

    public function reasonColor(): string
    {
        return match($this->reason) {
            'vacation' => '#3B82F6',
            'holiday'  => '#F59E0B',
            'other'    => '#78716C',
            default    => '#7C3AED',
        };
    }
}