<div>
<style>
    :root {
        --rsvp-accent: {{ $rsvpForm->branding['accent_color'] ?? '#1C1917' }};
        --rsvp-bg: {{ $rsvpForm->branding['bg_color'] ?? '#FAFAF9' }};
    }
    .rsvp-shell {
        display: grid;
        grid-template-columns: 1fr 1fr;
        min-height: 100vh;
    }
    .rsvp-left {
        position: sticky;
        top: 0;
        height: 100vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        padding: 56px 56px 40px;
    }
    .rsvp-left-bg {
        position: absolute;
        inset: 0;
        background: #1C1917;
        z-index: 0;
    }
    .rsvp-left-bg-image {
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center;
        z-index: 0;
    }
    .rsvp-left-bg-image::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(160deg, rgba(28,25,23,0.45) 0%, rgba(28,25,23,0.80) 100%);
    }
    .rsvp-left-content {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        height: 100%;
        justify-content: space-between;
    }
    .rsvp-brand {
        font-family: 'Spline Sans', sans-serif;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: rgba(255,255,255,0.4);
    }
    .rsvp-invited-label {
        font-family: 'Spline Sans', sans-serif;
        font-size: 11px;
        font-weight: 500;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: rgba(255,255,255,0.4);
        margin-bottom: 14px;
    }
    .rsvp-event-name {
        font-family: 'Fraunces', serif;
        font-size: clamp(34px, 3.5vw, 45px);
        font-weight: 400;
        color: #FAFAF9;
        line-height: 1.08;
        letter-spacing: -0.02em;
        margin-bottom: 24px;
    }
    .rsvp-meta-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 13px;
        color: rgba(255,255,255,0.65);
        font-family: 'Spline Sans', sans-serif;
        line-height: 1.5;
        margin-bottom: 10px;
    }
    .rsvp-meta-icon {
        width: 15px;
        height: 15px;
        flex-shrink: 0;
        margin-top: 2px;
        opacity: 0.55;
    }
    .rsvp-footer-text {
        font-size: 11px;
        color: rgba(255,255,255,0.25);
        font-family: 'Spline Sans', sans-serif;
        letter-spacing: 0.04em;
    }
    .rsvp-right {
        padding: 60px 52px;
        overflow-y: auto;
        background: var(--rsvp-bg);
        display: flex;
        align-items: flex-start;
        justify-content: center;
        min-height: 100vh;
    }
    .rsvp-form-wrap { width: 100%; max-width: 400px; }
    .rsvp-form-title {
        font-family: 'Fraunces', serif;
        font-size: 26px;
        font-weight: 600;
        color: #1C1917;
        margin-bottom: 6px;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }
    .rsvp-form-subtitle {
        font-size: 13px;
        color: #78716C;
        margin-bottom: 28px;
        font-family: 'Spline Sans', sans-serif;
        line-height: 1.6;
    }
    .rsvp-label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: #78716C;
        margin-bottom: 6px;
        font-family: 'Spline Sans', sans-serif;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }
    .rsvp-input {
        width: 100%;
        background: #FFFFFF;
        border: 1px solid #E7E5E4;
        border-radius: 6px;
        padding: 11px 14px;
        font-size: 14px;
        font-family: 'Spline Sans', sans-serif;
        color: #1C1917;
        outline: none;
        transition: border-color 150ms ease;
        appearance: none;
    }
    .rsvp-input:focus { border-color: var(--rsvp-accent); }
    .rsvp-input::placeholder { color: #C4C0BC; }
    .rsvp-input-group { margin-bottom: 20px; }
    .rsvp-input-hint { font-size: 11px; color: #A8A29E; margin-top: 5px; font-family: 'Spline Sans', sans-serif; }
    .rsvp-error-msg { font-size: 11px; color: #EF4444; margin-top: 4px; display: block; }

    /* Attend buttons — default state pre-rendered server side */
    .rsvp-btn-attend {
        flex: 1;
        padding: 12px 16px;
        border-radius: 6px;
        border: 1.5px solid #E7E5E4;
        background: #fff;
        color: #78716C;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        font-family: 'Spline Sans', sans-serif;
        transition: all 150ms ease;
        text-align: center;
    }
    .rsvp-btn-yes {
        border-color: var(--rsvp-accent);
        background: var(--rsvp-accent);
        color: #fff;
    }
    .rsvp-btn-no {
        border-color: #EF4444;
        background: #FEF2F2;
        color: #DC2626;
    }

    .rsvp-submit {
        width: 100%;
        padding: 13px;
        border-radius: 6px;
        background: var(--rsvp-accent);
        color: #fff;
        border: none;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        font-family: 'Spline Sans', sans-serif;
        letter-spacing: 0.02em;
        transition: opacity 150ms;
    }
    .rsvp-submit:disabled { opacity: 0.5; cursor: not-allowed; }

    .rsvp-divider { height: 1px; background: #E7E5E4; margin: 22px 0; }
    .rsvp-section-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #B8B3AF;
        margin-bottom: 16px;
        font-family: 'Spline Sans', sans-serif;
    }

    /* Success state */
    .rsvp-success-icon { font-size: 44px; margin-bottom: 14px; }
    .rsvp-success-title {
        font-family: 'Fraunces', serif;
        font-size: 28px;
        font-weight: 600;
        color: #1C1917;
        margin-bottom: 10px;
        letter-spacing: -0.02em;
    }
    .rsvp-success-text {
        font-size: 14px;
        color: #78716C;
        line-height: 1.7;
        margin-bottom: 24px;
        font-family: 'Spline Sans', sans-serif;
    }
    .rsvp-qr-box {
        background: #fff;
        border: 1px solid #E7E5E4;
        border-radius: 10px;
        padding: 28px;
        text-align: center;
        margin-bottom: 16px;
    }
    .rsvp-qr-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #A8A29E;
        margin-bottom: 16px;
        font-family: 'Spline Sans', sans-serif;
    }
    .rsvp-qr-token {
        font-size: 11px;
        color: #C4C0BC;
        font-family: monospace;
        letter-spacing: 0.06em;
        margin-top: 10px;
    }
    .rsvp-ticket-btn {
        display: block;
        width: 100%;
        padding: 13px;
        border-radius: 6px;
        background: #F5F5F4;
        color: #1C1917;
        border: 1px solid #E7E5E4;
        font-size: 13px;
        font-weight: 500;
        text-align: center;
        text-decoration: none;
        margin-bottom: 12px;
        font-family: 'Spline Sans', sans-serif;
        transition: background 150ms;
    }
    .rsvp-ticket-btn:hover { background: #ECEAE8; }
    .rsvp-edit-link {
        font-size: 12px;
        color: #A8A29E;
        text-align: center;
        display: block;
        text-decoration: none;
        font-family: 'Spline Sans', sans-serif;
        transition: color 150ms;
    }
    .rsvp-edit-link:hover { color: #57534E; }
    .rsvp-powered {
        font-size: 11px;
        color: #C4C0BC;
        text-align: center;
        margin-top: 36px;
        font-family: 'Spline Sans', sans-serif;
    }
    .rsvp-powered a { color: #A8A29E; text-decoration: none; }

    /* Dropdown menu */
    .rsvp-dropdown-menu {
        position: absolute;
        top: calc(100% + 4px);
        left: 0; right: 0;
        background: #fff;
        border: 1px solid #E7E5E4;
        border-radius: 6px;
        z-index: 50;
        overflow: hidden;
        max-height: 200px;
        overflow-y: auto;
    }
    .rsvp-dropdown-option {
        padding: 10px 14px;
        font-size: 13px;
        color: #1C1917;
        cursor: pointer;
        font-family: 'Spline Sans', sans-serif;
        transition: background 100ms;
    }
    .rsvp-dropdown-option:hover { background: #F5F5F4; }
    .rsvp-dropdown-option.selected { background: #F5F5F4; font-weight: 500; }

    @media (max-width: 768px) {
        .rsvp-shell { grid-template-columns: 1fr; }
        .rsvp-left {
            position: relative;
            height: auto;
            min-height: 240px;
            padding: 36px 28px 28px;
        }
        .rsvp-right { padding: 36px 24px 48px; min-height: auto; }
        .rsvp-form-wrap { max-width: 100%; }
    }
</style>

<div class="rsvp-shell">

    {{-- ── LEFT PANEL ── --}}
    <div class="rsvp-left">
        @if(!empty($rsvpForm->branding['cover_image']))
        <div class="rsvp-left-bg-image"
            style="background-image:url('{{ $rsvpForm->branding['cover_image'] }}');"></div>
        @else
        <div class="rsvp-left-bg"></div>
        @endif

        <div class="rsvp-left-content">
            <div class="rsvp-brand">Koordli</div>

            <div>
                <div class="rsvp-invited-label">You're Invited</div>
                <h1 class="rsvp-event-name">{{ $rsvpForm->event->name }}</h1>

                @if($rsvpForm->event->date)
                <div class="rsvp-meta-item">
                    <svg class="rsvp-meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <span>
                        {{ $rsvpForm->event->date->format('l, F j, Y') }}
                        @if($rsvpForm->event->start_time)
                        &nbsp;·&nbsp;{{ \Carbon\Carbon::parse($rsvpForm->event->start_time)->format('g:i A') }}
                        @endif
                    </span>
                </div>
                @endif

                @if($rsvpForm->event->venue)
                <div class="rsvp-meta-item">
                    <svg class="rsvp-meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    <span>
                        {{ $rsvpForm->event->venue }}
                        @if($rsvpForm->event->location)
                        &nbsp;·&nbsp;{{ $rsvpForm->event->location }}
                        @endif
                    </span>
                </div>
                @endif

                @if($rsvpForm->deadline && !$rsvpForm->isDeadlinePassed())
                <div class="rsvp-meta-item">
                    <svg class="rsvp-meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span>RSVP by {{ $rsvpForm->deadline->format('F j, Y') }}</span>
                </div>
                @endif
            </div>

            <div class="rsvp-footer-text">© {{ date('Y') }} Koordli</div>
        </div>
    </div>

    {{-- ── RIGHT PANEL ── --}}
    <div class="rsvp-right">
        <div class="rsvp-form-wrap">

            @if($submitted && $response)
            {{-- ── Success ── --}}
            @if($response->status === 'confirmed')
            <div class="rsvp-success-icon">🎉</div>
            <h2 class="rsvp-success-title">You're confirmed!</h2>
            <p class="rsvp-success-text">
                Thank you, <strong style="color:#1C1917;">{{ $response->respondent_name }}</strong>.
                Your attendance at <strong style="color:#1C1917;">{{ $rsvpForm->event->name }}</strong> has been confirmed.
                @if($response->plus_one_count > 0)
                You're attending with {{ $response->plus_one_count }} additional {{ $response->plus_one_count === 1 ? 'guest' : 'guests' }}.
                @endif
            </p>

            <div class="rsvp-qr-box">
                <div class="rsvp-qr-label">Your Entry Pass</div>
                <div style="display:flex;justify-content:center;">
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(180)->generate($response->qr_token) !!}
                </div>
                <div class="rsvp-qr-token">{{ $response->qr_token }}</div>
            </div>

            <a href="{{ url('/rsvp/ticket/' . $response->qr_token) }}" class="rsvp-ticket-btn">
                ↓ Download Ticket
            </a>

            @else
            <div class="rsvp-success-icon">💌</div>
            <h2 class="rsvp-success-title">Response recorded</h2>
            <p class="rsvp-success-text">
                Thank you for letting us know, <strong style="color:#1C1917;">{{ $response->respondent_name }}</strong>.
                We're sorry you won't be able to make it.
            </p>
            @endif

            <a href="{{ $response->editUrl() }}" class="rsvp-edit-link">
                Need to update your response? →
            </a>

            @else
            {{-- ── Form ── --}}

            @if($error)
            <div style="background:#FEE2E2;border:1px solid #FECACA;border-radius:6px;padding:12px 16px;margin-bottom:24px;font-size:13px;color:#DC2626;font-family:'Spline Sans',sans-serif;">
                {{ $error }}
            </div>
            @endif

            <h2 class="rsvp-form-title">{{ $rsvpForm->title }}</h2>
            <p class="rsvp-form-subtitle">Fill in your details below to confirm your attendance.</p>

            {{-- Attendance — server renders initial state, Alpine handles toggle --}}
            <div class="rsvp-input-group"
                x-data="{ attending: '{{ $status }}' }"
                x-init="$watch('attending', val => $wire.set('status', val))">
                <label class="rsvp-label">Will you attend? <span style="color:#EF4444;">*</span></label>
                <div style="display:flex;gap:8px;">
                    <button type="button"
                        x-on:click="attending = 'confirmed'"
                        class="rsvp-btn-attend"
                        :class="attending === 'confirmed' ? 'rsvp-btn-yes' : ''"
                        @class(['rsvp-btn-attend', 'rsvp-btn-yes' => $status === 'confirmed'])>
                        ✓ Yes, I'll attend
                    </button>
                    <button type="button"
                        x-on:click="attending = 'declined'"
                        class="rsvp-btn-attend"
                        :class="attending === 'declined' ? 'rsvp-btn-no' : ''"
                        @class(['rsvp-btn-attend', 'rsvp-btn-no' => $status === 'declined'])>
                        ✕ Can't make it
                    </button>
                </div>
            </div>

            <div class="rsvp-divider"></div>
            <div class="rsvp-section-label">Your Details</div>

            <div class="rsvp-input-group">
                <label class="rsvp-label">Full Name <span style="color:#EF4444;">*</span></label>
                <input wire:model="respondent_name" type="text" class="rsvp-input"
                    placeholder="Your full name" />
                @error('respondent_name') <span class="rsvp-error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="rsvp-input-group">
                <label class="rsvp-label">Email Address</label>
                <input wire:model="respondent_email" type="email" class="rsvp-input"
                    placeholder="your@email.com" />
                <div class="rsvp-input-hint">Required to receive your QR entry pass.</div>
            </div>

            <div class="rsvp-input-group">
                <label class="rsvp-label">Phone Number</label>
                <input wire:model="respondent_phone" type="tel" class="rsvp-input"
                    placeholder="+234..." />
            </div>

            <div class="rsvp-input-group" x-show="$wire.status === 'confirmed'" x-cloak>
                <label class="rsvp-label">Additional Guests</label>
                <input wire:model="plus_one_count" type="number" min="0" max="20"
                    class="rsvp-input" placeholder="0" style="max-width:110px;" />
                <div class="rsvp-input-hint">Enter 0 if you're coming alone.</div>
            </div>

            {{-- Custom questions --}}
            @if(($rsvpForm->questions ?? collect())->isNotEmpty())
            <div class="rsvp-divider"></div>
            <div class="rsvp-section-label">Additional Information</div>

            @foreach($rsvpForm->questions ?? [] as $q)
            <div class="rsvp-input-group">
                <label class="rsvp-label">
                    {{ $q->label }}
                    @if($q->is_required)<span style="color:#EF4444;">*</span>@endif
                </label>

                @if($q->field_type === 'text')
                <input wire:model="answers.{{ $q->id }}" type="text" class="rsvp-input" />

                @elseif($q->field_type === 'textarea')
                <textarea wire:model="answers.{{ $q->id }}" class="rsvp-input" rows="3" style="resize:vertical;"></textarea>

                @elseif($q->field_type === 'email')
                <input wire:model="answers.{{ $q->id }}" type="email" class="rsvp-input" />

                @elseif($q->field_type === 'phone')
                <input wire:model="answers.{{ $q->id }}" type="tel" class="rsvp-input" />

                @elseif($q->field_type === 'number')
                <input wire:model="answers.{{ $q->id }}" type="number" class="rsvp-input" style="max-width:140px;" />

                @elseif($q->field_type === 'date')
                <input wire:model="answers.{{ $q->id }}" type="date" class="rsvp-input" style="max-width:200px;" />

                @elseif($q->field_type === 'yes_no')
                <div style="display:flex;gap:8px;margin-top:2px;"
                    x-data="{ val: '{{ $answers[$q->id] ?? '' }}' }"
                    x-init="$watch('val', v => $wire.set('answers.{{ $q->id }}', v))">
                    <button type="button" x-on:click="val = 'Yes'"
                        class="rsvp-btn-attend"
                        :class="val === 'Yes' ? 'rsvp-btn-yes' : ''"
                        @class(['rsvp-btn-attend', 'rsvp-btn-yes' => ($answers[$q->id] ?? '') === 'Yes'])
                        style="max-width:90px;flex:none;padding:10px;">Yes</button>
                    <button type="button" x-on:click="val = 'No'"
                        class="rsvp-btn-attend"
                        :class="val === 'No' ? 'rsvp-btn-no' : ''"
                        @class(['rsvp-btn-attend', 'rsvp-btn-no' => ($answers[$q->id] ?? '') === 'No'])
                        style="max-width:90px;flex:none;padding:10px;">No</button>
                </div>

                @elseif($q->field_type === 'dropdown')
                <div x-data="{
                        open: false,
                        val: '{{ $answers[$q->id] ?? '' }}',
                        pick(v) { this.val = v; this.open = false; $wire.set('answers.{{ $q->id }}', v); }
                    }"
                    x-on:click.outside="open = false"
                    style="position:relative;">
                    <button type="button" x-on:click="open = !open"
                        class="rsvp-input"
                        style="display:flex;align-items:center;justify-content:space-between;cursor:pointer;text-align:left;">
                        <span x-text="val || 'Select an option'"
                            :style="!val ? 'color:#C4C0BC' : 'color:#1C1917'"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                            style="flex-shrink:0;color:#A8A29E;transition:transform 150ms;"
                            :style="open ? 'transform:rotate(180deg)' : ''">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak class="rsvp-dropdown-menu">
                        @foreach($q->options ?? [] as $opt)
                        <div x-on:click="pick('{{ $opt }}')"
                            class="rsvp-dropdown-option"
                            :class="val === '{{ $opt }}' ? 'selected' : ''">
                            {{ $opt }}
                        </div>
                        @endforeach
                    </div>
                </div>

                @elseif($q->field_type === 'radio')
                <div style="display:flex;flex-direction:column;gap:10px;margin-top:4px;">
                    @foreach($q->options ?? [] as $opt)
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;color:#57534E;font-family:'Spline Sans',sans-serif;">
                        <input wire:model="answers.{{ $q->id }}" type="radio" value="{{ $opt }}"
                            style="accent-color:var(--rsvp-accent);width:15px;height:15px;flex-shrink:0;" />
                        {{ $opt }}
                    </label>
                    @endforeach
                </div>

                @elseif($q->field_type === 'checkbox')
                <div style="display:flex;flex-direction:column;gap:10px;margin-top:4px;">
                    @foreach($q->options ?? [] as $opt)
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;color:#57534E;font-family:'Spline Sans',sans-serif;">
                        <input wire:model="answers.{{ $q->id }}" type="checkbox" value="{{ $opt }}"
                            style="accent-color:var(--rsvp-accent);width:15px;height:15px;flex-shrink:0;" />
                        {{ $opt }}
                    </label>
                    @endforeach
                </div>
                @endif

                @error("answers.{$q->id}")
                <span class="rsvp-error-msg">{{ $message }}</span>
                @enderror
            </div>
            @endforeach
            @endif

            <div style="margin-top:4px;">
                <button wire:click="submit" wire:loading.attr="disabled" class="rsvp-submit">
                    <span wire:loading.remove wire:target="submit">Submit RSVP</span>
                    <span wire:loading wire:target="submit">Submitting...</span>
                </button>
            </div>

            @endif

            <div class="rsvp-powered">
                Powered by <a href="/">Koordli</a>
            </div>

        </div>
    </div>

</div>
</div>