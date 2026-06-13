<div style="max-width: 480px; margin: 0 auto; padding: 48px 0;">

    <div style="text-align:center;margin-bottom:40px;">
        <div style="font-size:40px;margin-bottom:16px;">🔐</div>
        <h1 style="font-size:22px;font-weight:700;color:#1C1917;letter-spacing:-0.01em;margin-bottom:8px;">
            Set your password
        </h1>
        <p style="font-size:13px;color:#78716C;line-height:1.7;">
            For your security, please set a new password before viewing your event portal.
        </p>
    </div>

    <div class="krd-card" style="padding:28px;">

        <div class="krd-input-group">
            <label class="krd-label-text">New Password</label>
            <div style="position:relative;">
                <input
                    wire:model="new_password"
                    type="{{ $showNew ? 'text' : 'password' }}"
                    class="krd-input @error('new_password') krd-input-error @enderror"
                    placeholder="Min 8 characters"
                    style="padding-right:44px;"
                />
                <button type="button" wire:click="$toggle('showNew')"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#A8A29E;display:flex;align-items:center;padding:0;">
                    @if($showNew)
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    @endif
                </button>
            </div>
            @error('new_password') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            <span class="krd-input-hint">Must contain uppercase, lowercase and a number.</span>
        </div>

        <div class="krd-input-group" style="margin-bottom:0;">
            <label class="krd-label-text">Confirm Password</label>
            <div style="position:relative;">
                <input
                    wire:model="new_password_confirmation"
                    type="{{ $showConfirm ? 'text' : 'password' }}"
                    class="krd-input"
                    placeholder="Repeat new password"
                    style="padding-right:44px;"
                />
                <button type="button" wire:click="$toggle('showConfirm')"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#A8A29E;display:flex;align-items:center;padding:0;">
                    @if($showConfirm)
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    @endif
                </button>
            </div>
        </div>

        <div style="margin-top:20px;">
            <button wire:click="save" wire:loading.attr="disabled"
                class="krd-btn krd-btn-primary krd-btn-lg" style="width:100%;">
                <span wire:loading.remove wire:target="save">Set Password & View My Event</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>

    </div>

</div>