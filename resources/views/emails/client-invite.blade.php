<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You're invited to view your event on Koordli</title>
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
        .creds { background: #F5F3FF; border: 1px solid #DDD6FE; border-radius: 8px; padding: 20px 24px; margin-bottom: 24px; }
        .creds-label { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #7C3AED; margin-bottom: 12px; }
        .cred-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #EDE9FE; font-size: 13px; }
        .cred-row:last-child { border-bottom: none; }
        .cred-key { color: #78716C; }
        .cred-val { font-weight: 600; color: #1C1917; font-family: monospace; }
        .btn { display: inline-block; background: #7C3AED; color: #FFFFFF; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500; margin-bottom: 24px; }
        .divider { height: 1px; background: #E7E5E4; margin: 0 0 24px; }
        .security { font-size: 12px; color: #A8A29E; line-height: 1.7; }
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
                <div class="greeting">Hi {{ $clientName }},</div>
                <p class="text">
                    <strong>{{ $companyName }}</strong> has invited you to view the details of your event
                    <strong>{{ $eventName }}</strong> through your dedicated client portal on Koordli.
                    Use the credentials below to log in and track your event.
                </p>

                <div class="creds">
                    <div class="creds-label">Your Login Credentials</div>
                    <div class="cred-row">
                        <span class="cred-key">Portal URL</span>
                        <span class="cred-val">{{ url('/client/login') }}</span>
                    </div>
                    <div class="cred-row">
                        <span class="cred-key">Email</span>
                        <span class="cred-val">{{ $clientEmail }}</span>
                    </div>
                    <div class="cred-row">
                        <span class="cred-key">Password</span>
                        <span class="cred-val">{{ $password }}</span>
                    </div>
                </div>

                <a href="{{ url('/client/login') }}" class="btn">Access Your Event Portal →</a>

                <div class="divider"></div>

                <div class="security">
                    <strong style="color:#78716C;">Keep your credentials safe.</strong>
                    Never share your password with anyone. If you have questions about your event,
                    contact <strong style="color:#78716C;">{{ $companyName }}</strong> directly.<br><br>
                    If you believe this email was sent in error, you can safely ignore it.
                </div>
            </div>

            <div class="footer">
                <div class="footer-text">
                    This email was sent to {{ $clientEmail }}.<br>
                    © {{ date('Y') }} Koordli. All rights reserved.
                </div>
            </div>

        </div>
    </div>
</body>
</html>