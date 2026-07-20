<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Expired</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; background:#FAFAF9; color:#1C1917; }
        .wrapper { max-width:560px; margin:0 auto; padding:40px 20px; }
        .card { background:#fff; border:1px solid #E7E5E4; border-radius:8px; overflow:hidden; }
        .header { background:#1C1917; padding:32px 40px; }
        .logo { font-size:20px; font-weight:700; color:#fff; letter-spacing:-0.02em; }
        .logo-tag { font-size:10px; color:#78716C; letter-spacing:0.1em; text-transform:uppercase; margin-top:2px; }
        .banner { padding:16px 40px; background:#FEE2E2; border-bottom:1px solid #FECACA; }
        .banner-text { font-size:14px; font-weight:600; color:#DC2626; }
        .body { padding:40px; }
        .greeting { font-size:22px; font-weight:600; color:#1C1917; margin-bottom:12px; }
        .text { font-size:14px; color:#57534E; line-height:1.7; margin-bottom:24px; }
        .locked-box { background:#FEF2F2; border:1px solid #FECACA; border-radius:8px; padding:16px 20px; margin-bottom:24px; }
        .locked-title { font-size:14px; font-weight:600; color:#DC2626; margin-bottom:8px; }
        .locked-list { list-style:none; }
        .locked-list li { font-size:13px; color:#57534E; padding:4px 0; }
        .locked-list li::before { content:'✗ '; color:#DC2626; font-weight:700; }
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
                <div class="banner-text">❌ Your subscription has expired</div>
            </div>
            <div class="body">
                <div class="greeting">Hi {{ $tenantName }},</div>
                <p class="text">
                    Your <strong>{{ $planName }}</strong> plan has expired. Your account is now in read-only mode.
                </p>
                <div class="locked-box">
                    <div class="locked-title">What you can no longer do:</div>
                    <ul class="locked-list">
                        <li>Create or edit events</li>
                        <li>Add or update guests</li>
                        <li>Manage vendors or staff</li>
                        <li>Create forms or runsheets</li>
                    </ul>
                </div>
                <p class="text" style="margin-bottom:20px;">
                    Your data is safe. Renew your plan to restore full access immediately.
                </p>
                <a href="{{ $upgradeUrl }}" class="btn">Renew Now →</a>
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