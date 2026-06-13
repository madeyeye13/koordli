<div class="krd-auth-split">

    <div class="krd-auth-panel">
        <div><x-ui.logo color="light" /></div>
        <div style="max-width: 360px;">
            <h1 style="font-size: 40px; font-weight: 700; color: #FAFAF9; line-height: 1.1; letter-spacing: -0.02em; margin-bottom: 16px;">
                Your work,<br>
                <span style="color: #F59E0B;">all in one place.</span>
            </h1>
            <p style="font-size: 14px; color: #78716C; line-height: 1.8; margin-bottom: 40px;">
                Log in to view your assigned events, payment status, and more.
            </p>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach([
                    ['icon' => '📅', 'text' => 'Assigned events & details'],
                    ['icon' => '💰', 'text' => 'Payment status per event'],
                    ['icon' => '📋', 'text' => 'Runsheet & timeline'],
                    ['icon' => '👤', 'text' => 'Your vendor profile'],
                ] as $feature)
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 15px; line-height: 1;">{{ $feature['icon'] }}</span>
                    <span style="font-size: 13px; color: #A8A29E;">{{ $feature['text'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
        <p style="font-size: 11px; color: #44403C; letter-spacing: 0.05em;">© {{ date('Y') }} Koordli. All rights reserved.</p>
    </div>

    <div class="krd-auth-form">
        <div style="width: 100%; max-width: 380px; padding: 32px 0;">

            <div class="krd-mobile-only" style="margin-bottom: 28px;">
                <x-ui.logo color="dark" />
            </div>

            <div style="margin-bottom: 32px;">
                <h2 style="font-size: 22px; font-weight: 600; color: #1C1917; letter-spacing: -0.01em; margin-bottom: 6px;">Vendor Portal</h2>
                <p style="font-size: 13px; color: #78716C;">Sign in with the credentials sent to your email.</p>
            </div>

            @if($error)
            <div style="background-color:#FEE2E2;border:1px solid #FECACA;border-radius:6px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#DC2626;">
                {{ $error }}
            </div>
            @endif

            <div class="krd-input-group">
                <label class="krd-label-text">Email address</label>
                <input wire:model="email" type="email" class="krd-input @error('email') krd-input-error @enderror"
                    placeholder="you@business.com" autocomplete="email" wire:keydown.enter="login" />
                @error('email') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="krd-input-group">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
                    <label class="krd-label-text" style="margin-bottom:0;">Password</label>
                    <a href="#" style="font-size:12px;color:#7C3AED;text-decoration:none;">Forgot password?</a>
                </div>
                <div style="position:relative;">
                    <input wire:model="password" type="{{ $showPassword ? 'text' : 'password' }}"
                        class="krd-input @error('password') krd-input-error @enderror"
                        placeholder="••••••••" autocomplete="current-password"
                        wire:keydown.enter="login" style="padding-right:44px;" />
                    <button type="button" wire:click="togglePassword"
                        style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#A8A29E;display:flex;align-items:center;padding:0;">
                        @if($showPassword)
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        @else
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        @endif
                    </button>
                </div>
                @error('password') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;margin-top:4px;">
                <input wire:model="remember" type="checkbox" id="remember"
                    style="width:15px;height:15px;cursor:pointer;accent-color:#7C3AED;" />
                <label for="remember" style="font-size:13px;color:#57534E;cursor:pointer;">Remember me for 30 days</label>
            </div>

            <button wire:click="login" wire:loading.attr="disabled"
                class="krd-btn krd-btn-primary krd-btn-lg" style="width:100%;">
                <span wire:loading.remove wire:target="login">Sign in</span>
                <span wire:loading wire:target="login">Signing in...</span>
            </button>

        </div>
    </div>
</div>