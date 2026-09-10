<div>
    <div style="margin-bottom:24px;">
        <div class="krd-label" style="margin-bottom:4px;">Platform</div>
        <h2 class="krd-heading-3">My Account</h2>
    </div>

    <div class="krd-account-grid">
        {{-- Left column — the actual forms --}}
        <div style="display:flex;flex-direction:column;gap:20px;">
            <div class="krd-card" style="padding:24px;">
                <div class="krd-label" style="margin-bottom:14px;">Profile</div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Full Name</label>
                    <input wire:model="name" type="text" class="krd-input @error('name') krd-input-error @enderror">
                    @error('name')<span class="krd-input-error-msg">{{ $message }}</span>@enderror
                </div>

                <div class="krd-input-group" x-data="{ open: @entangle('showEmailChange') }">
                    <label class="krd-label-text">Email Address</label>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <input type="email" value="{{ $email }}" class="krd-input" disabled style="opacity:0.6;flex:1;">
                        <button type="button" x-on:click="open = true" class="krd-btn krd-btn-secondary krd-btn-sm">Change</button>
                    </div>

                    <div x-show="open" x-cloak style="margin-top:12px;padding:14px;background:#F5F5F4;border-radius:8px;">
                        @if(!$emailCodeSent)
                        <input wire:model="newEmail" type="email" class="krd-input @error('newEmail') krd-input-error @enderror" placeholder="New email address" style="margin-bottom:8px;">
                        @error('newEmail')<span class="krd-input-error-msg">{{ $message }}</span>@enderror
                        <input wire:model="emailChangePassword" type="password" class="krd-input @error('emailChangePassword') krd-input-error @enderror" placeholder="Your current password" style="margin-bottom:10px;">
                        @error('emailChangePassword')<span class="krd-input-error-msg">{{ $message }}</span>@enderror
                        <div style="display:flex;gap:8px;">
                            <button wire:click="requestEmailChange" class="krd-btn krd-btn-primary krd-btn-sm">Send Code</button>
                            <button type="button" x-on:click="open = false" class="krd-btn krd-btn-secondary krd-btn-sm">Cancel</button>
                        </div>
                        @else
                        <p style="font-size:12px;color:#57534E;margin-bottom:8px;">Enter the code sent to {{ $newEmail }}.</p>
                        <input wire:model="emailCode" type="text" inputmode="numeric" maxlength="6" class="krd-input @error('emailCode') krd-input-error @enderror" placeholder="000000" style="margin-bottom:10px;text-align:center;letter-spacing:6px;">
                        @error('emailCode')<span class="krd-input-error-msg">{{ $message }}</span>@enderror
                        <div style="display:flex;gap:8px;">
                            <button wire:click="confirmEmailChange" class="krd-btn krd-btn-primary krd-btn-sm">Confirm</button>
                            <button type="button" x-on:click="open = false" class="krd-btn krd-btn-secondary krd-btn-sm">Cancel</button>
                        </div>
                        @endif
                    </div>
                </div>

                <button wire:click="updateName" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-sm">
                    <span wire:loading.remove wire:target="updateName">Save</span>
                    <span wire:loading wire:target="updateName">Saving...</span>
                </button>
            </div>

            <div class="krd-card" style="padding:24px;" x-data="{
                showCurrent: false, showNew: false, showConfirm: false,
                newPw: '', confirmPw: '',
                get pwMatch() { return this.confirmPw.length > 0 && this.newPw === this.confirmPw; },
                get pwMismatch() { return this.confirmPw.length > 0 && this.newPw !== this.confirmPw; }
            }">
                <div class="krd-label" style="margin-bottom:14px;">Change Password</div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Current Password</label>
                    <div style="position:relative;">
                        <input wire:model="currentPassword" :type="showCurrent ? 'text' : 'password'" class="krd-input @error('currentPassword') krd-input-error @enderror" style="padding-right:44px;">
                        <button type="button" x-on:click="showCurrent = !showCurrent" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#A8A29E;display:flex;align-items:center;padding:0;">
                            <svg x-show="!showCurrent" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="showCurrent" x-cloak xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    @error('currentPassword')<span class="krd-input-error-msg">{{ $message }}</span>@enderror
                </div>

                <div class="krd-input-group">
                    <label class="krd-label-text">New Password</label>
                    <div style="position:relative;">
                        <input wire:model="newPassword" x-model="newPw" :type="showNew ? 'text' : 'password'" class="krd-input @error('newPassword') krd-input-error @enderror" style="padding-right:44px;">
                        <button type="button" x-on:click="showNew = !showNew" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#A8A29E;display:flex;align-items:center;padding:0;">
                            <svg x-show="!showNew" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="showNew" x-cloak xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    @error('newPassword')<span class="krd-input-error-msg">{{ $message }}</span>@enderror
                    <p style="font-size:11px;color:#A8A29E;margin-top:4px;">Minimum 8 characters, with uppercase, lowercase, a number, and a symbol.</p>
                </div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Confirm New Password</label>
                    <div style="position:relative;">
                        <input wire:model="newPasswordConfirm" x-model="confirmPw" :type="showConfirm ? 'text' : 'password'" class="krd-input" x-bind:style="pwMismatch ? 'padding-right:44px;border-color:#EF4444;' : 'padding-right:44px;'">
                        <button type="button" x-on:click="showConfirm = !showConfirm" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#A8A29E;display:flex;align-items:center;padding:0;">
                            <svg x-show="!showConfirm" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="showConfirm" x-cloak xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    <p x-show="pwMismatch" x-cloak style="font-size:11px;color:#EF4444;margin-top:4px;">Passwords don't match.</p>
                    <p x-show="pwMatch" x-cloak style="font-size:11px;color:#059669;margin-top:4px;">✓ Passwords match.</p>
                </div>

                <button wire:click="changePassword" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-sm">
                    <span wire:loading.remove wire:target="changePassword">Change Password</span>
                    <span wire:loading wire:target="changePassword">Updating...</span>
                </button>
            </div>
        </div>

        {{-- Right column — real account info, not filler --}}
        <div style="display:flex;flex-direction:column;gap:20px;">
            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:14px;">Account Info</div>
                <div style="display:flex;flex-direction:column;gap:10px;font-size:13px;">
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:#78716C;">Role</span>
                        <span style="font-weight:500;color:#1C1917;">{{ str_replace('platform_', '', auth('platform')->user()->roles->first()?->name ?? '—') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:#78716C;">Status</span>
                        <span class="krd-badge krd-badge-green" style="font-size:10.5px;">Active</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:#78716C;">Member Since</span>
                        <span style="font-weight:500;color:#1C1917;">{{ auth('platform')->user()->created_at?->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>

            <div class="krd-card" style="padding:20px;background:#F5F3FF;border-color:#DDD6FE;">
                <div style="display:flex;gap:10px;">
                    <span style="font-size:18px;">🔒</span>
                    <div>
                        <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:4px;">Security Tip</div>
                        <p style="font-size:12px;color:#57534E;line-height:1.6;margin:0;">Changing your password signs you out of every other device and browser — your current session stays active.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .krd-account-grid { display: grid; grid-template-columns: 1fr 320px; gap: 20px; align-items: start; }
    @media (max-width: 900px) {
        .krd-account-grid { grid-template-columns: 1fr; }
    }
</style>