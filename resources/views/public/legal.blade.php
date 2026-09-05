<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - {{ config('app.name', 'Koordli') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@40..144,300..400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="krd-body">
    <main style="max-width:820px;margin:0 auto;padding:48px 24px 72px;">
        <a href="{{ route('landing') }}" style="display:inline-block;color:#7C3AED;text-decoration:none;font-size:13px;font-weight:600;margin-bottom:28px;">{{ config('app.name', 'Koordli') }}</a>
        <article class="krd-card" style="padding:32px 36px;">
            <h1 style="font-family:'Fraunces',serif;font-size:32px;font-weight:600;color:#1C1917;margin:0 0 24px;">{{ $title }}</h1>
            <div style="font-family:'Fraunces',serif;white-space:pre-line;font-size:15px;line-height:1.85;color:#57534E;">{{ $content }}</div>
        </article>
    </main>
</body>
</html>