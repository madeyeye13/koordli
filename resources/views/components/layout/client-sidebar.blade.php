<div style="display: flex; flex-direction: column; height: 100%;">

    {{-- Logo + Close --}}
    <div style="padding: 20px 16px; border-bottom: 1px solid #E7E5E4; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
        <x-ui.portal-logo :tenant="auth('client')->user()?->tenant" color="auto" />
        <button
            class="krd-mobile-only"
            x-on:click="sidebarOpen = false"
            style="background:none;border:none;cursor:pointer;color:#78716C;padding:4px;display:flex;align-items:center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>

    {{-- Nav --}}
    <nav style="flex: 1; padding: 8px 0; overflow-y: auto;"
         x-on:click="if (window.innerWidth < 768) sidebarOpen = false">

        <div class="krd-nav-section">
            <div class="krd-nav-label">My Event</div>
            <a href="{{ route('client.dashboard') }}"
               class="krd-nav-item {{ request()->routeIs('client.dashboard') ? 'active' : '' }}"
               wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                </svg>
                Overview
            </a>
            <a href="{{ route('client.conversations') }}"
               class="krd-nav-item {{ request()->routeIs('client.conversations*') ? 'active' : '' }}"
               wire:navigate
               style="display:flex;align-items:center;">
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/>
                </svg>
                Messages
                <livewire:client.conversations.unread-badge lazy />
            </a>
        </div>

        <div class="krd-nav-section">
            <div class="krd-nav-label">Settings</div>
            <a href="{{ route('client.notifications.preferences') }}"
               class="krd-nav-item {{ request()->routeIs('client.notifications.preferences') ? 'active' : '' }}"
               wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/>
                </svg>
                Notifications
            </a>
        </div>

    </nav>

    {{-- Client info + logout --}}
    <div style="padding:12px 16px;border-top:1px solid #E7E5E4;flex-shrink:0;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:32px;height:32px;background:#EDE9FE;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:#7C3AED;flex-shrink:0;">
                {{ strtoupper(substr(auth('client')->user()?->name ?? 'C', 0, 1)) }}
            </div>
            <div style="flex:1;min-width:0;overflow:hidden;">
                <div style="font-size:13px;font-weight:500;color:#1C1917;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ auth('client')->user()?->name }}
                </div>
                <div style="font-size:11px;color:#A8A29E;">Client Portal</div>
            </div>
            <form method="POST" action="{{ route('client.logout') }}">
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