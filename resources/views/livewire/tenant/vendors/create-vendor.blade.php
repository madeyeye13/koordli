<div>
    <div style="margin-bottom:28px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('tenant.vendors') }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">
                ← Back to Vendor Directory
            </a>
        </div>
        <div class="krd-label" style="margin-bottom:4px;">Vendors</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">{{ $vendor ? 'Edit Vendor' : 'Add Vendor' }}</h2>
    </div>

    <div style="display:grid;grid-template-columns:1fr;gap:16px;" id="vendor-form-grid">

        {{-- Main Form --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            {{-- Basic Info --}}
            <div class="krd-card" style="padding:24px;">
                <div class="krd-label" style="margin-bottom:16px;">Vendor Information</div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Vendor / Business Name <span style="color:#EF4444;">*</span></label>
                    <input wire:model="name" type="text" class="krd-input @error('name') krd-input-error @enderror"
                        placeholder="e.g. Elite Photography Studio" autofocus />
                    @error('name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Category</label>
                    <x-ui.dropdown wire="vendor_category_id" placeholder="Select category"
                        selected="{{ $vendor_category_id ? ($categories->firstWhere('id', $vendor_category_id)?->name ?? 'Select category') : 'Select category' }}">
                        <div class="krd-dropdown-option" x-on:click="select('Select category', null); $wire.set('vendor_category_id', null)">— None —</div>
                        @foreach($categories as $cat)
                        <div class="krd-dropdown-option {{ $vendor_category_id == $cat->id ? 'selected' : '' }}"
                            x-on:click="select('{{ $cat->name }}', {{ $cat->id }})">
                            {{ $cat->name }}
                        </div>
                        @endforeach
                    </x-ui.dropdown>
                </div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Description</label>
                    <textarea wire:model="description" class="krd-input" rows="2"
                        placeholder="Brief description of their services..."></textarea>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="krd-input-group">
                        <label class="krd-label-text">Contact Person</label>
                        <input wire:model="contact_name" type="text" class="krd-input" placeholder="e.g. Kemi Adeyemi" />
                    </div>
                    <div class="krd-input-group">
                        <label class="krd-label-text">Phone</label>
                        <input wire:model="phone" type="tel" class="krd-input" placeholder="+234 801 234 5678" />
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="krd-input-group">
                        <label class="krd-label-text">Email</label>
                        <input wire:model="email" type="email" class="krd-input @error('email') krd-input-error @enderror" placeholder="vendor@email.com" />
                        @error('email') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>
                    <div class="krd-input-group">
                        <label class="krd-label-text">Instagram</label>
                        <input wire:model="instagram" type="text" class="krd-input" placeholder="@elitephoto" />
                    </div>
                </div>

                <div class="krd-input-group" style="margin-bottom:0;">
                    <label class="krd-label-text">Website</label>
                    <input wire:model="website" type="url" class="krd-input @error('website') krd-input-error @enderror" placeholder="https://elitephoto.com" />
                    @error('website') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Rating + Preferences --}}
            <div class="krd-card" style="padding:24px;">
                <div class="krd-label" style="margin-bottom:16px;">Rating & Preferences</div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Rating</label>
                    <div x-data="{ rating: {{ $rating ?? 0 }} }" style="display:flex;gap:6px;align-items:center;">
                        @for($i = 1; $i <= 5; $i++)
                        <button
                            type="button"
                            x-on:click="rating = {{ $i }}; $wire.set('rating', {{ $i }})"
                            :style="rating >= {{ $i }} ? 'font-size:24px;background:none;border:none;cursor:pointer;color:#F59E0B;' : 'font-size:24px;background:none;border:none;cursor:pointer;color:#E7E5E4;'"
                        >★</button>
                        @endfor
                        <button type="button" x-show="rating > 0"
                            x-on:click="rating = 0; $wire.set('rating', null)"
                            style="font-size:11px;color:#A8A29E;background:none;border:none;cursor:pointer;margin-left:4px;">
                            Clear
                        </button>
                    </div>
                </div>

                <div style="display:flex;gap:16px;flex-wrap:wrap;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#57534E;">
                        <input wire:model="is_preferred" type="checkbox" style="width:16px;height:16px;cursor:pointer;accent-color:#7C3AED;" />
                        ⭐ Mark as preferred vendor
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#57534E;">
                        <input wire:model="is_active" type="checkbox" style="width:16px;height:16px;cursor:pointer;accent-color:#7C3AED;" />
                        Active (available for assignments)
                    </label>
                </div>
            </div>

            {{-- Notes --}}
            <div class="krd-card" style="padding:24px;">
                <div class="krd-label" style="margin-bottom:16px;">Internal Notes</div>
                <textarea wire:model="notes" class="krd-input" rows="3"
                    placeholder="Private notes about this vendor — pricing tips, reliability, special requirements..."></textarea>
            </div>

            {{-- Submit --}}
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button wire:click="save" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-lg" style="flex:1;min-width:140px;">
                    <span wire:loading.remove wire:target="save">{{ $vendor ? 'Update Vendor' : 'Add to Directory' }}</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
                <a href="{{ route('tenant.vendors') }}" wire:navigate class="krd-btn krd-btn-ghost">Cancel</a>
            </div>

        </div>

        {{-- Tips Panel --}}
        <div style="display:flex;flex-direction:column;gap:12px;" id="vendor-tips-panel">
            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;">💡 Vendor Directory Tips</div>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    @foreach([
                        'Add all vendors you work with regularly — photographers, caterers, DJs, decorators.',
                        'Mark preferred vendors with ⭐ so they appear first when assigning to events.',
                        'Use ratings to track vendor performance over time.',
                        'Internal notes are private — perfect for pricing notes and reliability tips.',
                        'Once added, you can assign this vendor to any event from the event page.',
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
</div>

<style>
@media (min-width: 768px) {
    #vendor-form-grid {
        grid-template-columns: 1fr 280px !important;
    }
}
</style>