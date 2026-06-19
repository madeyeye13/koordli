<div style="min-height:100vh;background:#FAFAF9;">

    {{-- Hero --}}
    <div style="background:#1C1917;padding:48px 24px;text-align:center;">
        <div style="font-size:12px;font-weight:500;letter-spacing:0.15em;text-transform:uppercase;color:#78716C;margin-bottom:16px;font-family:'Spline Sans',sans-serif;">
            Update Your RSVP
        </div>
        <h1 class="rsvp-heading" style="font-size:clamp(24px,4vw,40px);font-weight:700;color:#FAFAF9;line-height:1.15;margin-bottom:12px;">
            {{ $response->rsvpForm->event->name }}
        </h1>
        @if($response->rsvpForm->event->date)
        <div style="font-size:15px;color:#A8A29E;font-family:'Spline Sans',sans-serif;">
            {{ $response->rsvpForm->event->date->format('l, F j, Y') }}
        </div>
        @endif
    </div>

    <div style="max-width:560px;margin:0 auto;padding:40px 20px;">

        @if($saved)
        {{-- Success --}}
        <div style="text-align:center;padding:40px 0;">
            <div style="font-size:56px;margin-bottom:20px;">✅</div>
            <h2 class="rsvp-heading" style="font-size:28px;font-weight:700;color:#1C1917;margin-bottom:10px;">
                Response Updated
            </h2>
            <p style="font-size:15px;color:#78716C;line-height:1.7;margin-bottom:32px;font-family:'Spline Sans',sans-serif;">
                Your RSVP has been updated successfully.
                @if($response->respondent_email)
                A confirmation has been sent to <strong style="color:#1C1917;">{{ $response->respondent_email }}</strong>.
                @endif
            </p>

            @if($status === 'confirmed' && $response->qr_token)
            <div style="background:#fff;border:1px solid #E7E5E4;border-radius:12px;padding:24px;margin-bottom:24px;">
                <div style="font-size:11px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:#78716C;margin-bottom:12px;font-family:'Spline Sans',sans-serif;">
                    Your Entry Pass
                </div>
                <div style="display:flex;justify-content:center;margin-bottom:10px;">
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(180)->generate($response->qr_token) !!}
                </div>
                <div style="font-size:11px;color:#A8A29E;font-family:monospace;">{{ $response->qr_token }}</div>
            </div>

            <a href="{{ url('/rsvp/ticket/' . $response->qr_token) }}"
                style="display:inline-block;background:#1C1917;color:#FAFAF9;padding:12px 28px;border-radius:8px;text-decoration:none;font-size:14px;font-weight:600;font-family:'Spline Sans',sans-serif;">
                Download Updated Ticket
            </a>
            @endif
        </div>

        @else

        @if($error)
        <div style="background:#FEE2E2;border:1px solid #FECACA;border-radius:8px;padding:14px 18px;margin-bottom:24px;font-size:14px;color:#DC2626;font-family:'Spline Sans',sans-serif;">
            {{ $error }}
        </div>
        @endif

        <div style="background:#fff;border:1px solid #E7E5E4;border-radius:12px;padding:32px;">
            <h2 class="rsvp-heading" style="font-size:22px;font-weight:600;color:#1C1917;margin-bottom:20px;">
                Edit Your Response
            </h2>

            {{-- Attendance toggle --}}
            <div style="margin-bottom:24px;"
                x-data="{ attending: '{{ $status }}' }"
                x-init="$watch('attending', val => $wire.set('status', val))">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:10px;font-family:'Spline Sans',sans-serif;">
                    Will you attend? <span style="color:#EF4444;">*</span>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="button"
                        x-on:click="attending = 'confirmed'"
                        :style="attending === 'confirmed'
                            ? 'flex:1;padding:12px;border-radius:8px;border:2px solid #10B981;background:#F0FDF4;color:#065F46;font-size:14px;font-weight:600;cursor:pointer;font-family:Spline Sans,sans-serif;transition:all 150ms;'
                            : 'flex:1;padding:12px;border-radius:8px;border:2px solid #E7E5E4;background:#fff;color:#78716C;font-size:14px;font-weight:500;cursor:pointer;font-family:Spline Sans,sans-serif;transition:all 150ms;'">
                        ✓ Yes, I'll be there
                    </button>
                    <button type="button"
                        x-on:click="attending = 'declined'"
                        :style="attending === 'declined'
                            ? 'flex:1;padding:12px;border-radius:8px;border:2px solid #EF4444;background:#FEF2F2;color:#DC2626;font-size:14px;font-weight:600;cursor:pointer;font-family:Spline Sans,sans-serif;transition:all 150ms;'
                            : 'flex:1;padding:12px;border-radius:8px;border:2px solid #E7E5E4;background:#fff;color:#78716C;font-size:14px;font-weight:500;cursor:pointer;font-family:Spline Sans,sans-serif;transition:all 150ms;'">
                        ✕ Can't make it
                    </button>
                </div>
            </div>

            {{-- System fields --}}
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:500;color:#57534E;margin-bottom:5px;font-family:'Spline Sans',sans-serif;">
                    Full Name <span style="color:#EF4444;">*</span>
                </label>
                <input wire:model="respondent_name" type="text"
                    class="krd-input @error('respondent_name') krd-input-error @enderror"
                    placeholder="Your full name" />
                @error('respondent_name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:500;color:#57534E;margin-bottom:5px;font-family:'Spline Sans',sans-serif;">
                    Email Address
                </label>
                <input wire:model="respondent_email" type="email" class="krd-input" placeholder="your@email.com" />
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:500;color:#57534E;margin-bottom:5px;font-family:'Spline Sans',sans-serif;">
                    Phone Number
                </label>
                <input wire:model="respondent_phone" type="tel" class="krd-input" placeholder="+234..." />
            </div>

            {{-- Plus one --}}
            <div style="margin-bottom:24px;" x-show="$wire.status === 'confirmed'">
                <label style="display:block;font-size:13px;font-weight:500;color:#57534E;margin-bottom:5px;font-family:'Spline Sans',sans-serif;">
                    Number of Additional Guests
                </label>
                <input wire:model="plus_one_count" type="number" min="0" max="20"
                    class="krd-input" style="max-width:120px;" />
            </div>

            {{-- Custom questions --}}
            @foreach($response->rsvpForm->questions as $q)
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:500;color:#57534E;margin-bottom:5px;font-family:'Spline Sans',sans-serif;">
                    {{ $q->label }}
                    @if($q->is_required)<span style="color:#EF4444;">*</span>@endif
                </label>

                @if(in_array($q->field_type, ['text', 'email', 'phone', 'number', 'date']))
                <input wire:model="answers.{{ $q->id }}"
                    type="{{ $q->field_type === 'phone' ? 'tel' : ($q->field_type === 'number' ? 'number' : ($q->field_type === 'date' ? 'date' : 'text')) }}"
                    class="krd-input" />

                @elseif($q->field_type === 'textarea')
                <textarea wire:model="answers.{{ $q->id }}" class="krd-input" rows="3" style="resize:vertical;"></textarea>

                @elseif($q->field_type === 'yes_no')
                <div style="display:flex;gap:10px;margin-top:4px;"
                    x-data="{ val: '{{ $answers[$q->id] ?? '' }}' }"
                    x-init="$watch('val', v => $wire.set('answers.{{ $q->id }}', v))">
                    <button type="button" x-on:click="val = 'Yes'"
                        :style="val === 'Yes' ? 'padding:8px 20px;border-radius:6px;border:2px solid #10B981;background:#F0FDF4;color:#065F46;font-size:13px;font-weight:600;cursor:pointer;' : 'padding:8px 20px;border-radius:6px;border:2px solid #E7E5E4;background:#fff;color:#78716C;font-size:13px;cursor:pointer;'">Yes</button>
                    <button type="button" x-on:click="val = 'No'"
                        :style="val === 'No' ? 'padding:8px 20px;border-radius:6px;border:2px solid #EF4444;background:#FEF2F2;color:#DC2626;font-size:13px;font-weight:600;cursor:pointer;' : 'padding:8px 20px;border-radius:6px;border:2px solid #E7E5E4;background:#fff;color:#78716C;font-size:13px;cursor:pointer;'">No</button>
                </div>

                @elseif($q->field_type === 'dropdown')
                <div x-data="{ open: false, val: '{{ $answers[$q->id] ?? '' }}', pick(v) { this.val = v; this.open = false; $wire.set('answers.{{ $q->id }}', v); } }"
                    x-on:click.outside="open = false" style="position:relative;">
                    <button type="button" x-on:click="open = !open"
                        x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'" style="width:100%;">
                        <span x-text="val || 'Select an option'" :style="!val ? 'color:#A8A29E' : ''"></span>
                        <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-cloak class="krd-dropdown-menu">
                        @foreach($q->options ?? [] as $opt)
                        <div class="krd-dropdown-option" x-on:click="pick('{{ $opt }}')">{{ $opt }}</div>
                        @endforeach
                    </div>
                </div>

                @elseif($q->field_type === 'radio')
                <div style="display:flex;flex-direction:column;gap:8px;margin-top:4px;">
                    @foreach($q->options ?? [] as $opt)
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#57534E;font-family:'Spline Sans',sans-serif;">
                        <input wire:model="answers.{{ $q->id }}" type="radio" value="{{ $opt }}"
                            style="accent-color:#7C3AED;width:15px;height:15px;" />
                        {{ $opt }}
                    </label>
                    @endforeach
                </div>

                @elseif($q->field_type === 'checkbox')
                <div style="display:flex;flex-direction:column;gap:8px;margin-top:4px;">
                    @foreach($q->options ?? [] as $opt)
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#57534E;font-family:'Spline Sans',sans-serif;">
                        <input wire:model="answers.{{ $q->id }}" type="checkbox" value="{{ $opt }}"
                            style="accent-color:#7C3AED;width:15px;height:15px;" />
                        {{ $opt }}
                    </label>
                    @endforeach
                </div>
                @endif

                @error("answers.{$q->id}")
                <span class="krd-input-error-msg">{{ $message }}</span>
                @enderror
            </div>
            @endforeach

            <button wire:click="update" wire:loading.attr="disabled"
                style="width:100%;padding:14px;border-radius:8px;background:#1C1917;color:#FAFAF9;border:none;font-size:15px;font-weight:600;cursor:pointer;font-family:'Spline Sans',sans-serif;"
                wire:loading.class="opacity-50">
                <span wire:loading.remove wire:target="update">Update My RSVP</span>
                <span wire:loading wire:target="update">Updating...</span>
            </button>
        </div>
        @endif

        <div style="text-align:center;margin-top:32px;font-size:12px;color:#A8A29E;font-family:'Spline Sans',sans-serif;">
            Powered by <a href="/" style="color:#7C3AED;text-decoration:none;">Koordli</a>
        </div>
    </div>
</div>