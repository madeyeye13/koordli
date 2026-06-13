<div x-data="{ view: '{{ $view }}' }">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <div class="krd-label" style="margin-bottom:4px;">Operations</div>
            <h2 class="krd-heading-3" style="color:#1C1917;">Events</h2>
        </div>
        <a href="{{ route('tenant.events.create') }}" wire:navigate class="krd-btn krd-btn-primary">
            + New Event
        </a>
    </div>

    {{-- Filters + View Toggle --}}
    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
        <div style="display:flex;gap:10px;flex-wrap:wrap;flex:1;">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                class="krd-input"
                placeholder="Search events..."
                style="max-width:240px;"
            />

            {{-- Status Filter --}}
            <x-ui.dropdown
                wire="statusFilter"
                placeholder="All statuses"
                selected="{{ $statusFilter ? ($statuses->firstWhere('id', (int)$statusFilter)?->name ?? 'All statuses') : 'All statuses' }}"
                max-width="180px"
            >
                @foreach($statuses as $status)
                <div
                    class="krd-dropdown-option {{ $statusFilter == $status->id ? 'selected' : '' }}"
                    x-on:click="select('{{ $status->name }}', '{{ $status->id }}')"
                >
                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $status->color }};flex-shrink:0;display:inline-block;"></span>
                    {{ $status->name }}
                </div>
                @endforeach
            </x-ui.dropdown>

            {{-- Type Filter --}}
            <x-ui.dropdown
                wire="typeFilter"
                placeholder="All types"
                selected="{{ $typeFilter ? ($types->firstWhere('id', (int)$typeFilter)?->name ?? 'All types') : 'All types' }}"
                max-width="180px"
            >
                @foreach($types as $type)
                <div
                    class="krd-dropdown-option {{ $typeFilter == $type->id ? 'selected' : '' }}"
                    x-on:click="select('{{ $type->name }}', '{{ $type->id }}')"
                >
                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $type->color }};flex-shrink:0;display:inline-block;"></span>
                    {{ $type->name }}
                </div>
                @endforeach
            </x-ui.dropdown>
        </div>

        {{-- View Toggle --}}
        <div style="display:flex;border:1px solid #E7E5E4;border-radius:6px;overflow:hidden;flex-shrink:0;">
            <button
                type="button"
                x-on:click="view = 'list'; $wire.setView('list')"
                :style="view === 'list'
                    ? 'padding:7px 12px;background:#7C3AED;color:#fff;border:none;cursor:pointer;display:flex;align-items:center;'
                    : 'padding:7px 12px;background:#fff;color:#78716C;border:none;cursor:pointer;display:flex;align-items:center;'"
                title="List view"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
                    <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
            </button>
            <button
                type="button"
                x-on:click="view = 'grid'; $wire.setView('grid')"
                :style="view === 'grid'
                    ? 'padding:7px 12px;background:#7C3AED;color:#fff;border:none;cursor:pointer;display:flex;align-items:center;border-left:1px solid #E7E5E4;'
                    : 'padding:7px 12px;background:#fff;color:#78716C;border:none;cursor:pointer;display:flex;align-items:center;border-left:1px solid #E7E5E4;'"
                title="Grid view"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- List View --}}
    <div x-show="view === 'list'">

        {{-- Desktop Table --}}
        <div class="krd-card" style="padding:0;overflow:hidden;" id="event-list-desktop">
            <div class="krd-table-wrap">
                <table class="krd-table">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                        <tr>
                            <td>
                                <a href="{{ route('tenant.events.show', $event->slug) }}" wire:navigate style="text-decoration:none;">
                                    <div style="font-weight:500;color:#1C1917;">{{ $event->name }}</div>
                                </a>
                                <div style="font-size:11px;color:#A8A29E;font-family:monospace;">{{ $event->slug }}</div>
                            </td>
                            <td>
                                @if($event->eventType)
                                <span style="display:inline-flex;align-items:center;gap:5px;font-size:12px;color:#57534E;">
                                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $event->eventType->color ?? '#A8A29E' }};flex-shrink:0;"></span>
                                    {{ $event->eventType->name }}
                                </span>
                                @else
                                <span style="font-size:12px;color:#A8A29E;">—</span>
                                @endif
                            </td>
                            <td style="font-size:12px;color:#57534E;white-space:nowrap;">
                                {{ $event->date ? $event->date->format('M d, Y') : '—' }}
                            </td>
                            <td style="font-size:12px;color:#57534E;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                {{ $event->venue ?? '—' }}
                            </td>
                            <td>
                                @if($event->status)
                                <span class="krd-badge" style="background:{{ $event->status->color }}22;color:{{ $event->status->color }};">
                                    {{ $event->status->name }}
                                </span>
                                @else
                                <span class="krd-badge krd-badge-stone">No Status</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <a href="{{ route('tenant.events.show', $event->slug) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">View</a>
                                    <a href="{{ route('tenant.events.edit', $event->slug) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">Edit</a>
                                    <button wire:click="confirmDelete({{ $event->id }})" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">Delete</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">
                                <div class="krd-empty-state">
                                    <div class="krd-empty-state-icon">📋</div>
                                    <div class="krd-empty-state-title">No events found</div>
                                    <div class="krd-empty-state-desc">{{ $search ? 'Try a different search term.' : 'Create your first event to get started.' }}</div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($events->hasPages())
            <div style="padding:12px 16px;border-top:1px solid #E7E5E4;">{{ $events->links() }}</div>
            @endif
        </div>

        {{-- Mobile Cards --}}
        <div id="event-list-mobile" style="display:flex;flex-direction:column;gap:10px;">
            @forelse($events as $event)
            <div class="krd-card" style="padding:0;overflow:hidden;">
                <div style="height:3px;background:{{ $event->eventType->color ?? '#7C3AED' }};"></div>
                <div style="padding:14px 16px;">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:8px;">
                        <a href="{{ route('tenant.events.show', $event->slug) }}" wire:navigate style="text-decoration:none;flex:1;min-width:0;">
                            <div style="font-size:13px;font-weight:600;color:#1C1917;line-height:1.3;">{{ $event->name }}</div>
                        </a>
                        @if($event->status)
                        <span class="krd-badge" style="background:{{ $event->status->color }}22;color:{{ $event->status->color }};font-size:10px;flex-shrink:0;">
                            {{ $event->status->name }}
                        </span>
                        @endif
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
                        @if($event->eventType)
                        <span style="font-size:11px;color:#78716C;display:flex;align-items:center;gap:4px;">
                            <span style="width:7px;height:7px;border-radius:50%;background:{{ $event->eventType->color }};"></span>
                            {{ $event->eventType->name }}
                        </span>
                        @endif
                        @if($event->date)
                        <span style="font-size:11px;color:#78716C;">📅 {{ $event->date->format('M d, Y') }}</span>
                        @endif
                        @if($event->venue)
                        <span style="font-size:11px;color:#78716C;">📍 {{ Str::limit($event->venue, 25) }}</span>
                        @endif
                    </div>
                    <div style="display:flex;gap:8px;">
                        <a href="{{ route('tenant.events.show', $event->slug) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">View</a>
                        <a href="{{ route('tenant.events.edit', $event->slug) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">Edit</a>
                        <button wire:click="confirmDelete({{ $event->id }})" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">Delete</button>
                    </div>
                </div>
            </div>
            @empty
            <div class="krd-card">
                <div class="krd-empty-state">
                    <div class="krd-empty-state-icon">📋</div>
                    <div class="krd-empty-state-title">No events found</div>
                    <div class="krd-empty-state-desc">{{ $search ? 'Try a different search term.' : 'Create your first event to get started.' }}</div>
                </div>
            </div>
            @endforelse
            @if($events->hasPages())
            <div style="margin-top:8px;">{{ $events->links() }}</div>
            @endif
        </div>

    </div>

    {{-- Grid View --}}
    <div x-show="view === 'grid'">
        @if($events->isEmpty())
        <div class="krd-card">
            <div class="krd-empty-state">
                <div class="krd-empty-state-icon">📋</div>
                <div class="krd-empty-state-title">No events found</div>
                <div class="krd-empty-state-desc">Create your first event to get started.</div>
            </div>
        </div>
        @else
        <div class="krd-grid-3" style="margin-bottom:16px;">
            @foreach($events as $event)
            <div class="krd-card" style="padding:0;overflow:hidden;display:flex;flex-direction:column;">

                {{-- Color bar --}}
                <div style="height:4px;background:{{ $event->eventType->color ?? '#7C3AED' }};"></div>

                <div style="padding:16px;flex:1;">
                    {{-- Status + Type --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                        @if($event->status)
                        <span class="krd-badge" style="background:{{ $event->status->color }}22;color:{{ $event->status->color }};font-size:10px;">
                            {{ $event->status->name }}
                        </span>
                        @else
                        <span class="krd-badge krd-badge-stone" style="font-size:10px;">No Status</span>
                        @endif
                        @if($event->eventType)
                        <span style="font-size:11px;color:#A8A29E;">{{ $event->eventType->name }}</span>
                        @endif
                    </div>

                    {{-- Name --}}
                    <a href="{{ route('tenant.events.show', $event->slug) }}" wire:navigate style="text-decoration:none;">
                        <div style="font-size:15px;font-weight:600;color:#1C1917;margin-bottom:8px;line-height:1.3;">
                            {{ $event->name }}
                        </div>
                    </a>

                    {{-- Date + Venue --}}
                    <div style="display:flex;flex-direction:column;gap:5px;">
                        <div style="display:flex;align-items:center;gap:7px;font-size:12px;color:#78716C;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            {{ $event->date ? $event->date->format('M d, Y') : 'Date TBC' }}
                        </div>
                        @if($event->venue)
                        <div style="display:flex;align-items:center;gap:7px;font-size:12px;color:#78716C;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
                            </svg>
                            {{ Str::limit($event->venue, 30) }}
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div style="padding:12px 16px;border-top:1px solid #E7E5E4;display:flex;gap:8px;">
                    <a href="{{ route('tenant.events.show', $event->slug) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm" style="flex:1;justify-content:center;">
                        View
                    </a>
                    <a href="{{ route('tenant.events.edit', $event->slug) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">
                        Edit
                    </a>
                    <button
                        wire:click="confirmDelete({{ $event->id }})"
                        class="krd-btn krd-btn-sm"
                        style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                            <path d="M10 11v6M14 11v6M9 6V4h6v2"/>
                        </svg>
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        @if($events->hasPages())
        <div style="margin-top:8px;">{{ $events->links() }}</div>
        @endif
        @endif
    </div>

    {{-- Delete Modal --}}
    @if($showDeleteModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:8px;padding:28px;max-width:400px;width:90%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Delete Event?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">
                This will permanently delete this event and all associated data including tasks, guests, and budget items. This cannot be undone.
            </p>
            <div style="display:flex;gap:10px;">
                <button wire:click="delete" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Delete</button>
                <button wire:click="cancelDelete" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif

</div>

<style>
@media (min-width: 768px) {
    #event-list-desktop { display: block !important; }
    #event-list-mobile  { display: none !important; }
}
@media (max-width: 767px) {
    #event-list-desktop { display: none !important; }
    #event-list-mobile  { display: flex !important; }
}
</style>