<div>
    {{-- Header --}}
    <div style="margin-bottom:28px;">
        <div style="margin-bottom:8px;">
            @if($isEdit)
            <a href="{{ route('platform.tenants') }}" wire:navigate
                style="color:#A8A29E;text-decoration:none;font-size:13px;">← Back to Companies</a>
            @endif
        </div>
        <div class="krd-label" style="margin-bottom:4px;">Platform Admin</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">
            {{ $isEdit ? 'Edit Company' : 'Create Event Company' }}
        </h2>
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
                    <label class="krd-label-text">Company Name <span style="color:#EF4444;">*</span></label>
                    <input wire:model="name" type="text"
                        class="krd-input @error('name') krd-input-error @enderror"
                        placeholder="e.g. Stellar Events" />
                    @error('name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                <div class="krd-divider"></div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Owner Full Name <span style="color:#EF4444;">*</span></label>
                    <input wire:model="owner_name" type="text"
                        class="krd-input @error('owner_name') krd-input-error @enderror"
                        placeholder="e.g. Amara Johnson" />
                    @error('owner_name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                @if(!$isEdit)
                <div class="krd-input-group">
                    <label class="krd-label-text">Owner Email <span style="color:#EF4444;">*</span></label>
                    <input wire:model="owner_email" type="email"
                        class="krd-input @error('owner_email') krd-input-error @enderror"
                        placeholder="owner@company.com" />
                    @error('owner_email') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Temporary Password <span style="color:#EF4444;">*</span></label>
                    <input wire:model="owner_password" type="text"
                        class="krd-input @error('owner_password') krd-input-error @enderror"
                        placeholder="Min 8 characters" />
                    @error('owner_password') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    <span class="krd-input-hint">Sent to owner via welcome email.</span>
                </div>
                @else
                <div class="krd-input-group">
                    <label class="krd-label-text">Owner Email</label>
                    <input type="text" class="krd-input" value="{{ $owner_email }}" disabled
                        style="background:#F5F5F4;color:#A8A29E;" />
                    <span class="krd-input-hint">Email cannot be changed from here.</span>
                </div>
                @endif

                <div class="krd-divider"></div>

                {{-- Status (edit only) --}}
                @if($isEdit)
                <div class="krd-input-group"
                    x-data="{
                        open: false,
                        label: '{{ ucfirst($status) }}',
                        pick(val, label) { this.label = label; this.open = false; $wire.set('status', val); }
                    }"
                    x-on:click.outside="open = false"
                    style="position:relative;">
                    <label class="krd-label-text">Status</label>
                    <button type="button"
                        x-on:click="open = !open"
                        x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'"
                        style="width:100%;">
                        <span x-text="label"></span>
                        <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-cloak class="krd-dropdown-menu">
                        @foreach(['trial' => 'Trial', 'active' => 'Active', 'suspended' => 'Suspended', 'cancelled' => 'Cancelled'] as $val => $label)
                        <div class="krd-dropdown-option {{ $status === $val ? 'selected' : '' }}"
                            x-on:click="pick('{{ $val }}', '{{ $label }}')">
                            {{ $label }}
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Billing Currency --}}
                <div class="krd-input-group"
                    x-data="{
                        open: false,
                        selected: @entangle('billing_currency'),
                        options: @js([
                            'NGN' => 'NGN — Nigerian Naira (₦)',
                            'GHS' => 'GHS — Ghanaian Cedi (₵)',
                            'GBP' => 'GBP — British Pound (£)',
                            'USD' => 'USD — US Dollar ($)',
                            'EUR' => 'EUR — Euro (€)',
                            'KES' => 'KES — Kenyan Shilling (KSh)',
                            'ZAR' => 'ZAR — South African Rand (R)',
                        ]),
                        get label() {
                            return this.options[this.selected] ?? 'NGN — Nigerian Naira (₦)';
                        },
                        pick(val) { this.selected = val; this.open = false; $wire.set('billing_currency', val); }
                    }"
                    x-on:click.outside="open = false"
                    x-bind:style="open ? 'position: relative; z-index: 30;' : 'position: relative; z-index: 1;'">
                    <label class="krd-label-text">Billing Currency</label>
                    <button type="button"
                        x-on:click="open = !open"
                        x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'"
                        style="width:100%;">
                        <span x-text="label"></span>
                        <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-cloak class="krd-dropdown-menu">
                        @foreach([
                            'NGN' => 'NGN — Nigerian Naira (₦)',
                            'GHS' => 'GHS — Ghanaian Cedi (₵)',
                            'GBP' => 'GBP — British Pound (£)',
                            'USD' => 'USD — US Dollar ($)',
                            'EUR' => 'EUR — Euro (€)',
                            'KES' => 'KES — Kenyan Shilling (KSh)',
                            'ZAR' => 'ZAR — South African Rand (R)',
                        ] as $val => $label)
                        <div class="krd-dropdown-option {{ $billing_currency === $val ? 'selected' : '' }}"
                            x-on:click="pick('{{ $val }}')">
                            {{ $label }}
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Country --}}
                <div class="krd-input-group"
                    x-data="{
                        open: false,
                        selected: @entangle('country'),
                        options: @js($countries),
                        get label() {
                            return this.options[this.selected] ?? 'Select country';
                        },
                        pick(val) { this.selected = val; this.open = false; $wire.set('country', val); }
                    }"
                    x-on:click.outside="open = false"
                    x-bind:style="open ? 'position: relative; z-index: 30;' : 'position: relative; z-index: 1;'">
                    <label class="krd-label-text">Country <span style="color:#EF4444;">*</span></label>
                    <button type="button"
                        x-on:click="open = !open"
                        x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'"
                        style="width:100%;">
                        <span x-text="label"></span>
                        <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-cloak class="krd-dropdown-menu" style="max-height:200px;overflow-y:auto;">
                        @foreach($countries as $code => $name)
                        <div class="krd-dropdown-option {{ $country === $code ? 'selected' : '' }}"
                            x-on:click="pick('{{ $code }}')">
                            {{ $name }}
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Industry Profile --}}
                <div class="krd-input-group"
                    x-data="{
                        open: false,
                        selected: @entangle('industry_profile_id'),
                        options: @js($industryProfiles->mapWithKeys(fn ($profile) => [$profile->id => $profile->icon . ' ' . $profile->name])->all()),
                        get label() {
                            if (this.selected === null || this.selected === '') return 'No profile';
                            return this.options[this.selected] ?? 'No profile';
                        },
                        pick(val) { this.selected = val; this.open = false; $wire.set('industry_profile_id', val); }
                    }"
                    x-on:click.outside="open = false"
                    x-bind:style="open ? 'position: relative; z-index: 30;' : 'position: relative; z-index: 1;'">
                    <label class="krd-label-text">Industry Profile <span style="color:#A8A29E;font-weight:400;">(optional)</span></label>
                    <button type="button"
                        x-on:click="open = !open"
                        x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'"
                        style="width:100%;">
                        <span x-text="label"></span>
                        <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-cloak class="krd-dropdown-menu">
                        <div class="krd-dropdown-option {{ !$industry_profile_id ? 'selected' : '' }}"
                            x-on:click="pick(null)">
                            No profile
                        </div>
                        @foreach($industryProfiles as $profile)
                        <div class="krd-dropdown-option {{ $industry_profile_id == $profile->id ? 'selected' : '' }}"
                            x-on:click="pick({{ $profile->id }})">
                            {{ $profile->icon }} {{ $profile->name }}
                        </div>
                        @endforeach
                    </div>
                    <span class="krd-input-hint">Sets up default event types, vendor categories, and roles for this tenant.</span>
                </div>

                {{-- Plan --}}
                <div class="krd-input-group" style="margin-bottom:0; position:relative; z-index:1;"
                    x-data="{
                        open: false,
                        selected: @entangle('plan_id'),
                        options: @js($plans->mapWithKeys(fn ($plan) => [$plan->id => $plan->name . ' ' . ucfirst($plan->billing_cycle)])->all()),
                        get label() {
                            if (this.selected === null || this.selected === '') return 'Let tenant choose during onboarding';
                            return this.options[this.selected] ?? 'Let tenant choose during onboarding';
                        },
                        pick(val) { this.selected = val; this.open = false; $wire.set('plan_id', val); }
                    }"
                    x-on:click.outside="open = false"
                    x-bind:style="open ? 'position: relative; z-index: 10;' : 'position: relative; z-index: 1;'">
                    <label class="krd-label-text">Assign Plan</label>
                    <button type="button"
                        x-on:click="open = !open"
                        x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'"
                        style="width:100%;">
                        <span x-text="label" style="color:#A8A29E;"></span>
                        <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-cloak class="krd-dropdown-menu" style="z-index:80;">
                        <div class="krd-dropdown-option {{ !$plan_id ? 'selected' : '' }}"
                            x-on:click="pick(null)">
                            Let tenant choose during onboarding
                        </div>
                        @foreach($plans as $plan)
                        <div class="krd-dropdown-option {{ $plan_id === $plan->id ? 'selected' : '' }}"
                            x-on:click="pick({{ $plan->id }})">
                            {{ $plan->name }}
                            <span style="font-size:11px;color:#A8A29E;margin-left:6px;">{{ ucfirst($plan->billing_cycle) }}</span>
                        </div>
                        @endforeach
                    </div>
                    <span class="krd-input-hint">If no plan is assigned, tenant will choose during onboarding.</span>
                </div>

                @if($plan_id)
                <div class="krd-input-group"
                    x-data="{
                        open: false,
                        selected: @entangle('subscription_mode'),
                        cycle: @entangle('subscription_cycle')
                    }"
                    x-on:click.outside="open = false"
                    x-bind:style="open ? 'position: relative; z-index: 30;' : 'position: relative; z-index: 1;'">
                    <label class="krd-label-text">Subscription Mode</label>
                    <button type="button"
                        x-on:click="open = !open"
                        x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'"
                        style="width:100%;">
                        <span x-text="selected === 'trial' ? 'Trial' : 'Direct payment'" style="color:#1C1917;"></span>
                        <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-cloak class="krd-dropdown-menu" style="z-index:80;">
                        <div class="krd-dropdown-option {{ $subscription_mode === 'trial' ? 'selected' : '' }}" x-on:click="selected = 'trial'; open = false; $wire.set('subscription_mode', 'trial');">Trial</div>
                        <div class="krd-dropdown-option {{ $subscription_mode === 'direct' ? 'selected' : '' }}" x-on:click="selected = 'direct'; open = false; $wire.set('subscription_mode', 'direct');">Direct payment</div>
                    </div>
                </div>

                <div class="krd-input-group"
                    x-data="{
                        open: false,
                        selected: @entangle('subscription_cycle')
                    }"
                    x-on:click.outside="open = false"
                    x-bind:style="open ? 'position: relative; z-index: 20;' : 'position: relative; z-index: 1;'">
                    <label class="krd-label-text">Billing Cycle</label>
                    <button type="button"
                        x-on:click="open = !open"
                        x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'"
                        style="width:100%;">
                        <span x-text="selected === 'annual' ? 'Annual' : 'Monthly'" style="color:#1C1917;"></span>
                        <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-cloak class="krd-dropdown-menu" style="z-index:80;">
                        <div class="krd-dropdown-option {{ $subscription_cycle === 'monthly' ? 'selected' : '' }}" x-on:click="selected = 'monthly'; open = false; $wire.set('subscription_cycle', 'monthly');">Monthly</div>
                        <div class="krd-dropdown-option {{ $subscription_cycle === 'annual' ? 'selected' : '' }}" x-on:click="selected = 'annual'; open = false; $wire.set('subscription_cycle', 'annual');">Annual</div>
                    </div>
                    <span class="krd-input-hint">Only applies when using direct payment; trial mode will use the plan's configured trial days.</span>
                </div>
                @endif
            </div>

            <div style="margin-top:16px;display:flex;align-items:center;gap:12px;">
                @if($isEdit)
                <button wire:click="update" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                    <span wire:loading.remove wire:target="update">Save Changes</span>
                    <span wire:loading wire:target="update">Saving...</span>
                </button>
                @else
                <button wire:click="create" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                    <span wire:loading.remove wire:target="create">Create Company</span>
                    <span wire:loading wire:target="create">Creating...</span>
                </button>
                @endif
                <a href="{{ route('platform.tenants') }}" wire:navigate class="krd-btn krd-btn-ghost">Cancel</a>
            </div>
        </div>

        {{-- Right — Instructions --}}
        <div style="display:flex;flex-direction:column;gap:12px;">
            @if(!$isEdit)
            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
                    <span style="font-size:16px;">📋</span> What happens next
                </div>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    @foreach([
                        ['step' => '1', 'text' => 'Company workspace is created with default event types, statuses and categories.'],
                        ['step' => '2', 'text' => 'A welcome email is sent to the owner with their login credentials.'],
                        ['step' => '3', 'text' => 'Owner logs in, changes password, selects a plan (if not pre-assigned), and sets up branding.'],
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
                    The temporary password is sent in plain text via email. The owner must change it on first login.
                </p>
            </div>
            @else
            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;">⚠️ Edit Guidelines</div>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    @foreach([
                        'Owner email cannot be changed from this page.',
                        'Changing status to Suspended blocks all users immediately.',
                        'Assigning a new plan takes effect on the next billing cycle.',
                        'Currency change only affects new invoices going forward.',
                    ] as $tip)
                    <div style="display:flex;gap:8px;align-items:flex-start;">
                        <div style="width:5px;height:5px;border-radius:50%;background:#7C3AED;flex-shrink:0;margin-top:6px;"></div>
                        <p style="font-size:12px;color:#78716C;line-height:1.6;">{{ $tip }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="krd-card" style="padding:20px;background:#F0FDF4;border-color:#86EFAC;">
                <div style="font-size:13px;font-weight:600;color:#166534;margin-bottom:8px;">💡 Plan Assignment</div>
                <p style="font-size:12px;color:#166534;line-height:1.6;">
                    Assigning a plan is useful for clients who paid via bank transfer. Leave blank to let them choose during onboarding.
                </p>
            </div>
        </div>
    </div>
</div>