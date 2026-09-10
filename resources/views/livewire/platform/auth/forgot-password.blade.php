<div class="krd-auth-split">

    <div class="krd-auth-panel">
        <div>
            <x-ui.logo color="light" tagline="Platform Administration" />
        </div>
        <div style="max-width: 360px;">
            <h1 style="font-size: 40px; font-weight: 700; color: #FAFAF9; line-height: 1.1; letter-spacing: -0.02em; margin-bottom: 16px;">
                Reset your<br><span style="color: #7C3AED;">Password</span>
            </h1>
            <p style="font-size: 14px; color: #78716C; line-height: 1.8;">
                We'll send a verification code to confirm it's really you before setting a new password.
            </p>
        </div>
        <div>
            <p style="font-size: 11px; color: #44403C; letter-spacing: 0.05em;">© {{ date('Y') }} Koordli. All rights reserved.</p>
        </div>
    </div>

    <div class="krd-auth-form">
        <div style="width: 100%; max-width: 380px; padding: 32px 0;">

            <div class="krd-mobile-only" style="margin-bottom: 28px;">
                <x-ui.logo color="dark" />
            </div>

            <div style="margin-bottom: 32px;">
                <h2 style="font-size: 22px; font-weight: 600; color: #1C1917; letter-spacing: -0.01em; margin-bottom: 6px;">Reset Password</h2>
                <p style="font-size: 13px; color: #78716C;">Restricted to platform administrators only.</p>
            </div>

            @if($error)
            <div style="background-color: #FEE2E2; border: 1px solid #FECACA; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px; font-size: 13px; color: #DC2626;">{{ $error }}</div>
            @endif
            @if($success)
            <div style="background-color: #D1FAE5; border: 1px solid #A7F3D0; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px; font-size: 13px; color: #059669;">{{ $success }}</div>
            @endif

            @if($step === 1)
            <div class="krd-input-group">
                <label class="krd-label-text">Email address</label>
                <input wire:model="email" type="email" class="krd-input @error('email') krd-input-error @enderror" placeholder="you@koordli.com" autocomplete="email" wire:keydown.enter="requestCode">
                @error('email')<span class="krd-input-error-msg">{{ $message }}</span>@enderror
            </div>

            <button wire:click="requestCode" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-lg" style="width:100%;margin-top:8px;">
                <span wire:loading.remove wire:target="requestCode">Send Code</span>
                <span wire:loading wire:target="requestCode">Sending...</span>
            </button>

            @else
            <div x-data="{ cooldown: {{ $resendCooldown }}, showPw: false, showPwConfirm: false, init() { const t = setInterval(() => { if (this.cooldown > 0) this.cooldown--; else clearInterval(t); }, 1000); } }">

                <div class="krd-input-group">
                    <label class="krd-label-text">Verification Code</label>
                    <input wire:model="code" type="text" inputmode="numeric" maxlength="6" class="krd-input @error('code') krd-input-error @enderror" placeholder="000000" style="text-align:center;letter-spacing:8px;font-size:18px;">
                    @error('code')<span class="krd-input-error-msg">{{ $message }}</span>@enderror
                </div>

                <div class="krd-input-group">
                    <label class="krd-label-text">New Password</label>
                    <div style="position:relative;">
                        <input wire:model="newPassword" :type="showPw ? 'text' : 'password'" class="krd-input @error('newPassword') krd-input-error @enderror" placeholder="••••••••" style="padding-right:44px;">
                        <button type="button" x-on:click="showPw = !showPw" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#A8A29E;display:flex;align-items:center;padding:0;">
                            <svg x-show="!showPw" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="showPw" x-cloak xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    @error('newPassword')<span class="krd-input-error-msg">{{ $message }}</span>@enderror
                    <p style="font-size:11px;color:#A8A29E;margin-top:6px;">Minimum 8 characters, with uppercase, lowercase, a number, and a symbol.</p>
                </div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Confirm New Password</label>
                    <div style="position:relative;">
                        <input wire:model="newPasswordConfirm" :type="showPwConfirm ? 'text' : 'password'" class="krd-input" placeholder="••••••••" style="padding-right:44px;">
                        <button type="button" x-on:click="showPwConfirm = !showPwConfirm" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#A8A29E;display:flex;align-items:center;padding:0;">
                            <svg x-show="!showPwConfirm" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="showPwConfirm" x-cloak xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>

                <button wire:click="verifyAndReset" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-lg" style="width:100%;margin-top:8px;margin-bottom:14px;">
                    <span wire:loading.remove wire:target="verifyAndReset">Reset Password</span>
                    <span wire:loading wire:target="verifyAndReset">Verifying...</span>
                </button>

                <div style="text-align:center;">
                    <button type="button" wire:click="resendCode" x-bind:disabled="cooldown > 0"
                        x-bind:style="cooldown > 0 ? 'background:none;border:none;font-size:12px;color:#D6D3D1;cursor:not-allowed;' : 'background:none;border:none;font-size:12px;color:#7C3AED;cursor:pointer;'">
                        <span x-show="cooldown === 0">Resend code</span>
                        <span x-show="cooldown > 0" x-text="'Resend in ' + cooldown + 's'"></span>
                    </button>
                </div>
            </div>
            @endif

            <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #E7E5E4; text-align: center;">
                <a href="{{ route('platform.login') }}" wire:navigate style="font-size:12px;color:#78716C;text-decoration:none;">← Back to sign in</a>
            </div>

        </div>
    </div>

</div>