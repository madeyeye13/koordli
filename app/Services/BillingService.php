<?php

namespace App\Services;

use App\Models\Central\BillingSetting;
use App\Models\Central\GatewayCharge;
use App\Models\Central\Plan;
use App\Models\Central\PlanPrice;
use App\Models\Central\Subscription;
use App\Models\Central\SubscriptionInvoice;
use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BillingService
{
    // ── Exchange Rates ─────────────────────────────────────────────

    public function getExchangeRate(string $from, string $to): float
    {
        if ($from === $to) return 1.0;

        $cacheKey = "exchange_rate_{$from}_{$to}";
        $hours    = (int) BillingSetting::get('frankfurter_cache_hours', 24);

        return Cache::remember($cacheKey, now()->addHours($hours), function () use ($from, $to) {
            try {
                $response = Http::timeout(10)
                    ->get("https://api.frankfurter.app/latest", [
                        'from'   => $from,
                        'to'     => $to,
                    ]);

                if ($response->successful()) {
                    return $response->json("rates.{$to}", 1.0);
                }
            } catch (\Exception $e) {
                Log::warning("Frankfurter API failed: {$e->getMessage()}");
            }
            return 1.0;
        });
    }

    public function convertAmount(float $amount, string $from, string $to): float
    {
        $rate = $this->getExchangeRate($from, $to);
        return round($amount * $rate, 2);
    }

    // ── Price Calculation ─────────────────────────────────────────

    public function getPriceForTenant(Plan $plan, Tenant $tenant, string $cycle = 'monthly'): array
    {
        $baseCurrency    = BillingSetting::get('base_currency', 'NGN');
        $tenantCurrency  = $tenant->billing_currency ?? 'NGN';
        $country         = $tenant->country ?? 'NG';
        $isInternational = !in_array(strtoupper($country), ['NG']);

        // Get base NGN price
        $basePrice = PlanPrice::where('plan_id', $plan->id)
            ->where('currency', $baseCurrency)
            ->where('billing_cycle', $cycle)
            ->where('is_active', true)
            ->first();

        $baseAmount = $basePrice ? (float) $basePrice->amount : (float) $plan->price;

        // Apply annual discount
        if ($cycle === 'annual' && $plan->annual_discount_percent > 0) {
            $discount   = $plan->annual_discount_percent / 100;
            $baseAmount = $baseAmount * 12 * (1 - $discount);
        } elseif ($cycle === 'annual') {
            $baseAmount = $baseAmount * 12;
        }

        // Convert to tenant currency
        $convertedAmount = $this->convertAmount($baseAmount, $baseCurrency, $tenantCurrency);

        // Calculate absorbed gateway charges
        $gateway         = $this->getPreferredGateway($tenantCurrency);
        $region          = $isInternational ? 'international' : 'local';
        $gatewayCharge   = GatewayCharge::where('gateway', $gateway)
            ->where('region', $region)
            ->where('is_active', true)
            ->first();

        $amountWithCharges = $convertedAmount;
        $feeAmount         = 0;

        if ($gatewayCharge && $gatewayCharge->absorb) {
            $amountWithCharges = $gatewayCharge->calculateAbsorbed($convertedAmount);
            $feeAmount         = $amountWithCharges - $convertedAmount;
        }

        return [
            'base_amount'        => $baseAmount,
            'base_currency'      => $baseCurrency,
            'amount'             => $convertedAmount,
            'amount_with_charges'=> $amountWithCharges,
            'fee_absorbed'       => round($feeAmount, 2),
            'currency'           => $tenantCurrency,
            'gateway'            => $gateway,
            'region'             => $region,
            'cycle'              => $cycle,
            'exchange_rate'      => $this->getExchangeRate($baseCurrency, $tenantCurrency),
        ];
    }

    public function getPreferredGateway(string $currency): string
    {
        $enabled = BillingSetting::get('enabled_gateways', ['paystack', 'flutterwave']);

        // Paystack supports NGN, GHS, USD, ZAR, KES
        // Flutterwave supports more currencies
        $paystackCurrencies     = ['NGN', 'GHS', 'USD', 'ZAR', 'KES', 'GBP'];
        $flutterwaveCurrencies  = ['NGN', 'GHS', 'USD', 'ZAR', 'KES', 'GBP', 'EUR', 'XOF', 'XAF'];

        if (in_array('paystack', $enabled) && in_array($currency, $paystackCurrencies)) {
            return 'paystack';
        }
        if (in_array('flutterwave', $enabled) && in_array($currency, $flutterwaveCurrencies)) {
            return 'flutterwave';
        }

        return $enabled[0] ?? 'paystack';
    }

    // ── Payment Initialization ─────────────────────────────────────

    public function initializePaystackPayment(Tenant $tenant, Plan $plan, string $cycle, array $pricing): array
    {
        $secretKey = BillingSetting::get('paystack_secret_key');
        $reference = 'KRD-' . strtoupper(Str::random(12));
        $owner     = \App\Models\Tenant\User::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->orderBy('id')
            ->first();

        $response = Http::withToken($secretKey)
            ->post('https://api.paystack.co/transaction/initialize', [
                'email'     => $owner?->email,
                'amount'    => (int) ($pricing['amount_with_charges'] * 100), // kobo
                'currency'  => $pricing['currency'],
                'reference' => $reference,
                'metadata'  => [
                    'tenant_id'   => $tenant->id,
                    'plan_id'     => $plan->id,
                    'cycle'       => $cycle,
                    'koordli_ref' => $reference,
                ],
                'callback_url' => route('tenant.billing.callback', ['gateway' => 'paystack']),
            ]);

        if ($response->successful() && $response->json('status')) {
            return [
                'success'           => true,
                'authorization_url' => $response->json('data.authorization_url'),
                'reference'         => $reference,
            ];
        }

        return ['success' => false, 'message' => $response->json('message', 'Payment initialization failed.')];
    }

    public function initializeFlutterwavePayment(Tenant $tenant, Plan $plan, string $cycle, array $pricing): array
    {
        $secretKey = BillingSetting::get('flutterwave_secret_key');
        $reference = 'KRD-' . strtoupper(Str::random(12));
        $owner     = \App\Models\Tenant\User::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->orderBy('id')
            ->first();

        $response = Http::withToken($secretKey)
            ->post('https://api.flutterwave.com/v3/payments', [
                'tx_ref'          => $reference,
                'amount'          => $pricing['amount_with_charges'],
                'currency'        => $pricing['currency'],
                'redirect_url'    => route('tenant.billing.callback', ['gateway' => 'flutterwave']),
                'customer'        => [
                    'email'       => $owner?->email,
                    'name'        => $owner?->name,
                ],
                'meta' => [
                    'tenant_id'   => $tenant->id,
                    'plan_id'     => $plan->id,
                    'cycle'       => $cycle,
                    'koordli_ref' => $reference,
                ],
                'customizations' => [
                    'title'       => 'Koordli Subscription',
                    'description' => "{$plan->name} — " . ucfirst($cycle),
                    'logo'        => url('/images/logoonwhite.png'),
                ],
            ]);

        if ($response->successful() && $response->json('status') === 'success') {
            return [
                'success'           => true,
                'authorization_url' => $response->json('data.link'),
                'reference'         => $reference,
            ];
        }

        return ['success' => false, 'message' => $response->json('message', 'Payment initialization failed.')];
    }

    // ── Subscription Activation ────────────────────────────────────

    public function activateSubscription(Tenant $tenant, Plan $plan, string $cycle, string $gateway, string $reference, float $amount, string $currency): Subscription
    {
        $now     = now();
        $expires = $cycle === 'annual' ? $now->copy()->addYear() : $now->copy()->addMonth();
        $grace   = $expires->copy()->addDays((int) BillingSetting::get('grace_period_days', 7));

        // Deactivate existing subscriptions
        Subscription::where('tenant_id', $tenant->id)
            ->whereIn('status', ['active', 'trial'])
            ->update(['status' => 'cancelled', 'cancelled_at' => $now]);

        $subscription = Subscription::create([
            'tenant_id'             => $tenant->id,
            'plan_id'               => $plan->id,
            'status'                => 'active',
            'billing_cycle'         => $cycle,
            'current_period_start'  => $now,
            'current_period_end'    => $expires,
            'expires_at'            => $expires,
            'grace_until'           => $grace,
            'gateway'               => $gateway,
            'gateway_subscription_id' => $reference,
            'currency'              => $currency,
            'amount'                => $amount,
            'reminder_14_sent'      => false,
            'reminder_3_sent'       => false,
        ]);

        // Create invoice
        $baseCurrency = BillingSetting::get('base_currency', 'NGN');
        $rate         = $this->getExchangeRate($currency, $baseCurrency);

        SubscriptionInvoice::create([
            'tenant_id'          => $tenant->id,
            'subscription_id'    => $subscription->id,
            'gateway'            => $gateway,
            'gateway_invoice_id' => $reference,
            'amount'             => $amount,
            'amount_ngn'         => round($amount * $rate, 2),
            'exchange_rate'      => $rate,
            'currency'           => $currency,
            'billing_cycle'      => $cycle,
            'status'             => 'paid',
            'paid_at'            => $now,
        ]);

       // Update tenant status + plan
        $tenant->update([
            'status'  => 'active',
            'plan_id' => $plan->id,
        ]);

        // Notify tenant + platform owner
        $owner = \App\Models\Tenant\User::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->orderBy('id')
            ->first();

        if ($owner?->email) {
            \App\Jobs\SendSubscriptionActivatedJob::dispatch(
                $owner->email,
                $owner->name,
                $plan->name,
                $cycle,
                number_format($amount, 2),
                $currency,
                $expires->format('D, d M Y'),
                $gateway,
            );
        }

        \App\Jobs\SendPlatformPaymentNotificationJob::dispatch(
            $tenant->name,
            $plan->name,
            $cycle,
            number_format($amount, 2),
            $currency,
            number_format($amount * $rate, 2),
            $gateway,
            $now->format('D, d M Y g:i A'),
        );

        return $subscription;
    }

    // ── Verify Paystack Payment ────────────────────────────────────

    public function verifyPaystackPayment(string $reference): array
    {
        $secretKey = BillingSetting::get('paystack_secret_key');

        $response = Http::withToken($secretKey)
            ->get("https://api.paystack.co/transaction/verify/{$reference}");

        if ($response->successful() && $response->json('data.status') === 'success') {
            $data = $response->json('data');
            return [
                'success'   => true,
                'amount'    => $data['amount'] / 100,
                'currency'  => $data['currency'],
                'reference' => $reference,
                'metadata'  => $data['metadata'] ?? [],
            ];
        }

        return ['success' => false, 'message' => $response->json('message', 'Verification failed.')];
    }

    // ── Verify Flutterwave Payment ─────────────────────────────────

    public function verifyFlutterwavePayment(string $transactionId): array
    {
        $secretKey = BillingSetting::get('flutterwave_secret_key');

        $response = Http::withToken($secretKey)
            ->get("https://api.flutterwave.com/v3/transactions/{$transactionId}/verify");

        if ($response->successful() && $response->json('data.status') === 'successful') {
            $data = $response->json('data');
            return [
                'success'   => true,
                'amount'    => $data['amount'],
                'currency'  => $data['currency'],
                'reference' => $data['tx_ref'],
                'metadata'  => $data['meta'] ?? [],
            ];
        }

        return ['success' => false, 'message' => $response->json('message', 'Verification failed.')];
    }

    // ── Check & Update Expired Subscriptions ──────────────────────

    public function processExpiredSubscriptions(): void
    {
        // Update trial-expired tenants
        $expiredTrials = Subscription::where('status', 'trial')
            ->where('trial_ends_at', '<', now())
            ->get();

        foreach ($expiredTrials as $sub) {
            $grace = now()->addDays((int) BillingSetting::get('grace_period_days', 7));
            $sub->update([
                'status'      => 'expired',
                'expires_at'  => $sub->trial_ends_at,
                'grace_until' => $grace,
            ]);
            $sub->tenant->update(['status' => 'expired']);
        }

        // Update active subscriptions past their expiry
        $expiredActive = Subscription::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expiredActive as $sub) {
            $sub->update(['status' => 'expired']);
            $sub->tenant->update(['status' => 'expired']);
        }
    }
}