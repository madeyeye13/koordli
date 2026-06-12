<?php

namespace App\Services;

use App\Models\Central\PlatformUser;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    // Platform owner login
    public function platformLogin(string $email, string $password): bool
    {
        $user = PlatformUser::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return false;
        }

        Auth::guard('platform')->login($user);
        return true;
    }

    // Tenant user login
    public function tenantLogin(
        string $email,
        string $password,
        int $tenantId,
        bool $remember = false  // ← add this
    ): bool {
        $user = User::withoutGlobalScope('tenant')
            ->where('email', $email)
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return false;
        }

        Auth::guard('web')->login($user, $remember); // ← pass remember flag

        $user->update(['last_login_at' => now()]);

        return true;
    }

    public function platformLogout(): void
    {
        Auth::guard('platform')->logout();
    }

    public function tenantLogout(): void
    {
        Auth::guard('web')->logout();
    }

    public function platformUser(): ?PlatformUser
    {
        return Auth::guard('platform')->user();
    }

    public function tenantUser(): ?User
    {
        return Auth::guard('web')->user();
    }
}