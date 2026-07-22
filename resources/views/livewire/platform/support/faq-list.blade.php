<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <div class="krd-label" style="margin-bottom:4px;">Support</div>
            <h2 class="krd-heading-3" style="color:#1C1917;">FAQ Knowledge Base</h2>
            <p style="font-size:12px;color:#A8A29E;margin-top:4px;">Used by the support bot to answer tenant questions automatically.</p>
        </div>
        <button wire:click="showCreate" class="krd-btn krd-btn-primary">+ Add FAQ</button>
    </div>

    @if($showForm)
    <div class="krd-card" style="padding:24px;margin-bottom:20px;border:2px solid #7C3AED;">
        <div style="font-size:14px;font-weight:600;color:#1C1917;margin-bottom:16px;">
            {{ $editId ? 'Edit FAQ' : 'New FAQ' }}
        </div>

        <div class="krd-input-group">
            <label class="krd-label-text">Question</label>
            <input wire:model="question" type="text" class="krd-input @error('question') krd-input-error @enderror" />
            @error('question') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
        </div>

        <div class="krd-input-group">
            <label class="krd-label-text">Answer</label>
            <textarea wire:model="answer" class="krd-input" rows="4"></textarea>
            <span class="krd-input-hint">You can include full URLs — they'll become clickable links automatically.</span>
            @error('answer') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
        </div>

        <div class="krd-grid-2" style="gap:12px;">
            <div class="krd-input-group">
                <label class="krd-label-text">Keywords <span style="color:#A8A29E;font-weight:400;">(comma-separated)</span></label>
                <input wire:model="keywords" type="text" class="krd-input" placeholder="domain, dns, custom domain" />
            </div>
            <div class="krd-input-group">
                <label class="krd-label-text">Category</label>
                <input wire:model="category" type="text" class="krd-input" placeholder="billing" />
            </div>
        </div>

        <div style="display:flex;gap:10px;">
            <button wire:click="save" class="krd-btn krd-btn-primary">{{ $editId ? 'Update' : 'Create' }}</button>
            <button wire:click="$set('showForm', false)" class="krd-btn krd-btn-ghost">Cancel</button>
        </div>
    </div>
    @endif

    {{-- Desktop --}}
    <div class="krd-card" style="padding:0;overflow:hidden;" id="faq-desktop">
        <div class="krd-table-wrap">
            <table class="krd-table">
                <thead>
                    <tr><th>Question</th><th>Category</th><th>Keywords</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($faqs as $faq)
                    <tr>
                        <td style="font-size:13px;font-weight:500;color:#1C1917;max-width:280px;">{{ $faq->question }}</td>
                        <td style="font-size:12px;color:#78716C;">{{ $faq->category ?? '—' }}</td>
                        <td style="font-size:11px;color:#A8A29E;">{{ implode(', ', array_slice($faq->keywords ?? [], 0, 3)) }}</td>
                        <td>
                            <span class="krd-badge {{ $faq->is_active ? 'krd-badge-green' : 'krd-badge-stone' }}">
                                {{ $faq->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <button wire:click="showEdit({{ $faq->id }})" class="krd-btn krd-btn-secondary krd-btn-sm">Edit</button>
                                <button wire:click="toggleActive({{ $faq->id }})" class="krd-btn krd-btn-secondary krd-btn-sm">{{ $faq->is_active ? 'Disable' : 'Enable' }}</button>
                                <button wire:click="confirmDelete({{ $faq->id }})" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;">✕</button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile --}}
    <div id="faq-mobile" style="display:flex;flex-direction:column;gap:10px;">
        @foreach($faqs as $faq)
        <div class="krd-card" style="padding:16px;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:8px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;">{{ $faq->question }}</div>
                <span class="krd-badge {{ $faq->is_active ? 'krd-badge-green' : 'krd-badge-stone' }}" style="flex-shrink:0;">
                    {{ $faq->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            @if($faq->category)
            <div style="font-size:11px;color:#78716C;margin-bottom:4px;">{{ $faq->category }}</div>
            @endif
            @if(!empty($faq->keywords))
            <div style="font-size:11px;color:#A8A29E;margin-bottom:12px;">{{ implode(', ', array_slice($faq->keywords, 0, 3)) }}</div>
            @endif
            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                <button wire:click="showEdit({{ $faq->id }})" class="krd-btn krd-btn-secondary krd-btn-sm">Edit</button>
                <button wire:click="toggleActive({{ $faq->id }})" class="krd-btn krd-btn-secondary krd-btn-sm">{{ $faq->is_active ? 'Disable' : 'Enable' }}</button>
                <button wire:click="confirmDelete({{ $faq->id }})" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;">Delete</button>
            </div>
        </div>
        @endforeach
    </div>

    <style>
    @media (min-width: 768px) { #faq-desktop { display: block !important; } #faq-mobile { display: none !important; } }
    @media (max-width: 767px) { #faq-desktop { display: none !important; } #faq-mobile { display: flex !important; } }
    </style>

    @if($showDeleteModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Delete FAQ?</h3>
            <div style="display:flex;gap:10px;">
                <button wire:click="delete" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Delete</button>
                <button wire:click="$set('showDeleteModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif
</div>