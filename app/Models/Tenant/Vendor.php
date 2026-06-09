<?php

namespace App\Models\Tenant;

use App\Enums\VendorStatus;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vendor extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'event_id',
        'vendor_category_id',
        'name',
        'contact_name',
        'phone',
        'email',
        'amount_agreed',
        'amount_paid',
        'status',
        'notes',
    ];

    protected $casts = [
        'status'        => VendorStatus::class,
        'amount_agreed' => 'decimal:2',
        'amount_paid'   => 'decimal:2',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VendorCategory::class, 'vendor_category_id');
    }
}