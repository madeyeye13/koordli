<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Ticket — {{ $event->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Spline+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Spline Sans', sans-serif; background: #FAFAF9; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .ticket { background: #fff; border: 1px solid #E7E5E4; border-radius: 12px; max-width: 480px; width: 100%; overflow: hidden; }
        .ticket-header { background: #1C1917; padding: 32px; text-align: center; }
        .ticket-event { font-family: 'Playfair Display', serif; font-size: 24px; color: #FAFAF9; margin-bottom: 6px; }
        .ticket-date { font-size: 13px; color: #A8A29E; }
        .ticket-body { padding: 32px; text-align: center; }
        .ticket-name { font-family: 'Playfair Display', serif; font-size: 20px; color: #1C1917; margin-bottom: 4px; }
        .ticket-label { font-size: 11px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: #A8A29E; margin-bottom: 24px; }
        .ticket-qr { display: flex; justify-content: center; margin-bottom: 12px; }
        .ticket-token { font-size: 11px; color: #A8A29E; font-family: monospace; letter-spacing: 0.05em; margin-bottom: 24px; }
        .ticket-divider { border-top: 1px dashed #E7E5E4; margin: 0 -32px 24px; }
        .ticket-details { display: flex; justify-content: space-between; gap: 16px; text-align: left; }
        .ticket-detail-item { flex: 1; }
        .ticket-detail-label { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: #A8A29E; margin-bottom: 4px; }
        .ticket-detail-value { font-size: 13px; font-weight: 600; color: #1C1917; }
        .ticket-footer { background: #F5F5F4; padding: 16px 32px; text-align: center; font-size: 11px; color: #A8A29E; }
        @media print {
            body { background: #fff; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="ticket-header">
            <div class="ticket-event">{{ $event->name }}</div>
            @if($event->date)
            <div class="ticket-date">{{ $event->date->format('l, F j, Y') }}
                @if($event->start_time) · {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }} @endif
            </div>
            @endif
        </div>

        <div class="ticket-body">
            <div class="ticket-label">Attendee</div>
            <div class="ticket-name">{{ $response->respondent_name }}</div>

            @if($response->plus_one_count > 0)
            <div style="font-size:12px;color:#78716C;margin-bottom:20px;">
                + {{ $response->plus_one_count }} additional {{ $response->plus_one_count === 1 ? 'guest' : 'guests' }}
            </div>
            @else
            <div style="margin-bottom:20px;"></div>
            @endif

            <div class="ticket-qr">
                {!! $qrSvg !!}
            </div>
            <div class="ticket-token">{{ $response->qr_token }}</div>

            <div class="ticket-divider"></div>

            <div class="ticket-details">
                @if($event->venue)
                <div class="ticket-detail-item">
                    <div class="ticket-detail-label">Venue</div>
                    <div class="ticket-detail-value">{{ $event->venue }}</div>
                    @if($event->location)<div style="font-size:11px;color:#A8A29E;">{{ $event->location }}</div>@endif
                </div>
                @endif
                <div class="ticket-detail-item">
                    <div class="ticket-detail-label">Status</div>
                    <div class="ticket-detail-value" style="color:#10B981;">✓ Confirmed</div>
                </div>
            </div>
        </div>

        <div class="ticket-footer">
            Present this QR code at the event entrance · Powered by Koordli
        </div>
    </div>

    <div class="no-print" style="text-align:center;margin-top:20px;">
        <button onclick="window.print()"
            style="background:#1C1917;color:#fff;border:none;padding:12px 28px;border-radius:6px;font-size:14px;font-weight:500;cursor:pointer;font-family:'Spline Sans',sans-serif;">
            🖨️ Print / Save as PDF
        </button>
    </div>
</body>
</html>