<?php

namespace App\Livewire\Vendor\Auth;

use App\Models\Central\VendorAccount;
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
        $key = 'vendor-login:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->error = 'Too many attempts. Please try again later.';
            return;
        }

        $this->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $vendor = VendorAccount::where('email', $this->email)->first();

        if (!$vendor || !Hash::check($this->password, $vendor->password)) {
            RateLimiter::hit($key, 900);
            $this->error = 'Invalid email or password.';
            return;
        }

        if (!$vendor->is_active) {
            $this->error = 'Your account has been deactivated. Please contact the event company.';
            return;
        }

        RateLimiter::clear($key);
        Auth::guard('vendor')->login($vendor, $this->remember);
        $vendor->update(['last_login_at' => now()]);

        $this->redirect(route('vendor.dashboard'), navigate: true);
    }

    public function togglePassword(): void
    {
        $this->showPassword = !$this->showPassword;
    }

    public function render()
    {
        return view('livewire.vendor.auth.login');
    }
}