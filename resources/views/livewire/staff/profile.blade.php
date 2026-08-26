<div>
    <div style="margin-bottom:24px;">
        <div class="krd-label" style="margin-bottom:4px;">My Account</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">My Profile</h2>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

        <div class="krd-card">
            <div style="font-size:14px;font-weight:600;color:#1C1917;margin-bottom:16px;">Profile Information</div>

            <div class="krd-input-group">
                <label class="krd-label-text">Full Name</label>
                <input wire:model="name" type="text" class="krd-input @error('name') krd-input-error @enderror" />
                @error('name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="krd-input-group" style="margin-bottom:0;">
                <label class="krd-label-text">Email</label>
                <input type="text" value="{{ auth()->user()->email }}" disabled class="krd-input" style="background:#F5F5F4;color:#A8A29E;" />
                <span class="krd-input-hint">Contact your organization owner to change your email.</span>
            </div>

            <div style="margin-top:16px;">
                <button wire:click="saveProfile" wire:loading.attr="disabled" wire:target="saveProfile" class="krd-btn krd-btn-primary">
                    <span wire:loading.remove wire:target="saveProfile">Save Changes</span>
                    <span wire:loading wire:target="saveProfile">Saving...</span>
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
</div>