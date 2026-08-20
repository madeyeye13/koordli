<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Digest</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; background:#FAFAF9; color:#1C1917; }
        .wrapper { max-width:560px; margin:0 auto; padding:40px 20px; }
        .card { background:#fff; border:1px solid #E7E5E4; border-radius:8px; overflow:hidden; }
        .header { background:#1C1917; padding:28px 40px; }
        .logo { font-size:20px; font-weight:700; color:#fff; }
        .body { padding:32px 40px; }
        .greeting { font-size:18px; font-weight:600; margin-bottom:16px; }
        .section-title { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#7C3AED; margin:20px 0 8px; }
        .item { font-size:13px; color:#57534E; padding:6px 0; border-bottom:1px solid #F5F5F4; }
        .item-overdue { color:#EF4444; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header"><div class="logo">Koordli</div></div>
            <div class="body">
                <div class="greeting">Hi {{ $userName }}, here's your {{ $period === 'week' ? 'weekly' : 'daily' }} summary:</div>

                @if($tasksDueToday->isNotEmpty())
                <div class="section-title">Due Today</div>
                @foreach($tasksDueToday as $t)<div class="item">{{ $t->title }}</div>@endforeach
                @endif

                @if($overdueTasks->isNotEmpty())
                <div class="section-title">Overdue</div>
                @foreach($overdueTasks as $t)<div class="item item-overdue">{{ $t->title }}</div>@endforeach
                @endif

                @if($unreadNotifications->isNotEmpty())
                <div class="section-title">Unread Notifications</div>
                @foreach($unreadNotifications as $n)<div class="item">{{ $n->data['subject'] ?? '' }}</div>@endforeach
                @endif
            </div>
        </div>
    </div>
</body>
</html>