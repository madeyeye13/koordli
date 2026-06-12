<?php

namespace App\Livewire\Platform\Tenants;

use App\Models\Central\Plan;
use App\Services\TenantService;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.platform')]
class CreateTenant extends Component
{
    use WithToast;

    public string $name             = '';
    public string $owner_name       = '';
    public string $owner_email      = '';
    public string $owner_password   = '';
    public string $billing_currency = 'NGN';
    public ?int   $plan_id          = null;
    public bool   $success          = false;
    public string $error            = '';

    protected array $rules = [
        'name'             => 'required|string|min:2|max:100',
        'owner_name'       => 'required|string|min:2|max:100',
        'owner_email'      => 'required|email|unique:users,email',
        'owner_password'   => 'required|min:8',
        'billing_currency' => 'required|string',
        'plan_id'          => 'nullable|exists:plans,id',
    ];

    public function create(TenantService $tenantService): void
    {
        $this->validate();

        try {
            $tenantService->create([
                'name'             => $this->name,
                'owner_name'       => $this->owner_name,
                'owner_email'      => $this->owner_email,
                'owner_password'   => $this->owner_password,
                'billing_currency' => $this->billing_currency,
                'plan_id'          => $this->plan_id,
                'is_self_registered' => false,
            ]);

            $this->success = true;
            $this->reset(['name', 'owner_name', 'owner_email', 'owner_password', 'plan_id']);
            $this->toastSuccess('Company created. Welcome email sent to owner.');

            // Auto-dismiss success after 4 seconds
            $this->js("setTimeout(() => { \$wire.success = false; }, 4000)");

        } catch (\Exception $e) {
            $this->error = 'Something went wrong: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.platform.tenants.create-tenant', [
            'plans' => Plan::where('is_active', true)->get(),
        ]);
    }
}