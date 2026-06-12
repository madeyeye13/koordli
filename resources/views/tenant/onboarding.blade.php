@extends('layouts.tenant')

@section('content')

<div style="max-width: 560px; margin: 0 auto; padding: 48px 0;">

    <div style="margin-bottom: 32px;">
        <div style="font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #7C3AED; margin-bottom: 8px;">
            Getting Started
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: #1C1917; letter-spacing: -0.02em; margin-bottom: 8px;">
            Welcome to Koordli
        </h1>
        <p style="font-size: 14px; color: #78716C; line-height: 1.7;">
            Let's get your workspace set up. This only takes a minute.
        </p>
    </div>

    <div class="krd-card" style="padding: 32px; text-align: center;">
        <div style="font-size: 32px; margin-bottom: 16px;">🎉</div>
        <div style="font-size: 16px; font-weight: 600; color: #1C1917; margin-bottom: 8px;">
            Your account is ready
        </div>
        <p style="font-size: 13px; color: #78716C; margin-bottom: 24px; line-height: 1.7;">
            The full onboarding flow is coming in the next build.
        </p>
        <a href="{{ route('tenant.dashboard') }}" class="krd-btn krd-btn-primary">
            Go to Dashboard →
        </a>
    </div>

</div>

@endsection