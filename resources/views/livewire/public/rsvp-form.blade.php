<div>
<style>
    :root {
        --rsvp-accent: {{ $rsvpForm->branding['accent_color'] ?? '#1C1917' }};
        --rsvp-bg: {{ $rsvpForm->branding['bg_color'] ?? '#FAFAF9' }};
    }
    .rsvp-page { min-height: 100vh; background: var(--rsvp-bg); }

    .rsvp-header { text-align: center; padding: 48px 20px 20px; }
    .rsvp-brand { font-family: 'Spline Sans', sans-serif; font-size: 12px; font-weight: 600; letter-spacing: 0.14em; text-transform: uppercase; color: #A8A29E; margin-bottom: 20px; }
    .rsvp-invited-label { font-family: 'Spline Sans', sans-serif; font-size: 11px; font-weight: 500; letter-spacing: 0.14em; text-transform: uppercase; color: #A8A29E; margin-bottom: 12px; }
    .rsvp-event-name { font-family: 'Fraunces', serif; font-size: clamp(30px, 5vw, 46px); font-weight: 400; color: #1C1917; line-height: 1.1; letter-spacing: -0.02em; margin-bottom: 10px; }
    .rsvp-event-meta { font-size: 13px; color: #78716C; font-family: 'Spline Sans', sans-serif; }

    .rsvp-form-wrap { max-width: 420px; margin: 0 auto; padding: 20px 20px 80px; }

    /* Step progress dots */
    .rsvp-steps { display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 32px; }
    .rsvp-step-dot { width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; font-family: 'Spline Sans', sans-serif; background: #E7E5E4; color: #A8A29E; transition: all 200ms ease; }
    .rsvp-step-dot.active { background: var(--rsvp-accent); color: #fff; }
    .rsvp-step-dot.done { background: #1C1917; color: #fff; }
    .rsvp-step-line { width: 24px; height: 1px; background: #E7E5E4; }
    .rsvp-step-label { text-align: center; font-size: 11px; color: #A8A29E; font-family: 'Spline Sans', sans-serif; letter-spacing: 0.06em; text-transform: uppercase; margin-bottom: 28px; }

    .rsvp-form-title { font-family: 'Fraunces', serif; font-size: 22px; font-weight: 600; color: #1C1917; margin-bottom: 6px; letter-spacing: -0.02em; }
    .rsvp-form-subtitle { font-size: 13px; color: #78716C; margin-bottom: 26px; font-family: 'Spline Sans', sans-serif; line-height: 1.6; }

    .rsvp-label { display: block; font-size: 11px; font-weight: 600; color: #78716C; margin-bottom: 6px; font-family: 'Spline Sans', sans-serif; letter-spacing: 0.05em; text-transform: uppercase; }
    .rsvp-input { width: 100%; background: #fff; border: 1px solid #E7E5E4; border-radius: 6px; padding: 12px 14px; font-size: 14px; font-family: 'Spline Sans', sans-serif; color: #1C1917; outline: none; transition: border-color 150ms ease; appearance: none; }
    .rsvp-input:focus { border-color: var(--rsvp-accent); }
    .rsvp-input::placeholder { color: #C4C0BC; }
    .rsvp-input-group { margin-bottom: 18px; }
    .rsvp-input-hint { font-size: 11px; color: #A8A29E; margin-top: 5px; font-family: 'Spline Sans', sans-serif; }
    .rsvp-error-msg { font-size: 11px; color: #EF4444; margin-top: 4px; display: block; }

    .rsvp-btn-attend { flex: 1; padding: 13px 16px; border-radius: 6px; border: 1.5px solid #E7E5E4; background: #fff; color: #78716C; font-size: 13.5px; font-weight: 500; cursor: pointer; font-family: 'Spline Sans', sans-serif; transition: all 150ms ease; text-align: center; }
    .rsvp-btn-yes { border-color: var(--rsvp-accent); background: var(--rsvp-accent); color: #fff; }
    .rsvp-btn-no { border-color: #EF4444; background: #FEF2F2; color: #DC2626; }

    .rsvp-nav-row { display: flex; gap: 10px; margin-top: 28px; }
    .rsvp-next-btn { flex: 1; padding: 13px; border-radius: 6px; background: var(--rsvp-accent); color: #fff; border: none; font-size: 14px; font-weight: 600; cursor: pointer; font-family: 'Spline Sans', sans-serif; }
    .rsvp-next-btn:disabled { opacity: 0.5; cursor: not-allowed; }
    .rsvp-back-btn { padding: 13px 18px; border-radius: 6px; background: #fff; color: #78716C; border: 1px solid #E7E5E4; font-size: 14px; font-weight: 500; cursor: pointer; font-family: 'Spline Sans', sans-serif; }

    .rsvp-dropdown-menu { position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: #fff; border: 1px solid #E7E5E4; border-radius: 6px; z-index: 50; overflow: hidden; max-height: 200px; overflow-y: auto; }
    .rsvp-dropdown-option { padding: 10px 14px; font-size: 13px; color: #1C1917; cursor: pointer; font-family: 'Spline Sans', sans-serif; }
    .rsvp-dropdown-option:hover { background: #F5F5F4; }
    .rsvp-dropdown-option.selected { background: #F5F5F4; font-weight: 500; }

    .rsvp-success-icon { font-size: 44px; margin-bottom: 14px; text-align: center; }
    .rsvp-success-title { font-family: 'Fraunces', serif; font-size: 26px; font-weight: 600; color: #1C1917; margin-bottom: 10px; letter-spacing: -0.02em; text-align: center; }
    .rsvp-success-text { font-size: 14px; color: #78716C; line-height: 1.7; margin-bottom: 24px; font-family: 'Spline Sans', sans-serif; text-align: center; }
    .rsvp-qr-box { background: #fff; border: 1px solid #E7E5E4; border-radius: 10px; padding: 28px; text-align: center; margin-bottom: 16px; }
    .rsvp-qr-label { font-size: 10px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: #A8A29E; margin-bottom: 16px; font-family: 'Spline Sans', sans-serif; }
    .rsvp-qr-token { font-size: 11px; color: #C4C0BC; font-family: monospace; letter-spacing: 0.06em; margin-top: 10px; }
    .rsvp-ticket-btn { display: block; width: 100%; padding: 13px; border-radius: 6px; background: #F5F5F4; color: #1C1917; border: 1px solid #E7E5E4; font-size: 13px; font-weight: 500; text-align: center; text-decoration: none; margin-bottom: 12px; font-family: 'Spline Sans', sans-serif; }
    .rsvp-edit-link { font-size: 12px; color: #A8A29E; text-align: center; display: block; text-decoration: none; font-family: 'Spline Sans', sans-serif; }
    .rsvp-powered { font-size: 11px; color: #C4C0BC; text-align: center; margin-top: 36px; font-family: 'Spline Sans', sans-serif; }
    .rsvp-powered a { color: #A8A29E; text-decoration: none; }
</style>

@php
    $__tenant = \App\Models\Central\Tenant::find($rsvpForm->tenant_id);
    $__whiteLabel = app(\App\Services\FeatureGateService::class)->canAccess($__tenant, 'white_label');
    $__hasCustomQuestions = ($rsvpForm->customQuestions ?? collect())->isNotEmpty();
    $__totalSteps = $__hasCustomQuestions ? 3 : 2;
@endphp

<div class="rsvp-page" x-data="{
    step: 1,
    total: {{ $__totalSteps }},
    canNext() {
        if (this.step === 1) return $wire.respondent_name.trim().length >= 2;
        return true;
    }
}">
    <div class="rsvp-header">
        <div class="rsvp-brand">{{ $__whiteLabel ? $__tenant->name : 'Koordli' }}</div>
        <div class="rsvp-invited-label">You're Invited</div>
        <h1 class="rsvp-event-name">{{ $rsvpForm->event->name }}</h1>
        <div class="rsvp-event-meta">
            @if($rsvpForm->event->date){{ $rsvpForm->event->date->format('l, F j, Y') }}@endif
            @if($rsvpForm->event->venue) &middot; {{ $rsvpForm->event->venue }} @endif
        </div>
    </div>

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
        <a href="{{ url('/rsvp/ticket/' . $response->qr_token) }}" class="rsvp-ticket-btn">↓ Download Ticket</a>
        @else
        <div class="rsvp-success-icon">💌</div>
        <h2 class="rsvp-success-title">Response recorded</h2>
        <p class="rsvp-success-text">
            Thank you for letting us know, <strong style="color:#1C1917;">{{ $response->respondent_name }}</strong>.
            We're sorry you won't be able to make it.
        </p>
        @endif
        <a href="{{ $response->editUrl() }}" class="rsvp-edit-link">Need to update your response? →</a>

        @else
        {{-- ── Multi-step form ── --}}

        @if($error)
        <div style="background:#FEE2E2;border:1px solid #FECACA;border-radius:6px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#DC2626;font-family:'Spline Sans',sans-serif;">
            {{ $error }}
        </div>
        @endif

        {{-- Progress dots --}}
        <div class="rsvp-steps">
            <template x-for="n in total" :key="n">
                <div style="display:flex;align-items:center;gap:8px;">
                    <div class="rsvp-step-dot" :class="{ active: step === n, done: step > n }" x-text="step > n ? '✓' : n"></div>
                    <div class="rsvp-step-line" x-show="n < total"></div>
                </div>
            </template>
        </div>
        <div class="rsvp-step-label">Step <span x-text="step"></span> of <span x-text="total"></span></div>

        {{-- STEP 1 — Your Details --}}
        <div x-show="step === 1">
            <h2 class="rsvp-form-title">Your Details</h2>
            <p class="rsvp-form-subtitle">Let's start with who you are.</p>

            <div class="rsvp-input-group">
                <label class="rsvp-label">Full Name <span style="color:#EF4444;">*</span></label>
                <input wire:model="respondent_name" type="text" class="rsvp-input" placeholder="Your full name">
                @error('respondent_name') <span class="rsvp-error-msg">{{ $message }}</span> @enderror
            </div>
            <div class="rsvp-input-group">
                <label class="rsvp-label">Email Address</label>
                <input wire:model="respondent_email" type="email" class="rsvp-input" placeholder="your@email.com">
                <div class="rsvp-input-hint">Required to receive your QR entry pass.</div>
            </div>
            <div class="rsvp-input-group">
                <label class="rsvp-label">Phone Number</label>
                <input wire:model="respondent_phone" type="tel" class="rsvp-input" placeholder="+234...">
            </div>

            <div class="rsvp-nav-row">
                <button type="button" x-on:click="if (canNext()) step = 2" class="rsvp-next-btn">Next →</button>
            </div>
        </div>

        {{-- STEP 2 — Attendance --}}
        <div x-show="step === 2">
            <h2 class="rsvp-form-title">Will You Attend?</h2>
            <p class="rsvp-form-subtitle">Let us know if you'll be joining us.</p>

            <div class="rsvp-input-group" x-data="{ attending: '{{ $status }}' }" x-init="$watch('attending', val => $wire.set('status', val))">
                <div style="display:flex;gap:8px;">
                    <button type="button" x-on:click="attending = 'confirmed'" class="rsvp-btn-attend" :class="{ 'rsvp-btn-yes': attending === 'confirmed' }">✓ Yes, I'll attend</button>
                    <button type="button" x-on:click="attending = 'declined'" class="rsvp-btn-attend" :class="{ 'rsvp-btn-no': attending === 'declined' }">✕ Can't make it</button>
                </div>
            </div>

            <div class="rsvp-input-group" x-show="$wire.status === 'confirmed'" x-cloak>
                <label class="rsvp-label">Additional Guests</label>
                <input wire:model="plus_one_count" type="number" min="0" max="20" class="rsvp-input" placeholder="0" style="max-width:110px;">
                <div class="rsvp-input-hint">Enter 0 if you're coming alone.</div>
            </div>

            <div class="rsvp-nav-row">
                <button type="button" x-on:click="step = 1" class="rsvp-back-btn">← Back</button>
                @if($__hasCustomQuestions)
                <button type="button" x-on:click="step = 3" class="rsvp-next-btn">Next →</button>
                @else
                <button wire:click="submit" wire:loading.attr="disabled" class="rsvp-next-btn">
                    <span wire:loading.remove wire:target="submit">Submit RSVP</span>
                    <span wire:loading wire:target="submit">Submitting...</span>
                </button>
                @endif
            </div>
        </div>

        {{-- STEP 3 — Custom Questions --}}
        @if($__hasCustomQuestions)
        <div x-show="step === 3">
            <h2 class="rsvp-form-title">A Few More Details</h2>
            <p class="rsvp-form-subtitle">Almost done — just a couple more questions.</p>

            @foreach($rsvpForm->customQuestions as $q)
            <div class="rsvp-input-group">
                <label class="rsvp-label">{{ $q->label }} @if($q->is_required)<span style="color:#EF4444;">*</span>@endif</label>

                @if($q->field_type === 'text')
                <input wire:model="answers.{{ $q->id }}" type="text" class="rsvp-input">
                @elseif($q->field_type === 'textarea')
                <textarea wire:model="answers.{{ $q->id }}" class="rsvp-input" rows="3" style="resize:vertical;"></textarea>
                @elseif($q->field_type === 'email')
                <input wire:model="answers.{{ $q->id }}" type="email" class="rsvp-input">
                @elseif($q->field_type === 'phone')
                <input wire:model="answers.{{ $q->id }}" type="tel" class="rsvp-input">
                @elseif($q->field_type === 'number')
                <input wire:model="answers.{{ $q->id }}" type="number" class="rsvp-input" style="max-width:140px;">
                @elseif($q->field_type === 'date')
                <input wire:model="answers.{{ $q->id }}" type="date" class="rsvp-input" style="max-width:200px;">
                @elseif($q->field_type === 'yes_no')
                <div style="display:flex;gap:8px;margin-top:2px;" x-data="{ val: '{{ $answers[$q->id] ?? '' }}' }" x-init="$watch('val', v => $wire.set('answers.{{ $q->id }}', v))">
                    <button type="button" x-on:click="val = 'Yes'" class="rsvp-btn-attend" :class="{ 'rsvp-btn-yes': val === 'Yes' }" style="max-width:90px;flex:none;padding:10px;">Yes</button>
                    <button type="button" x-on:click="val = 'No'" class="rsvp-btn-attend" :class="{ 'rsvp-btn-no': val === 'No' }" style="max-width:90px;flex:none;padding:10px;">No</button>
                </div>
                @elseif($q->field_type === 'dropdown')
                <div x-data="{ open: false, val: '{{ $answers[$q->id] ?? '' }}', pick(v) { this.val = v; this.open = false; $wire.set('answers.{{ $q->id }}', v); } }" x-on:click.outside="open = false" style="position:relative;">
                    <button type="button" x-on:click="open = !open" class="rsvp-input" style="display:flex;align-items:center;justify-content:space-between;cursor:pointer;text-align:left;">
                        <span x-text="val || 'Select an option'" :style="!val ? 'color:#C4C0BC' : 'color:#1C1917'"></span>
                    </button>
                    <div x-show="open" x-cloak class="rsvp-dropdown-menu">
                        @foreach($q->options ?? [] as $opt)
                        <div x-on:click="pick('{{ $opt }}')" class="rsvp-dropdown-option" :class="{ selected: val === '{{ $opt }}' }">{{ $opt }}</div>
                        @endforeach
                    </div>
                </div>
                @elseif($q->field_type === 'radio')
                <div style="display:flex;flex-direction:column;gap:10px;margin-top:4px;">
                    @foreach($q->options ?? [] as $opt)
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;color:#57534E;font-family:'Spline Sans',sans-serif;">
                        <input wire:model="answers.{{ $q->id }}" type="radio" value="{{ $opt }}" style="accent-color:var(--rsvp-accent);width:15px;height:15px;flex-shrink:0;"> {{ $opt }}
                    </label>
                    @endforeach
                </div>
                @elseif($q->field_type === 'checkbox')
                <div style="display:flex;flex-direction:column;gap:10px;margin-top:4px;">
                    @foreach($q->options ?? [] as $opt)
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;color:#57534E;font-family:'Spline Sans',sans-serif;">
                        <input wire:model="answers.{{ $q->id }}" type="checkbox" value="{{ $opt }}" style="accent-color:var(--rsvp-accent);width:15px;height:15px;flex-shrink:0;"> {{ $opt }}
                    </label>
                    @endforeach
                </div>
                @endif

                @error("answers.{$q->id}") <span class="rsvp-error-msg">{{ $message }}</span> @enderror
            </div>
            @endforeach

            <div class="rsvp-nav-row">
                <button type="button" x-on:click="step = 2" class="rsvp-back-btn">← Back</button>
                <button wire:click="submit" wire:loading.attr="disabled" class="rsvp-next-btn">
                    <span wire:loading.remove wire:target="submit">Submit RSVP</span>
                    <span wire:loading wire:target="submit">Submitting...</span>
                </button>
            </div>
        </div>
        @endif

        @endif

        <div class="rsvp-powered">
            @if(!$__whiteLabel)
            Powered by <a href="/">Koordli</a>
            @endif
        </div>
    </div>
</div>
</div>