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

    public function setCycle(string $cycle): void
    {
        $this->selectedCycle = $cycle;
    }

    public function setGateway(string $gateway): void
    {
        $this->selectedGateway = $gateway;
    }

    public function checkout(int $planId): void
    {
        $this->processing = true;

        $billing = app(BillingService::class);
        $tenant  = auth()->user()->tenant;
        $plan    = Plan::find($planId);

        if (!$plan) {
            $this->toastError('Plan not found.');
            $this->processing = false;
            return;
        }

        $pricing = $billing->getPriceForTenant($plan, $tenant, $this->selectedCycle);

        $result = match($this->selectedGateway) {
            'flutterwave' => $billing->initializeFlutterwavePayment($tenant, $plan, $this->selectedCycle, $pricing),
            default       => $billing->initializePaystackPayment($tenant, $plan, $this->selectedCycle, $pricing),
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