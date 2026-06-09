<?php

namespace App\Livewire\Platform\Auth;

use App\Services\AuthService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;
    public string $error = '';

    protected array $rules = [
        'email'    => 'required|email',
        'password' => 'required|min:6',
    ];

    public function login(AuthService $authService): void
    {
        $this->validate();

        if (!$authService->platformLogin($this->email, $this->password)) {
            $this->error = 'Invalid credentials.';
            return;
        }

        $this->redirect(route('platform.dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.platform.auth.login');
    }
}