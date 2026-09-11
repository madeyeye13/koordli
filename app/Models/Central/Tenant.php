<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'client_vendor_involvement_level',
        'vendor_disclaimer_text',
        'client_notification_settings',
        'industry_profile_id',
        'client_financial_visibility',
    ];

    protected $casts = [
        'branding'                     => 'array',
        'domain_verified_at'           => 'datetime',
        'domain_last_checked_at'       => 'datetime',
        'client_notification_settings' => 'array',
        'client_financial_visibility'  => 'array',
    ];

    /**
     * Every client notification category defaults to enabled unless a
     * tenant has explicitly turned it off — matches how tenant-staff
     * notification categories already default to enabled. This is the
     * ONE place that decision is made; every dispatch hook must call
     * this before notifying a client, never check the raw column directly.
     */
    public function clientNotificationEnabled(string $category): bool
    {
        $settings = $this->client_notification_settings ?? [];
        return $settings[$category] ?? true;
    }

    /**
     * Sensitive financial fields default to FALSE (hidden) unless the
     * planner explicitly turns them on — deliberately the opposite
     * default of client notifications, since financial detail is more
     * sensitive by nature. Basic figures (their own balance) default true.
     */
    public function clientFinancialVisible(string $field): bool
    {
        $settings = $this->client_financial_visibility ?? [];
        $safeDefaults = ['balance' => true];
        return $settings[$field] ?? ($safeDefaults[$field] ?? false);
    }

    public function industryProfile(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(IndustryProfile::class);
    }

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

        /**
     * Resolves this tenant's real public-facing base URL — their own
     * verified custom domain when configured, falling back to their
     * subdomain, then to the app's own default. Single source of truth
     * for every model that generates a tenant-facing public link
     * (RsvpForm, RsvpResponse, Form, etc.) — do not duplicate this
     * logic locally in another model; call this method instead.
     */
    public function resolvePublicBaseUrl(): string
    {
        if ($this->custom_domain && $this->domain_status === 'verified') {
            return 'https://' . $this->custom_domain;
        }

        if ($this->subdomain) {
            $appHost = parse_url(config('app.url'), PHP_URL_HOST);
            $scheme  = parse_url(config('app.url'), PHP_URL_SCHEME) ?? 'https';
            return $scheme . '://' . $this->subdomain . '.' . $appHost;
        }

        return config('app.url');
    }

    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Central\Subscription::class);
    }

    public function latestSubscription(): HasOne
    {
        return $this->hasOne(\App\Models\Central\Subscription::class)->latestOfMany();
    }
}