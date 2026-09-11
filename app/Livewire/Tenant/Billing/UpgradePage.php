<?php

namespace App\Livewire\Tenant\Billing;

use App\Models\Central\Plan;
use App\Models\Central\BillingSetting;
use App\Services\BillingService;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class UpgradePage extends Component
{
    use WithToast;

    public string $selectedCycle   = 'monthly';
    public ?int   $selectedPlanId  = null;
    public string $selectedGateway = 'paystack';
    public bool   $processing      = false;
    public array  $pricingData     = [];

        public function mount(): void
    {
        abort_unless(
            app(\App\Services\PermissionService::class)->userCan(auth()->user(), 'billing.manage'),
            403
        );

        $tenant = auth()->user()->tenant;
        $enabledGateways = BillingSetting::get('enabled_gateways', ['paystack', 'flutterwave']);
        $this->selectedGateway = $enabledGateways[0] ?? 'paystack';
        $this->loadPricing();
    }

    public function loadPricing(): void
    {
        $billing = app(BillingService::class);
        $tenant  = auth()->user()->tenant;
        $plans   = Plan::where('is_active', true)->orderByDesc('is_featured')->get();

        $this->pricingData = [];
        foreach ($plans as $plan) {
            foreach (['monthly', 'annual'] as $cycle) {
                if ($cycle === 'monthly' && !$plan->allowsMonthly()) continue;
                if ($cycle === 'annual' && !$plan->allowsAnnual()) continue;
                $this->pricingData[$plan->id][$cycle] = $billing->getPriceForTenant($plan, $tenant, $cycle);
            }
        }
    }

    public function checkout(int $planId, string $cycle = 'monthly', string $gateway = 'paystack'): void
    {
        if (!app(\App\Services\PermissionService::class)->userCan(auth()->user(), 'billing.manage')) {
            $this->toastError('You do not have permission to change the subscription plan.');
            return;
        }

        $this->processing = true;

        $billing = app(BillingService::class);
        $tenant  = auth()->user()->tenant;
        $plan    = Plan::find($planId);

        if (!$plan) {
            $this->toastError('Plan not found.');
            $this->processing = false;
            return;
        }

        if (!in_array($cycle, ['monthly', 'annual'], true)
            || !in_array($cycle, $plan->allowed_cycles ?? ['monthly', 'annual'], true)) {
            $this->toastError('That billing cycle is not available for this plan.');
            $this->processing = false;
            return;
        }

        $this->selectedCycle = $cycle;
        $this->selectedGateway = $gateway;

        $pricing = $billing->getPriceForTenant($plan, $tenant, $cycle);

        $result = match($gateway) {
            'flutterwave' => $billing->initializeFlutterwavePayment($tenant, $plan, $cycle, $pricing),
            default       => $billing->initializePaystackPayment($tenant, $plan, $cycle, $pricing),
        };

        if ($result['success']) {
            $this->redirect($result['authorization_url']);
            return;
        }

        $this->toastError($result['message'] ?? 'Payment initialization failed.');
        $this->processing = false;
    }

    public function render()
    {
        $tenant  = auth()->user()->tenant;
        $plans   = Plan::where('is_active', true)->orderByDesc('is_featured')->get();
        $enabled = BillingSetting::get('enabled_gateways', ['paystack', 'flutterwave']);

        $subscription = \App\Models\Central\Subscription::where('tenant_id', $tenant->id)
            ->latest()->first();

        return view('livewire.tenant.billing.upgrade-page', compact('plans', 'enabled', 'subscription'));
    }
}