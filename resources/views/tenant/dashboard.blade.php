@extends('layouts.tenant')

@section('content')

<div style="margin-bottom: 24px;">
    <h1 style="font-size: 22px; font-weight: 600; color: #1C1917; letter-spacing: -0.01em;">
        Dashboard
    </h1>
    <p style="font-size: 13px; color: #78716C; margin-top: 4px;">
        Welcome back. Here's what's happening.
    </p>
</div>

<div class="krd-grid-4" style="margin-bottom: 24px;">
    @foreach(['Events', 'Tasks', 'Guests', 'Vendors'] as $label)
    <div class="krd-card">
        <div style="font-size: 11px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; color: #78716C; margin-bottom: 8px;">
            {{ $label }}
        </div>
        <div class="krd-stat-number" style="font-size: 28px; font-weight: 700; letter-spacing: -0.02em;">
            0
        </div>
    </div>
    @endforeach
</div>

<div class="krd-card">
    <div class="krd-empty-state">
        <div class="krd-empty-state-icon">📋</div>
        <div class="krd-empty-state-title">No events yet</div>
        <div class="krd-empty-state-desc">Create your first event to get started.</div>
    </div>
</div>

@endsection