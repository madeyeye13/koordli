<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class VendorApplication extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'uuid', 'tenant_id', 'vendor_category_id',
        'business_name', 'contact_name', 'email', 'phone',
        'service_description', 'instagram', 'website',
        'available_to_travel',
        'status', 'rejection_reason', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at'        => 'datetime',
        'available_to_travel' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (VendorApplication $app) {
            if (empty($app->uuid)) {
                $app->uuid = Str::uuid();
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VendorCategory::class, 'vendor_category_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }
}