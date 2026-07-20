<?php

namespace App\Livewire\Platform;

use App\Models\Central\BillingSetting;
use App\Models\Central\GatewayCharge;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.platform')]
class BillingConfig extends Component
{
    use WithToast;

    // API Keys
    public string $paystack_secret_key    = '';
    public string $paystack_public_key    = '';
    public string $flutterwave_secret_key = '';
    public string $flutterwave_public_key = '';

    // Settings
    public string $grace_period_days       = '7';
    public string $reminder_days           = '14';
    public string $reminder_days_urgent    = '3';
    public string $frankfurter_cache_hours = '24';
    public array  $enabled_gateways        = ['paystack', 'flutterwave'];

    // Gateway charges (indexed by gateway-region)
    public array $charges = [];

    public string $activeTab = 'settings';

    public function mount(): void
    {
        $this->paystack_secret_key    = BillingSetting::get('paystack_secret_key', '');
        $this->paystack_public_key    = BillingSetting::get('paystack_public_key', '');
        $this->flutterwave_secret_key = BillingSetting::get('flutterwave_secret_key', '');
        $this->flutterwave_public_key = BillingSetting::get('flutterwave_public_key', '');
        $this->grace_period_days      = (string) BillingSetting::get('grace_period_days', '7');
        $this->reminder_days          = (string) BillingSetting::get('reminder_days', '14');
        $this->reminder_days_urgent   = (string) BillingSetting::get('reminder_days_urgent', '3');
        $this->frankfurter_cache_hours= (string) BillingSetting::get('frankfurter_cache_hours', '24');
        $this->enabled_gateways       = BillingSetting::get('enabled_gateways', ['paystack', 'flutterwave']);

        // Load gateway charges
        foreach (GatewayCharge::all() as $charge) {
            $key = $charge->gateway . '_' . $charge->region;
            $this->charges[$key] = [
                'id'          => $charge->id,
                'gateway'     => $charge->gateway,
                'region'      => $charge->region,
                'percentage'  => (string) ($charge->percentage * 100), // display as percent
                'fixed_fee'   => (string) $charge->fixed_fee,
                'cap'         => (string) ($charge->cap ?? ''),
                'absorb'      => $charge->absorb,
                'is_active'   => $charge->is_active,
                'description' => $charge->description ?? '',
            ];
        }
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function saveSettings(): void
    {
        $this->validate([
            'grace_period_days'       => 'required|integer|min:0|max:30',
            'reminder_days'           => 'required|integer|min:1|max:30',
            'reminder_days_urgent'    => 'required|integer|min:1|max:14',
            'frankfurter_cache_hours' => 'required|integer|min:1|max:168',
        ]);

        BillingSetting::set('grace_period_days',       $this->grace_period_days);
        BillingSetting::set('reminder_days',           $this->reminder_days);
        BillingSetting::set('reminder_days_urgent',    $this->reminder_days_urgent);
        BillingSetting::set('frankfurter_cache_hours', $this->frankfurter_cache_hours);
        BillingSetting::set('enabled_gateways',        $this->enabled_gateways);

        $this->toastSuccess('Billing settings saved.');
    }

    public function saveApiKeys(): void
    {
        BillingSetting::set('paystack_secret_key',    $this->paystack_secret_key);
        BillingSetting::set('paystack_public_key',    $this->paystack_public_key);
        BillingSetting::set('flutterwave_secret_key', $this->flutterwave_secret_key);
        BillingSetting::set('flutterwave_public_key', $this->flutterwave_public_key);
        $this->toastSuccess('API keys saved.');
    }

    public function saveGatewayCharges(): void
    {
        foreach ($this->charges as $key => $charge) {
            GatewayCharge::updateOrCreate(
                ['gateway' => $charge['gateway'], 'region' => $charge['region']],
                [
                    'percentage'  => (float) $charge['percentage'] / 100,
                    'fixed_fee'   => (float) $charge['fixed_fee'],
                    'cap'         => $charge['cap'] !== '' ? (float) $charge['cap'] : null,
                    'absorb'      => $charge['absorb'],
                    'is_active'   => $charge['is_active'],
                    'description' => $charge['description'],
                ]
            );
        }
        $this->toastSuccess('Gateway charges updated.');
    }

    #[\Livewire\Attributes\Renderless]
    public function toggleGateway(string $gateway): void
    {
        if (in_array($gateway, $this->enabled_gateways)) {
            $this->enabled_gateways = array_values(array_filter(
                $this->enabled_gateways, fn($g) => $g !== $gateway
            ));
        } else {
            $this->enabled_gateways[] = $gateway;
        }
    }

    public function render()
    {
        return view('livewire.platform.billing-config');
    }
}