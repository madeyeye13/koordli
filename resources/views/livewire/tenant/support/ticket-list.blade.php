<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <div class="krd-label" style="margin-bottom:4px;">Support</div>
            <h2 class="krd-heading-3" style="color:#1C1917;">My Tickets</h2>
        </div>
        <a href="{{ route('tenant.support.tickets.create') }}" wire:navigate class="krd-btn krd-btn-primary">+ New Ticket</a>
    </div>

    <div wire:ignore x-data="{ active: '{{ $statusFilter }}' }" style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
        @foreach(['' => 'All', 'open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $val => $label)
        <button
            x-on:click="active = '{{ $val }}'; $wire.set('statusFilter', '{{ $val }}')"
            class="krd-btn krd-btn-sm"
            :class="active === '{{ $val }}' ? 'krd-btn-primary' : 'krd-btn-secondary'">
            {{ $label }}
        </button>
        @endforeach
    </div>

    @if($tickets->isEmpty())
    <div class="krd-card">
        <div class="krd-empty-state">
            <div class="krd-empty-state-icon">🎫</div>
            <div class="krd-empty-state-title">No tickets yet</div>
            <div class="krd-empty-state-desc">Need help with something? Open a ticket and we'll get back to you.</div>
        </div>
    </div>
    @else
    <div style="display:flex;flex-direction:column;gap:10px;">
        @foreach($tickets as $ticket)
        <a href="{{ route('tenant.support.tickets.show', $ticket->uuid) }}" wire:navigate
            class="krd-card" style="padding:18px;text-decoration:none;display:block;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;flex-wrap:wrap;">
                        <span style="font-size:14px;font-weight:600;color:#1C1917;">{{ $ticket->subject }}</span>
                        <span class="krd-badge" style="background:{{ $ticket->priorityColor() }}1a;color:{{ $ticket->priorityColor() }};">{{ ucfirst($ticket->priority) }}</span>
                    </div>
                    <div style="font-size:12px;color:#78716C;">
                        #{{ strtoupper(substr($ticket->uuid, 0, 8)) }} · {{ $ticket->messages_count }} {{ Str::plural('message', $ticket->messages_count) }} · {{ $ticket->created_at->diffForHumans() }}
                    </div>
                </div>
                <span class="krd-badge" style="background:{{ $ticket->statusColor() }}1a;color:{{ $ticket->statusColor() }};flex-shrink:0;">
                    {{ $ticket->statusLabel() }}
                </span>
            </div>
        </a>
        @endforeach
    </div>
    @endif
</div>