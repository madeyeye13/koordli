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
</div>

<style>
@media (min-width: 768px) {
    #vendor-events-desktop { display: block !important; }
    #vendor-events-mobile  { display: none !important; }
}
@media (max-width: 767px) {
    #vendor-events-desktop { display: none !important; }
    #vendor-events-mobile  { display: flex !important; }
}
</style>