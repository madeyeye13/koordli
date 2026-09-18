<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>New Koordli Tester Feedback</title></head>
<body style="margin:0;background:#FAFAF9;color:#1C1917;font-family:Arial,sans-serif;">
    <div style="max-width:620px;margin:0 auto;padding:32px 20px;">
        <div style="background:#1C1917;color:#fff;padding:28px 32px;border-radius:10px 10px 0 0;"><strong style="font-size:20px;">Koordli</strong><div style="font-size:11px;color:#A8A29E;margin-top:4px;letter-spacing:.08em;text-transform:uppercase;">Tester feedback</div></div>
        <div style="background:#fff;border:1px solid #E7E5E4;border-top:0;padding:32px;">
            <h1 style="font-size:22px;margin:0 0 8px;">New feedback from {{ $feedback->name }}</h1>
            <p style="font-size:14px;line-height:1.6;color:#57534E;margin-bottom:24px;">A tester has submitted feedback through the public Koordli survey.</p>
            @foreach([
                'Experience' => \Illuminate\Support\Str::headline($feedback->experience),
                'Navigation' => $feedback->navigation_rating . '/5',
                'Clarity' => $feedback->clarity_rating . '/5',
                'Most useful' => collect($feedback->most_useful ?? [])->map(fn ($feature) => \Illuminate\Support\Str::headline($feature))->join(', '),
                'Real-event likelihood' => $feedback->likelihood . '/5',
                'Overall rating' => $feedback->overall_rating . '/5',
            ] as $label => $value)
            <div style="display:flex;justify-content:space-between;gap:16px;padding:10px 0;border-bottom:1px solid #E7E5E4;font-size:13px;"><span style="color:#78716C;">{{ $label }}</span><strong>{{ $value }}</strong></div>
            @endforeach
            @if($feedback->had_confusion)
            <h3 style="font-size:13px;text-transform:uppercase;letter-spacing:.06em;color:#78716C;margin:24px 0 8px;">What confused them</h3><p style="font-size:14px;line-height:1.6;white-space:pre-wrap;">{{ $feedback->confusion_details }}</p>
            @endif
            <h3 style="font-size:13px;text-transform:uppercase;letter-spacing:.06em;color:#78716C;margin:24px 0 8px;">Suggested improvement</h3><p style="font-size:14px;line-height:1.6;white-space:pre-wrap;">{{ $feedback->improvement }}</p>
        </div>
        <div style="font-size:11px;color:#A8A29E;padding:16px 0;">Submitted {{ $feedback->created_at->format('M d, Y g:i A') }}.</div>
    </div>
</body>
</html>
