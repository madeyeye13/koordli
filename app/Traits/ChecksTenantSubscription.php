<?php

namespace App\Traits;

use App\Models\Central\Subscription;

trait ChecksTenantSubscription
{
    public function checkSubscription(): bool
    {
        $user   = auth('web')->user();
        $tenant = $user?->tenant;

        if (!$tenant) return true;

        $subscription = Subscription::where('tenant_id', $tenant->id)
            ->latest()->first();

        if (!$subscription) return true;

        if ($subscription->isLocked()) {
            $this->dispatch('notify', message: 'Your subscription has expired. Renew to continue.', type: 'error');
            $this->redirect(route('tenant.billing.upgrade'), navigate: true);
            return false;
        }

        return true;
    }
}