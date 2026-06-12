@props(['feature' => 'this feature', 'message' => null])

<div style="
    border: 1px solid #DDD6FE;
    border-radius: 6px;
    padding: 16px 20px;
    background: #F5F3FF;
    display: flex;
    align-items: center;
    gap: 14px;
">
    <div style="width:36px;height:36px;background:#EDE9FE;border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#7C3AED" stroke-width="2">
            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
        </svg>
    </div>
    <div style="flex:1;">
        <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:3px;">
            Upgrade to access {{ $feature }}
        </div>
        <div style="font-size:12px;color:#78716C;">
            {{ $message ?? 'This feature is not available on your current plan.' }}
        </div>
    </div>
    <a href="#" style="background:#7C3AED;color:#fff;font-size:12px;font-weight:500;padding:7px 14px;border-radius:4px;text-decoration:none;white-space:nowrap;flex-shrink:0;">
        Upgrade
    </a>
</div>