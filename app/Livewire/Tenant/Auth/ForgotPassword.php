<?php

namespace App\Livewire\Tenant\Auth;

use App\Mail\TenantPasswordReset;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class ForgotPassword extends Component
{
    public string $email = '';
    public bool $sent = false;

    public function sendResetLink(): void
    {
        $this->validate(['email' => 'required|email']);

        $tenantId = app('tenant.id');
        $user = User::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('email', $this->email)
            ->where('is_active', true)
            ->first();

        if ($user) {
            $token = Str::random(64);
            DB::table('tenant_password_reset_tokens')->updateOrInsert(
                ['tenant_id' => $tenantId, 'email' => $user->email],
                ['token' => Hash::make($token), 'created_at' => now()]
            );

            $url = route('tenant.password.reset', ['token' => $token, 'email' => $user->email]);
            Mail::to($user->email)->send(new TenantPasswordReset($url, $user->name));
        }

        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.tenant.auth.forgot-password');
    }
}
