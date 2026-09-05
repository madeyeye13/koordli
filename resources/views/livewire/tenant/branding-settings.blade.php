<div>
    <div style="margin-bottom:24px;">
        <div class="krd-label" style="margin-bottom:4px;">Settings</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">Branding</h2>
        <p style="font-size:13px;color:#78716C;margin-top:4px;">Personalise your workspace logo and brand colors.</p>
    </div>

    <div class="krd-card" style="padding:24px;max-width:720px;">
        <div class="krd-input-group">
            <label class="krd-label-text">Company Logo</label>
            @if($logo)
            <div style="margin-bottom:10px;">
                <img src="{{ $logo->temporaryUrl() }}" alt="New company logo preview" style="height:48px;width:auto;border-radius:4px;" />
            </div>
            @elseif($current_logo)
            <div style="margin-bottom:10px;">
                <img src="{{ Storage::disk('public')->url($current_logo) }}" alt="Current company logo" style="height:48px;width:auto;border-radius:4px;" />
            </div>
            @endif
            <input wire:model="logo" type="file" accept="image/*" class="krd-input" style="padding:7px 12px;cursor:pointer;" />
            @error('logo') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            <span class="krd-input-hint">Upload a replacement logo. PNG, JPG or SVG, max 2MB.</span>
        </div>

        <div class="krd-input-group">
            <label class="krd-label-text">Primary Color</label>
            <div style="display:flex;align-items:center;gap:10px;">
                <input wire:model.live="primary_color" type="color" style="width:40px;height:36px;border:1px solid #E7E5E4;border-radius:4px;cursor:pointer;padding:2px;" />
                <input wire:model.live="primary_color" type="text" class="krd-input" placeholder="#7C3AED" style="font-family:monospace;text-transform:uppercase;" />
            </div>
            @error('primary_color') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
        </div>

        <div class="krd-input-group" style="margin-bottom:0;">
            <label class="krd-label-text">Accent Color</label>
            <div style="display:flex;align-items:center;gap:10px;">
                <input wire:model.live="accent_color" type="color" style="width:40px;height:36px;border:1px solid #E7E5E4;border-radius:4px;cursor:pointer;padding:2px;" />
                <input wire:model.live="accent_color" type="text" class="krd-input" placeholder="#F59E0B" style="font-family:monospace;text-transform:uppercase;" />
            </div>
            @error('accent_color') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
        </div>
    </div>

    <div style="margin-top:16px;padding:16px;max-width:720px;border:1px solid #E7E5E4;border-radius:4px;background:#FAFAF9;">
        <div style="font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#A8A29E;margin-bottom:10px;">Preview</div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <button style="background:{{ $primary_color }};color:#fff;border:none;border-radius:4px;padding:8px 16px;font-size:13px;font-weight:500;cursor:default;">Primary</button>
            <button style="background:{{ $accent_color }};color:#fff;border:none;border-radius:4px;padding:8px 16px;font-size:13px;font-weight:500;cursor:default;">Accent</button>
            <span style="display:inline-flex;align-items:center;padding:2px 8px;background:{{ $primary_color }}22;color:{{ $primary_color }};font-size:11px;font-weight:500;border-radius:4px;">Badge</span>
        </div>
    </div>

    <div style="margin-top:16px;max-width:720px;">
        <button wire:click="save" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-lg">
            <span wire:loading.remove wire:target="save">Save Branding</span>
            <span wire:loading wire:target="save">Saving...</span>
        </button>
    </div>
</div>
