<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Received</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #FAFAF9; color: #1C1917; }
        .wrapper { max-width: 560px; margin: 0 auto; padding: 40px 20px; }
        .card { background: #FFFFFF; border: 1px solid #E7E5E4; border-radius: 8px; overflow: hidden; }
        .header { background: #1C1917; padding: 32px 40px; }
        .logo { font-size: 20px; font-weight: 700; color: #FFFFFF; letter-spacing: -0.02em; }
        .logo-tag { font-size: 10px; color: #78716C; letter-spacing: 0.1em; text-transform: uppercase; margin-top: 2px; }
        .body { padding: 40px; }
        .greeting { font-size: 22px; font-weight: 600; color: #1C1917; margin-bottom: 12px; }
        .text { font-size: 14px; color: #57534E; line-height: 1.7; margin-bottom: 24px; }
        .info-box { background: #FEF3C7; border: 1px solid #FDE68A; border-radius: 8px; padding: 16px 20px; margin-bottom: 24px; }
        .info-box-text { font-size: 13px; color: #92400E; line-height: 1.7; }
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
            <div class="body">
                <div class="greeting">Hi {{ $vendorName }},</div>
                <p class="text">
                    Thank you for applying to join the <strong>{{ $companyName }}</strong> vendor network
                    on Koordli. We've received your application for <strong>{{ $businessName }}</strong>
                    and it is currently under review.
                </p>
                <div class="info-box">
                    <div class="info-box-text">
                        ⏳ <strong>What happens next?</strong><br>
                        The team at {{ $companyName }} will review your application and get back to you
                        via email. This usually takes a few business days.
                    </div>
                </div>
                <div class="divider"></div>
                <div class="note">
                    If you have any questions in the meantime, contact
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