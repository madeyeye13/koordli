<div style="display: flex; flex-direction: column; height: 100%;">

    {{-- Logo + Mobile Close Button --}}
    <div style="padding: 20px 16px; border-bottom: 1px solid #E7E5E4; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
        <x-ui.logo color="auto" tagline="Event Operations Simplified" />

        {{-- Close button — mobile only --}}
        <button
            class="krd-mobile-only"
            onclick="
                document.getElementById('krd-sidebar').classList.remove('open');
                document.getElementById('krd-overlay').classList.remove('active');
            "
            style="background: none; border: none; cursor: pointer; color: #78716C; padding: 4px; display: flex; align-items: center;"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>

    {{-- Navigation --}}
    <nav style="flex: 1; padding: 8px 0; overflow-y: auto;">

        <div class="krd-nav-section">
            <div class="krd-nav-label">Overview</div>
            <a href="{{ route('platform.dashboard') }}"
               class="krd-nav-item {{ request()->routeIs('platform.dashboard') ? 'active' : '' }}"
               wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                </svg>
                Dashboard
            </a>
        </div>

        <div class="krd-nav-section">
            <div class="krd-nav-label">Management</div>
            <a href="{{ route('platform.tenants') }}"
                class="krd-nav-item {{ request()->routeIs('platform.tenants*') ? 'active' : '' }}"
                wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                Companies
            </a>
            <a href="{{ route('platform.plans') }}"
                class="krd-nav-item {{ request()->routeIs('platform.plans*') ? 'active' : '' }}"
                wire:navigate>
                    <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                    Plans
            </a>
            <a href="#" class="krd-nav-item">
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
                </svg>
                Subscriptions
            </a>
        </div>

        <div class="krd-nav-section">
            <div class="krd-nav-label">System</div>
            <a href="#" class="krd-nav-item">
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M19.07 19.07l-1.41-1.41M4.93 19.07l1.41-1.41M12 2v2M12 20v2M2 12h2M20 12h2"/>
                </svg>
                Settings
            </a>
        </div>

    </nav>

    {{-- User --}}
    <div style="padding: 12px 16px; border-top: 1px solid #E7E5E4; flex-shrink: 0;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 32px; height: 32px; background: #EDE9FE; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; color: #7C3AED; flex-shrink: 0;">
                {{ strtoupper(substr(auth('platform')->user()?->name ?? 'A', 0, 1)) }}
            </div>
            <div style="flex: 1; min-width: 0; overflow: hidden;">
                <div style="font-size: 13px; font-weight: 500; color: #1C1917; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ auth('platform')->user()?->name }}
                </div>
                <div style="font-size: 11px; color: #A8A29E;">Platform Owner</div>
            </div>
            <form method="POST" action="{{ route('platform.logout') }}">
                @csrf
                <button type="submit" style="background: none; border: none; cursor: pointer; color: #A8A29E; padding: 4px; display: flex;" title="Sign out">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

</div>