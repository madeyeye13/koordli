<?php

namespace App\Livewire\Client\Auth;

use App\Models\Central\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class Login extends Component
{
    public string $email        = '';
    public string $password     = '';
    public bool   $showPassword = false;
    public bool   $remember     = false;
    public string $error        = '';

    public function login(): void
    {
        $key = 'client-login:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->error = 'Too many attempts. Please try again later.';
            return;
        }

        $this->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $client = Client::where('email', $this->email)->first();

        if (!$client || !Hash::check($this->password, $client->password)) {
            RateLimiter::hit($key, 900);
            $this->error = 'Invalid email or password.';
            return;
        }

        if (!$client->is_active) {
            $this->error = 'Your account has been deactivated. Please contact the event company.';
            return;
        }

        RateLimiter::clear($key);
        Auth::guard('client')->login($client, $this->remember);
        $client->update(['last_login_at' => now()]);

        $this->redirect(route('client.dashboard'), navigate: true);
    }

    public function togglePassword(): void
    {
        $this->showPassword = !$this->showPassword;
    }

    public function render()
    {
        return view('livewire.client.auth.login');
    }
}