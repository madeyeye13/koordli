<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorProfile extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'vendor_application_id',
        'vendor_category_id',
        'business_name',
        'description',
        'social_links',
        'portfolio_urls',
        'rate_card_path',
        'pricing_info',
        'is_active',
    ];

    protected $casts = [
        'social_links'   => 'array',
        'portfolio_urls' => 'array',
        'pricing_info'   => 'array',
        'is_active'      => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(VendorApplication::class, 'vendor_application_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VendorCategory::class, 'vendor_category_id');
    }

    public function eventAssignments(): HasMany
    {
        return $this->hasMany(Vendor::class, 'vendor_profile_id');
    }
}