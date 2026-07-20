<div>
    <div style="margin-bottom:24px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('tenant.contracts') }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">← Back to Contracts</a>
        </div>
        <div class="krd-label" style="margin-bottom:4px;">Vendor Contracts</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">New Contract</h2>
    </div>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;" id="create-contract-grid">
        <div>
            <div class="krd-card" style="padding:24px;margin-bottom:16px;">
                <div class="krd-label" style="margin-bottom:16px;">Contract Details</div>

                <div class="krd-grid-2" style="gap:12px;">
                    <div class="krd-input-group">
                        <label class="krd-label-text">Vendor <span style="color:#EF4444;">*</span></label>
                        <x-ui.dropdown wire="vendor_id" placeholder="Select vendor"
                            selected="{{ $vendor_id ? ($vendors->firstWhere('id', $vendor_id)?->name ?? 'Select vendor') : 'Select vendor' }}">
                            @foreach($vendors as $v)
                            <div class="krd-dropdown-option {{ $vendor_id == $v->id ? 'selected' : '' }}" x-on:click="select('{{ $v->name }}', {{ $v->id }})">{{ $v->name }}</div>
                            @endforeach
                        </x-ui.dropdown>
                        @error('vendor_id') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>

                    <div class="krd-input-group">
                        <label class="krd-label-text">Event <span style="color:#A8A29E;font-weight:400;">(optional)</span></label>
                        <x-ui.dropdown wire="event_id" placeholder="General agreement (no event)"
                            selected="{{ $event_id ? ($events->firstWhere('id', $event_id)?->name ?? 'General agreement') : 'General agreement (no event)' }}">
                            <div class="krd-dropdown-option {{ !$event_id ? 'selected' : '' }}" x-on:click="select('General agreement (no event)', null)">General Agreement (no event)</div>
                            @foreach($events as $e)
                            <div class="krd-dropdown-option {{ $event_id == $e->id ? 'selected' : '' }}" x-on:click="select('{{ $e->name }}', {{ $e->id }})">
                                {{ $e->name }} @if($e->date)<span style="color:#A8A29E;"> · {{ $e->date->format('M d, Y') }}</span>@endif
                            </div>
                            @endforeach
                        </x-ui.dropdown>
                    </div>

                    <div class="krd-input-group">
                        <label class="krd-label-text">Contract Amount</label>
                        <input wire:model="contract_amount" type="number" step="0.01" class="krd-input" placeholder="0.00" />
                    </div>

                    <div class="krd-input-group">
                        <label class="krd-label-text">Expires On <span style="color:#A8A29E;font-weight:400;">(optional)</span></label>
                        <input wire:model="expires_at" type="date" class="krd-input" />
                    </div>
                </div>

                <div class="krd-input-group" style="margin-bottom:0;">
                    <label class="krd-label-text">Payment Schedule</label>
                    <input wire:model="payment_schedule" type="text" class="krd-input" placeholder="e.g. 50% deposit, 50% on delivery" />
                </div>
            </div>

            @if($vendor_id)
            <div class="krd-card" style="padding:24px;margin-bottom:16px;">
                <div class="krd-label" style="margin-bottom:12px;">Start from a Template</div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    @forelse($templates as $template)
                    <button wire:click="selectTemplate({{ $template->id }})"
                        class="krd-btn {{ $template_id === $template->id ? 'krd-btn-primary' : 'krd-btn-secondary' }} krd-btn-sm">
                        {{ $template->name }}
                    </button>
                    @empty
                    <p style="font-size:13px;color:#A8A29E;">No templates yet. <a href="{{ route('tenant.contract-templates') }}" wire:navigate style="color:#7C3AED;">Create one →</a></p>
                    @endforelse
                </div>
            </div>
            @endif

            @if($content)
            <div class="krd-card" style="padding:24px;" x-data="{ mode: 'edit' }">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                    <div class="krd-label" style="margin-bottom:0;">Contract Content</div>
                    <div style="display:flex;gap:4px;background:#F5F5F4;border-radius:6px;padding:3px;">
                        <button type="button" x-on:click="mode = 'edit'"
                            :style="mode === 'edit' ? 'padding:5px 12px;border-radius:4px;border:none;background:#fff;color:#1C1917;font-size:12px;font-weight:600;cursor:pointer;' : 'padding:5px 12px;border-radius:4px;border:none;background:transparent;color:#78716C;font-size:12px;cursor:pointer;'">
                            ✏️ Edit
                        </button>
                        <button type="button" x-on:click="mode = 'preview'"
                            :style="mode === 'preview' ? 'padding:5px 12px;border-radius:4px;border:none;background:#fff;color:#1C1917;font-size:12px;font-weight:600;cursor:pointer;' : 'padding:5px 12px;border-radius:4px;border:none;background:transparent;color:#78716C;font-size:12px;cursor:pointer;'">
                            👁 Preview
                        </button>
                    </div>
                </div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Title <span style="color:#EF4444;">*</span></label>
                    <input wire:model="title" type="text" class="krd-input @error('title') krd-input-error @enderror" />
                    @error('title') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                {{-- Preview mode --}}
                <div x-show="mode === 'preview'" x-cloak
                    style="border:1px solid #E7E5E4;border-radius:6px;padding:32px;background:#fff;max-height:600px;overflow-y:auto;">
                    <div style="max-width:640px;margin:0 auto;font-size:13px;line-height:1.8;color:#1C1917;">
                        <div style="text-align:center;margin-bottom:24px;padding-bottom:16px;border-bottom:2px solid #1C1917;">
                            <div style="font-size:11px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:#A8A29E;">Contract Preview</div>
                        </div>
                        {!! $content !!}
                    </div>
                </div>

                {{-- Edit mode --}}
                <div x-show="mode === 'edit'" x-cloak class="krd-input-group" style="margin-bottom:0;">
                    <label class="krd-label-text">Review & Edit Content</label>
                    <div style="display:flex;gap:4px;padding:8px;background:#F5F5F4;border:1px solid #E7E5E4;border-bottom:none;border-radius:6px 6px 0 0;flex-wrap:wrap;">
                        <button type="button" onclick="document.execCommand('bold')" class="krd-btn krd-btn-ghost krd-btn-sm" style="font-weight:700;">B</button>
                        <button type="button" onclick="document.execCommand('italic')" class="krd-btn krd-btn-ghost krd-btn-sm" style="font-style:italic;">I</button>
                        <button type="button" onclick="document.execCommand('underline')" class="krd-btn krd-btn-ghost krd-btn-sm" style="text-decoration:underline;">U</button>
                        <button type="button" onclick="document.execCommand('formatBlock', false, 'h2')" class="krd-btn krd-btn-ghost krd-btn-sm">H2</button>
                        <button type="button" onclick="document.execCommand('formatBlock', false, 'h3')" class="krd-btn krd-btn-ghost krd-btn-sm">H3</button>
                        <button type="button" onclick="document.execCommand('insertUnorderedList')" class="krd-btn krd-btn-ghost krd-btn-sm">• List</button>
                    </div>
                    <div
                        contenteditable="true"
                        x-data
                        x-init="$el.innerHTML = @js($content)"
                        x-on:input="$wire.set('content', $el.innerHTML, false)"
                        style="min-height:350px;max-height:600px;overflow-y:auto;padding:16px;border:1px solid #E7E5E4;border-radius:0 0 6px 6px;font-size:13px;line-height:1.7;color:#1C1917;background:#fff;">
                    </div>
                    @error('content') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>
            </div>
            @endif

            @if($content)
            <div style="margin-top:16px;">
                <button wire:click="save" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                    <span wire:loading.remove wire:target="save">Save Contract as Draft</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
            @endif
        </div>

        <div style="position:sticky;top:80px;" id="create-contract-tips">
            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;">💡 How it works</div>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    @foreach([
                        'Select a vendor and optionally an event.',
                        'Choose a template — placeholders auto-fill with your data.',
                        'Review and edit the generated content freely.',
                        'Save as draft, then send when ready — this emails a PDF to the vendor.',
                        'Upload the signed copy once you receive it back.',
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
@media (max-width:768px) { #create-contract-grid { grid-template-columns:1fr !important; } #create-contract-tips { position:static !important; } }
</style>