<?php

namespace App\Services;

use App\Models\Central\Plan;
use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Cache;

class FeatureGateService
{
    public function canAccess(Tenant $tenant, string $featureKey): bool
    {
        $plan = $tenant->plan;
        if (!$plan) return false;

        $features = $plan->features ?? [];
        $value    = $features[$featureKey] ?? 'false';

        return $value === 'true' || $value === 'unlimited' || (is_numeric($value) && (int)$value > 0);
    }

    public function getLimit(Tenant $tenant, string $limitKey): int
    {
        // Check tenant override first
        $override = $tenant->featureOverrides()
            ->whereHas('featureFlag', fn($q) => $q->where('key', $limitKey))
            ->first();

        if ($override) return (int) $override->value;

        $limits = $tenant->plan?->limits ?? [];
        return isset($limits[$limitKey]) ? (int) $limits[$limitKey] : 0;
    }

    public function isOnTrial(Tenant $tenant): bool
    {
        return $tenant->subscriptions()
            ->where('status', 'trial')
            ->exists();
    }

    public function trialDaysLeft(Tenant $tenant): int
    {
        $subscription = $tenant->subscriptions()
            ->where('status', 'trial')
            ->first();

        if (!$subscription || !$subscription->trial_ends_at) return 0;

        return max(0, now()->diffInDays($subscription->trial_ends_at, false));
    }

    public function isHighestPlan(Tenant $tenant): bool
    {
        if (!$tenant->plan) return false;

        // Highest plan = the one with the highest price or marked as enterprise
        $highestPlan = Plan::where('is_active', true)
            ->orderByDesc('id')
            ->first();

        return $tenant->plan_id === $highestPlan?->id;
    }

    public function storageBytesUsed(Tenant $tenant): int
    {
        return app(\App\Services\DocumentStorageService::class)->totalBytesUsed($tenant->id);
    }

    /**
     * $additionalBytes is the size of a file about to be uploaded — pass 0
     * to just check whether the tenant is ALREADY over their limit.
     */
    public function hasStorageAvailable(Tenant $tenant, int $additionalBytes = 0): bool
    {
        $limitMb = $this->getLimit($tenant, 'max_storage_mb');
        if ($limitMb <= 0) return true; // 0/unset = unlimited, matches getLimit()'s existing convention elsewhere in this app

        $limitBytes = $limitMb * 1024 * 1024;
        $usedBytes  = $this->storageBytesUsed($tenant);

        return ($usedBytes + $additionalBytes) <= $limitBytes;
    }
}