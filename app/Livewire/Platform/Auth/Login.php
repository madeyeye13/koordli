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
    public bool $showPassword = false;
    public bool $remember = false;
    public string $error = '';

    protected array $rules = [
        'email'    => 'required|email',
        'password' => 'required|min:6',
    ];

    // togglePassword() removed — the eye toggle is now pure Alpine in the
    // view, with zero server round-trip, matching the instant-UI standard
    // used everywhere else in this app.

    public function login(AuthService $authService): void
    {
        $this->validate();

        $throttleKey = 'platform-login:' . strtolower($this->email) . '|' . request()->ip();

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            $this->error = "Too many failed attempts. Please try again in " . ceil($seconds / 60) . " minute(s).";
            return;
        }

        if (!$authService->platformLogin($this->email, $this->password)) {
            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300); // 5-minute lockout window
            $this->error = 'Invalid credentials.';
            return;
        }

        \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
        $this->redirect(route('platform.dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.platform.auth.login');
    }
}