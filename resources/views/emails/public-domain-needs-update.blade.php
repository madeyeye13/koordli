<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSVP domain needs an update</title>
</head>
<body style="margin:0;background:#FAFAF9;color:#1C1917;font-family:Arial,Helvetica,sans-serif;">
    <div style="max-width:560px;margin:0 auto;padding:40px 20px;">
        <div style="background:#FFFFFF;border:1px solid #E7E5E4;border-radius:8px;overflow:hidden;">
            <div style="background:#1C1917;padding:28px 36px;color:#FFFFFF;font-size:20px;font-weight:700;">Koordli</div>
            <div style="padding:36px;">
                <h1 style="font-size:22px;margin:0 0 14px;">Your RSVP domain needs an update</h1>
                <p style="font-size:14px;line-height:1.7;color:#57534E;">
                    Hi {{ $recipientName }}, the custom RSVP domain for <strong>{{ $eventName }}</strong> is no longer pointing to Koordli correctly.
                </p>
                <p style="font-size:14px;line-height:1.7;color:#57534E;">{{ $failureMessage }}</p>
                <div style="background:#F5F5F4;border:1px solid #E7E5E4;border-radius:6px;padding:16px;margin:20px 0;font-family:monospace;font-size:12px;line-height:1.8;">
                    <div><strong>Domain:</strong> {{ $domain }}</div>
                    <div><strong>Record type:</strong> {{ $domainType === 'apex' ? 'A' : 'CNAME' }}</div>
                    <div><strong>Name:</strong> {{ $domainType === 'apex' ? '@' : $domain }}</div>
                    <div><strong>Expected value:</strong> {{ $expectedDnsValue }}</div>
                    @if($observedDnsValue)<div><strong>Detected value:</strong> {{ $observedDnsValue }}</div>@endif
                </div>
                @if($domainType === 'apex')
                <p style="font-size:14px;line-height:1.7;color:#57534E;">Update the A record at your domain registrar to the expected value above. Your normal Koordli RSVP link continues working.</p>
                @else
                <p style="font-size:14px;line-height:1.7;color:#57534E;">Update the CNAME record at your domain registrar to the expected value above. Your normal Koordli RSVP link continues working.</p>
                @endif
                <p><a href="{{ $managerUrl }}" style="display:inline-block;background:#7C3AED;color:#FFFFFF;padding:12px 22px;border-radius:6px;text-decoration:none;font-size:14px;">Open RSVP Manager</a></p>
            </div>
        </div>
    </div>
</body>
</html>
