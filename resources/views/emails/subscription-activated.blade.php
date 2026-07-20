<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Activated</title>
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
        .receipt-box { border:1px solid #E7E5E4; border-radius:8px; overflow:hidden; margin-bottom:24px; }
        .receipt-header { background:#7C3AED; padding:14px 20px; }
        .receipt-title { font-size:14px; font-weight:600; color:#fff; }
        .receipt-sub { font-size:11px; color:rgba(255,255,255,0.7); margin-top:2px; }
        .receipt-row { display:flex; justify-content:space-between; padding:12px 20px; border-bottom:1px solid #F5F5F4; }
        .receipt-row:last-child { border-bottom:none; }
        .receipt-key { font-size:12px; color:#A8A29E; }
        .receipt-val { font-size:13px; font-weight:600; color:#1C1917; }
        .receipt-amount { font-size:20px; font-weight:700; color:#7C3AED; }
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
                <span class="banner-icon">✅</span>
                <div class="banner-text">Payment received — your plan is active</div>
            </div>
            <div class="body">
                <div class="greeting">Hi {{ $tenantName }},</div>
                <p class="text">
                    Thank you for your payment. Your <strong>{{ $planName }}</strong> plan is now active and you have full access to all Koordli features.
                </p>

                <div class="receipt-box">
                    <div class="receipt-header">
                        <div class="receipt-title">Payment Receipt</div>
                        <div class="receipt-sub">{{ ucfirst($gateway) }} · {{ now()->format('D, d M Y g:i A') }}</div>
                    </div>
                    <div class="receipt-row">
                        <span class="receipt-key">Plan</span>
                        <span class="receipt-val">{{ $planName }}</span>
                    </div>
                    <div class="receipt-row">
                        <span class="receipt-key">Billing Cycle</span>
                        <span class="receipt-val">{{ ucfirst($billingCycle) }}</span>
                    </div>
                    <div class="receipt-row">
                        <span class="receipt-key">Amount Paid</span>
                        <span class="receipt-amount">{{ $currency }} {{ $amount }}</span>
                    </div>
                    <div class="receipt-row">
                        <span class="receipt-key">Next Renewal</span>
                        <span class="receipt-val">{{ $expiresAt }}</span>
                    </div>
                </div>

                <p class="text" style="margin-bottom:20px;">
                    Since renewals are manual, we'll email you a reminder before your plan expires so you never lose access unexpectedly.
                </p>

                <a href="{{ route('tenant.dashboard') }}" class="btn">Go to Dashboard →</a>
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