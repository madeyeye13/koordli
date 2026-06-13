<div>
    <div style="margin-bottom: 28px;">
        <h1 style="font-size: 22px; font-weight: 600; color: #1C1917; letter-spacing: -0.01em;">My Profile</h1>
        <p style="font-size: 13px; color: #78716C; margin-top: 4px;">Update your contact and business information.</p>
    </div>

    <div class="krd-card" style="max-width: 560px;">
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

        <div class="krd-input-group">
            <label class="krd-label-text">Phone</label>
            <input wire:model="phone" type="text" class="krd-input @error('phone') krd-input-error @enderror"
                placeholder="+234..." />
            @error('phone') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
        </div>

        <div style="margin-top: 8px;">
            <button wire:click="save" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                <span wire:loading.remove wire:target="save">Save Changes</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </div>
</div>