<div class="krd-auth-split">

    {{-- Left Panel — hidden on mobile --}}
    <div class="krd-auth-panel">

        {{-- Top --}}
        <div>
            <x-ui.logo color="light" tagline="Platform Administration" />
        </div>

        {{-- Middle --}}
        <div style="max-width: 360px;">
            <h1 style="font-size: 40px; font-weight: 700; color: #FAFAF9; line-height: 1.1; letter-spacing: -0.02em; margin-bottom: 16px;">
                Platform<br>
                <span style="color: #7C3AED;">Control Center</span>
            </h1>
            <p style="font-size: 14px; color: #78716C; line-height: 1.8;">
                Manage tenants, subscriptions, plans, and platform settings from one central dashboard.
            </p>
        </div>

        {{-- Bottom --}}
        <div>
            <p style="font-size: 11px; color: #44403C; letter-spacing: 0.05em;">
                © {{ date('Y') }} Koordli. All rights reserved.
            </p>
        </div>

    </div>

    {{-- Right Panel --}}
    <div class="krd-auth-form">
        <div style="width: 100%; max-width: 380px; padding: 32px 0;">

            {{-- Mobile logo --}}
            {{-- Mobile logo --}}
            <div class="krd-mobile-only" style="margin-bottom: 28px;">
                <x-ui.logo color="dark" />
            </div>

            <div style="margin-bottom: 32px;">
                <h2 style="font-size: 22px; font-weight: 600; color: #1C1917; letter-spacing: -0.01em; margin-bottom: 6px;">
                    Platform sign in
                </h2>
                <p style="font-size: 13px; color: #78716C;">
                    Restricted to platform administrators only.
                </p>
            </div>

            {{-- Error --}}
            @if($error)
            <div style="background-color: #FEE2E2; border: 1px solid #FECACA; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px; font-size: 13px; color: #DC2626;">
                {{ $error }}
            </div>
            @endif

            {{-- Email --}}
            <div class="krd-input-group">
                <label class="krd-label-text">Email address</label>
                <input
                    wire:model="email"
                    type="email"
                    class="krd-input @error('email') krd-input-error @enderror"
                    placeholder="admin@koordli.com"
                    autocomplete="email"
                    wire:keydown.enter="login"
                />
                @error('email')
                <span class="krd-input-error-msg">{{ $message }}</span>
                @enderror
            </div>

            {{-- Password --}}
            <div class="krd-input-group">
                <label class="krd-label-text">Password</label>
                <div style="position: relative;">
                    <input
                        wire:model="password"
                        type="{{ $showPassword ? 'text' : 'password' }}"
                        class="krd-input @error('password') krd-input-error @enderror"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        wire:keydown.enter="login"
                        style="padding-right: 44px;"
                    />
                    <button
                        type="button"
                        wire:click="togglePassword"
                        style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #A8A29E; display: flex; align-items: center; padding: 0;"
                    >
                        @if($showPassword)
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/>
                            <path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                        @else
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        @endif
                    </button>
                </div>
                @error('password')
                <span class="krd-input-error-msg">{{ $message }}</span>
                @enderror
            </div>

            {{-- Submit --}}
            <button
                wire:click="login"
                wire:loading.attr="disabled"
                class="krd-btn krd-btn-primary krd-btn-lg"
                style="width: 100%; margin-top: 8px;"
            >
                <span wire:loading.remove wire:target="login">Sign in</span>
                <span wire:loading wire:target="login">Signing in...</span>
            </button>

            {{-- Security note --}}
            <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #E7E5E4; text-align: center;">
                <p style="font-size: 12px; color: #A8A29E;">
                    🔒 This area is restricted to Koordli administrators.
                </p>
            </div>

        </div>
    </div>

</div>