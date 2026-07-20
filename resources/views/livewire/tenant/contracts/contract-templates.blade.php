<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <div class="krd-label" style="margin-bottom:4px;">Vendor Contracts</div>
            <h2 class="krd-heading-3" style="color:#1C1917;">Contract Templates</h2>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('tenant.contracts') }}" wire:navigate class="krd-btn krd-btn-secondary">All Contracts</a>
            <button wire:click="showCreate" class="krd-btn krd-btn-primary">+ New Template</button>
        </div>
    </div>

    @if($showForm)
    <div class="krd-card" style="padding:24px;margin-bottom:20px;border:2px solid #7C3AED;">
        <div style="font-size:14px;font-weight:600;color:#1C1917;margin-bottom:16px;">
            {{ $editId ? 'Edit Template' : 'New Template' }}
        </div>

        <div class="krd-grid-2" style="gap:12px;">
            <div class="krd-input-group">
                <label class="krd-label-text">Template Name <span style="color:#EF4444;">*</span></label>
                <input wire:model="name" type="text" class="krd-input @error('name') krd-input-error @enderror" placeholder="e.g. Photography Service Agreement" />
                @error('name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>
            <div class="krd-input-group">
                <label class="krd-label-text">Category <span style="color:#A8A29E;font-weight:400;">(optional)</span></label>
                <input wire:model="category" type="text" class="krd-input" placeholder="e.g. Photography" />
            </div>
        </div>

        {{-- Placeholder reference --}}
        <div style="background:#F5F3FF;border:1px solid #DDD6FE;border-radius:6px;padding:12px 14px;margin-bottom:16px;">
            <div style="font-size:11px;font-weight:600;color:#5B21B6;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.06em;">Available Placeholders</div>
            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                @foreach(\App\Models\Tenant\VendorContractTemplate::availablePlaceholders() as $tag => $desc)
                <span title="{{ $desc }}" style="font-size:11px;font-family:monospace;background:#fff;border:1px solid #DDD6FE;color:#7C3AED;padding:3px 8px;border-radius:4px;cursor:help;">{{ $tag }}</span>
                @endforeach
            </div>
        </div>

        <div class="krd-input-group" style="margin-bottom:16px;" x-data="{ tplMode: 'edit' }">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                <label class="krd-label-text" style="margin-bottom:0;">Content <span style="color:#EF4444;">*</span></label>
                <div style="display:flex;gap:4px;background:#F5F5F4;border-radius:6px;padding:3px;">
                    <button type="button" x-on:click="tplMode = 'edit'"
                        :style="tplMode === 'edit' ? 'padding:4px 10px;border-radius:4px;border:none;background:#fff;color:#1C1917;font-size:11px;font-weight:600;cursor:pointer;' : 'padding:4px 10px;border-radius:4px;border:none;background:transparent;color:#78716C;font-size:11px;cursor:pointer;'">
                        ✏️ Edit
                    </button>
                    <button type="button" x-on:click="tplMode = 'preview'"
                        :style="tplMode === 'preview' ? 'padding:4px 10px;border-radius:4px;border:none;background:#fff;color:#1C1917;font-size:11px;font-weight:600;cursor:pointer;' : 'padding:4px 10px;border-radius:4px;border:none;background:transparent;color:#78716C;font-size:11px;cursor:pointer;'">
                        👁 Preview
                    </button>
                </div>
            </div>

            <div x-show="tplMode === 'preview'" x-cloak
                style="border:1px solid #E7E5E4;border-radius:6px;padding:32px;background:#fff;max-height:500px;overflow-y:auto;">
                <div style="max-width:600px;margin:0 auto;font-size:13px;line-height:1.8;color:#1C1917;">
                    {!! $content !!}
                </div>
            </div>

            <div x-show="tplMode === 'edit'" x-cloak>
            <div id="template-editor-toolbar" style="display:flex;gap:4px;padding:8px;background:#F5F5F4;border:1px solid #E7E5E4;border-bottom:none;border-radius:6px 6px 0 0;flex-wrap:wrap;">
                <button type="button" onclick="document.execCommand('italic')" class="krd-btn krd-btn-ghost krd-btn-sm" style="font-style:italic;">I</button>
                <button type="button" onclick="document.execCommand('underline')" class="krd-btn krd-btn-ghost krd-btn-sm" style="text-decoration:underline;">U</button>
                <button type="button" onclick="document.execCommand('formatBlock', false, 'h2')" class="krd-btn krd-btn-ghost krd-btn-sm">H2</button>
                <button type="button" onclick="document.execCommand('formatBlock', false, 'h3')" class="krd-btn krd-btn-ghost krd-btn-sm">H3</button>
                <button type="button" onclick="document.execCommand('insertUnorderedList')" class="krd-btn krd-btn-ghost krd-btn-sm">• List</button>
                <button type="button" onclick="document.execCommand('insertOrderedList')" class="krd-btn krd-btn-ghost krd-btn-sm">1. List</button>
                <button type="button" onclick="document.execCommand('formatBlock', false, 'p')" class="krd-btn krd-btn-ghost krd-btn-sm">¶</button>
            </div>
            <div
                id="template-content-editable"
                contenteditable="true"
                x-data
                x-init="$el.innerHTML = @js($content)"
                x-on:input="$wire.set('content', $el.innerHTML, false)"
                style="min-height:300px;max-height:500px;overflow-y:auto;padding:16px;border:1px solid #E7E5E4;border-radius:0 0 6px 6px;font-size:13px;line-height:1.7;color:#1C1917;background:#fff;">
            </div>
            </div>
            @error('content') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
        </div>

        <div style="display:flex;gap:10px;">
            <button wire:click="save" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                <span wire:loading.remove wire:target="save">{{ $editId ? 'Update Template' : 'Create Template' }}</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
            <button wire:click="$set('showForm', false)" class="krd-btn krd-btn-ghost">Cancel</button>
        </div>
    </div>
    @endif

    @if($templates->isEmpty())
    <div class="krd-card">
        <div class="krd-empty-state">
            <div class="krd-empty-state-icon">📄</div>
            <div class="krd-empty-state-title">No templates yet</div>
            <div class="krd-empty-state-desc">Create reusable contract templates with placeholders to save time.</div>
        </div>
    </div>
    @else
    <div class="krd-grid-3" style="gap:16px;">
        @foreach($templates as $template)
        <div class="krd-card" style="padding:20px;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:8px;">
                <div style="font-size:14px;font-weight:600;color:#1C1917;">{{ $template->name }}</div>
                <span class="krd-badge {{ $template->is_active ? 'krd-badge-green' : 'krd-badge-stone' }}">
                    {{ $template->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            @if($template->category)
            <div style="font-size:11px;color:#A8A29E;margin-bottom:10px;">{{ $template->category }}</div>
            @endif
            <div style="font-size:12px;color:#78716C;margin-bottom:14px;">
                {{ $template->contracts()->count() }} contract(s) created from this template
            </div>
            <div style="display:flex;gap:8px;">
                <button wire:click="showEdit({{ $template->id }})" class="krd-btn krd-btn-secondary krd-btn-sm">Edit</button>
                <button wire:click="toggleActive({{ $template->id }})" class="krd-btn krd-btn-secondary krd-btn-sm">
                    {{ $template->is_active ? 'Deactivate' : 'Activate' }}
                </button>
                <button wire:click="confirmDelete({{ $template->id }})" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                </button>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @if($showDeleteModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Delete Template?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;">Existing contracts created from this template will not be affected.</p>
            <div style="display:flex;gap:10px;">
                <button wire:click="delete" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Delete</button>
                <button wire:click="$set('showDeleteModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif
</div>