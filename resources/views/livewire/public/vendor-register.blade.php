<div class="krd-auth-split">

    {{-- Left Panel --}}
    <div class="krd-auth-panel">
        <div><x-ui.logo color="light" /></div>
        <div style="max-width: 360px;">
            <h1 style="font-size: 36px; font-weight: 700; color: #FAFAF9; line-height: 1.1; letter-spacing: -0.02em; margin-bottom: 14px;">
                Join the<br>
                <span style="color: #F59E0B;">{{ $tenant->name }}</span><br>
                vendor network.
            </h1>
            <p style="font-size: 14px; color: #78716C; line-height: 1.7;">
                Apply to become a vendor. Once approved, you'll get access to your vendor portal
                to view assignments, timelines, and payment status.
            </p>
        </div>
        <p style="font-size: 11px; color: #44403C; letter-spacing: 0.05em;">© {{ date('Y') }} Koordli. All rights reserved.</p>
    </div>

    {{-- Right Panel --}}
    <div class="krd-auth-form" style="align-items: flex-start;">
        <div style="width: 100%; max-width: 440px; padding: 48px 0;">

            <div class="krd-mobile-only" style="margin-bottom: 20px;">
                <x-ui.logo color="dark" />
            </div>

            @if($submitted)
            {{-- Success State --}}
            <div style="text-align:center;padding:40px 0;">
                <div style="font-size:48px;margin-bottom:20px;">🎉</div>
                <h2 style="font-size:22px;font-weight:700;color:#1C1917;margin-bottom:10px;">Application Submitted!</h2>
                <p style="font-size:14px;color:#78716C;line-height:1.7;margin-bottom:24px;">
                    Thank you, <strong>{{ $contact_name }}</strong>. Your application for
                    <strong>{{ $business_name }}</strong> has been received.<br><br>
                    The team at <strong>{{ $tenant->name }}</strong> will review it and get back to you via email.
                </p>
                <div style="background:#F5F3FF;border:1px solid #DDD6FE;border-radius:8px;padding:16px 20px;text-align:left;">
                    <div style="font-size:12px;font-weight:600;color:#7C3AED;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.06em;">What happens next</div>
                    <div style="font-size:13px;color:#57534E;line-height:1.8;">
                        1. {{ $tenant->name }} reviews your application<br>
                        2. You'll receive an approval email with login credentials<br>
                        3. Log in to your vendor portal to view assignments
                    </div>
                </div>
            </div>

            @else

            <div style="margin-bottom: 28px;">
                <h2 style="font-size:22px;font-weight:600;color:#1C1917;letter-spacing:-0.01em;margin-bottom:6px;">
                    Vendor Application
                </h2>
                <p style="font-size:13px;color:#78716C;">
                    Fill in your details to apply to join <strong>{{ $tenant->name }}</strong>'s vendor network.
                </p>
            </div>

            @if($error)
            <div style="background:#FEE2E2;border:1px solid #FECACA;border-radius:6px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#DC2626;">
                {{ $error }}
            </div>
            @endif

            <div class="krd-input-group">
                <label class="krd-label-text">Business Name <span style="color:#EF4444;">*</span></label>
                <input wire:model="business_name" type="text"
                    class="krd-input @error('business_name') krd-input-error @enderror"
                    placeholder="Your business or company name" />
                @error('business_name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="krd-input-group">
                <label class="krd-label-text">Contact Name <span style="color:#EF4444;">*</span></label>
                <input wire:model="contact_name" type="text"
                    class="krd-input @error('contact_name') krd-input-error @enderror"
                    placeholder="Your full name" />
                @error('contact_name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="krd-input-group">
                <label class="krd-label-text">Email Address <span style="color:#EF4444;">*</span></label>
                <input wire:model="email" type="email"
                    class="krd-input @error('email') krd-input-error @enderror"
                    placeholder="you@business.com" />
                @error('email') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="krd-input-group">
                <label class="krd-label-text">Phone Number</label>
                <input wire:model="phone" type="text" class="krd-input" placeholder="+234..." />
            </div>

            {{-- Country — plain Alpine, instant UI, IP pre-filled --}}
            <div class="krd-input-group"
                x-data="{
                    open: false,
                    selectedCode: '{{ $country }}',
                    selectedName: '{{ $countryName ?: 'Select your country' }}',
                    countries: {
                        'NG': 'Nigeria', 'GH': 'Ghana', 'KE': 'Kenya', 'ZA': 'South Africa',
                        'GB': 'United Kingdom', 'US': 'United States', 'CA': 'Canada',
                        'AU': 'Australia', 'DE': 'Germany', 'FR': 'France', 'IT': 'Italy',
                        'ES': 'Spain', 'AE': 'United Arab Emirates', 'SA': 'Saudi Arabia',
                        'IN': 'India', 'SG': 'Singapore', 'RW': 'Rwanda', 'UG': 'Uganda',
                        'TZ': 'Tanzania', 'ET': 'Ethiopia', 'CM': 'Cameroon', 'CI': 'Ivory Coast',
                        'SN': 'Senegal', 'TG': 'Togo', 'BJ': 'Benin', 'NL': 'Netherlands',
                        'BE': 'Belgium', 'PT': 'Portugal', 'IE': 'Ireland', 'SE': 'Sweden',
                        'NO': 'Norway', 'DK': 'Denmark', 'FI': 'Finland',
                    },
                    pick(code, name) {
                        this.selectedCode = code;
                        this.selectedName = name;
                        this.open = false;
                        $wire.setCountry(code, name);
                    }
                }"
                x-on:click.outside="open = false"
                style="position:relative;">
                <label class="krd-label-text">Country</label>
                <button type="button"
                    x-on:click="open = !open"
                    x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'"
                    style="width:100%;">
                    <span
                        x-text="selectedName"
                        :style="selectedName === 'Select your country' ? 'color:#A8A29E' : 'color:#1C1917'">
                    </span>
                    <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </button>
                <div x-show="open" x-cloak class="krd-dropdown-menu">
                    <template x-for="(name, code) in countries" :key="code">
                        <div class="krd-dropdown-option"
                            :class="selectedCode === code ? 'selected' : ''"
                            x-on:click="pick(code, name)"
                            x-text="name">
                        </div>
                    </template>
                </div>
                @if($country)
                <span class="krd-input-hint">📍 Detected from your IP — change if incorrect</span>
                @endif
            </div>

            {{-- Category dropdown --}}
            <div class="krd-input-group"
                x-data="{
                    open: false,
                    label: '{{ $categoryLabel ?: 'Select a category' }}'
                }"
                x-on:click.outside="open = false"
                style="position:relative;">
                <label class="krd-label-text">Service Category</label>
                <button type="button"
                    x-on:click="open = !open"
                    x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'"
                    style="width:100%;">
                    <span
                        x-bind:style="label === 'Select a category' ? 'color:#A8A29E' : 'color:#1C1917'"
                        x-text="label">
                    </span>
                    <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </button>
                <div x-show="open" x-cloak class="krd-dropdown-menu">
                    @foreach($categories as $cat)
                    <div class="krd-dropdown-option {{ $vendor_category_id === $cat->id ? 'selected' : '' }}"
                        x-on:click="
                            label = '{{ $cat->name }}';
                            open = false;
                            $wire.selectCategory({{ $cat->id }}, '{{ $cat->name }}');
                        ">
                        {{ $cat->name }}
                    </div>
                    @endforeach
                    @if($categories->isEmpty())
                    <div class="krd-dropdown-empty">No categories available</div>
                    @endif
                </div>
            </div>

            <div class="krd-input-group">
                <label class="krd-label-text">What services do you offer?</label>
                <textarea wire:model="service_description" class="krd-input"
                    rows="3" placeholder="Brief description of your services..."
                    style="resize:vertical;"></textarea>
            </div>

            <div class="krd-grid-2" style="gap:12px;">
                <div class="krd-input-group">
                    <label class="krd-label-text">Instagram Handle</label>
                    <input wire:model="instagram" type="text" class="krd-input" placeholder="@yourbusiness" />
                </div>
                <div class="krd-input-group">
                    <label class="krd-label-text">Website</label>
                    <input wire:model="website" type="text" class="krd-input" placeholder="https://..." />
                </div>
            </div>

            {{-- Available to travel — instant Alpine toggle --}}
            <div class="krd-input-group" style="margin-bottom:24px;">
                <label class="krd-label-text">Available to Travel?</label>
                <div
                    x-data="{ val: {{ $available_to_travel ? 'true' : 'false' }} }"
                    style="display:flex;gap:8px;margin-top:2px;">
                    <button type="button"
                        x-on:click="val = true; $wire.set('available_to_travel', true)"
                        :style="val
                            ? 'padding:8px 20px;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;border:2px solid #7C3AED;background:#F5F3FF;color:#7C3AED;transition:all 150ms;'
                            : 'padding:8px 20px;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;border:2px solid #E7E5E4;background:#fff;color:#57534E;transition:all 150ms;'">
                        ✈️ Yes
                    </button>
                    <button type="button"
                        x-on:click="val = false; $wire.set('available_to_travel', false)"
                        :style="!val
                            ? 'padding:8px 20px;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;border:2px solid #7C3AED;background:#F5F3FF;color:#7C3AED;transition:all 150ms;'
                            : 'padding:8px 20px;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;border:2px solid #E7E5E4;background:#fff;color:#57534E;transition:all 150ms;'">
                        📍 No, local only
                    </button>
                </div>
                <span class="krd-input-hint">Let event companies know if you can work outside your area.</span>
            </div>

            <button wire:click="submit" wire:loading.attr="disabled"
                class="krd-btn krd-btn-primary krd-btn-lg" style="width:100%;margin-top:8px;">
                <span wire:loading.remove wire:target="submit">Submit Application</span>
                <span wire:loading wire:target="submit">Submitting...</span>
            </button>

            <div style="margin-top:16px;text-align:center;font-size:12px;color:#A8A29E;">
                Already have an account?
                <a href="{{ route('vendor.login') }}" style="color:#7C3AED;text-decoration:none;font-weight:500;">Sign in</a>
            </div>

            @endif
        </div>
    </div>

</div>