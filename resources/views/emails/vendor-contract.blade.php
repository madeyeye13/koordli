<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vendor Contract</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; background:#FAFAF9; color:#1C1917; }
        .wrapper { max-width:560px; margin:0 auto; padding:40px 20px; }
        .card { background:#fff; border:1px solid #E7E5E4; border-radius:8px; overflow:hidden; }
        .header { background:#1C1917; padding:32px 40px; }
        .logo { font-size:20px; font-weight:700; color:#fff; letter-spacing:-0.02em; }
        .logo-tag { font-size:10px; color:#78716C; letter-spacing:0.1em; text-transform:uppercase; margin-top:2px; }
        .body { padding:40px; }
        .greeting { font-size:22px; font-weight:600; color:#1C1917; margin-bottom:12px; }
        .text { font-size:14px; color:#57534E; line-height:1.7; margin-bottom:24px; }
        .info-box { background:#F5F3FF; border:1px solid #DDD6FE; border-radius:8px; padding:16px 20px; margin-bottom:24px; }
        .info-title { font-size:13px; font-weight:600; color:#5B21B6; }
        .attach-note { font-size:12px; color:#78716C; margin-top:4px; }
        .btn { display:inline-block; background:#1C1917; color:#fff; padding:13px 28px; border-radius:6px; text-decoration:none; font-size:14px; font-weight:600; }
        .footer { padding:20px 40px; background:#FAFAF9; border-top:1px solid #E7E5E4; }
        .footer-text { font-size:11px; color:#A8A29E; line-height:1.7; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <div class="logo">{{ $whiteLabel ? $companyName : 'Koordli' }}</div>
                @if(!$whiteLabel)
                <div class="logo-tag">Event Operations Platform</div>
                @endif
            </div>
            <div class="body">
                <div class="greeting">Hi {{ $vendorName }},</div>
                <p class="text">
                    {{ $companyName }} has sent you a contract for your review. Please find the details below.
                </p>

                <div class="info-box">
                    <div class="info-title">📄 {{ $contractTitle }}</div>
                    <div class="attach-note">The full contract is attached as a PDF to this email.</div>
                </div>

                <p class="text" style="margin-bottom:20px;">
                    Please review the attached document carefully. If you agree to the terms, print, sign, and return a scanned copy to {{ $companyName }}, or contact them directly with any questions.
                </p>

                <a href="{{ $viewUrl }}" class="btn">View Contract Online →</a>
            </div>
            <div class="footer">
                <div class="footer-text">
                    This email was sent to {{ $vendorEmail }} on behalf of {{ $companyName }}.<br>
                    © {{ date('Y') }} {{ $whiteLabel ? $companyName : 'Koordli' }}. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</body>
</html>