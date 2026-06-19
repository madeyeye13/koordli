<div>
    {{-- Header --}}
    <div style="margin-bottom:24px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('tenant.events.show', $event->slug) }}" wire:navigate
                style="color:#A8A29E;text-decoration:none;font-size:13px;">
                ← Back to {{ Str::limit($event->name, 30) }}
            </a>
        </div>
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <div class="krd-label" style="margin-bottom:4px;">Guest Management</div>
                <h2 class="krd-heading-3" style="color:#1C1917;">{{ $event->name }}</h2>
            </div>
            <button wire:click="toggleAddForm" class="krd-btn krd-btn-primary">
                {{ $showAddForm ? '✕ Cancel' : '+ Add Guest' }}
            </button>
        </div>
    </div>

    {{-- Stats Strip --}}
    <div class="krd-grid-4" style="margin-bottom:20px;gap:10px;">
        <div class="krd-card" style="padding:14px;border-left:3px solid #7C3AED;">
            <div class="krd-label" style="margin-bottom:4px;">Total Guests</div>
            <div style="font-size:24px;font-weight:700;color:#7C3AED;">{{ $stats['total'] }}</div>
        </div>
        <div class="krd-card" style="padding:14px;border-left:3px solid #10B981;">
            <div class="krd-label" style="margin-bottom:4px;">Confirmed</div>
            <div style="font-size:24px;font-weight:700;color:#10B981;">{{ $stats['confirmed'] }}</div>
        </div>
        <div class="krd-card" style="padding:14px;border-left:3px solid #EF4444;">
            <div class="krd-label" style="margin-bottom:4px;">Declined</div>
            <div style="font-size:24px;font-weight:700;color:#EF4444;">{{ $stats['declined'] }}</div>
        </div>
        <div class="krd-card" style="padding:14px;border-left:3px solid #F59E0B;">
            <div class="krd-label" style="margin-bottom:4px;">Pending</div>
            <div style="font-size:24px;font-weight:700;color:#F59E0B;">{{ $stats['pending'] }}</div>
        </div>
    </div>

    {{-- Expected Guest Count --}}
    <div class="krd-card" style="padding:16px;margin-bottom:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <div class="krd-label" style="margin-bottom:4px;">Expected Guest Count</div>
                @if(!$editingCount)
                <div style="font-size:22px;font-weight:700;color:#1C1917;">
                    {{ $event->max_guests ? number_format($event->max_guests) : '—' }}
                    <span style="font-size:13px;font-weight:400;color:#A8A29E;">expected</span>
                </div>
                @endif
            </div>
            @if(!$editingCount)
            <button wire:click="$set('editingCount', true)" class="krd-btn krd-btn-secondary krd-btn-sm">
                {{ $event->max_guests ? 'Update Count' : 'Set Expected Count' }}
            </button>
            @endif
        </div>

        @if($editingCount)
        <div style="display:flex;align-items:center;gap:10px;margin-top:12px;flex-wrap:wrap;">
            <input wire:model="expectedGuests" type="number" min="0"
                class="krd-input" placeholder="e.g. 200"
                style="max-width:160px;" />
            <button wire:click="saveGuestCount" wire:loading.attr="disabled"
                class="krd-btn krd-btn-primary krd-btn-sm">
                <span wire:loading.remove wire:target="saveGuestCount">Save</span>
                <span wire:loading wire:target="saveGuestCount">Saving...</span>
            </button>
            <button wire:click="$set('editingCount', false)" class="krd-btn krd-btn-ghost krd-btn-sm">Cancel</button>
        </div>
        @endif

        @if($event->max_guests && $stats['total'] > 0)
        <div style="margin-top:14px;">
            @php $pct = min(100, round(($stats['total'] / $event->max_guests) * 100)); @endphp
            <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
                <span style="font-size:11px;color:#57534E;">{{ $stats['total'] }} of {{ number_format($event->max_guests) }} guests added</span>
                <span style="font-size:11px;font-weight:600;color:#7C3AED;">{{ $pct }}%</span>
            </div>
            <div style="height:6px;background:#E7E5E4;border-radius:4px;overflow:hidden;">
                <div style="height:100%;width:{{ $pct }}%;background:#7C3AED;border-radius:4px;transition:width 400ms ease;"></div>
            </div>
        </div>
        @endif
    </div>

    {{-- Add Guest Form --}}
    @if($showAddForm)
    <div class="krd-card" style="padding:20px;margin-bottom:16px;border:2px solid #7C3AED;">
        <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:16px;">Add Guest</div>
        <div class="krd-grid-2" style="gap:12px;">
            <div class="krd-input-group">
                <label class="krd-label-text">Full Name <span style="color:#EF4444;">*</span></label>
                <input wire:model="name" type="text"
                    class="krd-input @error('name') krd-input-error @enderror"
                    placeholder="Guest name" autofocus />
                @error('name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>
            <div class="krd-input-group">
                <label class="krd-label-text">Category</label>
                <input wire:model="category" type="text" class="krd-input"
                    placeholder="e.g. Family, VIP, Colleague" />
            </div>
            <div class="krd-input-group">
                <label class="krd-label-text">Email</label>
                <input wire:model="email" type="email"
                    class="krd-input @error('email') krd-input-error @enderror"
                    placeholder="guest@email.com" />
                @error('email') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>
            <div class="krd-input-group">
                <label class="krd-label-text">Phone</label>
                <input wire:model="phone" type="text" class="krd-input" placeholder="+234..." />
            </div>
        </div>
        <div class="krd-input-group">
            <label class="krd-label-text">Notes</label>
            <input wire:model="notes" type="text" class="krd-input" placeholder="Any special notes..." />
        </div>
        <div style="display:flex;gap:10px;margin-top:4px;">
            <button wire:click="addGuest" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                <span wire:loading.remove wire:target="addGuest">Add Guest</span>
                <span wire:loading wire:target="addGuest">Adding...</span>
            </button>
            <button wire:click="toggleAddForm" type="button" class="krd-btn krd-btn-ghost">Cancel</button>
        </div>
    </div>
    @endif

    {{-- Filters --}}
    <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:flex-start;">
        <input wire:model.live.debounce.300ms="search" type="text" class="krd-input"
            placeholder="Search guests..." style="max-width:220px;" />

        {{-- Status filter dropdown --}}
        <div x-data="{
                open: false,
                label: '{{ $statusFilter ? ucfirst($statusFilter) : 'All statuses' }}',
                pick(val, label) {
                    this.label = label;
                    this.open  = false;
                    $wire.set('statusFilter', val);
                }
            }"
            x-on:click.outside="open = false"
            style="position:relative;min-width:150px;">
            <button type="button"
                x-on:click="open = !open"
                x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'"
                style="width:100%;">
                <span x-text="label"></span>
                <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </button>
            <div x-show="open" x-cloak class="krd-dropdown-menu">
                <div class="krd-dropdown-option {{ !$statusFilter ? 'selected' : '' }}"
                    x-on:click="pick('', 'All statuses')">All statuses</div>
                <div class="krd-dropdown-option {{ $statusFilter === 'pending' ? 'selected' : '' }}"
                    x-on:click="pick('pending', 'Pending')">Pending</div>
                <div class="krd-dropdown-option {{ $statusFilter === 'confirmed' ? 'selected' : '' }}"
                    x-on:click="pick('confirmed', 'Confirmed')">Confirmed</div>
                <div class="krd-dropdown-option {{ $statusFilter === 'declined' ? 'selected' : '' }}"
                    x-on:click="pick('declined', 'Declined')">Declined</div>
            </div>
        </div>

        {{-- Category filter dropdown --}}
        @if($categories->isNotEmpty())
        <div x-data="{
                open: false,
                label: '{{ $categoryFilter ?: 'All categories' }}',
                pick(val, label) {
                    this.label = label;
                    this.open  = false;
                    $wire.set('categoryFilter', val);
                }
            }"
            x-on:click.outside="open = false"
            style="position:relative;min-width:160px;">
            <button type="button"
                x-on:click="open = !open"
                x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'"
                style="width:100%;">
                <span x-text="label"></span>
                <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </button>
            <div x-show="open" x-cloak class="krd-dropdown-menu">
                <div class="krd-dropdown-option {{ !$categoryFilter ? 'selected' : '' }}"
                    x-on:click="pick('', 'All categories')">All categories</div>
                @foreach($categories as $cat)
                <div class="krd-dropdown-option {{ $categoryFilter === $cat ? 'selected' : '' }}"
                    x-on:click="pick('{{ $cat }}', '{{ $cat }}')">{{ $cat }}</div>
                @endforeach
            </div>
        </div>
        @endif

        @if($search || $statusFilter || $categoryFilter)
        <button wire:click="$set('search','');$set('statusFilter','');$set('categoryFilter','')"
            class="krd-btn krd-btn-ghost krd-btn-sm" style="color:#EF4444;align-self:center;">Clear</button>
        @endif
    </div>

    
    {{-- Guest List --}}
    @if($guests->isEmpty())
    <div class="krd-card">
        <div class="krd-empty-state">
            <div class="krd-empty-state-icon">👥</div>
            <div class="krd-empty-state-title">No guests yet</div>
            <div class="krd-empty-state-desc">Add guests individually or set an expected guest count above.</div>
        </div>
    </div>
    @else

    {{-- Desktop Table --}}
    <div class="krd-card" style="padding:0;overflow:hidden;" id="guest-list-desktop">
        <div class="krd-table-wrap">
            <table class="krd-table">
                <thead>
                    <tr>
                        <th>Guest</th>
                        <th>Category</th>
                        <th>Contact</th>
                        <th>RSVP</th>
                        <th>Check-in</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($guests as $guest)
                    @if($editId === $guest->id)
                    <tr style="background:#F5F3FF;">
                        <td><input wire:model="editName" type="text" class="krd-input" style="min-width:140px;" /></td>
                        <td><input wire:model="editCategory" type="text" class="krd-input" placeholder="Category" style="min-width:100px;" /></td>
                        <td>
                            <input wire:model="editEmail" type="email" class="krd-input" placeholder="Email" style="margin-bottom:4px;" />
                            <input wire:model="editPhone" type="text" class="krd-input" placeholder="Phone" />
                        </td>
                        <td></td>
                        <td></td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <button wire:click="saveEdit" class="krd-btn krd-btn-primary krd-btn-sm">Save</button>
                                <button wire:click="cancelEdit" class="krd-btn krd-btn-ghost krd-btn-sm">Cancel</button>
                            </div>
                        </td>
                    </tr>
                    @else
                    <tr>
                        <td>
                            <div style="font-size:13px;font-weight:500;color:#1C1917;">{{ $guest->name }}</div>
                            @if($guest->notes)
                            <div style="font-size:11px;color:#A8A29E;">{{ Str::limit($guest->notes, 50) }}</div>
                            @endif
                        </td>
                        <td>
                            @if($guest->category)
                            <span class="krd-badge krd-badge-violet" style="font-size:10px;">{{ $guest->category }}</span>
                            @else
                            <span style="color:#A8A29E;font-size:12px;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($guest->email)
                            <div style="font-size:12px;color:#57534E;">{{ $guest->email }}</div>
                            @endif
                            @if($guest->phone)
                            <div style="font-size:12px;color:#A8A29E;">{{ $guest->phone }}</div>
                            @endif
                            @if(!$guest->email && !$guest->phone)
                            <span style="color:#A8A29E;font-size:12px;">—</span>
                            @endif
                        </td>
                        <td>
                            <div x-data="{ open: false }" style="position:relative;">
                                <button type="button"
                                    x-on:click="open = !open"
                                    class="krd-badge {{ $guest->statusBadgeClass() }}"
                                    style="cursor:pointer;border:none;font-size:11px;">
                                    {{ ucfirst($guest->rsvp_status) }} ▾
                                </button>
                                <div x-show="open" x-on:click.outside="open = false"
                                    style="position:absolute;top:calc(100% + 4px);left:0;background:#fff;border:1px solid #E7E5E4;border-radius:6px;z-index:50;min-width:130px;overflow:hidden;"
                                    x-cloak>
                                    @foreach(['pending' => 'Pending', 'confirmed' => 'Confirmed', 'declined' => 'Declined'] as $val => $label)
                                    <button type="button"
                                        wire:click="updateRsvpStatus({{ $guest->id }}, '{{ $val }}')"
                                        x-on:click="open = false"
                                        style="display:block;width:100%;padding:8px 12px;font-size:12px;text-align:left;border:none;background:{{ $guest->rsvp_status === $val ? '#F5F3FF' : '#fff' }};color:{{ $guest->rsvp_status === $val ? '#7C3AED' : '#57534E' }};cursor:pointer;">
                                        {{ $label }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                        </td>
                        <td>
                            <button wire:click="checkIn({{ $guest->id }})"
                                class="krd-btn krd-btn-sm"
                                style="background:{{ $guest->checked_in ? '#D1FAE5' : '#F5F5F4' }};color:{{ $guest->checked_in ? '#065F46' : '#57534E' }};border-color:{{ $guest->checked_in ? '#6EE7B7' : '#E7E5E4' }};">
                                {{ $guest->checked_in ? '✓ Checked In' : 'Check In' }}
                            </button>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <button wire:click="startEdit({{ $guest->id }})" class="krd-btn krd-btn-secondary krd-btn-sm">Edit</button>
                                <button wire:click="confirmDelete({{ $guest->id }})"
                                    class="krd-btn krd-btn-sm"
                                    style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                                        <path d="M10 11v6M14 11v6M9 6V4h6v2"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile Cards --}}
    <div id="guest-list-mobile" style="display:flex;flex-direction:column;gap:10px;">
        @foreach($guests as $guest)
        <div class="krd-card" style="padding:16px;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:10px;">
                <div>
                    <div style="font-size:14px;font-weight:600;color:#1C1917;">{{ $guest->name }}</div>
                    @if($guest->category)
                    <span class="krd-badge krd-badge-violet" style="font-size:10px;margin-top:4px;">{{ $guest->category }}</span>
                    @endif
                </div>
                <span class="krd-badge {{ $guest->statusBadgeClass() }}">{{ ucfirst($guest->rsvp_status) }}</span>
            </div>

            @if($guest->email || $guest->phone)
            <div style="margin-bottom:10px;">
                @if($guest->email)<div style="font-size:12px;color:#78716C;">{{ $guest->email }}</div>@endif
                @if($guest->phone)<div style="font-size:12px;color:#A8A29E;">{{ $guest->phone }}</div>@endif
            </div>
            @endif

            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button wire:click="checkIn({{ $guest->id }})"
                    class="krd-btn krd-btn-sm"
                    style="background:{{ $guest->checked_in ? '#D1FAE5' : '#F5F5F4' }};color:{{ $guest->checked_in ? '#065F46' : '#57534E' }};border-color:{{ $guest->checked_in ? '#6EE7B7' : '#E7E5E4' }};">
                    {{ $guest->checked_in ? '✓ In' : 'Check In' }}
                </button>
                <button wire:click="startEdit({{ $guest->id }})" class="krd-btn krd-btn-secondary krd-btn-sm">Edit</button>
                <button wire:click="confirmDelete({{ $guest->id }})"
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
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Remove Guest?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">
                This will permanently remove this guest from the event.
            </p>
            <div style="display:flex;gap:10px;">
                <button wire:click="deleteGuest" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Remove</button>
                <button wire:click="cancelDelete" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif

</div>

<style>
@media (min-width: 768px) {
    #guest-list-desktop { display: block !important; }
    #guest-list-mobile  { display: none !important; }
}
@media (max-width: 767px) {
    #guest-list-desktop { display: none !important; }
    #guest-list-mobile  { display: flex !important; }
}
</style>