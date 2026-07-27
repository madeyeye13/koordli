<div>
    <div style="margin-bottom:24px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('tenant.assets') }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">← Back to {{ term_title('asset_plural', 'Assets') }}</a>
        </div>
        <div class="krd-label" style="margin-bottom:4px;">Operations</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">{{ $isEdit ? 'Edit' : 'New' }} {{ term_title('asset', 'Asset') }}</h2>
    </div>

    <div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;" id="create-asset-grid">
    <div class="krd-card" style="padding:24px;">
        <div class="krd-input-group">
            <label class="krd-label-text">Name <span style="color:#EF4444;">*</span></label>
            <input wire:model="name" type="text" class="krd-input @error('name') krd-input-error @enderror" placeholder="e.g. Canon C300 Camera" />
            @error('name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
        </div>

        <div class="krd-input-group">
            <label class="krd-label-text">Category</label>
            <x-ui.dropdown wire="asset_category_id" placeholder="Select category"
                selected="{{ $asset_category_id ? ($categories->firstWhere('id', $asset_category_id)?->name ?? 'Select category') : 'Select category' }}">
                @foreach($categories as $cat)
                <div class="krd-dropdown-option {{ $asset_category_id == $cat->id ? 'selected' : '' }}" x-on:click="select(@js($cat->name), {{ $cat->id }})">{{ $cat->name }}</div>
                @endforeach
            </x-ui.dropdown>
        </div>

        <div class="krd-input-group">
            <label class="krd-label-text">Status</label>
            <x-ui.dropdown wire="status" placeholder="Available"
                selected="{{ ucfirst($status) }}">
                <div class="krd-dropdown-option {{ $status === 'available' ? 'selected' : '' }}" x-on:click="select('Available', 'available')">Available</div>
                <div class="krd-dropdown-option {{ $status === 'reserved' ? 'selected' : '' }}" x-on:click="select('Reserved', 'reserved')">Reserved</div>
                <div class="krd-dropdown-option {{ $status === 'maintenance' ? 'selected' : '' }}" x-on:click="select('Maintenance', 'maintenance')">Maintenance</div>
            </x-ui.dropdown>
        </div>

        <div class="krd-input-group" style="margin-bottom:0;">
            <label class="krd-label-text">Notes</label>
            <textarea wire:model="notes" class="krd-input" rows="3" placeholder="Any additional details..."></textarea>
        </div>

        <button wire:click="save" wire:loading.attr="disabled" class="krd-btn krd-btn-primary" style="margin-top:16px;">
            <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Save Changes' : 'Create' }} {{ term('asset', 'Asset') }}</span>
            <span wire:loading wire:target="save">Saving...</span>
        </button>
    </div>

    <div style="display:flex;flex-direction:column;gap:12px;" id="create-asset-tips">
        <div class="krd-card" style="padding:20px;">
            <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;">💡 What are {{ term('asset_plural', 'assets') }}?</div>
            <div style="display:flex;flex-direction:column;gap:10px;">
                @foreach([
                    'Track equipment and resources you own or manage — cameras, speakers, lighting, chairs, tables, decor.',
                    'Assign an ' . term('asset', 'asset') . ' to an ' . term('event', 'event') . ' to know exactly what\'s in use where.',
                    'Mark items as Available, Reserved, or in Maintenance so you never double-book equipment.',
                    'This is intentionally lightweight — not a full inventory system, just enough to avoid conflicts.',
                ] as $tip)
                <div style="display:flex;gap:8px;align-items:flex-start;">
                    <div style="width:5px;height:5px;border-radius:50%;background:#7C3AED;flex-shrink:0;margin-top:6px;"></div>
                    <p style="font-size:12px;color:#78716C;line-height:1.6;">{{ $tip }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width:768px) { #create-asset-grid { grid-template-columns:1fr !important; } }
</style>