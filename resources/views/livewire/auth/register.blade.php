<div class="krd-auth-split">

    {{-- Left Panel — hidden on mobile --}}
    <div class="krd-auth-panel">

        {{-- Top --}}
        <div>
            <x-ui.logo color="light" />
        </div>

        {{-- Middle --}}
        <div style="max-width: 360px;">

            {{-- Step Indicators --}}
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 32px; flex-wrap: wrap;">
                @foreach([1 => 'Account', 2 => 'Verify', 3 => 'Plan', 4 => 'Setup'] as $s => $label)
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="
                        width: 24px; height: 24px; border-radius: 50%;
                        display: flex; align-items: center; justify-content: center;
                        font-size: 11px; font-weight: 600;
                        background: {{ $step >= $s ? '#7C3AED' : 'rgba(255,255,255,0.1)' }};
                        color: {{ $step >= $s ? '#fff' : 'rgba(255,255,255,0.3)' }};
                        transition: all 300ms ease;
                        flex-shrink: 0;
                    ">
                        @if($step > $s) ✓ @else {{ $s }} @endif
                    </div>
                    <span style="font-size: 12px; color: {{ $step >= $s ? '#FAFAF9' : 'rgba(255,255,255,0.3)' }}; transition: all 300ms; white-space: nowrap;">
                        {{ $label }}
                    </span>
                    @if($s < 4)
                    <div style="width: 16px; height: 1px; background: {{ $step > $s ? '#7C3AED' : 'rgba(255,255,255,0.1)' }}; flex-shrink: 0;"></div>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- Step Headlines --}}
            @if($step === 1)
            <h1 style="font-size: 36px; font-weight: 700; color: #FAFAF9; line-height: 1.1; letter-spacing: -0.02em; margin-bottom: 14px;">
                Start running<br>
                <span style="color: #F59E0B;">smarter events.</span>
            </h1>
            <p style="font-size: 14px; color: #78716C; line-height: 1.7;">
                Join event companies already using Koordli to manage operations, clients, and guests.
            </p>
            @elseif($step === 2)
            <h1 style="font-size: 32px; font-weight: 700; color: #FAFAF9; line-height: 1.1; letter-spacing: -0.02em; margin-bottom: 14px;">
                Check your<br>
                <span style="color: #F59E0B;">inbox.</span>
            </h1>
            <p style="font-size: 14px; color: #78716C; line-height: 1.7;">
                We sent a 6-digit code to<br>
                <span style="color: #FAFAF9; font-weight: 500;">{{ $email }}</span>
            </p>
            @elseif($step === 3)
            <h1 style="font-size: 32px; font-weight: 700; color: #FAFAF9; line-height: 1.1; letter-spacing: -0.02em; margin-bottom: 14px;">
                Choose your<br>
                <span style="color: #F59E0B;">plan.</span>
            </h1>
            <p style="font-size: 14px; color: #78716C; line-height: 1.7;">
                Start with a free trial — no card required. Upgrade anytime.
            </p>
            @elseif($step === 4)
            <h1 style="font-size: 32px; font-weight: 700; color: #FAFAF9; line-height: 1.1; letter-spacing: -0.02em; margin-bottom: 14px;">
                Almost<br>
                <span style="color: #F59E0B;">there.</span>
            </h1>
            <p style="font-size: 14px; color: #78716C; line-height: 1.7;">
                Help us personalise your experience. You can skip this anytime.
            </p>
            @endif

        </div>

        {{-- Bottom --}}
        <div>
            <p style="font-size: 11px; color: #44403C; letter-spacing: 0.05em;">
                © {{ date('Y') }} Koordli. All rights reserved.
            </p>
        </div>

    </div>

    {{-- Right Panel --}}
    <div class="krd-auth-form" style="align-items: flex-start;">
        <div style="width: 100%; max-width: 420px; padding: 48px 0;">

            {{-- Mobile logo --}}
            <div class="krd-mobile-only" style="margin-bottom: 20px;">
                <x-ui.logo color="dark" />
            </div>

            {{-- Mobile step indicator --}}
            <div class="krd-mobile-only" style="margin-bottom: 24px;">
                <div style="display: flex; align-items: center; justify-content: center; gap: 6px; margin-bottom: 10px;">
                    @foreach([1, 2, 3, 4] as $s)
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <div style="
                            width: 28px; height: 28px; border-radius: 50%;
                            display: flex; align-items: center; justify-content: center;
                            font-size: 11px; font-weight: 600;
                            background: {{ $step >= $s ? '#7C3AED' : '#E7E5E4' }};
                            color: {{ $step >= $s ? '#fff' : '#A8A29E' }};
                            flex-shrink: 0;
                        ">
                            @if($step > $s) ✓ @else {{ $s }} @endif
                        </div>
                        @if($s < 4)
                        <div style="width: 20px; height: 2px; background: {{ $step > $s ? '#7C3AED' : '#E7E5E4' }};"></div>
                        @endif
                    </div>
                    @endforeach
                </div>
                <div style="text-align: center; font-size: 12px; color: #78716C;">
                    Step {{ $step }} of 4 —
                    @if($step === 1) Create Account
                    @elseif($step === 2) Verify Email
                    @elseif($step === 3) Choose Plan
                    @else Setup
                    @endif
                </div>
            </div>

            {{-- Error --}}
            @if($error)
            <div style="background:#FEE2E2;border:1px solid #FECACA;border-radius:6px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#DC2626;">
                {{ $error }}
            </div>
            @endif

            {{-- Success --}}
            @if($success)
            <div style="background:#D1FAE5;border:1px solid #6EE7B7;border-radius:6px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#065F46;">
                {{ $success }}
            </div>
            @endif

            {{-- ══ STEP 1 ══ --}}
            @if($step === 1)

            <div style="margin-bottom: 28px;">
                <h2 style="font-size: 22px; font-weight: 600; color: #1C1917; letter-spacing: -0.01em; margin-bottom: 6px;">Create your account</h2>
                <p style="font-size: 13px; color: #78716C;">Get started with a 30-day free trial.</p>
            </div>

            {{-- Honeypot --}}
            <div style="display: none;" aria-hidden="true">
                <input wire:model="honeypot" type="text" name="website" tabindex="-1" autocomplete="off" />
            </div>

            <div class="krd-input-group">
                <label class="krd-label-text">Company Name</label>
                <input wire:model="company_name" type="text" class="krd-input @error('company_name') krd-input-error @enderror" placeholder="e.g. Stellar Events" autocomplete="organization" />
                @error('company_name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            {{-- Country + Currency --}}
            <div
                x-data="{
                    open: false,
                    selectedCountry: '{{ $country }}',
                    selectedName: '{{ $country ? ($countries[$country] ?? "Select country") : "Select country" }}',
                    currencyMap: {
                        'NG': 'NGN — Nigerian Naira (₦)',
                        'GH': 'GHS — Ghanaian Cedi (₵)',
                        'GB': 'GBP — British Pound (£)',
                        'US': 'USD — US Dollar ($)',
                        'CA': 'USD — US Dollar ($)',
                        'AU': 'USD — US Dollar ($)',
                        'DE': 'EUR — Euro (€)',
                        'FR': 'EUR — Euro (€)',
                        'IT': 'EUR — Euro (€)',
                        'ES': 'EUR — Euro (€)',
                        'NL': 'EUR — Euro (€)',
                        'BE': 'EUR — Euro (€)',
                        'PT': 'EUR — Euro (€)',
                        'AT': 'EUR — Euro (€)',
                        'FI': 'EUR — Euro (€)',
                        'IE': 'EUR — Euro (€)',
                        'KE': 'KES — Kenyan Shilling (KSh)',
                        'ZA': 'ZAR — South African Rand (R)',
                        'AE': 'USD — US Dollar ($)',
                        'SA': 'USD — US Dollar ($)',
                        'IN': 'USD — US Dollar ($)',
                        'SG': 'USD — US Dollar ($)',
                    },
                    get currencyLabel() {
                        return this.currencyMap[this.selectedCountry] ?? 'USD — US Dollar ($)';
                    },
                    pick(code, name) {
                        this.selectedCountry = code;
                        this.selectedName    = name;
                        this.open            = false;
                        $wire.set('country', code);
                    }
                }"
                x-on:click.outside="open = false"
            >
                {{-- Country Dropdown --}}
                <div class="krd-input-group">
                    <label class="krd-label-text">Country <span style="color:#EF4444;">*</span></label>
                    <div class="krd-dropdown">
                        <button
                            type="button"
                            class="krd-dropdown-trigger"
                            x-bind:class="{ open: open }"
                            x-on:click="open = !open"
                        >
                            <span x-text="selectedName" :style="selectedName === 'Select country' ? 'color:#A8A29E' : ''"></span>
                            <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <template x-if="open">
                            <div class="krd-dropdown-menu">
                                @foreach($countries as $code => $name)
                                <div
                                    class="krd-dropdown-option"
                                    :class="selectedCountry === '{{ $code }}' ? 'selected' : ''"
                                    x-on:click="pick('{{ $code }}', '{{ $name }}')"
                                >
                                    {{ $name }}
                                </div>
                                @endforeach
                            </div>
                        </template>
                    </div>
                    @error('country') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                {{-- Currency — instant Alpine update --}}
                <div class="krd-input-group">
                    <label class="krd-label-text">Billing Currency</label>
                    <div style="padding:9px 12px;background:#F5F5F4;border:1px solid #E7E5E4;border-radius:6px;font-size:13px;color:#57534E;display:flex;align-items:center;justify-content:space-between;">
                        <span x-text="currencyLabel"></span>
                        <span style="font-size:11px;color:#A8A29E;">Auto-detected</span>
                    </div>
                    <span class="krd-input-hint">Automatically set from your country. You can change this later in settings.</span>
                </div>
            </div>

            <div class="krd-input-group">
                <label class="krd-label-text">Your Full Name</label>
                <input wire:model="name" type="text" class="krd-input @error('name') krd-input-error @enderror" placeholder="e.g. Amara Johnson" autocomplete="name" />
                @error('name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="krd-input-group">
                <label class="krd-label-text">Work Email</label>
                <input wire:model="email" type="email" class="krd-input @error('email') krd-input-error @enderror" placeholder="you@company.com" autocomplete="email" />
                @error('email') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="krd-input-group">
                <label class="krd-label-text">Password</label>
                <div style="position: relative;">
                    <input wire:model="password" type="{{ $showPassword ? 'text' : 'password' }}" class="krd-input @error('password') krd-input-error @enderror" placeholder="Min 8 characters" autocomplete="new-password" style="padding-right: 44px;" />
                    <button type="button" wire:click="togglePassword" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#A8A29E;display:flex;align-items:center;padding:0;">
                        @if($showPassword)
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        @else
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        @endif
                    </button>
                </div>
                @error('password') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                <span class="krd-input-hint">Must contain uppercase, lowercase and a number.</span>
            </div>

            <div class="krd-input-group">
                <label class="krd-label-text">Confirm Password</label>
                <div style="position: relative;">
                    <input wire:model="password_confirmation" type="{{ $showPasswordConfirm ? 'text' : 'password' }}" class="krd-input" placeholder="Repeat your password" autocomplete="new-password" style="padding-right: 44px;" />
                    <button type="button" wire:click="togglePasswordConfirm" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#A8A29E;display:flex;align-items:center;padding:0;">
                        @if($showPasswordConfirm)
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        @else
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        @endif
                    </button>
                </div>
            </div>

            {{-- Terms --}}
            <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:20px;">
                <input wire:model="agreed_to_terms" type="checkbox" id="terms" style="width:15px;height:15px;margin-top:2px;accent-color:#7C3AED;flex-shrink:0;cursor:pointer;" />
                <label for="terms" style="font-size:12px;color:#57534E;line-height:1.6;cursor:pointer;">
                    By registering, you agree to our
                    <a href="#" style="color:#7C3AED;text-decoration:none;font-weight:500;">Terms & Conditions</a>
                    and
                    <a href="#" style="color:#7C3AED;text-decoration:none;font-weight:500;">Privacy Policy</a>.
                </label>
            </div>
            @error('agreed_to_terms') <div style="font-size:11px;color:#EF4444;margin-bottom:12px;">{{ $message }}</div> @enderror

            <button wire:click="submitStep1" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-lg" style="width:100%;">
                <span wire:loading.remove wire:target="submitStep1">Create Account</span>
                <span wire:loading wire:target="submitStep1">Creating account...</span>
            </button>

            <div style="margin-top:20px;text-align:center;font-size:12px;color:#A8A29E;">
                Already have an account?
                <a href="{{ route('tenant.login') }}" wire:navigate style="color:#7C3AED;text-decoration:none;font-weight:500;">Sign in</a>
            </div>

            @endif

            {{-- ══ STEP 2 ══ --}}
            @if($step === 2)

            <div style="margin-bottom: 28px;">
                <h2 style="font-size: 22px; font-weight: 600; color: #1C1917; letter-spacing: -0.01em; margin-bottom: 6px;">Verify your email</h2>
                <p style="font-size: 13px; color: #78716C; line-height: 1.6;">
                    Enter the 6-digit code we sent to<br>
                    <strong style="color: #1C1917;">{{ $email }}</strong>
                </p>
            </div>

            {{-- 6 Digit Inputs — fixed Alpine/Livewire sync --}}
            <div
                style="display:flex;gap:8px;margin-bottom:24px;"
                x-data="{
                    digits: @entangle('code_digits'),
                    focusNext(index) {
                        if (index < 5) {
                            $nextTick(() => {
                                const inputs = $el.querySelectorAll('input');
                                if (inputs[index + 1]) inputs[index + 1].focus();
                            });
                        }
                    },
                    focusPrev(index) {
                        if (index > 0) {
                            $nextTick(() => {
                                const inputs = $el.querySelectorAll('input');
                                if (inputs[index - 1]) inputs[index - 1].focus();
                            });
                        }
                    },
                    handlePaste(e) {
                        const paste = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                        paste.split('').forEach((char, idx) => { this.digits[idx] = char; });
                        $nextTick(() => {
                            const inputs = $el.querySelectorAll('input');
                            const last = Math.min(paste.length, 5);
                            if (inputs[last]) inputs[last].focus();
                        });
                    }
                }"
                x-on:paste.prevent="handlePaste($event)"
            >
                @foreach(range(0, 5) as $i)
                <input
                    type="text"
                    maxlength="1"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    class="krd-input"
                    style="width:100%;max-width:56px;height:56px;text-align:center;font-size:22px;font-weight:600;padding:0;flex-shrink:1;"
                    x-model="digits[{{ $i }}]"
                    x-on:input="
                        digits[{{ $i }}] = $el.value.replace(/[^0-9]/g, '').slice(-1);
                        $el.value = digits[{{ $i }}];
                        if (digits[{{ $i }}]) focusNext({{ $i }});
                    "
                    x-on:keydown.backspace="
                        if (!digits[{{ $i }}]) {
                            digits[{{ $i > 0 ? $i - 1 : 0 }}] = '';
                            focusPrev({{ $i }});
                        } else {
                            digits[{{ $i }}] = '';
                            $el.value = '';
                        }
                    "
                />
                @endforeach
            </div>

            <button wire:click="verifyCode" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-lg" style="width:100%;margin-bottom:16px;">
                <span wire:loading.remove wire:target="verifyCode">Verify Email</span>
                <span wire:loading wire:target="verifyCode">Verifying...</span>
            </button>

            {{-- Resend with real countdown --}}
            <div
                style="text-align:center;font-size:12px;color:#A8A29E;"
                x-data="{
                    countdown: {{ $resendCooldown }},
                    init() {
                        if (this.countdown > 0) {
                            const timer = setInterval(() => {
                                this.countdown--;
                                if (this.countdown <= 0) clearInterval(timer);
                            }, 1000);
                        }
                    }
                }"
            >
                Didn't receive the code?
                <button
                    wire:click="resendCode"
                    type="button"
                    x-bind:disabled="countdown > 0"
                    x-on:click="
                        if (countdown <= 0) {
                            countdown = 60;
                            const timer = setInterval(() => {
                                countdown--;
                                if (countdown <= 0) clearInterval(timer);
                            }, 1000);
                        }
                    "
                    style="background:none;border:none;font-size:12px;font-weight:500;font-family:inherit;padding:0;"
                    x-bind:style="countdown > 0 ? 'color:#A8A29E;cursor:not-allowed;' : 'color:#7C3AED;cursor:pointer;'"
                >
                    <span x-show="countdown <= 0">Resend code</span>
                    <span x-show="countdown > 0">Resend in <span x-text="countdown"></span>s</span>
                </button>
            </div>

            @endif

            {{-- ══ STEP 3 ══ --}}
            @if($step === 3)

            <div style="margin-bottom: 24px;">
                <h2 style="font-size: 22px; font-weight: 600; color: #1C1917; letter-spacing: -0.01em; margin-bottom: 6px;">Choose a plan</h2>
                <p style="font-size: 13px; color: #78716C;">Start free. Upgrade when you're ready.</p>
            </div>

            <div style="display:flex;flex-direction:column;gap:12px;">
                @foreach($plans as $plan)
                <div
                    wire:click="selectPlan({{ $plan->id }})"
                    style="
                        border: 2px solid {{ $selected_plan_id === $plan->id ? '#7C3AED' : ($plan->is_featured ? '#DDD6FE' : '#E7E5E4') }};
                        border-radius: 8px;
                        padding: 16px 18px;
                        cursor: pointer;
                        transition: all 150ms ease;
                        background: {{ $selected_plan_id === $plan->id ? '#F5F3FF' : ($plan->is_featured ? '#FAFAF9' : '#FFFFFF') }};
                        position: relative;
                    "
                >
                    @if($plan->is_featured)
                    <div style="position:absolute;top:-10px;right:16px;background:#7C3AED;color:#fff;font-size:10px;font-weight:600;padding:3px 10px;border-radius:20px;letter-spacing:0.05em;">
                        ⭐ RECOMMENDED
                    </div>
                    @endif

                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;flex-wrap:wrap;gap:8px;">
                        <div style="font-size:14px;font-weight:600;color:#1C1917;">{{ $plan->name }}</div>
                        <div style="font-size:13px;font-weight:600;color:#7C3AED;">
                            @if($plan->trial_days > 0)
                                Free for {{ $plan->trial_days }} days
                            @else
                                Contact us
                            @endif
                        </div>
                    </div>

                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;">
                        @foreach(($plan->features ?? []) as $key => $value)
                            @if($value === 'true' || (is_numeric($value) && $value > 0) || $value === 'unlimited')
                            <span style="font-size:10px;background:#F5F5F4;color:#57534E;padding:2px 8px;border-radius:4px;font-weight:500;">
                                @if($value === 'true') ✓ {{ str_replace('_', ' ', ucfirst($key)) }}
                                @elseif($value === 'unlimited') ∞ {{ str_replace('_', ' ', ucfirst($key)) }}
                                @else {{ $value }} {{ str_replace('_', ' ', ucfirst($key)) }}
                                @endif
                            </span>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            @endif

            {{-- ══ STEP 4 ══ --}}
            @if($step === 4)

            <div style="margin-bottom: 24px;">
                <h2 style="font-size: 22px; font-weight: 600; color: #1C1917; letter-spacing: -0.01em; margin-bottom: 6px;">Tell us about yourself</h2>
                <p style="font-size: 13px; color: #78716C;">Help us personalise your experience. Takes 30 seconds.</p>
            </div>

            <div class="krd-input-group">
                <label class="krd-label-text">How did you hear about Koordli?</label>
                <select wire:model="heard_from" class="krd-input" style="cursor:pointer;">
                    <option value="">Select an option</option>
                    <option value="google">Google Search</option>
                    <option value="instagram">Instagram</option>
                    <option value="twitter">Twitter / X</option>
                    <option value="linkedin">LinkedIn</option>
                    <option value="referral">Referred by someone</option>
                    <option value="facebook">Facebook</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="krd-input-group">
                <label class="krd-label-text">How many team members do you have?</label>
                <select wire:model="team_size" class="krd-input" style="cursor:pointer;">
                    <option value="">Select an option</option>
                    <option value="just-me">Just me</option>
                    <option value="2-5">2 – 5</option>
                    <option value="6-10">6 – 10</option>
                    <option value="11-20">11 – 20</option>
                    <option value="20+">20+</option>
                </select>
            </div>

            <div class="krd-input-group" style="margin-bottom: 24px;">
                <label class="krd-label-text" style="margin-bottom: 8px;">What types of events do you plan?</label>
                <div style="display:flex;flex-wrap:wrap;gap:10px;">
                    @foreach(['Weddings', 'Birthdays', 'Corporate Events', 'Concerts', 'Private Events', 'Conferences', 'Social Events'] as $type)
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                        <input wire:model="event_types" type="checkbox" value="{{ $type }}" style="accent-color:#7C3AED;width:14px;height:14px;" />
                        <span style="font-size:12px;color:#57534E;">{{ $type }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div style="display:flex;gap:10px;">
                <button wire:click="submitOnboarding" wire:loading.attr="disabled" class="krd-btn krd-btn-primary" style="flex:1;">
                    <span wire:loading.remove wire:target="submitOnboarding">Continue to Dashboard</span>
                    <span wire:loading wire:target="submitOnboarding">Setting up...</span>
                </button>
                <button wire:click="skip" type="button" class="krd-btn krd-btn-ghost">
                    Skip
                </button>
            </div>

            @endif

        </div>
    </div>

</div>