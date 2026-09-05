<div class="krd-auth-split">
    <div class="krd-auth-panel"><x-ui.logo color="light" /><div style="max-width:360px;"><h1 style="font-size:40px;font-weight:700;color:#FAFAF9;line-height:1.1;margin-bottom:16px;">Choose a new<br><span style="color:#F59E0B;">password.</span></h1><p style="font-size:14px;color:#78716C;line-height:1.8;">Keep your Koordli workspace secure with a strong password.</p></div><p style="font-size:11px;color:#44403C;letter-spacing:.05em;">© {{ date('Y') }} Koordli. All rights reserved.</p></div>
    <div class="krd-auth-form"><div style="width:100%;max-width:380px;padding:32px 0;"><div class="krd-mobile-only" style="margin-bottom:28px;"><x-ui.logo color="dark" /></div>
        @if($complete)
        <div style="margin-bottom:28px;"><h2 style="font-size:22px;font-weight:600;color:#1C1917;margin-bottom:8px;">Password updated</h2><p style="font-size:13px;color:#78716C;line-height:1.7;">Your password has been changed successfully. You can now sign in to your workspace.</p></div>
        <a href="{{ route('tenant.login') }}" wire:navigate class="krd-btn krd-btn-primary krd-btn-lg" style="width:100%;text-align:center;">Go to sign in</a>
        @else
        <div style="margin-bottom:28px;"><h2 style="font-size:22px;font-weight:600;color:#1C1917;margin-bottom:8px;">Reset your password</h2><p style="font-size:13px;color:#78716C;line-height:1.7;">Use at least 8 characters with uppercase, lowercase, and a number.</p></div>
        @if($error)<div style="background:#FEE2E2;border:1px solid #FECACA;border-radius:6px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#DC2626;">{{ $error }}</div>@endif
        <div class="krd-input-group"><label class="krd-label-text">Email address</label><input wire:model="email" type="email" class="krd-input @error('email') krd-input-error @enderror" autocomplete="email" />@error('email')<span class="krd-input-error-msg">{{ $message }}</span>@enderror</div>
        <div class="krd-input-group"><label class="krd-label-text">New password</label><input wire:model="password" type="password" class="krd-input @error('password') krd-input-error @enderror" autocomplete="new-password" />@error('password')<span class="krd-input-error-msg">{{ $message }}</span>@enderror</div>
        <div class="krd-input-group"><label class="krd-label-text">Confirm password</label><input wire:model="password_confirmation" type="password" class="krd-input" autocomplete="new-password" wire:keydown.enter="resetPassword" /></div>
        <button wire:click="resetPassword" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-lg" style="width:100%;"><span wire:loading.remove wire:target="resetPassword">Update password</span><span wire:loading wire:target="resetPassword">Updating...</span></button>
        @endif
        <div style="margin-top:28px;text-align:center;font-size:12px;color:#A8A29E;"><a href="{{ route('tenant.login') }}" wire:navigate style="color:#7C3AED;text-decoration:none;font-weight:500;">Back to sign in</a></div>
    </div></div>
</div>
