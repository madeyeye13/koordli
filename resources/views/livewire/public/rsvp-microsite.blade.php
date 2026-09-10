<div x-data="{
    section: new URLSearchParams(window.location.search).get('section') || @js($editing ? 'rsvp' : null) || (window.location.hash ? window.location.hash.slice(1) : 'home'),
    step: @entangle('step'),
    mobileMenuOpen: false,
    countdown: { d: 0, h: 0, m: 0, s: 0 },
    eventDate: '{{ $rsvpForm->event->date?->toIso8601String() }}',
    init() {
    if ('scrollRestoration' in history) { history.scrollRestoration = 'manual'; }
    window.scrollTo(0, 0);
    this.$watch('section', (val) => {
        history.replaceState(null, '', '#' + val);
        this.$nextTick(() => window.scrollTo(0, 0));
    });
        if (this.eventDate) {
            setInterval(() => {
                const diff = new Date(this.eventDate) - new Date();
                if (diff <= 0) return;
                this.countdown.d = Math.floor(diff / 86400000);
                this.countdown.h = Math.floor((diff % 86400000) / 3600000);
                this.countdown.m = Math.floor((diff % 3600000) / 60000);
                this.countdown.s = Math.floor((diff % 60000) / 1000);
            }, 1000);
        }
    }
}">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Satoshi', sans-serif; }
    * { scrollbar-width:thin; scrollbar-color:#E2DFDC transparent; }
    *::-webkit-scrollbar { width:7px; }
    *::-webkit-scrollbar-thumb { background:#E2DFDC; border-radius:10px; }
    [x-cloak] { display:none !important; }

    /* Shared design tokens — 3 real, tenant-customizable colors
       (already editable via the existing Branding tab); everything
       else (border, muted text, the lighter gold accent) derives
       automatically via color-mix(), so changing one real color
       repaints the whole site consistently. */
    :root {
        --color-obsidian: {{ $rsvpForm->branding['dark_color'] ?? '#1A1815' }};
        --color-gold: {{ $rsvpForm->branding['accent_color'] ?? '#C9943A' }};
        --color-ivory: {{ $rsvpForm->branding['bg_color'] ?? '#F5F0E8' }};
        --color-gold-light: color-mix(in srgb, var(--color-gold) 55%, white);
        --color-border: color-mix(in srgb, var(--color-obsidian) 12%, var(--color-ivory));
        --color-muted: color-mix(in srgb, var(--color-obsidian) 45%, white);
        --font-serif: 'Fraunces', serif;
        --font-sans: 'Satoshi', sans-serif;
    }
    body { font-family: var(--font-sans); }

    .section-eyebrow { font-family: var(--font-sans); font-size: 0.75rem; font-weight: 500; letter-spacing: 0.15em; text-transform: uppercase; color: var(--color-gold); }
    .section-title { font-family: var(--font-serif); font-size: clamp(1.75rem, 4vw, 2.75rem); font-weight: 400; color: var(--color-obsidian); }
    .section-title em { font-style: italic; color: var(--color-gold); }

    .btn-gold { display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: var(--color-gold); color: #fff; padding: 14px 32px; font-size: 13px; font-weight: 600; letter-spacing: 0.05em; text-decoration: none; border: none; cursor: pointer; transition: opacity 150ms; }
    .btn-gold:hover { opacity: 0.88; }
    .btn-outline-gold { display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: transparent; color: var(--color-gold); padding: 13px 30px; font-size: 13px; font-weight: 600; letter-spacing: 0.05em; text-decoration: none; border: 1.5px solid var(--color-gold); cursor: pointer; transition: all 150ms; }
    .btn-outline-gold:hover { background: var(--color-gold); color: #fff; }

    .form-label { display: block; font-size: 11px; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: var(--color-muted); margin-bottom: 6px; font-family: var(--font-sans); }
    .form-input { width: 100%; background: #fff; border: 1px solid var(--color-border); border-radius: 0; padding: 12px 14px; font-size: 14px; font-family: var(--font-sans); color: var(--color-obsidian); outline: none; transition: border-color 150ms; }
    .form-input:focus { border-color: var(--color-gold); }

    .gold-divider { display: flex; align-items: center; gap: 16px; }
    .gold-divider::before, .gold-divider::after { content: ''; flex: 1; height: 1px; background: var(--color-border); }

    .hero-section { position: relative; min-height: 85vh; display: flex; align-items: center; justify-content: center; }
    .hero-overlay { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(26,24,21,0.55), rgba(26,24,21,0.82)); }
    .hero-content { position: relative; z-index: 1; text-align: center; padding: 20px; }

    .stepper-dot { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; font-family: var(--font-sans); background: #fff; border: 1.5px solid var(--color-border); color: var(--color-muted); }
    .stepper-dot.active { border-color: var(--color-gold); background: var(--color-gold); color: #fff; }
    .stepper-dot.done { border-color: var(--color-obsidian); background: var(--color-obsidian); color: #fff; }

    .ms-serif { font-family:'Fraunces', serif; }
    .ms-wrap { background:var(--ms-bg); color:#1C1917; min-height:100vh; overflow-anchor:none; }

    .ms-nav { position:sticky; top:0; z-index:30; background:var(--color-obsidian); border-bottom:1px solid rgba(255,255,255,0.08); display:flex; justify-content:center; gap:4px; padding:16px; flex-wrap:wrap; }
    .ms-nav-item { display:inline-block; font-size:12.5px; font-weight:600; color:rgba(255,255,255,0.65); padding:8px 16px; border-radius:20px; cursor:pointer; letter-spacing:0.02em; text-transform:uppercase; text-decoration:none; transition:color 150ms; }
    .ms-nav-item:hover { color:#fff; }
    .ms-nav-item.active { background:var(--color-gold); color:#fff; }
    .ms-nav-toggle { display:none; align-items:center; justify-content:space-between; width:100%; padding:12px 16px; color:#fff; background:none; border:0; font-size:13px; font-weight:600; letter-spacing:0.08em; text-transform:uppercase; cursor:pointer; }
    .ms-nav-toggle-icon { width:20px; height:20px; }
    .ms-nav-links { display:flex; justify-content:center; gap:4px; flex-wrap:wrap; }

    .ms-section { display:none; padding:60px 20px 100px; max-width:900px; margin:0 auto; }
    .ms-section.active { display:block; }

    .ms-hero { text-align:center; padding:100px 20px 60px; }
    @if(($rsvpForm->branding['cover_image'] ?? null))
    .ms-hero { background: linear-gradient(180deg, rgba(0,0,0,0.35), rgba(0,0,0,0.55)), url('{{ $rsvpForm->branding['cover_image'] }}') center/cover; color:#fff; }
    @endif
    .ms-hero-eyebrow { font-size:12px; letter-spacing:0.15em; text-transform:uppercase; opacity:0.85; margin-bottom:16px; }
    .ms-hero h1 { font-size:clamp(38px,7vw,70px); font-weight:500; margin-bottom:14px; }
    .ms-hero-tag { font-size:14px; opacity:0.9; margin-bottom:6px; }
    .ms-hero-date { font-size:13px; opacity:0.8; margin-bottom:32px; }
    .ms-hero-btn { display:inline-block; background:var(--ms-accent); color:#fff; padding:14px 32px; border-radius:6px; font-size:13px; font-weight:600; text-decoration:none; letter-spacing:0.05em; text-transform:uppercase; }

    .ms-countdown { display:flex; justify-content:center; gap:20px; margin-top:40px; flex-wrap:wrap; }
    .ms-countdown-box { text-align:center; }
    .ms-countdown-num { font-size:32px; font-weight:700; font-family:'Fraunces',serif; }
    .ms-countdown-label { font-size:10px; letter-spacing:0.1em; text-transform:uppercase; opacity:0.8; }

    .ms-section-eyebrow { font-size:12px; letter-spacing:0.15em; text-transform:uppercase; color:var(--ms-accent); text-align:center; margin-bottom:14px; }
    .ms-section-title { font-size:clamp(26px,5vw,42px); text-align:center; margin-bottom:40px; font-weight:500; }

    .ms-chapter { margin-bottom:48px; text-align:center; }
    .ms-chapter-mark { color:var(--ms-accent); font-size:20px; margin-bottom:12px; }
    .ms-chapter h3 { font-size:24px; font-style:italic; font-weight:300; margin-bottom:16px; }
    .ms-chapter p { font-size:14.5px; line-height:1.85; color:#3F3C3A; max-width:640px; margin:0 auto; }

    .ms-gallery-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
    @media (max-width:640px) { .ms-gallery-grid { grid-template-columns:repeat(2,1fr); } }
    .ms-gallery-grid img { width:100%; height:220px; object-fit:cover; border-radius:8px; }

    .ms-wish-form { max-width:480px; margin:0 auto 50px; }
    .ms-wish-form input, .ms-wish-form textarea { width:100%; padding:12px 14px; border:1px solid #E7E5E4; border-radius:8px; font-size:13.5px; margin-bottom:10px; font-family:inherit; }
    .ms-wish-submit { width:100%; background:var(--ms-accent); color:#fff; border:none; padding:13px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; }
    .ms-wish-card { background:#fff; border-radius:10px; padding:18px; margin-bottom:14px; box-shadow:0 2px 10px rgba(0,0,0,0.04); }
    .ms-wish-name { font-size:13px; font-weight:700; margin-bottom:6px; }
    .ms-wish-msg { font-size:13.5px; color:#57534E; line-height:1.6; }

    .ms-gift-card { background:#fff; border-radius:12px; padding:28px; margin-bottom:16px; text-align:center; }
    .ms-gift-note { font-size:14px; color:#57534E; line-height:1.7; margin-bottom:24px; max-width:500px; margin-left:auto; margin-right:auto; }
    .ms-bank-row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #F5F5F4; font-size:13px; text-align:left; }
    .ms-registry-link { display:inline-block; background:var(--ms-accent); color:#fff; padding:10px 22px; border-radius:8px; font-size:12.5px; font-weight:600; text-decoration:none; margin:6px; }

    .ms-rsvp-form input, .ms-rsvp-form select, .ms-rsvp-form textarea { width:100%; padding:12px 14px; border:1px solid #E7E5E4; border-radius:8px; font-size:13.5px; margin-bottom:12px; font-family:inherit; }
    .ms-rsvp-submit { width:100%; background:var(--ms-accent); color:#fff; border:none; padding:14px; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; }

    .ms-dress-swatch { display:inline-flex; flex-direction:column; align-items:center; margin:10px; }
    .ms-dress-circle { width:56px; height:56px; border-radius:50%; margin-bottom:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
    .ms-dress-name { font-size:11px; color:#57534E; }

    .ms-venue-locked { background:#F5F5F4; border-radius:8px; padding:16px; font-size:12.5px; color:#78716C; text-align:center; }
    .ms-page { padding:0 1.5rem 5rem; background:var(--color-ivory); }
    .ms-page-hero { margin:0 -1.5rem; text-align:center; padding:4rem 1.5rem; background:var(--color-obsidian); }
    .ms-lightbox-content { width:100%; height:100%; display:flex; align-items:center; justify-content:center; padding:60px 80px; }
    .ms-lightbox-content img { max-width:100%; max-height:80vh; object-fit:contain; }
    .rsvp-nav-btn { transition: opacity 180ms ease, transform 180ms ease; }
    .rsvp-nav-btn.is-loading { opacity:0.55; cursor:wait; transform:translateY(1px); }
    .rsvp-submit-btn { transition: opacity 180ms ease, transform 180ms ease; }
    .rsvp-submit-btn.is-loading { opacity:0.55; cursor:wait; transform:translateY(1px); }
    .rsvp-step-fields > div:not(:last-child) { margin-bottom:1.5rem; }
    .rsvp-step-attendance > div:first-child { margin-bottom:1rem; }
    @media (max-width:640px) {
        .ms-lightbox-content { padding:56px 44px; }
    }
    button {font-family: var(--font-sans); }
    @media (max-width:640px) {
        .ms-nav { display:block; padding:8px 12px; }
        .ms-nav-toggle { display:flex; }
        .ms-nav-links { display:none; flex-direction:column; gap:2px; padding:8px 0 4px; }
        .ms-nav-links.is-open { display:flex; }
        .ms-nav-item { display:block; width:100%; padding:12px 16px; border-radius:6px; }
    }
</style>

@php
    $systemQuestions = array_replace_recursive([
        'name' => ['label' => 'Full Name', 'enabled' => true, 'required' => true],
        'email' => ['label' => 'Email Address', 'enabled' => true, 'required' => false],
        'status' => ['label' => 'Will You Be Attending?', 'enabled' => true, 'required' => true],
        'plus_one_count' => ['label' => 'Additional Guests', 'enabled' => true, 'required' => false],
    ], $rsvpForm->system_questions ?? []);
@endphp

<div class="ms-wrap">
    <nav class="ms-nav">
        <button type="button" class="ms-nav-toggle" x-on:click="mobileMenuOpen = !mobileMenuOpen" :aria-expanded="mobileMenuOpen.toString()" aria-controls="public-rsvp-nav-links">
            <span>Menu</span>
            <svg class="ms-nav-toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <div id="public-rsvp-nav-links" class="ms-nav-links" :class="mobileMenuOpen && 'is-open'">
            <a href="{{ request()->url() }}?section=home" class="ms-nav-item" :class="section === 'home' && 'active'">Home</a>
            @if($settings?->story_enabled && $chapters->isNotEmpty())
            <a href="{{ request()->url() }}?section=story" class="ms-nav-item" :class="section === 'story' && 'active'">Our Story</a>
            @endif
            <a href="{{ request()->url() }}?section=rsvp" class="ms-nav-item" :class="section === 'rsvp' && 'active'">RSVP</a>
            @if($settings?->wishes_enabled)
            <a href="{{ request()->url() }}?section=wishes" class="ms-nav-item" :class="section === 'wishes' && 'active'">Wishes</a>
            @endif
            @if($settings?->gallery_enabled && $images->isNotEmpty())
            <a href="{{ request()->url() }}?section=gallery" class="ms-nav-item" :class="section === 'gallery' && 'active'">Gallery</a>
            @endif

            @if($settings?->gifts_enabled && $giftInfo)
            <a href="{{ request()->url() }}?section=gifts" class="ms-nav-item" :class="section === 'gifts' && 'active'">Gift Us</a>
            @endif
        </div>
    </nav>

    {{-- HOME --}}
    <div x-show="section === 'home'">
        <section class="hero-section" @if($rsvpForm->branding['cover_image'] ?? null) style="background-image:url('{{ $rsvpForm->branding['cover_image'] }}');background-size:cover;background-position:center;" @else style="background:var(--color-obsidian);" @endif>
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <p class="mb-3" style="font-family:var(--font-sans);font-size:0.75rem;font-weight:500;letter-spacing:0.15em;text-transform:uppercase;color:{{ $rsvpForm->branding['invited_label_color'] ?? '#E8D5A3' }};">You're Invited</p>
                <h1 style="font-family:var(--font-serif);font-size:clamp(2rem,5.5vw,3.75rem);font-weight:300;color:#fff;line-height:1.12;">
                    {{ $rsvpForm->title }}
                </h1>
                <p style="color:rgba(255,255,255,0.6);font-size:0.875rem;letter-spacing:0.1em;text-transform:uppercase;margin:1rem 0 2.5rem;">
                    {{ $rsvpForm->event->date?->format('l, jS F Y') ?? 'Date TBC' }}
                    @if($rsvpForm->event->venue) &middot; {{ $rsvpForm->event->venue }} @endif
                </p>
                <button type="button" x-on:click="section = 'rsvp'" class="btn-gold">RSVP Now</button>

                @if($settings?->countdown_enabled)
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;max-width:420px;margin:2.5rem auto 0;">
                    <div style="text-align:center;">
                        <div style="border:1px solid rgba(255,255,255,0.2);background:rgba(255,255,255,0.05);padding:1rem 0;">
                            <span x-text="countdown.d" style="font-family:var(--font-serif);font-size:1.75rem;color:var(--color-gold-light);"></span>
                        </div>
                        <p style="font-size:10px;color:rgba(255,255,255,0.5);letter-spacing:0.1em;text-transform:uppercase;margin-top:6px;">Days</p>
                    </div>
                    <div style="text-align:center;">
                        <div style="border:1px solid rgba(255,255,255,0.2);background:rgba(255,255,255,0.05);padding:1rem 0;">
                            <span x-text="countdown.h" style="font-family:var(--font-serif);font-size:1.75rem;color:var(--color-gold-light);"></span>
                        </div>
                        <p style="font-size:10px;color:rgba(255,255,255,0.5);letter-spacing:0.1em;text-transform:uppercase;margin-top:6px;">Hours</p>
                    </div>
                    <div style="text-align:center;">
                        <div style="border:1px solid rgba(255,255,255,0.2);background:rgba(255,255,255,0.05);padding:1rem 0;">
                            <span x-text="countdown.m" style="font-family:var(--font-serif);font-size:1.75rem;color:var(--color-gold-light);"></span>
                        </div>
                        <p style="font-size:10px;color:rgba(255,255,255,0.5);letter-spacing:0.1em;text-transform:uppercase;margin-top:6px;">Mins</p>
                    </div>
                    <div style="text-align:center;">
                        <div style="border:1px solid rgba(255,255,255,0.2);background:rgba(255,255,255,0.05);padding:1rem 0;">
                            <span x-text="countdown.s" style="font-family:var(--font-serif);font-size:1.75rem;color:var(--color-gold-light);"></span>
                        </div>
                        <p style="font-size:10px;color:rgba(255,255,255,0.5);letter-spacing:0.1em;text-transform:uppercase;margin-top:6px;">Secs</p>
                    </div>
                </div>
                @endif
            </div>
        </section>

        @if($settings?->wishes_enabled)
        <section style="padding:6rem 1.5rem;text-align:center;background:var(--color-ivory);">
            <div style="max-width:36rem;margin:0 auto;">
                <p class="section-eyebrow mb-3">Share Your Love</p>
                <h2 class="section-title mb-4" style="font-weight:300;">Leave a Wish</h2>
                <p style="color:var(--color-muted);font-size:0.9375rem;margin-bottom:2rem;line-height:1.8;">Your words mean the world to us. Share a message, blessing, or memory.</p>
                <button type="button" x-on:click="section = 'wishes'" class="btn-outline-gold">Write a Wish</button>
            </div>
        </section>
        @endif


        @if($settings?->hotels_enabled && $settings->hotels_maps_url)
        <section style="background:var(--color-obsidian);padding:5rem 1.5rem;text-align:center;">
            <p class="section-eyebrow mb-3">Travelling From Afar?</p>
            <h2 class="section-title mb-3" style="color:#fff;font-weight:300;">Find Nearby Hotels</h2>
            @if($settings->hotels_note)
            <p style="color:rgba(255,255,255,0.55);font-size:0.9375rem;max-width:480px;margin:0 auto 2.5rem;line-height:1.8;">{{ $settings->hotels_note }}</p>
            @endif
            <a href="{{ $settings->hotels_maps_url }}" target="_blank" class="btn-gold">📍 Find Hotels Near Venue</a>
            <p style="color:rgba(255,255,255,0.35);font-size:0.75rem;margin-top:1rem;">Opens Google Maps</p>
        </section>
        @endif

        @if($settings?->dress_code_enabled && !empty($settings->dress_code_colors))
        <section style="padding:5rem 1.5rem;background:#fff;text-align:center;">
            <p class="section-eyebrow mb-3">Dress Code</p>
            <h2 class="section-title mb-3" style="font-weight:300;">Colour Palette for Guests</h2>
            @if($settings->dress_code_note)<p style="color:var(--color-muted);font-size:0.9375rem;margin-bottom:2.5rem;">{{ $settings->dress_code_note }}</p>@endif
            @php
                $palette = $settings->dress_code_colors;
                $palette = isset($palette[0]['colors'])
                    ? $palette
                    : [['name' => 'Colour Palette', 'colors' => $palette]];
            @endphp
            <div style="max-width:47rem;margin:0 auto;">
                @foreach($palette as $group)
                <div style="margin-top:2.75rem;">
                    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.75rem;">
                        <span style="height:1px;background:#E6DCC9;flex:1;"></span>
                        <span style="font-size:0.6875rem;letter-spacing:0.18em;text-transform:uppercase;color:var(--color-gold);white-space:nowrap;">{{ $group['name'] }}</span>
                        <span style="height:1px;background:#E6DCC9;flex:1;"></span>
                    </div>
                    <div style="display:flex;align-items:flex-start;justify-content:center;gap:1.25rem;flex-wrap:wrap;">
                        @foreach($group['colors'] as $c)
                        <div style="display:flex;flex-direction:column;align-items:center;min-width:76px;">
                            <div style="width:74px;height:74px;border-radius:50%;background:{{ $c['hex'] }};box-shadow:0 0 0 3px #fff,0 1px 7px rgba(28,25,23,0.2);margin-bottom:0.75rem;"></div>
                            <p style="font-size:0.75rem;color:var(--color-obsidian);margin:0 0 0.35rem;font-weight:500;">{{ $c['name'] }}</p>
                            <p style="font-size:0.6875rem;color:var(--color-muted);margin:0;letter-spacing:0.04em;">{{ strtoupper($c['hex']) }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </section>
        @endif


    </div>

    {{-- STORY --}}
    @if($settings?->story_enabled)
    <div x-show="section === 'story'" x-cloak>
        <div style="text-align:center;padding:4rem 1.5rem;background:var(--color-obsidian);">
            <p class="section-eyebrow mb-2">Our Journey</p>
            <h1 style="font-family:var(--font-serif);font-size:clamp(1.75rem,4vw,2.75rem);font-weight:300;color:#fff;">Our Story</h1>
        </div>
        <section style="padding:6rem 1.5rem;background:var(--color-ivory);">
            <div style="max-width:48rem;margin:0 auto;">

                @foreach($chapters as $chapter)
                <div style="margin-bottom:5rem;text-align:center;">
                    <h3 style="font-family:var(--font-serif);font-size:1.5rem;font-style:italic;font-weight:300;color:var(--color-obsidian);margin-bottom:1.5rem;">{{ $chapter->title }}</h3>
                    <div class="gold-divider" style="max-width:120px;margin:0 auto 2rem;"><span style="color:var(--color-gold);font-size:1rem;">✦</span></div>
                    <div style="color:#3a3a3a;font-size:1rem;line-height:1.9;text-align:left;">
                        @foreach(explode("\n", $chapter->content) as $para)
                            @if(trim($para))<p style="margin-bottom:1.25rem;">{{ trim($para) }}</p>@endif
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        @if($settings->gate_venue_address && ($rsvpForm->event->venue || ($settings->separate_venues_enabled && $settings->reception_venue)))
        <section style="background:var(--color-obsidian);padding:5rem 1.5rem;">
            <div style="max-width:32rem;margin:0 auto;text-align:center;">
                <p class="section-eyebrow mb-3">The Celebration</p>
                <h2 class="section-title mb-3" style="color:#fff;">Venue Details</h2>
                <p style="color:rgba(255,255,255,0.4);font-size:0.875rem;margin-bottom:2.5rem;">Venue details visible to confirmed guests only</p>

                @if($this->canSeeVenueAddress())
                <div style="display:grid;grid-template-columns:{{ $settings->separate_venues_enabled && $settings->reception_venue ? 'repeat(2, minmax(0, 1fr))' : '1fr' }};gap:1rem;text-align:left;">
                    <div style="border:1px solid rgba(255,255,255,0.1);padding:1.5rem;">
                        <p class="section-eyebrow" style="margin-bottom:0.75rem;">Ceremony</p>
                        <p style="font-family:var(--font-serif);font-size:1.125rem;color:#fff;margin-bottom:0.75rem;">{{ $rsvpForm->event->venue }}</p>
                        @if($rsvpForm->event->date || $rsvpForm->event->start_time)
                        <p style="font-size:0.875rem;color:rgba(255,255,255,0.55);">🕙 @if($rsvpForm->event->date){{ $rsvpForm->event->date->format('l, F j, Y') }}@endif @if($rsvpForm->event->start_time)&middot; {{ \Carbon\Carbon::parse($rsvpForm->event->start_time)->format('g:i A') }}@endif</p>
                        @endif
                    </div>
                    @if($settings->separate_venues_enabled && $settings->reception_venue)
                    <div style="border:1px solid rgba(255,255,255,0.1);padding:1.5rem;">
                        <p class="section-eyebrow" style="margin-bottom:0.75rem;">Reception</p>
                        <p style="font-family:var(--font-serif);font-size:1.125rem;color:#fff;margin-bottom:0.75rem;">{{ $settings->reception_venue }}</p>
                        @if($settings->reception_address)<p style="font-size:0.875rem;color:rgba(255,255,255,0.55);margin-bottom:0.75rem;">{{ $settings->reception_address }}</p>@endif
                        @if($settings->reception_time)<p style="font-size:0.875rem;color:rgba(255,255,255,0.55);">🕙 {{ \Carbon\Carbon::parse($settings->reception_time)->format('g:i A') }}</p>@endif
                    </div>
                    @endif
                </div>
                @else
                <div class="ms-venue-locked">Full venue addresses are provided to confirmed guests via RSVP.</div>
                @endif

                <div style="margin-top:2.5rem;">
                    <button type="button" x-on:click="section = 'rsvp'" class="btn-gold">Confirm Your Attendance</button>
                </div>
            </div>
        </section>
        @endif
    </div>
    @endif

            {{-- RSVP --}}
    <div x-show="section === 'rsvp'" x-cloak>
        <div style="text-align:center;padding:4rem 1.5rem;background:var(--color-obsidian);">
            <p style="font-family:var(--font-sans);font-size:0.75rem;font-weight:500;letter-spacing:0.15em;text-transform:uppercase;color:{{ $rsvpForm->branding['invited_label_color'] ?? '#E8D5A3' }};margin-bottom:0.5rem;">You're Invited</p>
            <h1 style="font-family:var(--font-serif);font-size:clamp(1.75rem,4vw,2.75rem);font-weight:300;color:#fff;margin-bottom:0.75rem;">{{ $rsvpForm->title }}</h1>
            <p style="color:rgba(255,255,255,0.55);font-size:0.875rem;letter-spacing:0.1em;text-transform:uppercase;">
                {{ $rsvpForm->event->date?->format('l, jS F Y') ?? 'Date TBC' }}
                @if($rsvpForm->event->venue) &middot; {{ $rsvpForm->event->venue }} @endif
            </p>
        </div>

        <div style="padding:6rem 1.5rem;background:var(--color-ivory);">
        <div style="max-width:32rem;margin:0 auto;">

            @if($submitted && $response)
            {{-- SUCCESS --}}
            @if($response->status === 'confirmed')
            <div style="text-align:center;margin-bottom:2rem;">
                <div style="width:56px;height:56px;background:color-mix(in srgb, var(--color-gold) 10%, transparent);border:1px solid color-mix(in srgb, var(--color-gold) 30%, transparent);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                    <svg style="width:28px;height:28px;color:var(--color-gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h2 class="section-title mb-3">We Can't Wait to See You!</h2>
                <p style="color:var(--color-muted);font-size:0.9375rem;line-height:1.8;">
                    Thank you, <strong>{{ $respondent_name }}</strong>! Your RSVP is confirmed.
                    @if($respondent_email)A copy has been sent to <strong>{{ $respondent_email }}</strong>.@endif
                </p>
                <div style="display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin-top:1.5rem;">
                    @if($settings?->gifts_enabled && $giftInfo)
                    <button type="button" x-on:click="section = 'gifts'" class="btn-gold" style="font-size:12px;padding:11px 20px;">Gift Us</button>
                    @endif
                    @if($settings?->wishes_enabled)
                    <button type="button" x-on:click="section = 'wishes'" class="btn-outline-gold" style="font-size:12px;padding:10px 20px;">Send a Wish</button>
                    @endif
                </div>
            </div>

            @if($response->qr_token)
            <div style="max-width:320px;margin:0 auto 2rem;border:1px solid var(--color-border);background:#fff;">
                <div style="background:var(--color-obsidian);padding:1rem 1.25rem;display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <p style="font-size:10px;letter-spacing:0.15em;text-transform:uppercase;color:rgba(255,255,255,0.5);margin:0 0 2px;">Entry Pass</p>
                        <p style="font-family:var(--font-serif);color:#fff;font-size:1rem;margin:0;">{{ $rsvpForm->title }}</p>
                    </div>
                </div>
                <div style="padding:1.25rem;">
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(240)->generate($response->qr_token) !!}
                </div>
                <div style="padding:0.625rem 1.25rem;background:#fafaf9;border-top:1px solid var(--color-border);">
                    <p style="font-size:11px;color:var(--color-muted);text-align:center;margin:0;">Present this QR code at the venue entrance for check-in</p>
                </div>
            </div>
            <div style="text-align:center;">
                <a href="{{ url('/rsvp/ticket/' . $response->qr_token) }}" class="btn-outline-gold">↓ Download Ticket</a>
            </div>
            @endif
            @else
            <div style="text-align:center;">
                <div style="width:56px;height:56px;background:#f9f9f9;border:1px solid var(--color-border);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                    <span style="font-size:1.5rem;">💛</span>
                </div>
                <h2 class="section-title mb-3">We'll Miss You</h2>
                <p style="color:var(--color-muted);font-size:0.9375rem;line-height:1.8;">
                    Thank you for letting us know, <strong>{{ $respondent_name }}</strong>. We'll miss having you there.
                </p>
            </div>
            @endif

            @else
            {{-- MULTI-STEP FORM --}}

            @if($error)
            <div style="padding:12px 16px;margin-bottom:20px;font-size:13px;color:#DC2626;background:#FEE2E2;border:1px solid #FECACA;">{{ $error }}</div>
            @endif

            <div style="display:flex;align-items:center;justify-content:center;margin-bottom:2rem;">
                @for($s = 1; $s <= $this->totalSteps(); $s++)
                <div style="display:flex;align-items:center;">
                    <div class="stepper-dot {{ $step > $s ? 'done' : ($step === $s ? 'active' : '') }}">
                        @if($step > $s)✓@else{{ $s }}@endif
                    </div>
                    @if($s < $this->totalSteps())<div style="height:1px;width:60px;background:{{ $step > $s ? 'var(--color-obsidian)' : 'var(--color-border)' }};"></div>@endif
                </div>
                @endfor
            </div>
            <div style="text-align:center;margin-bottom:2rem;">
                <p class="section-eyebrow">Step {{ $step }} of {{ $this->totalSteps() }}</p>
                <p style="font-size:0.875rem;color:var(--color-muted);margin-top:4px;">
                    @if($step === 1) Your Details
                    @elseif($step === 2) Attendance
                    @else Additional Information
                    @endif
                </p>
            </div>

            <div wire:key="rsvp-step-1" x-show="step === 1" x-cloak class="rsvp-step-fields" style="display:flex;flex-direction:column;gap:1.5rem;">
                @if($systemQuestions['name']['enabled'])
                <div>
                    <label class="form-label">{{ $systemQuestions['name']['label'] }} @if($systemQuestions['name']['required'])<span style="color:#EF4444;">*</span>@endif</label>
                    <input wire:model="respondent_name" type="text" placeholder="Your full name" class="form-input">
                    @error('respondent_name')<p style="color:#EF4444;font-size:11px;margin-top:4px;">{{ $message }}</p>@enderror
                </div>
                @endif
                @if($systemQuestions['email']['enabled'])
                <div>
                    <label class="form-label">{{ $systemQuestions['email']['label'] }} @if($systemQuestions['email']['required'])<span style="color:#EF4444;">*</span>@endif</label>
                    <input wire:model.blur="respondent_email" type="{{ $systemQuestions['email']['field_type'] ?: 'email' }}" placeholder="your@email.com" class="form-input">
                </div>
                @endif

                @if($emailExists)
                <div style="padding:1rem;border:1px solid color-mix(in srgb, var(--color-gold) 30%, transparent);background:color-mix(in srgb, var(--color-gold) 5%, transparent);">
                    <p style="font-size:0.875rem;margin-bottom:0.75rem;color:var(--color-obsidian);">
                        <strong>{{ $existingName }}</strong> has already submitted an RSVP with this email.
                    </p>
                    <a href="{{ $existingEditUrl }}" class="btn-gold" style="font-size:12px;padding:8px 16px;">Edit My Submission</a>
                </div>
                @endif

                <div>
                    <label class="form-label">Phone Number</label>
                    <input wire:model="respondent_phone" type="tel" placeholder="+234 800 000 0000" class="form-input">
                </div>
            </div>

            <div wire:key="rsvp-step-2" x-show="step === 2" x-cloak class="rsvp-step-attendance" style="display:flex;flex-direction:column;gap:1.5rem;" x-data="{ withSomeone: @js($comingWithSomeone), selectedStatus: @js($status) }">
                @if($systemQuestions['status']['enabled'])
                <div>
                    <label class="form-label" style="margin-bottom:12px;">{{ $systemQuestions['status']['label'] }} @if($systemQuestions['status']['required'])<span style="color:#EF4444;">*</span>@endif</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div x-on:click="selectedStatus = 'confirmed'; $wire.set('status', 'confirmed')" x-bind:style="selectedStatus === 'confirmed' ? 'cursor:pointer;border:2px solid var(--color-gold);background:color-mix(in srgb, var(--color-gold) 5%, transparent);padding:1.25rem;text-align:center;' : 'cursor:pointer;border:2px solid var(--color-border);background:#fff;padding:1.25rem;text-align:center;'">
                            <div style="font-size:1.5rem;margin-bottom:4px;">🎉</div>
                            <p style="font-size:0.875rem;font-weight:500;margin:0;">Yes, I'll be there!</p>
                        </div>
                        <div x-on:click="selectedStatus = 'declined'; $wire.set('status', 'declined')" x-bind:style="selectedStatus === 'declined' ? 'cursor:pointer;border:2px solid var(--color-gold);background:color-mix(in srgb, var(--color-gold) 5%, transparent);padding:1.25rem;text-align:center;' : 'cursor:pointer;border:2px solid var(--color-border);background:#fff;padding:1.25rem;text-align:center;'">
                            <div style="font-size:1.5rem;margin-bottom:4px;">😔</div>
                            <p style="font-size:0.875rem;font-weight:500;margin:0;">I can't make it</p>
                        </div>
                    </div>
                </div>
                @endif

                @if($systemQuestions['plus_one_count']['enabled'])
                <div x-show="selectedStatus === 'confirmed'" x-cloak style="margin-top:0.5rem;">
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                        <input type="checkbox" x-model="withSomeone" wire:model="comingWithSomeone" style="width:16px;height:16px;accent-color:var(--color-gold);">
                        <span style="font-size:0.875rem;">Are you coming with someone?</span>
                    </label>

                    <div x-show="withSomeone" x-cloak style="margin-top:1rem;padding-left:1.5rem;border-left:2px solid color-mix(in srgb, var(--color-gold) 30%, transparent);">
                        <p style="font-size:0.75rem;color:var(--color-muted);margin-bottom:0.75rem;">You can add up to 5 people.</p>

                        @foreach($companions as $i => $c)
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:#fff;border:1px solid var(--color-border);margin-bottom:8px;">
                            <span style="font-size:0.8125rem;">{{ $c['name'] }}{{ $c['relation'] ? ' — ' . $c['relation'] : '' }}</span>
                            <button type="button" wire:click="removeCompanion({{ $i }})" style="background:none;border:none;color:#DC2626;cursor:pointer;font-size:12px;">✕</button>
                        </div>
                        @endforeach

                        @if(count($companions) < 5)
                        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
                            <input wire:model="newCompanionName" type="text" placeholder="Full name" class="form-input" style="max-width:180px;">
                            <input wire:model="newCompanionRelation" type="text" placeholder="Relation (e.g. Sister)" class="form-input" style="max-width:180px;">
                            <button type="button" wire:click="addCompanion" class="btn-outline-gold" style="font-size:11px;padding:10px 16px;">+ Add</button>
                        </div>
                        <p style="font-size:0.6875rem;color:var(--color-muted);margin-top:6px;">{{ 5 - count($companions) }} more can be added.</p>
                        @else
                        <p style="font-size:0.75rem;color:var(--color-gold);margin-top:6px;">You've reached the maximum of 5 guests.</p>
                        @endif
                    </div>
                </div>
                @endif

                <div x-show="selectedStatus === 'declined'" x-cloak>
                    <label class="form-label">Reason (optional)</label>
                    <textarea wire:model="decline_reason" rows="3" placeholder="Let us know if you'd like..." class="form-input"></textarea>
                </div>
            </div>

            <div wire:key="rsvp-step-3" x-show="step === 3" x-cloak style="display:flex;flex-direction:column;gap:1.25rem;">
                @foreach($rsvpForm->customQuestions as $q)
                <div>
                    <label class="form-label">{{ $q->label }} @if($q->is_required)<span style="color:#EF4444;">*</span>@endif</label>
                    @if(in_array($q->field_type, ['text','email','phone','number','date']))
                    <input wire:model="answers.{{ $q->id }}" type="{{ $q->field_type === 'phone' ? 'tel' : ($q->field_type === 'number' ? 'number' : ($q->field_type === 'date' ? 'date' : 'text')) }}" class="form-input">
                    @elseif($q->field_type === 'textarea')
                    <textarea wire:model="answers.{{ $q->id }}" class="form-input" rows="3"></textarea>
                    @elseif($q->field_type === 'dropdown')
                    <select wire:model="answers.{{ $q->id }}" class="form-input">
                        <option value="">Select an option</option>
                        @foreach($q->options ?? [] as $opt)<option value="{{ $opt }}">{{ $opt }}</option>@endforeach
                    </select>
                    @elseif(in_array($q->field_type, ['radio','checkbox']))
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        @foreach($q->options ?? [] as $opt)
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--color-muted);">
                            <input wire:model="answers.{{ $q->id }}" type="{{ $q->field_type }}" value="{{ $opt }}" style="accent-color:var(--color-gold);"> {{ $opt }}
                        </label>
                        @endforeach
                    </div>
                    @endif
                    @error("answers.{$q->id}")<p style="color:#EF4444;font-size:11px;margin-top:4px;">{{ $message }}</p>@enderror
                </div>
                @endforeach
            </div>

            <div wire:key="rsvp-navigation" style="display:flex;justify-content:space-between;margin-top:2.5rem;max-width:32rem;margin-left:auto;margin-right:auto;">
                @if($step > 1)
                <button type="button" wire:click="prevStep" wire:loading.attr="disabled" wire:loading.class="is-loading" wire:target="prevStep" class="btn-outline-gold rsvp-nav-btn" style="font-size:12px;padding:12px 24px;">
                    <span wire:loading.remove wire:target="prevStep">← Back</span>
                    <span wire:loading wire:target="prevStep">...</span>
                </button>
                @else
                <div></div>
                @endif

                @if($step < $this->totalSteps())
                <button type="button" wire:click="nextStep" wire:loading.attr="disabled" wire:loading.class="is-loading" wire:target="nextStep" class="btn-gold rsvp-nav-btn" style="font-size:12px;padding:12px 32px;">
                    <span wire:loading.remove wire:target="nextStep">Next →</span>
                    <span wire:loading wire:target="nextStep">...</span>
                </button>
                @else
                <button type="button" wire:click="submitRsvp" wire:loading.attr="disabled" wire:loading.class="is-loading" wire:target="submitRsvp" x-on:click="$el.classList.add('is-loading'); $el.setAttribute('disabled', 'disabled')" class="btn-gold rsvp-submit-btn" style="font-size:12px;padding:12px 32px;">
                    <span wire:loading.remove wire:target="submitRsvp">{{ $editing ? 'Save RSVP Changes' : 'Submit RSVP' }}</span>
                    <span wire:loading wire:target="submitRsvp">Processing...</span>
                </button>
                @endif
            </div>
            @endif
            
        </div>
        </div>
    </div>

    {{-- WISHES --}}
    @if($settings?->wishes_enabled)
    <div x-show="section === 'wishes'" x-cloak>
        <div style="text-align:center;padding:4rem 1.5rem;background:var(--color-obsidian);">
            <p class="section-eyebrow mb-2">Share Your Love</p>
            <h1 style="font-family:var(--font-serif);font-size:clamp(1.75rem,4vw,2.75rem);font-weight:300;color:#fff;margin-bottom:0.75rem;">Wishes &amp; Blessings</h1>
            <p style="color:rgba(255,255,255,0.5);font-size:0.9375rem;max-width:440px;margin:0 auto;line-height:1.8;">Share a message, blessing, or memory as we begin this journey.</p>
        </div>
        <div style="padding:5rem 1.5rem;background:var(--color-ivory);">
        <div style="max-width:32rem;margin:0 auto;">

            @if($wishSubmitted)
            <div x-data x-init="setTimeout(() => $wire.resetWishSubmission(), 4000)" style="text-align:center;padding:3rem 0;border:1px solid color-mix(in srgb, var(--color-gold) 30%, transparent);background:color-mix(in srgb, var(--color-gold) 5%, transparent);margin-bottom:3rem;">
                <div style="font-size:1.75rem;margin-bottom:0.75rem;">💛</div>
                <h3 style="font-family:var(--font-serif);font-size:1.25rem;margin-bottom:0.5rem;">Your wish has been sent!</h3>
                <p style="color:var(--color-muted);font-size:0.9375rem;">
                    {{ $settings->wishes_require_approval ? "It'll appear here once approved." : "It's live now." }}
                </p>
            </div>
            @else
            <div style="margin-bottom:3.5rem;">
                <label class="form-label">Your Name *</label>
                <input wire:model="wishName" type="text" placeholder="e.g. Amara Okonkwo" class="form-input" style="margin-bottom:1rem;">
                <label class="form-label">Your Email (optional)</label>
                <input wire:model="wishEmail" type="email" placeholder="your@email.com" class="form-input" style="margin-bottom:1rem;">
                <label class="form-label">Your Message *</label>
                <textarea wire:model="wishMessage" rows="4" placeholder="Write your wish, prayer, or message here..." class="form-input" style="margin-bottom:1rem;"></textarea>
                <button wire:click="submitWish" wire:loading.attr="disabled" class="btn-gold" style="width:100%;">
                    <span wire:loading.remove wire:target="submitWish">Send My Wish 💛</span>
                    <span wire:loading wire:target="submitWish">Sending...</span>
                </button>
            </div>
            @endif

            @if($wishes->isNotEmpty())
            <div class="gold-divider mb-10"><span style="font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--color-muted);white-space:nowrap;padding:0 12px;">Wishes from loved ones</span></div>
            <div style="display:flex;flex-direction:column;gap:1.25rem;">
                @foreach($wishes as $wish)
                <div style="border:1px solid var(--color-border);background:#fff;padding:1.5rem;" x-data="{
                    heart: { count: {{ $wish->heart_reactions_count }}, active: {{ in_array('heart', $myWishReactions[$wish->id] ?? []) ? 'true' : 'false' }} },
                    congrats: { count: {{ $wish->congrats_reactions_count }}, active: {{ in_array('congrats', $myWishReactions[$wish->id] ?? []) ? 'true' : 'false' }} }
                }">
                    <p style="font-size:0.9375rem;line-height:1.8;color:#3a3a3a;margin-bottom:0.75rem;">"{{ $wish->message }}"</p>
                    <p style="font-size:0.875rem;font-weight:500;color:var(--color-gold);">— {{ $wish->guest_name }}</p>
                    <div style="display:flex;gap:8px;margin-top:1rem;">
                        <button type="button" x-on:click="heart.active = !heart.active; heart.count += heart.active ? 1 : -1; $wire.toggleWishReaction({{ $wish->id }}, 'heart')" x-bind:aria-pressed="heart.active" style="border:1px solid var(--color-border);background:transparent;padding:7px 10px;cursor:pointer;color:var(--color-gold);font-size:12px;">
                            <span>♥</span> <span x-text="heart.count"></span>
                        </button>
                        <button type="button" x-on:click="congrats.active = !congrats.active; congrats.count += congrats.active ? 1 : -1; $wire.toggleWishReaction({{ $wish->id }}, 'congrats')" x-bind:aria-pressed="congrats.active" style="border:1px solid var(--color-border);background:transparent;padding:7px 10px;cursor:pointer;color:var(--color-gold);font-size:12px;">
                            <span>Congratulations</span> <span x-text="congrats.count"></span>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p style="text-align:center;color:var(--color-muted);font-size:0.875rem;">No wishes yet. Be the first! 💛</p>
            @endif
        </div>
        </div>
    </div>
    @endif

    {{-- GALLERY --}}
    @if($settings?->gallery_enabled)
    <div x-show="section === 'gallery'" x-cloak class="ms-page"
        x-data="{
            lightboxOpen: false, current: 0, zoom: 1,
            imgs: @js($images->map(fn($i) => ['url' => $i->url(), 'caption' => $i->caption])->values()),
            open(i) { this.current = i; this.lightboxOpen = true; this.zoom = 1; },
            next() { this.current = (this.current + 1) % this.imgs.length; this.zoom = 1; },
            prev() { this.current = (this.current - 1 + this.imgs.length) % this.imgs.length; this.zoom = 1; }
        }"
        x-on:keydown.escape.window="lightboxOpen = false"
        x-on:keydown.arrow-right.window="if (lightboxOpen) next()"
        x-on:keydown.arrow-left.window="if (lightboxOpen) prev()">

        <div class="ms-page-hero">
            <p class="section-eyebrow mb-2">Our Moments</p>
            <h1 style="font-family:var(--font-serif);font-size:clamp(1.75rem,4vw,2.75rem);font-weight:300;color:#fff;">Photo Gallery</h1>
        </div>
        <div style="max-width:64rem;margin:0 auto;padding-top:4rem;">

            @if($settings->gallery_password_protected && !$this->galleryUnlocked())
            <div style="max-width:24rem;margin:0 auto;text-align:center;padding:2rem 1.5rem;">
                <div style="width:56px;height:56px;border:1px solid var(--color-border);display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;">
                    <svg style="width:24px;height:24px;color:var(--color-gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h2 style="font-family:var(--font-serif);font-size:1.375rem;margin-bottom:0.75rem;">Gallery is Private</h2>
                <p style="color:var(--color-muted);font-size:0.9375rem;margin-bottom:2rem;">Enter the password to view our photos.</p>

                <input wire:model="galleryPasswordInput" type="password" placeholder="Enter gallery password" class="form-input" style="margin-bottom:8px;">
                @if($galleryPasswordError)<p style="color:#EF4444;font-size:12px;margin-bottom:12px;">{{ $galleryPasswordError }}</p>@endif
                <button wire:click="unlockGallery" class="btn-gold" style="width:100%;">Unlock Gallery</button>

                @if($settings->gallery_password_whatsapp)
                <div style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid var(--color-border);">
                    <p style="font-size:12px;color:var(--color-muted);margin-bottom:6px;">Don't have the password?</p>
                    <a href="{{ $settings->gallery_password_whatsapp }}" target="_blank" style="font-size:13px;color:var(--color-gold);">Request Password on WhatsApp</a>
                </div>
                @endif
            </div>
            @elseif($images->isNotEmpty())
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                @foreach($images as $i => $img)
                <div style="aspect-ratio:1;overflow:hidden;cursor:pointer;" x-on:click="open({{ $i }})">
                    <img src="{{ $img->url() }}" loading="lazy" alt="{{ $img->caption }}" style="width:100%;height:100%;object-fit:cover;transition:transform 300ms;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                </div>
                @endforeach
            </div>
            @else
            <p style="text-align:center;color:var(--color-muted);font-size:0.875rem;">Gallery photos will be added soon.</p>
            @endif
        </div>

        {{-- Lightbox --}}
        <div x-show="lightboxOpen" x-cloak x-transition.opacity style="position:fixed;inset:0;z-index:9000;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.93);" x-on:click.self="lightboxOpen = false">
            <div style="position:absolute;top:0;left:0;right:0;display:flex;justify-content:flex-end;padding:12px 16px;gap:8px;">
                <span style="color:rgba(255,255,255,0.6);font-size:13px;padding:4px 12px;background:rgba(0,0,0,0.3);"><span x-text="current+1"></span> / <span x-text="imgs.length"></span></span>
                <button type="button" x-on:click="lightboxOpen = false" style="color:rgba(255,255,255,0.7);background:none;border:none;cursor:pointer;padding:8px;">
                    <svg style="width:22px;height:22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <button type="button" x-on:click="prev()" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,0.7);background:none;border:none;cursor:pointer;padding:12px;">
                <svg style="width:32px;height:32px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <div class="ms-lightbox-content">
                <img :src="imgs[current]?.url" alt="">
            </div>
            <button type="button" x-on:click="next()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,0.7);background:none;border:none;cursor:pointer;padding:12px;">
                <svg style="width:32px;height:32px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>
    @endif


    {{-- GIFTS --}}
    @if($settings?->gifts_enabled && $giftInfo)
    <div x-show="section === 'gifts'" x-cloak class="ms-page"
        x-data="{
            copied: false,
            copy(text) {
                navigator.clipboard.writeText(text);
                this.copied = true;
                setTimeout(() => this.copied = false, 1600);
            }
        }">
        <div class="ms-page-hero">
            <p class="section-eyebrow mb-2">Bless the Couple</p>
            <h1 style="font-family:var(--font-serif);font-size:clamp(1.75rem,4vw,2.75rem);font-weight:300;color:#fff;margin-bottom:0.75rem;">Gift Us</h1>
            @if($giftInfo->note)<p style="color:rgba(255,255,255,0.55);font-size:0.9375rem;max-width:480px;margin:0 auto;line-height:1.8;">{{ $giftInfo->note }}</p>@endif
        </div>
        <div style="max-width:34rem;margin:0 auto;padding-top:4rem;">

            @if(!empty($giftInfo->bank_accounts))
            <div style="margin-bottom:3rem;">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:1.5rem;">
                    <div style="width:36px;height:36px;background:color-mix(in srgb, var(--color-gold) 10%, transparent);border:1px solid color-mix(in srgb, var(--color-gold) 30%, transparent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg style="width:16px;height:16px;color:var(--color-gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <div>
                        <h3 style="font-family:var(--font-serif);font-size:1.125rem;margin:0;">Bank Transfer</h3>
                        <p style="color:var(--color-muted);font-size:0.875rem;margin:0;">Copy account number and transfer directly</p>
                    </div>
                </div>

                @foreach(collect($giftInfo->bank_accounts)->groupBy(fn($a) => $a['currency'] ?? 'NGN') as $currency => $accounts)
                <p style="font-size:0.6875rem;font-weight:600;letter-spacing:0.15em;text-transform:uppercase;color:var(--color-gold);display:flex;align-items:center;gap:12px;margin-bottom:0.75rem;">
                    {{ $currency }}
                    <span style="flex:1;height:1px;background:var(--color-border);"></span>
                </p>
                <div style="display:flex;flex-direction:column;gap:1rem;margin-bottom:1.5rem;">
                    @foreach($accounts as $acc)
                    <div style="background:#fff;border:1px solid var(--color-border);padding:1.5rem;display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;">
                        <div>
                            <p style="font-size:0.875rem;font-weight:500;margin-bottom:2px;">{{ $acc['bank_name'] }}</p>
                            <p style="color:var(--color-muted);font-size:0.875rem;">{{ $acc['account_name'] }}</p>
                            <p style="font-family:var(--font-serif);font-size:1.5rem;letter-spacing:0.04em;color:var(--color-obsidian);margin-top:0.5rem;">{{ $acc['account_number'] }}</p>
                            @if(!empty($acc['swift_code']))<p style="color:var(--color-muted);font-size:0.75rem;margin-top:0.35rem;">SWIFT / BIC: {{ $acc['swift_code'] }}</p>@endif
                        </div>
                        <button type="button" x-on:click="copy('{{ $acc['account_number'] }}')" style="border:1px solid var(--color-border);padding:10px;background:none;cursor:pointer;flex-shrink:0;">
                            <svg style="width:16px;height:16px;color:var(--color-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </button>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
            @endif

            @if(!empty($giftInfo->registry_links))
            <div>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:1.5rem;">
                    <div style="width:36px;height:36px;background:color-mix(in srgb, var(--color-gold) 10%, transparent);border:1px solid color-mix(in srgb, var(--color-gold) 30%, transparent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg style="width:16px;height:16px;color:var(--color-gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </div>
                    <div>
                        <h3 style="font-family:var(--font-serif);font-size:1.125rem;margin:0;">Send Online</h3>
                        <p style="color:var(--color-muted);font-size:0.875rem;margin:0;">Quick one-tap payment links</p>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:1rem;">
                    @foreach($giftInfo->registry_links as $link)
                    <div style="background:#fff;border:1px solid var(--color-border);padding:1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                        <div>
                            @if(!empty($link['currencies']))
                            <span style="font-size:11px;padding:2px 8px;border:1px solid var(--color-gold);color:var(--color-gold);letter-spacing:0.05em;margin-bottom:4px;display:inline-block;">{{ implode(', ', $link['currencies']) }}</span>
                            @endif
                            <p style="font-size:0.875rem;font-weight:500;margin:0;">{{ $link['label'] }}</p>
                        </div>
                        <a href="{{ $link['url'] }}" target="_blank" class="btn-gold" style="font-size:12px;padding:10px 20px;">Send Gift</a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div x-show="copied" x-cloak style="position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:var(--color-obsidian);color:#fff;padding:10px 20px;font-size:13px;">Account number copied!</div>
        </div>
    </div>
    @endif

    @php
        $__tenant = \App\Models\Central\Tenant::find($rsvpForm->tenant_id);
        $__whiteLabel = app(\App\Services\FeatureGateService::class)->canAccess($__tenant, 'white_label');
    @endphp
    <footer style="background:var(--color-obsidian);padding:3rem 1.5rem 2rem;text-align:center;">
        <h3 style="font-family:var(--font-serif);font-size:1.375rem;font-weight:300;color:var(--color-gold-light);margin-bottom:0.5rem;">{{ $rsvpForm->title }}</h3>
        <p style="color:rgba(255,255,255,0.5);font-size:0.8125rem;margin-bottom:1.5rem;">
            {{ $rsvpForm->event->date?->format('l, jS F Y') ?? '' }}
            @if($rsvpForm->event->venue) &middot; {{ $rsvpForm->event->venue }} @endif
        </p>
        <div style="height:1px;background:rgba(255,255,255,0.08);max-width:200px;margin:0 auto 1.5rem;"></div>
        @if(!$__whiteLabel)
        <p style="color:rgba(255,255,255,0.3);font-size:0.75rem;">Website created with <a href="/" style="color:rgba(255,255,255,0.45);">Koordli</a></p>
        @endif
    </footer>
</div>
</div>