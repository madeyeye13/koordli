<?php

namespace App\Livewire\Tenant\Auth;

use App\Services\AuthService;
use App\Services\TenantContext;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public string $error = '';

    protected array $rules = [
        'email'    => 'required|email',
        'password' => 'required|min:6',
    ];

    public function login(
        AuthService $authService,
        TenantContext $tenantContext
    ): void {
        $this->validate();

        $tenant = $tenantContext->get();

        if (!$tenant) {
            $this->error = 'Tenant not found.';
            return;
        }

        if (!$authService->tenantLogin($this->email, $this->password, $tenant->id)) {
            $this->error = 'Invalid credentials.';
            return;
        }

        $this->redirect(route('tenant.dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.tenant.auth.login');
    }
}