<div>
<style>
    .cf-shell { display:grid; grid-template-columns:1fr 1fr; min-height:100vh; }
    .cf-left { position:sticky; top:0; height:100vh; overflow:hidden; display:flex; flex-direction:column; padding:56px; }
    .cf-left-bg { position:absolute; inset:0; background:#1C1917; z-index:0; }
    .cf-left-bg-img { position:absolute; inset:0; background-size:cover; background-position:center; z-index:0; }
    .cf-left-bg-img::after { content:''; position:absolute; inset:0; background:linear-gradient(160deg, rgba(28,25,23,0.5) 0%, rgba(28,25,23,0.82) 100%); }
    .cf-left-content { position:relative; z-index:1; display:flex; flex-direction:column; height:100%; justify-content:space-between; }
    .cf-brand { font-family:'Spline Sans',sans-serif; font-size:12px; font-weight:600; letter-spacing:0.14em; text-transform:uppercase; color:rgba(255,255,255,0.4); }
    .cf-title { font-family:'Fraunces',serif; font-size:clamp(28px,3vw,46px); font-weight:600; color:#FAFAF9; line-height:1.08; letter-spacing:-0.02em; margin-bottom:16px; }
    .cf-desc { font-size:14px; color:rgba(255,255,255,0.65); font-family:'Spline Sans',sans-serif; line-height:1.7; margin-bottom:20px; }
    .cf-meta-item { display:flex; align-items:flex-start; gap:10px; font-size:13px; color:rgba(255,255,255,0.65); font-family:'Spline Sans',sans-serif; margin-bottom:10px; }
    .cf-meta-icon { width:15px; height:15px; flex-shrink:0; margin-top:2px; opacity:0.55; }
    .cf-footer { font-size:11px; color:rgba(255,255,255,0.25); font-family:'Spline Sans',sans-serif; }
    .cf-right { padding:40px 48px; overflow-y:auto; background:#FAFAF9; display:flex; align-items:flex-start; justify-content:center; min-height:100vh; }
    .cf-form-wrap { width:100%; max-width:460px; }

    /* Steps */
    .cf-steps { display:flex; align-items:center; gap:0; margin-bottom:28px; }
    .cf-step-item { display:flex; align-items:center; gap:8px; }
    .cf-step-dot { width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; font-family:'Spline Sans',sans-serif; flex-shrink:0; transition:all 300ms; }
    .cf-step-dot.active { background:#1C1917; color:#FAFAF9; }
    .cf-step-dot.completed { background:#10B981; color:#fff; }
    .cf-step-dot.inactive { background:#E7E5E4; color:#A8A29E; }
    .cf-step-label { font-size:12px; font-weight:500; font-family:'Spline Sans',sans-serif; transition:color 300ms; }
    .cf-step-label.active { color:#1C1917; }
    .cf-step-label.inactive { color:#A8A29E; }
    .cf-step-line { flex:1; height:1px; background:#E7E5E4; margin:0 12px; }

    .cf-form-title { font-family:'Fraunces',serif; font-size:22px; font-weight:600; color:#1C1917; margin-bottom:6px; letter-spacing:-0.01em; }
    .cf-form-sub { font-size:13px; color:#78716C; margin-bottom:24px; font-family:'Spline Sans',sans-serif; line-height:1.6; }
    .cf-label { display:block; font-size:11px; font-weight:600; color:#78716C; margin-bottom:6px; font-family:'Spline Sans',sans-serif; letter-spacing:0.05em; text-transform:uppercase; }
    .cf-input { width:100%; background:#fff; border:1px solid #E7E5E4; border-radius:6px; padding:10px 14px; font-size:14px; font-family:'Spline Sans',sans-serif; color:#1C1917; outline:none; transition:border-color 150ms; appearance:none; }
    .cf-input:focus { border-color:#1C1917; }
    .cf-input::placeholder { color:#C4C0BC; }
    .cf-input-group { margin-bottom:16px; }
    .cf-error-msg { font-size:11px; color:#EF4444; margin-top:4px; display:block; }
    .cf-submit { width:100%; padding:13px; border-radius:6px; background:#1C1917; color:#FAFAF9; border:none; font-size:14px; font-weight:600; cursor:pointer; font-family:'Spline Sans',sans-serif; transition:opacity 150ms; }
    .cf-submit:disabled { opacity:0.5; }
    .cf-divider { height:1px; background:#E7E5E4; margin:20px 0; }

    /* Type toggle */
    .cf-type-btn { flex:1; padding:12px 16px; border-radius:6px; border:1.5px solid #E7E5E4; background:#fff; color:#78716C; font-size:13px; font-weight:500; cursor:pointer; font-family:'Spline Sans',sans-serif; transition:all 150ms; text-align:center; }
    .cf-type-btn.active { border-color:#1C1917; background:#1C1917; color:#FAFAF9; }

    /* Calendar */
    .cf-calendar { background:#fff; border:1px solid #E7E5E4; border-radius:8px; padding:16px; margin-bottom:16px; }
    .cf-cal-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
    .cf-cal-month { font-family:'Fraunces',serif; font-size:16px; font-weight:600; color:#1C1917; }
    .cf-cal-nav { background:none; border:1px solid #E7E5E4; border-radius:6px; width:30px; height:30px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#57534E; transition:background 150ms; }
    .cf-cal-nav:hover { background:#F5F5F4; }
    .cf-cal-grid { display:grid; grid-template-columns:repeat(7, 1fr); gap:2px; }
    .cf-cal-dow { font-size:10px; font-weight:600; letter-spacing:0.06em; text-transform:uppercase; color:#A8A29E; text-align:center; padding:4px 0 8px; font-family:'Spline Sans',sans-serif; }
    .cf-cal-day { aspect-ratio:1; display:flex; align-items:center; justify-content:center; border-radius:6px; font-size:13px; font-family:'Spline Sans',sans-serif; cursor:default; transition:all 150ms; border:1.5px solid transparent; position:relative; }
    .cf-cal-day.available { cursor:pointer; color:#1C1917; font-weight:500; }
    .cf-cal-day.available:hover { background:#F5F5F4; }
    .cf-cal-day.past { color:#D6D3D1; }
    .cf-cal-day.holiday { color:#EF4444; }
    .cf-cal-day.fully-booked { color:#F59E0B; }
    .cf-cal-day.today-marker { border-color:#7C3AED; }

    /* Time slots */
    .cf-slots { display:grid; grid-template-columns:repeat(3, 1fr); gap:8px; margin-bottom:16px; }
    .cf-slot { padding:10px 6px; border:1.5px solid #E7E5E4; border-radius:6px; font-size:12px; font-weight:500; text-align:center; cursor:pointer; font-family:'Spline Sans',sans-serif; color:#57534E; transition:all 150ms; background:#fff; }
    .cf-slot:hover { border-color:#1C1917; color:#1C1917; }
    .cf-slot.booked { background:#F5F5F4; color:#D6D3D1; cursor:not-allowed; }

    /* Success */
    .cf-success { text-align:center; padding:20px 0; }
    .cf-success-check { width:72px; height:72px; border-radius:50%; background:#F0FDF4; border:2px solid #86EFAC; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; animation:cf-pop 400ms cubic-bezier(0.34,1.56,0.64,1) forwards; }
    .cf-success-title { font-family:'Fraunces',serif; font-size:26px; font-weight:600; color:#1C1917; margin-bottom:8px; letter-spacing:-0.01em; }
    .cf-success-sub { font-size:14px; color:#78716C; line-height:1.7; margin-bottom:24px; font-family:'Spline Sans',sans-serif; }
    .cf-booking-card { background:#fff; border:1px solid #E7E5E4; border-radius:10px; overflow:hidden; margin-bottom:20px; }
    .cf-booking-card-header { background:#1C1917; padding:14px 20px; }
    .cf-booking-card-title { font-family:'Fraunces',serif; font-size:14px; font-weight:600; color:#FAFAF9; }
    .cf-booking-card-sub { font-size:11px; color:rgba(255,255,255,0.45); margin-top:2px; font-family:'Spline Sans',sans-serif; }
    .cf-booking-row { display:flex; justify-content:space-between; align-items:center; padding:12px 20px; border-bottom:1px solid #F5F5F4; }
    .cf-booking-row:last-child { border-bottom:none; }
    .cf-booking-key { font-size:12px; color:#A8A29E; font-family:'Spline Sans',sans-serif; }
    .cf-booking-val { font-size:13px; font-weight:600; color:#1C1917; font-family:'Spline Sans',sans-serif; }
    .cf-wa-btn { display:flex; align-items:center; justify-content:center; gap:8px; width:100%; padding:13px; border-radius:6px; background:#25D366; color:#fff; border:none; font-size:14px; font-weight:600; cursor:pointer; font-family:'Spline Sans',sans-serif; text-decoration:none; transition:opacity 150ms; }
    .cf-wa-btn:hover { opacity:0.9; }
    .cf-powered { font-size:11px; color:#C4C0BC; text-align:center; margin-top:28px; font-family:'Spline Sans',sans-serif; }
    .cf-powered a { color:#A8A29E; text-decoration:none; }

    @keyframes cf-pop {
        0%   { transform: scale(0); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    @keyframes cf-fade-up {
        0%   { transform: translateY(16px); opacity: 0; }
        100% { transform: translateY(0);    opacity: 1; }
    }
    .cf-animate-1 { animation: cf-fade-up 400ms ease 100ms both; }
    .cf-animate-2 { animation: cf-fade-up 400ms ease 200ms both; }
    .cf-animate-3 { animation: cf-fade-up 400ms ease 300ms both; }
    .cf-animate-4 { animation: cf-fade-up 400ms ease 400ms both; }

    [x-cloak] { display:none !important; }

    @media (max-width:768px) {
        .cf-shell { grid-template-columns:1fr; }
        .cf-left { position:relative; height:auto; min-height:220px; padding:32px 24px; }
        .cf-right { padding:32px 20px 48px; min-height:auto; }
        .cf-slots { grid-template-columns:repeat(2,1fr); }
    }
</style>

<div class="cf-shell">

    {{-- Left Panel --}}
    <div class="cf-left">
        @if($form->hero_image)
        <div class="cf-left-bg-img" style="background-image:url('{{ $form->hero_image }}');"></div>
        @else
        <div class="cf-left-bg"></div>
        @endif

        <div class="cf-left-content">
            <div class="cf-brand">Koordli</div>
            <div>
                <h1 class="cf-title">{{ $form->name }}</h1>
                @if($form->description)
                <p class="cf-desc">{{ $form->description }}</p>
                @endif

                @if($form->duration_minutes)
                <div class="cf-meta-item">
                    <svg class="cf-meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span>{{ $form->duration_minutes }} minute consultation</span>
                </div>
                @endif
                @if($form->tenant_email)
                <div class="cf-meta-item">
                    <svg class="cf-meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <span>{{ $form->tenant_email }}</span>
                </div>
                @endif
                @if($form->tenant_phone)
                <div class="cf-meta-item">
                    <svg class="cf-meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01-.07 1.18 2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16z"/>
                    </svg>
                    <span>{{ $form->tenant_phone }}</span>
                </div>
                @endif
                @if($form->location && in_array($form->consultation_type, ['physical', 'both']))
                <div class="cf-meta-item">
                    <svg class="cf-meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
                    </svg>
                    <span>{{ $form->location }}</span>
                </div>
                @endif
            </div>
            <div class="cf-footer">© {{ date('Y') }} Koordli</div>
        </div>
    </div>

    {{-- Right Panel --}}
    <div class="cf-right">
        <div class="cf-form-wrap">

            @if($submitted)
            {{-- ── Success ── --}}
            <div class="cf-success">
                <div class="cf-success-check">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2.5">
                        <path d="M20 6L9 17l-5-5"/>
                    </svg>
                </div>

                <h2 class="cf-success-title cf-animate-1">You're all booked!</h2>
                <p class="cf-success-sub cf-animate-2">
                    Your consultation has been scheduled.
                    @if($form->tenant_email) A confirmation has been sent to your email. @endif
                </p>

                <div class="cf-booking-card cf-animate-3">
                    <div class="cf-booking-card-header">
                        <div class="cf-booking-card-title">{{ $form->name }}</div>
                        <div class="cf-booking-card-sub">Booking Confirmation</div>
                    </div>
                    <div class="cf-booking-row">
                        <span class="cf-booking-key">📅 Date</span>
                        <span class="cf-booking-val">{{ \Carbon\Carbon::parse($selectedDate)->format('D, d M Y') }}</span>
                    </div>
                    <div class="cf-booking-row">
                        <span class="cf-booking-key">🕐 Time</span>
                        <span class="cf-booking-val">{{ \Carbon\Carbon::parse($selectedTime)->format('g:i A') }}</span>
                    </div>
                    <div class="cf-booking-row">
                        <span class="cf-booking-key">📋 Type</span>
                        <span class="cf-booking-val">{{ ucfirst($consultation_type) }} Consultation</span>
                    </div>
                    @if($consultation_type === 'physical' && $form->location)
                    <div class="cf-booking-row">
                        <span class="cf-booking-key">📍 Location</span>
                        <span class="cf-booking-val" style="text-align:right;max-width:60%;">{{ $form->location }}</span>
                    </div>
                    @endif
                    <div class="cf-booking-row">
                        <span class="cf-booking-key">⏱ Duration</span>
                        <span class="cf-booking-val">{{ $form->duration_minutes }} minutes</span>
                    </div>
                </div>

                @if($whatsappUrl)
                <a href="{{ $whatsappUrl }}" target="_blank" class="cf-wa-btn cf-animate-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Chat on WhatsApp
                </a>
                @endif
            </div>

            @else

            {{-- ── Step Indicator ── --}}
            <div class="cf-steps">
                <div class="cf-step-item">
                    <div class="cf-step-dot {{ $step >= 1 ? ($step > 1 ? 'completed' : 'active') : 'inactive' }}">
                        @if($step > 1)
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                        @else
                        1
                        @endif
                    </div>
                    <span class="cf-step-label {{ $step >= 1 ? 'active' : 'inactive' }}">Date & Time</span>
                </div>
                <div class="cf-step-line"></div>
                <div class="cf-step-item">
                    <div class="cf-step-dot {{ $step >= 2 ? 'active' : 'inactive' }}">2</div>
                    <span class="cf-step-label {{ $step >= 2 ? 'active' : 'inactive' }}">Your Details</span>
                </div>
            </div>

            {{-- Error --}}
            @if($error)
            <div style="background:#FEE2E2;border:1px solid #FECACA;border-radius:6px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#DC2626;font-family:'Spline Sans',sans-serif;">
                {{ $error }}
            </div>
            @endif

            {{-- ── STEP 1: Date & Time ── --}}
            @if($step === 1)

            {{-- Consultation type --}}
            @if($form->consultation_type === 'both')
            <div class="cf-input-group">
                <label class="cf-label">Consultation Type</label>
                <div style="display:flex;gap:8px;"
                    x-data="{
                        type: '{{ $consultation_type }}',
                        setType(val) {
                            this.type = val;
                            $wire.setConsultationType(val);
                        }
                    }">
                    <button type="button"
                        x-on:click="setType('physical')"
                        :class="type === 'physical' ? 'cf-type-btn active' : 'cf-type-btn'">
                        📍 In Person
                    </button>
                    <button type="button"
                        x-on:click="setType('virtual')"
                        :class="type === 'virtual' ? 'cf-type-btn active' : 'cf-type-btn'">
                        💻 Virtual
                    </button>
                </div>
            </div>
            @endif

            {{-- Calendar --}}
            <div class="cf-input-group">
                <label class="cf-label">Select a Date</label>
                <div class="cf-calendar">
                    <div class="cf-cal-header">
                        <button type="button" wire:click="prevMonth" class="cf-cal-nav">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <div class="cf-cal-month">{{ $currentMonth }} {{ $currentYear }}</div>
                        <button type="button" wire:click="nextMonth" class="cf-cal-nav">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    </div>

                    <div class="cf-cal-grid">
                        @foreach(['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'] as $dow)
                        <div class="cf-cal-dow">{{ $dow }}</div>
                        @endforeach

                        @foreach($calendarDays as $day)
                        @if($day === null)
                        <div></div>
                        @else
                        @php
                            $classes = 'cf-cal-day';
                            if ($day['isSelected'])     $classes .= ' active';
                            elseif ($day['isPast'])     $classes .= ' past';
                            elseif ($day['isHoliday'])  $classes .= ' holiday';
                            elseif ($day['fullyBooked'])$classes .= ' fully-booked';
                            elseif ($day['isAvailable'])$classes .= ' available';
                            if ($day['isToday'] && !$day['isSelected']) $classes .= ' today-marker';
                        @endphp
                        <div class="{{ $classes }}"
                            @if($day['isAvailable']) wire:click="selectDate('{{ $day['date'] }}')" @endif
                            title="{{ $day['isHoliday'] ? 'Public holiday' : ($day['fullyBooked'] ? 'Fully booked' : '') }}"
                            style="{{ $day['isSelected'] ? 'background:#1C1917;color:#FAFAF9;font-weight:600;border-color:#1C1917;' : '' }}">
                            {{ $day['day'] }}
                        </div>
                        @endif
                        @endforeach
                    </div>

                    {{-- Legend --}}
                    <div style="display:flex;gap:12px;margin-top:12px;flex-wrap:wrap;">
                        <div style="display:flex;align-items:center;gap:4px;font-size:11px;color:#A8A29E;font-family:'Spline Sans',sans-serif;">
                            <div style="width:10px;height:10px;border-radius:3px;background:#1C1917;"></div> Selected
                        </div>
                        <div style="display:flex;align-items:center;gap:4px;font-size:11px;color:#A8A29E;font-family:'Spline Sans',sans-serif;">
                            <div style="width:10px;height:10px;border-radius:3px;border:1.5px solid #7C3AED;"></div> Today
                        </div>
                        <div style="display:flex;align-items:center;gap:4px;font-size:11px;color:#A8A29E;font-family:'Spline Sans',sans-serif;">
                            <span style="color:#EF4444;font-size:11px;font-weight:700;">×</span> Holiday
                        </div>
                        <div style="display:flex;align-items:center;gap:4px;font-size:11px;color:#A8A29E;font-family:'Spline Sans',sans-serif;">
                            <span style="color:#F59E0B;font-size:11px;font-weight:700;">●</span> Full
                        </div>
                    </div>
                </div>
            </div>

            {{-- Time Slots --}}
            @if($selectedDate)
            <div class="cf-input-group">
                <label class="cf-label">
                    Select a Time — {{ \Carbon\Carbon::parse($selectedDate)->format('D, d M') }}
                </label>
                @if(empty($availableSlots))
                <div style="background:#FEF3C7;border:1px solid #FDE68A;border-radius:6px;padding:12px 14px;font-size:13px;color:#92400E;font-family:'Spline Sans',sans-serif;">
                    ⚠️ No available slots for this date. Please select another day.
                </div>
                @else
                <div class="cf-slots">
                    @foreach($availableSlots as $slot)
                    <button type="button"
                        @if($slot['available']) wire:click="selectTime('{{ $slot['time'] }}')" @endif
                        class="cf-slot {{ $selectedTime === $slot['time'] ? 'active' : '' }} {{ !$slot['available'] ? 'booked' : '' }}"
                        style="{{ $selectedTime === $slot['time'] ? 'background:#1C1917;color:#FAFAF9;border-color:#1C1917;font-weight:600;' : '' }}"
                        {{ !$slot['available'] ? 'disabled' : '' }}
                        title="{{ !$slot['available'] ? 'Booked' : '' }}">
                        {{ $slot['label'] }}
                    </button>
                    @endforeach
                </div>
                @endif
            </div>
            @else
            <div style="text-align:center;padding:16px 0;font-size:13px;color:#A8A29E;font-family:'Spline Sans',sans-serif;">
                Select a date above to see available times
            </div>
            @endif

            {{-- Continue button --}}
            <button wire:click="proceedToStep2"
                style="width:100%;padding:13px;border-radius:6px;background:#1C1917;color:#FAFAF9;border:none;font-size:14px;font-weight:600;cursor:pointer;font-family:'Spline Sans',sans-serif;opacity:{{ $selectedDate && $selectedTime ? '1' : '0.4' }};"
                {{ !$selectedDate || !$selectedTime ? 'disabled' : '' }}>
                Continue →
            </button>

            @endif

            {{-- ── STEP 2: Your Details ── --}}
            @if($step === 2)

            {{-- Booking summary strip --}}
            <div style="background:#F5F5F4;border-radius:8px;padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;color:#A8A29E;font-family:'Spline Sans',sans-serif;margin-bottom:2px;">Your selected slot</div>
                    <div style="font-size:13px;font-weight:600;color:#1C1917;font-family:'Spline Sans',sans-serif;">
                        {{ \Carbon\Carbon::parse($selectedDate)->format('D, d M Y') }} at {{ \Carbon\Carbon::parse($selectedTime)->format('g:i A') }}
                    </div>
                </div>
                <button wire:click="backToStep1"
                    style="font-size:12px;color:#7C3AED;background:none;border:none;cursor:pointer;font-family:'Spline Sans',sans-serif;white-space:nowrap;text-decoration:underline;">
                    Change
                </button>
            </div>

            {{-- Fields --}}
            @foreach($form->fields as $field)
            <div class="cf-input-group">
                <label class="cf-label">
                    {{ $field->label }}
                    @if($field->is_required)<span style="color:#EF4444;">*</span>@endif
                </label>

                @if($field->field_type === 'text')
                <input wire:model="answers.{{ $field->id }}" type="text" class="cf-input"
                    placeholder="{{ $field->placeholder }}" />

                @elseif($field->field_type === 'textarea')
                <textarea wire:model="answers.{{ $field->id }}" class="cf-input" rows="3"
                    placeholder="{{ $field->placeholder }}" style="resize:vertical;"></textarea>

                @elseif($field->field_type === 'email')
                <input wire:model="answers.{{ $field->id }}" type="email" class="cf-input"
                    placeholder="{{ $field->placeholder ?: 'your@email.com' }}" />

                @elseif($field->field_type === 'phone')
                <input wire:model="answers.{{ $field->id }}" type="tel" class="cf-input"
                    placeholder="{{ $field->placeholder ?: '+234...' }}" />

                @elseif($field->field_type === 'number')
                <input wire:model="answers.{{ $field->id }}" type="number" class="cf-input" />

                @elseif($field->field_type === 'date')
                <input wire:model="answers.{{ $field->id }}" type="date" class="cf-input" />

                @elseif($field->field_type === 'dropdown')
                <div x-data="{
                        open: false,
                        label: '{{ $field->placeholder ?: 'Select an option' }}',
                        pick(v) { this.label = v; this.open = false; $wire.set('answers.{{ $field->id }}', v); }
                    }"
                    x-on:click.outside="open = false"
                    style="position:relative;">
                    <button type="button" x-on:click="open = !open"
                        class="cf-input"
                        style="display:flex;align-items:center;justify-content:space-between;cursor:pointer;text-align:left;">
                        <span x-text="label" :style="label === '{{ $field->placeholder ?: 'Select an option' }}' ? 'color:#C4C0BC' : ''"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;color:#A8A29E;"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-cloak style="position:absolute;top:calc(100% + 4px);left:0;right:0;background:#fff;border:1px solid #E7E5E4;border-radius:6px;z-index:50;overflow:hidden;max-height:200px;overflow-y:auto;">
                        @foreach($field->options ?? [] as $opt)
                        <div x-on:click="pick('{{ $opt }}')"
                            style="padding:10px 14px;font-size:13px;color:#1C1917;cursor:pointer;font-family:'Spline Sans',sans-serif;"
                            onmouseover="this.style.background='#F5F5F4'" onmouseout="this.style.background='transparent'">
                            {{ $opt }}
                        </div>
                        @endforeach
                    </div>
                </div>

                @elseif($field->field_type === 'radio')
                <div style="display:flex;flex-direction:column;gap:8px;margin-top:4px;">
                    @foreach($field->options ?? [] as $opt)
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#57534E;font-family:'Spline Sans',sans-serif;">
                        <input wire:model="answers.{{ $field->id }}" type="radio" value="{{ $opt }}"
                            style="accent-color:#1C1917;width:14px;height:14px;flex-shrink:0;" />
                        {{ $opt }}
                    </label>
                    @endforeach
                </div>

                @elseif($field->field_type === 'checkbox')
                <div style="display:flex;flex-direction:column;gap:8px;margin-top:4px;">
                    @foreach($field->options ?? [] as $opt)
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#57534E;font-family:'Spline Sans',sans-serif;">
                        <input wire:model="answers.{{ $field->id }}" type="checkbox" value="{{ $opt }}"
                            style="accent-color:#1C1917;width:14px;height:14px;flex-shrink:0;" />
                        {{ $opt }}
                    </label>
                    @endforeach
                </div>
                @endif

                @error("answers.{$field->id}")
                <span class="cf-error-msg">{{ $message }}</span>
                @enderror
            </div>
            @endforeach

            <button wire:click="submit" wire:loading.attr="disabled" class="cf-submit">
                <span wire:loading.remove wire:target="submit">Confirm Booking</span>
                <span wire:loading wire:target="submit">Confirming...</span>
            </button>

            @endif

            @endif

            <div class="cf-powered">Powered by <a href="/">Koordli</a></div>
        </div>
    </div>
</div>
</div>