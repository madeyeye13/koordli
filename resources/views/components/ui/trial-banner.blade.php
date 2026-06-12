@php
    $gate     = app(\App\Services\FeatureGateService::class);
    $tenant   = auth()->user()?->tenant;
    $onTrial  = $tenant && $gate->isOnTrial($tenant);
    $highest  = $tenant && $gate->isHighestPlan($tenant);
    $daysLeft = $onTrial ? $gate->trialDaysLeft($tenant) : 0;
@endphp

@if($onTrial && !$highest)
<div style="background:linear-gradient(135deg,#7C3AED 0%,#6D28D9 100%);padding:10px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
    <div style="display:flex;align-items:center;gap:10px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#DDD6FE" stroke-width="2">
            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
        </svg>
        <span style="font-size:13px;color:#FFFFFF;font-weight:500;">
            @if($daysLeft > 0)
                Your free trial ends in <strong>{{ $daysLeft }} {{ Str::plural('day', $daysLeft) }}</strong>.
            @else
                Your free trial has ended.
            @endif
        </span>
        <span style="font-size:12px;color:#DDD6FE;">Upgrade to keep full access to all features.</span>
    </div>
    <a href="#" style="background:#FFFFFF;color:#7C3AED;font-size:12px;font-weight:600;padding:6px 16px;border-radius:4px;text-decoration:none;white-space:nowrap;flex-shrink:0;">
        Upgrade Now →
    </a>
</div>
@endif