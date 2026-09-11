<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Account Suspended</title>
</head>
<body style="margin:0;background:#FAFAF9;color:#1C1917;font-family:Arial,sans-serif;">
    <div style="max-width:560px;margin:0 auto;padding:40px 20px;">
        <div style="background:#fff;border:1px solid #E7E5E4;border-radius:8px;overflow:hidden;">
            <div style="background:#1C1917;padding:32px 40px;color:#fff;font-size:20px;font-weight:700;">Koordli</div>
            <div style="padding:16px 40px;background:#FEF3C7;border-bottom:1px solid #FDE68A;color:#92400E;font-size:14px;font-weight:600;">Your company account has been suspended</div>
            <div style="padding:40px;">
                <h1 style="font-size:22px;margin:0 0 12px;">Hi {{ $tenantName }},</h1>
                <p style="font-size:14px;color:#57534E;line-height:1.7;">Your Koordli company account has been suspended by the platform administrator. Workspace access is currently unavailable, but your data remains safe and has not been deleted.</p>
                <p style="font-size:14px;color:#57534E;line-height:1.7;">Please review your account or contact the Koordli platform team if you believe this was a mistake.</p>
                <a href="{{ $supportUrl }}" style="display:inline-block;background:#7C3AED;color:#fff;padding:13px 28px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;">Review Account</a>
            </div>
            <div style="padding:20px 40px;background:#FAFAF9;border-top:1px solid #E7E5E4;color:#A8A29E;font-size:11px;">This email was sent to {{ $tenantEmail }}.</div>
        </div>
    </div>
</body>
</html>
