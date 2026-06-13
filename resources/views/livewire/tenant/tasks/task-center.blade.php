<div x-data="{ activeView: '{{ $view }}' }">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <div class="krd-label" style="margin-bottom:4px;">Operations</div>
            <h2 class="krd-heading-3" style="color:#1C1917;">Task Center</h2>
        </div>
        <a href="{{ route('tenant.tasks.create') }}" wire:navigate class="krd-btn krd-btn-primary">
            + New Task
        </a>
    </div>

    {{-- View Tabs --}}
    <div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:1px solid #E7E5E4;flex-wrap:wrap;">
        @foreach([
            ['key' => 'all',     'label' => 'All Tasks',    'count' => $allCount],
            ['key' => 'event',   'label' => 'Event Tasks',  'count' => $eventCount],
            ['key' => 'company', 'label' => 'Company Tasks','count' => $companyCount],
            ['key' => 'mine',    'label' => 'My Tasks',     'count' => $mineCount],
        ] as $tab)
        <button
            type="button"
            x-on:click="activeView = '{{ $tab['key'] }}'; $wire.setView('{{ $tab['key'] }}')"
            :style="activeView === '{{ $tab['key'] }}'
                ? 'padding:10px 16px;font-size:13px;font-weight:500;border:none;background:none;cursor:pointer;border-bottom:2px solid #7C3AED;color:#7C3AED;margin-bottom:-1px;display:flex;align-items:center;gap:6px;'
                : 'padding:10px 16px;font-size:13px;font-weight:500;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;color:#78716C;margin-bottom:-1px;display:flex;align-items:center;gap:6px;'"
        >
            {{ $tab['label'] }}
            <span
                :style="activeView === '{{ $tab['key'] }}'
                    ? 'font-size:10px;font-weight:600;background:#EDE9FE;color:#7C3AED;padding:1px 6px;border-radius:10px;'
                    : 'font-size:10px;font-weight:600;background:#F5F5F4;color:#A8A29E;padding:1px 6px;border-radius:10px;'"
            >{{ $tab['count'] }}</span>
        </button>
        @endforeach

        @if($overdueCount > 0)
        <button
            type="button"
            wire:click="$set('view', 'all')"
            x-on:click="activeView = 'all'"
            style="padding:10px 16px;font-size:13px;font-weight:500;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;color:#EF4444;margin-bottom:-1px;display:flex;align-items:center;gap:6px;"
        >
            Overdue
            <span style="font-size:10px;font-weight:600;background:#FEE2E2;color:#DC2626;padding:1px 6px;border-radius:10px;">{{ $overdueCount }}</span>
        </button>
        @endif
    </div>

    {{-- Filters --}}
    <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:center;">
        <input
            wire:model.live.debounce.300ms="search"
            type="text"
            class="krd-input"
            placeholder="Search tasks..."
            style="max-width:220px;"
        />

        {{-- Status Filter --}}
        <x-ui.dropdown
            wire="statusFilter"
            placeholder="All statuses"
            selected="{{ $statusFilter ? (collect($statuses)->firstWhere('value', $statusFilter)?->label() ?? 'All statuses') : 'All statuses' }}"
            max-width="160px"
        >
            @foreach($statuses as $status)
            <div class="krd-dropdown-option {{ $statusFilter === $status->value ? 'selected' : '' }}"
                x-on:click="select('{{ $status->label() }}', '{{ $status->value }}')">
                <span style="width:8px;height:8px;border-radius:50%;background:{{ $status->color() }};flex-shrink:0;display:inline-block;"></span>
                {{ $status->label() }}
            </div>
            @endforeach
        </x-ui.dropdown>

        {{-- Priority Filter --}}
        <x-ui.dropdown
            wire="priorityFilter"
            placeholder="All priorities"
            selected="{{ $priorityFilter ? (collect($priorities)->firstWhere('value', $priorityFilter)?->label() ?? 'All priorities') : 'All priorities' }}"
            max-width="160px"
        >
            @foreach($priorities as $priority)
            <div class="krd-dropdown-option {{ $priorityFilter === $priority->value ? 'selected' : '' }}"
                x-on:click="select('{{ $priority->label() }}', '{{ $priority->value }}')">
                <span style="width:8px;height:8px;border-radius:50%;background:{{ $priority->color() }};flex-shrink:0;display:inline-block;"></span>
                {{ $priority->label() }}
            </div>
            @endforeach
        </x-ui.dropdown>

        {{-- Event Filter --}}
        @if($view !== 'company')
        <x-ui.dropdown
            wire="eventFilter"
            placeholder="All events"
            selected="{{ $eventFilter ? ($events->firstWhere('id', (int)$eventFilter)?->name ?? 'All events') : 'All events' }}"
            max-width="200px"
        >
            @foreach($events as $event)
            <div class="krd-dropdown-option {{ $eventFilter == $event->id ? 'selected' : '' }}"
                x-on:click="select('{{ $event->name }}', {{ $event->id }})">
                {{ $event->name }}
            </div>
            @endforeach
        </x-ui.dropdown>
        @endif

        {{-- Loading indicator --}}
        <div wire:loading.flex style="display:none;align-items:center;gap:6px;font-size:12px;color:#A8A29E;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="animation:krd-spin 1s linear infinite;">
                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
            </svg>
            Loading...
        </div>

        {{-- Clear filters --}}
        @if($search || $statusFilter || $priorityFilter || $eventFilter)
        <button
            wire:click="$set('search', ''); $set('statusFilter', ''); $set('priorityFilter', ''); $set('eventFilter', '')"
            class="krd-btn krd-btn-ghost krd-btn-sm"
            style="color:#EF4444;"
        >
            Clear filters
        </button>
        @endif
    </div>

    {{-- Task List --}}
