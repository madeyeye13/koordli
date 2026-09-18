<nav class="lp-nav" :class="{ 'menu-open': mobileMenuOpen }"
    x-data="{
        dark: localStorage.getItem('krd-dark') === 'true',
        mobileMenuOpen: false,
        toggleDark() {
            this.dark = !this.dark;
            localStorage.setItem('krd-dark', this.dark);
            document.documentElement.classList.toggle('dark', this.dark);
        },
        init() { document.documentElement.classList.toggle('dark', this.dark); }
    }">
    <div class="lp-nav-inner">
        @php
            $navFaviconPath = \App\Models\Central\PlatformSetting::get('site_favicon');
            $navFaviconVersion = $navFaviconPath ? \Illuminate\Support\Facades\Storage::disk('public')->lastModified($navFaviconPath) : null;
        @endphp
        <a href="{{ route('landing') }}" class="lp-logo">
            @if($navFaviconPath)
            <img class="lp-logo-mark" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($navFaviconPath) }}?v={{ $navFaviconVersion }}" alt="{{ $siteName ?? 'Koordli' }} logo">
            @else
            <span class="lp-logo-mark lp-logo-fallback"></span>
            @endif
            {{ $siteName ?? 'Koordli' }}
        </a>
        <div class="lp-nav-links">
            <a href="{{ route('landing') }}#features" class="lp-nav-link">Features</a>
            <a href="{{ route('landing') }}#pricing" class="lp-nav-link">Pricing</a>
            <a href="{{ route('blog.index') }}" class="lp-nav-link" wire:navigate style="{{ request()->routeIs('blog.*') ? 'color:var(--lp-text);font-weight:600;' : '' }}">Blog</a>
            <a href="{{ route('landing') }}#faq" class="lp-nav-link">FAQ</a>
        </div>
        <div class="lp-nav-cta">
            <button class="lp-theme-toggle" x-on:click="toggleDark()">
                <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                <svg x-show="dark" x-cloak xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            </button>
            <a href="{{ route('tenant.login') }}" class="lp-btn lp-btn-secondary lp-btn-sm-mobile" wire:navigate>Sign In</a>
            <a href="{{ route('register') }}" class="lp-btn lp-btn-primary lp-btn-sm-mobile" wire:navigate>Start Free Trial</a>
        </div>
        <button class="lp-menu-toggle" type="button" x-on:click="mobileMenuOpen = !mobileMenuOpen" :aria-expanded="mobileMenuOpen.toString()" aria-label="Toggle navigation menu">
            <svg x-show="!mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            <svg x-show="mobileMenuOpen" x-cloak xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
    </div>
    <div class="lp-mobile-menu" x-show="mobileMenuOpen" x-cloak x-transition.opacity>
        <a href="{{ route('landing') }}#features" class="lp-mobile-menu-link" x-on:click="mobileMenuOpen = false">Features</a>
        <a href="{{ route('landing') }}#pricing" class="lp-mobile-menu-link" x-on:click="mobileMenuOpen = false">Pricing</a>
        <a href="{{ route('blog.index') }}" class="lp-mobile-menu-link" x-on:click="mobileMenuOpen = false" wire:navigate>Blog</a>
        <a href="{{ route('landing') }}#faq" class="lp-mobile-menu-link" x-on:click="mobileMenuOpen = false">FAQ</a>
        <div class="lp-mobile-menu-actions">
            <button class="lp-theme-toggle" type="button" x-on:click="toggleDark()" :aria-label="dark ? 'Switch to light mode' : 'Switch to dark mode'">
                <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                <svg x-show="dark" x-cloak xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/></svg>
            </button>
            <a href="{{ route('tenant.login') }}" class="lp-btn lp-btn-secondary" wire:navigate x-on:click="mobileMenuOpen = false">Sign In</a>
            <a href="{{ route('register') }}" class="lp-btn lp-btn-primary" wire:navigate x-on:click="mobileMenuOpen = false">Start Trial</a>
        </div>
    </div>
</nav>