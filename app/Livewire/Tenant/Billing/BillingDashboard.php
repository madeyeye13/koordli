<?php

namespace App\Livewire\Tenant\Billing;

use App\Models\Central\Subscription;
use App\Models\Central\SubscriptionInvoice;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class BillingDashboard extends Component
{
    public function render()
    {
        abort_unless(
            app(\App\Services\PermissionService::class)->userCan(auth()->user(), 'billing.view'),
            403
        );

        $tenant       = auth()->user()->tenant;
        $subscription = Subscription::where('tenant_id', $tenant->id)
            ->with('plan')
            ->latest()
            ->first();

        $invoices = SubscriptionInvoice::where('tenant_id', $tenant->id)
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.tenant.billing.billing-dashboard', compact('subscription', 'invoices'));
    }
}