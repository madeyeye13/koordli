<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tenant extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'domain',
        'status',
        'plan_id',
        'branding',
        'country',
        'billing_currency',
        'detected_country',
        'subdomain',
        'custom_domain',
        'domain_verification_token',
        'domain_verified_at',
        'domain_last_checked_at',
        'domain_status',
    ];

    protected $casts = [
        'branding'                => 'array',
        'domain_verified_at'      => 'datetime',
        'domain_last_checked_at'  => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(\App\Models\Tenant\User::class);
    }

    public function featureOverrides(): HasMany
    {
        return $this->hasMany(TenantFeatureOverride::class);
    }

    public function hasFeature(string $key): bool
    {
        // Check tenant override first, then plan features
        $override = $this->featureOverrides()
            ->whereHas('featureFlag', fn($q) => $q->where('key', $key))
            ->first();

        if ($override) {
            return (bool) $override->value;
        }

        return $this->plan?->hasFeature($key) ?? false;
    }

    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Central\Subscription::class);
    }
}