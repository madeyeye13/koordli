<?php

namespace App\Livewire\Tenant\Billing;

use App\Models\Central\Plan;
use App\Models\Central\Tenant;
use App\Models\Tenant\User;
use App\Services\BillingService;
use App\Traits\WithToast;
use Livewire\Component;

class BillingCallback extends Component
{
    use WithToast;

    public string $status  = 'processing';
    public string $message = 'Verifying your payment...';
    public bool $isNewRegistration = false;

    public function mount(string $gateway): void
    {
        $this->isNewRegistration = !auth()->check();
        $billing = app(BillingService::class);

        try {
            $result = match ($gateway) {
                'paystack'    => $billing->verifyPaystackPayment(request('reference')),
                'flutterwave' => $billing->verifyFlutterwavePayment(request('transaction_id')),
                default       => ['success' => false],
            };

            if (!$result['success']) {
                $this->status  = 'failed';
                $this->message = 'Payment verification failed. Please contact support.';
                return;
            }

            $meta      = $result['metadata'] ?? [];
            $plan      = Plan::find($meta['plan_id'] ?? null);
            $cycle     = $meta['cycle'] ?? 'monthly';
            $reference = $result['reference'] ?? (request('reference') ?? request('transaction_id'));

            // Resolve tenant two ways:
            // - Logged in already → existing tenant upgrading/renewing (normal case).
            // - Not logged in → brand-new tenant paying mid-registration; use metadata instead.
            $isNewRegistration = $this->isNewRegistration;
            $tenant = $isNewRegistration
                ? Tenant::find($meta['tenant_id'] ?? null)
                : auth()->user()->tenant;

            if (!$tenant || !$plan) {
                $this->status  = 'failed';
                $this->message = 'Payment verification failed. Please contact support.';
                return;
            }

            $billing->activateSubscription(
                $tenant, $plan, $cycle, $gateway,
                $reference, $result['amount'], $result['currency']
            );

            if ($isNewRegistration) {
                $owner = User::withoutGlobalScope('tenant')
                    ->where('tenant_id', $tenant->id)
                    ->orderBy('id')
                    ->first();

                if ($owner) {
                    auth('web')->login($owner);
                    $owner->update(['last_login_at' => now()]);
                }

                $registration = session('registration.wizard', []);
                $registration['step'] = 4;
                session()->put('registration.wizard', $registration);
                $this->status  = 'success';
                $this->message = 'Your payment has been confirmed. Your workspace is ready for setup.';
                return;
            }

            $this->status  = 'success';
            $this->message = 'Payment successful! Your plan is now active.';

        } catch (\Exception $e) {
            $this->status  = 'failed';
            $this->message = 'An error occurred. Please contact support.';
            \Log::error('Billing callback error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.tenant.billing.billing-callback')
            ->layout($this->isNewRegistration ? 'layouts.auth' : 'layouts.tenant');
    }
}