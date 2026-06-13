<div>
    {{-- Header --}}
    <div style="margin-bottom:28px;">
        <div class="krd-label" style="margin-bottom:4px;">Platform Admin</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">Create Event Company</h2>
    </div>

    <div class="krd-form-grid">

        {{-- Left — Form --}}
        <div>
            @if($error)
            <div style="background:#FEE2E2;border:1px solid #FECACA;border-radius:6px;padding:14px 16px;margin-bottom:20px;font-size:13px;color:#DC2626;">
                {{ $error }}
            </div>
            @endif

            <div class="krd-card">
                <div class="krd-input-group">
                    <label class="krd-label-text">Company Name</label>
                    <input wire:model="name" type="text" class="krd-input @error('name') krd-input-error @enderror" placeholder="e.g. Stellar Events" />
                    @error('name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                <div class="krd-divider"></div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Owner Full Name</label>
                    <input wire:model="owner_name" type="text" class="krd-input @error('owner_name') krd-input-error @enderror" placeholder="e.g. Amara Johnson" />
                    @error('owner_name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Owner Email</label>
                    <input wire:model="owner_email" type="email" class="krd-input @error('owner_email') krd-input-error @enderror" placeholder="owner@company.com" />
                    @error('owner_email') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Temporary Password</label>
                    <input wire:model="owner_password" type="text" class="krd-input @error('owner_password') krd-input-error @enderror" placeholder="Min 8 characters" />
                    @error('owner_password') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    <span class="krd-input-hint">Sent to owner via welcome email. They must change it on first login.</span>
                </div>

                <div class="krd-divider"></div>

                {{-- Billing Currency — custom dropdown --}}
                <div class="krd-input-group">
                    <label class="krd-label-text">Billing Currency</label>
                    <div class="krd-dropdown" x-data="{ open: false, selected: 'NGN — Nigerian Naira (₦)' }">
                        <button type="button" class="krd-dropdown-trigger" x-bind:class="{ 'open': open }" x-on:click="open = !open" x-on:click.outside="open = false">
                            <span x-text="selected"></span>
                            <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div class="krd-dropdown-menu" x-show="open" x-transition x-cloak>
                            @foreach([
                                ['value' => 'NGN', 'label' => 'NGN — Nigerian Naira (₦)'],
                                ['value' => 'GHS', 'label' => 'GHS — Ghanaian Cedi (₵)'],
                                ['value' => 'GBP', 'label' => 'GBP — British Pound (£)'],
                                ['value' => 'USD', 'label' => 'USD — US Dollar ($)'],
                                ['value' => 'EUR', 'label' => 'EUR — Euro (€)'],
                                ['value' => 'KES', 'label' => 'KES — Kenyan Shilling (KSh)'],
                                ['value' => 'ZAR', 'label' => 'ZAR — South African Rand (R)'],
                            ] as $currency)
                            <div
                                class="krd-dropdown-option"
                                wire:click="$set('billing_currency', '{{ $currency['value'] }}')"
                                x-on:click="selected = '{{ $currency['label'] }}'; open = false"
                            >
                                {{ $currency['label'] }}
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Country --}}
                <div class="krd-input-group">
                    <label class="krd-label-text">Country <span style="color:#EF4444;">*</span></label>
                    <x-ui.dropdown
                        wire="country"
                        placeholder="Select country"
                        selected="{{ $country ? ($countries[$country] ?? 'Select country') : 'Select country' }}"
                    >
                        @foreach($countries as $code => $name)
                        <div class="krd-dropdown-option {{ $country === $code ? 'selected' : '' }}"
                            x-on:click="select('{{ $name }}', '{{ $code }}')">
                            {{ $name }}
                        </div>
                        @endforeach
                    </x-ui.dropdown>
                    @error('country') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                {{-- Plan Assignment --}}
                <div class="krd-input-group" style="margin-bottom:0;">
                    <label class="krd-label-text">
                        Assign Plan
                        <span style="color:#A8A29E;font-weight:400;"> — optional</span>
                    </label>
                    <div class="krd-dropdown" x-data="{ open: false, selected: 'Let tenant choose during onboarding' }">
                        <button type="button" class="krd-dropdown-trigger" x-bind:class="{ 'open': open }" x-on:click="open = !open" x-on:click.outside="open = false">
                            <span x-text="selected" style="color: #A8A29E;"></span>
                            <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div class="krd-dropdown-menu" x-show="open" x-transition x-cloak>
                            <div
                                class="krd-dropdown-option"
                                wire:click="$set('plan_id', null)"
                                x-on:click="selected = 'Let tenant choose during onboarding'; open = false"
                            >
                                Let tenant choose during onboarding
                            </div>
                            @foreach($plans as $plan)
                            <div
                                class="krd-dropdown-option {{ $plan_id === $plan->id ? 'selected' : '' }}"
                                wire:click="$set('plan_id', {{ $plan->id }})"
                                x-on:click="selected = '{{ $plan->name }}'; open = false"
                            >
                                {{ $plan->name }}
                                <span style="font-size:11px;color:#A8A29E;margin-left:6px;">{{ ucfirst($plan->billing_cycle) }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <span class="krd-input-hint">If no plan is assigned, tenant will choose during onboarding.</span>
                </div>
            </div>

            <div style="margin-top:16px;display:flex;align-items:center;gap:12px;">
                <button wire:click="create" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                    <span wire:loading.remove wire:target="create">Create Company</span>
                    <span wire:loading wire:target="create">Creating...</span>
                </button>
                <a href="{{ route('platform.tenants') }}" wire:navigate class="krd-btn krd-btn-ghost">Cancel</a>
            </div>
        </div>

        {{-- Right — Instructions --}}
        <div style="display:flex;flex-direction:column;gap:12px;">
            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
                    <span style="font-size:16px;">📋</span> What happens next
                </div>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    @foreach([
                        ['step' => '1', 'text' => 'Company workspace is created with default event types, statuses and categories.'],
                        ['step' => '2', 'text' => 'A welcome email is sent to the owner with their login credentials.'],
                        ['step' => '3', 'text' => 'Owner logs in and changes their password, selects a plan (if not pre-assigned), and sets up branding.'],
                        ['step' => '4', 'text' => 'Company is ready to manage events, clients, vendors and guests.'],
                    ] as $item)
                    <div style="display:flex;gap:10px;align-items:flex-start;">
                        <div style="width:20px;height:20px;border-radius:50%;background:#EDE9FE;color:#7C3AED;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            {{ $item['step'] }}
                        </div>
                        <p style="font-size:12px;color:#78716C;line-height:1.6;">{{ $item['text'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="krd-card" style="padding:20px;background:#FFFBEB;border-color:#FDE68A;">
                <div style="font-size:13px;font-weight:600;color:#92400E;margin-bottom:8px;">⚠️ Important</div>
                <p style="font-size:12px;color:#92400E;line-height:1.6;">
                    The temporary password is sent in plain text via email.
                    The owner must change it immediately on first login.
                </p>
            </div>

            <div class="krd-card" style="padding:20px;background:#F0FDF4;border-color:#86EFAC;">
                <div style="font-size:13px;font-weight:600;color:#166534;margin-bottom:8px;">💡 Plan Assignment</div>
                <p style="font-size:12px;color:#166534;line-height:1.6;">
                    Assigning a plan now is useful for clients who paid via bank transfer or special arrangements. Leave blank to let them choose during onboarding.
                </p>
            </div>
        </div>
    </div>
</div>