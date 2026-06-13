<div style="display: flex; flex-direction: column; height: 100%;">

    <div style="padding: 20px 16px; border-bottom: 1px solid #E7E5E4; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
        <x-ui.logo color="auto" />
        <button class="krd-mobile-only" x-on:click="sidebarOpen = false"
            style="background:none;border:none;cursor:pointer;color:#78716C;padding:4px;display:flex;align-items:center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>

    <nav style="flex: 1; padding: 8px 0; overflow-y: auto;"
         x-on:click="if (window.innerWidth < 768) sidebarOpen = false">

        <div class="krd-nav-section">
            <div class="krd-nav-label">Portal</div>
            <a href="{{ route('vendor.dashboard') }}"
               class="krd-nav-item {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}"
               wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                </svg>
                My Events
            </a>
            <a href="{{ route('vendor.profile') }}"
               class="krd-nav-item {{ request()->routeIs('vendor.profile') ? 'active' : '' }}"
               wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                My Profile
            </a>
        </div>

    </nav>

    <div style="padding:12px 16px;border-top:1px solid #E7E5E4;flex-shrink:0;">
        @php $vendor = auth('vendor')->user(); @endphp
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:32px;height:32px;background:#EDE9FE;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:#7C3AED;flex-shrink:0;">
                {{ strtoupper(substr($vendor?->name ?? 'V', 0, 1)) }}
            </div>
            <div style="flex:1;min-width:0;overflow:hidden;">
                <div style="font-size:13px;font-weight:500;color:#1C1917;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $vendor?->name }}
                </div>
                <div style="font-size:11px;color:#A8A29E;">{{ $vendor?->business_name ?? 'Vendor Portal' }}</div>
            </div>
            <form method="POST" action="{{ route('vendor.logout') }}">
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