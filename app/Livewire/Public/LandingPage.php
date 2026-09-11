<?php

namespace App\Livewire\Public;

use App\Models\Central\Plan;
use App\Models\Central\PlatformSetting;
use App\Services\BillingService;
use Livewire\Component;

#[\Livewire\Attributes\Layout('layouts.landing')]
class LandingPage extends Component
{
    public string $billingCycle = 'monthly';

    public function setCycle(string $cycle): void
    {
        $this->billingCycle = $cycle;
    }

    public function render()
    {
        $plans = Plan::where('is_active', true)
            ->orderByDesc('is_featured')
            ->get();

        $pricingMode = PlatformSetting::get('landing_pricing_mode', 'monthly');

        if (!$plans->contains(fn (Plan $plan) => $plan->allowsMonthly())
            && $plans->contains(fn (Plan $plan) => $plan->allowsAnnual())) {
            $this->billingCycle = 'annual';
        }

        // Detect visitor's likely currency the same way registration does
        $country  = request()->header('CF-IPCountry') ?? 'NG';
        $currency = \App\Helpers\CurrencyHelper::fromCountry($country);

        $billing = app(BillingService::class);
        $pricingData = [];

        foreach ($plans as $plan) {
            foreach (['monthly', 'annual'] as $cycle) {
                if ($cycle === 'monthly' && !$plan->allowsMonthly()) continue;
                if ($cycle === 'annual' && !$plan->allowsAnnual()) continue;

                // Always price off the MONTHLY row — annual is derived (monthly × 12 × discount)
                // below, never stored as its own row, to avoid double-applying ×12.
                $basePrice = \App\Models\Central\PlanPrice::where('plan_id', $plan->id)
                    ->where('currency', 'NGN')
                    ->where('billing_cycle', 'monthly')
                    ->where('is_active', true)
                    ->first();

                $baseAmount = $basePrice ? (float) $basePrice->amount : (float) $plan->price;

                if ($cycle === 'annual' && $plan->annual_discount_percent > 0) {
                    $baseAmount = $baseAmount * 12 * (1 - $plan->annual_discount_percent / 100);
                } elseif ($cycle === 'annual') {
                    $baseAmount = $baseAmount * 12;
                }

                $converted = $currency !== 'NGN'
                    ? $billing->convertAmount($baseAmount, 'NGN', $currency)
                    : $baseAmount;

                $pricingData[$plan->id][$cycle] = [
                    'amount'   => $converted,
                    'currency' => $currency,
                ];
            }
        }

        $siteName    = PlatformSetting::get('site_name', 'Koordli');
        $siteTagline = PlatformSetting::get('site_tagline', 'Event Operations Simplified');

        return view('livewire.public.landing-page', compact('plans', 'pricingData', 'siteName', 'siteTagline', 'pricingMode'));
    }
}