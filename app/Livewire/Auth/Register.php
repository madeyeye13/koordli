<?php

namespace App\Livewire\Auth;

use App\Helpers\CurrencyHelper;
use App\Models\Central\Plan;
use App\Models\Central\Tenant;
use App\Models\Tenant\User;
use App\Services\TenantService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Stevebauman\Location\Facades\Location;

#[Layout('layouts.auth')]
class Register extends Component
{
    // ── Current Step ──────────────────────────────────────────
    public int $step = 1;

    // ── Step 1 ────────────────────────────────────────────────
    public string $company_name          = '';
    public ?int   $industry_profile_id   = null;
    public string $country               = '';
    public string $name                  = '';
    public string $email                 = '';
    public string $password              = '';
    public string $password_confirmation = '';
    public bool   $showPassword          = false;
    public bool   $showPasswordConfirm   = false;
    public bool   $agreed_to_terms       = false;
    public string $honeypot              = '';

    // ── Step 2 ────────────────────────────────────────────────
    public array $code_digits    = ['', '', '', '', '', ''];
    public bool  $codeVerified   = false;
    public int   $resendCooldown = 0;

    // ── Step 3 ────────────────────────────────────────────────
    public ?int $selected_plan_id = null;

    // ── Step 4 (skippable) ────────────────────────────────────
    public string $heard_from  = '';
    public string $team_size   = '';
    public array  $event_types = [];

    // ── Internal ──────────────────────────────────────────────
    public ?int   $tenant_id = null;
    public string $error     = '';
    public string $success   = '';

    public function clearMessages(): void
    {
        $this->error   = '';
        $this->success = '';
    }

    #[Renderless]
    public function selectIndustryProfile(int $profileId): void
    {
        $this->industry_profile_id = $profileId;
    }

    public function mount(): void
    {
        $savedRegistration = session('registration.wizard');

        if (is_array($savedRegistration)) {
            if (!empty($savedRegistration['tenant_id']) && !Tenant::find($savedRegistration['tenant_id'])) {
                session()->forget('registration.wizard');
                $savedRegistration = null;
            }
        }

        if (is_array($savedRegistration)) {
            foreach ($savedRegistration as $property => $value) {
                if (property_exists($this, $property)) {
                    $this->{$property} = $value;
                }
            }

            return;
        }

        // Auto-detect country from IP
        try {
            $location = Location::get(request()->ip());
            if ($location && $location->countryCode) {
                $this->country = strtoupper($location->countryCode);
            }
        } catch (\Exception $e) {
            // Fail silently — user can pick manually
        }

        // Default to Nigeria if not detected
        if (empty($this->country)) {
            $this->country = 'NG';
        }
    }

    private function saveRegistrationState(): void
    {
        session()->put('registration.wizard', [
            'step'                => $this->step,
            'company_name'        => $this->company_name,
            'industry_profile_id' => $this->industry_profile_id,
            'country'             => $this->country,
            'name'                => $this->name,
            'email'               => $this->email,
            'password'            => $this->password,
            'password_confirmation' => $this->password_confirmation,
            'agreed_to_terms'     => $this->agreed_to_terms,
            'code_digits'         => $this->code_digits,
            'codeVerified'        => $this->codeVerified,
            'resendCooldown'      => $this->resendCooldown,
            'tenant_id'           => $this->tenant_id,
            'selected_plan_id'    => $this->selected_plan_id,
            'heard_from'          => $this->heard_from,
            'team_size'           => $this->team_size,
            'event_types'         => $this->event_types,
        ]);
    }

    public function updatedCountry(): void
    {
        // Currency updates automatically when country changes
        // Nothing needed here — computed in getCurrencyProperty()
    }

    public function getCurrency(): string
    {
        return CurrencyHelper::fromCountry($this->country);
    }

    // ── Step 1 Submit ─────────────────────────────────────────
    public function submitStep1(): void
    {
        if (!empty($this->honeypot)) return;

        $key = 'register:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->error = 'Too many attempts. Please try again in a few minutes.';
            return;
        }
        RateLimiter::hit($key, 3600);

        $this->validate([
            'company_name'        => 'required|string|min:2|max:100',
            'industry_profile_id' => 'required|exists:industry_profiles,id',
            'country'             => 'required|string|size:2',
            'name'            => 'required|string|min:2|max:100',
            'email'           => 'required|email|unique:users,email',
            'password'        => [
                'required', 'min:8', 'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
            'agreed_to_terms' => 'accepted',
        ], [
            'password.regex'           => 'Password must contain uppercase, lowercase and a number.',
            'agreed_to_terms.accepted' => 'You must agree to the Terms & Conditions.',
            'email.unique'             => 'An account with this email already exists.',
            'country.required'         => 'Please select your country.',
        ]);

        $this->sendVerificationCode();
        $this->error = '';
        $this->step  = 2;
        $this->saveRegistrationState();
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
        $this->saveRegistrationState();
    }

    public function backToAccount(): void
    {
        if ($this->step !== 2) {
            return;
        }

        $this->step        = 1;
        $this->code_digits = ['', '', '', '', '', ''];
        $this->error       = '';
        $this->success     = '';
        $this->saveRegistrationState();
    }

    // ── Step 2 Verify ─────────────────────────────────────────
    public function verifyCode(): void
    {
        $enteredCode = implode('', $this->code_digits);

        if (strlen($enteredCode) !== 6) {
            $this->error = 'Please enter all 6 digits.';
            return;
        }

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
            $remaining   = 3 - RateLimiter::attempts($key);
            $this->error = "Incorrect code. {$remaining} attempt(s) remaining.";
            return;
        }

