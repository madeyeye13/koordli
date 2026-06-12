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
}