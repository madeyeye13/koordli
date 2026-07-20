<?php

namespace App\Livewire\Tenant\Billing;

use App\Models\Central\Plan;
use App\Services\BillingService;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class BillingCallback extends Component
{
    use WithToast;

    public string $status  = 'processing';
    public string $message = 'Verifying your payment...';

    public function mount(string $gateway): void
    {
        $billing = app(BillingService::class);
        $tenant  = auth()->user()->tenant;

        try {
            if ($gateway === 'paystack') {
                $reference = request('reference');
                $result    = $billing->verifyPaystackPayment($reference);

                if ($result['success']) {
                    $meta    = $result['metadata'];
                    $plan    = Plan::find($meta['plan_id'] ?? null);
                    $cycle   = $meta['cycle'] ?? 'monthly';

                    if ($plan) {
                        $billing->activateSubscription(
                            $tenant, $plan, $cycle, 'paystack',
                            $reference, $result['amount'], $result['currency']
                        );
                        $this->status  = 'success';
                        $this->message = 'Payment successful! Your plan is now active.';
                        return;
                    }
                }
            } elseif ($gateway === 'flutterwave') {
                $transactionId = request('transaction_id');
                $result        = $billing->verifyFlutterwavePayment($transactionId);

                if ($result['success']) {
                    $meta  = $result['metadata'];
                    $plan  = Plan::find($meta['plan_id'] ?? null);
                    $cycle = $meta['cycle'] ?? 'monthly';

                    if ($plan) {
                        $billing->activateSubscription(
                            $tenant, $plan, $cycle, 'flutterwave',
                            $result['reference'], $result['amount'], $result['currency']
                        );
                        $this->status  = 'success';
                        $this->message = 'Payment successful! Your plan is now active.';
                        return;
                    }
                }
            }

            $this->status  = 'failed';
            $this->message = 'Payment verification failed. Please contact support.';

        } catch (\Exception $e) {
            $this->status  = 'failed';
            $this->message = 'An error occurred. Please contact support.';
            \Log::error('Billing callback error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.tenant.billing.billing-callback');
    }
}