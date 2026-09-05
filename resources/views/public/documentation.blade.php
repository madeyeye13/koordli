<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@php
    $docsFaviconPath = \App\Models\Central\PlatformSetting::get('site_favicon');
    $docsFaviconVersion = $docsFaviconPath ? \Illuminate\Support\Facades\Storage::disk('public')->lastModified($docsFaviconPath) : null;
@endphp
@include('partials.favicon')
<title>Documentation — Koordli</title>
<script src="//unpkg.com/alpinejs" defer></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@40..144,400..700&display=swap" rel="stylesheet">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    html { scroll-behavior:smooth; }
    body { font-family: 'Satoshi', -apple-system, sans-serif; background:#F8F7F4; color:#1C1917; line-height:1.65; font-size:14px; }
    a { color:inherit; text-decoration:none; }
    code { font-family:'SF Mono',Consolas,monospace; font-size:0.87em; background:#F5F4F3; color:#7C3AED; padding:2px 6px; border-radius:5px; }

    * { scrollbar-width: thin; scrollbar-color: #E2DFDC transparent; }
    *::-webkit-scrollbar { width: 7px; height: 7px; }
    *::-webkit-scrollbar-track { background: transparent; }
    *::-webkit-scrollbar-thumb { background: #E2DFDC; border-radius: 10px; }
    *::-webkit-scrollbar-thumb:hover { background: #C7C2BD; }

    /* Top bar — matches the reference exactly: logo+tag, search, utility links */
    .docs-header { height:68px; border-bottom:1px solid #E8E2DC; display:flex; align-items:center; padding:0 30px; gap:28px; position:sticky; top:0; z-index:30; background:rgba(255,255,255,0.92); backdrop-filter:blur(14px); }
    .docs-logo { font-family:'Fraunces',serif; font-weight:600; font-size:19px; display:flex; align-items:center; gap:9px; flex-shrink:0; letter-spacing:-0.02em; }
    .docs-logo-mark { width:24px; height:24px; object-fit:contain; border-radius:6px; }
    .docs-version { font-family:'Satoshi',sans-serif; font-size:10px; font-weight:700; letter-spacing:0.05em; text-transform:uppercase; color:#7C3AED; background:#F1EEFF; border:1px solid #DED6FF; border-radius:999px; padding:3px 9px; }
    .docs-search-wrap { position:relative; }
    .docs-search { width:220px; padding:7px 10px 7px 32px; border:1px solid #EEECEA; border-radius:8px; font-size:12.5px; background:#FAFAF9; outline:none; transition:all 120ms; color:#78716C; }
    .docs-search:focus { border-color:#C4B5FD; background:#fff; width:280px; }
    .docs-search-icon { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#A8A29E; pointer-events:none; }
    .docs-kbd { position:absolute; right:8px; top:50%; transform:translateY(-50%); font-size:10px; color:#A8A29E; background:#fff; border:1px solid #EEECEA; border-radius:4px; padding:1px 5px; }
    .docs-header-nav { display:flex; align-items:center; gap:22px; margin-left:auto; }
    .docs-header-link { font-size:13px; color:#57534E; font-weight:500; }
    .docs-header-link:hover { color:#1C1917; }
    .docs-header-cta { font-size:13px; font-weight:600; color:#7C3AED; display:flex; align-items:center; gap:5px; }

    .docs-wrap { display:flex; max-width:1440px; margin:0 auto; }

    /* Sidebar — icon top-level items, then plain grouped links, NO filled
       active background: bold text + thin left rule only, per feedback */
    .docs-nav { width:248px; flex-shrink:0; padding:28px 18px 60px; position:sticky; top:68px; height:calc(100vh - 68px); overflow-y:auto; }
    .docs-nav-top { display:flex; flex-direction:column; gap:1px; margin-bottom:18px; padding-bottom:16px; border-bottom:1px solid #F0EEEC; }
    .docs-nav-top-item { display:flex; align-items:center; gap:9px; padding:6px 8px; font-size:13px; color:#57534E; border-radius:6px; }
    .docs-nav-top-item:hover { background:#FAFAF9; color:#1C1917; }
    .docs-nav-top-item svg { color:#A8A29E; flex-shrink:0; }

    .docs-nav-group-label { font-size:11px; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; color:#B5B0AC; margin:20px 0 6px; padding:0 8px; }
    .docs-nav-group-label:first-of-type { margin-top:2px; }
    .docs-nav-item {
        display:block; padding:5px 8px 5px 12px; font-size:13px; color:#57534E; cursor:pointer;
        border-left:2px solid transparent; transition:color 100ms, border-color 100ms; margin-left:-1px;
    }
    .docs-nav-item:hover { color:#1C1917; }
    .docs-nav-item.active { color:#1C1917; font-weight:600; border-left-color:#7C3AED; }

    /* Content */
    .docs-content { flex:1; min-width:0; padding:30px 42px 100px 54px; max-width:900px; border-left:1px solid #E8E2DC; }
    .docs-section { display:none; }
    .docs-section.active { display:block; animation: docsFadeIn 180ms ease; }
    @keyframes docsFadeIn { from { opacity:0; transform:translateY(4px); } to { opacity:1; transform:translateY(0); } }

    .docs-eyebrow { font-size:11.5px; font-weight:700; letter-spacing:0.05em; text-transform:uppercase; color:#7C3AED; margin-bottom:10px; }
    .docs-section h1 { font-family:'Fraunces',serif; font-size:39px; line-height:1.08; font-weight:600; margin-bottom:12px; letter-spacing:-0.025em; }
    .docs-lede { font-size:15.5px; color:#57534E; margin-bottom:8px; max-width:640px; line-height:1.7; }
    .docs-section h2 { font-size:19px; font-weight:700; margin:40px 0 14px; letter-spacing:-0.01em; padding-top:4px; }
    .docs-section h2:first-of-type { margin-top:38px; }
    .docs-section p { font-size:14px; color:#3F3C3A; margin-bottom:14px; line-height:1.75; }
    .docs-section ul { font-size:14px; color:#3F3C3A; margin:0 0 18px 4px; list-style:none; }
    .docs-section ul li { position:relative; padding-left:20px; margin-bottom:9px; line-height:1.7; }
    .docs-section ul li::before { content:''; position:absolute; left:2px; top:9px; width:5px; height:5px; border-radius:50%; background:#C4B5FD; }

    .docs-callout { display:flex; gap:12px; padding:14px 16px; border-radius:10px; margin:18px 0; font-size:13px; line-height:1.6; border:1px solid; }
    .docs-callout.note { background:#F8F7FF; border-color:#E9E3FE; color:#4C1D95; }
    .docs-callout.warn { background:#FFFCF5; border-color:#FBE8B8; color:#78350F; }
    .docs-callout-icon { flex-shrink:0; margin-top:1px; }

    /* Code blocks */
    .docs-code-block { background:#171416; border-radius:10px; overflow:hidden; margin:16px 0; border:1px solid #2A2529; }
    .docs-code-bar { display:flex; align-items:center; justify-content:space-between; padding:9px 14px; background:#1D191C; border-bottom:1px solid #2A2529; }
    .docs-code-label { font-size:11px; color:#9C9793; font-family:'SF Mono',monospace; }
    .docs-copy-btn { display:flex; align-items:center; gap:5px; background:none; border:none; color:#8C8783; font-size:11px; cursor:pointer; padding:3px 7px; border-radius:5px; transition:background 100ms, color 100ms; font-family:inherit; }
    .docs-copy-btn:hover { background:#2A2529; color:#fff; }
    .docs-code-pre { padding:16px; overflow-x:auto; }
    .docs-code-pre code { background:none; padding:0; color:#E5E1DD; font-size:12.5px; line-height:1.75; white-space:pre; }

    /* Numbered steps — bracketed numbers + a real connecting vertical
       line running through them, matching the reference exactly */
    .docs-steps { position:relative; margin:22px 0 8px; }
    .docs-steps::before { content:''; position:absolute; left:13px; top:12px; bottom:12px; width:1px; background:#EEECEA; }
    .docs-step { position:relative; display:flex; gap:18px; padding-bottom:28px; }
    .docs-step:last-child { padding-bottom:0; }
    .docs-step-num {
        position:relative; z-index:1; width:27px; height:27px; flex-shrink:0;
        border:1.5px solid #E2DFDC; border-radius:7px; background:#fff;
        color:#A8A29E; font-size:11.5px; font-weight:700; font-family:'SF Mono',monospace;
        display:flex; align-items:center; justify-content:center;
    }
    .docs-step-body { flex:1; min-width:0; padding-top:2px; }
    .docs-step-title { font-size:14.5px; font-weight:700; margin-bottom:5px; }
    .docs-step-desc { font-size:13px; color:#78716C; line-height:1.65; }

    .docs-table { width:100%; border-collapse:collapse; font-size:13px; margin:18px 0; border:1px solid #EEECEA; border-radius:9px; overflow:hidden; }
    .docs-table th { text-align:left; background:#FAFAF9; padding:10px 14px; font-weight:600; color:#57534E; border-bottom:1px solid #EEECEA; }
    .docs-table td { padding:10px 14px; border-bottom:1px solid #F5F5F4; color:#3F3C3A; }
    .docs-table tr:last-child td { border-bottom:none; }

    .docs-stuck { display:flex; align-items:center; gap:16px; border:1px solid #EEECEA; border-radius:12px; padding:18px 20px; margin-top:36px; flex-wrap:wrap; }
    .docs-stuck-icon { width:30px; height:30px; border-radius:50%; background:#F5F4F3; display:flex; align-items:center; justify-content:center; flex-shrink:0; color:#78716C; }
    .docs-stuck-text { flex:1; min-width:200px; }
    .docs-stuck-text strong { font-size:13.5px; }
    .docs-stuck-text span { font-size:13px; color:#78716C; }
    .docs-stuck-btn { background:#F5F4F3; border:1px solid #EEECEA; border-radius:8px; padding:9px 16px; font-size:12.5px; font-weight:600; color:#1C1917; cursor:pointer; white-space:nowrap; font-family:inherit; }
    .docs-stuck-btn:hover { background:#EEECEA; }

    .docs-mobile-toggle { display:none; }
    @media (max-width: 900px) {
        .docs-wrap { flex-direction:column; }
        .docs-nav { width:100%; height:auto; position:static; display:none; padding:16px; border-bottom:1px solid #EEECEA; }
        .docs-nav.open { display:block; }
        .docs-mobile-toggle { display:flex; align-items:center; gap:6px; background:#F5F4F3; border:none; border-radius:7px; padding:8px 14px; font-size:12.5px; margin:12px 20px 0; cursor:pointer; }
        .docs-content { padding:24px 20px 80px; max-width:100%; border-left:none; }
        .docs-rail { width:100%; padding:0 20px 60px; }
        .docs-rail-card { position:static; }
        .docs-intro { margin-bottom:24px; }
        .docs-section h1 { font-size:34px; }
        .docs-search-wrap, .docs-header-nav { display:none; }
    }

        /* Feature UI preview — same visual language as the landing page's
       dashboard mockups, so documentation feels like a natural extension
       of the real product rather than a separate illustration style. */
    .docs-mockup-wrap { position:relative; margin:22px 0 8px; }
    .docs-mockup-wrap::before {
        content:''; position:absolute; inset:-14px; z-index:0;
        background:radial-gradient(ellipse at 30% 20%, #F5F3FF, transparent 65%);
        opacity:0.9;
    }
    .docs-mockup { position:relative; z-index:1; background:#fff; border-radius:12px; border:1px solid #EEECEA; overflow:hidden; box-shadow:0 20px 44px -20px rgba(28,25,23,0.16); }
    .docs-mockup-bar { background:#1C1917; padding:9px 14px; display:flex; align-items:center; gap:6px; }
    .docs-mockup-dot { width:8px; height:8px; border-radius:50%; }
    .docs-mockup-title { color:#fff; font-size:11px; font-weight:600; margin-left:6px; }
    .docs-mockup-body { padding:18px; }
    .docs-mockup-caption { display:flex; align-items:center; gap:6px; font-size:11.5px; color:#A8A29E; margin-top:10px; }
    .docs-mockup-caption svg { color:#C4B5FD; flex-shrink:0; }

    .docs-intro { position:relative; overflow:hidden; margin:0 0 34px; padding:25px 26px 22px; border:1px solid #E4DBD2; border-radius:16px; background:linear-gradient(135deg,#211A2C 0%,#302148 62%,#563B31 100%); color:#fff; box-shadow:0 18px 38px -26px rgba(28,25,23,0.55); }
    .docs-intro::after { content:'K'; position:absolute; right:22px; bottom:-45px; font-family:'Fraunces',serif; font-size:190px; line-height:1; font-weight:600; color:rgba(255,255,255,0.07); pointer-events:none; }
    .docs-intro-kicker { position:relative; z-index:1; font-size:10px; text-transform:uppercase; letter-spacing:0.12em; font-weight:700; color:#FCD34D; margin-bottom:7px; }
    .docs-intro h2 { position:relative; z-index:1; font-family:'Fraunces',serif; font-size:28px; line-height:1.15; font-weight:500; margin:0 0 8px; max-width:460px; }
    .docs-intro p { position:relative; z-index:1; color:rgba(255,255,255,0.72); font-size:13px; line-height:1.65; max-width:500px; margin:0; }
    .docs-intro-stats { position:relative; z-index:1; display:flex; gap:8px; flex-wrap:wrap; margin-top:20px; }
    .docs-intro-stat { min-width:104px; padding:9px 11px; border:1px solid rgba(255,255,255,0.14); border-radius:9px; background:rgba(255,255,255,0.08); }
    .docs-intro-stat strong { display:block; font-family:'Fraunces',serif; font-size:20px; font-weight:600; color:#fff; line-height:1.1; }
    .docs-intro-stat span { display:block; margin-top:3px; color:rgba(255,255,255,0.58); font-size:10px; }
    .docs-rail { width:248px; flex-shrink:0; padding:30px 24px 60px 16px; }
    .docs-rail-card { position:sticky; top:98px; padding:18px; border:1px solid #E4DBD2; border-radius:14px; background:#fff; box-shadow:0 14px 32px -26px rgba(28,25,23,0.35); }
    .docs-rail-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:#A8A29E; margin-bottom:11px; }
    .docs-rail-title { font-family:'Fraunces',serif; font-size:19px; line-height:1.2; margin-bottom:14px; }
    .docs-rail-link { display:flex; align-items:center; gap:8px; width:100%; padding:7px 0; border:0; border-bottom:1px solid #F2EFEC; background:none; color:#57534E; cursor:pointer; font:500 12px 'Satoshi',sans-serif; text-align:left; }
    .docs-rail-link:last-child { border-bottom:0; }
    .docs-rail-link:hover { color:#7C3AED; }
    .docs-rail-link span { display:flex; align-items:center; justify-content:center; width:20px; height:20px; border-radius:6px; background:#F5F3FF; color:#7C3AED; font-size:10px; font-weight:700; }
    .docs-rail-note { margin-top:18px; padding-top:15px; border-top:1px solid #EEECEA; color:#78716C; font-size:11px; line-height:1.6; }
    .docs-rail-note a { color:#7C3AED; font-weight:600; }
    .docs-section > h2::before { content:''; display:inline-block; width:18px; height:2px; margin:0 8px 5px 0; background:#F59E0B; }
</style>
</head>
<body x-data="{
    section: 'getting-started',
    navOpen: false,
    q: '',
    copied: null,
    copy(text, id) {
        navigator.clipboard.writeText(text);
        this.copied = id;
        setTimeout(() => { if (this.copied === id) this.copied = null; }, 1600);
    },
    go(s) { this.section = s; this.navOpen = false; window.scrollTo(0,0); },
    match(text) { return text.toLowerCase().includes(this.q.toLowerCase()); }
}">

    <div class="docs-header">
        <a href="{{ route('landing') }}" class="docs-logo">
            @if($docsFaviconPath)
            <img class="docs-logo-mark" src="{{ Storage::disk('public')->url($docsFaviconPath) }}?v={{ $docsFaviconVersion }}" alt="Koordli logo">
            @else
            <span class="docs-logo-mark" style="display:block;background:#7C3AED;transform:rotate(24deg);"></span>
            @endif
            Koordli <span class="docs-version">Docs</span>
        </a>
        <div class="docs-search-wrap">
            <svg class="docs-search-icon" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input class="docs-search" type="text" placeholder="Search docs..." x-model="q">
            <span class="docs-kbd" x-show="!q">/</span>
        </div>
        <nav class="docs-header-nav">
            <a href="{{ route('landing') }}#pricing" class="docs-header-link">Pricing</a>
            <a href="/" class="docs-header-cta">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to Koordli
            </a>
        </nav>
    </div>

    <button class="docs-mobile-toggle" x-on:click="navOpen = !navOpen">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        <span x-text="navOpen ? 'Close' : 'Browse Topics'"></span>
    </button>

    <div class="docs-wrap">
        <nav class="docs-nav" x-bind:class="navOpen && 'open'">

            <div class="docs-nav-top">
                <a href="{{ route('landing') }}" class="docs-nav-top-item">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    Product Overview
                </a>
                <a href="{{ route('landing') }}#pricing" class="docs-nav-top-item">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                    Pricing
                </a>
                
            </div>

            <div class="docs-nav-group-label">Getting Started</div>
            <a class="docs-nav-item" x-show="match('registration industry profile getting started')" x-on:click="go('getting-started')" x-bind:class="section==='getting-started' && 'active'">Registration &amp; Industry Profile</a>
            <a class="docs-nav-item" x-show="match('events team statuses')" x-on:click="go('events')" x-bind:class="section==='events' && 'active'">Events</a>

            <div class="docs-nav-group-label">Clients &amp; Vendors</div>
            <a class="docs-nav-item" x-show="match('client portal financial visibility')" x-on:click="go('clients')" x-bind:class="section==='clients' && 'active'">Client Portal</a>
            <a class="docs-nav-item" x-show="match('vendors contracts invoices directory')" x-on:click="go('vendors')" x-bind:class="section==='vendors' && 'active'">Vendors, Contracts &amp; Invoices</a>

            <div class="docs-nav-group-label">Money</div>
            <a class="docs-nav-item" x-show="match('budget fees payments responsible party')" x-on:click="go('budget')" x-bind:class="section==='budget' && 'active'">Budget &amp; Your Fee</a>
            <a class="docs-nav-item" x-show="match('billing plan upgrade subscription')" x-on:click="go('billing')" x-bind:class="section==='billing' && 'active'">Billing &amp; Plans</a>

            <div class="docs-nav-group-label">Planning Tools</div>
            <a class="docs-nav-item" x-show="match('tasks checklists templates')" x-on:click="go('tasks')" x-bind:class="section==='tasks' && 'active'">Tasks &amp; Checklists</a>
            <a class="docs-nav-item" x-show="match('moodboards templates')" x-on:click="go('moodboards')" x-bind:class="section==='moodboards' && 'active'">Moodboards</a>
            <a class="docs-nav-item" x-show="match('guests rsvp qr checkin')" x-on:click="go('guests')" x-bind:class="section==='guests' && 'active'">Guests &amp; RSVP</a>
            <a class="docs-nav-item" x-show="match('runsheet event day timeline')" x-on:click="go('runsheet')" x-bind:class="section==='runsheet' && 'active'">Runsheet</a>

            <div class="docs-nav-group-label">Forms &amp; Integration</div>
            <a class="docs-nav-item" x-show="match('forms bookings api embed endpoint formspree')" x-on:click="go('forms')" x-bind:class="section==='forms' && 'active'">Forms, Bookings &amp; the Submit API</a>
            <a class="docs-nav-item" x-show="match('domain subdomain dns custom cname txt')" x-on:click="go('domain')" x-bind:class="section==='domain' && 'active'">Domain Setup</a>
            <a class="docs-nav-item" x-show="match('quick access no login staff vendor')" x-on:click="go('quickaccess')" x-bind:class="section==='quickaccess' && 'active'">Quick Access Links</a>

            <div class="docs-nav-group-label">Team &amp; Settings</div>
            <a class="docs-nav-item" x-show="match('roles permissions staff')" x-on:click="go('roles')" x-bind:class="section==='roles' && 'active'">Roles &amp; Permissions</a>
            <a class="docs-nav-item" x-show="match('notifications email preferences')" x-on:click="go('notifications')" x-bind:class="section==='notifications' && 'active'">Notifications</a>
        </nav>

        <div class="docs-content">

            <div class="docs-intro">
                <div class="docs-intro-kicker">Koordli product guide</div>
                <h2>Everything your event business needs, in one calm workspace.</h2>
                <p>Move through the guide at your own pace. Learn the workflow from first enquiry to final guest check-in, with practical details for every part of the system.</p>
                <div class="docs-intro-stats">
                    <div class="docs-intro-stat"><strong>14</strong><span>product areas</span></div>
                    <div class="docs-intro-stat"><strong>1</strong><span>connected workspace</span></div>
                    <div class="docs-intro-stat"><strong>24/7</strong><span>access for your team</span></div>
                </div>
            </div>

            {{-- Getting Started --}}
            <section class="docs-section" x-bind:class="section==='getting-started' && 'active'">
                <div class="docs-eyebrow">Getting Started</div>
                <h1>Registration &amp; Your Industry Profile</h1>
                <p class="docs-lede">What happens when you sign up, and what that one dropdown actually controls.</p>

                <h2>Signing up</h2>
                <p>Registration takes four short steps: your account details, a 6-digit email verification code, choosing a plan, and an optional setup step you can skip. Your workspace is created the moment your email is verified.</p>

                <h2>What "Industry Profile" actually does</h2>
                <p>During signup you'll choose what best describes your business — Weddings &amp; Celebrations, Corporate Events, Production, Church, Conference, Entertainment, Exhibition, Other, or Full-Service if you work across multiple types.</p>
                <p>This does exactly two things, both helpful, neither permanent:</p>
                <ul>
                    <li><strong>Starter defaults</strong> — your workspace is pre-populated with sensible starting Event Types, Vendor Categories, Task Categories, and Staff Roles suited to that kind of work.</li>
                    <li><strong>Template filtering</strong> — Moodboard and Checklist templates matching your profile show first, instead of all 8 categories mixed together.</li>
                </ul>
                <div class="docs-callout note">
                    <svg class="docs-callout-icon" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <span>Nothing is locked in. Every pre-filled category can be renamed, deleted, or added to immediately — it never changes your dashboard's layout or which features you can access.</span>
                </div>

                <div class="docs-stuck">
                    <div class="docs-stuck-icon">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <div class="docs-stuck-text">
                        <div><strong>Still not sure which profile fits?</strong></div>
                        <span>You can change it later from Settings — nothing about this choice is permanent.</span>
                    </div>
                    <a href="{{ route('tenant.support.tickets') }}" class="docs-stuck-btn">Contact Support</a>
                </div>
            </section>

                        {{-- Events --}}
            <section class="docs-section" x-bind:class="section==='events' && 'active'">
                <div class="docs-eyebrow">Getting Started</div>
                <h1>Events</h1>
                <p class="docs-lede">The core object everything else in Koordli attaches to.</p>
                <p>Every event has a status, a date, a venue, an assigned team, and its own dedicated pages for Guests, Budget, Vendors, Tasks, Checklist, Moodboards, and Runsheet. Assign staff to an event's team to control who sees it in their dashboard and notifications.</p>

                <div class="docs-mockup-wrap">
                    <div class="docs-mockup">
                        <div class="docs-mockup-bar">
                            <div class="docs-mockup-dot" style="background:#EF4444;"></div>
                            <div class="docs-mockup-dot" style="background:#F59E0B;"></div>
                            <div class="docs-mockup-dot" style="background:#10B981;"></div>
                            <span class="docs-mockup-title">Adaeze &amp; Chuka Wedding</span>
                        </div>
                        <div class="docs-mockup-body">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                                <div style="font-size:14px;font-weight:700;color:#1C1917;">Adaeze &amp; Chuka Wedding</div>
                                <span style="background:#D1FAE5;color:#059669;font-size:10px;padding:3px 9px;border-radius:10px;font-weight:600;">Confirmed</span>
                            </div>
                            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
                                <div style="text-align:center;padding:12px;background:#F5F5F4;border-radius:8px;">
                                    <div style="font-size:17px;font-weight:800;color:#7C3AED;">Aug 15</div>
                                    <div style="font-size:9.5px;color:#A8A29E;">Date</div>
                                </div>
                                <div style="text-align:center;padding:12px;background:#F5F5F4;border-radius:8px;">
                                    <div style="font-size:17px;font-weight:800;color:#10B981;">8</div>
                                    <div style="font-size:9.5px;color:#A8A29E;">Vendors</div>
                                </div>
                                <div style="text-align:center;padding:12px;background:#F5F5F4;border-radius:8px;">
                                    <div style="font-size:17px;font-weight:800;color:#F59E0B;">312</div>
                                    <div style="font-size:9.5px;color:#A8A29E;">RSVPs</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="docs-mockup-caption">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        This is exactly what an Event's detail page looks like in your dashboard.
                    </div>
                </div>
            </section>

            {{-- Clients --}}
            <section class="docs-section" x-bind:class="section==='clients' && 'active'">
                <div class="docs-eyebrow">Clients &amp; Vendors</div>
                <h1>Client Portal</h1>
                <p class="docs-lede">What your clients can see, and how you control it.</p>
                <p>Each client gets a secure login to a simplified portal — their event overview, messages with you, checklist progress, shared moodboards, and their budget — only what you explicitly choose to share.</p>

                <h2>Financial Visibility</h2>
                <p>Under <strong>Client Financial Visibility</strong> in your settings, four toggles control exactly what a client can see:</p>
                <table class="docs-table">
                    <tr><th>Field</th><th>Default</th><th>Shows</th></tr>
                    <tr><td>Their Balance</td><td>On</td><td>Agreed budget, amount paid, outstanding</td></tr>
                    <tr><td>Cost Breakdown</td><td>Off</td><td>Spending by category, paid vs. outstanding</td></tr>
                    <tr><td>Vendor Payment Detail</td><td>Off</td><td>Which vendors are paid, and how much</td></tr>
                    <tr><td>Your Professional Fee</td><td>Off</td><td>Your fee amount and structure</td></tr>
                </table>
                <div class="docs-callout note">
                    <svg class="docs-callout-icon" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <span>Sensitive fields default OFF. A client only ever sees more once you deliberately turn it on.</span>
                </div>
            </section>

            {{-- Vendors --}}
            <section class="docs-section" x-bind:class="section==='vendors' && 'active'">
                <div class="docs-eyebrow">Clients &amp; Vendors</div>
                <h1>Vendors, Contracts &amp; Invoices</h1>
                <p class="docs-lede">Your directory, agreements, and the money that flows through them.</p>
                <p>Build a private directory of vendors, assign them to events, generate branded contracts with e-signatures, and track invoices with multiple payments each. A vendor invoice automatically keeps your event's Budget in sync — nothing needs entering twice.</p>
                <p>Vendors and clients can also get their own portal logins — vendors to update runsheet status and manage availability, clients to track their event and payment progress.</p>
            </section>

            {{-- Budget --}}
            <section class="docs-section" x-bind:class="section==='budget' && 'active'">
                <div class="docs-eyebrow">Money</div>
                <h1>Budget &amp; Your Professional Fee</h1>
                <p class="docs-lede">Built to work whether you charge a flat fee, a percentage, or something else entirely.</p>

                <h2>Whose money is this?</h2>
                <p>Every cost item can be tagged with who's actually responsible for it:</p>
                <ul>
                    <li><strong>From Client's Budget</strong> (default) — you're spending money the client already gave you to hold. A real cost, never counted as your own loss.</li>
                    <li><strong>Client Pays Directly</strong> — the client pays that vendor themselves; you never touch this money.</li>
                    <li><strong>Your Own Money</strong> — the rare case where you're genuinely fronting your own cash.</li>
                </ul>

                <h2>Setting your fee</h2>
                <p>Your professional fee is completely separate from the client's event budget. Pick a structure for your own records (Fixed, Percentage of Budget, Per Guest, Per Day, Package, or Custom), then confirm one real number.</p>
                <div class="docs-callout warn">
                    <svg class="docs-callout-icon" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span>Percentages are a one-time calculator, not a live formula. Picking "15% of Budget" shows the resulting amount once — it never silently recalculates your fee if the budget changes later.</span>
                </div>

                <h2>On top, or out of the budget?</h2>
                <ul>
                    <li><strong>Added on top</strong> — the client pays the agreed budget, plus your fee separately.</li>
                    <li><strong>Taken out of the budget</strong> — your fee is included in what the client already pays; the amount left for real event costs shrinks accordingly.</li>
                </ul>
                <p>Tag a payment "Your Professional Fee" when recording it to count it toward your fee specifically. Once fully collected, you'll get an automatic email.</p>
            </section>

                        {{-- Billing --}}
            <section class="docs-section" x-bind:class="section==='billing' && 'active'">
                <div class="docs-eyebrow">Money</div>
                <h1>Billing &amp; Plans</h1>
                <p class="docs-lede">Your own Koordli subscription — separate from your clients' event budgets.</p>
                <p>Manage your plan, payment method, and upgrade options under Billing. Some features (custom subdomain, custom domain, white-label branding) are plan-gated and show an upgrade prompt if your current plan doesn't include them.</p>

                <div class="docs-mockup-wrap">
                    <div class="docs-mockup">
                        <div class="docs-mockup-bar">
                            <div class="docs-mockup-dot" style="background:#EF4444;"></div>
                            <div class="docs-mockup-dot" style="background:#F59E0B;"></div>
                            <div class="docs-mockup-dot" style="background:#10B981;"></div>
                            <span class="docs-mockup-title">Upgrade to Pro</span>
                        </div>
                        <div class="docs-mockup-body" style="background:#F5F3FF;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:34px;height:34px;border-radius:9px;background:#EDE9FE;display:flex;align-items:center;justify-content:center;font-size:15px;">⚡</div>
                                <div>
                                    <div style="font-size:12.5px;font-weight:700;color:#1C1917;">Custom Domain locked</div>
                                    <div style="font-size:10.5px;color:#78716C;">Available on the Pro plan and above</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="docs-mockup-caption">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        A locked feature always shows exactly what plan unlocks it.
                    </div>
                </div>
            </section>

                        {{-- Tasks --}}
            <section class="docs-section" x-bind:class="section==='tasks' && 'active'">
                <div class="docs-eyebrow">Planning Tools</div>
                <h1>Tasks &amp; Checklists</h1>
                <p class="docs-lede">Two related but distinct tools — delegated work vs. planning milestones.</p>
                <p><strong>Tasks</strong> are assignable, tracked by status/priority/due date. <strong>Checklists</strong> are time-based milestones ("12+ months before," "1 week before") specific to your event type — apply a pre-built template, or build your own.</p>
                <p>Any checklist item can be converted into a real Task with one click — even select several items at once and convert them together.</p>

                <div class="docs-mockup-wrap">
                    <div class="docs-mockup">
                        <div class="docs-mockup-bar">
                            <div class="docs-mockup-dot" style="background:#EF4444;"></div>
                            <div class="docs-mockup-dot" style="background:#F59E0B;"></div>
                            <div class="docs-mockup-dot" style="background:#10B981;"></div>
                            <span class="docs-mockup-title">Tasks — This Week</span>
                        </div>
                        <div class="docs-mockup-body">
                            @foreach([['Confirm catering headcount','Urgent','#EF4444'],['Send RSVP reminder','Normal','#3B82F6'],['Finalize seating chart','High','#F59E0B']] as [$task, $priority, $color])
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid #F5F5F4;">
                                <span style="font-size:12px;color:#1C1917;">{{ $task }}</span>
                                <span style="font-size:9.5px;color:{{ $color }};background:{{ $color }}1a;padding:2px 9px;border-radius:8px;font-weight:600;">{{ $priority }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="docs-mockup-caption">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        Your real Task Center, filterable by event, priority, and assignee.
                    </div>
                </div>
            </section>

                        {{-- Moodboards --}}
            <section class="docs-section" x-bind:class="section==='moodboards' && 'active'">
                <div class="docs-eyebrow">Planning Tools</div>
                <h1>Moodboards</h1>
                <p class="docs-lede">Visual creative direction, shareable with your client.</p>
                <p>Build a free-form visual board — images, colors, notes, links — and optionally share it with your client for approval. Apply a starter template matched to your event type to begin with a sensible layout rather than a blank canvas.</p>

                <div class="docs-mockup-wrap">
                    <div class="docs-mockup">
                        <div class="docs-mockup-bar">
                            <div class="docs-mockup-dot" style="background:#EF4444;"></div>
                            <div class="docs-mockup-dot" style="background:#F59E0B;"></div>
                            <div class="docs-mockup-dot" style="background:#10B981;"></div>
                            <span class="docs-mockup-title">Overall Vision</span>
                        </div>
                        <div class="docs-mockup-body" style="background:#FAFAF9;">
                            <div style="display:grid;grid-template-columns:1.3fr 1fr 1fr;gap:8px;">
                                <div style="background:#1C1917;border-radius:8px;height:70px;"></div>
                                <div style="background:#F59E0B;border-radius:8px;height:70px;"></div>
                                <div style="background:#7C3AED;border-radius:8px;height:70px;"></div>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:8px;margin-top:8px;">
                                <div style="background:#fff;border:1px solid #E7E5E4;border-radius:8px;padding:10px;font-size:10px;color:#78716C;">Warm, romantic, gold accents throughout.</div>
                                <div style="background:#EC4899;border-radius:8px;height:50px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="docs-mockup-caption">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        A real Moodboard — colors, notes, and images arranged freely on canvas.
                    </div>
                </div>
            </section>

                        {{-- Guests --}}
            <section class="docs-section" x-bind:class="section==='guests' && 'active'">
                <div class="docs-eyebrow">Planning Tools</div>
                <h1>Guests &amp; RSVP</h1>
                <p class="docs-lede">Branded RSVP pages and QR check-in, replacing manual counting.</p>
                <p>Build a custom RSVP page per event, with your own cover image and questions beyond just attendance. Confirmed guests receive a scannable QR ticket by email, checked in at the door in seconds.</p>

                <div class="docs-mockup-wrap">
                    <div class="docs-mockup">
                        <div class="docs-mockup-bar">
                            <div class="docs-mockup-dot" style="background:#EF4444;"></div>
                            <div class="docs-mockup-dot" style="background:#F59E0B;"></div>
                            <div class="docs-mockup-dot" style="background:#10B981;"></div>
                            <span class="docs-mockup-title">RSVP — Will you attend?</span>
                        </div>
                        <div class="docs-mockup-body" style="text-align:center;">
                            <div style="width:60px;height:60px;background:#1C1917;border-radius:10px;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:9px;font-weight:600;">QR CODE</div>
                            <div style="font-size:13px;font-weight:700;color:#1C1917;">312 confirmed · 38 pending</div>
                        </div>
                    </div>
                    <div class="docs-mockup-caption">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        Every confirmed guest gets a ticket like this, scanned at check-in.
                    </div>
                </div>
            </section>

                        {{-- Runsheet --}}
            <section class="docs-section" x-bind:class="section==='runsheet' && 'active'">
                <div class="docs-eyebrow">Planning Tools</div>
                <h1>Runsheet</h1>
                <p class="docs-lede">Your event-day timeline, live.</p>
                <p>Build a timeline of the day's activities, assigned to staff or vendors. Vendors can update their own item's status from their phone on-site — no more printed schedules that go stale the moment something shifts.</p>

                <div class="docs-mockup-wrap">
                    <div class="docs-mockup">
                        <div class="docs-mockup-bar">
                            <div class="docs-mockup-dot" style="background:#EF4444;"></div>
                            <div class="docs-mockup-dot" style="background:#F59E0B;"></div>
                            <div class="docs-mockup-dot" style="background:#10B981;"></div>
                            <span class="docs-mockup-title">Wedding Day Runsheet</span>
                        </div>
                        <div class="docs-mockup-body">
                            @foreach([['9:00 AM','Venue Setup','#10B981','Done'],['12:00 PM','Guest Arrival','#F59E0B','In Progress'],['2:00 PM','Ceremony','#A8A29E','Pending']] as [$time, $item, $color, $status])
                            <div style="display:flex;gap:10px;padding:8px 0;border-bottom:1px solid #F5F5F4;align-items:center;">
                                <span style="font-size:10.5px;color:#78716C;width:58px;flex-shrink:0;">{{ $time }}</span>
                                <span style="width:8px;height:8px;border-radius:50%;background:{{ $color }};flex-shrink:0;"></span>
                                <span style="font-size:12px;color:#1C1917;flex:1;">{{ $item }}</span>
                                <span style="font-size:9.5px;color:{{ $color }};font-weight:600;">{{ $status }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="docs-mockup-caption">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        Status updates in real time as your team and vendors work the event.
                    </div>
                </div>
            </section>

            {{-- Forms — the connected numbered-step treatment --}}
            <section class="docs-section" x-bind:class="section==='forms' && 'active'">
                <div class="docs-eyebrow">Forms &amp; Integration</div>
                <h1>Forms, Bookings &amp; the Submit API</h1>
                <p class="docs-lede">Capture leads from anywhere — your own website included.</p>

                <h2>Two form types</h2>
                <p>Build <strong>Booking</strong> forms (a simple enquiry form) or <strong>Consultation</strong> forms (with a real calendar of available time slots), with your own custom fields.</p>

                <h2>Getting submissions into your dashboard</h2>
                <div class="docs-steps">
                    <div class="docs-step">
                        <div class="docs-step-num">01</div>
                        <div class="docs-step-body">
                            <div class="docs-step-title">Share the direct link</div>
                            <div class="docs-step-desc">Every form gets a public URL you can post anywhere — social media, email, WhatsApp.</div>
                        </div>
                    </div>
                    <div class="docs-step">
                        <div class="docs-step-num">02</div>
                        <div class="docs-step-body">
                            <div class="docs-step-title">Or embed it on your own website</div>
                            <div class="docs-step-desc">Paste the iframe code anywhere on your site to show the full form in place.</div>
                            <div class="docs-code-block">
                                <div class="docs-code-bar">
                                    <span class="docs-code-label">HTML</span>
                                    <button class="docs-copy-btn" x-on:click="copy('<iframe src=\`&quot;https://yourapp.com/consult/your-form-slug&quot;\` width=\`&quot;100%&quot;\` height=\`&quot;800&quot;\` frameborder=\`&quot;0&quot;\`></iframe>', 'embed')">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                                        <span x-text="copied === 'embed' ? 'Copied!' : 'Copy'"></span>
                                    </button>
                                </div>
                                <div class="docs-code-pre"><code>&lt;iframe src="https://yourapp.com/consult/your-form-slug" width="100%" height="800" frameborder="0"&gt;&lt;/iframe&gt;</code></div>
                            </div>
                        </div>
                    </div>
                    <div class="docs-step">
                        <div class="docs-step-num">03</div>
                        <div class="docs-step-body">
                            <div class="docs-step-title">Or point your own form at our Submit endpoint</div>
                            <div class="docs-step-desc">Already have an HTML form on an existing site? Send submissions straight to your Koordli dashboard — works like Formspree.</div>
                            <div class="docs-code-block">
                                <div class="docs-code-bar">
                                    <span class="docs-code-label">Endpoint</span>
                                    <button class="docs-copy-btn" x-on:click="copy('https://yourapp.com/api/forms/{your-form-token}/submit', 'endpoint')">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                                        <span x-text="copied === 'endpoint' ? 'Copied!' : 'Copy'"></span>
                                    </button>
                                </div>
                                <div class="docs-code-pre"><code>POST https://yourapp.com/api/forms/{your-form-token}/submit</code></div>
                            </div>
                            <p style="margin-top:14px;">Every input's <code>name</code> should match a field label configured on that form:</p>
                            <div class="docs-code-block">
                                <div class="docs-code-bar">
                                    <span class="docs-code-label">HTML</span>
                                    <button class="docs-copy-btn" x-on:click="copy('<form action=\`&quot;https://yourapp.com/api/forms/{your-form-token}/submit&quot;\` method=\`&quot;POST&quot;\`>\n  <input type=\`&quot;hidden&quot;\` name=\`&quot;_token&quot;\` value=\`&quot;your-csrf-token&quot;\`>\n  <input type=\`&quot;text&quot;\` name=\`&quot;Full Name&quot;\` required>\n  <input type=\`&quot;email&quot;\` name=\`&quot;Email&quot;\` required>\n  <button type=\`&quot;submit&quot;\`>Submit</button>\n</form>', 'form')">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                                        <span x-text="copied === 'form' ? 'Copied!' : 'Copy'"></span>
                                    </button>
                                </div>
                                <div class="docs-code-pre"><code>&lt;form action="https://yourapp.com/api/forms/{your-form-token}/submit" method="POST"&gt;
  &lt;input type="hidden" name="_token" value="your-csrf-token"&gt;
  &lt;input type="text" name="Full Name" required&gt;
  &lt;input type="email" name="Email" required&gt;
  &lt;button type="submit"&gt;Submit&lt;/button&gt;
&lt;/form&gt;</code></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="docs-stuck">
                    <div class="docs-stuck-icon">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <div class="docs-stuck-text">
                        <div><strong>Need your exact endpoint URL?</strong></div>
                        <span>It's shown on each form's own page in your dashboard, with one-click copy already built in.</span>
                    </div>
                    <a href="{{ route('tenant.forms') }}" class="docs-stuck-btn">Go to Forms</a>
                </div>
            </section>

            {{-- Domain --}}
            <section class="docs-section" x-bind:class="section==='domain' && 'active'">
                <div class="docs-eyebrow">Forms &amp; Integration</div>
                <h1>Domain Setup</h1>
                <p class="docs-lede">Your workspace address — from a free subdomain to your own custom domain.</p>

                <h2>Subdomain</h2>
                <p>Every workspace gets a subdomain that works instantly, with no DNS setup required.</p>

                <h2>Custom Domain</h2>
                <p>To use your own domain (e.g. <code>app.yourcompany.com</code>), add it under Domain Settings, then create two DNS records at your registrar:</p>
                <div class="docs-steps">
                    <div class="docs-step">
                        <div class="docs-step-num">01</div>
                        <div class="docs-step-body">
                            <div class="docs-step-title">CNAME Record</div>
                            <div class="docs-step-desc">Points your domain at Koordli's servers.</div>
                        </div>
                    </div>
                    <div class="docs-step">
                        <div class="docs-step-num">02</div>
                        <div class="docs-step-body">
                            <div class="docs-step-title">TXT Record (<code>_koordli-verify</code>)</div>
                            <div class="docs-step-desc">Proves you own the domain.</div>
                        </div>
                    </div>
                    <div class="docs-step">
                        <div class="docs-step-num">03</div>
                        <div class="docs-step-body">
                            <div class="docs-step-title">Click Verify Now</div>
                            <div class="docs-step-desc">Both exact record values appear on your Domain Settings page the moment you add a domain. Verification also re-checks automatically every day.</div>
                        </div>
                    </div>
                </div>
                <div class="docs-callout warn">
                    <svg class="docs-callout-icon" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span>DNS changes can take a few minutes to 48 hours to fully take effect. If verification fails right after adding records, wait a while and try again.</span>
                </div>
                <p>Custom domains and subdomains are plan-gated features — Domain Settings shows an upgrade prompt if your plan doesn't include them yet.</p>
            </section>

                        {{-- Quick Access --}}
            <section class="docs-section" x-bind:class="section==='quickaccess' && 'active'">
                <div class="docs-eyebrow">Forms &amp; Integration</div>
                <h1>Quick Access Links</h1>
                <p class="docs-lede">A permanent link for staff or vendors who don't need a full login.</p>
                <p>Generate a unique, no-login link for a staff member or vendor to update a task, mark a runsheet item, or complete a checklist item — right from their phone. Optionally protect it with a self-set PIN. Regenerating a link instantly invalidates the old one.</p>

                <div class="docs-mockup-wrap">
                    <div class="docs-mockup">
                        <div class="docs-mockup-bar">
                            <div class="docs-mockup-dot" style="background:#EF4444;"></div>
                            <div class="docs-mockup-dot" style="background:#F59E0B;"></div>
                            <div class="docs-mockup-dot" style="background:#10B981;"></div>
                            <span class="docs-mockup-title">Quick Access — No Login</span>
                        </div>
                        <div class="docs-mockup-body" style="text-align:center;">
                            <div style="font-size:11px;color:#78716C;margin-bottom:10px;">Confirm your PIN</div>
                            <div style="display:flex;justify-content:center;gap:6px;margin-bottom:14px;">
                                @for($i=0;$i<4;$i++)
                                <div style="width:28px;height:34px;border:1.5px solid #E7E5E4;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#1C1917;">•</div>
                                @endfor
                            </div>
                            <div style="font-size:11px;font-weight:600;color:#7C3AED;">Tap to mark "Setup Complete"</div>
                        </div>
                    </div>
                    <div class="docs-mockup-caption">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        What your vendor sees when they tap their permanent link on-site.
                    </div>
                </div>
            </section>

                        {{-- Roles --}}
            <section class="docs-section" x-bind:class="section==='roles' && 'active'">
                <div class="docs-eyebrow">Team &amp; Settings</div>
                <h1>Roles &amp; Permissions</h1>
                <p class="docs-lede">Control exactly what each staff member can see and do.</p>
                <p>Create custom roles and toggle individual permissions per role — view-only vs. full management access, per feature area. The account owner role always has full access and can't be edited or deleted.</p>

                <div class="docs-mockup-wrap">
                    <div class="docs-mockup">
                        <div class="docs-mockup-bar">
                            <div class="docs-mockup-dot" style="background:#EF4444;"></div>
                            <div class="docs-mockup-dot" style="background:#F59E0B;"></div>
                            <div class="docs-mockup-dot" style="background:#10B981;"></div>
                            <span class="docs-mockup-title">Coordinator Role</span>
                        </div>
                        <div class="docs-mockup-body">
                            @foreach([['Events', true],['Budget', true],['Contracts', false],['Invoices', false]] as [$perm, $on])
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #F5F5F4;">
                                <span style="font-size:12px;color:#1C1917;">{{ $perm }}</span>
                                <div style="width:32px;height:18px;border-radius:10px;background:{{ $on ? '#7C3AED' : '#E7E5E4' }};position:relative;">
                                    <div style="width:14px;height:14px;border-radius:50%;background:#fff;position:absolute;top:2px;{{ $on ? 'right:2px;' : 'left:2px;' }}"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="docs-mockup-caption">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        Instant toggles — no page reload, changes apply immediately.
                    </div>
                </div>
            </section>

                        {{-- Notifications --}}
            <section class="docs-section" x-bind:class="section==='notifications' && 'active'">
                <div class="docs-eyebrow">Team &amp; Settings</div>
                <h1>Notifications</h1>
                <p class="docs-lede">Who gets emailed, and when.</p>
                <p>Staff notification preferences control what you get emailed about. Client Notification Settings (separate, tenant-wide) control what your clients get emailed about — payments recorded, milestones reached, and more — each with its own on/off toggle.</p>

                <div class="docs-mockup-wrap">
                    <div class="docs-mockup">
                        <div class="docs-mockup-bar">
                            <div class="docs-mockup-dot" style="background:#EF4444;"></div>
                            <div class="docs-mockup-dot" style="background:#F59E0B;"></div>
                            <div class="docs-mockup-dot" style="background:#10B981;"></div>
                            <span class="docs-mockup-title">Client Notifications</span>
                        </div>
                        <div class="docs-mockup-body">
                            @foreach([['Payment Confirmations', true],['Vendor Suggestions', false],['Event Detail Changes', true]] as [$item, $on])
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #F5F5F4;">
                                <span style="font-size:12px;color:#1C1917;">{{ $item }}</span>
                                <div style="width:32px;height:18px;border-radius:10px;background:{{ $on ? '#7C3AED' : '#E7E5E4' }};position:relative;">
                                    <div style="width:14px;height:14px;border-radius:50%;background:#fff;position:absolute;top:2px;{{ $on ? 'right:2px;' : 'left:2px;' }}"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="docs-mockup-caption">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        Every toggle here controls a real email trigger — nothing is bundled.
                    </div>
                </div>
            </section>

        </div>

        <aside class="docs-rail">
            <div class="docs-rail-card">
                <div class="docs-rail-label">Quick explore</div>
                <div class="docs-rail-title">Make the most of Koordli</div>
                <button class="docs-rail-link" x-on:click="go('getting-started')"><span>01</span> Start with your profile</button>
                <button class="docs-rail-link" x-on:click="go('events')"><span>02</span> Build an event</button>
                <button class="docs-rail-link" x-on:click="go('clients')"><span>03</span> Bring in your client</button>
                <button class="docs-rail-link" x-on:click="go('guests')"><span>04</span> Manage RSVPs</button>
                <button class="docs-rail-link" x-on:click="go('runsheet')"><span>05</span> Run event day</button>
                <div class="docs-rail-note">Looking for a specific feature? Use the search field above, or <a href="{{ route('landing') }}#pricing">compare plans</a>.</div>
            </div>
        </aside>
    </div>
</body>
</html>