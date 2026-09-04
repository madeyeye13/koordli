<div>
    <div style="margin-bottom:24px;">
        <div class="krd-label" style="margin-bottom:4px;">Operations</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">Event Checklists</h2>
        <p style="font-size:12px;color:#78716C;margin-top:4px;">Pick an event to view or manage its checklist.</p>
    </div>

    @if($events->isEmpty())
    <div class="krd-card" style="padding:40px;text-align:center;color:#A8A29E;">No events yet.</div>
    @else
    <div class="krd-card" style="overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #E7E5E4;">
                    <th style="text-align:left;padding:12px 16px;font-size:11px;color:#78716C;text-transform:uppercase;">Event</th>
                    <th style="text-align:left;padding:12px 16px;font-size:11px;color:#78716C;text-transform:uppercase;">Date</th>
                    <th style="text-align:left;padding:12px 16px;font-size:11px;color:#78716C;text-transform:uppercase;">Progress</th>
                    <th style="text-align:right;padding:12px 16px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $event)
                @php
                    $total = $event->checklist?->items->count() ?? 0;
                    $completed = $event->checklist?->items->where('is_completed', true)->count() ?? 0;
                @endphp
                <tr style="border-bottom:1px solid #F5F5F4;">
                    <td style="padding:12px 16px;font-size:13px;font-weight:600;color:#1C1917;">{{ $event->name }}</td>
                    <td style="padding:12px 16px;font-size:12px;color:#78716C;">{{ $event->date?->format('M d, Y') ?? '—' }}</td>
                    <td style="padding:12px 16px;font-size:12px;color:#78716C;">{{ $total > 0 ? "{$completed} / {$total}" : '—' }}</td>
                    <td style="padding:12px 16px;text-align:right;">
                        <a href="{{ route('tenant.events.checklist', $event->slug) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">
                            {{ $total > 0 ? 'View' : 'Start' }}
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>