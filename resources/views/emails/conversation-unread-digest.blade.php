<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family:Helvetica,Arial,sans-serif;background:#FAFAF9;margin:0;padding:0;">
    <div style="max-width:480px;margin:0 auto;padding:40px 20px;">
        <div style="background:#fff;border:1px solid #E7E5E4;border-radius:8px;padding:32px;">
            <div style="font-size:18px;font-weight:700;color:#1C1917;margin-bottom:4px;">
                {{ $whiteLabel ? $companyName : 'Koordli' }}
            </div>
            <div style="font-size:13px;color:#78716C;margin-bottom:24px;">{{ $eventName }}</div>

            <p style="font-size:14px;color:#1C1917;line-height:1.6;">Hi {{ $recipientName }},</p>
            <p style="font-size:14px;color:#1C1917;line-height:1.6;">
                You have {{ $unreadCount }} unread message{{ $unreadCount === 1 ? '' : 's' }} in
                <strong>{{ $conversationName }}</strong> for {{ $eventName }}.
            </p>

            <a href="{{ $portalUrl }}" style="display:inline-block;margin-top:16px;background:#7C3AED;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;">
                View Conversation
            </a>
        </div>
    </div>
</body>
</html>