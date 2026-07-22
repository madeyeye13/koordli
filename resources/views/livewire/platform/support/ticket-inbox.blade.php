<div>
    <div style="margin-bottom:24px;">
        <div class="krd-label" style="margin-bottom:4px;">Support</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">Ticket Inbox</h2>
    </div>

    <div wire:ignore x-data="{ view: '{{ $viewFilter }}' }" style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
        <button x-on:click="view = 'mine'; $wire.set('viewFilter', 'mine')"
            :style="`font-size:12px;font-weight:600;padding:7px 16px;border-radius:8px;cursor:pointer;border:1px solid ${view === 'mine' ? '#7C3AED' : '#E7E5E4'};background:${view === 'mine' ? '#7C3AED' : '#fff'};color:${view === 'mine' ? '#fff' : '#57534E'};`">
            My Tickets ({{ $counts['mine'] }})
        </button>
        <button x-on:click="view = 'unassigned'; $wire.set('viewFilter', 'unassigned')"
            :style="`font-size:12px;font-weight:600;padding:7px 16px;border-radius:8px;cursor:pointer;border:1px solid ${view === 'unassigned' ? '#7C3AED' : '#E7E5E4'};background:${view === 'unassigned' ? '#7C3AED' : '#fff'};color:${view === 'unassigned' ? '#fff' : '#57534E'};`">
            Unassigned ({{ $counts['unassigned'] }})
        </button>
        <button x-on:click="view = 'all'; $wire.set('viewFilter', 'all')"
            :style="`font-size:12px;font-weight:600;padding:7px 16px;border-radius:8px;cursor:pointer;border:1px solid ${view === 'all' ? '#7C3AED' : '#E7E5E4'};background:${view === 'all' ? '#7C3AED' : '#fff'};color:${view === 'all' ? '#fff' : '#57534E'};`">
            All Open ({{ $counts['all'] }})
        </button>
    </div>

    <div wire:ignore x-data="{ status: '{{ $statusFilter }}' }" style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
        @foreach(['' => 'Any Status', 'open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $val => $label)
        <button
            x-on:click="status = '{{ $val }}'; $wire.set('statusFilter', '{{ $val }}')"
            :style="`font-size:11px;padding:5px 12px;border-radius:6px;cursor:pointer;border:1px solid ${status === '{{ $val }}' ? '#7C3AED' : '#E7E5E4'};background:${status === '{{ $val }}' ? '#F5F3FF' : '#fff'};color:${status === '{{ $val }}' ? '#7C3AED' : '#78716C'};`">
            {{ $label }}
        </button>
        @endforeach
    </div>

    @if($tickets->isEmpty())
    <div class="krd-card">
        <div class="krd-empty-state">
            <div class="krd-empty-state-icon">🎉</div>
            <div class="krd-empty-state-title">No tickets here</div>
            <div class="krd-empty-state-desc">Nothing matches this view right now.</div>
        </div>
    </div>
    @else
    <div class="krd-card" style="padding:0;overflow:hidden;">
        <div class="krd-table-wrap">
            <table class="krd-table">
                <thead>
                    <tr><th>Subject</th><th>Company</th><th>Priority</th><th>Status</th><th>Agent</th><th>Updated</th></tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                    <tr style="cursor:pointer;" onclick="window.location.href='{{ route('platform.support.tickets.show', $ticket->uuid) }}'">
                        <td style="font-size:13px;font-weight:500;color:#1C1917;">
                            {{ $ticket->subject }}
                            <div style="font-size:11px;color:#A8A29E;">#{{ strtoupper(substr($ticket->uuid, 0, 8)) }}</div>
                        </td>
                        <td style="font-size:12px;color:#57534E;">{{ $ticket->tenant->name }}</td>
                        <td><span class="krd-badge" style="background:{{ $ticket->priorityColor() }}1a;color:{{ $ticket->priorityColor() }};">{{ ucfirst($ticket->priority) }}</span></td>
                        <td><span class="krd-badge" style="background:{{ $ticket->statusColor() }}1a;color:{{ $ticket->statusColor() }};">{{ $ticket->statusLabel() }}</span></td>
                        <td style="font-size:12px;color:#78716C;">{{ $ticket->assignedAgent?->platformUser?->name ?? '— Unassigned —' }}</td>
                        <td style="font-size:12px;color:#A8A29E;">{{ $ticket->updated_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>