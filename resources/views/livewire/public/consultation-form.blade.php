<div>
<style>
    .cf-shell { display:grid; grid-template-columns:1fr 1fr; min-height:100vh; }
    .cf-left { position:sticky; top:0; height:100vh; overflow:hidden; display:flex; flex-direction:column; padding:56px; }
    .cf-left-bg { position:absolute; inset:0; background:#1C1917; z-index:0; }
    .cf-left-bg-img { position:absolute; inset:0; background-size:cover; background-position:center; z-index:0; }
    .cf-left-bg-img::after { content:''; position:absolute; inset:0; background:linear-gradient(160deg, rgba(28,25,23,0.5) 0%, rgba(28,25,23,0.82) 100%); }
    .cf-left-content { position:relative; z-index:1; display:flex; flex-direction:column; height:100%; justify-content:space-between; }
    .cf-brand { font-family:'Spline Sans',sans-serif; font-size:12px; font-weight:600; letter-spacing:0.14em; text-transform:uppercase; color:rgba(255,255,255,0.4); }
    .cf-title { font-family:'Fraunces',serif; font-size:clamp(30px,3vw,48px); font-weight:600; color:#FAFAF9; line-height:1.08; letter-spacing:-0.02em; margin-bottom:16px; }
    .cf-desc { font-size:14px; color:rgba(255,255,255,0.65); font-family:'Spline Sans',sans-serif; line-height:1.7; margin-bottom:20px; }
    .cf-meta-item { display:flex; align-items:flex-start; gap:10px; font-size:13px; color:rgba(255,255,255,0.65); font-family:'Spline Sans',sans-serif; margin-bottom:10px; }
    .cf-meta-icon { width:15px; height:15px; flex-shrink:0; margin-top:2px; opacity:0.55; }
    .cf-footer { font-size:11px; color:rgba(255,255,255,0.25); font-family:'Spline Sans',sans-serif; }
    .cf-right { padding:40px 48px; overflow-y:auto; background:#FAFAF9; display:flex; align-items:flex-start; justify-content:center; min-height:100vh; }
    .cf-form-wrap { width:100%; max-width:460px; }
    .cf-form-title { font-family:'Fraunces',serif; font-size:24px; font-weight:600; color:#1C1917; margin-bottom:6px; letter-spacing:-0.01em; }
    .cf-form-sub { font-size:13px; color:#78716C; margin-bottom:24px; font-family:'Spline Sans',sans-serif; line-height:1.6; }
    .cf-label { display:block; font-size:11px; font-weight:600; color:#78716C; margin-bottom:6px; font-family:'Spline Sans',sans-serif; letter-spacing:0.05em; text-transform:uppercase; }
    .cf-input { width:100%; background:#fff; border:1px solid #E7E5E4; border-radius:6px; padding:10px 14px; font-size:14px; font-family:'Spline Sans',sans-serif; color:#1C1917; outline:none; transition:border-color 150ms; appearance:none; }
    .cf-input:focus { border-color:#1C1917; }
    .cf-input::placeholder { color:#C4C0BC; }
    .cf-input-group { margin-bottom:16px; }
    .cf-error { font-size:11px; color:#EF4444; margin-top:4px; display:block; }
    .cf-submit { width:100%; padding:13px; border-radius:6px; background:#1C1917; color:#FAFAF9; border:none; font-size:14px; font-weight:600; cursor:pointer; font-family:'Spline Sans',sans-serif; transition:opacity 150ms; }
    .cf-submit:disabled { opacity:0.5; }
    .cf-divider { height:1px; background:#E7E5E4; margin:20px 0; }
    .cf-section-label { font-size:10px; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:#B8B3AF; margin-bottom:14px; font-family:'Spline Sans',sans-serif; }

    /* Calendar */
    .cf-calendar { background:#fff; border:1px solid #E7E5E4; border-radius:8px; padding:16px; margin-bottom:16px; }
    .cf-cal-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
    .cf-cal-month { font-family:'Fraunces',serif; font-size:16px; font-weight:600; color:#1C1917; }
    .cf-cal-nav { background:none; border:1px solid #E7E5E4; border-radius:6px; width:30px; height:30px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#57534E; transition:background 150ms; }
    .cf-cal-nav:hover { background:#F5F5F4; }
    .cf-cal-grid { display:grid; grid-template-columns:repeat(7, 1fr); gap:2px; }
    .cf-cal-dow { font-size:10px; font-weight:600; letter-spacing:0.06em; text-transform:uppercase; color:#A8A29E; text-align:center; padding:4px 0 8px; font-family:'Spline Sans',sans-serif; }
    .cf-cal-day { aspect-ratio:1; display:flex; align-items:center; justify-content:center; border-radius:6px; font-size:13px; font-family:'Spline Sans',sans-serif; cursor:default; transition:all 150ms; border:1.5px solid transparent; }
    .cf-cal-day.available { cursor:pointer; color:#1C1917; font-weight:500; }
    .cf-cal-day.available:hover { background:#F5F5F4; border-color:#E7E5E4; }
    .cf-cal-day.selected { background:#1C1917 !important; color:#FAFAF9 !important; border-color:#1C1917 !important; font-weight:600; }
    .cf-cal-day.today { border-color:#7C3AED; color:#7C3AED; font-weight:600; }
    .cf-cal-day.past { color:#D6D3D1; }
    .cf-cal-day.holiday { color:#EF4444; text-decoration:line-through; }
    .cf-cal-day.unavailable-day { color:#D6D3D1; }
    .cf-cal-day.fully-booked { color:#F59E0B; }

    /* Time slots */
    .cf-slots { display:grid; grid-template-columns:repeat(3, 1fr); gap:8px; margin-bottom:16px; }
    .cf-slot { padding:10px 6px; border:1.5px solid #E7E5E4; border-radius:6px; font-size:12px; font-weight:500; text-align:center; cursor:pointer; font-family:'Spline Sans',sans-serif; color:#57534E; transition:all 150ms; background:#fff; }
    .cf-slot:hover { border-color:#1C1917; color:#1C1917; }
    .cf-slot.selected { background:#1C1917; color:#FAFAF9; border-color:#1C1917; }
    .cf-slot.booked { background:#F5F5F4; color:#D6D3D1; cursor:not-allowed; text-decoration:line-through; }

    .cf-success-icon { font-size:44px; margin-bottom:14px; }
    .cf-success-title { font-family:'Fraunces',serif; font-size:28px; font-weight:600; color:#1C1917; margin-bottom:10px; }
    .cf-success-text { font-size:14px; color:#78716C; line-height:1.7; margin-bottom:24px; font-family:'Spline Sans',sans-serif; }
    .cf-booking-box { background:#F5F5F4; border-radius:8px; padding:16px 20px; margin-bottom:20px; }
    .cf-booking-row { display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #E7E5E4; font-size:13px; }
    .cf-booking-row:last-child { border-bottom:none; }
    .cf-booking-key { color:#78716C; }
    .cf-booking-val { font-weight:600; color:#1C1917; }
    .cf-wa-btn { display:block; width:100%; padding:13px; border-radius:6px; background:#25D366; color:#fff; border:none; font-size:14px; font-weight:600; cursor:pointer; font-family:'Spline Sans',sans-serif; text-align:center; text-decoration:none; }
    .cf-powered { font-size:11px; color:#C4C0BC; text-align:center; margin-top:32px; font-family:'Spline Sans',sans-serif; }
    .cf-powered a { color:#A8A29E; text-decoration:none; }
    [x-cloak] { display:none !important; }
    @media (max-width:768px) {
        .cf-shell { grid-template-columns:1fr; }
        .cf-left { position:relative; height:auto; min-height:220px; padding:32px 24px; }
        .cf-right { padding:32px 20px 48px; min-height:auto; }
        .cf-slots { grid-template-columns:repeat(2, 1fr); }
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
                        <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.63A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7a2 2 0 011.72 2z"/>
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
            {{-- Success --}}
            <div class="cf-success-icon">🎉</div>
            <h2 class="cf-success-title">Consultation booked!</h2>
            <p class="cf-success-text">
                Your consultation has been scheduled. You'll receive a confirmation email shortly.
            </p>

            <div class="cf-booking-box">
                <div class="cf-booking-row">
                    <span class="cf-booking-key">Date</span>
                    <span class="cf-booking-val">{{ \Carbon\Carbon::parse($selectedDate)->format('D, d M Y') }}</span>
                </div>
                <div class="cf-booking-row">
                    <span class="cf-booking-key">Time</span>
                    <span class="cf-booking-val">{{ \Carbon\Carbon::parse($selectedTime)->format('g:i A') }}</span>
                </div>
                <div class="cf-booking-row">
                    <span class="cf-booking-key">Type</span>
                    <span class="cf-booking-val">{{ ucfirst($consultation_type) }}</span>
                </div>
                @if($consultation_type === 'physical' && $form->location)
                <div class="cf-booking-row">
                    <span class="cf-booking-key">Location</span>
                    <span class="cf-booking-val">{{ $form->location }}</span>
                </div>
                @endif
            </div>

            @if($whatsappUrl)
            <a href="{{ $whatsappUrl }}" target="_blank" class="cf-wa-btn" style="margin-bottom:12px;">
                💬 Chat on WhatsApp
            </a>
            @endif

            @else
            {{-- Form --}}
            @if($error)
            <div style="background:#FEE2E2;border:1px solid #FECACA;border-radius:6px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#DC2626;font-family:'Spline Sans',sans-serif;">
                {{ $error }}
            </div>
            @endif

            <h2 class="cf-form-title">Book a Consultation</h2>
            <p class="cf-form-sub">Select a date and time that works for you.</p>

            {{-- Consultation type toggle (if both) --}}
            @if($form->consultation_type === 'both')
            <div class="cf-input-group">
                <label class="cf-label">Consultation Type</label>
                <div style="display:flex;gap:8px;"
                    x-data="{ type: '{{ $consultation_type }}' }"
                    x-init="$watch('type', val => $wire.set('consultation_type', val))">
                    <button type="button"
                        x-on:click="type = 'physical'"
                        :style="type === 'physical'
                            ? 'flex:1;padding:11px;border-radius:6px;border:1.5px solid #1C1917;background:#1C1917;color:#FAFAF9;font-size:13px;font-weight:600;cursor:pointer;font-family:Spline Sans,sans-serif;'
                            : 'flex:1;padding:11px;border-radius:6px;border:1.5px solid #E7E5E4;background:#fff;color:#78716C;font-size:13px;cursor:pointer;font-family:Spline Sans,sans-serif;'">
                        📍 In Person
                    </button>
                    <button type="button"
                        x-on:click="type = 'virtual'"
                        :style="type === 'virtual'
                            ? 'flex:1;padding:11px;border-radius:6px;border:1.5px solid #1C1917;background:#1C1917;color:#FAFAF9;font-size:13px;font-weight:600;cursor:pointer;font-family:Spline Sans,sans-serif;'
                            : 'flex:1;padding:11px;border-radius:6px;border:1.5px solid #E7E5E4;background:#fff;color:#78716C;font-size:13px;cursor:pointer;font-family:Spline Sans,sans-serif;'">
                        💻 Virtual
                    </button>
                </div>
            </div>
            @endif

            {{-- Calendar --}}
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
                    <div class="cf-cal-day
                        {{ $day['isSelected'] ? 'selected' : '' }}
                        {{ $day['isToday'] && !$day['isSelected'] ? 'today' : '' }}
                        {{ $day['isPast'] ? 'past' : '' }}
                        {{ $day['isHoliday'] ? 'holiday' : '' }}
                        {{ $day['fullyBooked'] ? 'fully-booked' : '' }}
                        {{ $day['isAvailable'] ? 'available' : '' }}
                        {{ !$day['isAvailable'] && !$day['isPast'] && !$day['isHoliday'] ? 'unavailable-day' : '' }}"
                        @if($day['isAvailable']) wire:click="selectDate('{{ $day['date'] }}')" @endif
                        title="{{ $day['isHoliday'] ? ($day['holidayNote'] ?? 'Holiday') : ($day['fullyBooked'] ? 'Fully booked' : '') }}"
                    >
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
                        <span style="color:#EF4444;font-size:11px;">—</span> Holiday
                    </div>
                    <div style="display:flex;align-items:center;gap:4px;font-size:11px;color:#A8A29E;font-family:'Spline Sans',sans-serif;">
                        <span style="color:#F59E0B;font-size:11px;">●</span> Full
                    </div>
                </div>
            </div>

            {{-- Time Slots --}}
            @if($selectedDate && !empty($availableSlots))
            <div class="cf-input-group">
                <label class="cf-label">Available Times — {{ \Carbon\Carbon::parse($selectedDate)->format('D, d M') }}</label>
                <div class="cf-slots">
                    @foreach($availableSlots as $slot)
                    <button type="button"
                        wire:click="{{ $slot['available'] ? 'selectTime(\'' . $slot['time'] . '\')' : '' }}"
                        class="cf-slot {{ $selectedTime === $slot['time'] ? 'selected' : '' }} {{ !$slot['available'] ? 'booked' : '' }}"
                        {{ !$slot['available'] ? 'disabled' : '' }}
                        title="{{ !$slot['available'] ? 'This slot is booked' : '' }}">
                        {{ $slot['label'] }}
                    </button>
                    @endforeach
                </div>
            </div>
            @elseif($selectedDate && empty($availableSlots))
            <div style="background:#FEF3C7;border:1px solid #FDE68A;border-radius:6px;padding:12px 14px;margin-bottom:16px;font-size:13px;color:#92400E;font-family:'Spline Sans',sans-serif;">
                ⚠️ No available slots for this date. Please select another day.
            </div>
            @endif

            {{-- Divider --}}
            @if($selectedDate && $selectedTime)
            <div class="cf-divider"></div>
            <div class="cf-section-label">Your Details</div>

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
                <input wire:model="answers.{{ $field->id }}" type="number" class="cf-input"
                    style="max-width:160px;" />

                @elseif($field->field_type === 'date')
                <input wire:model="answers.{{ $field->id }}" type="date" class="cf-input"
                    style="max-width:200px;" />

                @elseif($field->field_type === 'dropdown')
                <div x-data="{
                        open: false,
                        val: '',
                        pick(v) { this.val = v; this.open = false; $wire.set('answers.{{ $field->id }}', v); }
                    }"
                    x-on:click.outside="open = false"
                    style="position:relative;">
                    <button type="button" x-on:click="open = !open"
                        class="cf-input"
                        style="display:flex;align-items:center;justify-content:space-between;cursor:pointer;text-align:left;">
                        <span x-text="val || '{{ $field->placeholder ?: 'Select' }}'" :style="!val ? 'color:#C4C0BC' : ''"></span>
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
                <span class="cf-error">{{ $message }}</span>
                @enderror
            </div>
            @endforeach

            <div style="margin-top:8px;">
                <button wire:click="submit" wire:loading.attr="disabled" class="cf-submit">
                    <span wire:loading.remove wire:target="submit">Confirm Booking</span>
                    <span wire:loading wire:target="submit">Booking...</span>
                </button>
            </div>
            @elseif(!$selectedDate)
            <div style="text-align:center;padding:20px 0;font-size:13px;color:#A8A29E;font-family:'Spline Sans',sans-serif;">
                👆 Select a date above to see available times
            </div>
            @elseif($selectedDate && !$selectedTime)
            <div style="text-align:center;padding:20px 0;font-size:13px;color:#A8A29E;font-family:'Spline Sans',sans-serif;">
                👆 Select a time slot to continue
            </div>
            @endif

            @endif

            <div class="cf-powered">Powered by <a href="/">Koordli</a></div>
        </div>
    </div>
</div>
</div>