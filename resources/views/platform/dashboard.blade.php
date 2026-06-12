<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Platform Dashboard — Koordli</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="krd-body">

    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; flex-direction: column; gap: 16px;">

        <div style="font-size: 22px; font-weight: 700; color: #1C1917; letter-spacing: -0.02em;">
            Koordli
        </div>

        <div class="krd-card" style="max-width: 420px; width: 100%; text-align: center;">
            <div style="font-size: 32px; margin-bottom: 12px;">✅</div>
            <div style="font-size: 18px; font-weight: 600; color: #1C1917; margin-bottom: 8px;">
                Platform login successful
            </div>
            <div style="font-size: 13px; color: #78716C; margin-bottom: 24px;">
                You are signed in as the platform owner. The full dashboard is being built next.
            </div>
            <div style="font-size: 12px; color: #A8A29E; padding: 12px; background: #F5F5F4; border-radius: 6px;">
                Logged in as: <strong>admin@koordli.com</strong>
            </div>
        </div>

        <form method="POST" action="/platform/logout" style="margin-top: 8px;">
            @csrf
            <button type="submit" class="krd-btn krd-btn-ghost" style="font-size: 12px; color: #A8A29E;">
                Sign out
            </button>
        </form>

    </div>

</body>
</html>