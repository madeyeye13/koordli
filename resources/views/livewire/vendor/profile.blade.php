<div>
    <div style="margin-bottom: 28px;">
        <h1 style="font-size: 22px; font-weight: 600; color: #1C1917; letter-spacing: -0.01em;">My Profile</h1>
        <p style="font-size: 13px; color: #78716C; margin-top: 4px;">Update your contact and business information.</p>
    </div>

    <div class="vendor-profile-layout" style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;">

        {{-- Left — Form --}}
        <div style="display:flex;flex-direction:column;gap:16px;">
            <div class="krd-card">
                <div style="font-size:14px;font-weight:600;color:#1C1917;margin-bottom:16px;">Profile Information</div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Full Name</label>
                    <input wire:model="name" type="text" class="krd-input @error('name') krd-input-error @enderror"
                        placeholder="Your full name" />
                    @error('name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Business Name</label>
                    <input wire:model="business_name" type="text" class="krd-input @error('business_name') krd-input-error @enderror"
                        placeholder="Your business or company name" />
                    @error('business_name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                <div class="krd-input-group" style="margin-bottom:0;">
                    <label class="krd-label-text">Phone</label>
                    <input wire:model="phone" type="text" class="krd-input @error('phone') krd-input-error @enderror"
                        placeholder="+234..." />
                    @error('phone') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                <div style="margin-top: 16px;">
                    <button wire:click="save" wire:loading.attr="disabled" wire:target="save" class="krd-btn krd-btn-primary">
                        <span wire:loading.remove wire:target="save">Save Changes</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                </div>
            </div>

            <div class="krd-card">
                <div style="font-size:14px;font-weight:600;color:#1C1917;margin-bottom:16px;">Change Password</div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Current Password</label>
                    <input wire:model="current_password" type="password" class="krd-input @error('current_password') krd-input-error @enderror" />
                    @error('current_password') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                <div class="krd-input-group">
                    <label class="krd-label-text">New Password</label>
                    <input wire:model="new_password" type="password" class="krd-input @error('new_password') krd-input-error @enderror" />
                    @error('new_password') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                <div class="krd-input-group" style="margin-bottom:0;">
                    <label class="krd-label-text">Confirm New Password</label>
                    <input wire:model="new_password_confirmation" type="password" class="krd-input" />
                </div>

                <div style="margin-top:16px;">
                    <button wire:click="changePassword" wire:loading.attr="disabled" wire:target="changePassword" class="krd-btn krd-btn-primary">
                        <span wire:loading.remove wire:target="changePassword">Change Password</span>
                        <span wire:loading wire:target="changePassword">Saving...</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Right — Quick Access + Tips --}}
        <div class="vendor-profile-tips" style="display:flex;flex-direction:column;gap:12px;position:sticky;top:80px;">
            @if($showQuickAccess)
            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:4px;">Quick Access Link</div>
                <p style="font-size:11px;color:#78716C;margin-bottom:12px;line-height:1.6;">Update your tasks and runsheet items without logging in.</p>

                @if(!$quickAccessLink->is_active)
                <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:6px;padding:10px;margin-bottom:12px;font-size:11px;color:#DC2626;">
                    Deactivated by your organizer.
                </div>
                @endif

                <div style="background:#F5F5F4;border-radius:6px;padding:8px 10px;font-size:10.5px;color:#57534E;word-break:break-all;font-family:monospace;margin-bottom:10px;">
                    {{ $quickAccessUrl }}
                </div>

                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <button type="button" x-data x-on:click="navigator.clipboard.writeText('{{ $quickAccessUrl }}'); $el.textContent = 'Copied!'; setTimeout(() => $el.textContent = 'Copy Link', 1500)" class="krd-btn krd-btn-secondary krd-btn-sm" style="flex:1;">Copy Link</button>
                </div>
                <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:6px;">
                    <x-ui.confirm-button action="regenerateQuickAccess" message="This will immediately invalidate your current link. Continue?" label="Regenerate" class="krd-btn krd-btn-danger krd-btn-sm" />
                    @if($quickAccessLink->pin_enabled)
                    <x-ui.confirm-button action="resetQuickAccessPin" message="You'll set a new PIN next time you use your link. Continue?" label="Reset PIN" class="krd-btn krd-btn-ghost krd-btn-sm" />
                    @endif
                </div>

                <div style="margin-top:10px;font-size:10.5px;color:#A8A29E;">
                    PIN required: <strong style="color:#78716C;">{{ $quickAccessLink->pin_enabled ? 'Yes' : 'No' }}</strong>
                </div>

                <x-ui.dismissible-tip id="quick-access-vendor-profile" text="Treat this link like a password — regenerate it immediately if compromised." />
            </div>
            @endif

            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;">💡 Keep this updated</div>
                <p style="font-size:11.5px;color:#78716C;line-height:1.6;">Your business name and phone number are visible to event organizers when they book you — keep them current so you don't miss opportunities.</p>
            </div>
        </div>

    </div>
</div>