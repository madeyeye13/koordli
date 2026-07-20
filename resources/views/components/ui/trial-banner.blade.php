@php
    $tenant = auth()->user()?->tenant;
    $sub    = $tenantSubscription ?? null;

    // Fallback: use FeatureGateService if middleware hasn't shared subscription
    if (!$sub && $tenant) {
        $sub = \App\Models\Central\Subscription::where('tenant_id', $tenant->id)
            ->latest()->first();
    }

    $showBanner = false;
    $bannerType = 'trial'; // trial|grace|locked|expiring

    if (!$sub && $tenant && $tenant->status === 'trial') {
        // No subscription record — treat as locked expired trial
        $showBanner = true;
        $bannerType = 'locked';
    } elseif ($sub) {
        if ($sub->isLocked()) {
            $showBanner = true;
            $bannerType = 'locked';
        } elseif ($sub->isInGracePeriod()) {
            $showBanner = true;
            $bannerType = 'grace';
        } elseif ($sub->isTrialing()) {
            $showBanner = true;
            $bannerType = 'trial';
        } elseif ($sub->isActive() && $sub->daysUntilExpiry() <= 14) {
            $showBanner = true;
            $bannerType = 'expiring';
        }
    }
@endphp

@if($showBanner)
<div style="background:{{ $bannerType === 'locked' ? '#EF4444' : ($bannerType === 'grace' ? '#F59E0B' : 'linear-gradient(135deg,#7C3AED 0%,#6D28D9 100%)') }};padding:10px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
    <div style="display:flex;align-items:center;gap:10px;">
        @if($bannerType === 'locked')
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
        </svg>
        @else
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#DDD6FE" stroke-width="2">
            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
        </svg>
        @endif

        <span style="font-size:13px;color:#FFFFFF;font-weight:500;">
            @if($bannerType === 'locked')
                Your account is locked — subscription expired.
            @elseif($bannerType === 'grace')
                Your plan has expired. Grace period ends {{ $sub->grace_until?->diffForHumans() }}.
            @elseif($bannerType === 'trial')
                @if($sub->trialDaysRemaining() > 0)
                    Your free trial ends in <strong>{{ $sub->trialDaysRemaining() }} {{ Str::plural('day', $sub->trialDaysRemaining()) }}</strong>.
                @else
                    Your free trial has ended.
                @endif
            @elseif($bannerType === 'expiring')
                Your plan expires in <strong>{{ $sub->daysUntilExpiry() }} {{ Str::plural('day', $sub->daysUntilExpiry()) }}</strong>.
            @endif
        </span>
        <span style="font-size:12px;color:#DDD6FE;">
            {{ $bannerType === 'locked' ? 'Renew to restore access to your data.' : 'Upgrade to keep full access to all features.' }}
        </span>
    </div>
    <a href="{{ route('tenant.billing.upgrade') }}" wire:navigate
        style="background:#FFFFFF;color:{{ $bannerType === 'locked' ? '#EF4444' : '#7C3AED' }};font-size:12px;font-weight:600;padding:6px 16px;border-radius:4px;text-decoration:none;white-space:nowrap;flex-shrink:0;">
        {{ $bannerType === 'locked' ? 'Renew Now' : 'Upgrade Now →' }}
    </a>
</div>
@endif