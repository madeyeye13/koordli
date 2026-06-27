<div x-data="{ activeTab: '{{ $activeTab }}' }">

    {{-- Header --}}
    <div style="margin-bottom:24px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('tenant.forms') }}" wire:navigate
                style="color:#A8A29E;text-decoration:none;font-size:13px;">← Back to Forms</a>
        </div>
        <div class="krd-label" style="margin-bottom:4px;">Forms & Bookings</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">{{ $form ? 'Edit Form' : 'New Form' }}</h2>
    </div>

    {{-- Tabs --}}
    <div style="display:flex;gap:4px;border-bottom:1px solid #E7E5E4;margin-bottom:20px;flex-wrap:wrap;">
        @foreach(['details' => 'Details', 'fields' => 'Fields', 'redirect' => 'After Submission', 'availability' => 'Availability', 'embed' => 'Embed & Share'] as $tab => $label)
        @if($tab === 'availability' && $type !== 'consultation') @continue @endif
        <button type="button"
            x-on:click="activeTab = '{{ $tab }}'; $wire.setTab('{{ $tab }}')"
            :style="activeTab === '{{ $tab }}'
                ? 'padding:10px 16px;font-size:13px;font-weight:500;border:none;background:none;cursor:pointer;border-bottom:2px solid #7C3AED;color:#7C3AED;margin-bottom:-1px;'
                : 'padding:10px 16px;font-size:13px;font-weight:500;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;color:#78716C;margin-bottom:-1px;'"
        >{{ $label }}</button>
        @endforeach
    </div>

    {{-- ══ Details Tab ══ --}}
    <div x-show="activeTab === 'details'">
        <div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;" id="form-details-grid">
            <div>
                <div class="krd-card" style="padding:24px;margin-bottom:16px;">
                    <div class="krd-label" style="margin-bottom:16px;">Form Details</div>

                    <div class="krd-input-group">
                        <label class="krd-label-text">Form Name <span style="color:#EF4444;">*</span></label>
                        <input wire:model="name" type="text"
                            class="krd-input @error('name') krd-input-error @enderror"
                            placeholder="e.g. Wedding Enquiry Form" />
                        @error('name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>

                    <div class="krd-grid-2" style="gap:12px;">
                        {{-- Type --}}
                        <div class="krd-input-group"
                            x-data="{
                                open: false,
                                label: '{{ $type === 'consultation' ? 'Consultation' : 'Booking' }}',
                                pick(val, label) { this.label = label; this.open = false; $wire.set('type', val); }
                            }"
                            x-on:click.outside="open = false" style="position:relative;">
                            <label class="krd-label-text">Form Type</label>
                            <button type="button" x-on:click="open = !open"
                                x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'" style="width:100%;">
                                <span x-text="label"></span>
                                <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <div x-show="open" x-cloak class="krd-dropdown-menu">
                                <div class="krd-dropdown-option {{ $type === 'booking' ? 'selected' : '' }}"
                                    x-on:click="pick('booking', 'Booking')">Booking</div>
                                <div class="krd-dropdown-option {{ $type === 'consultation' ? 'selected' : '' }}"
                                    x-on:click="pick('consultation', 'Consultation')">Consultation</div>
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="krd-input-group"
                            x-data="{
                                open: false,
                                label: '{{ ucfirst($status) }}',
                                pick(val, label) { this.label = label; this.open = false; $wire.set('status', val); }
                            }"
                            x-on:click.outside="open = false" style="position:relative;">
                            <label class="krd-label-text">Status</label>
                            <button type="button" x-on:click="open = !open"
                                x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'" style="width:100%;">
                                <span x-text="label"></span>
                                <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <div x-show="open" x-cloak class="krd-dropdown-menu">
                                <div class="krd-dropdown-option {{ $status === 'active' ? 'selected' : '' }}"
                                    x-on:click="pick('active', 'Active')">Active</div>
                                <div class="krd-dropdown-option {{ $status === 'inactive' ? 'selected' : '' }}"
                                    x-on:click="pick('inactive', 'Inactive')">Inactive</div>
                            </div>
                        </div>
                    </div>

                    <div class="krd-input-group">
                        <label class="krd-label-text">Description</label>
                        <textarea wire:model="description" class="krd-input" rows="2"
                            placeholder="Brief description shown on the public form page..."></textarea>
                    </div>

                    {{-- Hero Image --}}
                    <div class="krd-input-group">
                        <label class="krd-label-text">Hero Image</label>
                        @if($hero_image_url)
                        <div style="margin-bottom:8px;border-radius:6px;overflow:hidden;border:1px solid #E7E5E4;">
                            <img src="{{ $hero_image_url }}" style="width:100%;height:140px;object-fit:cover;display:block;" />
                        </div>
                        @endif
                        @if($hero_image)
                        <div style="margin-bottom:8px;border-radius:6px;overflow:hidden;border:1px solid #DDD6FE;">
                            <img src="{{ $hero_image->temporaryUrl() }}" style="width:100%;height:140px;object-fit:cover;display:block;" />
                        </div>
                        @endif
                        <input wire:model="hero_image" type="file" accept="image/*"
                            class="krd-input" style="padding:7px 12px;cursor:pointer;" />
                        @error('hero_image') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                        <span class="krd-input-hint">JPG or PNG. Max 3MB. Shown as left panel background.</span>
                    </div>
                </div>

                {{-- Contact Info --}}
                <div class="krd-card" style="padding:24px;margin-bottom:16px;">
                    <div class="krd-label" style="margin-bottom:16px;">Your Contact Information</div>
                    <div class="krd-input-group">
                        <label class="krd-label-text">Email</label>
                        <input wire:model="tenant_email" type="email" class="krd-input" placeholder="your@email.com" />
                    </div>
                    <div class="krd-input-group">
                        <label class="krd-label-text">Phone</label>
                        <input wire:model="tenant_phone" type="text" class="krd-input" placeholder="+234..." />
                    </div>
                    <div class="krd-input-group" style="margin-bottom:0;">
                        <label class="krd-label-text">Address</label>
                        <textarea wire:model="tenant_address" class="krd-input" rows="2"
                            placeholder="Your office or studio address..."></textarea>
                    </div>
                </div>

                {{-- Consultation specific --}}
                @if($type === 'consultation')
                <div class="krd-card" style="padding:24px;margin-bottom:16px;">
                    <div class="krd-label" style="margin-bottom:16px;">Consultation Settings</div>

                    <div class="krd-grid-2" style="gap:12px;">
                        {{-- Consultation type --}}
                        <div class="krd-input-group"
                            x-data="{
                                open: false,
                                label: '{{ ['physical' => 'Physical Only', 'virtual' => 'Virtual Only', 'both' => 'Physical & Virtual'][$consultation_type] ?? 'Both' }}',
                                pick(val, label) { this.label = label; this.open = false; $wire.set('consultation_type', val); }
                            }"
                            x-on:click.outside="open = false" style="position:relative;">
                            <label class="krd-label-text">Consultation Type</label>
                            <button type="button" x-on:click="open = !open"
                                x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'" style="width:100%;">
                                <span x-text="label"></span>
                                <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <div x-show="open" x-cloak class="krd-dropdown-menu">
                                @foreach(['physical' => 'Physical Only', 'virtual' => 'Virtual Only', 'both' => 'Physical & Virtual'] as $val => $label)
                                <div class="krd-dropdown-option {{ $consultation_type === $val ? 'selected' : '' }}"
                                    x-on:click="pick('{{ $val }}', '{{ $label }}')">{{ $label }}</div>
                                @endforeach
                            </div>
                        </div>

                        <div class="krd-input-group">
                            <label class="krd-label-text">Duration (minutes)</label>
                            <input wire:model="duration_minutes" type="number" min="15" max="480"
                                class="krd-input" placeholder="60" />
                        </div>
                    </div>

                    @if(in_array($consultation_type, ['physical', 'both']))
                    <div class="krd-input-group" style="margin-bottom:0;">
                        <label class="krd-label-text">Physical Location</label>
                        <input wire:model="location" type="text" class="krd-input"
                            placeholder="e.g. 12 Adeola Odeku Street, Victoria Island, Lagos" />
                    </div>
                    @endif
                </div>
                @endif

                <button wire:click="saveDetails" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                    <span wire:loading.remove wire:target="saveDetails">{{ $form ? 'Save Changes' : 'Create Form' }}</span>
                    <span wire:loading wire:target="saveDetails">Saving...</span>
                </button>
            </div>

            {{-- Right tips --}}
            <div style="position:sticky;top:80px;display:flex;flex-direction:column;gap:12px;" id="form-details-tips">
                <div class="krd-card" style="padding:20px;">
                    <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;">💡 Tips</div>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        @foreach($type === 'consultation' ? [
                            'Set your available days and times in the Availability tab.',
                            'Guests pick a date and time slot on the public page.',
                            'Past dates and already-booked slots are automatically blocked.',
                            'Public holidays for your country are blocked automatically.',
                            'Add custom fields to collect specific information.',
                        ] : [
                            'Add custom fields to collect exactly the information you need.',
                            'Use the After Submission tab to set up WhatsApp redirect.',
                            'Share the public link or embed the form on your website.',
                            'All submissions appear in the Submissions tab.',
                            'Use the external endpoint to connect your own website form.',
                        ] as $tip)
                        <div style="display:flex;gap:8px;align-items:flex-start;">
                            <div style="width:5px;height:5px;border-radius:50%;background:#7C3AED;flex-shrink:0;margin-top:6px;"></div>
                            <p style="font-size:12px;color:#78716C;line-height:1.6;">{{ $tip }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ Fields Tab ══ --}}
    <div x-show="activeTab === 'fields'">
        @if(!$form)
        <div class="krd-card">
            <div class="krd-empty-state">
                <div class="krd-empty-state-icon">⚙️</div>
                <div class="krd-empty-state-title">Save form details first</div>
                <div class="krd-empty-state-desc">Create the form in the Details tab before adding fields.</div>
            </div>
        </div>
        @else
        <div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;">
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                    <div style="font-size:13px;color:#78716C;">
                        {{ $form->fields->count() }} {{ Str::plural('field', $form->fields->count()) }}
                    </div>
                    <button wire:click="showAddField" class="krd-btn krd-btn-primary krd-btn-sm">+ Add Field</button>
                </div>

                @if($showFieldForm)
                <div class="krd-card" style="padding:20px;margin-bottom:16px;border:2px solid #7C3AED;">
                    <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:16px;">
                        {{ $editFieldId ? 'Edit Field' : 'New Field' }}
                    </div>

                    <div class="krd-input-group">
                        <label class="krd-label-text">Label <span style="color:#EF4444;">*</span></label>
                        <input wire:model="f_label" type="text"
                            class="krd-input @error('f_label') krd-input-error @enderror"
                            placeholder="e.g. Event Date" autofocus />
                        @error('f_label') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>

                    <div class="krd-grid-2" style="gap:12px;">
                        <div class="krd-input-group"
                            x-data="{
                                open: false,
                                label: '{{ collect(['text'=>'Short Text','textarea'=>'Long Text','email'=>'Email','phone'=>'Phone','number'=>'Number','dropdown'=>'Dropdown','radio'=>'Radio','checkbox'=>'Checkbox','date'=>'Date'])->get($f_type, 'Short Text') }}',
                                pick(val, label) { this.label = label; this.open = false; $wire.set('f_type', val); }
                            }"
                            x-on:click.outside="open = false" style="position:relative;">
                            <label class="krd-label-text">Field Type</label>
                            <button type="button" x-on:click="open = !open"
                                x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'" style="width:100%;">
                                <span x-text="label"></span>
                                <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <div x-show="open" x-cloak class="krd-dropdown-menu">
                                @foreach(['text'=>'Short Text','textarea'=>'Long Text','email'=>'Email','phone'=>'Phone','number'=>'Number','dropdown'=>'Dropdown','radio'=>'Radio','checkbox'=>'Checkbox','date'=>'Date'] as $val => $label)
                                <div class="krd-dropdown-option {{ $f_type === $val ? 'selected' : '' }}"
                                    x-on:click="pick('{{ $val }}', '{{ $label }}')">{{ $label }}</div>
                                @endforeach
                            </div>
                        </div>

                        <div class="krd-input-group">
                            <label class="krd-label-text">Placeholder</label>
                            <input wire:model="f_placeholder" type="text" class="krd-input" placeholder="Optional hint..." />
                        </div>
                    </div>

                    @if(in_array($f_type, ['dropdown', 'radio', 'checkbox']))
                    <div class="krd-input-group">
                        <label class="krd-label-text">Options</label>
                        <input wire:model="f_options_raw" type="text" class="krd-input"
                            placeholder="Option 1, Option 2, Option 3" />
                        <span class="krd-input-hint">Separate with commas.</span>
                    </div>
                    @endif

                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                        <div x-data="{ on: {{ $f_required ? 'true' : 'false' }} }">
                            <input type="checkbox" wire:model="f_required" x-bind:checked="on"
                                style="display:none;" id="f_required_cb" />
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div x-on:click="on = !on; document.getElementById('f_required_cb').checked = on; document.getElementById('f_required_cb').dispatchEvent(new Event('change'))"
                                    :style="on ? 'width:44px;height:24px;border-radius:12px;background:#7C3AED;cursor:pointer;position:relative;' : 'width:44px;height:24px;border-radius:12px;background:#D6D3D1;cursor:pointer;position:relative;'">
                                    <div :style="on ? 'position:absolute;top:3px;right:3px;width:18px;height:18px;border-radius:50%;background:#fff;transition:all 200ms;' : 'position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;transition:all 200ms;'"></div>
                                </div>
                                <span style="font-size:12px;color:#57534E;" x-text="on ? 'Required' : 'Optional'"></span>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;gap:10px;">
                        <button wire:click="saveField" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                            <span wire:loading.remove wire:target="saveField">{{ $editFieldId ? 'Update' : 'Add Field' }}</span>
                            <span wire:loading wire:target="saveField">Saving...</span>
                        </button>
                        <button wire:click="$set('showFieldForm', false)" type="button" class="krd-btn krd-btn-ghost">Cancel</button>
                    </div>
                </div>
                @endif

                @if($form->fields->isEmpty() && !$showFieldForm)
                <div class="krd-card">
                    <div class="krd-empty-state">
                        <div class="krd-empty-state-icon">📝</div>
                        <div class="krd-empty-state-title">No fields yet</div>
                        <div class="krd-empty-state-desc">Add fields to collect information from your guests.</div>
                    </div>
                </div>
                @elseif($form->fields->isNotEmpty())
                <div class="krd-card" style="padding:0;overflow:hidden;">
                    @foreach($form->fields as $field)
                    <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;border-bottom:1px solid #E7E5E4;flex-wrap:wrap;">
                        <div style="display:flex;flex-direction:column;gap:2px;flex-shrink:0;">
                            <button wire:click="moveFieldUp({{ $field->id }})" class="krd-btn krd-btn-ghost krd-btn-sm" style="padding:2px 6px;font-size:10px;">↑</button>
                            <button wire:click="moveFieldDown({{ $field->id }})" class="krd-btn krd-btn-ghost krd-btn-sm" style="padding:2px 6px;font-size:10px;">↓</button>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13px;font-weight:500;color:#1C1917;">
                                {{ $field->label }}
                                @if($field->is_required)<span style="color:#EF4444;font-size:11px;margin-left:4px;">*</span>@endif
                            </div>
                            <div style="display:flex;gap:8px;margin-top:3px;flex-wrap:wrap;">
                                <span class="krd-badge krd-badge-stone" style="font-size:10px;">{{ $field->fieldTypeLabel() }}</span>
                                @if($field->placeholder)
                                <span style="font-size:11px;color:#A8A29E;">{{ $field->placeholder }}</span>
                                @endif
                                @if($field->options)
                                <span style="font-size:11px;color:#A8A29E;">{{ implode(', ', $field->options) }}</span>
                                @endif
                            </div>
                        </div>
                        <div style="display:flex;gap:6px;flex-shrink:0;">
                            <button wire:click="editField({{ $field->id }})" class="krd-btn krd-btn-secondary krd-btn-sm">Edit</button>
                            <button wire:click="deleteField({{ $field->id }})"
                                class="krd-btn krd-btn-sm"
                                style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Right --}}
            <div style="position:sticky;top:80px;">
                <div class="krd-card" style="padding:20px;">
                    <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;">📝 Field Types</div>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        @foreach(['Short Text' => 'Single line text', 'Long Text' => 'Multi-line textarea', 'Email' => 'Email address field', 'Phone' => 'Phone number field', 'Number' => 'Numeric input', 'Dropdown' => 'Select from a list', 'Radio' => 'Choose one option', 'Checkbox' => 'Choose multiple options', 'Date' => 'Date picker'] as $type => $desc)
                        <div style="display:flex;gap:8px;align-items:flex-start;">
                            <span class="krd-badge krd-badge-stone" style="font-size:10px;flex-shrink:0;">{{ $type }}</span>
                            <span style="font-size:11px;color:#78716C;">{{ $desc }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- ══ After Submission Tab ══ --}}
    <div x-show="activeTab === 'redirect'">
        @if(!$form)
        <div class="krd-card">
            <div class="krd-empty-state">
                <div class="krd-empty-state-icon">⚙️</div>
                <div class="krd-empty-state-title">Save form details first</div>
            </div>
        </div>
        @else
        <div style="max-width:560px;">
            <div class="krd-card" style="padding:24px;">
                <div class="krd-label" style="margin-bottom:16px;">After Submission Action</div>

                {{-- Redirect type --}}
                <div class="krd-input-group"
                    x-data="{
                        open: false,
                        label: '{{ ['none' => 'No redirect (show thank you message)', 'url' => 'Redirect to URL', 'whatsapp' => 'Redirect to WhatsApp'][$redirect_type] ?? 'No redirect' }}',
                        pick(val, label) { this.label = label; this.open = false; $wire.set('redirect_type', val); }
                    }"
                    x-on:click.outside="open = false" style="position:relative;">
                    <label class="krd-label-text">What happens after submission?</label>
                    <button type="button" x-on:click="open = !open"
                        x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'" style="width:100%;">
                        <span x-text="label"></span>
                        <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-cloak class="krd-dropdown-menu">
                        @foreach(['none' => 'No redirect (show thank you message)', 'url' => 'Redirect to URL', 'whatsapp' => 'Redirect to WhatsApp'] as $val => $label)
                        <div class="krd-dropdown-option {{ $redirect_type === $val ? 'selected' : '' }}"
                            x-on:click="pick('{{ $val }}', '{{ $label }}')">{{ $label }}</div>
                        @endforeach
                    </div>
                </div>

                @if($redirect_type === 'url')
                <div class="krd-input-group">
                    <label class="krd-label-text">Redirect URL</label>
                    <input wire:model="redirect_url" type="url" class="krd-input"
                        placeholder="https://yourwebsite.com/thank-you" />
                </div>
                @endif

                @if($redirect_type === 'whatsapp')
                <div class="krd-input-group">
                    <label class="krd-label-text">WhatsApp Number <span style="color:#EF4444;">*</span></label>
                    <input wire:model="whatsapp_number" type="text" class="krd-input"
                        placeholder="+2348012345678" />
                    <span class="krd-input-hint">Include country code. e.g. +2348012345678</span>
                </div>
                <div class="krd-input-group">
                    <label class="krd-label-text">Pre-filled Message</label>
                    <textarea wire:model="whatsapp_message" class="krd-input" rows="3"
                        placeholder="Hello, I just submitted a booking enquiry via your website..."></textarea>
                    <span class="krd-input-hint">Use {name} to insert the guest's name automatically.</span>
                </div>
                @endif

                <button wire:click="saveRedirect" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                    <span wire:loading.remove wire:target="saveRedirect">Save Settings</span>
                    <span wire:loading wire:target="saveRedirect">Saving...</span>
                </button>
            </div>
        </div>
        @endif
    </div>

    {{-- ══ Availability Tab (Consultation only) ══ --}}
    <div x-show="activeTab === 'availability'">
        @if(!$form)
        <div class="krd-card">
            <div class="krd-empty-state">
                <div class="krd-empty-state-icon">⚙️</div>
                <div class="krd-empty-state-title">Save form details first</div>
            </div>
        </div>
        @else
        <div style="max-width:560px;">
            <div class="krd-card" style="padding:24px;">
                <div class="krd-label" style="margin-bottom:6px;">Weekly Availability</div>
                <p style="font-size:12px;color:#A8A29E;margin-bottom:20px;line-height:1.6;">
                    Set the days and hours you're available for consultations.
                    Public holidays and already-booked slots will be blocked automatically.
                </p>

                <div style="display:flex;flex-direction:column;gap:0;">
                    @php
                        $days = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
                    @endphp
                    @foreach($days as $dayNum => $dayName)
                    <div style="display:flex;align-items:center;gap:16px;padding:14px 0;border-bottom:1px solid #E7E5E4;flex-wrap:wrap;">
                        {{-- Day toggle --}}
                        <div style="width:100px;flex-shrink:0;"
                            x-data="{ on: {{ $availability[$dayNum]['active'] ? 'true' : 'false' }} }">
                            <input type="checkbox"
                                wire:model="availability.{{ $dayNum }}.active"
                                x-bind:checked="on"
                                style="display:none;"
                                id="day_{{ $dayNum }}_cb" />
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div x-on:click="on = !on; document.getElementById('day_{{ $dayNum }}_cb').checked = on; document.getElementById('day_{{ $dayNum }}_cb').dispatchEvent(new Event('change'))"
                                    :style="on ? 'width:36px;height:20px;border-radius:10px;background:#7C3AED;cursor:pointer;position:relative;flex-shrink:0;' : 'width:36px;height:20px;border-radius:10px;background:#D6D3D1;cursor:pointer;position:relative;flex-shrink:0;'">
                                    <div :style="on ? 'position:absolute;top:2px;right:2px;width:16px;height:16px;border-radius:50%;background:#fff;transition:all 200ms;' : 'position:absolute;top:2px;left:2px;width:16px;height:16px;border-radius:50%;background:#fff;transition:all 200ms;'"></div>
                                </div>
                                <span style="font-size:13px;font-weight:500;color:#1C1917;">{{ $dayName }}</span>
                            </div>
                        </div>

                        {{-- Time inputs --}}
                        @if($availability[$dayNum]['active'])
                        <div style="display:flex;align-items:center;gap:10px;flex:1;flex-wrap:wrap;">
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span style="font-size:12px;color:#78716C;">From</span>
                                <input wire:model="availability.{{ $dayNum }}.start" type="time"
                                    class="krd-input" style="max-width:120px;" />
                            </div>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span style="font-size:12px;color:#78716C;">To</span>
                                <input wire:model="availability.{{ $dayNum }}.end" type="time"
                                    class="krd-input" style="max-width:120px;" />
                            </div>
                        </div>
                        @else
                        <span style="font-size:12px;color:#A8A29E;">Unavailable</span>
                        @endif
                    </div>
                    @endforeach
                </div>

                <div style="margin-top:20px;">
                    <button wire:click="saveAvailability" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                        <span wire:loading.remove wire:target="saveAvailability">Save Availability</span>
                        <span wire:loading wire:target="saveAvailability">Saving...</span>
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- ══ Embed & Share Tab ══ --}}
    <div x-show="activeTab === 'embed'">
        @if(!$form)
        <div class="krd-card">
            <div class="krd-empty-state">
                <div class="krd-empty-state-icon">⚙️</div>
                <div class="krd-empty-state-title">Save form details first</div>
            </div>
        </div>
        @else
        <div style="display:flex;flex-direction:column;gap:16px;max-width:680px;">

            {{-- Public link --}}
            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;">🔗 Public Link</div>
                <div style="font-size:12px;color:#1C1917;font-family:monospace;word-break:break-all;background:#F5F5F4;padding:10px 12px;border-radius:6px;margin-bottom:10px;">
                    {{ $form->publicUrl() }}
                </div>
                <div style="display:flex;gap:8px;">
                    <button x-data
                        x-on:click="navigator.clipboard.writeText('{{ $form->publicUrl() }}').then(() => { KrdToast.success('Link copied!') })"
                        class="krd-btn krd-btn-secondary krd-btn-sm">
                        🔗 Copy Link
                    </button>
                    <a href="{{ $form->publicUrl() }}" target="_blank" class="krd-btn krd-btn-ghost krd-btn-sm">
                        Preview ↗
                    </a>
                </div>
            </div>

            {{-- External endpoint --}}
            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:6px;">⚡ External Endpoint</div>
                <p style="font-size:12px;color:#78716C;line-height:1.6;margin-bottom:12px;">
                    Use this URL as your HTML form's <code style="background:#F5F5F4;padding:1px 5px;border-radius:3px;">action</code> attribute.
                    Works like Formspree — submissions go directly to your Koordli dashboard.
                </p>
                <div style="font-size:12px;color:#7C3AED;font-family:monospace;word-break:break-all;background:#F5F3FF;border:1px solid #DDD6FE;padding:10px 12px;border-radius:6px;margin-bottom:10px;">
                    POST {{ $form->endpointUrl() }}
                </div>
                <div style="background:#1C1917;border-radius:6px;padding:16px;margin-bottom:10px;overflow-x:auto;">
                    <pre style="font-size:11px;color:#A8A29E;margin:0;font-family:monospace;line-height:1.8;">&lt;form action="{{ $form->endpointUrl() }}" method="POST"&gt;
  &lt;input type="hidden" name="_token" value="your-csrf-token"&gt;
  @foreach($form->fields as $field)  &lt;input type="{{ in_array($field->field_type, ['email','phone','number','date']) ? $field->field_type : 'text' }}" name="{{ $field->label }}" {{ $field->is_required ? 'required' : '' }}&gt;
  @endforeach  &lt;button type="submit"&gt;Submit&lt;/button&gt;
