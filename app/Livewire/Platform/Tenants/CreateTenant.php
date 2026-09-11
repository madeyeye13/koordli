<?php

namespace App\Livewire\Platform\Tenants;

use App\Helpers\CurrencyHelper;
use App\Models\Central\Plan;
use App\Models\Central\Tenant;
use App\Services\TenantService;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.platform')]
class CreateTenant extends Component
{
    use WithToast;

    public ?Tenant $tenant = null;
    public bool    $isEdit = false;

    public string $name             = '';
    public string $owner_name       = '';
    public string $owner_email      = '';
    public string $owner_password   = '';
    public string $billing_currency = 'NGN';
    public string $country          = 'NG';
    public ?int   $plan_id          = null;
    public ?int   $industry_profile_id = null;
    public string $subscription_mode = 'trial';
    public string $subscription_cycle = 'monthly';
    public string $status           = 'trial';
    public bool   $success          = false;
    public string $error            = '';

    public function mount(?Tenant $tenant = null): void
    {
        if ($tenant && $tenant->exists) {
            $this->isEdit           = true;
            $this->tenant           = $tenant;
            $this->name             = $tenant->name;
            $this->billing_currency = $tenant->billing_currency ?? 'NGN';
            $this->country          = $tenant->country ?? 'NG';
            $this->plan_id          = $tenant->plan_id ?? null;
            $this->industry_profile_id = $tenant->industry_profile_id ?? null;
            $this->status           = $tenant->status ?? 'trial';

            $subscription = $tenant->latestSubscription;
            if ($subscription) {
                $this->subscription_mode = $subscription->status === 'active' ? 'direct' : 'trial';
                $this->subscription_cycle = in_array($subscription->billing_cycle, ['monthly', 'annual'], true)
                    ? $subscription->billing_cycle
                    : 'monthly';
            }

            // Load owner info from first user
            $owner = \App\Models\Tenant\User::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenant->id)
                ->orderBy('id')
                ->first();

            if ($owner) {
                $this->owner_name  = $owner->name;
                $this->owner_email = $owner->email;
            }
        }
    }

    public function create(\App\Services\TenantProvisioningService $provisioningService): void
    {
        $this->validate([
            'name'                => 'required|string|min:2|max:100',
            'owner_name'          => 'required|string|min:2|max:100',
            'owner_email'         => 'required|email|unique:users,email',
            'owner_password'      => 'required|min:8',
            'billing_currency'    => 'required|string',
            'country'             => 'required|string|size:2',
            'plan_id'             => 'nullable|exists:plans,id',
            'industry_profile_id' => 'nullable|exists:industry_profiles,id',
            'subscription_mode'   => 'required|in:trial,direct',
            'subscription_cycle'  => 'required|in:monthly,annual',
        ]);

        try {
            $provisioningService->provision([
                'name'                => $this->name,
                'owner_name'          => $this->owner_name,
                'owner_email'         => $this->owner_email,
                'owner_password'      => $this->owner_password,
                'billing_currency'    => $this->billing_currency,
                'country'             => $this->country,
                'plan_id'             => $this->plan_id,
                'industry_profile_id' => $this->industry_profile_id,
                'subscription_mode'   => $this->subscription_mode,
                'subscription_cycle'  => $this->subscription_cycle,
                'is_self_registered'  => false,
            ]);

            $this->success = true;
            $this->reset(['name', 'owner_name', 'owner_email', 'owner_password', 'plan_id', 'industry_profile_id']);
            $this->toastSuccess('Company created. Welcome email sent to owner.');
            $this->js("setTimeout(() => { \$wire.success = false; }, 4000)");

        } catch (\Exception $e) {
            $this->error = 'Something went wrong: ' . $e->getMessage();
        }
    }

    public function update(): void
    {
        $this->validate([
            'name'             => 'required|string|min:2|max:100',
            'billing_currency' => 'required|string',
            'country'          => 'required|string|size:2',
            'plan_id'          => 'nullable|exists:plans,id',
            'status'           => 'required|in:trial,active,suspended,cancelled',
            'owner_name'       => 'required|string|min:2|max:100',
        ]);

        try {
            $wasSuspended = $this->tenant->status === 'suspended';
            $this->tenant->update([
                'name'             => $this->name,
                'billing_currency' => $this->billing_currency,
                'country'          => $this->country,
                'plan_id'          => $this->plan_id,
                'status'           => $this->status,
            ]);

            if (!$wasSuspended && $this->status === 'suspended') {
                $owner = \App\Models\Tenant\User::withoutGlobalScope('tenant')
                    ->where('tenant_id', $this->tenant->id)
                    ->orderBy('id')
                    ->first();

                if ($owner?->email) {
                    \App\Jobs\SendTenantSuspendedJob::dispatch(
                        $owner->email,
                        $this->tenant->name,
                        route('tenant.billing.upgrade'),
                    );
                }
            }

            if ($wasSuspended && $this->status === 'active') {
                $owner = \App\Models\Tenant\User::withoutGlobalScope('tenant')
                    ->where('tenant_id', $this->tenant->id)
                    ->orderBy('id')
                    ->first();

                if ($owner?->email) {
                    \App\Jobs\SendTenantReactivatedJob::dispatch(
                        $owner->email,
                        $this->tenant->name,
                        route('tenant.dashboard'),
                    );
                }
            }

            // Update owner name
            $owner = \App\Models\Tenant\User::withoutGlobalScope('tenant')
                ->where('tenant_id', $this->tenant->id)
                ->orderBy('id')
                ->first();

            if ($owner) {
                $owner->update(['name' => $this->owner_name]);
            }

            $this->toastSuccess('Company updated successfully.');
            $this->redirect(route('platform.tenants'), navigate: true);

        } catch (\Exception $e) {
            $this->error = 'Something went wrong: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.platform.tenants.create-tenant', [
            'plans'            => Plan::where('is_active', true)->get(),
            'countries'        => CurrencyHelper::countries(),
            'industryProfiles' => \App\Models\Central\IndustryProfile::where('is_active', true)->orderBy('sort_order')->get(),
            'subscription'     => $this->isEdit ? $this->tenant->latestSubscription : null,
        ]);
    }
}