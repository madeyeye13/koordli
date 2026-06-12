<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VendorApplication extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'uuid',
        'tenant_id',
        'vendor_category_id',
        'business_name',
        'contact_name',
        'email',
        'phone',
        'social_links',
        'service_description',
        'portfolio_urls',
        'rate_card_path',
        'pricing_info',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'social_links'   => 'array',
        'portfolio_urls' => 'array',
        'pricing_info'   => 'array',
        'reviewed_at'    => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(VendorCategory::class, 'vendor_category_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function vendorProfile(): HasOne
    {
        return $this->hasOne(VendorProfile::class, 'vendor_application_id');
    }
}