        DB::table('email_verification_codes')->where('email', $this->email)->delete();
        RateLimiter::clear($key);

        try {
            $provisioningService = app(\App\Services\TenantProvisioningService::class);
            $result = $provisioningService->provision([
                'name'                => $this->company_name,
                'owner_name'          => $this->name,
                'owner_email'         => $this->email,
                'owner_password'      => $this->password,
                'billing_currency'    => $this->getCurrency(),
                'country'             => $this->country,
                'is_self_registered'  => true,
                'industry_profile_id' => $this->industry_profile_id,
            ]);

            $this->tenant_id    = $result->id;
            $this->codeVerified = true;
            $this->error        = '';
            $this->step         = 3;
            $this->saveRegistrationState();

        } catch (\Exception $e) {
            $this->error = 'Something went wrong creating your account. Please try again.';
        }
    }

            // ── Step 3 Select Plan ────────────────────────────────────
    public function selectPlan(int $planId, string $intent = 'trial'): void
    {
        $plan = Plan::find($planId);
        if (!$plan || $plan->is_contact_only) return;

        $tenant = Tenant::find($this->tenant_id);
        if (!$tenant) return;

        $this->selected_plan_id = $planId;
        $this->error = '';

        if ($intent === 'trial') {
            if (!$plan->hasTrial()) {
                $this->error = 'This plan does not offer a free trial. Please choose "Subscribe now" instead.';
                return;
            }

            $tenant->update(['plan_id' => $planId]);

            DB::table('subscriptions')->insert([
                'tenant_id'            => $tenant->id,
                'plan_id'              => $planId,
                'status'               => 'trial',
                'trial_ends_at'        => now()->addDays($plan->trial_days),
                'current_period_start' => now(),
                'current_period_end'   => now()->addDays($plan->trial_days),
                'currency'             => $tenant->billing_currency,
                'amount'               => 0,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

                        $owner = \App\Models\Tenant\User::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenant->id)
                ->orderBy('id')
                ->first();

            if ($owner) {
                \App\Jobs\SendTrialStartedJob::dispatch(
                    $owner->email,
                    $owner->name,
                    $tenant->name,
                    $plan->name,
                    $plan->trial_days,
                    now()->addDays($plan->trial_days)->format('D, d M Y'),
                    route('tenant.dashboard'),
                );
            }

            $this->step = 4;
            $this->saveRegistrationState();
            return;
        }

        if ($intent === 'subscribe') {
            $baseCurrency = \App\Models\Central\BillingSetting::get('base_currency', 'NGN');
            if (!$plan->getPriceFor($baseCurrency, 'monthly')) {
                $this->error = 'This plan is not available for direct subscription yet. Please contact us.';
                return;
            }

            $tenant->update(['plan_id' => $planId]);

            $billing = app(\App\Services\BillingService::class);
            $pricing = $billing->getPriceForTenant($plan, $tenant, 'monthly');
            $gateway = $pricing['gateway'];

            $result = $gateway === 'paystack'
                ? $billing->initializePaystackPayment($tenant, $plan, 'monthly', $pricing)
                : $billing->initializeFlutterwavePayment($tenant, $plan, 'monthly', $pricing);

            if (!$result['success']) {
                $this->error = $result['message'] ?? 'Could not start payment. Please try again.';
                return;
            }

            $this->saveRegistrationState();
            $this->redirect($result['authorization_url']);
        }
    }

    // ── Step 4 Onboarding (skippable) ────────────────────────
    public function submitOnboarding(): void { $this->finishRegistration(); }
    public function skip(): void             { $this->finishRegistration(); }

    private function finishRegistration(): void
    {
        $user = User::withoutGlobalScope('tenant')
            ->where('email', $this->email)
            ->where('tenant_id', $this->tenant_id)
            ->first();

        if ($user) {
            auth('web')->login($user);
            $user->update(['last_login_at' => now()]);
        }

        session()->forget('registration.wizard');
        $this->redirect(route('tenant.onboarding'), navigate: true);
    }

    public function togglePassword(): void        { $this->showPassword = !$this->showPassword; }
    public function togglePasswordConfirm(): void { $this->showPasswordConfirm = !$this->showPasswordConfirm; }

    public function render()
    {
        $plans = Plan::where('is_active', true)->orderByDesc('is_featured')->get();

        // Auto-select if only one plan exists
        if ($plans->count() === 1 && !$this->selected_plan_id) {
            $this->selected_plan_id = $plans->first()->id;
        }

        $pricingData = [];

        if ($this->step === 3) {
            $billing      = app(\App\Services\BillingService::class);
            $baseCurrency = \App\Models\Central\BillingSetting::get('base_currency', 'NGN');
            $currency     = $this->getCurrency();

            foreach ($plans as $plan) {
                if ($plan->is_contact_only) continue;

                $monthlyPrice = $plan->getPriceFor($baseCurrency, 'monthly');
                if (!$monthlyPrice) continue;

                $amount = $currency !== $baseCurrency
                    ? $billing->convertAmount((float) $monthlyPrice->amount, $baseCurrency, $currency)
                    : (float) $monthlyPrice->amount;

                $pricingData[$plan->id] = ['amount' => $amount, 'currency' => $currency];
            }
        }

        $industryProfiles = \App\Models\Central\IndustryProfile::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('livewire.auth.register', [
            'plans'             => $plans,
            'pricingData'       => $pricingData,
            'countries'         => CurrencyHelper::countries(),
            'currency'          => $this->getCurrency(),
            'industryProfiles'  => $industryProfiles,
        ]);
    }
}