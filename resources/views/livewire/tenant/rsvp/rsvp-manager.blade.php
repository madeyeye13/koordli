<div x-data="{ activeTab: '{{ $activeTab }}' }">

    {{-- Header --}}
    <div style="margin-bottom:24px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('tenant.events.show', $event->slug) }}" wire:navigate
                style="color:#A8A29E;text-decoration:none;font-size:13px;">
                ← Back to {{ Str::limit($event->name, 30) }}
            </a>
        </div>
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <div class="krd-label" style="margin-bottom:4px;">RSVP</div>
                <h2 class="krd-heading-3" style="color:#1C1917;">{{ $event->name }}</h2>
                @if($form)
                <div style="margin-top:6px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <span class="krd-badge {{ $form->is_active ? 'krd-badge-green' : 'krd-badge-stone' }}">
                        {{ $form->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <button
                        x-data
                        x-on:click="navigator.clipboard.writeText('{{ $form->publicUrl() }}').then(() => { KrdToast.success('RSVP link copied!') })"
                        class="krd-btn krd-btn-secondary krd-btn-sm">
                        🔗 Copy RSVP Link
                    </button>
                    <a href="{{ $form->publicUrl() }}" target="_blank" class="krd-btn krd-btn-ghost krd-btn-sm">
                        Preview ↗
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Stats --}}
    @if($form)
    <div class="krd-grid-4" style="margin-bottom:20px;gap:10px;">
        <div class="krd-card" style="padding:14px;border-left:3px solid #7C3AED;">
            <div class="krd-label" style="margin-bottom:4px;">Total Responses</div>
            <div style="font-size:24px;font-weight:700;color:#7C3AED;">{{ $stats['total'] }}</div>
        </div>
        <div class="krd-card" style="padding:14px;border-left:3px solid #10B981;">
            <div class="krd-label" style="margin-bottom:4px;">Attending</div>
            <div style="font-size:24px;font-weight:700;color:#10B981;">{{ $stats['confirmed'] }}</div>
            <div style="font-size:11px;color:#A8A29E;">{{ $stats['attendees'] }} total attendees</div>
        </div>
        <div class="krd-card" style="padding:14px;border-left:3px solid #EF4444;">
            <div class="krd-label" style="margin-bottom:4px;">Not Attending</div>
            <div style="font-size:24px;font-weight:700;color:#EF4444;">{{ $stats['declined'] }}</div>
        </div>
        <div class="krd-card" style="padding:14px;border-left:3px solid #F59E0B;">
            <div class="krd-label" style="margin-bottom:4px;">Checked In</div>
            <div style="font-size:24px;font-weight:700;color:#F59E0B;">{{ $stats['checked_in'] }}</div>
        </div>
    </div>
    @endif

    {{-- Tabs + Content --}}
    <div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;" id="rsvp-main-grid">

        {{-- Left: Tab Content --}}
        <div>
            {{-- Tabs --}}
            <div style="display:flex;gap:4px;border-bottom:1px solid #E7E5E4;margin-bottom:20px;">
                @foreach(['setup' => 'Setup', 'questions' => 'Questions', 'branding' => 'Branding', 'responses' => 'Responses'] as $tab => $label)
                <button type="button"
                    x-on:click="activeTab = '{{ $tab }}'; $wire.setTab('{{ $tab }}')"
                    :style="activeTab === '{{ $tab }}'
                        ? 'padding:10px 16px;font-size:13px;font-weight:500;border:none;background:none;cursor:pointer;border-bottom:2px solid #7C3AED;color:#7C3AED;margin-bottom:-1px;'
                        : 'padding:10px 16px;font-size:13px;font-weight:500;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;color:#78716C;margin-bottom:-1px;'"
                >
                    {{ $label }}
                    @if($tab === 'responses' && ($stats['total'] ?? 0) > 0)
                    <span style="font-size:10px;background:#EDE9FE;color:#7C3AED;padding:1px 6px;border-radius:10px;font-weight:600;margin-left:4px;">{{ $stats['total'] }}</span>
                    @endif
                </button>
                @endforeach
            </div>

            {{-- ══ TAB: Setup ══ --}}
            <div x-show="activeTab === 'setup'">
                <div class="krd-card" style="padding:24px;">
                    <div class="krd-label" style="margin-bottom:16px;">RSVP Form Settings</div>

                    <div class="krd-input-group">
                        <label class="krd-label-text">Form Title <span style="color:#EF4444;">*</span></label>
                        <input wire:model="title" type="text"
                            class="krd-input @error('title') krd-input-error @enderror"
                            placeholder="e.g. John & Sarah Wedding RSVP" />
                        @error('title') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>

                    <div class="krd-grid-2" style="gap:12px;">
                        <div class="krd-input-group">
                            <label class="krd-label-text">RSVP Deadline</label>
                            <input wire:model="deadline" type="datetime-local"
                                class="krd-input @error('deadline') krd-input-error @enderror" />
                            @error('deadline') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                            <span class="krd-input-hint">Optional — close RSVP after this date.</span>
                        </div>
                        <div class="krd-input-group">
                            <label class="krd-label-text">Guest Limit</label>
                            <input wire:model="guest_limit" type="number" min="1"
                                class="krd-input @error('guest_limit') krd-input-error @enderror"
                                placeholder="Leave blank for unlimited" />
                            @error('guest_limit') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Active toggle --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 0;border-top:1px solid #E7E5E4;margin-top:4px;">
                        <div>
                            <div style="font-size:13px;font-weight:500;color:#1C1917;">RSVP Active</div>
                            <div style="font-size:12px;color:#78716C;margin-top:2px;">Turn off to stop accepting new responses.</div>
                        </div>
                        <div x-data="{ on: {{ $is_active ? 'true' : 'false' }} }">
                            <input type="checkbox" wire:model="is_active"
                                x-bind:checked="on"
                                style="display:none;" id="is_active_input" />
                            <div x-on:click="
                                    on = !on;
                                    document.getElementById('is_active_input').checked = on;
                                    document.getElementById('is_active_input').dispatchEvent(new Event('change'));
                                "
                                :style="on
                                    ? 'width:44px;height:24px;border-radius:12px;background:#7C3AED;cursor:pointer;position:relative;flex-shrink:0;'
                                    : 'width:44px;height:24px;border-radius:12px;background:#D6D3D1;cursor:pointer;position:relative;flex-shrink:0;'">
                                <div :style="on
                                    ? 'position:absolute;top:3px;right:3px;width:18px;height:18px;border-radius:50%;background:#fff;transition:all 200ms;'
                                    : 'position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;transition:all 200ms;'">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:16px;">
                        <button wire:click="saveForm" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                            <span wire:loading.remove wire:target="saveForm">{{ $form ? 'Update Settings' : 'Create RSVP Form' }}</span>
                            <span wire:loading wire:target="saveForm">Saving...</span>
                        </button>
                    </div>

                    @if($form)
                    <div style="margin-top:20px;padding:14px 16px;background:#F5F3FF;border-radius:6px;border:1px solid #DDD6FE;">
                        <div style="font-size:11px;font-weight:600;color:#7C3AED;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">Public RSVP Link</div>
                        <div style="font-size:12px;color:#1C1917;font-family:monospace;word-break:break-all;margin-bottom:8px;">{{ $form->publicUrl() }}</div>
                        <button
                            x-data
                            x-on:click="navigator.clipboard.writeText('{{ $form->publicUrl() }}').then(() => { KrdToast.success('RSVP link copied!') })"
                            class="krd-btn krd-btn-secondary krd-btn-sm">
                            🔗 Copy Link
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ══ TAB: Questions ══ --}}
            <div x-show="activeTab === 'questions'">
                @if(!$form)
                <div class="krd-card">
                    <div class="krd-empty-state">
                        <div class="krd-empty-state-icon">⚙️</div>
                        <div class="krd-empty-state-title">Save form settings first</div>
                        <div class="krd-empty-state-desc">Go to the Setup tab to create your RSVP form before adding questions.</div>
                    </div>
                </div>
                @else

                <div style="background:#FEF3C7;border:1px solid #FDE68A;border-radius:6px;padding:12px 16px;margin-bottom:16px;display:flex;gap:10px;">
                    <span style="font-size:16px;">ℹ️</span>
                    <div style="font-size:12px;color:#92400E;line-height:1.7;">
                        <strong>System fields always included:</strong> Full Name, Email Address, Will You Attend?, Number of Attendees.
                        Add custom questions below.
                    </div>
                </div>

                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                    <div style="font-size:13px;color:#78716C;">
                        {{ $form->questions?->count() ?? 0 }} custom {{ Str::plural('question', $form->questions?->count() ?? 0) }}
                    </div>
                    <button wire:click="showAddQuestion" class="krd-btn krd-btn-primary krd-btn-sm">+ Add Question</button>
                </div>

                @if($showQuestionForm)
                <div class="krd-card" style="padding:20px;margin-bottom:16px;border:2px solid #7C3AED;">
                    <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:16px;">
                        {{ $editQuestionId ? 'Edit Question' : 'New Question' }}
                    </div>

                    <div class="krd-input-group">
                        <label class="krd-label-text">Question Label <span style="color:#EF4444;">*</span></label>
                        <input wire:model="q_label" type="text"
                            class="krd-input @error('q_label') krd-input-error @enderror"
                            placeholder="e.g. Meal Preference" />
                        @error('q_label') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>

                    <div class="krd-grid-2" style="gap:12px;">
                        <div class="krd-input-group"
                            x-data="{
                                open: false,
                                label: '{{ collect(['text'=>'Short Text','textarea'=>'Long Text','email'=>'Email','phone'=>'Phone','number'=>'Number','dropdown'=>'Dropdown','checkbox'=>'Checkbox','radio'=>'Radio','yes_no'=>'Yes / No','date'=>'Date'])->get($q_field_type, 'Short Text') }}',
                                pick(val, label) { this.label = label; this.open = false; $wire.set('q_field_type', val); }
                            }"
                            x-on:click.outside="open = false"
                            style="position:relative;">
                            <label class="krd-label-text">Field Type</label>
                            <button type="button"
                                x-on:click="open = !open"
                                x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'"
                                style="width:100%;">
                                <span x-text="label"></span>
                                <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <div x-show="open" x-cloak class="krd-dropdown-menu">
                                @foreach(['text'=>'Short Text','textarea'=>'Long Text','email'=>'Email','phone'=>'Phone','number'=>'Number','dropdown'=>'Dropdown','checkbox'=>'Checkbox','radio'=>'Radio','yes_no'=>'Yes / No','date'=>'Date'] as $val => $label)
                                <div class="krd-dropdown-option {{ $q_field_type === $val ? 'selected' : '' }}"
                                    x-on:click="pick('{{ $val }}', '{{ $label }}')">{{ $label }}</div>
                                @endforeach
                            </div>
                        </div>

                        <div class="krd-input-group">
                            <label class="krd-label-text">Required?</label>
                            <div x-data="{ on: {{ $q_is_required ? 'true' : 'false' }} }" style="padding-top:8px;">
                                <input type="checkbox" wire:model="q_is_required"
                                    x-bind:checked="on"
                                    style="display:none;" id="q_required_input" />
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div x-on:click="
                                            on = !on;
                                            document.getElementById('q_required_input').checked = on;
                                            document.getElementById('q_required_input').dispatchEvent(new Event('change'));
                                        "
                                        :style="on
                                            ? 'width:44px;height:24px;border-radius:12px;background:#7C3AED;cursor:pointer;position:relative;'
                                            : 'width:44px;height:24px;border-radius:12px;background:#D6D3D1;cursor:pointer;position:relative;'">
                                        <div :style="on
                                            ? 'position:absolute;top:3px;right:3px;width:18px;height:18px;border-radius:50%;background:#fff;transition:all 200ms;'
                                            : 'position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;transition:all 200ms;'">
                                        </div>
                                    </div>
                                    <span style="font-size:12px;color:#57534E;" x-text="on ? 'Required' : 'Optional'"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(in_array($q_field_type, ['dropdown', 'radio', 'checkbox']))
                    <div class="krd-input-group">
                        <label class="krd-label-text">Options <span style="color:#EF4444;">*</span></label>
                        <input wire:model="q_options_raw" type="text" class="krd-input"
                            placeholder="Option 1, Option 2, Option 3" />
                        <span class="krd-input-hint">Separate options with commas.</span>
                    </div>
                    @endif

                    <div style="display:flex;gap:10px;margin-top:8px;">
                        <button wire:click="saveQuestion" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                            <span wire:loading.remove wire:target="saveQuestion">{{ $editQuestionId ? 'Update' : 'Add Question' }}</span>
                            <span wire:loading wire:target="saveQuestion">Saving...</span>
                        </button>
                        <button wire:click="$set('showQuestionForm', false)" type="button" class="krd-btn krd-btn-ghost">Cancel</button>
                    </div>
                </div>
                @endif

                @if($form->questions?->isEmpty())
                <div class="krd-card">
                    <div class="krd-empty-state">
                        <div class="krd-empty-state-icon">❓</div>
                        <div class="krd-empty-state-title">No custom questions yet</div>
                        <div class="krd-empty-state-desc">Add questions like meal preference, accommodation needs, or song requests.</div>
                    </div>
                </div>
                @else
                <div class="krd-card" style="padding:0;overflow:hidden;">
                    @foreach($form->questions ?? [] as $q)
                    <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;border-bottom:1px solid #E7E5E4;flex-wrap:wrap;">
                        <div style="display:flex;flex-direction:column;gap:2px;flex-shrink:0;">
                            <button wire:click="moveUp({{ $q->id }})" class="krd-btn krd-btn-ghost krd-btn-sm" style="padding:2px 6px;font-size:10px;">↑</button>
                            <button wire:click="moveDown({{ $q->id }})" class="krd-btn krd-btn-ghost krd-btn-sm" style="padding:2px 6px;font-size:10px;">↓</button>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13px;font-weight:500;color:#1C1917;">
                                {{ $q->label }}
                                @if($q->is_required)<span style="color:#EF4444;font-size:11px;margin-left:4px;">*</span>@endif
                            </div>
                            <div style="display:flex;gap:8px;margin-top:3px;flex-wrap:wrap;">
                                <span class="krd-badge krd-badge-stone" style="font-size:10px;">{{ $q->fieldTypeLabel() }}</span>
                                @if($q->options)
                                <span style="font-size:11px;color:#A8A29E;">{{ implode(', ', $q->options) }}</span>
                                @endif
                            </div>
                        </div>
                        <div style="display:flex;gap:6px;flex-shrink:0;">
                            <button wire:click="editQuestion({{ $q->id }})" class="krd-btn krd-btn-secondary krd-btn-sm">Edit</button>
                            <button wire:click="deleteQuestion({{ $q->id }})"
                                class="krd-btn krd-btn-sm"
                                style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                                    <path d="M10 11v6M14 11v6M9 6V4h6v2"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
                @endif
            </div>

            {{-- ══ TAB: Branding ══ --}}
            <div x-show="activeTab === 'branding'">
                @if(!$form)
                <div class="krd-card">
                    <div class="krd-empty-state">
                        <div class="krd-empty-state-icon">🎨</div>
                        <div class="krd-empty-state-title">Save form settings first</div>
                        <div class="krd-empty-state-desc">Create your RSVP form in the Setup tab before customising branding.</div>
                    </div>
                </div>
                @else
                <div class="krd-card" style="padding:24px;max-width:560px;">
                    <div class="krd-label" style="margin-bottom:20px;">RSVP Page Branding</div>

                    {{-- Cover Image --}}
                    <div class="krd-input-group">
                        <label class="krd-label-text">Cover / Hero Image</label>
                        @if($cover_image_url)
                        <div style="margin-bottom:10px;position:relative;border-radius:6px;overflow:hidden;border:1px solid #E7E5E4;">
                            <img src="{{ $cover_image_url }}" style="width:100%;height:160px;object-fit:cover;display:block;" />
                            <button wire:click="removeCoverImage" type="button"
                                style="position:absolute;top:8px;right:8px;background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:4px;padding:4px 10px;font-size:11px;cursor:pointer;font-weight:500;">
                                Remove
                            </button>
                        </div>
                        @endif
                        @if($cover_image)
                        <div style="margin-bottom:10px;border-radius:6px;overflow:hidden;border:1px solid #DDD6FE;">
                            <img src="{{ $cover_image->temporaryUrl() }}" style="width:100%;height:160px;object-fit:cover;display:block;" />
                        </div>
                        @endif
                        <input wire:model="cover_image" type="file" accept="image/*"
                            class="krd-input" style="padding:7px 12px;cursor:pointer;" />
                        @error('cover_image') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                        <span class="krd-input-hint">JPG or PNG. Max 3MB. Displayed as the left panel background on the RSVP page.</span>
                    </div>

                    {{-- Colors --}}
                    <div class="krd-grid-2" style="gap:16px;margin-bottom:4px;">
                        <div class="krd-input-group">
                            <label class="krd-label-text">Accent Color</label>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <input wire:model.live="accent_color" type="color"
                                    style="width:40px;height:36px;border:1px solid #E7E5E4;border-radius:4px;cursor:pointer;padding:2px;" />
                                <input wire:model.live="accent_color" type="text"
                                    class="krd-input" placeholder="#7C3AED"
                                    style="font-family:monospace;text-transform:uppercase;" />
                            </div>
                            <span class="krd-input-hint">Used for buttons and highlights on the RSVP page.</span>
                        </div>

                        <div class="krd-input-group">
                            <label class="krd-label-text">Background Color</label>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <input wire:model.live="bg_color" type="color"
                                    style="width:40px;height:36px;border:1px solid #E7E5E4;border-radius:4px;cursor:pointer;padding:2px;" />
                                <input wire:model.live="bg_color" type="text"
                                    class="krd-input" placeholder="#FAFAF9"
                                    style="font-family:monospace;text-transform:uppercase;" />
                            </div>
                            <span class="krd-input-hint">Right panel background color.</span>
                        </div>
                    </div>

                    {{-- Live Preview --}}
                    <div style="margin-top:8px;margin-bottom:20px;padding:16px;border:1px solid #E7E5E4;border-radius:6px;background:#F5F5F4;">
                        <div style="font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#A8A29E;margin-bottom:12px;">Preview</div>
                        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                            <button style="background:{{ $accent_color }};color:#fff;border:none;border-radius:6px;padding:10px 20px;font-size:13px;font-weight:600;cursor:default;">
                                Submit RSVP
                            </button>
                            <button style="border:1.5px solid {{ $accent_color }};color:{{ $accent_color }};background:transparent;border-radius:6px;padding:10px 20px;font-size:13px;font-weight:500;cursor:default;">
                                Yes, I'll attend
                            </button>
                            <div style="width:48px;height:28px;border-radius:14px;background:{{ $accent_color }};position:relative;">
                                <div style="position:absolute;top:4px;right:4px;width:20px;height:20px;border-radius:50%;background:#fff;"></div>
                            </div>
                        </div>
                    </div>

                    <button wire:click="saveBranding" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                        <span wire:loading.remove wire:target="saveBranding">Save Branding</span>
                        <span wire:loading wire:target="saveBranding">Saving...</span>
                    </button>
                </div>
                @endif
            </div>

            {{-- ══ TAB: Responses ══ --}}
            <div x-show="activeTab === 'responses'">
                @if(!$form || $responses->isEmpty())
                <div class="krd-card">
                    <div class="krd-empty-state">
                        <div class="krd-empty-state-icon">📋</div>
                        <div class="krd-empty-state-title">No responses yet</div>
                        <div class="krd-empty-state-desc">Share the RSVP link to start collecting responses.</div>
                    </div>
                </div>
                @else
                <div class="krd-card" style="padding:0;overflow:hidden;" id="rsvp-responses-desktop">
                    <div class="krd-table-wrap">
                        <table class="krd-table">
                            <thead>
                                <tr>
                                    <th>Guest</th>
                                    <th>Status</th>
                                    <th>Attendees</th>
                                    <th>Submitted</th>
                                    <th>Check-in</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($responses as $response)
                                <tr>
                                    <td>
                                        <div style="font-size:13px;font-weight:500;color:#1C1917;">{{ $response->respondent_name }}</div>
                                        @if($response->respondent_email)
                                        <div style="font-size:11px;color:#A8A29E;">{{ $response->respondent_email }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($response->status === 'confirmed')
                                        <span class="krd-badge krd-badge-green">Attending</span>
                                        @elseif($response->status === 'declined')
                                        <span class="krd-badge krd-badge-red">Not Attending</span>
                                        @else
                                        <span class="krd-badge krd-badge-amber">Pending</span>
                                        @endif
                                    </td>
                                    <td style="font-size:13px;color:#57534E;">{{ $response->totalAttendees() }}</td>
                                    <td style="font-size:12px;color:#78716C;">{{ $response->created_at->format('M d, Y') }}</td>
                                    <td>
                                        @if($response->status === 'confirmed')
                                        <button wire:click="checkInResponse({{ $response->id }})"
                                            class="krd-btn krd-btn-sm"
                                            style="background:{{ $response->checked_in_at ? '#D1FAE5' : '#F5F5F4' }};color:{{ $response->checked_in_at ? '#065F46' : '#57534E' }};border-color:{{ $response->checked_in_at ? '#6EE7B7' : '#E7E5E4' }};">
                                            {{ $response->checked_in_at ? '✓ In' : 'Check In' }}
                                        </button>
                                        @else
                                        <span style="color:#A8A29E;font-size:12px;">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button wire:click="confirmDeleteResponse({{ $response->id }})"
                                            class="krd-btn krd-btn-sm"
                                            style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="rsvp-responses-mobile" style="display:flex;flex-direction:column;gap:10px;">
                    @foreach($responses as $response)
                    <div class="krd-card" style="padding:16px;">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:10px;">
                            <div>
                                <div style="font-size:14px;font-weight:600;color:#1C1917;">{{ $response->respondent_name }}</div>
                                @if($response->respondent_email)
                                <div style="font-size:11px;color:#A8A29E;">{{ $response->respondent_email }}</div>
                                @endif
                            </div>
                            @if($response->status === 'confirmed')
                            <span class="krd-badge krd-badge-green">Attending</span>
                            @elseif($response->status === 'declined')
                            <span class="krd-badge krd-badge-red">Not Attending</span>
                            @else
                            <span class="krd-badge krd-badge-amber">Pending</span>
                            @endif
                        </div>
                        <div style="font-size:12px;color:#78716C;margin-bottom:10px;">
                            {{ $response->totalAttendees() }} {{ $response->totalAttendees() === 1 ? 'person' : 'people' }}
                            · {{ $response->created_at->format('M d, Y') }}
                        </div>
                        <div style="display:flex;gap:8px;">
                            @if($response->status === 'confirmed')
                            <button wire:click="checkInResponse({{ $response->id }})"
                                class="krd-btn krd-btn-sm"
                                style="background:{{ $response->checked_in_at ? '#D1FAE5' : '#F5F5F4' }};color:{{ $response->checked_in_at ? '#065F46' : '#57534E' }};border-color:{{ $response->checked_in_at ? '#6EE7B7' : '#E7E5E4' }};">
                                {{ $response->checked_in_at ? '✓ Checked In' : 'Check In' }}
                            </button>
                            @endif
                            <button wire:click="confirmDeleteResponse({{ $response->id }})"
                                class="krd-btn krd-btn-sm"
                                style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">Delete</button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Right: Help Panel --}}
        <div style="display:flex;flex-direction:column;gap:12px;position:sticky;top:80px;" id="rsvp-help-panel">

            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:14px;">💡 How RSVP works</div>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    @foreach([
                        ['step' => '1', 'title' => 'Set up your form', 'desc' => 'Configure title, deadline, and guest limit in the Setup tab.'],
                        ['step' => '2', 'title' => 'Add custom questions', 'desc' => 'Go to Questions tab to add meal preferences, requirements, and more.'],
                        ['step' => '3', 'title' => 'Share the link', 'desc' => 'Copy your RSVP link and share it via WhatsApp, email, or social media.'],
                        ['step' => '4', 'title' => 'Track responses', 'desc' => 'Watch responses come in on the Responses tab. Confirmed guests get a QR entry pass automatically.'],
                        ['step' => '5', 'title' => 'Check in at the event', 'desc' => 'Scan or manually check in guests on the day of the event.'],
                    ] as $item)
                    <div style="display:flex;gap:10px;align-items:flex-start;">
                        <div style="width:22px;height:22px;border-radius:50%;background:#EDE9FE;color:#7C3AED;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            {{ $item['step'] }}
                        </div>
                        <div>
                            <div style="font-size:12px;font-weight:600;color:#1C1917;margin-bottom:2px;">{{ $item['title'] }}</div>
                            <div style="font-size:11px;color:#78716C;line-height:1.6;">{{ $item['desc'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="krd-card" style="padding:20px;background:#F0FDF4;border-color:#86EFAC;">
                <div style="font-size:13px;font-weight:600;color:#166534;margin-bottom:10px;">🎫 QR Entry Passes</div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @foreach([
                        'Guests who confirm attendance automatically receive a unique QR code.',
                        'QR codes are included in confirmation emails.',
                        'Guests can download their ticket from the confirmation page.',
                        'Scan QR codes at the event to mark attendance.',
                    ] as $tip)
                    <div style="display:flex;gap:8px;align-items:flex-start;">
                        <div style="width:5px;height:5px;border-radius:50%;background:#16A34A;flex-shrink:0;margin-top:6px;"></div>
                        <p style="font-size:11px;color:#166534;line-height:1.6;">{{ $tip }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:10px;">📋 Default fields</div>
                <div style="display:flex;flex-direction:column;gap:6px;">
                    @foreach(['Full Name', 'Email Address', 'Will You Attend?', 'Number of Additional Guests'] as $field)
                    <div style="display:flex;align-items:center;gap:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                        <span style="font-size:12px;color:#57534E;">{{ $field }}</span>
                    </div>
                    @endforeach
                    <p style="font-size:11px;color:#A8A29E;margin-top:6px;line-height:1.6;">These are always included. Add more in the Questions tab.</p>
                </div>
            </div>

        </div>

    </div>

    {{-- Delete Response Modal --}}
    @if($showDeleteResponseModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Remove Response?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">This will permanently remove this RSVP response.</p>
            <div style="display:flex;gap:10px;">
                <button wire:click="deleteResponse" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Remove</button>
                <button wire:click="$set('showDeleteResponseModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif

</div>

<style>
@media (max-width: 768px) {
    #rsvp-main-grid { grid-template-columns: 1fr !important; }
    #rsvp-help-panel { position: static !important; }
    #rsvp-responses-desktop { display: none !important; }
    #rsvp-responses-mobile  { display: flex !important; }
}
@media (min-width: 769px) {
    #rsvp-responses-desktop { display: block !important; }
    #rsvp-responses-mobile  { display: none !important; }
}
</style>