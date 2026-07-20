<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Payment Received</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; background:#FAFAF9; color:#1C1917; }
        .wrapper { max-width:560px; margin:0 auto; padding:40px 20px; }
        .card { background:#fff; border:1px solid #E7E5E4; border-radius:8px; overflow:hidden; }
        .header { background:#1C1917; padding:32px 40px; }
        .logo { font-size:20px; font-weight:700; color:#fff; letter-spacing:-0.02em; }
        .logo-tag { font-size:10px; color:#78716C; letter-spacing:0.1em; text-transform:uppercase; margin-top:2px; }
        .banner { padding:16px 40px; background:#F5F3FF; border-bottom:1px solid #DDD6FE; display:flex; align-items:center; gap:10px; }
        .banner-icon { font-size:22px; }
        .banner-text { font-size:14px; font-weight:600; color:#5B21B6; }
        .body { padding:40px; }
        .amount-display { text-align:center; padding:20px 0 24px; }
        .amount-value { font-size:36px; font-weight:700; color:#10B981; letter-spacing:-0.02em; }
        .amount-ngn { font-size:12px; color:#A8A29E; margin-top:4px; }
        .info-box { background:#F5F5F4; border-radius:8px; padding:20px 24px; margin-bottom:20px; }
        .info-row { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #E7E5E4; font-size:13px; }
        .info-row:last-child { border-bottom:none; }
        .info-key { color:#78716C; }
        .info-val { font-weight:600; color:#1C1917; }
        .btn { display:inline-block; background:#7C3AED; color:#fff; padding:12px 24px; border-radius:6px; text-decoration:none; font-size:13px; font-weight:600; }
        .footer { padding:20px 40px; background:#FAFAF9; border-top:1px solid #E7E5E4; }
        .footer-text { font-size:11px; color:#A8A29E; line-height:1.7; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <div class="logo">Koordli</div>
                <div class="logo-tag">Platform Notification</div>
            </div>
            <div class="banner">
                <span class="banner-icon">💰</span>
                <div class="banner-text">New subscription payment received</div>
            </div>
            <div class="body">
                <div class="amount-display">
                    <div class="amount-value">{{ $currency }} {{ $amount }}</div>
                    @if($currency !== 'NGN')
                    <div class="amount-ngn">≈ ₦{{ $amountNgn }} NGN</div>
                    @endif
                </div>

                <div class="info-box">
                    <div class="info-row">
                        <span class="info-key">Company</span>
                        <span class="info-val">{{ $companyName }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Plan</span>
                        <span class="info-val">{{ $planName }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Billing Cycle</span>
                        <span class="info-val">{{ ucfirst($billingCycle) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Gateway</span>
                        <span class="info-val">{{ ucfirst($gateway) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Paid At</span>
                        <span class="info-val">{{ $paidAt }}</span>
                    </div>
                </div>

                <div style="text-align:center;">
                    <a href="{{ route('platform.billing') }}" class="btn">View Billing Dashboard →</a>
                </div>
            </div>
            <div class="footer">
                <div class="footer-text">
                    Automated notification from Koordli Platform.<br>
                    © {{ date('Y') }} Koordli. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</body>
</html>