<?php

namespace App\Livewire\Auth;

use App\Models\Central\Plan;
use App\Models\Central\Tenant;
use App\Models\Tenant\User;
use App\Services\TenantService;
use App\Enums\UserType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

#[Layout('layouts.auth')]
class Register extends Component
{
    // ── Current Step ──────────────────────────────────────────
    public int $step = 1;

    // ── Step 1 ────────────────────────────────────────────────
    public string $company_name = '';
    public string $name         = '';
    public string $email        = '';
    public string $password     = '';
    public string $password_confirmation = '';
    public bool   $showPassword        = false;
    public bool   $showPasswordConfirm = false;
    public bool   $agreed_to_terms     = false;
    public string $honeypot            = ''; // bot trap

    // ── Step 2 ────────────────────────────────────────────────
    public array  $code_digits  = ['', '', '', '', '', ''];
    public bool   $codeVerified = false;
    public int    $resendCooldown = 0;

    // ── Step 3 ────────────────────────────────────────────────
    public ?int $selected_plan_id = null;

    // ── Step 4 (skippable) ────────────────────────────────────
    public string $heard_from  = '';
    public string $team_size   = '';
    public array  $event_types = [];

    // ── Internal ──────────────────────────────────────────────
    public ?int $tenant_id = null;
    public string $error   = '';
    public string $success = '';

    // ── Step 1 Submit ─────────────────────────────────────────
    public function submitStep1(): void
    {
        // Honeypot check — bots fill hidden fields
        if (!empty($this->honeypot)) {
            return; // silently reject, don't tell bot it failed
        }

        // Rate limit by IP
        $key = 'register:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->error = 'Too many attempts. Please try again in a few minutes.';
            return;
        }
        RateLimiter::hit($key, 3600);

        // Validate
        $this->validate([
            'company_name' => 'required|string|min:2|max:100',
            'name'         => 'required|string|min:2|max:100',
            'email'        => 'required|email|unique:users,email',
            'password'     => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
            'agreed_to_terms' => 'accepted',
        ], [
            'password.regex'          => 'Password must contain uppercase, lowercase and a number.',
            'agreed_to_terms.accepted' => 'You must agree to the Terms & Conditions.',
            'email.unique'            => 'An account with this email already exists.',
        ]);

        // Send verification code
        $this->sendVerificationCode();

        $this->error = '';
        $this->step  = 2;
    }

    // ── Send/Resend Code ──────────────────────────────────────
    public function sendVerificationCode(): void
    {
        $resendKey = 'resend:' . $this->email;
        if (RateLimiter::tooManyAttempts($resendKey, 3)) {
            $this->error = 'Too many code requests. Please wait before requesting another.';
            return;
        }
        RateLimiter::hit($resendKey, 60);

        $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('email_verification_codes')
            ->where('email', $this->email)
            ->delete();

        DB::table('email_verification_codes')->insert([
            'email'      => $this->email,
            'code'       => Hash::make($code),
            'attempts'   => 0,
            'expires_at' => now()->addMinutes(15),
            'ip_address' => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \App\Jobs\SendVerificationCodeJob::dispatch($this->email, $this->name, $code);

        $this->resendCooldown = 60;
    }

    public function resendCode(): void
    {
        $this->sendVerificationCode();
        $this->success = 'A new code has been sent to your email.';
        $this->error   = '';
    }

    // ── Step 2 Verify ─────────────────────────────────────────
    public function verifyCode(): void
    {
        $enteredCode = implode('', $this->code_digits);

        if (strlen($enteredCode) !== 6) {
            $this->error = 'Please enter all 6 digits.';
            return;
        }

        // Rate limit verification attempts
        $key = 'verify:' . $this->email;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->error = 'Too many incorrect attempts. Please request a new code.';
            return;
        }

        $record = DB::table('email_verification_codes')
            ->where('email', $this->email)
            ->first();

        if (!$record) {
            $this->error = 'Verification code not found. Please request a new one.';
            return;
        }

        if (now()->isAfter($record->expires_at)) {
            $this->error = 'This code has expired. Please request a new one.';
            DB::table('email_verification_codes')->where('email', $this->email)->delete();
            return;
        }

        if (!Hash::check($enteredCode, $record->code)) {
            RateLimiter::hit($key, 900);
            $remaining = 3 - RateLimiter::attempts($key);
            $this->error = "Incorrect code. {$remaining} attempt(s) remaining.";
            return;
        }

        // Code is correct — delete it (one-time use)
        DB::table('email_verification_codes')->where('email', $this->email)->delete();
        RateLimiter::clear($key);

        // Create tenant + user inside transaction
        try {
            $result = DB::transaction(function () {
                $tenantService = app(\App\Services\TenantService::class);
                $tenant = $tenantService->create([
                    'name'             => $this->company_name,
                    'owner_name'       => $this->name,
                    'owner_email'      => $this->email,
                    'owner_password'   => $this->password,
                    'billing_currency' => 'NGN',
                    'is_self_registered' => true,
                ]);
                return $tenant;
            });

            $this->tenant_id = $result->id;
            $this->codeVerified = true;
            $this->error = '';

            // Auto advance to step 3 after short delay
            $this->step = 3;

        } catch (\Exception $e) {
            $this->error = 'Something went wrong creating your account. Please try again.';
        }
    }

    // ── Step 3 Select Plan ────────────────────────────────────
    public function selectPlan(int $planId): void
    {
        $plan = Plan::find($planId);
        if (!$plan) return;

        $this->selected_plan_id = $planId;

        // Update tenant subscription
        $tenant = Tenant::find($this->tenant_id);
        if ($tenant) {
            $tenant->update(['plan_id' => $planId]);

            // Create subscription record
            $trialDays = $plan->trial_days ?? 30;
            DB::table('subscriptions')->insert([
                'tenant_id'            => $tenant->id,
                'plan_id'              => $planId,
                'status'               => $plan->billing_cycle === 'trial' ? 'trial' : 'trial',
                'trial_ends_at'        => now()->addDays($trialDays),
                'current_period_start' => now(),
                'current_period_end'   => now()->addDays($trialDays),
                'currency'             => $tenant->billing_currency ?? 'NGN',
                'amount'               => 0,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }

        $this->step = 4;
    }

    // ── Step 4 Onboarding (skippable) ────────────────────────
    public function submitOnboarding(): void
    {
        $this->finishRegistration();
    }

    public function skip(): void
    {
        $this->finishRegistration();
    }

    private function finishRegistration(): void
    {
        // Log them in automatically
        $user = User::withoutGlobalScope('tenant')
            ->where('email', $this->email)
            ->where('tenant_id', $this->tenant_id)
            ->first();

        if ($user) {
            auth('web')->login($user);
            $user->update(['last_login_at' => now()]);
        }

        $this->step = 5;

        // Redirect to onboarding after short delay
        $this->redirect(route('tenant.onboarding'), navigate: true);
    }

    // ── Toggle Password Visibility ────────────────────────────
    public function togglePassword(): void
    {
        $this->showPassword = !$this->showPassword;
    }

    public function togglePasswordConfirm(): void
    {
        $this->showPasswordConfirm = !$this->showPasswordConfirm;
    }

    public function render()
    {
        return view('livewire.auth.register', [
            'plans' => Plan::where('is_active', true)->get(),
        ]);
    }
}