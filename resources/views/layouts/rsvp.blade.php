<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'RSVP' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,400;1,9..144,600&family=Spline+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            font-family: 'Spline Sans', sans-serif;
            background: #FAFAF9;
            color: #1C1917;
            -webkit-font-smoothing: antialiased;
        }
        [x-cloak] { display: none !important; }
    </style>
    @livewireStyles
</head>
<body>
    <div id="krd-toast-container"
         class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none">
    </div>

    {{ $slot }}

    @livewireScripts
</body>
</html>