<?php

namespace App\Livewire\Tenant\Auth;

use App\Models\Tenant\User;
use App\Services\AuthService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class Login extends Component
{
    public string $email       = '';
    public string $password    = '';
    public bool   $remember    = false;
    public bool   $showPassword = false;
    public string $error       = '';

    protected array $rules = [
        'email'    => 'required|email',
        'password' => 'required|min:6',
    ];

    public function togglePassword(): void
    {
        $this->showPassword = !$this->showPassword;
    }

    public function login(AuthService $authService): void
    {
        $this->validate();

        // Find user by email across all tenants
        $user = User::withoutGlobalScope('tenant')
            ->where('email', $this->email)
            ->where('is_active', true)
            ->first();

        if (!$user) {
            $this->error = 'Invalid credentials.';
            return;
        }

        // Login using tenant_id from the user record
        if (!$authService->tenantLogin(
            $this->email,
            $this->password,
            $user->tenant_id,
            $this->remember
        )) {
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