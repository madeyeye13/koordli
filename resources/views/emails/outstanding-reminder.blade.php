<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Reminder</title>
    <style>
        * { margin:0;padding:0;box-sizing:border-box; }
        body { font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;background:#FFF7ED;color:#1C1917; }
        .wrapper { max-width:560px;margin:0 auto;padding:40px 20px; }
        .card { background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.06); }
        .header { background:linear-gradient(135deg,#1C1917,#292524);padding:32px 40px;position:relative;overflow:hidden; }
        .header-circle { position:absolute;top:-30px;right:-30px;width:120px;height:120px;border-radius:50%;background:rgba(245,158,11,0.15); }
        .logo { font-size:20px;font-weight:800;color:#fff;letter-spacing:-0.02em;position:relative;z-index:1; }
        .logo span { color:#F59E0B; }
        .alert-banner { background:linear-gradient(135deg,#F59E0B,#D97706);padding:18px 40px;display:flex;align-items:center;gap:12px; }
        .alert-icon { font-size:24px;flex-shrink:0; }
        .alert-text { color:#fff; }
        .alert-title { font-size:14px;font-weight:700;margin-bottom:2px; }
        .alert-sub { font-size:12px;opacity:0.85; }
        .body { padding:36px 40px; }
        .greeting { font-size:20px;font-weight:700;color:#1C1917;margin-bottom:10px; }
        .text { font-size:14px;color:#57534E;line-height:1.8;margin-bottom:24px; }
        .summary { border:1px solid #E7E5E4;border-radius:10px;overflow:hidden;margin-bottom:24px; }
        .summary-header { background:#F5F5F4;padding:12px 20px;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#78716C; }
        .summary-row { display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-bottom:1px solid #F5F5F4; }
        .summary-row:last-child { border-bottom:none; }
        .summary-label { font-size:13px;color:#78716C; }
        .summary-value { font-size:13px;font-weight:600;color:#1C1917; }
        .outstanding-box { background:linear-gradient(135deg,#FEF3C7,#FDE68A);border:2px solid #F59E0B;border-radius:10px;padding:20px;margin-bottom:24px;text-align:center; }
        .outstanding-label { font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#92400E;margin-bottom:6px; }
        .outstanding-amount { font-size:32px;font-weight:800;color:#92400E;letter-spacing:-0.02em; }
        .btn-wrap { text-align:center;margin-bottom:24px; }
        .btn { display:inline-block;background:#F59E0B;color:#fff;padding:13px 32px;border-radius:8px;text-decoration:none;font-size:14px;font-weight:600; }
        .note { font-size:12px;color:#A8A29E;line-height:1.7; }
        .footer { padding:20px 40px;background:#FAFAF9;border-top:1px solid #E7E5E4;text-align:center; }
        .footer-text { font-size:11px;color:#A8A29E;line-height:1.7; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="header">
            <div class="header-circle"></div>
            <div class="logo">Koord<span>li</span></div>
        </div>

        <div class="alert-banner">
            <div class="alert-icon">💳</div>
            <div class="alert-text">
                <div class="alert-title">Payment Reminder</div>
                <div class="alert-sub">Sent on behalf of {{ $companyName }}</div>
            </div>
        </div>

        <div class="body">
            <div class="greeting">Dear {{ $clientName }},</div>
            <p class="text">
                This is a friendly reminder from <strong>{{ $companyName }}</strong> regarding the outstanding
                balance for your upcoming event <strong>{{ $eventName }}</strong>.
                We kindly ask that you arrange payment at your earliest convenience.
            </p>

            <div class="summary">
                <div class="summary-header">Payment Summary — {{ $eventName }}</div>
                <div class="summary-row">
                    <span class="summary-label">Agreed Budget</span>
                    <span class="summary-value">{{ $currency }}{{ $agreedBudget }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Amount Received</span>
                    <span class="summary-value" style="color:#10B981;">{{ $currency }}{{ $amountPaid }}</span>
                </div>
            </div>

            <div class="outstanding-box">
                <div class="outstanding-label">Outstanding Balance</div>
                <div class="outstanding-amount">{{ $currency }}{{ $outstanding }}</div>
            </div>

            <p class="text">
                Please contact us to arrange payment or if you have any questions about this balance.
                We look forward to delivering an exceptional event for you.
            </p>

            <div class="note">
                This reminder was sent by <strong>{{ $companyName }}</strong> via Koordli.
                If you believe this was sent in error, please contact {{ $companyName }} directly.
            </div>
        </div>

        <div class="footer">
            <div class="footer-text">
                © {{ date('Y') }} Koordli · Event Operations Platform<br>
                Sent on behalf of {{ $companyName }}
            </div>
        </div>
    </div>
</div>
</body>
</html>