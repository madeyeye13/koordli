<div>
<style>
    .bf-shell { display:grid; grid-template-columns:1fr 1fr; min-height:100vh; }
    .bf-left { position:sticky; top:0; height:100vh; overflow:hidden; display:flex; flex-direction:column; padding:56px; }
    .bf-left-bg { position:absolute; inset:0; background:#1C1917; z-index:0; }
    .bf-left-bg-img { position:absolute; inset:0; background-size:cover; background-position:center; z-index:0; }
    .bf-left-bg-img::after { content:''; position:absolute; inset:0; background:linear-gradient(160deg, rgba(28,25,23,0.5) 0%, rgba(28,25,23,0.82) 100%); }
    .bf-left-content { position:relative; z-index:1; display:flex; flex-direction:column; height:100%; justify-content:space-between; }
    .bf-brand { font-family:'Spline Sans',sans-serif; font-size:12px; font-weight:600; letter-spacing:0.14em; text-transform:uppercase; color:rgba(255,255,255,0.4); }
    .bf-title { font-family:'Fraunces',serif; font-size:clamp(32px,3.5vw,52px); font-weight:600; color:#FAFAF9; line-height:1.08; letter-spacing:-0.02em; margin-bottom:20px; }
    .bf-desc { font-size:14px; color:rgba(255,255,255,0.65); font-family:'Spline Sans',sans-serif; line-height:1.7; margin-bottom:24px; }
    .bf-meta-item { display:flex; align-items:flex-start; gap:10px; font-size:13px; color:rgba(255,255,255,0.65); font-family:'Spline Sans',sans-serif; margin-bottom:10px; }
    .bf-meta-icon { width:15px; height:15px; flex-shrink:0; margin-top:2px; opacity:0.55; }
    .bf-footer { font-size:11px; color:rgba(255,255,255,0.25); font-family:'Spline Sans',sans-serif; }
    .bf-right { padding:56px 48px; overflow-y:auto; background:#FAFAF9; display:flex; align-items:flex-start; justify-content:center; min-height:100vh; }
    .bf-form-wrap { width:100%; max-width:420px; }
    .bf-form-title { font-family:'Fraunces',serif; font-size:26px; font-weight:600; color:#1C1917; margin-bottom:6px; letter-spacing:-0.01em; }
    .bf-form-sub { font-size:13px; color:#78716C; margin-bottom:28px; font-family:'Spline Sans',sans-serif; line-height:1.6; }
    .bf-label { display:block; font-size:11px; font-weight:600; color:#78716C; margin-bottom:6px; font-family:'Spline Sans',sans-serif; letter-spacing:0.05em; text-transform:uppercase; }
    .bf-input { width:100%; background:#fff; border:1px solid #E7E5E4; border-radius:6px; padding:11px 14px; font-size:14px; font-family:'Spline Sans',sans-serif; color:#1C1917; outline:none; transition:border-color 150ms; appearance:none; }
    .bf-input:focus { border-color:#1C1917; }
    .bf-input::placeholder { color:#C4C0BC; }
    .bf-input-group { margin-bottom:18px; }
    .bf-error { font-size:11px; color:#EF4444; margin-top:4px; display:block; }
    .bf-submit { width:100%; padding:13px; border-radius:6px; background:#1C1917; color:#FAFAF9; border:none; font-size:14px; font-weight:600; cursor:pointer; font-family:'Spline Sans',sans-serif; transition:opacity 150ms; }
    .bf-submit:disabled { opacity:0.5; }
    .bf-divider { height:1px; background:#E7E5E4; margin:22px 0; }
    .bf-section-label { font-size:10px; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:#B8B3AF; margin-bottom:16px; font-family:'Spline Sans',sans-serif; }
    .bf-success-icon { font-size:44px; margin-bottom:14px; }
    .bf-success-title { font-family:'Fraunces',serif; font-size:28px; font-weight:600; color:#1C1917; margin-bottom:10px; }
    .bf-success-text { font-size:14px; color:#78716C; line-height:1.7; margin-bottom:24px; font-family:'Spline Sans',sans-serif; }
    .bf-wa-btn { display:block; width:100%; padding:13px; border-radius:6px; background:#25D366; color:#fff; border:none; font-size:14px; font-weight:600; cursor:pointer; font-family:'Spline Sans',sans-serif; text-align:center; text-decoration:none; }
    .bf-powered { font-size:11px; color:#C4C0BC; text-align:center; margin-top:32px; font-family:'Spline Sans',sans-serif; }
    .bf-powered a { color:#A8A29E; text-decoration:none; }
    [x-cloak] { display:none !important; }
    @media (max-width:768px) {
        .bf-shell { grid-template-columns:1fr; }
        .bf-left { position:relative; height:auto; min-height:240px; padding:36px 28px 28px; }
        .bf-right { padding:36px 24px 48px; min-height:auto; }
    }
</style>

<div class="bf-shell">

    {{-- Left Panel --}}
    <div class="bf-left">
        @if($form->hero_image)
        <div class="bf-left-bg-img" style="background-image:url('{{ $form->hero_image }}');"></div>
        @else
        <div class="bf-left-bg"></div>
        @endif

        <div class="bf-left-content">
            <div class="bf-brand">Koordli</div>

            <div>
                <h1 class="bf-title">{{ $form->name }}</h1>
                @if($form->description)
                <p class="bf-desc">{{ $form->description }}</p>
                @endif

                @if($form->tenant_email)
                <div class="bf-meta-item">
                    <svg class="bf-meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <span>{{ $form->tenant_email }}</span>
                </div>
                @endif

                @if($form->tenant_phone)
                <div class="bf-meta-item">
                    <svg class="bf-meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.63A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7a2 2 0 011.72 2z"/>
                    </svg>
                    <span>{{ $form->tenant_phone }}</span>
                </div>
                @endif

                @if($form->tenant_address)
                <div class="bf-meta-item">
                    <svg class="bf-meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
                    </svg>
                    <span>{{ $form->tenant_address }}</span>
                </div>
                @endif
            </div>

            <div class="bf-footer">© {{ date('Y') }} Koordli</div>
        </div>
    </div>

    {{-- Right Panel --}}
    <div class="bf-right">
        <div class="bf-form-wrap">

            @if($submitted)
            {{-- Success --}}
            <div class="bf-success-icon">🎉</div>
            <h2 class="bf-success-title">Enquiry received!</h2>
            <p class="bf-success-text">
                Thank you for reaching out. We've received your enquiry and will get back to you shortly.
            </p>

            @if($whatsappUrl)
            <div style="margin-bottom:16px;">
                <p style="font-size:13px;color:#78716C;margin-bottom:10px;font-family:'Spline Sans',sans-serif;">
                    Want to chat directly? Connect via WhatsApp:
                </p>
                <a href="{{ $whatsappUrl }}" target="_blank" class="bf-wa-btn">
                    💬 Chat on WhatsApp
                </a>
            </div>
            @endif

            @else
            {{-- Form --}}
            @if($error)
            <div style="background:#FEE2E2;border:1px solid #FECACA;border-radius:6px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#DC2626;font-family:'Spline Sans',sans-serif;">
                {{ $error }}
            </div>
            @endif

            <h2 class="bf-form-title">{{ $form->name }}</h2>
            <p class="bf-form-sub">
                {{ $form->description ?: 'Fill in the details below and we\'ll get back to you.' }}
            </p>

            @foreach($form->fields as $field)
            <div class="bf-input-group">
                <label class="bf-label">
                    {{ $field->label }}
                    @if($field->is_required)<span style="color:#EF4444;">*</span>@endif
                </label>

                @if($field->field_type === 'text')
                <input wire:model="answers.{{ $field->id }}" type="text" class="bf-input"
                    placeholder="{{ $field->placeholder }}" />

                @elseif($field->field_type === 'textarea')
                <textarea wire:model="answers.{{ $field->id }}" class="bf-input" rows="4"
                    placeholder="{{ $field->placeholder }}" style="resize:vertical;"></textarea>

                @elseif($field->field_type === 'email')
                <input wire:model="answers.{{ $field->id }}" type="email" class="bf-input"
                    placeholder="{{ $field->placeholder ?: 'your@email.com' }}" />

                @elseif($field->field_type === 'phone')
                <input wire:model="answers.{{ $field->id }}" type="tel" class="bf-input"
                    placeholder="{{ $field->placeholder ?: '+234...' }}" />

                @elseif($field->field_type === 'number')
                <input wire:model="answers.{{ $field->id }}" type="number" class="bf-input"
                    placeholder="{{ $field->placeholder }}" style="max-width:160px;" />

                @elseif($field->field_type === 'date')
                <input wire:model="answers.{{ $field->id }}" type="date" class="bf-input"
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
                        class="bf-input"
                        style="display:flex;align-items:center;justify-content:space-between;cursor:pointer;text-align:left;">
                        <span x-text="val || '{{ $field->placeholder ?: 'Select an option' }}'"
                            :style="!val ? 'color:#C4C0BC' : 'color:#1C1917'"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                            style="flex-shrink:0;color:#A8A29E;" :style="open ? 'transform:rotate(180deg)' : ''">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak
                        style="position:absolute;top:calc(100% + 4px);left:0;right:0;background:#fff;border:1px solid #E7E5E4;border-radius:6px;z-index:50;overflow:hidden;max-height:200px;overflow-y:auto;">
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
                <div style="display:flex;flex-direction:column;gap:10px;margin-top:4px;">
                    @foreach($field->options ?? [] as $opt)
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;color:#57534E;font-family:'Spline Sans',sans-serif;">
                        <input wire:model="answers.{{ $field->id }}" type="radio" value="{{ $opt }}"
                            style="accent-color:#1C1917;width:15px;height:15px;flex-shrink:0;" />
                        {{ $opt }}
                    </label>
                    @endforeach
                </div>

                @elseif($field->field_type === 'checkbox')
                <div style="display:flex;flex-direction:column;gap:10px;margin-top:4px;">
                    @foreach($field->options ?? [] as $opt)
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;color:#57534E;font-family:'Spline Sans',sans-serif;">
                        <input wire:model="answers.{{ $field->id }}" type="checkbox" value="{{ $opt }}"
                            style="accent-color:#1C1917;width:15px;height:15px;flex-shrink:0;" />
                        {{ $opt }}
                    </label>
                    @endforeach
                </div>
                @endif

                @error("answers.{$field->id}")
                <span class="bf-error">{{ $message }}</span>
                @enderror
            </div>
            @endforeach

            <div style="margin-top:8px;">
                <button wire:click="submit" wire:loading.attr="disabled" class="bf-submit">
                    <span wire:loading.remove wire:target="submit">Submit Enquiry</span>
                    <span wire:loading wire:target="submit">Submitting...</span>
                </button>
            </div>
            @endif

            <div class="bf-powered">Powered by <a href="/">Koordli</a></div>
        </div>
    </div>
</div>
</div>