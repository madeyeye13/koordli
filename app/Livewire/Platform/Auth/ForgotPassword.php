<?php

namespace App\Livewire\Platform\Auth;

use App\Jobs\SendPlatformPasswordResetCodeJob;
use App\Models\Central\PlatformUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class ForgotPassword extends Component
{
    public int $step = 1; // 1 = enter email, 2 = enter code + new password

    public string $email = '';
    public string $code = '';
    public string $newPassword = '';
    public string $newPasswordConfirm = '';

    public string $error = '';
    public string $success = '';
    public int $resendCooldown = 0;

    private const CODE_EXPIRY_MINUTES = 10;
    private const RESEND_COOLDOWN_SECONDS = 60;

    public function requestCode(): void
    {
        $this->error = '';
        $this->validate(['email' => 'required|email']);

        // Rate limit the REQUEST itself, keyed by IP — independent of
        // whether the email exists, so this can't be used to hammer
        // the mail queue regardless of target.
        $throttleKey = 'platform-forgot-password:' . request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $this->error = 'Too many attempts. Please try again in a few minutes.';
            return;
        }
        RateLimiter::hit($throttleKey, 300);

        $user = PlatformUser::where('email', $this->email)->where('is_active', true)->first();

        $code = (string) random_int(100000, 999999);

        if ($user) {
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $this->email],
                ['token' => Hash::make($code), 'created_at' => now()]
            );

            SendPlatformPasswordResetCodeJob::dispatch($this->email, $user->name, $code);
        }

        // SAME message regardless of whether the account exists —
        // never confirms/denies a specific email is registered.
        $this->success = 'If that email is registered, a verification code has been sent.';
        $this->step = 2;
        $this->resendCooldown = self::RESEND_COOLDOWN_SECONDS;
    }

    public function resendCode(): void
    {
        if ($this->resendCooldown > 0) return;
        $this->success = '';
        $this->requestCode();
    }

    public function verifyAndReset(): void
    {
        $this->error = '';

        $this->validate([
            'code' => 'required|digits:6',
            'newPassword' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ], [], ['newPassword' => 'password']);

        $record = DB::table('password_reset_tokens')->where('email', $this->email)->first();

        if (!$record) {
            $this->error = 'This code has expired. Please request a new one.';
            return;
        }

        if (now()->diffInMinutes($record->created_at) > self::CODE_EXPIRY_MINUTES) {
            DB::table('password_reset_tokens')->where('email', $this->email)->delete();
            $this->error = 'This code has expired. Please request a new one.';
            return;
        }

        // hash_equals-safe via Hash::check() — Laravel's Hash facade
        // already performs a timing-safe comparison internally.
        if (!Hash::check($this->code, $record->token)) {
            $this->error = 'Incorrect code. Please try again.';
            return;
        }

        $user = PlatformUser::where('email', $this->email)->first();
        if (!$user) {
            $this->error = 'Something went wrong. Please request a new code.';
            return;
        }

        $user->update(['password' => $this->newPassword]);

        // Single-use — the code is now dead regardless of outcome from here.
        DB::table('password_reset_tokens')->where('email', $this->email)->delete();

        // Invalidate every other active session for this account — a
        // password reset should mean old sessions stop working too.
        DB::table('sessions')->where('user_id', $user->id)->delete();

        $this->success = 'Password updated. You can now sign in.';
        $this->redirect(route('platform.login'), navigate: true);
    }

    public function updatedStep(): void {}

    public function render()
    {
        return view('livewire.platform.auth.forgot-password');
    }
}