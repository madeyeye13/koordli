<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Reminder</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; background:#FAFAF9; color:#1C1917; }
        .wrapper { max-width:560px; margin:0 auto; padding:40px 20px; }
        .card { background:#fff; border:1px solid #E7E5E4; border-radius:8px; overflow:hidden; }
        .header { background:#1C1917; padding:32px 40px; }
        .logo { font-size:20px; font-weight:700; color:#fff; letter-spacing:-0.02em; }
        .logo-tag { font-size:10px; color:#78716C; letter-spacing:0.1em; text-transform:uppercase; margin-top:2px; }
        .banner { padding:16px 40px; background:{{ $daysLeft <= 3 ? '#FEF3C7' : '#EDE9FE' }}; border-bottom:1px solid {{ $daysLeft <= 3 ? '#FDE68A' : '#DDD6FE' }}; }
        .banner-text { font-size:14px; font-weight:600; color:{{ $daysLeft <= 3 ? '#92400E' : '#5B21B6' }}; }
        .body { padding:40px; }
        .greeting { font-size:22px; font-weight:600; color:#1C1917; margin-bottom:12px; }
        .text { font-size:14px; color:#57534E; line-height:1.7; margin-bottom:24px; }
        .info-box { background:#F5F5F4; border-radius:8px; padding:20px 24px; margin-bottom:24px; }
        .info-row { display:flex; justify-content:space-between; padding:7px 0; border-bottom:1px solid #E7E5E4; font-size:13px; }
        .info-row:last-child { border-bottom:none; }
        .info-key { color:#78716C; }
        .info-val { font-weight:600; color:#1C1917; }
        .btn { display:inline-block; background:#7C3AED; color:#fff; padding:13px 28px; border-radius:6px; text-decoration:none; font-size:14px; font-weight:600; }
        .footer { padding:20px 40px; background:#FAFAF9; border-top:1px solid #E7E5E4; }
        .footer-text { font-size:11px; color:#A8A29E; line-height:1.7; }
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
                <div class="banner-text">
                    {{ $daysLeft <= 3 ? '⚠️ Urgent' : '🔔 Reminder' }} — Your plan expires in {{ $daysLeft }} {{ $daysLeft === 1 ? 'day' : 'days' }}
                </div>
            </div>
            <div class="body">
                <div class="greeting">Hi {{ $tenantName }},</div>
                <p class="text">
                    Your <strong>{{ $planName }}</strong> plan on Koordli expires on <strong>{{ $expiryDate }}</strong>.
                    Renew now to keep full access to all your events, clients, vendors, and guests.
                </p>
                <div class="info-box">
                    <div class="info-row">
                        <span class="info-key">Current Plan</span>
                        <span class="info-val">{{ $planName }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Expires</span>
                        <span class="info-val">{{ $expiryDate }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Days Remaining</span>
                        <span class="info-val" style="color:{{ $daysLeft <= 3 ? '#DC2626' : '#D97706' }};">{{ $daysLeft }} {{ $daysLeft === 1 ? 'day' : 'days' }}</span>
                    </div>
                </div>
                <p class="text" style="margin-bottom:20px;">After expiry, you'll have a grace period before your account is locked. Renew now to avoid any interruption.</p>
                <a href="{{ $upgradeUrl }}" class="btn">Renew My Plan →</a>
            </div>
            <div class="footer">
                <div class="footer-text">
                    This email was sent to {{ $tenantEmail }}.<br>
                    © {{ date('Y') }} Koordli. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</body>
</html>