<div class="krd-card" style="padding:0;overflow:visible;" wire:loading.class="krd-loading-dim">

    @forelse($tasks as $task)
    @php $isOverdue = $task->isOverdue(); @endphp

    <div
        x-data="{
            open: false,
            status: '{{ $task->status->value }}',
            statusColor: '{{ $task->status->color() }}'
        }"
        x-on:click.outside="open = false"
        class="krd-task-list-row"
        :class="(status === 'done' || status === 'cancelled') ? 'krd-task-faded' : ''"
    >

       {{-- Status Checkbox --}}
    <div style="position:relative;flex-shrink:0;align-self:flex-start;margin-top:2px;">
        <button
            type="button"
            x-on:click.stop="open = !open"
            :style="`width:18px;height:18px;border-radius:4px;border:2px solid ${statusColor};background:${status === 'done' ? statusColor : 'transparent'};cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;`"
        >
            <template x-if="status === 'done'">
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
            </template>
            <template x-if="status === 'in_progress'">
                <svg xmlns="http://www.w3.org/2000/svg" width="7" height="7" viewBox="0 0 24 24" fill="#3B82F6"><circle cx="12" cy="12" r="8"/></svg>
            </template>
            <template x-if="status === 'blocked'">
                <svg xmlns="http://www.w3.org/2000/svg" width="7" height="7" viewBox="0 0 24 24" fill="#EF4444"><rect x="5" y="5" width="14" height="14" rx="2"/></svg>
            </template>
        </button>

        {{-- Status Picker Dropdown --}}
        <div
            x-show="open"
            x-transition
            x-on:click.outside="open = false"
            style="position:absolute;top:24px;left:0;z-index:999;background:#fff;border:1px solid #E7E5E4;border-radius:6px;min-width:160px;box-shadow:0 4px 16px rgba(0,0,0,0.12);"
        >
            @foreach(\App\Enums\TaskStatus::cases() as $s)
            <button
                type="button"
                x-on:click.stop="
                    status = '{{ $s->value }}';
                    statusColor = '{{ $s->color() }}';
                    open = false;
                    $wire.updateStatus({{ $task->id }}, '{{ $s->value }}');
                "
                class="krd-status-picker-btn"
                :class="status === '{{ $s->value }}' ? 'krd-status-picker-btn--active' : ''"
            >
                <span style="width:8px;height:8px;border-radius:50%;background:{{ $s->color() }};flex-shrink:0;display:inline-block;"></span>
                {{ $s->label() }}
                <svg
                    x-show="status === '{{ $s->value }}'"
                    xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="#7C3AED" stroke-width="2.5" style="margin-left:auto;flex-shrink:0;"
                ><path d="M20 6L9 17l-5-5"/></svg>
            </button>
            @endforeach
        </div>
    </div>

        {{-- Task Content --}}
        <div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:4px;">

            {{-- Title + badges --}}
            <div style="display:flex;align-items:flex-start;gap:8px;flex-wrap:wrap;">
                <span
                    style="font-size:13px;font-weight:500;color:#1C1917;line-height:1.4;"
                    :style="(status === 'done' || status === 'cancelled') ? 'text-decoration:line-through;color:#A8A29E;' : ''"
                >{{ $task->title }}</span>

                <span class="krd-badge {{ $task->priority->badgeClass() }}" style="font-size:10px;flex-shrink:0;">
                    {{ $task->priority->label() }}
                </span>

                <span
                    class="krd-badge {{ $task->status->badgeClass() }}"
                    style="font-size:10px;flex-shrink:0;"
                    x-show="status !== 'todo'"
                    x-text="
                        status === 'in_progress' ? 'In Progress' :
                        status === 'blocked' ? 'Blocked' :
                        status === 'done' ? 'Done' :
                        status === 'cancelled' ? 'Cancelled' : ''
                    "
                ></span>

                @if($isOverdue)
                <span
                    class="krd-badge krd-badge-red"
                    style="font-size:10px;flex-shrink:0;"
                    x-show="status !== 'done' && status !== 'cancelled'"
                >Overdue</span>
                @endif
            </div>

            {{-- Meta row --}}
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                @if($task->event)
                <span style="font-size:11px;color:#78716C;display:flex;align-items:center;gap:4px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    {{ Str::limit($task->event->name, 28) }}
                </span>
                @else
                <span style="font-size:11px;color:#A8A29E;display:flex;align-items:center;gap:4px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                    Company Task
                </span>
                @endif

                @if($task->due_date)
                <span style="font-size:11px;color:{{ $isOverdue ? '#EF4444' : '#78716C' }};display:flex;align-items:center;gap:4px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    {{ $task->due_date->format('M d, Y') }}
                </span>
                @endif

                @if($task->assignedTo)
                <span style="font-size:11px;color:#78716C;display:flex;align-items:center;gap:4px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    {{ $task->assignedTo->name }}
                </span>
                @endif

                @if($task->category)
                <span style="font-size:11px;color:#A8A29E;">{{ $task->category->name }}</span>
                @endif
            </div>

            {{-- Mobile-only actions (shown below meta on small screens) --}}
            <div class="krd-task-actions-mobile">
                <a href="{{ route('tenant.tasks.edit', $task->id) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">Edit</a>
                <button wire:click="confirmDelete({{ $task->id }})" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4h6v2"/>
                    </svg>
                </button>
            </div>

        </div>

        {{-- Desktop-only actions (right side) --}}
        <div class="krd-task-actions-desktop">
            <a href="{{ route('tenant.tasks.edit', $task->id) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">Edit</a>
            <button wire:click="confirmDelete({{ $task->id }})" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4h6v2"/>
                </svg>
            </button>
        </div>

    </div>
    @empty
    <div class="krd-empty-state">
        <div class="krd-empty-state-icon">✅</div>
        <div class="krd-empty-state-title">No tasks found</div>
        <div class="krd-empty-state-desc">
            {{ $search ? 'Try a different search term.' : 'Create your first task to get started.' }}
        </div>
    </div>
    @endforelse

    @if($tasks->hasPages())
    <div style="padding:12px 16px;border-top:1px solid #E7E5E4;">
        {{ $tasks->links() }}
    </div>
    @endif
</div>
    {{-- Delete Modal --}}
    @if($showDeleteModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:8px;padding:28px;max-width:400px;width:90%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Delete Task?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">
                This will permanently delete this task. This cannot be undone.
            </p>
            <div style="display:flex;gap:10px;">
                <button wire:click="delete" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Delete</button>
                <button wire:click="cancelDelete" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif

</div>