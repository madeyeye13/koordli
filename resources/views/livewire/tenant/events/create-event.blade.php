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

    <div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;">

        {{-- Left — Form --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            <div class="krd-card" style="padding:24px;">
                <div class="krd-label" style="margin-bottom:16px;">Event Details</div>

                {{-- Name --}}
                <div class="krd-input-group">
                    <label class="krd-label-text">Event Name <span style="color:#EF4444;">*</span></label>
                    <input
                        wire:model="name"
                        type="text"
                        class="krd-input @error('name') krd-input-error @enderror"
                        placeholder="e.g. Adewale & Folake Wedding"
                        autofocus
                    />
                    @error('name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                {{-- Type + Status --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    {{-- Event Type --}}
                    <div class="krd-input-group">
                        <label class="krd-label-text">Event Type</label>
                        <div class="krd-dropdown" x-data="{ open: false, selected: '{{ $eventTypes->firstWhere('id', $event_type_id)?->name ?? 'Select type' }}' }">
                            <button type="button" class="krd-dropdown-trigger" x-bind:class="{ open: open }" x-on:click="open = !open" x-on:click.outside="open = false">
                                <span x-text="selected" :style="selected === 'Select type' ? 'color:#A8A29E' : 'color:#1C1917'"></span>
                                <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <div class="krd-dropdown-menu" x-show="open" x-transition x-cloak>
                                <div class="krd-dropdown-option" wire:click="$set('event_type_id', null)" x-on:click="selected = 'Select type'; open = false">
                                    — None —
                                </div>
                                @foreach($eventTypes as $type)
                                <div class="krd-dropdown-option {{ $event_type_id == $type->id ? 'selected' : '' }}"
                                    wire:click="$set('event_type_id', {{ $type->id }})"
                                    x-on:click="selected = '{{ $type->name }}'; open = false">
                                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $type->color }};flex-shrink:0;display:inline-block;"></span>
                                    {{ $type->name }}
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="krd-input-group">
                        <label class="krd-label-text">Status</label>
                        <div class="krd-dropdown" x-data="{ open: false, selected: '{{ $statuses->firstWhere('id', $status_id)?->name ?? 'Select status' }}' }">
                            <button type="button" class="krd-dropdown-trigger" x-bind:class="{ open: open }" x-on:click="open = !open" x-on:click.outside="open = false">
                                <span x-text="selected" :style="selected === 'Select status' ? 'color:#A8A29E' : 'color:#1C1917'"></span>
                                <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <div class="krd-dropdown-menu" x-show="open" x-transition x-cloak>
                                @foreach($statuses as $status)
                                <div class="krd-dropdown-option {{ $status_id == $status->id ? 'selected' : '' }}"
                                    wire:click="$set('status_id', {{ $status->id }})"
                                    x-on:click="selected = '{{ $status->name }}'; open = false">
                                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $status->color }};flex-shrink:0;display:inline-block;"></span>
                                    {{ $status->name }}
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Date + Max Guests --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="krd-input-group">
                        <label class="krd-label-text">Event Date</label>
                        <input
                            wire:model="date"
                            type="date"
                            class="krd-input @error('date') krd-input-error @enderror"
                        />
                        @error('date') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>

                    <div class="krd-input-group">
                        <label class="krd-label-text">Max Guests</label>
                        <input
                            wire:model="max_guests"
                            type="number"
                            min="1"
                            class="krd-input @error('max_guests') krd-input-error @enderror"
                            placeholder="e.g. 200"
                        />
                        @error('max_guests') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Venue --}}
                <div class="krd-input-group" style="margin-bottom:0;">
                    <label class="krd-label-text">Venue</label>
                    <input
                        wire:model="venue"
                        type="text"
                        class="krd-input @error('venue') krd-input-error @enderror"
                        placeholder="e.g. The Grand Ballroom, Lagos"
                    />
                    @error('venue') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Submit --}}
            <div style="display:flex;gap:10px;">
                <button
                    wire:click="save"
                    wire:loading.attr="disabled"
                    class="krd-btn krd-btn-primary krd-btn-lg"
                >
                    <span wire:loading.remove wire:target="save">
                        {{ $event ? 'Update Event' : 'Create Event' }}
                    </span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
                <a href="{{ route('tenant.events') }}" wire:navigate class="krd-btn krd-btn-ghost">
                    Cancel
                </a>
            </div>

        </div>

        {{-- Right — Tips --}}
        <div style="display:flex;flex-direction:column;gap:12px;position:sticky;top:80px;">
            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;">
                    💡 Quick tips
                </div>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    @foreach([
                        'Give your event a clear name that includes the client name or occasion.',
                        'Set the status to track where this event is in your workflow.',
                        'You can add tasks, vendors, guests and a budget after creating the event.',
                        'Max guests helps with RSVP and seating management.',
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