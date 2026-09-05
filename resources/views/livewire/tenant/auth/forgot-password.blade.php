<div class="krd-auth-split">
    <div class="krd-auth-panel">
        <x-ui.logo color="light" />
        <div style="max-width:360px;">
            <h1 style="font-size:40px;font-weight:700;color:#FAFAF9;line-height:1.1;margin-bottom:16px;">Back to your<br><span style="color:#F59E0B;">workspace.</span></h1>
            <p style="font-size:14px;color:#78716C;line-height:1.8;">We will send a secure password reset link to your email address.</p>
        </div>
        <p style="font-size:11px;color:#44403C;letter-spacing:.05em;">© {{ date('Y') }} Koordli. All rights reserved.</p>
    </div>
    <div class="krd-auth-form">
        <div style="width:100%;max-width:380px;padding:32px 0;">
            <div class="krd-mobile-only" style="margin-bottom:28px;"><x-ui.logo color="dark" /></div>
            @if($sent)
            <div style="margin-bottom:28px;"><h2 style="font-size:22px;font-weight:600;color:#1C1917;margin-bottom:8px;">Check your inbox</h2><p style="font-size:13px;color:#78716C;line-height:1.7;">If an active Koordli account uses that email, we have sent a password reset link. The link expires in 60 minutes.</p></div>
            @else
            <div style="margin-bottom:28px;"><h2 style="font-size:22px;font-weight:600;color:#1C1917;margin-bottom:8px;">Forgot your password?</h2><p style="font-size:13px;color:#78716C;line-height:1.7;">Enter your account email and we will send a secure reset link.</p></div>
            <div class="krd-input-group"><label class="krd-label-text">Email address</label><input wire:model="email" type="email" class="krd-input @error('email') krd-input-error @enderror" placeholder="you@company.com" autocomplete="email" wire:keydown.enter="sendResetLink" />@error('email')<span class="krd-input-error-msg">{{ $message }}</span>@enderror</div>
            <button wire:click="sendResetLink" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-lg" style="width:100%;"><span wire:loading.remove wire:target="sendResetLink">Email reset link</span><span wire:loading wire:target="sendResetLink">Sending...</span></button>
            @endif
            <div style="margin-top:28px;text-align:center;font-size:12px;color:#A8A29E;"><a href="{{ route('tenant.login') }}" wire:navigate style="color:#7C3AED;text-decoration:none;font-weight:500;">Back to sign in</a></div>
        </div>
    </div>
</div>
