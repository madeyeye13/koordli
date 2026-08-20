<div x-data="{
    activeStatusId: {{ $event->status_id ?? 'null' }},
    activeStatusName: '{{ $event->status?->name ?? '' }}',
    activeStatusColor: '{{ $event->status?->color ?? '#A8A29E' }}'
}">
    {{-- Header --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
        <div>
            <div style="margin-bottom:8px;">
                <a href="{{ route('tenant.events') }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">
                    ← Back to Events
                </a>
            </div>
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <h2 class="krd-heading-3" style="color:#1C1917;">{{ $event->name }}</h2>
                @if($event->status)
                <span class="krd-badge krd-badge-dynamic"
                    x-bind:style="`background: ${activeStatusColor}22; color: ${activeStatusColor};`"
                    x-text="activeStatusName"></span>
                @endif
            </div>
            @if($event->eventType)
            <div style="font-size:12px;color:#78716C;margin-top:4px;display:flex;align-items:center;gap:6px;">
                <span style="width:8px;height:8px;border-radius:50%;background:{{ $event->eventType->color ?? '#A8A29E' }};"></span>
                {{ $event->eventType->name }}
            </div>
            @endif
        </div>

        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('tenant.events.guests', $event->slug) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">
                👥 Guests
            </a>

            <a href="{{ route('tenant.events.runsheet', $event->slug) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">
                📋 Runsheet
            </a>

            @if($event->rsvp_enabled)
            <a href="{{ route('tenant.events.rsvp', $event->slug) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">
                📋 RSVP
            </a>
            @endif


            <a href="{{ route('tenant.events.budget', $event->slug) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">
                💰 Budget
            </a>
            @if($event->client_email)
            <button wire:click="inviteClient" wire:loading.attr="disabled" wire:target="inviteClient" class="krd-btn krd-btn-secondary krd-btn-sm">
                <span wire:loading.remove wire:target="inviteClient">✉️ Invite Client</span>
                <span wire:loading wire:target="inviteClient">Sending...</span>
            </button>
            @endif
            <a href="{{ route('tenant.events.edit', $event->slug) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">
                Edit Event
            </a>
        </div>
    </div>

    {{-- KPI Strip --}}
    <div class="krd-grid-4" style="margin-bottom:24px;">
        <div class="krd-card" style="text-align:center;padding:16px;">
            <div class="krd-label" style="margin-bottom:6px;">Date</div>
            <div style="font-size:18px;font-weight:700;color:#1C1917;">
                {{ $event->date ? $event->date->format('M d') : '—' }}
            </div>
            @if($event->date)
            <div style="font-size:11px;color:#A8A29E;margin-top:2px;">{{ $event->date->format('Y') }}</div>
            @endif
        </div>
        <div class="krd-card" style="text-align:center;padding:16px;">
            <div class="krd-label" style="margin-bottom:6px;">Tasks</div>
            <div style="font-size:28px;font-weight:700;color:#3B82F6;line-height:1;">{{ $event->tasks->count() }}</div>
        </div>
        <div class="krd-card" style="text-align:center;padding:16px;">
            <div class="krd-label" style="margin-bottom:6px;">Vendors</div>
            <div style="font-size:28px;font-weight:700;color:#F59E0B;line-height:1;">{{ $event->vendorAssignments->count() }}</div>
        </div>
        <div class="krd-card" style="text-align:center;padding:16px;">
            <div class="krd-label" style="margin-bottom:6px;">RSVPs</div>
            <div style="font-size:28px;font-weight:700;color:#10B981;line-height:1;">{{ $event->rsvpResponses->count() }}</div>
            @if($event->max_guests)
            <div style="font-size:11px;color:#A8A29E;margin-top:2px;">of {{ number_format($event->max_guests) }} expected</div>
            @endif
        </div>
    </div>

    {{-- Info + Status --}}
    <div class="krd-grid-2" style="margin-bottom:16px;">

        {{-- Event Info --}}
        <div class="krd-card">
            <div class="krd-label" style="margin-bottom:16px;">Event Info</div>

            @foreach([
                ['label' => 'Event Name',      'value' => $event->name],
                ['label' => 'Type',            'value' => $event->eventType?->name ?? '—'],
                ['label' => 'Start Date',      'value' => $event->date?->format('l, F j, Y') ?? '—'],
                ['label' => 'Start Time',      'value' => $event->start_time ? date('g:i A', strtotime($event->start_time)) : '—'],
                ['label' => 'End Date',        'value' => $event->end_date?->format('l, F j, Y') ?? '—'],
                ['label' => 'End Time',        'value' => $event->end_time ? date('g:i A', strtotime($event->end_time)) : '—'],
                ['label' => 'Venue',           'value' => $event->venue ?? '—'],
                ['label' => 'City / State',    'value' => $event->location ?? '—'],
                ['label' => 'Expected Guests', 'value' => $event->max_guests ? number_format($event->max_guests) : '—'],
                ['label' => 'Agreed Budget',   'value' => $event->agreed_budget ? $symbol . number_format($event->agreed_budget, 2) : '—'],
            ] as $info)
            <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #E7E5E4;">
                <span style="font-size:12px;color:#78716C;">{{ $info['label'] }}</span>
                <span style="font-size:12px;font-weight:500;color:#1C1917;text-align:right;max-width:60%;">{{ $info['value'] }}</span>
            </div>
            @endforeach

            @if($event->client_name)
            <div style="margin-top:16px;">
                <div class="krd-label" style="margin-bottom:12px;">Client</div>
                @foreach([
                    ['label' => 'Name',  'value' => $event->client_name],
                    ['label' => 'Phone', 'value' => $event->client_phone ?? '—'],
                    ['label' => 'Email', 'value' => $event->client_email ?? '—'],
                ] as $info)
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #E7E5E4;">
                    <span style="font-size:12px;color:#78716C;">{{ $info['label'] }}</span>
                    <span style="font-size:12px;font-weight:500;color:#1C1917;">{{ $info['value'] }}</span>
                </div>
                @endforeach
            </div>
            @endif

            @if($event->notes)
            <div style="margin-top:16px;">
                <div class="krd-label" style="margin-bottom:8px;">Internal Notes</div>
                <p style="font-size:12px;color:#78716C;line-height:1.7;">{{ $event->notes }}</p>
            </div>
            @endif
        </div>

        {{-- Quick Status Change --}}
        <div class="krd-card">
            <div class="krd-label" style="margin-bottom:16px;">Update Status</div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                @foreach($statuses as $status)
                <button
                    type="button"
                    x-on:click="
                        activeStatusId = {{ $status->id }};
                        activeStatusName = '{{ $status->name }}';
                        activeStatusColor = '{{ $status->color }}';
                        $wire.updateStatus({{ $status->id }});
                    "
                    :style="activeStatusId === {{ $status->id }}
                        ? 'display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:6px;cursor:pointer;border:2px solid {{ $status->color }};background:{{ $status->color }}15;text-align:left;width:100%;transition:all 150ms ease;'
                        : 'display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:6px;cursor:pointer;border:2px solid #E7E5E4;background:#fff;text-align:left;width:100%;transition:all 150ms ease;'"
                >
                    <span style="width:10px;height:10px;border-radius:50%;background:{{ $status->color }};flex-shrink:0;"></span>
                    <span :style="activeStatusId === {{ $status->id }}
                        ? 'font-size:13px;font-weight:600;color:{{ $status->color }};'
                        : 'font-size:13px;font-weight:400;color:#57534E;'">
                        {{ $status->name }}
                    </span>
                    <svg x-show="activeStatusId === {{ $status->id }}"
                        xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                        viewBox="0 0 24 24" stroke="{{ $status->color }}" stroke-width="2.5"
                        style="margin-left:auto;">
                        <path d="M20 6L9 17l-5-5"/>
                    </svg>
                </button>
                @endforeach
            </div>

            @if($event->agreed_budget)
            <div style="margin-top:20px;padding:14px;background:#F5F3FF;border-radius:6px;border:1px solid #DDD6FE;">
                <div style="font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#7C3AED;margin-bottom:4px;">Agreed Budget</div>
                <div style="font-size:22px;font-weight:700;color:#7C3AED;letter-spacing:-0.02em;">
                    {{ $symbol }}{{ number_format($event->agreed_budget, 2) }}
                </div>
            </div>
            @endif
        </div>

    </div>

    {{-- Row 2: Tasks + Vendors --}}
    <div class="krd-grid-2" style="margin-bottom:16px;">

        {{-- Tasks --}}
        <div class="krd-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <div class="krd-label">Tasks</div>
                <a href="{{ route('tenant.tasks.create', ['eventId' => $event->id]) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">+ Add Task</a>
            </div>
            @if($event->tasks->isEmpty())
            <div class="krd-empty-state" style="padding:24px;">
                <div class="krd-empty-state-icon" style="font-size:24px;">✅</div>
                <div class="krd-empty-state-title">No tasks yet</div>
                <div class="krd-empty-state-desc">Add tasks to track what needs to be done for this event.</div>
            </div>
            @else
            <div style="display:flex;flex-direction:column;gap:2px;">
                @foreach($event->tasks->take(5) as $task)
                <div style="display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #E7E5E4;">
                    <div style="width:14px;height:14px;border-radius:3px;border:1.5px solid {{ $task->isCompleted() ? '#10B981' : '#D6D3D1' }};background:{{ $task->isCompleted() ? '#10B981' : 'transparent' }};flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                        @if($task->isCompleted())
                        <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
                        @endif
                    </div>
                    <span style="font-size:13px;color:#1C1917;flex:1;{{ $task->isCompleted() ? 'text-decoration:line-through;color:#A8A29E;' : '' }}">
                        {{ $task->title }}
                    </span>
                    <span class="krd-badge {{ $task->priority->badgeClass() }}" style="font-size:10px;">
                        {{ $task->priority->label() }}
                    </span>
                </div>
                @endforeach
                @if($event->tasks->count() > 5)
                <div style="padding:10px 0;text-align:center;">
                    <a href="{{ route('tenant.tasks', ['eventFilter' => $event->id]) }}" wire:navigate style="font-size:12px;color:#7C3AED;text-decoration:none;">
                        View all {{ $event->tasks->count() }} tasks →
                    </a>
                </div>
                @endif
            </div>
            @endif
        </div>

        {{-- Vendors --}}
        <div class="krd-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <div class="krd-label">Vendors</div>
                <a href="{{ route('tenant.vendors') }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">Assign Vendor</a>
            </div>
            @if($event->vendorAssignments->isEmpty())
            <div class="krd-empty-state" style="padding:24px;">
                <div class="krd-empty-state-icon" style="font-size:24px;">🏢</div>
                <div class="krd-empty-state-title">No vendors assigned</div>
                <div class="krd-empty-state-desc">Go to the vendor directory to assign vendors to this event.</div>
            </div>
            @else
            <div style="display:flex;flex-direction:column;gap:2px;">
                @foreach($event->vendorAssignments->take(5) as $assignment)
                <div style="display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #E7E5E4;">
                    <div style="width:28px;height:28px;border-radius:6px;background:#EDE9FE;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#7C3AED;flex-shrink:0;">
                        {{ strtoupper(substr($assignment->vendor->name, 0, 1)) }}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:500;color:#1C1917;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $assignment->vendor->name }}
                        </div>
                        <div style="font-size:11px;color:#A8A29E;">
                            {{ $assignment->vendor->category?->name ?? '—' }}
                            @if($assignment->amount_agreed > 0)
                            · {{ $symbol }}{{ number_format($assignment->amount_agreed, 2) }}
                            @endif
                        </div>
                    </div>
                    <span class="krd-badge {{ $assignment->statusBadge() }}" style="font-size:10px;">
                        {{ ucfirst($assignment->status) }}
                    </span>
                </div>
                @endforeach
                @if($event->vendorAssignments->count() > 5)
                <div style="padding:10px 0;text-align:center;">
                    <span style="font-size:12px;color:#A8A29E;">+{{ $event->vendorAssignments->count() - 5 }} more vendors</span>
                </div>
                @endif
            </div>
            @endif
        </div>

    </div>

    {{-- Staff & Conversations --}}
    <div class="krd-grid-2" style="margin-bottom:16px;">

        {{-- Assigned Staff --}}
        <div class="krd-card" x-data="{ open: {{ $showAddStaffForm ? 'true' : 'false' }} }">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <div class="krd-label">Assigned Staff</div>
                <button x-on:click="open = true; $wire.showAddStaff()" class="krd-btn krd-btn-secondary krd-btn-sm">+ Add Staff</button>
            </div>

            <div x-show="open" x-cloak style="background:#F5F3FF;border:1px solid #DDD6FE;border-radius:6px;padding:14px;margin-bottom:14px;">
                <div class="krd-input-group" wire:ignore wire:key="staff-dropdown-{{ $event->team->count() }}"
                    x-data="{
                        ddOpen: false,
                        label: 'Select staff...',
                        pick(val, label) { this.label = label; this.ddOpen = false; $wire.set('add_staff_user_id', val); }
                    }"
                    x-on:click.outside="ddOpen = false"
                    style="position:relative;">
                    <label class="krd-label-text">Staff Member</label>
                    <button type="button"
                        x-on:click="ddOpen = !ddOpen"
                        x-bind:class="ddOpen ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'"
                        style="width:100%;">
                        <span x-text="label"></span>
                        <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="ddOpen" x-cloak class="krd-dropdown-menu">
                        @foreach($eligibleStaff as $s)
                        <div class="krd-dropdown-option {{ $add_staff_user_id == $s->id ? 'selected' : '' }}"
                            x-on:click="pick({{ $s->id }}, @js($s->name))">{{ $s->name }}</div>
                        @endforeach
                    </div>
                    @error('add_staff_user_id') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>
                <div class="krd-input-group" style="margin-bottom:0;">
                    <label class="krd-label-text">Role on this event <span style="color:#A8A29E;font-weight:400;">(optional)</span></label>
                    <input wire:model="add_staff_role" type="text" class="krd-input" placeholder="e.g. Lead Coordinator" />
                </div>
                <div style="display:flex;gap:8px;margin-top:12px;">
                    <button wire:click="addStaffToEvent" class="krd-btn krd-btn-primary krd-btn-sm">Add</button>
                    <button x-on:click="open = false" wire:click="$set('showAddStaffForm', false)" class="krd-btn krd-btn-ghost krd-btn-sm">Cancel</button>
                </div>
            </div>

            @forelse($event->team as $teamMember)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid #F5F5F4;">
                <div>
                    <div style="font-size:13px;font-weight:500;color:#1C1917;">{{ $teamMember->user->name ?? 'Unknown' }}</div>
                    @if($teamMember->role_in_event)<div style="font-size:11px;color:#A8A29E;">{{ $teamMember->role_in_event }}</div>@endif
                </div>
                <button wire:click="removeStaffFromEvent({{ $teamMember->id }})" style="background:none;border:none;color:#EF4444;cursor:pointer;font-size:16px;">×</button>
            </div>
            @empty
            <div class="krd-empty-state" style="padding:20px;">
                <div class="krd-empty-state-desc">No staff assigned to this event yet.</div>
            </div>
            @endforelse
        </div>

        {{-- Conversations --}}
        <div class="krd-card" x-data="{ convOpen: {{ $showCreateConversationForm ? 'true' : 'false' }}, typeVal: '{{ $conversation_type }}' }">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <div class="krd-label">Conversations</div>
                <button x-on:click="convOpen = true; $wire.showCreateConversation()" class="krd-btn krd-btn-secondary krd-btn-sm">+ New</button>
            </div>

            <div x-show="convOpen" x-cloak style="background:#F5F3FF;border:1px solid #DDD6FE;border-radius:6px;padding:14px;margin-bottom:14px;">
                <div class="krd-input-group"
                    x-data="{
                        typeOpen: false,
                        typeLabel: '{{ $conversation_type === 'direct' ? 'Direct Message' : 'Group' }}',
                        pickType(val, label) { this.typeLabel = label; this.typeOpen = false; typeVal = val; $wire.set('conversation_type', val); }
                    }"
                    x-on:click.outside="typeOpen = false"
                    style="position:relative;">
                    <label class="krd-label-text">Type</label>
                    <button type="button"
                        x-on:click="typeOpen = !typeOpen"
                        x-bind:class="typeOpen ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'"
                        style="width:100%;">
                        <span x-text="typeLabel"></span>
                        <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="typeOpen" x-cloak class="krd-dropdown-menu">
                        <div class="krd-dropdown-option {{ $conversation_type === 'group' ? 'selected' : '' }}" x-on:click="pickType('group', 'Group')">Group</div>
                        <div class="krd-dropdown-option {{ $conversation_type === 'direct' ? 'selected' : '' }}" x-on:click="pickType('direct', 'Direct Message')">Direct Message</div>
                    </div>
                </div>

                <div class="krd-input-group" x-show="typeVal === 'group'" x-cloak>
                    <label class="krd-label-text">Name</label>
                    <input wire:model="conversation_name" type="text" class="krd-input" placeholder="e.g. Event Communication" />
                    @error('conversation_name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                <div class="krd-input-group" style="margin-bottom:0;">
                    <label class="krd-label-text">Participants</label>
                    <div style="max-height:180px;overflow-y:auto;display:flex;flex-direction:column;gap:6px;margin-top:6px;">
                        @forelse($eligibleParticipants as $option)
                        <label style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:#1C1917;">
                            <input type="checkbox" wire:model="selected_participants" value="{{ $option['key'] }}" style="accent-color:#7C3AED;" />
                            {{ $option['label'] }}
                        </label>
                        @empty
                        <span style="font-size:12px;color:#A8A29E;">No eligible participants yet — assign staff, invite a client, or assign a vendor first.</span>
                        @endforelse
                    </div>
                </div>
                <div style="display:flex;gap:8px;margin-top:12px;">
                    <button wire:click="createConversation" class="krd-btn krd-btn-primary krd-btn-sm">Create</button>
                    <button x-on:click="convOpen = false" wire:click="$set('showCreateConversationForm', false)" class="krd-btn krd-btn-ghost krd-btn-sm">Cancel</button>
                </div>
            </div>

            @forelse($conversations as $conv)
            <a href="{{ route('tenant.conversations.show', $conv->uuid) }}" wire:navigate style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid #F5F5F4;text-decoration:none;">
                <div>
                    <div style="font-size:13px;font-weight:500;color:#1C1917;">{{ $conv->name ?: ($conv->type === 'direct' ? 'Direct Message' : 'Conversation') }}</div>
                    <div style="font-size:11px;color:#A8A29E;">{{ $conv->messages_count }} message(s)</div>
                </div>
                <span style="color:#A8A29E;">→</span>
            </a>
            @empty
            <div class="krd-empty-state" style="padding:20px;">
                <div class="krd-empty-state-desc">No conversations yet for this event.</div>
            </div>
            @endforelse
        </div>

    </div>
    {{-- Row 3: Guests + Budget --}}
    <div class="krd-grid-2">

        {{-- Guests & RSVP --}}
        <div class="krd-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <div class="krd-label">Guests</div>
                <a href="{{ route('tenant.events.guests', $event->slug) }}" wire:navigate
                    class="krd-btn krd-btn-secondary krd-btn-sm">Manage Guests</a>
            </div>
            @php
                $guestCount = \App\Models\Tenant\Guest::where('event_id', $event->id)->count();
                $confirmedCount = \App\Models\Tenant\Guest::where('event_id', $event->id)->where('rsvp_status', 'confirmed')->count();
            @endphp
            @if($guestCount === 0 && !$event->max_guests)
            <div class="krd-empty-state" style="padding:24px;">
                <div class="krd-empty-state-icon" style="font-size:24px;">👥</div>
                <div class="krd-empty-state-title">No guests yet</div>
                <div class="krd-empty-state-desc">Click Manage Guests to add guests or set an expected count.</div>
            </div>
            @else
            <div style="display:flex;flex-direction:column;gap:2px;">
                @if($event->max_guests)
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #E7E5E4;">
                    <span style="font-size:12px;color:#78716C;">Expected</span>
                    <span style="font-size:12px;font-weight:600;color:#1C1917;">{{ number_format($event->max_guests) }}</span>
                </div>
                @endif
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #E7E5E4;">
                    <span style="font-size:12px;color:#78716C;">Added</span>
                    <span style="font-size:12px;font-weight:600;color:#7C3AED;">{{ $guestCount }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;">
                    <span style="font-size:12px;color:#78716C;">Confirmed</span>
                    <span style="font-size:12px;font-weight:600;color:#10B981;">{{ $confirmedCount }}</span>
                </div>
            </div>
            @endif
        </div>

        {{-- Budget Quick View --}}
        <div class="krd-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <div class="krd-label">Budget</div>
                <a href="{{ route('tenant.events.budget', $event->slug) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">View Budget</a>
            </div>
            @if($event->agreed_budget)
            <div style="display:flex;flex-direction:column;">
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #E7E5E4;">
                    <span style="font-size:12px;color:#78716C;">Agreed Budget</span>
                    <span style="font-size:13px;font-weight:600;color:#7C3AED;">{{ $symbol }}{{ number_format($event->agreed_budget, 2) }}</span>
                </div>
                @if($event->budget)
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #E7E5E4;">
                    <span style="font-size:12px;color:#78716C;">Client Paid</span>
                    <span style="font-size:13px;font-weight:600;color:#10B981;">{{ $symbol }}{{ number_format($event->budget->totalClientPaid(), 2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #E7E5E4;">
                    <span style="font-size:12px;color:#78716C;">Actual Spent</span>
                    <span style="font-size:13px;font-weight:600;color:#F59E0B;">{{ $symbol }}{{ number_format($event->budget->totalActual(), 2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:10px 0;">
                    <span style="font-size:12px;color:#78716C;">Outstanding</span>
                    <span style="font-size:13px;font-weight:600;color:{{ $event->budget->clientOutstanding() > 0 ? '#EF4444' : '#10B981' }};">
                        {{ $symbol }}{{ number_format($event->budget->clientOutstanding(), 2) }}
                    </span>
                </div>
                @else
                <div class="krd-empty-state" style="padding:16px;">
                    <div class="krd-empty-state-desc">No budget items added yet.</div>
                </div>
                @endif
            </div>
            @else
            <div class="krd-empty-state" style="padding:24px;">
                <div class="krd-empty-state-icon" style="font-size:24px;">💰</div>
                <div class="krd-empty-state-title">No budget set</div>
                <div class="krd-empty-state-desc">Edit the event to set an agreed budget.</div>
            </div>
            @endif
        </div>

    </div>

</div>