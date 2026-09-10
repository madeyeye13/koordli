<?php

namespace App\Livewire\Platform;

use App\Traits\WithToast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.platform')]
class Profile extends Component
{
    use WithToast;

    public string $name = '';
    public string $email = '';

    public string $currentPassword = '';
    public string $newPassword = '';
    public string $newPasswordConfirm = '';

    public function mount(): void
    {
        $user = auth('platform')->user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function updateName(): void
    {
        $this->validate(['name' => 'required|string|min:2|max:150']);

        auth('platform')->user()->update(['name' => $this->name]);
        $this->toastSuccess('Name updated.');
    }

    // ── Change Email ─────────────────────────────────────────
    public bool $showEmailChange = false;
    public string $newEmail = '';
    public string $emailChangePassword = '';
    public string $emailCode = '';
    public bool $emailCodeSent = false;

    public function requestEmailChange(): void
    {
        $user = auth('platform')->user();

        $this->validate([
            'newEmail' => 'required|email|unique:platform_users,email',
            'emailChangePassword' => 'required',
        ]);

        if (!Hash::check($this->emailChangePassword, $user->password)) {
            $this->addError('emailChangePassword', 'Your password is incorrect.');
            return;
        }

        $code = (string) random_int(100000, 999999);

        // Reuses the exact same table the forgot-password code lives in —
        // keyed by the NEW email, so nothing new needs to exist for this.
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $this->newEmail],
            ['token' => Hash::make($code), 'created_at' => now()]
        );

        \App\Jobs\SendPlatformEmailChangeCodeJob::dispatch($this->newEmail, $user->name, $code);
        \App\Jobs\SendPlatformEmailChangeAlertJob::dispatch($user->email, $user->name, $this->newEmail);

        $this->emailCodeSent = true;
        $this->toastSuccess('A verification code was sent to ' . $this->newEmail . '.');
    }

    public function confirmEmailChange(): void
    {
        $this->validate(['emailCode' => 'required|digits:6']);

        $record = DB::table('password_reset_tokens')->where('email', $this->newEmail)->first();

        if (!$record || now()->diffInMinutes($record->created_at) > 10 || !Hash::check($this->emailCode, $record->token)) {
            $this->addError('emailCode', 'That code is invalid or has expired.');
            return;
        }

        DB::table('password_reset_tokens')->where('email', $this->newEmail)->delete();

        $user = auth('platform')->user();
        $user->update(['email' => $this->newEmail]);
        $this->email = $this->newEmail;

        $this->reset(['showEmailChange', 'newEmail', 'emailChangePassword', 'emailCode', 'emailCodeSent']);
        $this->toastSuccess('Email address updated.');
    }

    public function changePassword(): void
    {
        $user = auth('platform')->user();

        $this->validate([
            'currentPassword' => 'required',
            'newPassword'     => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ], [], ['newPassword' => 'password']);

        if (!Hash::check($this->currentPassword, $user->password)) {
            $this->addError('currentPassword', 'Your current password is incorrect.');
            return;
        }

        if (Hash::check($this->newPassword, $user->password)) {
            $this->addError('newPassword', 'Your new password must be different from your current one.');
            return;
        }

        $user->update(['password' => $this->newPassword]);

        // Same real security behavior as the forgot-password flow — a
        // password change invalidates every OTHER active session for
        // this account, not just leaves the old credential silently
        // valid alongside the new one. The CURRENT session stays alive
        // deliberately, so this page doesn't log the person out on
        // themselves the moment they change it.
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', session()->getId())
            ->delete();

        \App\Jobs\SendPlatformPasswordChangedJob::dispatch($user->email, $user->name);

        $this->reset(['currentPassword', 'newPassword', 'newPasswordConfirm']);
        $this->toastSuccess('Password changed. You\'ve been signed out of any other active sessions.');
    }

    public function render()
    {
        return view('livewire.platform.profile');
    }
}