&lt;/form&gt;</pre>
                </div>
                <button x-data
                    x-on:click="navigator.clipboard.writeText('{{ $form->endpointUrl() }}').then(() => { KrdToast.success('Endpoint copied!') })"
                    class="krd-btn krd-btn-secondary krd-btn-sm">
                    Copy Endpoint URL
                </button>
            </div>

            {{-- Embed code --}}
            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:6px;">🖼️ Embed on Your Website</div>
                <p style="font-size:12px;color:#78716C;line-height:1.6;margin-bottom:12px;">
                    Paste this code anywhere on your website to embed the full form.
                </p>
                <div style="background:#1C1917;border-radius:6px;padding:16px;margin-bottom:10px;overflow-x:auto;">
                    <pre style="font-size:11px;color:#A8A29E;margin:0;font-family:monospace;">{{ $form->embedCode() }}</pre>
                </div>
                <button x-data
                    x-on:click="navigator.clipboard.writeText('{{ addslashes($form->embedCode()) }}').then(() => { KrdToast.success('Embed code copied!') })"
                    class="krd-btn krd-btn-secondary krd-btn-sm">
                    Copy Embed Code
                </button>
            </div>

        </div>
        @endif
    </div>

</div>

<style>
@media (max-width: 768px) {
    #form-details-grid { grid-template-columns: 1fr !important; }
    #form-details-tips { position: static !important; }
}
</style>