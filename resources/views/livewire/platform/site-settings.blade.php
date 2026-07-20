<div>
    <div style="margin-bottom:24px;">
        <div class="krd-label" style="margin-bottom:4px;">Platform Management</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">Site Settings</h2>
    </div>

    <div style="max-width:560px;">
        <div class="krd-card" style="padding:24px;">
            <div class="krd-label" style="margin-bottom:16px;">Public Landing Page</div>

            <div class="krd-input-group">
                <label class="krd-label-text">Site Name</label>
                <input wire:model="site_name" type="text" class="krd-input" />
                @error('site_name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="krd-input-group">
                <label class="krd-label-text">Tagline</label>
                <input wire:model="site_tagline" type="text" class="krd-input" />
            </div>

            <div class="krd-input-group" style="margin-bottom:0;">
                <label class="krd-label-text">Favicon</label>
                @if($favicon_path)
                <div style="margin-bottom:8px;">
                    <img src="{{ Storage::url($favicon_path) }}" style="width:32px;height:32px;border-radius:4px;" />
                </div>
                @endif
                <input wire:model="favicon" type="file" accept="image/*" class="krd-input" style="padding:8px;" />
                <span class="krd-input-hint">Square image, ideally 32×32 or 64×64px.</span>
                @error('favicon') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            <button wire:click="save" wire:loading.attr="disabled" class="krd-btn krd-btn-primary" style="margin-top:16px;">
                <span wire:loading.remove wire:target="save">Save Settings</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </div>
</div>