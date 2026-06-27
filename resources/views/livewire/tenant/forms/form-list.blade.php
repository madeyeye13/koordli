<div>
    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <div class="krd-label" style="margin-bottom:4px;">Business</div>
            <h2 class="krd-heading-3" style="color:#1C1917;">Forms & Bookings</h2>
        </div>
        <a href="{{ route('tenant.forms.create') }}" wire:navigate class="krd-btn krd-btn-primary">
            + New Form
        </a>
    </div>

    {{-- Filters --}}
    <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:flex-start;">
        <input wire:model.live.debounce.300ms="search" type="text"
            class="krd-input" placeholder="Search forms..."
            style="max-width:240px;" />

        <div x-data="{
                open: false,
                label: '{{ $typeFilter ? ucfirst($typeFilter) : 'All types' }}',
                pick(val, label) { this.label = label; this.open = false; $wire.set('typeFilter', val); }
            }"
            x-on:click.outside="open = false"
            style="position:relative;min-width:150px;">
            <button type="button" x-on:click="open = !open"
                x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'"
                style="width:100%;">
                <span x-text="label"></span>
                <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div x-show="open" x-cloak class="krd-dropdown-menu">
                <div class="krd-dropdown-option {{ !$typeFilter ? 'selected' : '' }}"
                    x-on:click="pick('', 'All types')">All types</div>
                <div class="krd-dropdown-option {{ $typeFilter === 'booking' ? 'selected' : '' }}"
                    x-on:click="pick('booking', 'Booking')">Booking</div>
                <div class="krd-dropdown-option {{ $typeFilter === 'consultation' ? 'selected' : '' }}"
                    x-on:click="pick('consultation', 'Consultation')">Consultation</div>
            </div>
        </div>
    </div>

    @if($forms->isEmpty())
    <div class="krd-card">
        <div class="krd-empty-state">
            <div class="krd-empty-state-icon">📋</div>
            <div class="krd-empty-state-title">No forms yet</div>
            <div class="krd-empty-state-desc">Create a booking or consultation form to start collecting enquiries.</div>
        </div>
    </div>
    @else

    {{-- Desktop Table --}}
    <div class="krd-card" style="padding:0;overflow:hidden;" id="forms-desktop">
        <div class="krd-table-wrap">
            <table class="krd-table">
                <thead>
                    <tr>
                        <th>Form</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Submissions</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($forms as $form)
                    <tr>
                        <td>
                            <div style="font-size:13px;font-weight:500;color:#1C1917;">{{ $form->name }}</div>
                            <div style="font-size:11px;color:#A8A29E;font-family:monospace;margin-top:2px;">{{ $form->slug }}</div>
                        </td>
                        <td>
                            <span class="krd-badge {{ $form->type === 'consultation' ? 'krd-badge-violet' : 'krd-badge-blue' }}">
                                {{ ucfirst($form->type) }}
                            </span>
                        </td>
                        <td>
                            <span class="krd-badge {{ $form->status === 'active' ? 'krd-badge-green' : 'krd-badge-stone' }}">
                                {{ ucfirst($form->status) }}
                            </span>
                        </td>
                        <td style="font-size:13px;color:#57534E;">{{ $form->submissions_count }}</td>
                        <td style="font-size:12px;color:#78716C;">{{ $form->created_at->format('M d, Y') }}</td>
                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <a href="{{ $form->publicUrl() }}" target="_blank"
                                    class="krd-btn krd-btn-ghost krd-btn-sm">↗</a>
                                <a href="{{ route('tenant.forms.submissions', $form->id) }}"
                                    wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">
                                    Submissions
                                </a>
                                <a href="{{ route('tenant.forms.edit', $form->id) }}"
                                    wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">
                                    Edit
                                </a>
                                <button wire:click="toggleStatus({{ $form->id }})"
                                    class="krd-btn krd-btn-sm"
                                    style="background:{{ $form->status === 'active' ? '#FEE2E2' : '#D1FAE5' }};color:{{ $form->status === 'active' ? '#DC2626' : '#059669' }};border-color:{{ $form->status === 'active' ? '#FECACA' : '#6EE7B7' }};">
                                    {{ $form->status === 'active' ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button wire:click="confirmDelete({{ $form->id }}, '{{ $form->name }}')"
                                    class="krd-btn krd-btn-sm"
                                    style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile Cards --}}
    <div id="forms-mobile" style="display:flex;flex-direction:column;gap:10px;">
        @foreach($forms as $form)
        <div class="krd-card" style="padding:16px;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:10px;">
                <div>
                    <div style="font-size:14px;font-weight:600;color:#1C1917;">{{ $form->name }}</div>
                    <div style="font-size:11px;color:#A8A29E;margin-top:2px;">{{ $form->submissions_count }} submissions</div>
                </div>
                <div style="display:flex;gap:6px;flex-shrink:0;">
                    <span class="krd-badge {{ $form->type === 'consultation' ? 'krd-badge-violet' : 'krd-badge-blue' }}">
                        {{ ucfirst($form->type) }}
                    </span>
                    <span class="krd-badge {{ $form->status === 'active' ? 'krd-badge-green' : 'krd-badge-stone' }}">
                        {{ ucfirst($form->status) }}
                    </span>
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="{{ route('tenant.forms.submissions', $form->id) }}" wire:navigate
                    class="krd-btn krd-btn-secondary krd-btn-sm">Submissions</a>
                <a href="{{ route('tenant.forms.edit', $form->id) }}" wire:navigate
                    class="krd-btn krd-btn-secondary krd-btn-sm">Edit</a>
                <button wire:click="confirmDelete({{ $form->id }}, '{{ $form->name }}')"
                    class="krd-btn krd-btn-sm"
                    style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">Delete</button>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Delete Modal --}}
    @if($showDeleteModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Delete "{{ $deleteName }}"?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">
                This will permanently delete this form and all its submissions.
            </p>
            <div style="display:flex;gap:10px;">
                <button wire:click="deleteForm" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Delete</button>
                <button wire:click="$set('showDeleteModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
@media (min-width: 768px) {
    #forms-desktop { display: block !important; }
    #forms-mobile  { display: none !important; }
}
@media (max-width: 767px) {
    #forms-desktop { display: none !important; }
    #forms-mobile  { display: flex !important; }
}
</style>