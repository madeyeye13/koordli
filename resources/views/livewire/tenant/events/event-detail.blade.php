<div>
    {{-- Header --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
        <div>
            <div style="margin-bottom:8px;">
                <a href="{{ route('tenant.events') }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">
                    ← Back to Events
                </a>
            </div>
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <h2 class="krd-heading-3" style="color:#1C1917;">{{ $event->name }}</h2>
                @if($event->status)
                <span class="krd-badge" style="background:{{ $event->status->color }}22;color:{{ $event->status->color }};">
                    {{ $event->status->name }}
                </span>
                @endif
            </div>
            @if($event->eventType)
            <div style="font-size:12px;color:#78716C;margin-top:4px;display:flex;align-items:center;gap:6px;">
                <span style="width:8px;height:8px;border-radius:50%;background:{{ $event->eventType->color ?? '#A8A29E' }};"></span>
                {{ $event->eventType->name }}
            </div>
            @endif
        </div>

        <div style="display:flex;gap:8px;">
            <a href="{{ route('tenant.events.edit', $event->uuid) }}" wire:navigate class="krd-btn krd-btn-secondary">
                Edit Event
            </a>
        </div>
    </div>

    {{-- KPI Strip --}}
    <div class="krd-grid-4" style="margin-bottom:24px;">
        <div class="krd-card" style="text-align:center;padding:16px;">
            <div class="krd-label" style="margin-bottom:6px;">Date</div>
            <div style="font-size:18px;font-weight:700;color:#1C1917;">
                {{ $event->date ? $event->date->format('M d') : '—' }}
            </div>
            @if($event->date)
            <div style="font-size:11px;color:#A8A29E;margin-top:2px;">{{ $event->date->format('Y') }}</div>
            @endif
        </div>
        <div class="krd-card" style="text-align:center;padding:16px;">
            <div class="krd-label" style="margin-bottom:6px;">Tasks</div>
            <div style="font-size:28px;font-weight:700;color:#3B82F6;line-height:1;">{{ $event->tasks->count() }}</div>
        </div>
        <div class="krd-card" style="text-align:center;padding:16px;">
            <div class="krd-label" style="margin-bottom:6px;">Guests</div>
            <div style="font-size:28px;font-weight:700;color:#10B981;line-height:1;">{{ $event->guests->count() }}</div>
            @if($event->max_guests)
            <div style="font-size:11px;color:#A8A29E;margin-top:2px;">of {{ number_format($event->max_guests) }} max</div>
            @endif
        </div>
        <div class="krd-card" style="text-align:center;padding:16px;">
            <div class="krd-label" style="margin-bottom:6px;">Team</div>
            <div style="font-size:28px;font-weight:700;color:#7C3AED;line-height:1;">{{ $event->team->count() }}</div>
        </div>
    </div>

    <div class="krd-grid-2">

        {{-- Event Info --}}
        <div class="krd-card">
            <div class="krd-label" style="margin-bottom:16px;">Event Info</div>

            @foreach([
                ['label' => 'Event Name', 'value' => $event->name],
                ['label' => 'Type',       'value' => $event->eventType?->name ?? '—'],
                ['label' => 'Date',       'value' => $event->date?->format('l, F j, Y') ?? '—'],
                ['label' => 'Venue',      'value' => $event->venue ?? '—'],
                ['label' => 'Max Guests', 'value' => $event->max_guests ? number_format($event->max_guests) : '—'],
            ] as $info)
            <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #E7E5E4;">
                <span style="font-size:12px;color:#78716C;">{{ $info['label'] }}</span>
                <span style="font-size:12px;font-weight:500;color:#1C1917;text-align:right;max-width:60%;">{{ $info['value'] }}</span>
            </div>
            @endforeach
        </div>

        {{-- Quick Status Change --}}
        <div class="krd-card">
            <div class="krd-label" style="margin-bottom:16px;">Update Status</div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                @foreach($statuses as $status)
                <button
                    wire:click="updateStatus({{ $status->id }})"
                    style="
                        display:flex;align-items:center;gap:10px;
                        padding:10px 14px;border-radius:6px;cursor:pointer;
                        border:2px solid {{ $event->status_id === $status->id ? $status->color : '#E7E5E4' }};
                        background:{{ $event->status_id === $status->id ? $status->color . '15' : '#fff' }};
                        text-align:left;width:100%;
                        transition:all 150ms ease;
                    "
                >
                    <span style="width:10px;height:10px;border-radius:50%;background:{{ $status->color }};flex-shrink:0;"></span>
                    <span style="font-size:13px;font-weight:{{ $event->status_id === $status->id ? '600' : '400' }};color:{{ $event->status_id === $status->id ? $status->color : '#57534E' }};">
                        {{ $status->name }}
                    </span>
                    @if($event->status_id === $status->id)
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="{{ $status->color }}" stroke-width="2.5" style="margin-left:auto;">
                        <path d="M20 6L9 17l-5-5"/>
                    </svg>
                    @endif
                </button>
                @endforeach
            </div>
        </div>

    </div>

    {{-- Coming Soon Panels --}}
    <div class="krd-grid-2" style="margin-top:16px;">
        <div class="krd-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <div class="krd-label">Tasks</div>
                <a href="#" class="krd-btn krd-btn-secondary krd-btn-sm">+ Add Task</a>
            </div>
            <div class="krd-empty-state" style="padding:24px;">
                <div class="krd-empty-state-icon" style="font-size:24px;">✅</div>
                <div class="krd-empty-state-title">No tasks yet</div>
                <div class="krd-empty-state-desc">Task management coming in the next build.</div>
            </div>
        </div>
        <div class="krd-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <div class="krd-label">Guests</div>
                <a href="#" class="krd-btn krd-btn-secondary krd-btn-sm">+ Add Guest</a>
            </div>
            <div class="krd-empty-state" style="padding:24px;">
                <div class="krd-empty-state-icon" style="font-size:24px;">👥</div>
                <div class="krd-empty-state-title">No guests yet</div>
                <div class="krd-empty-state-desc">Guest management coming in the next build.</div>
            </div>
        </div>
    </div>

</div>