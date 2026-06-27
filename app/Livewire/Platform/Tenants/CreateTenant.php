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
            $this->status           = $tenant->status ?? 'trial';

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

    public function create(TenantService $tenantService): void
    {
        $this->validate([
            'name'             => 'required|string|min:2|max:100',
            'owner_name'       => 'required|string|min:2|max:100',
            'owner_email'      => 'required|email|unique:users,email',
            'owner_password'   => 'required|min:8',
            'billing_currency' => 'required|string',
            'country'          => 'required|string|size:2',
            'plan_id'          => 'nullable|exists:plans,id',
        ]);

        try {
            $tenantService->create([
                'name'               => $this->name,
                'owner_name'         => $this->owner_name,
                'owner_email'        => $this->owner_email,
                'owner_password'     => $this->owner_password,
                'billing_currency'   => $this->billing_currency,
                'country'            => $this->country,
                'plan_id'            => $this->plan_id,
                'is_self_registered' => false,
            ]);

            $this->success = true;
            $this->reset(['name', 'owner_name', 'owner_email', 'owner_password', 'plan_id']);
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
            $this->tenant->update([
                'name'             => $this->name,
                'billing_currency' => $this->billing_currency,
                'country'          => $this->country,
                'plan_id'          => $this->plan_id,
                'status'           => $this->status,
            ]);

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
            'plans'     => Plan::where('is_active', true)->get(),
            'countries' => CurrencyHelper::countries(),
        ]);
    }
}