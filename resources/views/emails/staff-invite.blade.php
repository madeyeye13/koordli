<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You're invited to Koordli</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #F5F3FF; color: #1C1917; }
        .wrapper { max-width: 580px; margin: 0 auto; padding: 48px 20px; }

        .card { background: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(124,58,237,0.08); }

        .header { background: linear-gradient(135deg, #1C1917 0%, #292524 100%); padding: 40px; position: relative; overflow: hidden; }
        .header-accent { position: absolute; top: -40px; right: -40px; width: 160px; height: 160px; border-radius: 50%; background: rgba(124,58,237,0.15); }
        .header-accent-2 { position: absolute; bottom: -20px; left: 20px; width: 80px; height: 80px; border-radius: 50%; background: rgba(245,158,11,0.1); }
        .logo { font-size: 22px; font-weight: 800; color: #FFFFFF; letter-spacing: -0.03em; position: relative; z-index: 1; }
        .logo span { color: #7C3AED; }
        .header-tagline { font-size: 11px; color: #78716C; letter-spacing: 0.12em; text-transform: uppercase; margin-top: 4px; position: relative; z-index: 1; }

        .invite-banner { background: linear-gradient(135deg, #7C3AED, #6D28D9); padding: 20px 40px; display: flex; align-items: center; gap: 14px; }
        .invite-icon { width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 20px; }
        .invite-text { color: #FFFFFF; }
        .invite-text-title { font-size: 14px; font-weight: 600; margin-bottom: 2px; }
        .invite-text-sub { font-size: 12px; color: rgba(255,255,255,0.7); }

        .body { padding: 40px; }

        .greeting { font-size: 24px; font-weight: 700; color: #1C1917; margin-bottom: 10px; letter-spacing: -0.02em; }
        .text { font-size: 14px; color: #57534E; line-height: 1.8; margin-bottom: 28px; }
        .text strong { color: #1C1917; }

        .creds { background: #FAFAF9; border: 1px solid #E7E5E4; border-radius: 10px; overflow: hidden; margin-bottom: 28px; }
        .creds-header { background: #F5F3FF; padding: 12px 20px; border-bottom: 1px solid #E7E5E4; display: flex; align-items: center; gap: 8px; }
        .creds-header-dot { width: 8px; height: 8px; border-radius: 50%; background: #7C3AED; }
        .creds-header-text { font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #7C3AED; }
        .cred-row { display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-bottom: 1px solid #F5F5F4; }
        .cred-row:last-child { border-bottom: none; }
        .cred-key { font-size: 12px; color: #78716C; font-weight: 500; }
        .cred-val { font-size: 13px; font-weight: 600; color: #1C1917; font-family: 'Courier New', monospace; background: #F5F3FF; padding: 3px 10px; border-radius: 4px; color: #7C3AED; }
        .cred-val-plain { font-size: 13px; font-weight: 500; color: #1C1917; }

        .btn-wrap { text-align: center; margin-bottom: 32px; }
        .btn { display: inline-block; background: #7C3AED; color: #FFFFFF; padding: 14px 36px; border-radius: 8px; text-decoration: none; font-size: 15px; font-weight: 600; letter-spacing: -0.01em; }

        .steps { margin-bottom: 28px; }
        .steps-title { font-size: 12px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #A8A29E; margin-bottom: 14px; }
        .step { display: flex; gap: 14px; margin-bottom: 12px; align-items: flex-start; }
        .step-num { width: 24px; height: 24px; border-radius: 50%; background: #EDE9FE; color: #7C3AED; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
        .step-text { font-size: 13px; color: #57534E; line-height: 1.6; }
        .step-text strong { color: #1C1917; }

        .warning { background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 14px 18px; margin-bottom: 28px; display: flex; gap: 10px; align-items: flex-start; }
        .warning-icon { font-size: 16px; flex-shrink: 0; margin-top: 1px; }
        .warning-text { font-size: 12px; color: #92400E; line-height: 1.7; }

        .divider { height: 1px; background: #F5F5F4; margin: 0 0 24px; }

        .footer { padding: 24px 40px; background: #FAFAF9; border-top: 1px solid #E7E5E4; }
        .footer-text { font-size: 11px; color: #A8A29E; line-height: 1.8; text-align: center; }
        .footer-brand { font-size: 12px; font-weight: 700; color: #1C1917; letter-spacing: -0.01em; margin-bottom: 4px; text-align: center; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">

            {{-- Header --}}
            <div class="header">
                <div class="header-accent"></div>
                <div class="header-accent-2"></div>
                <div class="logo">Koord<span>li</span></div>
                <div class="header-tagline">Event Operations Platform</div>
            </div>

            {{-- Invite Banner --}}
            <div class="invite-banner">
                <div class="invite-icon">🎉</div>
                <div class="invite-text">
                    <div class="invite-text-title">You've been invited to join a workspace</div>
                    <div class="invite-text-sub">{{ $inviterName }} is waiting for you on Koordli</div>
                </div>
            </div>

            {{-- Body --}}
            <div class="body">
                <div class="greeting">Welcome, {{ $staffName }}!</div>
                <p class="text">
                    <strong>{{ $inviterName }}</strong> has added you to the
                    <strong>{{ $companyName }}</strong> workspace on Koordli.
                    Use the credentials below to log in and get started.
                </p>

                {{-- Credentials --}}
                <div class="creds">
                    <div class="creds-header">
                        <div class="creds-header-dot"></div>
                        <div class="creds-header-text">Your Login Credentials</div>
                    </div>
                    <div class="cred-row">
                        <span class="cred-key">Login URL</span>
                        <span class="cred-val-plain">{{ url('/login') }}</span>
                    </div>
                    <div class="cred-row">
                        <span class="cred-key">Email Address</span>
                        <span class="cred-val-plain">{{ $staffEmail }}</span>
                    </div>
                    <div class="cred-row">
                        <span class="cred-key">Temporary Password</span>
                        <span class="cred-val">{{ $tempPassword }}</span>
                    </div>
                </div>

                {{-- CTA --}}
                <div class="btn-wrap">
                    <a href="{{ url('/login') }}" class="btn">Access Your Workspace →</a>
                </div>

                {{-- Steps --}}
                <div class="steps">
                    <div class="steps-title">Getting started</div>
                    <div class="step">
                        <div class="step-num">1</div>
                        <div class="step-text">Click the button above or visit <strong>{{ url('/login') }}</strong></div>
                    </div>
                    <div class="step">
                        <div class="step-num">2</div>
                        <div class="step-text">Log in with your email and the temporary password above</div>
                    </div>
                    <div class="step">
                        <div class="step-num">3</div>
                        <div class="step-text"><strong>Change your password immediately</strong> when prompted</div>
                    </div>
                    <div class="step">
                        <div class="step-num">4</div>
                        <div class="step-text">Start collaborating on events with your team</div>
                    </div>
                </div>

                {{-- Warning --}}
                <div class="warning">
                    <div class="warning-icon">⚠️</div>
                    <div class="warning-text">
                        <strong>Security reminder:</strong> Never share your password with anyone, including {{ $inviterName }}.
                        Change your temporary password immediately after your first login.
                    </div>
                </div>

                <div class="divider"></div>

                <div style="font-size:12px;color:#A8A29E;line-height:1.7;">
                    If you believe this invitation was sent in error, you can safely ignore this email.
                    This invitation was sent by <strong style="color:#78716C;">{{ $inviterName }}</strong> from
                    <strong style="color:#78716C;">{{ $companyName }}</strong>.
                </div>
            </div>

            {{-- Footer --}}
            <div class="footer">
                <div class="footer-brand">Koordli</div>
                <div class="footer-text">
                    This email was sent to {{ $staffEmail }}.<br>
                    © {{ date('Y') }} Koordli. All rights reserved.
                </div>
            </div>

        </div>
    </div>
</body>
</html>