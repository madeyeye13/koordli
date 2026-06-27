<div>
    <div style="margin-bottom: 28px;">
        <h1 style="font-size: 22px; font-weight: 600; color: #1C1917; letter-spacing: -0.01em;">
            Welcome, {{ auth('vendor')->user()?->name }}
        </h1>
        <p style="font-size: 13px; color: #78716C; margin-top: 4px;">
            {{ auth('vendor')->user()?->business_name }} — here are your assigned events.
        </p>
    </div>

    @if($assignments->isEmpty())
    <div class="krd-card">
        <div class="krd-empty-state">
            <div class="krd-empty-state-icon">📅</div>
            <div class="krd-empty-state-title">No events assigned yet</div>
            <div class="krd-empty-state-desc">You'll be notified when you're assigned to an event.</div>
        </div>
    </div>
    @else

    {{-- Summary strip --}}
    <div class="krd-grid-2" style="margin-bottom: 24px; gap: 12px;">
        <div class="krd-card" style="padding: 16px; border-left: 3px solid #7C3AED;">
            <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#A8A29E;margin-bottom:4px;">Events Assigned</div>
            <div style="font-size:28px;font-weight:700;color:#7C3AED;line-height:1;">{{ $assignments->count() }}</div>
        </div>
        <div class="krd-card" style="padding: 16px; border-left: 3px solid #10B981;">
            <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#A8A29E;margin-bottom:4px;">Total Agreed</div>
            <div style="font-size:28px;font-weight:700;color:#10B981;line-height:1;">
                {{ number_format($assignments->sum('amount_agreed'), 2) }}
            </div>
        </div>
    </div>

    {{-- Desktop Table --}}
    <div class="krd-card" style="padding:0;overflow:hidden;" id="vendor-events-desktop">
        <div class="krd-table-wrap">
            <table class="krd-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th style="text-align:right;">Agreed</th>
                        <th style="text-align:right;">Paid</th>
                        <th style="text-align:right;">Balance</th>
                        <th>Payment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignments as $assignment)
                    @php
                        $balance = (float)$assignment->amount_agreed - (float)$assignment->amount_paid;
                    @endphp
                    <tr>
                        <td>
                            <div style="font-size:13px;font-weight:500;color:#1C1917;">{{ $assignment->event->name }}</div>
                            @if($assignment->event->eventType)
                            <div style="font-size:11px;color:#A8A29E;">{{ $assignment->event->eventType->name }}</div>
                            @endif
                        </td>
                        <td style="font-size:13px;color:#57534E;">
                            {{ $assignment->event->date?->format('M d, Y') ?? '—' }}
                        </td>
                        <td>
                            <span class="krd-badge" style="background:{{ $assignment->statusColor() }}1a;color:{{ $assignment->statusColor() }};">
                                {{ ucfirst($assignment->status) }}
                            </span>
                        </td>
                        <td style="text-align:right;font-size:13px;font-weight:500;color:#7C3AED;">
                            {{ number_format($assignment->amount_agreed, 2) }}
                        </td>
                        <td style="text-align:right;font-size:13px;font-weight:500;color:#10B981;">
                            {{ number_format($assignment->amount_paid, 2) }}
                        </td>
                        <td style="text-align:right;font-size:13px;font-weight:600;color:{{ $balance > 0 ? '#EF4444' : '#10B981' }};">
                            {{ number_format(abs($balance), 2) }}
                        </td>
                        <td>
                            @if($balance <= 0)
                                <span class="krd-badge krd-badge-green">Fully Paid</span>
                            @elseif($assignment->amount_paid > 0)
                                <span class="krd-badge krd-badge-amber">Partial</span>
                            @else
                                <span class="krd-badge krd-badge-red">Unpaid</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile Cards --}}
    <div id="vendor-events-mobile" style="display:flex;flex-direction:column;gap:12px;">
        @foreach($assignments as $assignment)
        @php $balance = (float)$assignment->amount_agreed - (float)$assignment->amount_paid; @endphp
        <div class="krd-card" style="padding:16px;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:12px;">
                <div>
                    <div style="font-size:14px;font-weight:600;color:#1C1917;">{{ $assignment->event->name }}</div>
                    <div style="font-size:11px;color:#A8A29E;margin-top:2px;">
                        {{ $assignment->event->date?->format('M d, Y') ?? 'Date TBC' }}
                        @if($assignment->event->eventType) · {{ $assignment->event->eventType->name }} @endif
                    </div>
                </div>
                <span class="krd-badge" style="background:{{ $assignment->statusColor() }}1a;color:{{ $assignment->statusColor() }};">
                    {{ ucfirst($assignment->status) }}
                </span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:12px;">
                <div style="background:#F5F5F4;border-radius:6px;padding:10px;">
                    <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#A8A29E;margin-bottom:2px;">Agreed</div>
                    <div style="font-size:13px;font-weight:700;color:#7C3AED;">{{ number_format($assignment->amount_agreed, 2) }}</div>
                </div>
                <div style="background:#F5F5F4;border-radius:6px;padding:10px;">
                    <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#A8A29E;margin-bottom:2px;">Paid</div>
                    <div style="font-size:13px;font-weight:700;color:#10B981;">{{ number_format($assignment->amount_paid, 2) }}</div>
                </div>
                <div style="background:#F5F5F4;border-radius:6px;padding:10px;">
                    <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#A8A29E;margin-bottom:2px;">Balance</div>
                    <div style="font-size:13px;font-weight:700;color:{{ $balance > 0 ? '#EF4444' : '#10B981' }};">{{ number_format(abs($balance), 2) }}</div>
                </div>
            </div>
            @if($balance <= 0)
                <span class="krd-badge krd-badge-green">Fully Paid</span>
            @elseif($assignment->amount_paid > 0)
                <span class="krd-badge krd-badge-amber">Partial Payment</span>
            @else
                <span class="krd-badge krd-badge-red">Unpaid</span>
            @endif
        </div>
        @endforeach
    </div>

    @endif

    {{-- ── Tasks ── --}}
    @if($tasks->isNotEmpty())
    <div style="margin-top:28px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <div>
                <div class="krd-label" style="margin-bottom:2px;">Your Tasks</div>
                <div style="font-size:12px;color:#A8A29E;">{{ $tasks->count() }} task{{ $tasks->count() === 1 ? '' : 's' }} assigned to you</div>
            </div>
        </div>

        {{-- Desktop --}}
        <div class="krd-card" style="padding:0;overflow:hidden;" id="vendor-tasks-desktop">
            <div class="krd-table-wrap">
                <table class="krd-table">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Event</th>
                            <th>Priority</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                        <tr>
                            <td>
                                <div style="font-size:13px;font-weight:500;color:#1C1917;">{{ $task->title }}</div>
                                @if($task->description)
                                <div style="font-size:11px;color:#A8A29E;margin-top:2px;">{{ Str::limit($task->description, 60) }}</div>
                                @endif
                            </td>
                            <td style="font-size:12px;color:#78716C;">
                                {{ $task->event?->name ?? 'Company Task' }}
                            </td>
                            <td>
                                @php
                                    $priorityColor = match($task->priority->value) {
                                        'urgent' => '#EF4444',
                                        'high'   => '#F59E0B',
                                        'normal' => '#3B82F6',
                                        default  => '#A8A29E',
                                    };
                                @endphp
                                <span class="krd-badge" style="background:{{ $priorityColor }}1a;color:{{ $priorityColor }};">
                                    {{ ucfirst($task->priority->value) }}
                                </span>
                            </td>
                            <td style="font-size:12px;color:{{ $task->isOverdue() ? '#EF4444' : '#78716C' }};">
                                {{ $task->due_date?->format('M d, Y') ?? '—' }}
                                @if($task->isOverdue())
                                <span style="font-size:10px;display:block;color:#EF4444;">Overdue</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusColor = match($task->status->value) {
                                        'done'        => '#10B981',
                                        'in_progress' => '#F59E0B',
                                        'blocked'     => '#EF4444',
                                        'cancelled'   => '#A8A29E',
                                        default       => '#78716C',
                                    };
                                @endphp
                                <span class="krd-badge" style="background:{{ $statusColor }}1a;color:{{ $statusColor }};">
                                    {{ ucfirst(str_replace('_', ' ', $task->status->value)) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile --}}
        <div id="vendor-tasks-mobile" style="display:flex;flex-direction:column;gap:10px;">
            @foreach($tasks as $task)
            @php
                $priorityColor = match($task->priority->value) {
                    'urgent' => '#EF4444',
                    'high'   => '#F59E0B',
                    'normal' => '#3B82F6',
                    default  => '#A8A29E',
                };
                $statusColor = match($task->status->value) {
                    'done'        => '#10B981',
                    'in_progress' => '#F59E0B',
                    'blocked'     => '#EF4444',
                    'cancelled'   => '#A8A29E',
                    default       => '#78716C',
                };
            @endphp
            <div class="krd-card" style="padding:16px;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:8px;">
                    <div style="font-size:14px;font-weight:600;color:#1C1917;">{{ $task->title }}</div>
                    <span class="krd-badge" style="background:{{ $statusColor }}1a;color:{{ $statusColor }};flex-shrink:0;">
                        {{ ucfirst(str_replace('_', ' ', $task->status->value)) }}
                    </span>
                </div>
                @if($task->description)
                <div style="font-size:12px;color:#A8A29E;margin-bottom:8px;">{{ Str::limit($task->description, 80) }}</div>
                @endif
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                    <span class="krd-badge" style="background:{{ $priorityColor }}1a;color:{{ $priorityColor }};">
                        {{ ucfirst($task->priority->value) }}
                    </span>
                    @if($task->due_date)
                    <span style="font-size:11px;color:{{ $task->isOverdue() ? '#EF4444' : '#78716C' }};">
                        Due {{ $task->due_date->format('M d, Y') }}
                        @if($task->isOverdue()) · Overdue @endif
                    </span>
                    @endif
                    @if($task->event)
                    <span style="font-size:11px;color:#A8A29E;">· {{ $task->event->name }}</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Runsheet Items ── --}}
    @if($runsheetItems->isNotEmpty())
    <div style="margin-top:28px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <div>
                <div class="krd-label" style="margin-bottom:2px;">Runsheet Items</div>
                <div style="font-size:12px;color:#A8A29E;">{{ $runsheetItems->count() }} item{{ $runsheetItems->count() === 1 ? '' : 's' }} assigned to you</div>
            </div>
            <a href="{{ route('vendor.runsheet') }}" wire:navigate class="krd-btn krd-btn-primary krd-btn-sm">
                View Runsheet →
            </a>
        </div>

        {{-- Summary strip --}}
        @php
            $rsDone       = $runsheetItems->where('status', \App\Enums\RunsheetItemStatus::Done)->count();
            $rsInProgress = $runsheetItems->where('status', \App\Enums\RunsheetItemStatus::InProgress)->count();
            $rsDelayed    = $runsheetItems->where('status', \App\Enums\RunsheetItemStatus::Delayed)->count();
            $rsPending    = $runsheetItems->where('status', \App\Enums\RunsheetItemStatus::Pending)->count();
            $rsTotal      = $runsheetItems->count();
            $rsProgress   = $rsTotal > 0 ? round(($rsDone / $rsTotal) * 100) : 0;
        @endphp
        <div class="krd-grid-4" style="gap:10px;margin-bottom:12px;">
            <div class="krd-card" style="padding:14px;border-left:3px solid #A8A29E;">
                <div class="krd-label" style="margin-bottom:4px;">Pending</div>
                <div style="font-size:22px;font-weight:700;color:#A8A29E;">{{ $rsPending }}</div>
            </div>
            <div class="krd-card" style="padding:14px;border-left:3px solid #F59E0B;">
                <div class="krd-label" style="margin-bottom:4px;">In Progress</div>
                <div style="font-size:22px;font-weight:700;color:#F59E0B;">{{ $rsInProgress }}</div>
            </div>
            <div class="krd-card" style="padding:14px;border-left:3px solid #10B981;">
                <div class="krd-label" style="margin-bottom:4px;">Done</div>
                <div style="font-size:22px;font-weight:700;color:#10B981;">{{ $rsDone }}</div>
            </div>
            <div class="krd-card" style="padding:14px;border-left:3px solid #EF4444;">
                <div class="krd-label" style="margin-bottom:4px;">Delayed</div>
                <div style="font-size:22px;font-weight:700;color:#EF4444;">{{ $rsDelayed }}</div>
            </div>
        </div>

        {{-- Progress bar --}}
        <div class="krd-card" style="padding:14px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                <span style="font-size:12px;color:#57534E;">Overall progress</span>
                <span style="font-size:12px;font-weight:600;color:#10B981;">{{ $rsProgress }}% complete</span>
            </div>
            <div style="height:8px;background:#E7E5E4;border-radius:4px;overflow:hidden;">
                <div style="height:100%;width:{{ $rsProgress }}%;background:#10B981;border-radius:4px;transition:width 400ms;"></div>
            </div>
            <div style="margin-top:10px;text-align:center;">
                <a href="{{ route('vendor.runsheet') }}" wire:navigate
                    style="font-size:13px;color:#7C3AED;font-weight:500;text-decoration:none;">
                    Open Runsheet to update statuses →
                </a>
            </div>
        </div>
    </div>
    @endif


</div>

<style>
@media (min-width: 768px) {
    #vendor-events-desktop   { display: block !important; }
    #vendor-events-mobile    { display: none !important; }
    #vendor-tasks-desktop    { display: block !important; }
    #vendor-tasks-mobile     { display: none !important; }
    #vendor-runsheet-desktop { display: block !important; }
    #vendor-runsheet-mobile  { display: none !important; }
}
@media (max-width: 767px) {
    #vendor-events-desktop   { display: none !important; }
    #vendor-events-mobile    { display: flex !important; }
    #vendor-tasks-desktop    { display: none !important; }
    #vendor-tasks-mobile     { display: flex !important; }
    #vendor-runsheet-desktop { display: none !important; }
    #vendor-runsheet-mobile  { display: flex !important; }
}
</style>