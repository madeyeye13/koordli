<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Support Ticket Reply</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; background:#FAFAF9; color:#1C1917; }
        .wrapper { max-width:560px; margin:0 auto; padding:40px 20px; }
        .card { background:#fff; border:1px solid #E7E5E4; border-radius:8px; overflow:hidden; }
        .header { background:#1C1917; padding:32px 40px; }
        .logo { font-size:20px; font-weight:700; color:#fff; letter-spacing:-0.02em; }
        .logo-tag { font-size:10px; color:#78716C; letter-spacing:0.1em; text-transform:uppercase; margin-top:2px; }
        .body { padding:40px; }
        .greeting { font-size:20px; font-weight:600; color:#1C1917; margin-bottom:12px; }
        .text { font-size:14px; color:#57534E; line-height:1.7; margin-bottom:20px; }
        .reply-box { background:#F5F3FF; border:1px solid #DDD6FE; border-radius:8px; padding:18px 22px; margin-bottom:24px; font-size:14px; color:#1C1917; line-height:1.7; }
        .btn { display:inline-block; background:#1C1917; color:#fff; padding:12px 26px; border-radius:6px; text-decoration:none; font-size:14px; font-weight:600; }
        .footer { padding:20px 40px; background:#FAFAF9; border-top:1px solid #E7E5E4; }
        .footer-text { font-size:11px; color:#A8A29E; line-height:1.7; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <div class="logo">Koordli</div>
                <div class="logo-tag">Support</div>
            </div>
            <div class="body">
                <div class="greeting">Hi {{ $tenantName }},</div>
                <p class="text">You have a new reply on your support ticket: <strong>{{ $ticketSubject }}</strong></p>

                <div class="reply-box">{{ $replyMessage }}</div>

                <a href="{{ $ticketUrl }}" class="btn">View & Reply →</a>
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