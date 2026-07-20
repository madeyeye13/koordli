<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contract Signed</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; background:#FAFAF9; color:#1C1917; }
        .wrapper { max-width:560px; margin:0 auto; padding:40px 20px; }
        .card { background:#fff; border:1px solid #E7E5E4; border-radius:8px; overflow:hidden; }
        .header { background:#1C1917; padding:32px 40px; }
        .logo { font-size:20px; font-weight:700; color:#fff; letter-spacing:-0.02em; }
        .logo-tag { font-size:10px; color:#78716C; letter-spacing:0.1em; text-transform:uppercase; margin-top:2px; }
        .banner { padding:16px 40px; background:#F0FDF4; border-bottom:1px solid #86EFAC; display:flex; align-items:center; gap:10px; }
        .banner-icon { font-size:22px; }
        .banner-text { font-size:14px; font-weight:600; color:#166534; }
        .body { padding:40px; }
        .greeting { font-size:22px; font-weight:600; color:#1C1917; margin-bottom:12px; }
        .text { font-size:14px; color:#57534E; line-height:1.7; margin-bottom:24px; }
        .info-box { background:#F5F5F4; border-radius:8px; padding:16px 20px; margin-bottom:24px; }
        .info-row { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #E7E5E4; font-size:13px; }
        .info-row:last-child { border-bottom:none; }
        .info-key { color:#78716C; }
        .info-val { font-weight:600; color:#1C1917; }
        .btn { display:inline-block; background:#1C1917; color:#fff; padding:13px 28px; border-radius:6px; text-decoration:none; font-size:14px; font-weight:600; }
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
                <span class="banner-icon">{{ $fullySigned ? '✅' : '✍️' }}</span>
                <div class="banner-text">
                    {{ $fullySigned ? 'Contract fully executed' : 'Vendor has signed' }}
                </div>
            </div>
            <div class="body">
                <div class="greeting">Hi {{ $plannerName }},</div>
                <p class="text">
                    @if($fullySigned)
                        Great news — <strong>{{ $vendorName }}</strong> has signed the contract, and both parties have now completed their signatures. This contract is fully executed.
                    @else
                        <strong>{{ $vendorName }}</strong> has signed your contract. If you haven't already, add your own signature to complete this agreement.
                    @endif
                </p>

                <div class="info-box">
                    <div class="info-row">
                        <span class="info-key">Contract</span>
                        <span class="info-val">{{ $contractTitle }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Signed by</span>
                        <span class="info-val">{{ $vendorName }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Signed at</span>
                        <span class="info-val">{{ $signedAt }}</span>
                    </div>
                </div>

                <a href="{{ $contractUrl }}" class="btn">View Contract →</a>
            </div>
            <div class="footer">
                <div class="footer-text">
                    This email was sent to {{ $plannerEmail }}.<br>
                    © {{ date('Y') }} Koordli. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</body>
</html>