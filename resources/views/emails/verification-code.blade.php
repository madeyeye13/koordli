<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify your Koordli account</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #FAFAF9; color: #1C1917; }
        .wrapper { max-width: 560px; margin: 0 auto; padding: 40px 20px; }
        .card { background: #FFFFFF; border: 1px solid #E7E5E4; border-radius: 8px; overflow: hidden; }
        .header { background: #1C1917; padding: 32px 40px; }
        .logo { font-size: 20px; font-weight: 700; color: #FFFFFF; letter-spacing: -0.02em; }
        .logo-tag { font-size: 10px; color: #78716C; letter-spacing: 0.1em; text-transform: uppercase; margin-top: 2px; }
        .body { padding: 40px; }
        .greeting { font-size: 22px; font-weight: 600; color: #1C1917; margin-bottom: 12px; letter-spacing: -0.01em; }
        .text { font-size: 14px; color: #57534E; line-height: 1.7; margin-bottom: 32px; }
        .code-wrapper { background: #F5F3FF; border: 1px solid #DDD6FE; border-radius: 8px; padding: 24px; text-align: center; margin-bottom: 32px; }
        .code-label { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #7C3AED; margin-bottom: 12px; }
        .code { font-size: 40px; font-weight: 700; color: #1C1917; letter-spacing: 0.15em; }
        .code-expiry { font-size: 12px; color: #A8A29E; margin-top: 10px; }
        .divider { height: 1px; background: #E7E5E4; margin: 0 0 32px; }
        .security-note { font-size: 12px; color: #A8A29E; line-height: 1.7; }
        .security-note strong { color: #78716C; }
        .footer { padding: 24px 40px; background: #FAFAF9; border-top: 1px solid #E7E5E4; }
        .footer-text { font-size: 11px; color: #A8A29E; line-height: 1.7; }
        .footer-brand { font-size: 12px; font-weight: 600; color: #57534E; margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">

            {{-- Header --}}
            <div class="header">
                <div class="logo">Koordli</div>
                <div class="logo-tag">Event Operations Platform</div>
            </div>

            {{-- Body --}}
            <div class="body">
                <div class="greeting">Hi {{ $name }},</div>
                <p class="text">
                    Welcome to Koordli. To complete your registration and verify your email address,
                    please use the verification code below. This code is valid for <strong>15 minutes</strong>.
                </p>

                {{-- Code --}}
                <div class="code-wrapper">
                    <div class="code-label">Your verification code</div>
                    <div class="code">{{ $code }}</div>
                    <div class="code-expiry">Expires in 15 minutes</div>
                </div>

                <div class="divider"></div>

                {{-- Security Note --}}
                <div class="security-note">
                    <strong>Didn't create a Koordli account?</strong><br>
                    If you did not request this, you can safely ignore this email.
                    Someone may have entered your email address by mistake.
                    Your account will not be created without verification.
                    <br><br>
                    <strong>Never share this code with anyone.</strong>
                    Koordli will never ask for your verification code.
                </div>
            </div>

            {{-- Footer --}}
            <div class="footer">
                <div class="footer-brand">Koordli</div>
                <div class="footer-text">
                    This email was sent to {{ $email }}.<br>
                    © {{ date('Y') }} Koordli. All rights reserved.
                </div>
            </div>

        </div>
    </div>
</body>
</html>