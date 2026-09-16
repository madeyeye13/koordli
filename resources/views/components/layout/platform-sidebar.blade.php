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

        <div class="platform-status-mobile" style="padding: 12px 16px 4px;">
            <livewire:platform.support.agent-status />
        </div>

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
            <a href="{{ route('platform.billing') }}"
                class="krd-nav-item {{ request()->routeIs('platform.billing') ? 'active' : '' }}"
                wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>
                </svg>
                Billing Config
            </a>
            <a href="{{ route('platform.blog.index') }}"
                class="krd-nav-item {{ request()->routeIs('platform.blog.*') ? 'active' : '' }}"
                wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>
                </svg>
                Blog
            </a>

            <a href="{{ route('platform.blog.comments') }}" class="krd-nav-item {{ request()->routeIs('platform.blog.comments') ? 'active' : '' }}" wire:navigate style="padding-left:32px;font-size:12.5px;">
                Comment Moderation
            </a>
            <a href="{{ route('platform.staff') }}"
                class="krd-nav-item {{ request()->routeIs('platform.staff*') ? 'active' : '' }}"
                wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
                </svg>
                Staff & Roles
            </a>
        </div>

        <div class="krd-nav-section">
            <div class="krd-nav-label">Support</div>
            <a href="{{ route('platform.support.tickets') }}"
                class="krd-nav-item {{ request()->routeIs('platform.support.tickets*') ? 'active' : '' }}"
                wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                </svg>
                Support Tickets
            </a>
            <a href="{{ route('platform.support.faqs') }}"
                class="krd-nav-item {{ request()->routeIs('platform.support.faqs') ? 'active' : '' }}"
                wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                FAQ Knowledge Base
            </a>
        </div>

        <div class="krd-nav-section">
            <div class="krd-nav-label">System</div>
            <a href="{{ route('platform.site-settings') }}"
                class="krd-nav-item {{ request()->routeIs('platform.site-settings') ? 'active' : '' }}"
                wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/>
                    <path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>
                </svg>
                Site Settings
            </a>

            <a href="{{ route('platform.site-settings') }}#legal-pages"
                class="krd-nav-item {{ request()->routeIs('platform.site-settings') ? 'active' : '' }}"
                wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M6 3h12v18H6z"/><path d="M9 7h6M9 11h6M9 15h4"/>
                </svg>
                Legal Pages
            </a>

            <a href="{{ route('platform.profile') }}"
                class="krd-nav-item {{ request()->routeIs('platform.profile') ? 'active' : '' }}"
                wire:navigate>
                <svg class="krd-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                My Account
            </a>
        </div>

    </nav>

    {{-- User --}}
    <div style="padding: 12px 16px; border-top: 1px solid #E7E5E4; flex-shrink: 0;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('platform.profile') }}" wire:navigate style="width: 32px; height: 32px; background: #EDE9FE; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; color: #7C3AED; flex-shrink: 0; text-decoration: none;" title="My Account">
                {{ strtoupper(substr(auth('platform')->user()?->name ?? 'A', 0, 1)) }}
            </a>
            <a href="{{ route('platform.profile') }}" wire:navigate style="flex: 1; min-width: 0; overflow: hidden; text-decoration: none;">
                <div style="font-size: 13px; font-weight: 500; color: #1C1917; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ auth('platform')->user()?->name }}
                </div>
                <div style="font-size: 11px; color: #A8A29E;">{{ str_replace('platform_', '', auth('platform')->user()?->roles->first()?->name ?? 'Staff') }}</div>
            </a>
            <form id="platform-logout-form" method="POST" action="{{ route('platform.logout') }}" x-data="{ confirmOpen: false }" x-on:submit.prevent="confirmOpen = true">
                @csrf
                <button type="submit" style="background: none; border: none; cursor: pointer; color: #A8A29E; padding: 4px; display: flex;" title="Sign out" aria-label="Sign out">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
                    </svg>
                </button>

                <template x-teleport="body">
                    <div x-show="confirmOpen" x-cloak
                        style="position:fixed;left:0;top:0;width:100vw;height:100vh;z-index:100;padding:20px;"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0">
                        <div style="position:absolute;inset:0;background:rgba(28,25,23,0.42);" x-on:click="confirmOpen = false"></div>
                        <div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:100%;max-width:360px;background:#fff;border:1px solid #E7E5E4;border-radius:10px;padding:24px;box-shadow:0 20px 50px rgba(28,25,23,0.2);"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95">
                            <div style="width:38px;height:38px;border-radius:50%;background:#FEE2E2;color:#DC2626;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                            </div>
                            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:6px;">Sign out of Koordli?</h3>
                            <p style="font-size:13px;line-height:1.6;color:#78716C;margin-bottom:20px;">You will need to sign in again to access this workspace.</p>
                            <div style="display:flex;gap:10px;justify-content:flex-end;">
                                <button type="button" class="krd-btn krd-btn-secondary" x-on:click="confirmOpen = false">Cancel</button>
                                <button type="button" class="krd-btn" style="background:#DC2626;color:#fff;border-color:#DC2626;"
                                    x-on:click="document.getElementById('platform-logout-form').classList.add('platform-logout-fading'); setTimeout(() => document.getElementById('platform-logout-form').submit(), 180)">
                                    Sign out
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </form>
        </div>
    </div>

</div>

<style>
#platform-logout-form.platform-logout-fading { opacity:0; transition:opacity 180ms ease; }
</style>