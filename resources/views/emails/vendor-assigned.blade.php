<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Assignment</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #FAFAF9; color: #1C1917; }
        .wrapper { max-width: 560px; margin: 0 auto; padding: 40px 20px; }
        .card { background: #FFFFFF; border: 1px solid #E7E5E4; border-radius: 8px; overflow: hidden; }
        .header { background: #1C1917; padding: 32px 40px; }
        .logo { font-size: 20px; font-weight: 700; color: #FFFFFF; letter-spacing: -0.02em; }
        .logo-tag { font-size: 10px; color: #78716C; letter-spacing: 0.1em; text-transform: uppercase; margin-top: 2px; }
        .banner { background: #D1FAE5; border-bottom: 1px solid #6EE7B7; padding: 14px 40px; display: flex; align-items: center; gap: 10px; }
        .banner-text { font-size: 14px; font-weight: 600; color: #065F46; }
        .body { padding: 40px; }
        .greeting { font-size: 22px; font-weight: 600; color: #1C1917; margin-bottom: 12px; }
        .text { font-size: 14px; color: #57534E; line-height: 1.7; margin-bottom: 24px; }
        .event-box { background: #F5F3FF; border: 1px solid #DDD6FE; border-radius: 8px; padding: 20px 24px; margin-bottom: 24px; }
        .event-label { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #7C3AED; margin-bottom: 12px; }
        .event-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #EDE9FE; font-size: 13px; }
        .event-row:last-child { border-bottom: none; }
        .event-key { color: #78716C; }
        .event-val { font-weight: 600; color: #1C1917; }
        .creds { background: #FEF3C7; border: 1px solid #FDE68A; border-radius: 8px; padding: 20px 24px; margin-bottom: 24px; }
        .creds-label { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #D97706; margin-bottom: 12px; }
        .cred-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #FDE68A; font-size: 13px; }
        .cred-row:last-child { border-bottom: none; }
        .cred-key { color: #78716C; }
        .cred-val { font-weight: 600; color: #1C1917; font-family: monospace; }
        .btn { display: inline-block; background: #7C3AED; color: #FFFFFF; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500; margin-bottom: 24px; }
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

            <div class="banner">
                <span style="font-size:18px;">📅</span>
                <div class="banner-text">You've been assigned to an event</div>
            </div>

            <div class="body">
                <div class="greeting">Hi {{ $vendorName }},</div>
                <p class="text">
                    <strong>{{ $companyName }}</strong> has assigned
                    <strong>{{ $businessName }}</strong> to an upcoming event.
                    @if($isNewAccount)
                    A vendor portal account has been created for you so you can view your assignment details.
                    @else
                    Log in to your vendor portal to view the full details.
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
                    <div class="event-row">
                        <span class="event-key">Company</span>
                        <span class="event-val">{{ $companyName }}</span>
                    </div>
                </div>

                @if($isNewAccount && $password)
                <div class="creds">
                    <div class="creds-label">Your Portal Login Credentials</div>
                    <div class="cred-row">
                        <span class="cred-key">Portal URL</span>
                        <span class="cred-val">{{ url('/vendor/login') }}</span>
                    </div>
                    <div class="cred-row">
                        <span class="cred-key">Email</span>
                        <span class="cred-val">{{ $vendorEmail }}</span>
                    </div>
                    <div class="cred-row">
                        <span class="cred-key">Temporary Password</span>
                        <span class="cred-val">{{ $password }}</span>
                    </div>
                </div>
                @endif

                <a href="{{ url('/vendor/login') }}" class="btn">
                    {{ $isNewAccount ? 'Set Up Your Account →' : 'View Assignment →' }}
                </a>

                <div class="divider"></div>

                <div class="note">
                    @if($isNewAccount)
                    You will be asked to change your temporary password on first login.
                    Keep your credentials safe and never share them with anyone.<br><br>
                    @endif
                    If you have questions about this assignment, contact
                    <strong style="color:#78716C;">{{ $companyName }}</strong> directly.
                </div>
            </div>

            <div class="footer">
                <div class="footer-text">
                    This email was sent to {{ $vendorEmail }}.<br>
                    © {{ date('Y') }} Koordli. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</body>
</html>