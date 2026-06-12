<div style="max-width: 560px; margin: 0 auto; padding: 32px 0;">

    {{-- Dynamic Progress Bar --}}
    @php
        $steps = [];
        if ($needsPassword) $steps[1] = 'Password';
        if ($needsPlan)     $steps[2] = 'Plan';
        $steps[3] = 'Branding';
        $steps[4] = 'Done';
        $stepNumbers = array_keys($steps);
        $stepLabels  = array_values($steps);
        $totalSteps  = count($steps);
        $currentIndex = array_search($step, $stepNumbers);
    @endphp

    <div style="display:flex;align-items:center;gap:8px;margin-bottom:40px;flex-wrap:wrap;">
        @foreach($steps as $s => $label)
        @php $idx = array_search($s, $stepNumbers) + 1; @endphp
        <div style="display:flex;align-items:center;gap:8px;">
            <div style="
                width:28px;height:28px;border-radius:50%;
                display:flex;align-items:center;justify-content:center;
                font-size:11px;font-weight:600;flex-shrink:0;
                background:{{ $step >= $s ? '#7C3AED' : '#E7E5E4' }};
                color:{{ $step >= $s ? '#fff' : '#A8A29E' }};
                transition:all 200ms ease;
            ">
                @if($step > $s) ✓ @else {{ $idx }} @endif
            </div>
            <span style="font-size:12px;color:{{ $step >= $s ? '#1C1917' : '#A8A29E' }};white-space:nowrap;">
                {{ $label }}
            </span>
            @if(!$loop->last)
            <div style="width:32px;height:1px;background:{{ $step > $s ? '#7C3AED' : '#E7E5E4' }};"></div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- ══ STEP 1 — Password ══ --}}
    @if($step === 1 && $needsPassword)
    <div style="margin-bottom:28px;">
        <h2 style="font-size:20px;font-weight:600;color:#1C1917;margin-bottom:6px;">Set your password</h2>
        <p style="font-size:13px;color:#78716C;line-height:1.6;">
            For security, please change your temporary password before continuing.
        </p>
    </div>

    <div class="krd-card" style="padding:24px;">
        <div class="krd-input-group">
            <label class="krd-label-text">Current Password</label>
            <div style="position:relative;">
                <input wire:model="current_password" type="{{ $showCurrent ? 'text' : 'password' }}" class="krd-input @error('current_password') krd-input-error @enderror" placeholder="Your temporary password" style="padding-right:44px;" />
                <button type="button" wire:click="$toggle('showCurrent')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#A8A29E;display:flex;align-items:center;padding:0;">
                    @if($showCurrent)
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    @endif
                </button>
            </div>
            @error('current_password') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
        </div>

        <div class="krd-input-group">
            <label class="krd-label-text">New Password</label>
            <div style="position:relative;">
                <input wire:model="new_password" type="{{ $showNew ? 'text' : 'password' }}" class="krd-input @error('new_password') krd-input-error @enderror" placeholder="Min 8 characters" style="padding-right:44px;" />
                <button type="button" wire:click="$toggle('showNew')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#A8A29E;display:flex;align-items:center;padding:0;">
                    @if($showNew)
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    @endif
                </button>
            </div>
            @error('new_password') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            <span class="krd-input-hint">Must contain uppercase, lowercase and a number.</span>
        </div>

        <div class="krd-input-group" style="margin-bottom:0;">
            <label class="krd-label-text">Confirm New Password</label>
            <div style="position:relative;">
                <input wire:model="new_password_confirmation" type="{{ $showConfirm ? 'text' : 'password' }}" class="krd-input" placeholder="Repeat new password" style="padding-right:44px;" />
                <button type="button" wire:click="$toggle('showConfirm')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#A8A29E;display:flex;align-items:center;padding:0;">
                    @if($showConfirm)
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    @endif
                </button>
            </div>
        </div>
    </div>

    <div style="margin-top:16px;">
        <button wire:click="changePassword" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-lg" style="width:100%;">
            <span wire:loading.remove wire:target="changePassword">Set Password & Continue</span>
            <span wire:loading wire:target="changePassword">Updating...</span>
        </button>
    </div>
    @endif

    {{-- ══ STEP 2 — Plan Selection ══ --}}
    @if($step === 2 && $needsPlan)
    <div style="margin-bottom:28px;">
        <h2 style="font-size:20px;font-weight:600;color:#1C1917;margin-bottom:6px;">Choose your plan</h2>
        <p style="font-size:13px;color:#78716C;line-height:1.6;">
            Select the plan that works best for your business.
        </p>
    </div>

    <div style="display:flex;flex-direction:column;gap:12px;">
        @foreach($plans as $plan)
        <div
            wire:click="selectPlan({{ $plan->id }})"
            style="
                border:2px solid {{ $selected_plan_id === $plan->id ? '#7C3AED' : '#E7E5E4' }};
                border-radius:8px;padding:16px 18px;cursor:pointer;
                transition:all 150ms ease;
                background:{{ $selected_plan_id === $plan->id ? '#F5F3FF' : '#FFFFFF' }};
                position:relative;
            "
        >
            @if($plan->billing_cycle === 'trial')
            <div style="position:absolute;top:-10px;right:16px;background:#7C3AED;color:#fff;font-size:10px;font-weight:600;padding:3px 10px;border-radius:20px;letter-spacing:0.05em;">
                RECOMMENDED
            </div>
            @endif

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;flex-wrap:wrap;gap:8px;">
                <div style="font-size:14px;font-weight:600;color:#1C1917;">{{ $plan->name }}</div>
                <div style="font-size:13px;font-weight:600;color:{{ $plan->billing_cycle === 'trial' ? '#7C3AED' : '#1C1917' }};">
                    @if($plan->billing_cycle === 'trial')
                        Free for {{ $plan->trial_days }} days
                    @else
                        Contact us
                    @endif
                </div>
            </div>

            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;">
                @foreach(($plan->features ?? []) as $key => $value)
                    @if($value === 'true' || (is_numeric($value) && $value > 0) || $value === 'unlimited')
                    <span style="font-size:10px;background:#F5F5F4;color:#57534E;padding:2px 8px;border-radius:4px;font-weight:500;">
                        @if($value === 'true') ✓ {{ str_replace('_', ' ', ucfirst($key)) }}
                        @elseif($value === 'unlimited') ∞ {{ str_replace('_', ' ', ucfirst($key)) }}
                        @else {{ $value }} {{ str_replace('_', ' ', ucfirst($key)) }}
                        @endif
                    </span>
                    @endif
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ══ STEP 3 — Branding ══ --}}
    @if($step === 3)
    <div style="margin-bottom:28px;">
        <h2 style="font-size:20px;font-weight:600;color:#1C1917;margin-bottom:6px;">Set up your branding</h2>
        <p style="font-size:13px;color:#78716C;line-height:1.6;">
            Personalise your workspace. You can update these anytime from Settings.
        </p>
    </div>

    <div class="krd-card" style="padding:24px;">
        <div class="krd-input-group">
            <label class="krd-label-text">Company Logo</label>
            @if($logo)
            <div style="margin-bottom:10px;">
                <img src="{{ $logo->temporaryUrl() }}" style="height:48px;width:auto;border-radius:4px;" />
            </div>
            @endif
            <input wire:model="logo" type="file" accept="image/*" class="krd-input" style="padding:7px 12px;cursor:pointer;" />
            @error('logo') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            <span class="krd-input-hint">PNG, JPG or SVG. Max 2MB.</span>
        </div>

        <div class="krd-input-group">
            <label class="krd-label-text">Primary Color</label>
            <div style="display:flex;align-items:center;gap:10px;">
                <input wire:model.live="primary_color" type="color" style="width:40px;height:36px;border:1px solid #E7E5E4;border-radius:4px;cursor:pointer;padding:2px;" />
                <input wire:model.live="primary_color" type="text" class="krd-input" placeholder="#7C3AED" style="font-family:monospace;text-transform:uppercase;" />
            </div>
        </div>

        <div class="krd-input-group" style="margin-bottom:0;">
            <label class="krd-label-text">Accent Color</label>
            <div style="display:flex;align-items:center;gap:10px;">
                <input wire:model.live="accent_color" type="color" style="width:40px;height:36px;border:1px solid #E7E5E4;border-radius:4px;cursor:pointer;padding:2px;" />
                <input wire:model.live="accent_color" type="text" class="krd-input" placeholder="#F59E0B" style="font-family:monospace;text-transform:uppercase;" />
            </div>
        </div>
    </div>

    {{-- Live preview --}}
    <div style="margin-top:16px;padding:16px;border:1px solid #E7E5E4;border-radius:4px;background:#FAFAF9;">
        <div style="font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#A8A29E;margin-bottom:10px;">Preview</div>
        <div style="display:flex;align-items:center;gap:10px;">
            <button style="background:{{ $primary_color }};color:#fff;border:none;border-radius:4px;padding:8px 16px;font-size:13px;font-weight:500;cursor:default;">Primary</button>
            <button style="background:{{ $accent_color }};color:#fff;border:none;border-radius:4px;padding:8px 16px;font-size:13px;font-weight:500;cursor:default;">Accent</button>
            <span style="display:inline-flex;align-items:center;padding:2px 8px;background:{{ $primary_color }}22;color:{{ $primary_color }};font-size:11px;font-weight:500;border-radius:4px;">Badge</span>
        </div>
    </div>

    <div style="margin-top:16px;display:flex;gap:10px;">
        <button wire:click="saveBranding" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-lg" style="flex:1;">
            <span wire:loading.remove wire:target="saveBranding">Save & Continue</span>
            <span wire:loading wire:target="saveBranding">Saving...</span>
        </button>
        <button wire:click="skipBranding" type="button" class="krd-btn krd-btn-ghost">Skip</button>
    </div>
    @endif

    {{-- ══ STEP 4 — Done ══ --}}
    @if($step === 4)
    <div style="text-align:center;padding:48px 0;">
        <div style="font-size:48px;margin-bottom:20px;">🎉</div>
        <h2 style="font-size:24px;font-weight:700;color:#1C1917;letter-spacing:-0.02em;margin-bottom:10px;">
            You're all set!
        </h2>
        <p style="font-size:14px;color:#78716C;line-height:1.7;margin-bottom:32px;max-width:360px;margin-left:auto;margin-right:auto;">
            Your Koordli workspace is ready. Start by creating your first event.
        </p>
        <button wire:click="goToDashboard" class="krd-btn krd-btn-primary krd-btn-lg" style="min-width:200px;">
            Go to Dashboard →
        </button>
    </div>
    @endif

</div>