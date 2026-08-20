<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied — Koordli</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#FAFAF9;padding:24px;">

    <div style="max-width:420px;width:100%;text-align:center;">

        <div style="margin-bottom:24px;display:flex;justify-content:center;">
            @if (auth('web')->check() || auth('client')->check() || auth('vendor')->check() || auth('platform')->check())
                <x-ui.logo color="auto" />
            @else
                <span style="font-size:20px;font-weight:700;color:#1C1917;">Koordli</span>
            @endif
        </div>

        <div class="krd-card" style="padding:32px 24px;">
            <div style="width:56px;height:56px;background:#FEF3C7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="#D97706" stroke-width="2">
                    <rect x="3" y="11" width="18" height="10" rx="2"/>
                    <path d="M7 11V7a5 5 0 0110 0v4"/>
                </svg>
            </div>

            <h1 style="font-size:18px;font-weight:700;color:#1C1917;margin:0 0 8px;">
                You don't have access to this
            </h1>
            <p style="font-size:13px;color:#78716C;line-height:1.6;margin:0 0 24px;">
                Your current role doesn't include permission for this page. If you think this is a mistake, ask your account owner to update your access under Staff → Roles &amp; Permissions.
            </p>

            @if (auth('web')->check())
                <a href="{{ route('tenant.dashboard') }}" wire:navigate class="krd-btn krd-btn-primary" style="width:100%;">
                    Back to Dashboard
                </a>
            @elseif (auth('client')->check())
                <a href="{{ route('client.dashboard') }}" wire:navigate class="krd-btn krd-btn-primary" style="width:100%;">
                    Back to Dashboard
                </a>
            @elseif (auth('vendor')->check())
                <a href="{{ route('vendor.dashboard') }}" wire:navigate class="krd-btn krd-btn-primary" style="width:100%;">
                    Back to Dashboard
                </a>
            @elseif (auth('platform')->check())
                <a href="{{ route('platform.dashboard') }}" wire:navigate class="krd-btn krd-btn-primary" style="width:100%;">
                    Back to Dashboard
                </a>
            @else
                <a href="{{ route('landing') }}" class="krd-btn krd-btn-primary" style="width:100%;">
                    Go to Homepage
                </a>
            @endif
        </div>

    </div>

</body>
</html>