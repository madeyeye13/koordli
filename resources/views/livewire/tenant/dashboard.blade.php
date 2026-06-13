<div>
    {{-- Header --}}
    <div style="margin-bottom: 28px;">
        <div class="krd-label" style="margin-bottom: 6px;">
            {{ now()->format('l, F j') }}
        </div>
        <h2 class="krd-heading-3" style="color: #1C1917;">
            Welcome back, {{ explode(' ', auth()->user()->name)[0] }} 👋
        </h2>
        <p class="krd-body-sm" style="color: #78716C; margin-top: 4px;">
            Here's what's happening with {{ auth()->user()->tenant->name }}.
        </p>
    </div>

    {{-- KPI Cards --}}
    <div class="krd-grid-4" style="margin-bottom: 24px;">
        <div class="krd-card" style="text-align: center;">
            <div class="krd-label" style="margin-bottom: 8px;">Total Events</div>
            <div class="krd-stat-number" style="font-size: 36px; font-weight: 700; color: #7C3AED; line-height: 1;">
                {{ $totalEvents }}
            </div>
        </div>
        <div class="krd-card" style="text-align: center;">
            <div class="krd-label" style="margin-bottom: 8px;">Total Tasks</div>
            <div class="krd-stat-number" style="font-size: 36px; font-weight: 700; color: #3B82F6; line-height: 1;">
                {{ $totalTasks }}
            </div>
            @if($overdueTasks > 0)
            <div style="font-size: 11px; color: #EF4444; margin-top: 4px;">
                {{ $overdueTasks }} overdue
            </div>
            @endif
        </div>
        <div class="krd-card" style="text-align: center;">
            <div class="krd-label" style="margin-bottom: 8px;">Vendors</div>
            <div class="krd-stat-number" style="font-size: 36px; font-weight: 700; color: #F59E0B; line-height: 1;">
                {{ $totalVendors }}
            </div>
        </div>
        <div class="krd-card" style="text-align: center;">
            <div class="krd-label" style="margin-bottom: 8px;">Guests</div>
            <div class="krd-stat-number" style="font-size: 36px; font-weight: 700; color: #10B981; line-height: 1;">
                {{ $totalGuests }}
            </div>
        </div>
    </div>

    {{-- Two column grid --}}
    <div class="krd-grid-2">

        {{-- Recent Events --}}
        <div class="krd-card">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                <div class="krd-label">Recent Events</div>
                
                <a href="{{ route('tenant.events.create') }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">+ New Event</a>
            </div>

            @if($recentEvents->isEmpty())
            <div class="krd-empty-state">
                <div class="krd-empty-state-icon">📋</div>
                <div class="krd-empty-state-title">No events yet</div>
                <div class="krd-empty-state-desc">Create your first event to get started.</div>
            </div>
            @else
            <div style="display: flex; flex-direction: column; gap: 2px;">
                @foreach($recentEvents as $event)
                <div style="display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #E7E5E4;">
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: #7C3AED; flex-shrink: 0;"></div>
                    <div style="flex: 1; min-width: 0;">
                        <a href="{{ route('tenant.events.show', $event->slug) }}" wire:navigate style="text-decoration:none;">
                            <div style="font-size: 13px; font-weight: 500; color: #1C1917; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $event->name }}
                            </div>
                        </a>
                        <div style="font-size: 11px; color: #A8A29E; margin-top: 2px;">
                            {{ $event->date ? $event->date->format('M d, Y') : 'Date TBC' }}
                            @if($event->eventType) · {{ $event->eventType->name }} @endif
                        </div>
                    </div>
                    @if($event->status)
                    <span class="krd-badge" style="background: {{ $event->status->color ?? '#F5F5F4' }}22; color: {{ $event->status->color ?? '#57534E' }}; font-size: 10px;">
                        {{ $event->status->name }}
                    </span>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Pending Tasks --}}
        <div class="krd-card">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                <div class="krd-label">Pending Tasks</div>
                <a href="#" class="krd-btn krd-btn-secondary krd-btn-sm">View All</a>
            </div>

            @if($pendingTasks->isEmpty())
            <div class="krd-empty-state">
                <div class="krd-empty-state-icon">✅</div>
                <div class="krd-empty-state-title">All caught up</div>
                <div class="krd-empty-state-desc">No pending tasks right now.</div>
            </div>
            @else
            <div style="display: flex; flex-direction: column; gap: 2px;">
                @foreach($pendingTasks as $task)
                <div style="display: flex; align-items: flex-start; gap: 12px; padding: 10px 0; border-bottom: 1px solid #E7E5E4;">
                    <div style="width: 16px; height: 16px; border: 1.5px solid #D6D3D1; border-radius: 4px; flex-shrink: 0; margin-top: 1px;"></div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 13px; color: #1C1917; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $task->title }}
                        </div>
                        <div style="font-size: 11px; color: #A8A29E; margin-top: 2px;">
                            @if($task->due_date)
                                @if($task->due_date->isPast())
                                    <span style="color: #EF4444;">Overdue · {{ $task->due_date->format('M d') }}</span>
                                @else
                                    Due {{ $task->due_date->format('M d') }}
                                @endif
                            @else
                                No due date
                            @endif
                            @if($task->assignedTo) · {{ $task->assignedTo->name }} @endif
                        </div>
                    </div>
                    @php
                        $priorityColors = [
                            'critical' => '#EF4444',
                            'high'     => '#F59E0B',
                            'medium'   => '#3B82F6',
                            'low'      => '#A8A29E',
                        ];
                    @endphp
                    <div style="width: 6px; height: 6px; border-radius: 50%; background: {{ $priorityColors[$task->priority->value ?? 'medium'] ?? '#A8A29E' }}; flex-shrink: 0; margin-top: 4px;"></div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

    </div>

    {{-- Budget Summary --}}
    @if($totalBudget > 0)
    <div class="krd-card" style="margin-top: 16px;">
        <div class="krd-label" style="margin-bottom: 12px;">Budget Overview</div>
        <div style="font-size: 28px; font-weight: 700; color: #1C1917; letter-spacing: -0.02em;">
            ₦{{ number_format($totalBudget, 0) }}
        </div>
        <div style="font-size: 12px; color: #A8A29E; margin-top: 4px;">Total across all events</div>
    </div>
    @endif

</div>