<div style="display: flex; flex-direction: column; height: 100%;">

    {{-- Logo + Mobile Close --}}
    <div style="padding: 20px 16px; border-bottom: 1px solid #E7E5E4; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
        <x-ui.logo color="auto" />

        {{-- Close button — mobile only --}}
        <button
            class="krd-mobile-only"
            x-on:click="sidebarOpen = false"
            style="background:none;border:none;cursor:pointer;color:#78716C;padding:4px;display:flex;align-items:center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>

    {{-- Navigation --}}
    <nav style="flex: 1; padding: 8px 0; overflow-y: auto;"
     x-on:click="if (window.innerWidth < 768) sidebarOpen = false">

        <div class="krd-nav-section">
            <div class="krd-nav-label">Overview</div>
            <a href="{{ route('tenant.dashboard') }}"
               class="krd-nav-item {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}"
               wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                </svg>
                Dashboard
            </a>
        </div>

        <div class="krd-nav-section">
            <div class="krd-nav-label">Operations</div>
            <a href="{{ route('tenant.events') }}"
                class="krd-nav-item {{ request()->routeIs('tenant.events*') ? 'active' : '' }}"
                wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
                Events
            </a>
            <a href="{{ route('tenant.tasks') }}"
                class="krd-nav-item {{ request()->routeIs('tenant.tasks*') ? 'active' : '' }}"
                wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
                </svg>
                Tasks
            </a>
            <a href="{{ route('tenant.vendors') }}"
                class="krd-nav-item {{ request()->routeIs('tenant.vendors*') && !request()->routeIs('tenant.vendor.applications') ? 'active' : '' }}"
                wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
                </svg>
                Vendors
            </a>
            <a href="{{ route('tenant.vendor.applications') }}"
                class="krd-nav-item {{ request()->routeIs('tenant.vendor.applications') ? 'active' : '' }}"
                wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                </svg>
                Applications
                @php $pendingCount = \App\Models\Tenant\VendorApplication::where('status','pending')->count(); @endphp
                @if($pendingCount > 0)
                <span style="margin-left:auto;font-size:10px;background:#EF4444;color:#fff;padding:1px 6px;border-radius:10px;font-weight:600;">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('tenant.budget') }}"
                class="krd-nav-item {{ request()->routeIs('tenant.budget*') ? 'active' : '' }}"
                wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                </svg>
                Budget
            </a>
        </div>

        <div class="krd-nav-section">
            <div class="krd-nav-label">Experience</div>
            <a href="#" class="krd-nav-item">
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                Clients
            </a>
            <a href="{{ route('tenant.events') }}"
                class="krd-nav-item {{ request()->routeIs('tenant.events.guests') ? 'active' : '' }}"
                wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
                </svg>
                Guests & RSVP
            </a>
            <a href="{{ route('tenant.events') }}"
                class="krd-nav-item {{ request()->routeIs('tenant.events.runsheet') ? 'active' : '' }}"
                wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/>
                </svg>
                Runsheet
            </a>
        </div>

        <div class="krd-nav-section">
            <div class="krd-nav-label">Business</div>
            <a href="{{ route('tenant.forms') }}"
                class="krd-nav-item {{ request()->routeIs('tenant.forms*') ? 'active' : '' }}"
                wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                </svg>
                Forms & Bookings
            </a>

            <a href="{{ route('tenant.staff') }}"
                class="krd-nav-item {{ request()->routeIs('tenant.staff*') ? 'active' : '' }}"
                wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                </svg>
                Staff
            </a>
            <a href="#" class="krd-nav-item">
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M19.07 19.07l-1.41-1.41M4.93 19.07l1.41-1.41M12 2v2M12 20v2M2 12h2M20 12h2"/>
                </svg>
                Settings
            </a>
        </div>

    </nav>

    {{-- User + Plan --}}
    <div style="padding:12px 16px;border-top:1px solid #E7E5E4;flex-shrink:0;">

        {{-- Plan Badge --}}
        @php $planName = auth()->user()?->tenant?->plan?->name ?? null; @endphp
        @if($planName)
        <div style="margin-bottom:10px;">
            <span style="
                display:inline-flex;align-items:center;gap:5px;
                background:#EDE9FE;color:#7C3AED;
                font-size:10px;font-weight:600;
                padding:3px 10px;border-radius:20px;
                letter-spacing:0.04em;
            ">
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                </svg>
                {{ $planName }} Plan
            </span>
        </div>
        @endif

        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:32px;height:32px;background:#EDE9FE;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:#7C3AED;flex-shrink:0;">
                {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
            </div>
            <div style="flex:1;min-width:0;overflow:hidden;">
                <div style="font-size:13px;font-weight:500;color:#1C1917;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ auth()->user()?->name }}
                </div>
                <div style="font-size:11px;color:#A8A29E;">
                    {{ auth()->user()?->tenant?->name }}
                </div>
            </div>
            <form method="POST" action="{{ route('tenant.logout') }}">
                @csrf
                <button type="submit" style="background:none;border:none;cursor:pointer;color:#A8A29E;padding:4px;display:flex;" title="Sign out">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

</div>