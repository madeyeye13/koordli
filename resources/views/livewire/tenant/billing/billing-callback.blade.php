<div style="display:flex;align-items:center;justify-content:center;width:100%;min-height:{{ $isNewRegistration ? '100vh' : '60vh' }};box-sizing:border-box;">
    <div style="text-align:center;max-width:400px;padding:40px;">
        @if($status === 'processing')
        <div style="font-size:48px;margin-bottom:16px;">⏳</div>
        <h2 style="font-size:22px;font-weight:600;color:#1C1917;margin-bottom:8px;">Verifying Payment</h2>
        <p style="font-size:14px;color:#78716C;line-height:1.7;">Please wait while we confirm your payment...</p>

        @elseif($status === 'success')
        <div style="width:72px;height:72px;border-radius:50%;background:#D1FAE5;border:2px solid #86EFAC;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
        </div>
        <h2 style="font-size:22px;font-weight:600;color:#1C1917;margin-bottom:8px;">Payment Successful!</h2>
        <p style="font-size:14px;color:#78716C;line-height:1.7;margin-bottom:24px;">{{ $message }}</p>
        <a href="{{ $isNewRegistration ? route('register') : route('tenant.dashboard') }}" wire:navigate
            style="display:inline-block;background:#7C3AED;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;">
            {{ $isNewRegistration ? 'Continue setup' : 'Go to Dashboard' }} →
        </a>

        @else
        <div style="font-size:48px;margin-bottom:16px;">❌</div>
        <h2 style="font-size:22px;font-weight:600;color:#1C1917;margin-bottom:8px;">Payment Failed</h2>
        <p style="font-size:14px;color:#78716C;line-height:1.7;margin-bottom:24px;">{{ $message }}</p>
        <a href="{{ route('tenant.billing.upgrade') }}" wire:navigate
            style="display:inline-block;background:#1C1917;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;">
            Try Again
        </a>
        @endif
    </div>
</div>