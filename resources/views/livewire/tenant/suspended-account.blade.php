<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:32px;background:#FAFAF9;">
    <div style="width:100%;max-width:520px;text-align:center;background:#fff;border:1px solid #E7E5E4;border-radius:8px;padding:44px 36px;">
        <div style="width:64px;height:64px;margin:0 auto 20px;border-radius:50%;background:#FEF3C7;color:#B45309;display:flex;align-items:center;justify-content:center;font-size:28px;">!</div>
        <div style="font-size:11px;color:#A8A29E;letter-spacing:.12em;text-transform:uppercase;margin-bottom:8px;">Account access</div>
        <h1 style="margin:0 0 12px;font-size:26px;color:#1C1917;">Your account has been suspended</h1>
        <p style="margin:0 auto 24px;max-width:410px;font-size:14px;line-height:1.7;color:#78716C;">
            Your workspace is temporarily unavailable. Your data is safe and has not been deleted.
        </p>
        <p style="margin:0 auto 28px;max-width:410px;font-size:13px;line-height:1.7;color:#57534E;">
            If you believe this is an error, please contact Koordli Support for reactivation.
        </p>
        <div style="display:flex;justify-content:center;gap:10px;flex-wrap:wrap;">
            <a href="mailto:{{ config('mail.from.address') }}" style="display:inline-block;background:#7C3AED;color:#fff;padding:12px 22px;border-radius:6px;text-decoration:none;font-size:13px;font-weight:600;">Contact Koordli Support</a>
            <a href="{{ route('tenant.logout') }}" style="display:inline-block;background:#fff;color:#57534E;padding:11px 22px;border:1px solid #D6D3D1;border-radius:6px;text-decoration:none;font-size:13px;font-weight:600;">Log out</a>
        </div>
    </div>
</div>
