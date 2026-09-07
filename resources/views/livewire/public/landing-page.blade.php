<div>
@php
    $landingFaviconPath = \App\Models\Central\PlatformSetting::get('site_favicon');
    $landingFaviconVersion = $landingFaviconPath ? \Illuminate\Support\Facades\Storage::disk('public')->lastModified($landingFaviconPath) : null;
@endphp
<style>
    :root {
        --lp-bg: #FAFAF9;
        --lp-bg-alt: #F5F5F4;
        --lp-text: #1C1917;
        --lp-text-muted: #57534E;
        --lp-text-faint: #78716C;
        --lp-border: #E7E5E4;
        --lp-card-bg: #ffffff;
        --lp-accent: #7C3AED;
        --lp-accent-bg: #F5F3FF;
        --lp-accent-border: #DDD6FE;
    }
    .dark {
        --lp-bg: #0C0A09;
        --lp-bg-alt: #1C1917;
        --lp-text: #FAFAF9;
        --lp-text-muted: #D6D3D1;
        --lp-text-faint: #A8A29E;
        --lp-border: #292524;
        --lp-card-bg: #1C1917;
        --lp-accent: #A78BFA;
        --lp-accent-bg: #2E1065;
        --lp-accent-border: #4C1D95;
    }

    .lp-wrap { font-family: 'Satoshi', sans-serif; color: var(--lp-text); overflow-x: hidden; background: var(--lp-bg); transition: background 200ms, color 200ms; padding-top: 68px; }
    .lp-container { max-width: 1180px; margin: 0 auto; padding: 0 24px; }
    .lp-serif { font-family: 'Fraunces', serif; font-optical-sizing: auto; }

    /* Nav */
    .lp-nav { position: fixed; top: 0; left: 0; right: 0; z-index: 50; background: color-mix(in srgb, var(--lp-bg) 88%, transparent); backdrop-filter: blur(14px); border-bottom: 1px solid var(--lp-border); }
    .lp-nav-inner { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; max-width: 1180px; margin: 0 auto; gap: 12px; }
    .lp-logo { display: inline-flex; align-items: center; gap: 9px; color: var(--lp-text); text-decoration: none; font-family: 'Fraunces', serif; font-size: 20px; font-weight: 600; letter-spacing: -0.02em; }
    .lp-logo-mark { width: 28px; height: 28px; object-fit: contain; border-radius: 7px; flex-shrink: 0; }
    .lp-logo-fallback { display: block; background: var(--lp-accent); transform: rotate(24deg); }
    .lp-nav-links { display: flex; gap: 28px; align-items: center; }
    .lp-nav-link { font-size: 14px; color: var(--lp-text-muted); text-decoration: none; font-weight: 500; }
    .lp-nav-link:hover { color: var(--lp-text); }
    .lp-nav-cta { display: flex; gap: 10px; align-items: center; flex-shrink: 0; }
    .lp-nav-cta .lp-btn-primary { background: #7C3AED; color: #fff; border-color: #7C3AED; }
    .lp-nav-cta .lp-btn-primary:hover { background: #6D28D9; border-color: #6D28D9; opacity: 1; }
    .lp-theme-toggle { background: none; border: 1.5px solid var(--lp-border); border-radius: 8px; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--lp-text-muted); flex-shrink: 0; }
    .lp-menu-toggle { display: none; background: none; border: 1.5px solid var(--lp-border); border-radius: 8px; width: 40px; height: 40px; align-items: center; justify-content: center; cursor: pointer; color: var(--lp-text); }
    .lp-mobile-menu { display: none; }

    /* Buttons */
    .lp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 12px 22px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; border: none; transition: all 150ms; white-space: nowrap; }
    .lp-btn-primary { background: var(--lp-text); color: var(--lp-bg); }
    .lp-btn-primary:hover { opacity: 0.85; }
    .lp-btn-secondary { background: transparent; color: var(--lp-text); border: 1.5px solid var(--lp-border); }
    .lp-btn-secondary:hover { border-color: var(--lp-text); }
    .lp-btn-lg { padding: 15px 30px; font-size: 15px; }
    .lp-btn-sm-mobile { padding: 9px 14px; font-size: 12px; }

    /* Hero */
    /* Full-bleed violet gradient hero — deep violet through violet-blue,
       always this palette regardless of the site's own light/dark toggle,
       since a gradient hero reads as a deliberate design choice, not a
       theme-dependent one. */
    .lp-hero-outer {
        width: 100%;
        background: linear-gradient(160deg, #4C1D95 0%, #6D28D9 35%, #7C3AED 60%, #4338CA 100%);
        overflow: hidden;
    }
    .lp-hero { padding: 80px 20px 56px; text-align: center; position: relative; z-index: 1; }
    .lp-hero-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.22); color: #fff; font-size: 12px; font-weight: 600; padding: 6px 14px; border-radius: 20px; margin-bottom: 24px; backdrop-filter: blur(6px); white-space: nowrap; max-width: 100%; }
    .lp-hero-title { font-size: clamp(30px, 7vw, 62px); font-weight: 700; letter-spacing: -0.03em; line-height: 1.08; max-width: 900px; margin: 0 auto 20px; color: #fff; }
    .lp-hero-title .accent { color: #FDE68A; }
    .lp-hero-sub { font-size: clamp(15px, 2.5vw, 18px); color: rgba(255,255,255,0.82); max-width: 600px; margin: 0 auto 32px; line-height: 1.6; padding: 0 8px; }
    .lp-hero-ctas { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-bottom: 48px; }
    .lp-hero-ctas .lp-btn-primary { background: #fff; color: #7C3AED; transition: background 150ms ease, color 150ms ease; }
    .lp-hero-ctas .lp-btn-primary:hover { background: #FDE68A; color: #4C1D95; opacity: 1; }
    .lp-hero-ctas .lp-btn-secondary { background: rgba(255,255,255,0.08); color: #fff; border: 1.5px solid rgba(255,255,255,0.35); backdrop-filter: blur(6px); }
    .lp-hero-ctas .lp-btn-secondary:hover { background: rgba(255,255,255,0.16); border-color: rgba(255,255,255,0.5); }
    .lp-hero-rotate { display: inline-block; opacity: 0; transform: translateY(6px); transition: opacity 0.4s ease, transform 0.4s ease; }
    .lp-hero-rotate-visible { opacity: 1; transform: translateY(0); }
    .lp-mockup-steps { display: flex; justify-content: center; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 20px; padding: 0 12px; }
    .lp-mockup-step { font-size: 11px; font-weight: 600; color: #78716C; padding: 5px 12px; border-radius: 16px; background: #F5F5F4; white-space: nowrap; }
    .lp-mockup-step.active { color: var(--lp-accent); background: var(--lp-accent-bg); }
    .lp-mockup-step-arrow { color: #A8A29E; font-size: 11px; }

    /* Dashboard mockup */
    .lp-mockup-frame { max-width: 1000px; margin: 0 auto; border-radius: 14px 14px 0 0; overflow: hidden; border: 1px solid var(--lp-border); box-shadow: 0 30px 80px -20px rgba(28,25,23,0.25); }
    .lp-mockup-bar { background: var(--lp-card-bg); padding: 10px 14px; display: flex; align-items: center; gap: 6px; border-bottom: 1px solid var(--lp-border); }
    .lp-mockup-dot { width: 9px; height: 9px; border-radius: 50%; }
    .lp-mockup-mobile-cols { display: grid; grid-template-columns: 160px 1fr; gap: 0; background: var(--lp-card-bg); border-radius: 8px; overflow: hidden; border: 1px solid var(--lp-border); min-height: 340px; }

    /* Trusted by */
    .lp-trusted { padding: 44px 0; text-align: center; }
    .lp-trusted-label { font-size: 11px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--lp-text-faint); margin-bottom: 22px; }

    /* Seamless infinite marquee — the track holds the logo list TWICE
       back-to-back; animating it exactly -50% loops perfectly with no
       visible jump, since the second copy lines up exactly where the
       first one started. */
    .lp-trusted-marquee { overflow: hidden; mask-image: linear-gradient(to right, transparent, black 64px, black calc(100% - 64px), transparent); -webkit-mask-image: linear-gradient(to right, transparent, black 64px, black calc(100% - 64px), transparent); }
    .lp-trusted-track { display: flex; width: max-content; animation: lp-marquee 28s linear infinite; }
    .lp-trusted-track:hover { animation-play-state: paused; }
    .lp-trusted-group { display: flex; align-items: center; gap: 40px; padding-right: 40px; flex-shrink: 0; }
    .lp-trusted-logo { font-size: 16px; font-weight: 700; color: var(--lp-text-faint); letter-spacing: -0.02em; opacity: 0.55; white-space: nowrap; }
    .lp-trusted-dot { width: 4px; height: 4px; border-radius: 50%; background: var(--lp-border); flex-shrink: 0; }

    @keyframes lp-marquee {
        from { transform: translateX(0); }
        to   { transform: translateX(-50%); }
    }

    /* Section headers */
    .lp-section { padding: 72px 20px; }
    .lp-section-alt { background: var(--lp-bg-alt); }
    .lp-section-label { font-size: 12px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--lp-accent); margin-bottom: 12px; text-align: center; }
    .lp-section-title { font-size: clamp(24px, 5vw, 42px); font-weight: 700; letter-spacing: -0.02em; text-align: center; max-width: 720px; margin: 0 auto 16px; line-height: 1.18; }
    .lp-section-sub { font-size: 15px; color: var(--lp-text-muted); text-align: center; max-width: 540px; margin: 0 auto 48px; line-height: 1.6; }

    /* Problem grid */
    .lp-problem-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 14px; max-width: 980px; margin: 0 auto; }
    .lp-problem-card { background: var(--lp-card-bg); border: 1px solid var(--lp-border); border-radius: 12px; padding: 22px; }
    .lp-problem-icon { font-size: 22px; margin-bottom: 12px; }
    .lp-problem-title { font-size: 14px; font-weight: 600; margin-bottom: 6px; }
    .lp-problem-text { font-size: 12.5px; color: var(--lp-text-faint); line-height: 1.6; }

    /* Feature blocks */
    .lp-feature-block { display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: center; margin-bottom: 88px; }
    .lp-feature-block:last-child { margin-bottom: 0; }
    .lp-feature-block.reverse .lp-feature-text { order: 2; }
    .lp-feature-block.reverse .lp-feature-visual { order: 1; }
    .lp-feature-tag { display: inline-block; font-size: 11.5px; font-weight: 600; color: var(--lp-accent); background: var(--lp-accent-bg); padding: 4px 12px; border-radius: 6px; margin-bottom: 14px; }
    .lp-feature-title { font-size: clamp(20px, 3vw, 26px); font-weight: 700; letter-spacing: -0.02em; margin-bottom: 12px; line-height: 1.25; }
    .lp-feature-text-desc { font-size: 14px; color: var(--lp-text-muted); line-height: 1.7; margin-bottom: 18px; }
    .lp-feature-list { list-style: none; display: flex; flex-direction: column; gap: 9px; }
    .lp-feature-list li { font-size: 13.5px; color: var(--lp-text); display: flex; align-items: flex-start; gap: 9px; }
    .lp-feature-list li::before { content: '✓'; color: #10B981; font-weight: 700; flex-shrink: 0; }

    /* Mockup mini */
    .lp-mockup-mini { background: var(--lp-card-bg); border-radius: 12px; border: 1px solid var(--lp-border); overflow: hidden; box-shadow: 0 20px 50px -15px rgba(28,25,23,0.15); }
    .lp-mockup-mini-header { background: #1C1917; padding: 13px 16px; display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; }
    .lp-mockup-mini-title { color: #fff; font-size: 11.5px; font-weight: 600; }
    .lp-mockup-mini-body { padding: 18px; }

    /* Why Koordli */
    .lp-why-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 20px; max-width: 1000px; margin: 0 auto; }
    .lp-why-item { text-align: center; }
    .lp-why-icon { width: 44px; height: 44px; border-radius: 12px; background: var(--lp-accent-bg); color: var(--lp-accent); display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 18px; }
    .lp-why-title { font-size: 13.5px; font-weight: 600; margin-bottom: 5px; }
    .lp-why-text { font-size: 11.5px; color: var(--lp-text-faint); line-height: 1.6; }

    /* How it works - horizontal scroll on mobile */
    .lp-flow { display: flex; align-items: center; justify-content: center; gap: 8px; max-width: 1000px; margin: 0 auto; flex-wrap: wrap; padding: 4px; }
.lp-flow::-webkit-scrollbar { display: none; }
    .lp-flow-step { background: var(--lp-card-bg); border: 1.5px solid var(--lp-border); border-radius: 10px; padding: 12px 18px; font-size: 12.5px; font-weight: 600; color: var(--lp-text); flex-shrink: 0; white-space: nowrap; }
    .lp-flow-arrow { color: var(--lp-border); font-size: 15px; flex-shrink: 0; }

    /* Pricing */
    .lp-pricing-toggle { display: flex; justify-content: center; margin-bottom: 40px; }
    .lp-pricing-toggle-inner { display: flex; background: var(--lp-bg-alt); border-radius: 10px; padding: 4px; gap: 4px; }
    .lp-pricing-toggle-btn { padding: 9px 20px; border-radius: 7px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit; background: transparent; color: var(--lp-text-faint); transition: background 100ms, color 100ms; }
    .lp-pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; max-width: 1000px; margin: 0 auto; }
    .lp-pricing-card { background: var(--lp-card-bg); border: 1.5px solid var(--lp-border); border-radius: 16px; padding: 28px; position: relative; }
    .lp-pricing-card.featured { border-color: var(--lp-accent); border-width: 2px; }
    .lp-pricing-badge { position: absolute; top: -13px; left: 24px; background: var(--lp-accent); color: #fff; font-size: 10.5px; font-weight: 700; letter-spacing: 0.05em; padding: 4px 14px; border-radius: 20px; }
    .lp-pricing-name { font-size: 16px; font-weight: 700; margin-bottom: 6px; }
    .lp-pricing-price { font-size: 32px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 4px; }
    .lp-pricing-period { font-size: 12.5px; color: var(--lp-text-faint); margin-bottom: 22px; }
    .lp-pricing-features { list-style: none; display: flex; flex-direction: column; gap: 9px; margin-bottom: 26px; }
    .lp-pricing-features li { font-size: 12.5px; color: var(--lp-text-muted); display: flex; align-items: center; gap: 8px; }
    .lp-pricing-features li::before { content: '✓'; color: #10B981; font-weight: 700; }

    /* FAQ */
    .lp-faq { max-width: 720px; margin: 0 auto; }
    .lp-faq-item { border-bottom: 1px solid var(--lp-border); }
    .lp-faq-q { padding: 18px 0; display: flex; align-items: center; justify-content: space-between; cursor: pointer; font-size: 14px; font-weight: 600; gap: 12px; }
    .lp-faq-a { padding: 0 0 18px; font-size: 13.5px; color: var(--lp-text-muted); line-height: 1.7; }

    /* Final CTA — FULL BLEED */
    .lp-final-cta-outer { background: #1C1917; width: 100%; padding: 90px 20px; }
    .lp-final-cta-inner { max-width: 700px; margin: 0 auto; text-align: center; }
    .lp-final-cta-title { font-size: clamp(24px, 5vw, 40px); font-weight: 700; color: #fff; letter-spacing: -0.02em; margin-bottom: 14px; line-height: 1.2; }
    .lp-final-cta-sub { font-size: 15px; color: rgba(255,255,255,0.6); margin-bottom: 30px; }

    /* Footer */
    .lp-footer { padding: 56px 20px 28px; }
    .lp-footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 28px; max-width: 1180px; margin: 0 auto 40px; }
    .lp-footer-col-title { font-size: 11.5px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--lp-text-faint); margin-bottom: 14px; }
    .lp-footer-link { display: block; font-size: 13px; color: var(--lp-text-muted); text-decoration: none; margin-bottom: 9px; }
    .lp-footer-link:hover { color: var(--lp-text); }
    .lp-footer-bottom { max-width: 1180px; margin: 0 auto; padding-top: 28px; border-top: 1px solid var(--lp-border); display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px; font-size: 11.5px; color: var(--lp-text-faint); }

    /* Scroll animations */
    .lp-animate { opacity: 0; transform: translateY(24px); transition: opacity 700ms cubic-bezier(0.16,1,0.3,1), transform 700ms cubic-bezier(0.16,1,0.3,1); }
    .lp-animate.lp-visible { opacity: 1; transform: translateY(0); }
    .lp-animate-left { opacity: 0; transform: translateX(-28px); transition: opacity 700ms cubic-bezier(0.16,1,0.3,1), transform 700ms cubic-bezier(0.16,1,0.3,1); }
    .lp-animate-left.lp-visible { opacity: 1; transform: translateX(0); }
    .lp-animate-right { opacity: 0; transform: translateX(28px); transition: opacity 700ms cubic-bezier(0.16,1,0.3,1), transform 700ms cubic-bezier(0.16,1,0.3,1); }
    .lp-animate-right.lp-visible { opacity: 1; transform: translateX(0); }

    /* Mobile responsiveness */
    @media (max-width: 900px) {
        .lp-feature-block { grid-template-columns: 1fr; gap: 28px; margin-bottom: 64px; }
        .lp-feature-block.reverse .lp-feature-text { order: 1; }
        .lp-feature-block.reverse .lp-feature-visual { order: 2; }
        .lp-footer-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 767px) {
        .lp-nav-inner { padding: 12px 16px; }
        .lp-nav-links, .lp-nav-cta { display: none; }
        .lp-menu-toggle { display: flex; }
        .lp-nav.menu-open .lp-mobile-menu { display: flex; }
        .lp-mobile-menu { flex-direction: column; gap: 4px; padding: 10px 16px 16px; border-top: 1px solid var(--lp-border); background: var(--lp-bg); }
        .lp-mobile-menu-link { display: block; padding: 11px 4px; color: var(--lp-text-muted); text-decoration: none; font-size: 14px; font-weight: 500; }
        .lp-mobile-menu-link:hover { color: var(--lp-text); }
        .lp-mobile-menu-actions { display: flex; align-items: center; gap: 10px; margin-top: 6px; padding-top: 12px; border-top: 1px solid var(--lp-border); }
        .lp-mobile-menu-actions .lp-theme-toggle { display: flex; }
        .lp-mobile-menu-actions .lp-btn { flex: 1; }
        .lp-hero { padding-top: 108px; }
        .lp-nav-links { display: none; }
        .lp-mockup-mobile-cols { grid-template-columns: 1fr; }
        .lp-mockup-mobile-cols > div:first-child { display: none; }
        .lp-hero { padding: 56px 16px 44px; }
    }
    @media (max-width: 400px) {
        .lp-hero-badge { font-size: 10.5px; padding: 6px 10px; gap: 5px; }

        /* CTA buttons — never wrap their text, tighten padding slightly
           so "Start Free Trial →" and "See How It Works" both sit
           comfortably on one line each when stacked. */
        .lp-hero-ctas .lp-btn { white-space: nowrap; padding: 13px 20px; font-size: 13.5px; }

        /* Dashboard mockup — tighter padding/type throughout so the
           3-column stat cards and list rows don't feel squeezed at
           this width. Nothing structural changes, just scale. */
        .lp-mockup-frame [style*="padding:18px"] { padding: 13px !important; }
        .lp-mockup-frame [style*="grid-template-columns:repeat(3,1fr)"] { gap: 6px !important; }
        .lp-mockup-frame [style*="font-size:18px"] { font-size: 15px !important; }
        .lp-mockup-frame [style*="font-size:14px"] { font-size: 12.5px !important; }
        .lp-mockup-frame [style*="font-size:11px"] { font-size: 10px !important; }
        .lp-mockup-frame [style*="font-size:11.5px"] { font-size: 10.5px !important; }
        .lp-mockup-frame [style*="font-size:10.5px"] { font-size: 9.5px !important; }
        .lp-mockup-frame [style*="font-size:9px"] { font-size: 8.5px !important; }
        .lp-section { padding: 56px 16px; }
        .lp-final-cta-outer { padding: 64px 16px; }
        .lp-footer-grid { grid-template-columns: 1fr; text-align: left; }
        .lp-nav-cta .lp-btn-secondary { display: none; }
        .lp-hero-ctas .lp-btn { width: calc(100% - 20px); margin: 0 10px; }
        .lp-hero-ctas { flex-direction: column; }
        .lp-trusted-logos { gap: 20px; }
        .lp-trusted-logo { font-size: 13px; }
    }
    @media (max-width: 420px) {
        .lp-pricing-grid { grid-template-columns: 1fr; }
        .lp-problem-grid { grid-template-columns: 1fr; }
    }

    [x-cloak] { display: none !important; }
</style>

<div class="lp-wrap"
    x-data="{
        dark: localStorage.getItem('krd-dark') === 'true',
        mobileMenuOpen: false,
        cycle: '{{ $billingCycle }}',
        toggleDark() {
            this.dark = !this.dark;
            localStorage.setItem('krd-dark', this.dark);
            document.documentElement.classList.toggle('dark', this.dark);
        },
        init() {
            document.documentElement.classList.toggle('dark', this.dark);
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('lp-visible');
                    }
                });
            }, { threshold: 0.15 });
            this.$nextTick(() => {
                document.querySelectorAll('.lp-animate, .lp-animate-left, .lp-animate-right').forEach(el => observer.observe(el));
            });
        }
    }"
>

    {{-- Nav — single source of truth: resources/views/partials/public-nav.blade.php --}}
    @include('partials.public-nav')

    {{-- Hero --}}
    <div class="lp-hero-outer">
    <section class="lp-hero">
        <div class="lp-hero-badge lp-animate">⚡ Now supporting Bookings, Contracts & E-Signatures</div>
        <h1 class="lp-hero-title lp-serif lp-animate"
            x-data="{
                words: ['Event Businesses', 'Wedding Planners', 'Corporate Planners', 'Conference Organizers', 'Churches & Ministries'],
                index: 0,
                visible: true,
                init() {
                    setInterval(() => {
                        this.visible = false;
                        setTimeout(() => {
                            this.index = (this.index + 1) % this.words.length;
                            this.visible = true;
                        }, 400);
                    }, 2800);
                }
            }">
            The Operating System For
            <span class="accent lp-hero-rotate" :class="visible && 'lp-hero-rotate-visible'" x-text="words[index]"></span>
        </h1>
        <p class="lp-hero-sub lp-animate">
            Koordli brings your clients, vendors, contracts, tasks, and event-day execution into one platform — replacing scattered WhatsApp threads, spreadsheets, and paper runsheets.
        </p>
        <div class="lp-hero-ctas lp-animate">
            <a href="{{ route('register') }}" class="lp-btn lp-btn-primary lp-btn-lg" wire:navigate>Start Free Trial →</a>
            <a href="#features" class="lp-btn lp-btn-secondary lp-btn-lg">See How It Works</a>
        </div>

        <div class="lp-mockup-frame lp-animate">
            <div class="lp-mockup-bar">
                <div class="lp-mockup-dot" style="background:#EF4444;"></div>
                <div class="lp-mockup-dot" style="background:#F59E0B;"></div>
                <div class="lp-mockup-dot" style="background:#10B981;"></div>
            </div>
            <div style="background:#FAFAF9;padding:20px;"
                x-data="{
                    screen: 0,
                    screens: ['dashboard', 'budget', 'tasks'],
                    init() { setInterval(() => { this.screen = (this.screen + 1) % this.screens.length; }, 4000); }
                }">
                <div class="lp-mockup-mobile-cols">
                    <div style="background:#fff;border-right:1px solid #E7E5E4;padding:16px 12px;">
                        <div style="font-size:13px;font-weight:800;color:#1C1917;margin-bottom:20px;padding:0 4px;">Koordli</div>
                        <div style="font-size:11px;padding:8px 10px;border-radius:6px;margin-bottom:2px;transition:all 250ms ease;"
                            :style="screens[screen] === 'dashboard' ? 'color:#7C3AED;background:#F5F3FF;font-weight:600;' : 'color:#78716C;background:transparent;font-weight:400;'">📊 Dashboard</div>
                        <div style="font-size:11px;padding:8px 10px;border-radius:6px;margin-bottom:2px;color:#78716C;">📅 Events</div>
                        <div style="font-size:11px;padding:8px 10px;border-radius:6px;margin-bottom:2px;transition:all 250ms ease;"
                            :style="screens[screen] === 'tasks' ? 'color:#7C3AED;background:#F5F3FF;font-weight:600;' : 'color:#78716C;background:transparent;font-weight:400;'">✓ Tasks</div>
                        <div style="font-size:11px;padding:8px 10px;border-radius:6px;margin-bottom:2px;color:#78716C;">👥 Vendors</div>
                        <div style="font-size:11px;padding:8px 10px;border-radius:6px;margin-bottom:2px;transition:all 250ms ease;"
                            :style="screens[screen] === 'budget' ? 'color:#7C3AED;background:#F5F3FF;font-weight:600;' : 'color:#78716C;background:transparent;font-weight:400;'">💰 Budget</div>
                        <div style="font-size:11px;padding:8px 10px;border-radius:6px;margin-bottom:2px;color:#78716C;">📝 Contracts</div>
                    </div>

                    <div style="position:relative;background:#fff;min-height:280px;">

                        {{-- Screen 1: Dashboard --}}
                        <div x-show="screens[screen] === 'dashboard'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="padding:18px;position:absolute;inset:0;">
                            <div style="font-size:14px;font-weight:700;margin-bottom:4px;color:#1C1917;">Good morning, Amara 👋</div>
                            <div style="font-size:11px;color:#A8A29E;margin-bottom:16px;">Here's what's happening today</div>
                            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:16px;">
                                <div style="background:#F5F3FF;border-radius:8px;padding:10px;">
                                    <div style="font-size:9px;color:#7C3AED;font-weight:600;">EVENTS</div>
                                    <div style="font-size:18px;font-weight:800;color:#1C1917;">12</div>
                                </div>
                                <div style="background:#F0FDF4;border-radius:8px;padding:10px;">
                                    <div style="font-size:9px;color:#10B981;font-weight:600;">TASKS DUE</div>
                                    <div style="font-size:18px;font-weight:800;color:#1C1917;">5</div>
                                </div>
                                <div style="background:#FFFBEB;border-radius:8px;padding:10px;">
                                    <div style="font-size:9px;color:#F59E0B;font-weight:600;">BUDGET</div>
                                    <div style="font-size:18px;font-weight:800;color:#1C1917;">₦2.4M</div>
                                </div>
                            </div>
                            <div style="border:1px solid #E7E5E4;border-radius:8px;padding:12px;">
                                <div style="font-size:11px;font-weight:600;margin-bottom:8px;color:#1C1917;">Upcoming Events</div>
                                @foreach(['Adaeze & Chuka Wedding — Aug 15','CenBa Awards Night — Sep 2','Corporate Retreat — Sep 20'] as $e)
                                <div style="font-size:10.5px;color:#57534E;padding:6px 0;border-bottom:1px solid #F5F5F4;">{{ $e }}</div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Screen 2: Budget --}}
                        <div x-show="screens[screen] === 'budget'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="padding:18px;position:absolute;inset:0;">
                            <div style="font-size:14px;font-weight:700;margin-bottom:4px;color:#1C1917;">Adaeze & Chuka Wedding</div>
                            <div style="font-size:11px;color:#A8A29E;margin-bottom:16px;">Event Budget</div>
                            <div style="display:flex;justify-content:space-between;font-size:12px;padding:10px 0;border-bottom:1px solid #F5F5F4;"><span style="color:#78716C;">Agreed Budget</span><span style="font-weight:700;color:#1C1917;">₦3,000,000</span></div>
                            <div style="display:flex;justify-content:space-between;font-size:12px;padding:10px 0;border-bottom:1px solid #F5F5F4;"><span style="color:#78716C;">Client Paid</span><span style="font-weight:700;color:#10B981;">₦2,040,000</span></div>
                            <div style="display:flex;justify-content:space-between;font-size:12px;padding:10px 0;border-bottom:1px solid #F5F5F4;"><span style="color:#78716C;">Vendor Costs</span><span style="font-weight:700;color:#EF4444;">₦1,850,000</span></div>
                            <div style="margin-top:14px;">
                                <div style="display:flex;justify-content:space-between;font-size:10px;margin-bottom:5px;"><span style="color:#78716C;">Payment Progress</span><span style="color:#10B981;font-weight:600;">68%</span></div>
                                <div style="height:7px;background:#F5F5F4;border-radius:4px;overflow:hidden;"><div style="height:100%;width:68%;background:#10B981;"></div></div>
                            </div>
                        </div>

                        {{-- Screen 3: Tasks --}}
                        <div x-show="screens[screen] === 'tasks'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="padding:18px;position:absolute;inset:0;">
                            <div style="font-size:14px;font-weight:700;margin-bottom:4px;color:#1C1917;">Tasks — This Week</div>
                            <div style="font-size:11px;color:#A8A29E;margin-bottom:16px;">5 due, 2 overdue</div>
                            @foreach([['Confirm catering headcount','Urgent','#EF4444'],['Send RSVP reminder','Normal','#3B82F6'],['Finalize seating chart','High','#F59E0B'],['Brief photographer','Normal','#3B82F6']] as [$task, $priority, $color])
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid #F5F5F4;">
                                <span style="font-size:11.5px;color:#1C1917;">{{ $task }}</span>
                                <span style="font-size:9px;color:{{ $color }};background:{{ $color }}1a;padding:2px 8px;border-radius:8px;font-weight:600;">{{ $priority }}</span>
                            </div>
                            @endforeach
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="lp-mockup-steps lp-animate">
            <span class="lp-mockup-step active">① Create Event</span>
            <span class="lp-mockup-step-arrow">→</span>
            <span class="lp-mockup-step">② Invite Vendors</span>
            <span class="lp-mockup-step-arrow">→</span>
            <span class="lp-mockup-step">③ Track Budget</span>
            <span class="lp-mockup-step-arrow">→</span>
            <span class="lp-mockup-step">④ Run the Day</span>
        </div>
    </section>
    </div>

    {{-- Trusted By --}}
    <section class="lp-trusted lp-animate">
        <div class="lp-trusted-label">Built for event businesses of every kind</div>
        <div class="lp-trusted-marquee">
            <div class="lp-trusted-track">
                @for ($i = 0; $i < 2; $i++)
                <div class="lp-trusted-group">
                    @foreach(['Wedding Planners', 'Corporate Events', 'Conferences', 'Churches', 'Award Ceremonies', 'Production Companies', 'Exhibitions'] as $logo)
                    <span class="lp-trusted-logo">{{ $logo }}</span>
                    <span class="lp-trusted-dot"></span>
                    @endforeach
                </div>
                @endfor
            </div>
        </div>
    </section>

    {{-- Problem --}}
    <section class="lp-section lp-section-alt">
        <div class="lp-container">
            <div class="lp-section-label lp-animate">The Problem</div>
            <h2 class="lp-section-title lp-serif lp-animate">Running Events Shouldn't Mean Chasing Information Everywhere</h2>
            <p class="lp-section-sub lp-animate">Most event businesses run on a patchwork of tools that were never built for this work.</p>

            <div class="lp-problem-grid">
                @foreach([
                    ['icon' => '💬', 'title' => 'WhatsApp Everywhere', 'text' => 'Client updates, vendor confirmations, and staff instructions scattered across dozens of chat threads.'],
                    ['icon' => '📇', 'title' => 'Scattered Client Info', 'text' => 'No single place to see a client\'s event details, payments, and communication history.'],
                    ['icon' => '🔔', 'title' => 'Vendor Follow-Ups', 'text' => 'Manually chasing vendors for availability, contracts, and payment confirmations.'],
                    ['icon' => '📋', 'title' => 'Manual RSVP Tracking', 'text' => 'Counting guest responses by hand from texts, calls, and spreadsheets.'],
                    ['icon' => '📊', 'title' => 'Spreadsheet Budgets', 'text' => 'Budget versions that fall out of sync the moment a vendor payment changes.'],
                    ['icon' => '📄', 'title' => 'Paper Runsheets', 'text' => 'Event-day timelines printed on paper, impossible to update in real time.'],
                ] as $p)
                <div class="lp-problem-card lp-animate">
                    <div class="lp-problem-icon">{{ $p['icon'] }}</div>
                    <div class="lp-problem-title">{{ $p['title'] }}</div>
                    <div class="lp-problem-text">{{ $p['text'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Core Features --}}
    <section class="lp-section" id="features">
        <div class="lp-container">
            <div class="lp-section-label lp-animate">Core Features</div>
            <h2 class="lp-section-title lp-serif lp-animate">Everything Your Event Business Needs, In One Workspace</h2>
            <p class="lp-section-sub lp-animate">From the first client inquiry to event-day execution and final payment.</p>

            {{-- Feature 1: Event Management --}}
            <div class="lp-feature-block">
                <div class="lp-feature-text lp-animate-left">
                    <span class="lp-feature-tag">Event Management</span>
                    <h3 class="lp-feature-title">Manage every event from planning to execution</h3>
                    <p class="lp-feature-text-desc">Track event details, status, timeline, and team all in one place — no more digging through old messages to find what was agreed.</p>
                    <ul class="lp-feature-list">
                        <li>Custom event statuses and types</li>
                        <li>Slug-based event pages, easy to share</li>
                        <li>Full event history and audit trail</li>
                    </ul>
                </div>
                <div class="lp-feature-visual lp-animate-right">
                    <div class="lp-mockup-mini">
                        <div class="lp-mockup-mini-header">
                            <span class="lp-mockup-mini-title">Adaeze & Chuka Wedding</span>
                            <span style="background:#10B981;color:#fff;font-size:10px;padding:3px 8px;border-radius:10px;">Confirmed</span>
                        </div>
                        <div class="lp-mockup-mini-body" style="background:#fff;">
                            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:14px;">
                                <div style="text-align:center;padding:10px;background:#F5F5F4;border-radius:6px;">
                                    <div style="font-size:16px;font-weight:800;color:#7C3AED;">Aug 15</div>
                                    <div style="font-size:9px;color:#A8A29E;">Date</div>
                                </div>
                                <div style="text-align:center;padding:10px;background:#F5F5F4;border-radius:6px;">
                                    <div style="font-size:16px;font-weight:800;color:#10B981;">8</div>
                                    <div style="font-size:9px;color:#A8A29E;">Vendors</div>
                                </div>
                                <div style="text-align:center;padding:10px;background:#F5F5F4;border-radius:6px;">
                                    <div style="font-size:16px;font-weight:800;color:#F59E0B;">312</div>
                                    <div style="font-size:9px;color:#A8A29E;">RSVPs</div>
                                </div>
                            </div>
                            <div style="font-size:11px;color:#78716C;">Victoria Island, Lagos · 350 guests expected</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Feature 2: Client Portal --}}
            <div class="lp-feature-block reverse">
                <div class="lp-feature-text lp-animate-left">
                    <span class="lp-feature-tag">Client Portal</span>
                    <h3 class="lp-feature-title">Collaborate with clients in one secure workspace</h3>
                    <p class="lp-feature-text-desc">Clients get their own portal to track their event, view payment history, and rate vendors — no account confusion, no shared spreadsheets.</p>
                    <ul class="lp-feature-list">
                        <li>Real-time payment progress tracking</li>
                        <li>Read-only RSVP stats view</li>
                        <li>Post-event vendor reviews</li>
                    </ul>
                </div>
                <div class="lp-feature-visual lp-animate-right">
                    <div class="lp-mockup-mini">
                        <div class="lp-mockup-mini-header">
                            <span class="lp-mockup-mini-title">Client Portal — Adaeze O.</span>
                        </div>
                        <div class="lp-mockup-mini-body" style="background:#fff;">
                            <div style="font-size:11px;color:#78716C;margin-bottom:8px;">Payment Progress</div>
                            <div style="height:8px;background:#F5F5F4;border-radius:4px;overflow:hidden;margin-bottom:6px;">
                                <div style="height:100%;width:68%;background:#10B981;"></div>
                            </div>
                            <div style="font-size:11px;color:#1C1917;font-weight:600;margin-bottom:16px;">₦2,040,000 of ₦3,000,000 paid</div>
                            <div style="font-size:11px;color:#78716C;margin-bottom:8px;">Your Vendors</div>
                            @foreach(['Elite Photography ★★★★★','Dutch Decorator ★★★★☆'] as $v)
                            <div style="font-size:11px;color:#1C1917;padding:6px 0;border-bottom:1px solid #F5F5F4;">{{ $v }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Feature 3: Bookings & Consultations --}}
            <div class="lp-feature-block">
                <div class="lp-feature-text lp-animate-left">
                    <span class="lp-feature-tag">Bookings & Consultations</span>
                    <h3 class="lp-feature-title">Capture new leads with custom forms</h3>
                    <p class="lp-feature-text-desc">Build branded booking and consultation forms with your own fields. Embed them anywhere or share a direct link — leads land straight in your dashboard.</p>
                    <ul class="lp-feature-list">
                        <li>Drag-free custom field builder</li>
                        <li>Live availability calendar for consultations</li>
                        <li>WhatsApp redirect after submission</li>
                    </ul>
                </div>
                <div class="lp-feature-visual lp-animate-right">
                    <div class="lp-mockup-mini">
                        <div class="lp-mockup-mini-header">
                            <span class="lp-mockup-mini-title">Book a Consultation</span>
                        </div>
                        <div class="lp-mockup-mini-body" style="background:#fff;">
                            <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;margin-bottom:10px;">
                                @for($i = 1; $i <= 14; $i++)
                                <div style="aspect-ratio:1;display:flex;align-items:center;justify-content:center;font-size:9px;border-radius:4px;{{ $i === 9 ? 'background:#1C1917;color:#fff;font-weight:700;' : 'color:#78716C;' }}">{{ $i }}</div>
                                @endfor
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                                @foreach(['9:00 AM','10:30 AM'] as $t)
                                <div style="text-align:center;font-size:10px;padding:6px;border:1px solid #E7E5E4;border-radius:6px;color:#57534E;">{{ $t }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Feature 4: Vendor Management --}}
            <div class="lp-feature-block reverse">
                <div class="lp-feature-text lp-animate-left">
                    <span class="lp-feature-tag">Vendor Management</span>
                    <h3 class="lp-feature-title">Your vendor directory, contracts, and payments — unified</h3>
                    <p class="lp-feature-text-desc">Build a private directory of trusted vendors, track their availability, generate branded contracts with e-signatures, and record every payment.</p>
                    <ul class="lp-feature-list">
                        <li>Vendor availability conflict warnings</li>
                        <li>Branded contracts with e-signature support</li>
                        <li>Multi-invoice, multi-payment tracking</li>
                    </ul>
                </div>
                <div class="lp-feature-visual lp-animate-right">
                    <div class="lp-mockup-mini">
                        <div class="lp-mockup-mini-header">
                            <span class="lp-mockup-mini-title">Vendor Service Agreement</span>
                            <span style="background:#10B981;color:#fff;font-size:10px;padding:3px 8px;border-radius:10px;">Signed</span>
                        </div>
                        <div class="lp-mockup-mini-body" style="background:#fff;">
                            <div style="font-size:11px;color:#78716C;margin-bottom:12px;">Dutch Decorator · ₦1,500,000</div>
                            <div style="display:flex;gap:8px;">
                                <div style="flex:1;border-top:2px solid #1C1917;padding-top:6px;">
                                    <div style="font-size:9px;color:#A8A29E;">Company</div>
                                    <div style="font-size:10px;font-weight:600;color:#1C1917;">✓ Signed</div>
                                </div>
                                <div style="flex:1;border-top:2px solid #1C1917;padding-top:6px;">
                                    <div style="font-size:9px;color:#A8A29E;">Vendor</div>
                                    <div style="font-size:10px;font-weight:600;color:#1C1917;">✓ Signed</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Feature 5: Task Management --}}
            <div class="lp-feature-block">
                <div class="lp-feature-text lp-animate-left">
                    <span class="lp-feature-tag">Task Management</span>
                    <h3 class="lp-feature-title">Assign work, track progress, stay accountable</h3>
                    <p class="lp-feature-text-desc">Assign tasks to staff or vendors with due dates and priority levels. Everyone sees exactly what's expected of them.</p>
                    <ul class="lp-feature-list">
                        <li>Assign to staff or vendor accounts</li>
                        <li>Priority levels and overdue alerts</li>
                        <li>Instant status updates</li>
                    </ul>
                </div>
                <div class="lp-feature-visual lp-animate-right">
                    <div class="lp-mockup-mini">
                        <div class="lp-mockup-mini-header">
                            <span class="lp-mockup-mini-title">Tasks — This Week</span>
                        </div>
                        <div class="lp-mockup-mini-body" style="background:#fff;">
                            @foreach([['Confirm catering headcount','Urgent','#EF4444'],['Send RSVP reminder','Normal','#3B82F6'],['Finalize seating chart','High','#F59E0B']] as [$task, $priority, $color])
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #F5F5F4;">
                                <span style="font-size:11px;color:#1C1917;">{{ $task }}</span>
                                <span style="font-size:9px;color:{{ $color }};background:{{ $color }}1a;padding:2px 8px;border-radius:8px;font-weight:600;">{{ $priority }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Feature 6: Runsheets --}}
            <div class="lp-feature-block reverse">
                <div class="lp-feature-text lp-animate-left">
                    <span class="lp-feature-tag">Runsheets</span>
                    <h3 class="lp-feature-title">Manage event-day schedules in real time</h3>
                    <p class="lp-feature-text-desc">Timeline-based runsheets with live status updates from vendors on-site. No more printed schedules that go stale the moment something shifts.</p>
                    <ul class="lp-feature-list">
                        <li>Vendors update status from their phones</li>
                        <li>Delay notes with reasons</li>
                        <li>Live progress bar for the whole event</li>
                    </ul>
                </div>
                <div class="lp-feature-visual lp-animate-right">
                    <div class="lp-mockup-mini">
                        <div class="lp-mockup-mini-header">
                            <span class="lp-mockup-mini-title">Wedding Day Runsheet</span>
                        </div>
                        <div class="lp-mockup-mini-body" style="background:#fff;">
                            @foreach([['9:00 AM','Venue Setup','#10B981','Done'],['12:00 PM','Guest Arrival','#F59E0B','In Progress'],['2:00 PM','Ceremony','#A8A29E','Pending']] as [$time, $item, $color, $status])
                            <div style="display:flex;gap:10px;padding:8px 0;border-bottom:1px solid #F5F5F4;align-items:center;">
                                <span style="font-size:10px;color:#78716C;width:56px;flex-shrink:0;">{{ $time }}</span>
                                <span style="width:8px;height:8px;border-radius:50%;background:{{ $color }};flex-shrink:0;"></span>
                                <span style="font-size:11px;color:#1C1917;flex:1;">{{ $item }}</span>
                                <span style="font-size:9px;color:{{ $color }};font-weight:600;">{{ $status }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Feature 7: RSVP --}}
            <div class="lp-feature-block">
                <div class="lp-feature-text lp-animate-left">
                    <span class="lp-feature-tag">RSVP & QR Check-In</span>
                    <h3 class="lp-feature-title">Branded RSVP pages, QR check-in, real numbers</h3>
                    <p class="lp-feature-text-desc">Give guests a beautiful, on-brand RSVP experience — then check them in at the door with a scannable QR ticket.</p>
                    <ul class="lp-feature-list">
                        <li>Custom cover image and colors per event</li>
                        <li>Custom questions beyond just attendance</li>
                        <li>QR ticket emailed automatically</li>
                    </ul>
                </div>
                <div class="lp-feature-visual lp-animate-right">
                    <div class="lp-mockup-mini">
                        <div class="lp-mockup-mini-header">
                            <span class="lp-mockup-mini-title">RSVP — Will you attend?</span>
                        </div>
                        <div class="lp-mockup-mini-body" style="text-align:center;background:#fff;">
                            <div style="width:64px;height:64px;background:#1C1917;border-radius:8px;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;">QR CODE</div>
                            <div style="font-size:11px;font-weight:600;color:#1C1917;">312 confirmed · 38 pending</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Feature 8: Budgets --}}
            <div class="lp-feature-block reverse">
                <div class="lp-feature-text lp-animate-left">
                    <span class="lp-feature-tag">Budgets & Payments</span>
                    <h3 class="lp-feature-title">Know what's agreed, paid, and outstanding — always</h3>
                    <p class="lp-feature-text-desc">Track client payments and vendor invoices side by side. Vendor invoices automatically sync into the event budget, so nothing is entered twice.</p>
                    <ul class="lp-feature-list">
                        <li>Multi-payment tracking per invoice</li>
                        <li>Auto-synced budget line items</li>
                        <li>Client and vendor balances at a glance</li>
                    </ul>
                </div>
                <div class="lp-feature-visual lp-animate-right">
                    <div class="lp-mockup-mini">
                        <div class="lp-mockup-mini-header">
                            <span class="lp-mockup-mini-title">Event Budget</span>
                        </div>
                        <div class="lp-mockup-mini-body" style="background:#fff;">
                            <div style="display:flex;justify-content:space-between;font-size:11px;padding:6px 0;border-bottom:1px solid #F5F5F4;"><span style="color:#78716C;">Agreed Budget</span><span style="font-weight:700;color:#1C1917;">₦3,000,000</span></div>
                            <div style="display:flex;justify-content:space-between;font-size:11px;padding:6px 0;border-bottom:1px solid #F5F5F4;"><span style="color:#78716C;">Client Paid</span><span style="font-weight:700;color:#10B981;">₦2,040,000</span></div>
                            <div style="display:flex;justify-content:space-between;font-size:11px;padding:6px 0;"><span style="color:#78716C;">Vendor Costs</span><span style="font-weight:700;color:#EF4444;">₦1,850,000</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Why Koordli --}}
    <section class="lp-section lp-section-alt">
        <div class="lp-container">
            <div class="lp-section-label lp-animate">Why Koordli</div>
            <h2 class="lp-section-title lp-serif lp-animate">Built For How Event Businesses Actually Work</h2>
            <div class="lp-why-grid">
                @foreach([
                    ['icon' => '🏢', 'title' => 'Multi-Tenant', 'text' => 'Your company gets its own secure workspace'],
                    ['icon' => '🔒', 'title' => 'Secure', 'text' => 'Isolated data, role-based access control'],
                    ['icon' => '📈', 'title' => 'Scalable', 'text' => 'From solo planners to growing teams'],
                    ['icon' => '📱', 'title' => 'Mobile-Ready', 'text' => 'Works everywhere your team does'],
                    ['icon' => '🔌', 'title' => 'API-Ready', 'text' => 'Built to connect with your other tools'],
                    ['icon' => '🤝', 'title' => 'Collaborative', 'text' => 'Clients, staff, and vendors, one workspace'],
                ] as $w)
                <div class="lp-why-item lp-animate">
                    <div class="lp-why-icon">{{ $w['icon'] }}</div>
                    <div class="lp-why-title">{{ $w['title'] }}</div>
                    <div class="lp-why-text">{{ $w['text'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- How It Works --}}
    <section class="lp-section">
        <div class="lp-container">
            <div class="lp-section-label lp-animate">How It Works</div>
            <h2 class="lp-section-title lp-serif lp-animate">From First Inquiry to Event Day</h2>
            <div class="lp-flow lp-animate">
                @foreach(['Lead', 'Booking', 'Client Portal', 'Planning', 'Vendors', 'Tasks', 'RSVP', 'Runsheet', 'Event Day'] as $step)
                <div class="lp-flow-step">{{ $step }}</div>
                @if(!$loop->last)<span class="lp-flow-arrow">→</span>@endif
                @endforeach
            </div>
        </div>
    </section>

    {{-- Pricing --}}
    <section class="lp-section lp-section-alt" id="pricing">
        <div class="lp-container">
            <div class="lp-section-label lp-animate">Pricing</div>
            <h2 class="lp-section-title lp-serif lp-animate">Simple Pricing That Grows With You</h2>
            <p class="lp-section-sub lp-animate">Start free. Upgrade whenever you're ready.</p>

            <div class="lp-pricing-toggle lp-animate">
                <div class="lp-pricing-toggle-inner">
                    <button x-on:click="cycle = 'monthly'"
                        class="lp-pricing-toggle-btn"
                        :style="cycle === 'monthly' ? 'background:var(--lp-text);color:var(--lp-bg);' : ''">
                        Monthly
                    </button>
                    <button x-on:click="cycle = 'annual'"
                        class="lp-pricing-toggle-btn"
                        :style="cycle === 'annual' ? 'background:var(--lp-text);color:var(--lp-bg);' : ''">
                        Annual
                    </button>
                </div>
            </div>

            <div class="lp-pricing-grid">
                @foreach($plans as $plan)
                @php
                    $monthlyPricing = $pricingData[$plan->id]['monthly'] ?? null;
                    $annualPricing  = $pricingData[$plan->id]['annual'] ?? null;
                @endphp
                <div class="lp-pricing-card {{ $plan->is_featured ? 'featured' : '' }} lp-animate"
                    x-show="{{ $plan->allowsMonthly() ? 'true' : 'false' }} && cycle === 'monthly' || {{ $plan->allowsAnnual() ? 'true' : 'false' }} && cycle === 'annual'">
                    @if($plan->is_featured)<div class="lp-pricing-badge">Most Popular</div>@endif
                    <div class="lp-pricing-name">{{ $plan->name }}</div>

                    @if($plan->is_contact_only)
                    <div class="lp-pricing-price" style="font-size: 0.6em;">Custom pricing</div>
                    <div class="lp-pricing-period">Talk to our team</div>
                    @else
                        @if($monthlyPricing)
                        <div x-show="cycle === 'monthly'">
                            <div class="lp-pricing-price">{{ \App\Helpers\CurrencyHelper::symbol($monthlyPricing['currency']) }}{{ number_format($monthlyPricing['amount'], 0) }}</div>
                            <div class="lp-pricing-period">per month · {{ $monthlyPricing['currency'] }}</div>
                        </div>
                        @endif
                        @if($annualPricing)
                        <div x-show="cycle === 'annual'" x-cloak>
                            <div class="lp-pricing-price">{{ \App\Helpers\CurrencyHelper::symbol($annualPricing['currency']) }}{{ number_format($annualPricing['amount'], 0) }}</div>
                            <div class="lp-pricing-period">per year · {{ $annualPricing['currency'] }}</div>
                        </div>
                        @endif

                        @if($plan->trial_days > 0)
                        <div style="display:flex;align-items:center;gap:6px;margin:10px 0;font-size:12.5px;font-weight:600;color:#059669;">
                            <span>✓</span> {{ $plan->trial_days }}-day free trial · no card required
                        </div>
                        @endif
                    @endif

                    @if(!empty($plan->limits))
                    <ul class="lp-pricing-features">
                        @foreach($plan->limits as $key => $val)
                        <li>{{ $val == -1 ? 'Unlimited' : $val }} {{ str_replace('_', ' ', str_replace('max ', '', strtolower(str_replace('max_', '', $key)))) }}</li>
                        @endforeach
                    </ul>
                    @endif

                    <a href="{{ route('register') }}" wire:navigate class="lp-btn {{ $plan->is_featured ? 'lp-btn-primary' : 'lp-btn-secondary' }}" style="width:100%;">
                        @if($plan->is_contact_only)
                            Contact Us →
                        @elseif($plan->trial_days > 0)
                            Start Free Trial →
                        @else
                            Subscribe Now →
                        @endif
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section class="lp-section" id="faq">
        <div class="lp-container">
            <div class="lp-section-label lp-animate">FAQ</div>
            <h2 class="lp-section-title lp-serif lp-animate">Frequently Asked Questions</h2>
            <div class="lp-faq lp-animate" x-data="{ openFaq: null }">
                @foreach([
                    'Is Koordli only for weddings?' => 'Not at all. Koordli is built for any kind of event business — corporate events, conferences, churches, award ceremonies, birthdays, exhibitions, and more.',
                    'Can I use my own domain?' => 'Custom domain support is on our roadmap. For now, every company gets a secure workspace on Koordli.',
                    'Can clients access the platform?' => 'Yes. Each client gets their own portal to track their event, view payment progress, and rate vendors after the event.',
                    'Can vendors log in?' => 'Yes. Vendors get a dedicated portal to view assigned events, update runsheet status, manage their availability, and sign contracts.',
                    'Does RSVP require extra payment?' => 'RSVP is included as part of your plan — no separate add-on required.',
                    'Can I customize booking forms?' => 'Yes. Build custom fields for booking and consultation forms, and embed them anywhere or share a direct link.',
                ] as $q => $a)
                @php $idx = $loop->index; @endphp
                <div class="lp-faq-item">
                    <div class="lp-faq-q" x-on:click="openFaq = openFaq === {{ $idx }} ? null : {{ $idx }}">
                        <span>{{ $q }}</span>
                        <span x-text="openFaq === {{ $idx }} ? '−' : '+'" style="font-size:20px;color:var(--lp-text-faint);flex-shrink:0;"></span>
                    </div>
                    <div class="lp-faq-a" x-show="openFaq === {{ $idx }}" x-cloak>{{ $a }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Recent Blog Posts --}}
    @php
        $recentBlogPosts = \App\Models\Central\BlogPost::where('status', 'published')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();
    @endphp
    @if($recentBlogPosts->isNotEmpty())
    <section class="lp-section lp-animate">
        <div class="lp-container">
            <div class="lp-section-label">From the Blog</div>
            <h2 class="lp-section-title lp-serif">Guides For Running A Better Event Business</h2>
            <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:20px;margin-top:32px;">
                @foreach($recentBlogPosts as $post)
                <a href="{{ route('blog.show', $post->slug) }}" wire:navigate style="border:1px solid var(--lp-border);border-radius:14px;overflow:hidden;text-decoration:none;background:var(--lp-card-bg);transition:transform 180ms ease;">
                    @if($post->featuredImageUrl())
                    <img src="{{ $post->featuredImageUrl() }}" style="width:100%;height:150px;object-fit:cover;background:var(--lp-bg-alt);" loading="lazy" alt="{{ $post->title }}">
                    @else
                    <div style="width:100%;height:150px;background:var(--lp-bg-alt);"></div>
                    @endif
                    <div style="padding:18px;">
                        <div style="font-size:15px;font-weight:700;color:var(--lp-text);margin-bottom:6px;line-height:1.35;">{{ $post->title }}</div>
                        <div style="font-size:12.5px;color:var(--lp-text-muted);line-height:1.6;">{{ \Illuminate\Support\Str::limit($post->excerpt, 90) }}</div>
                    </div>
                </a>
                @endforeach
            </div>
            <div style="text-align:center;margin-top:32px;">
                <a href="{{ route('blog.index') }}" wire:navigate class="lp-btn lp-btn-secondary">Read More Articles →</a>
            </div>
        </div>
    </section>
    @endif

    {{-- Final CTA — full bleed --}}
    <div class="lp-final-cta-outer">
        <div class="lp-final-cta-inner lp-animate">
            <h2 class="lp-final-cta-title lp-serif">Ready To Run Your Events Without The Chaos?</h2>
            <p class="lp-final-cta-sub">Start your free trial today. No card required.</p>
            <a href="{{ route('register') }}" wire:navigate class="lp-btn lp-btn-primary lp-btn-lg">Start Free Trial →</a>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="lp-footer">
        <div class="lp-footer-grid">
            <div>
                <x-ui.logo color="auto" />
                <p style="font-size:13px;color:var(--lp-text-faint);margin-top:16px;max-width:280px;line-height:1.6;">
                    {{ $siteTagline ?? 'Event Operations Simplified' }}
                </p>
            </div>
            <div>
                <div class="lp-footer-col-title">Product</div>
                <a href="#features" class="lp-footer-link">Features</a>
                <a href="#pricing" class="lp-footer-link">Pricing</a>
                <a href="{{ route('blog.index') }}" wire:navigate class="lp-footer-link">Blog</a>
                <a href="{{ route('docs') }}" target="_blank" class="lp-footer-link">Documentation</a>
            </div>
            <div>
                <div class="lp-footer-col-title">Company</div>
                <a href="#" class="lp-footer-link">Privacy Policy</a>
                <a href="#" class="lp-footer-link">Terms of Service</a>
                <a href="#" class="lp-footer-link">Contact</a>
            </div>
            <div>
                <div class="lp-footer-col-title">Get Started</div>
                <a href="{{ route('register') }}" wire:navigate class="lp-footer-link">Start Free Trial</a>
                <a href="{{ route('tenant.login') }}" wire:navigate class="lp-footer-link">Sign In</a>
            </div>
        </div>
        <div class="lp-footer-bottom">
            <span>© {{ date('Y') }} {{ $siteName ?? 'Koordli' }}. All rights reserved.</span>
            <span>Built for event businesses everywhere.</span>
        </div>
    </footer>

</div>
</div>