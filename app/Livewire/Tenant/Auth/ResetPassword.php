<?php

namespace App\Livewire\Tenant\Auth;

use App\Models\Tenant\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class ResetPassword extends Component
{
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $error = '';
    public bool $complete = false;

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = (string) request()->query('email');
    }

    public function resetPassword(): void
    {
        $this->validate([
            'email' => 'required|email',
            'password' => ['required', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
        ], [
            'password.regex' => 'Password must contain uppercase, lowercase and a number.',
        ]);

        $tenantId = app('tenant.id');
        $reset = DB::table('tenant_password_reset_tokens')
            ->where('tenant_id', $tenantId)
            ->where('email', $this->email)
            ->first();

        if (!$reset || !Hash::check($this->token, $reset->token) || !$reset->created_at || now()->diffInMinutes($reset->created_at) > 60) {
            $this->error = 'This password reset link is invalid or has expired.';
            return;
        }

        $user = User::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('email', $this->email)
            ->where('is_active', true)
            ->first();

        if (!$user) {
            $this->error = 'This password reset link is invalid or has expired.';
            return;
        }

        $user->update(['password' => Hash::make($this->password)]);
        DB::table('tenant_password_reset_tokens')->where('tenant_id', $tenantId)->where('email', $this->email)->delete();
        $this->complete = true;
    }

    public function render()
    {
        return view('livewire.tenant.auth.reset-password');
    }
}
