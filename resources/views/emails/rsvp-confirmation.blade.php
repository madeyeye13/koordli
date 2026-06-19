<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSVP Confirmation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #FAFAF9; color: #1C1917; }
        .wrapper { max-width: 560px; margin: 0 auto; padding: 40px 20px; }
        .card { background: #FFFFFF; border: 1px solid #E7E5E4; border-radius: 8px; overflow: hidden; }
        .header { background: #1C1917; padding: 32px 40px; }
        .logo { font-size: 20px; font-weight: 700; color: #FFFFFF; letter-spacing: -0.02em; }
        .logo-tag { font-size: 10px; color: #78716C; letter-spacing: 0.1em; text-transform: uppercase; margin-top: 2px; }
        .banner { padding: 14px 40px; display: flex; align-items: center; gap: 10px; }
        .banner-confirmed { background: #D1FAE5; border-bottom: 1px solid #6EE7B7; }
        .banner-declined  { background: #FEE2E2; border-bottom: 1px solid #FECACA; }
        .banner-text-confirmed { font-size: 14px; font-weight: 600; color: #065F46; }
        .banner-text-declined  { font-size: 14px; font-weight: 600; color: #DC2626; }
        .body { padding: 40px; }
        .greeting { font-size: 22px; font-weight: 600; color: #1C1917; margin-bottom: 12px; }
        .text { font-size: 14px; color: #57534E; line-height: 1.7; margin-bottom: 24px; }
        .event-box { background: #F5F3FF; border: 1px solid #DDD6FE; border-radius: 8px; padding: 20px 24px; margin-bottom: 24px; }
        .event-label { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #7C3AED; margin-bottom: 12px; }
        .event-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #EDE9FE; font-size: 13px; }
        .event-row:last-child { border-bottom: none; }
        .event-key { color: #78716C; }
        .event-val { font-weight: 600; color: #1C1917; }
        .qr-box { text-align: center; padding: 24px; border: 1px solid #E7E5E4; border-radius: 8px; margin-bottom: 24px; }
        .qr-label { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #78716C; margin-bottom: 16px; }
        .btn { display: inline-block; background: #7C3AED; color: #FFFFFF; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500; margin-bottom: 24px; }
        .btn-ghost { display: inline-block; background: #F5F5F4; color: #57534E; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500; margin-bottom: 24px; margin-left: 10px; }
        .divider { height: 1px; background: #E7E5E4; margin: 0 0 24px; }
        .note { font-size: 12px; color: #A8A29E; line-height: 1.7; }
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

            <div class="banner {{ $status === 'confirmed' ? 'banner-confirmed' : 'banner-declined' }}">
                <span style="font-size:18px;">{{ $status === 'confirmed' ? '✅' : '❌' }}</span>
                <div class="{{ $status === 'confirmed' ? 'banner-text-confirmed' : 'banner-text-declined' }}">
                    {{ $status === 'confirmed' ? 'Attendance Confirmed!' : 'RSVP: Not Attending' }}
                </div>
            </div>

            <div class="body">
                <div class="greeting">Hi {{ $guestName }},</div>
                <p class="text">
                    @if($status === 'confirmed')
                    Your attendance for <strong>{{ $eventName }}</strong> has been confirmed.
                    @if($plusOneCount > 0)
                    You're attending with <strong>{{ $plusOneCount }}</strong> additional {{ $plusOneCount === 1 ? 'guest' : 'guests' }}.
                    @endif
                    @else
                    We've recorded that you won't be attending <strong>{{ $eventName }}</strong>.
                    If this was a mistake, you can update your RSVP using the link below.
                    @endif
                </p>

                <div class="event-box">
                    <div class="event-label">Event Details</div>
                    <div class="event-row">
                        <span class="event-key">Event</span>
                        <span class="event-val">{{ $eventName }}</span>
                    </div>
                    <div class="event-row">
                        <span class="event-key">Date</span>
                        <span class="event-val">{{ $eventDate }}</span>
                    </div>
                    @if($venue)
                    <div class="event-row">
                        <span class="event-key">Venue</span>
                        <span class="event-val">{{ $venue }}</span>
                    </div>
                    @endif
                    @if($status === 'confirmed')
                    <div class="event-row">
                        <span class="event-key">Attending</span>
                        <span class="event-val">{{ 1 + $plusOneCount }} {{ (1 + $plusOneCount) === 1 ? 'person' : 'people' }}</span>
                    </div>
                    @endif
                </div>

                @if($status === 'confirmed')
                {{-- QR Ticket --}}
                <div class="qr-box">
                    <div class="qr-label">Your Entry QR Code</div>
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(180)->generate($qrToken) !!}
                    <div style="font-size:11px;color:#A8A29E;margin-top:12px;">{{ $qrToken }}</div>
                </div>

                <div style="text-align:center;margin-bottom:24px;">
                    <a href="{{ url('/rsvp/ticket/' . $qrToken) }}" class="btn">Download Ticket</a>
                </div>
                @endif

                <div class="divider"></div>

                <div class="note">
                    Need to update your RSVP? <a href="{{ $editUrl }}" style="color:#7C3AED;">Click here to edit your response</a>.<br><br>
                    If you have any questions about the event, please contact the event organiser directly.
                </div>
            </div>

            <div class="footer">
                <div class="footer-text">
                    This email was sent to {{ $guestEmail }}.<br>
                    © {{ date('Y') }} Koordli. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</body>
</html>