<div>
    {{-- Header --}}
    <div style="margin-bottom:28px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('tenant.events') }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">
                ← Back to Events
            </a>
        </div>
        <div class="krd-label" style="margin-bottom:4px;">Operations</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">
            {{ $event ? 'Edit Event' : 'New Event' }}
        </h2>
    </div>

    <div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;">

        {{-- Left — Form --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            {{-- Basic Details --}}
            <div class="krd-card" style="padding:24px;">
                <div class="krd-label" style="margin-bottom:16px;">Event Details</div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Event Name <span style="color:#EF4444;">*</span></label>
                    <input wire:model="name" type="text" class="krd-input @error('name') krd-input-error @enderror" placeholder="e.g. Adewale & Folake Wedding" autofocus />
                    @error('name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="krd-input-group">
                        <label class="krd-label-text">Event Type</label>
                        <x-ui.dropdown
                            wire="event_type_id"
                            placeholder="Select type"
                            selected="{{ $event_type_id ? ($eventTypes->firstWhere('id', $event_type_id)?->name ?? 'Select type') : 'Select type' }}"
                        >
                            <div class="krd-dropdown-option" x-on:click="select('Select type', null); $wire.set('event_type_id', null)">— None —</div>
                            @foreach($eventTypes as $type)
                            <div class="krd-dropdown-option {{ $event_type_id == $type->id ? 'selected' : '' }}"
                                x-on:click="select('{{ $type->name }}', {{ $type->id }})">
                                <span style="width:8px;height:8px;border-radius:50%;background:{{ $type->color }};flex-shrink:0;display:inline-block;"></span>
                                {{ $type->name }}
                            </div>
                            @endforeach
                        </x-ui.dropdown>
                    </div>

                    <div class="krd-input-group">
                        <label class="krd-label-text">Status</label>
                        <x-ui.dropdown
                            wire="status_id"
                            placeholder="Select status"
                            selected="{{ $status_id ? ($statuses->firstWhere('id', $status_id)?->name ?? 'Select status') : 'Select status' }}"
                        >
                            @foreach($statuses as $status)
                            <div class="krd-dropdown-option {{ $status_id == $status->id ? 'selected' : '' }}"
                                x-on:click="select('{{ $status->name }}', {{ $status->id }})">
                                <span style="width:8px;height:8px;border-radius:50%;background:{{ $status->color }};flex-shrink:0;display:inline-block;"></span>
                                {{ $status->name }}
                            </div>
                            @endforeach
                        </x-ui.dropdown>
                    </div>
                </div>

                {{-- Date + Time --}}
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px;">
                    <div class="krd-input-group">
                        <label class="krd-label-text">Start Date</label>
                        <input wire:model="date" type="date" class="krd-input @error('date') krd-input-error @enderror" />
                        @error('date') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>
                    <div class="krd-input-group">
                        <label class="krd-label-text">Start Time</label>
                        <input wire:model="start_time" type="time" class="krd-input @error('start_time') krd-input-error @enderror" />
                        @error('start_time') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>
                    <div class="krd-input-group">
                        <label class="krd-label-text">End Date</label>
                        <input wire:model="end_date" type="date" class="krd-input @error('end_date') krd-input-error @enderror" />
                        @error('end_date') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>
                    <div class="krd-input-group">
                        <label class="krd-label-text">End Time</label>
                        <input wire:model="end_time" type="time" class="krd-input @error('end_time') krd-input-error @enderror" />
                        @error('end_time') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Venue + Location --}}
                <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;">
                    <div class="krd-input-group">
                        <label class="krd-label-text">Venue</label>
                        <input wire:model="venue" type="text" class="krd-input @error('venue') krd-input-error @enderror" placeholder="e.g. The Grand Ballroom" />
                        @error('venue') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>
                    <div class="krd-input-group">
                        <label class="krd-label-text">City / State</label>
                        <input wire:model="location" type="text" class="krd-input @error('location') krd-input-error @enderror" placeholder="e.g. Lagos, Nigeria" />
                        @error('location') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Max Guests + Budget --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="krd-input-group">
                        <label class="krd-label-text">Expected Guests</label>
                        <input wire:model="max_guests" type="number" min="1" class="krd-input @error('max_guests') krd-input-error @enderror" placeholder="e.g. 200" />
                        @error('max_guests') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>
                    <div class="krd-input-group">
                        <label class="krd-label-text">Agreed Budget</label>
                        <input wire:model="agreed_budget" type="number" min="0" step="0.01" class="krd-input @error('agreed_budget') krd-input-error @enderror" placeholder="e.g. 5000000" />
                        @error('agreed_budget') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Notes --}}
                <div class="krd-input-group" style="margin-bottom:0;">
                    <label class="krd-label-text">Internal Notes</label>
                    <textarea wire:model="notes" class="krd-input @error('notes') krd-input-error @enderror" rows="3" placeholder="Any internal notes about this event..."></textarea>
                    @error('notes') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Client Info --}}
            <div class="krd-card" style="padding:24px;">
                <div class="krd-label" style="margin-bottom:16px;">Client Information</div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Client Name</label>
                    <input wire:model="client_name" type="text" class="krd-input @error('client_name') krd-input-error @enderror" placeholder="e.g. Mrs. Folake Adewale" />
                    @error('client_name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="krd-input-group" style="margin-bottom:0;">
                        <label class="krd-label-text">Client Phone</label>
                        <input wire:model="client_phone" type="tel" class="krd-input @error('client_phone') krd-input-error @enderror" placeholder="e.g. +234 801 234 5678" />
                        @error('client_phone') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>
                    <div class="krd-input-group" style="margin-bottom:0;">
                        <label class="krd-label-text">Client Email</label>
                        <input wire:model="client_email" type="email" class="krd-input @error('client_email') krd-input-error @enderror" placeholder="client@email.com" />
                        @error('client_email') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div style="display:flex;gap:10px;">
                <button wire:click="save" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-lg">
                    <span wire:loading.remove wire:target="save">{{ $event ? 'Update Event' : 'Create Event' }}</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
                <a href="{{ route('tenant.events') }}" wire:navigate class="krd-btn krd-btn-ghost">Cancel</a>
            </div>

        </div>

        {{-- Right — Tips --}}
        <div style="display:flex;flex-direction:column;gap:12px;position:sticky;top:80px;">
            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;">💡 Quick tips</div>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    @foreach([
                        'Include the client name in the event name for easy identification.',
                        'Set start and end times — Nigerian events need a clear timeline.',
                        'Agreed budget is what the client approved. Track actual spend in the Budget tab.',
                        'Use notes for internal information the client should not see.',
                        'You can add tasks, vendors, guests and documents after creating.',
                    ] as $tip)
                    <div style="display:flex;gap:8px;align-items:flex-start;">
                        <div style="width:5px;height:5px;border-radius:50%;background:#7C3AED;flex-shrink:0;margin-top:6px;"></div>
                        <p style="font-size:12px;color:#78716C;line-height:1.6;">{{ $tip }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="krd-card" style="padding:20px;background:#F0FDF4;border-color:#86EFAC;">
                <div style="font-size:13px;font-weight:600;color:#166534;margin-bottom:8px;">📋 After creating</div>
                <div style="display:flex;flex-direction:column;gap:6px;">
                    @foreach([
                        'Add event tasks and assign to staff',
                        'Attach vendors (venue, catering, photography)',
                        'Set up guest list and RSVP',
                        'Build the event runsheet',
                        'Track budget and payments',
                    ] as $item)
                    <div style="display:flex;gap:8px;align-items:center;">
                        <div style="width:5px;height:5px;border-radius:50%;background:#16A34A;flex-shrink:0;"></div>
                        <p style="font-size:12px;color:#166534;">{{ $item }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>