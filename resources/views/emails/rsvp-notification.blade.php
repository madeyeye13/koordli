<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New RSVP Response</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #FAFAF9; color: #1C1917; }
        .wrapper { max-width: 560px; margin: 0 auto; padding: 40px 20px; }
        .card { background: #FFFFFF; border: 1px solid #E7E5E4; border-radius: 8px; overflow: hidden; }
        .header { background: #1C1917; padding: 32px 40px; }
        .logo { font-size: 20px; font-weight: 700; color: #FFFFFF; letter-spacing: -0.02em; }
        .logo-tag { font-size: 10px; color: #78716C; letter-spacing: 0.1em; text-transform: uppercase; margin-top: 2px; }
        .body { padding: 40px; }
        .greeting { font-size: 22px; font-weight: 600; color: #1C1917; margin-bottom: 12px; }
        .text { font-size: 14px; color: #57534E; line-height: 1.7; margin-bottom: 24px; }
        .info-box { background: #F5F3FF; border: 1px solid #DDD6FE; border-radius: 8px; padding: 20px 24px; margin-bottom: 24px; }
        .info-label { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #7C3AED; margin-bottom: 12px; }
        .info-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #EDE9FE; font-size: 13px; }
        .info-row:last-child { border-bottom: none; }
        .info-key { color: #78716C; }
        .info-val { font-weight: 600; color: #1C1917; }
        .footer { padding: 20px 40px; background: #FAFAF9; border-top: 1px solid #E7E5E4; }
        .footer-text { font-size: 11px; color: #A8A29E; line-height: 1.7; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <div class="logo">Koordli</div>
                <div class="logo-tag">Event Operations Platform</div>
            </div>
            <div class="body">
                <div class="greeting">Hi {{ $recipientName }},</div>
                <p class="text">
                    {{ $isUpdate ? 'A guest has updated their RSVP' : 'A new RSVP response has been submitted' }}
                    for <strong>{{ $eventName }}</strong>.
                </p>

                <div class="info-box">
                    <div class="info-label">RSVP Details</div>
                    <div class="info-row">
                        <span class="info-key">Guest</span>
                        <span class="info-val">{{ $guestName }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Event</span>
                        <span class="info-val">{{ $eventName }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Status</span>
                        <span class="info-val" style="color: {{ $status === 'confirmed' ? '#059669' : ($status === 'declined' ? '#DC2626' : '#D97706') }};">
                            {{ ucfirst($status) }}
                        </span>
                    </div>
                    @if($status === 'confirmed' && $plusOneCount > 0)
                    <div class="info-row">
                        <span class="info-key">Additional Guests</span>
                        <span class="info-val">{{ $plusOneCount }}</span>
                    </div>
                    @endif
                </div>

                <div style="font-size:12px;color:#A8A29E;line-height:1.7;">
                    Log in to your Koordli dashboard to view all RSVP responses for this event.
                </div>
            </div>
            <div class="footer">
                <div class="footer-text">
                    This email was sent to {{ $recipientEmail }}.<br>
                    © {{ date('Y') }} Koordli. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</body>
</html>