<div x-data="{ activeTab: '{{ $activeTab }}' }" wire:poll.60s>

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
                <div class="krd-label" style="margin-bottom:4px;">Runsheet</div>
                <h2 class="krd-heading-3" style="color:#1C1917;">{{ $event->name }}</h2>
                @if($runsheet)
                <div style="margin-top:6px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    @php
                        $statusColor = match($runsheet->status) {
                            'active'    => 'krd-badge-green',
                            'completed' => 'krd-badge-violet',
                            default     => 'krd-badge-stone',
                        };
                    @endphp
                    <span class="krd-badge {{ $statusColor }}">{{ ucfirst($runsheet->status) }}</span>
                    @if($runsheet->date)
                    <span style="font-size:12px;color:#A8A29E;">{{ $runsheet->date->format('D, d M Y') }}</span>
                    @endif
                </div>
                @endif
            </div>
            @if($runsheet)
            <button wire:click="showAddItem" class="krd-btn krd-btn-primary">
                + Add Item
            </button>
            @endif
        </div>
    </div>

    {{-- Stats --}}
    @if($runsheet && $runsheet->items->isNotEmpty())
    @php
        $items     = $runsheet->items;
        $total     = $items->count();
        $done      = $items->where('status', \App\Enums\RunsheetItemStatus::Done)->count();
        $inProgress= $items->where('status', \App\Enums\RunsheetItemStatus::InProgress)->count();
        $delayed   = $items->where('status', \App\Enums\RunsheetItemStatus::Delayed)->count();
        $pending   = $items->where('status', \App\Enums\RunsheetItemStatus::Pending)->count();
        $progress  = $total > 0 ? round(($done / $total) * 100) : 0;
    @endphp
    <div class="krd-grid-4" style="margin-bottom:20px;gap:10px;">
        <div class="krd-card" style="padding:14px;border-left:3px solid #7C3AED;">
            <div class="krd-label" style="margin-bottom:4px;">Total Items</div>
            <div style="font-size:24px;font-weight:700;color:#7C3AED;">{{ $total }}</div>
            <div style="margin-top:8px;height:4px;background:#E7E5E4;border-radius:2px;overflow:hidden;">
                <div style="height:100%;width:{{ $progress }}%;background:#7C3AED;border-radius:2px;transition:width 400ms;"></div>
            </div>
            <div style="font-size:10px;color:#A8A29E;margin-top:4px;">{{ $progress }}% complete</div>
        </div>
        <div class="krd-card" style="padding:14px;border-left:3px solid #10B981;">
            <div class="krd-label" style="margin-bottom:4px;">Done</div>
            <div style="font-size:24px;font-weight:700;color:#10B981;">{{ $done }}</div>
        </div>
        <div class="krd-card" style="padding:14px;border-left:3px solid #F59E0B;">
            <div class="krd-label" style="margin-bottom:4px;">In Progress</div>
            <div style="font-size:24px;font-weight:700;color:#F59E0B;">{{ $inProgress }}</div>
        </div>
        <div class="krd-card" style="padding:14px;border-left:3px solid #EF4444;">
            <div class="krd-label" style="margin-bottom:4px;">Delayed</div>
            <div style="font-size:24px;font-weight:700;color:#EF4444;">{{ $delayed }}</div>
        </div>
    </div>
    @endif

    {{-- Main Grid --}}
    <div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;" id="runsheet-main-grid">

        {{-- Left --}}
        <div>
            {{-- Tabs --}}
            <div style="display:flex;gap:4px;border-bottom:1px solid #E7E5E4;margin-bottom:20px;">
                @foreach(['timeline' => 'Timeline', 'settings' => 'Settings'] as $tab => $label)
                <button type="button"
                    x-on:click="activeTab = '{{ $tab }}'; $wire.setTab('{{ $tab }}')"
                    :style="activeTab === '{{ $tab }}'
                        ? 'padding:10px 16px;font-size:13px;font-weight:500;border:none;background:none;cursor:pointer;border-bottom:2px solid #7C3AED;color:#7C3AED;margin-bottom:-1px;'
                        : 'padding:10px 16px;font-size:13px;font-weight:500;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;color:#78716C;margin-bottom:-1px;'"
                >{{ $label }}</button>
                @endforeach
            </div>

            {{-- ══ Timeline Tab ══ --}}
            <div x-show="activeTab === 'timeline'">

                @if(!$runsheet)
                <div class="krd-card">
                    <div class="krd-empty-state">
                        <div class="krd-empty-state-icon">📋</div>
                        <div class="krd-empty-state-title">No runsheet yet</div>
                        <div class="krd-empty-state-desc">Go to Settings tab to create the runsheet for this event.</div>
                    </div>
                </div>

                @else

                {{-- Item Form — always shown when open, regardless of item count --}}
                @if($showItemForm)
                <div class="krd-card" style="padding:20px;margin-bottom:16px;border:2px solid #7C3AED;">
                    <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:16px;">
                        {{ $editItemId ? 'Edit Item' : 'New Item' }}
                    </div>

                    <div class="krd-input-group">
                        <label class="krd-label-text">Title <span style="color:#EF4444;">*</span></label>
                        <input wire:model="item_title" type="text"
                            class="krd-input @error('item_title') krd-input-error @enderror"
                            placeholder="e.g. Venue Setup" autofocus />
                        @error('item_title') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>

                    <div class="krd-input-group">
                        <label class="krd-label-text">Description</label>
                        <input wire:model="item_desc" type="text" class="krd-input"
                            placeholder="Optional details..." />
                    </div>

                    <div class="krd-grid-2" style="gap:12px;">
                        <div class="krd-input-group">
                            <label class="krd-label-text">Start Time</label>
                            <input wire:model="item_start" type="time" class="krd-input" />
                        </div>
                        <div class="krd-input-group">
                            <label class="krd-label-text">End Time</label>
                            <input wire:model="item_end" type="time" class="krd-input" />
                        </div>
                    </div>

                    {{-- Status dropdown --}}
                    <div class="krd-input-group"
                        x-data="{
                            open: false,
                            label: '{{ collect(['pending'=>'Pending','in_progress'=>'In Progress','done'=>'Done','delayed'=>'Delayed'])->get($item_status, 'Pending') }}',
                            pick(val, label) { this.label = label; this.open = false; $wire.set('item_status', val); }
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
                            @foreach(['pending'=>'Pending','in_progress'=>'In Progress','done'=>'Done','delayed'=>'Delayed'] as $val => $label)
                            <div class="krd-dropdown-option {{ $item_status === $val ? 'selected' : '' }}"
                                x-on:click="pick('{{ $val }}', '{{ $label }}')">{{ $label }}</div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Assign to staff --}}
                    <div class="krd-input-group"
                        x-data="{
                            open: false,
                            label: '{{ $item_assigned_to ? ($staff->firstWhere('id', $item_assigned_to)?->name ?? 'Unassigned') : 'Unassigned' }}',
                            pick(val, label) { this.label = label; this.open = false; $wire.set('item_assigned_to', val); }
                        }"
                        x-on:click.outside="open = false"
                        style="position:relative;">
                        <label class="krd-label-text">Assign to Staff</label>
                        <button type="button"
                            x-on:click="open = !open"
                            x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'"
                            style="width:100%;">
                            <span x-text="label"></span>
                            <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div x-show="open" x-cloak class="krd-dropdown-menu">
                            <div class="krd-dropdown-option {{ !$item_assigned_to ? 'selected' : '' }}"
                                x-on:click="pick(null, 'Unassigned')">— Unassigned —</div>
                            @foreach($staff as $member)
                            <div class="krd-dropdown-option {{ $item_assigned_to === $member->id ? 'selected' : '' }}"
                                x-on:click="pick({{ $member->id }}, '{{ $member->name }}')">{{ $member->name }}</div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Assign to vendor --}}
                    <div class="krd-input-group"
                        x-data="{
                            open: false,
                            label: '{{ $item_vendor_id ? ($vendors->firstWhere('id', $item_vendor_id)?->name ?? 'None') : 'None' }}',
                            pick(val, label) { this.label = label; this.open = false; $wire.set('item_vendor_id', val); }
                        }"
                        x-on:click.outside="open = false"
                        style="position:relative;">
                        <label class="krd-label-text">Assign Vendor</label>
                        <button type="button"
                            x-on:click="open = !open"
                            x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'"
                            style="width:100%;">
                            <span x-text="label"></span>
                            <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div x-show="open" x-cloak class="krd-dropdown-menu">
                            <div class="krd-dropdown-option {{ !$item_vendor_id ? 'selected' : '' }}"
                                x-on:click="pick(null, 'None')">— None —</div>
                            @foreach($vendors as $vendor)
                            <div class="krd-dropdown-option {{ $item_vendor_id === $vendor->id ? 'selected' : '' }}"
                                x-on:click="pick({{ $vendor->id }}, '{{ $vendor->name }}')">{{ $vendor->name }}</div>
                            @endforeach
                        </div>
                    </div>

                    <div class="krd-input-group" style="margin-bottom:0;">
                        <label class="krd-label-text">Notes</label>
                        <input wire:model="item_notes" type="text" class="krd-input"
                            placeholder="Any notes for this item..." />
                    </div>

                    <div style="display:flex;gap:10px;margin-top:16px;">
                        <button wire:click="saveItem" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                            <span wire:loading.remove wire:target="saveItem">{{ $editItemId ? 'Update' : 'Add Item' }}</span>
                            <span wire:loading wire:target="saveItem">Saving...</span>
                        </button>
                        <button wire:click="$set('showItemForm', false)" type="button" class="krd-btn krd-btn-ghost">Cancel</button>
                    </div>
                </div>
                @endif

                {{-- Empty state --}}
                @if($runsheet->items->isEmpty() && !$showItemForm)
                <div class="krd-card">
                    <div class="krd-empty-state">
                        <div class="krd-empty-state-icon">⏱️</div>
                        <div class="krd-empty-state-title">No items yet</div>
                        <div class="krd-empty-state-desc">Click "+ Add Item" to start building the event timeline.</div>
                    </div>
                </div>
                @endif

                {{-- Timeline --}}
                @if($runsheet->items->isNotEmpty())
                <div style="display:flex;flex-direction:column;gap:0;position:relative;">
                    @foreach($runsheet->items as $index => $item)
                    @php
                        $itemStatusColor = match($item->status) {
                            \App\Enums\RunsheetItemStatus::Done       => '#10B981',
                            \App\Enums\RunsheetItemStatus::InProgress => '#F59E0B',
                            \App\Enums\RunsheetItemStatus::Delayed    => '#EF4444',
                            default                                    => '#D6D3D1',
                        };
                    @endphp
                    <div style="display:flex;gap:0;position:relative;">
                        {{-- Time column --}}
                        <div style="width:72px;flex-shrink:0;text-align:right;padding-right:16px;padding-top:14px;">
                            @if($item->start_time)
                            <div style="font-size:12px;font-weight:600;color:#57534E;">
                                {{ $item->start_time->format('g:i') }}
                            </div>
                            <div style="font-size:10px;color:#A8A29E;">
                                {{ $item->start_time->format('A') }}
                            </div>
                            @else
                            <div style="font-size:11px;color:#D6D3D1;">—</div>
                            @endif
                        </div>

                        {{-- Timeline line + dot --}}
                        <div style="display:flex;flex-direction:column;align-items:center;flex-shrink:0;width:20px;">
                            <div style="width:12px;height:12px;border-radius:50%;background:{{ $itemStatusColor }};border:2px solid #fff;box-shadow:0 0 0 2px {{ $itemStatusColor }};flex-shrink:0;margin-top:18px;z-index:1;"></div>
                            @if(!$loop->last)
                            <div style="width:2px;flex:1;background:#E7E5E4;min-height:20px;margin-top:4px;"></div>
                            @endif
                        </div>

                        {{-- Item card --}}
                        <div style="flex:1;padding:10px 0 20px 16px;">
                            <div class="krd-card" style="padding:14px;">
                                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                                    <div style="flex:1;min-width:0;">
                                        <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:4px;">
                                            {{ $item->title }}
                                        </div>
                                        @if($item->description)
                                        <div style="font-size:12px;color:#78716C;margin-bottom:6px;">{{ $item->description }}</div>
                                        @endif

                                        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                                            @if($item->end_time)
                                            <span style="font-size:11px;color:#A8A29E;">
                                                Until {{ $item->end_time->format('g:i A') }}
                                            </span>
                                            @endif
                                            @if($item->assignedTo)
                                            <span style="font-size:11px;color:#7C3AED;background:#F5F3FF;padding:2px 6px;border-radius:4px;">
                                                👤 {{ $item->assignedTo->name }}
                                            </span>
                                            @endif
                                            @if($item->vendor)
                                            <span style="font-size:11px;color:#F59E0B;background:#FFFBEB;padding:2px 6px;border-radius:4px;">
                                                🏢 {{ $item->vendor->name }}
                                            </span>
                                            @endif
                                            @if($item->notes)
                                            <span style="font-size:11px;color:#A8A29E;">📝 {{ Str::limit($item->notes, 40) }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;flex-wrap:wrap;">
                                        {{-- Status quick-change --}}
                                        <div x-data="{ open: false }" style="position:relative;">
                                            <button type="button"
                                                x-on:click="open = !open"
                                                x-on:click.outside="open = false"
                                                class="krd-badge"
                                                style="background:{{ $itemStatusColor }}1a;color:{{ $itemStatusColor }};border:none;cursor:pointer;font-size:11px;">
                                                {{ $item->status->label() }} ▾
                                            </button>
                                            <div x-show="open" x-cloak
                                                style="position:absolute;top:calc(100% + 4px);right:0;background:#fff;border:1px solid #E7E5E4;border-radius:6px;z-index:50;min-width:130px;overflow:hidden;">
                                                @foreach(['pending'=>'Pending','in_progress'=>'In Progress','done'=>'Done','delayed'=>'Delayed'] as $val => $label)
                                                <button type="button"
                                                    wire:click="updateItemStatus({{ $item->id }}, '{{ $val }}')"
                                                    x-on:click="open = false"
                                                    style="display:block;width:100%;padding:8px 12px;font-size:12px;text-align:left;border:none;background:{{ $item->status->value === $val ? '#F5F3FF' : '#fff' }};color:{{ $item->status->value === $val ? '#7C3AED' : '#57534E' }};cursor:pointer;">
                                                    {{ $label }}
                                                </button>
                                                @endforeach
                                            </div>
                                        </div>

                                        <button wire:click="moveUp({{ $item->id }})"
                                            class="krd-btn krd-btn-ghost krd-btn-sm" style="padding:4px 6px;font-size:10px;">↑</button>
                                        <button wire:click="moveDown({{ $item->id }})"
                                            class="krd-btn krd-btn-ghost krd-btn-sm" style="padding:4px 6px;font-size:10px;">↓</button>
                                        <button wire:click="editItem({{ $item->id }})"
                                            class="krd-btn krd-btn-secondary krd-btn-sm">Edit</button>
                                        <button wire:click="confirmDelete({{ $item->id }})"
                                            class="krd-btn krd-btn-sm"
                                            style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                @endif
            </div>

            {{-- ══ Settings Tab ══ --}}
            <div x-show="activeTab === 'settings'">
                <div class="krd-card" style="padding:24px;max-width:560px;">
                    <div class="krd-label" style="margin-bottom:16px;">Runsheet Settings</div>

                    <div class="krd-input-group">
                        <label class="krd-label-text">Runsheet Title <span style="color:#EF4444;">*</span></label>
                        <input wire:model="title" type="text"
                            class="krd-input @error('title') krd-input-error @enderror"
                            placeholder="e.g. Chukwuemeka & Adaeze Wedding Day" />
                        @error('title') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>

                    <div class="krd-grid-2" style="gap:12px;">
                        <div class="krd-input-group">
                            <label class="krd-label-text">Event Date</label>
                            <input wire:model="date" type="date" class="krd-input" />
                        </div>

                        {{-- Status dropdown --}}
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
                                @foreach(['draft'=>'Draft','active'=>'Active','completed'=>'Completed'] as $val => $label)
                                <div class="krd-dropdown-option {{ $status === $val ? 'selected' : '' }}"
                                    x-on:click="pick('{{ $val }}', '{{ $label }}')">{{ $label }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="krd-input-group" style="margin-bottom:0;">
                        <label class="krd-label-text">Notes</label>
                        <textarea wire:model="notes" class="krd-input" rows="3"
                            placeholder="Internal notes about this runsheet..."></textarea>
                    </div>

                    <div style="margin-top:16px;">
                        <button wire:click="saveRunsheet" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                            <span wire:loading.remove wire:target="saveRunsheet">{{ $runsheet ? 'Update Runsheet' : 'Create Runsheet' }}</span>
                            <span wire:loading wire:target="saveRunsheet">Saving...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Help Panel --}}
        <div style="display:flex;flex-direction:column;gap:12px;position:sticky;top:80px;" id="runsheet-help-panel">
            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:14px;">📋 How runsheets work</div>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    @foreach([
                        ['step' => '1', 'title' => 'Create the runsheet', 'desc' => 'Go to Settings and set the title, date, and status.'],
                        ['step' => '2', 'title' => 'Add timeline items', 'desc' => 'Add each activity with start/end times. Assign staff or vendors to each item.'],
                        ['step' => '3', 'title' => 'Go live', 'desc' => 'On the event day, update item statuses in real time as things happen.'],
                        ['step' => '4', 'title' => 'Track progress', 'desc' => 'The progress bar shows overall completion at a glance.'],
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
                <div style="font-size:13px;font-weight:600;color:#166534;margin-bottom:10px;">⚡ Status Guide</div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @foreach([
                        ['color' => '#D6D3D1', 'label' => 'Pending', 'desc' => 'Not started yet'],
                        ['color' => '#F59E0B', 'label' => 'In Progress', 'desc' => 'Currently happening'],
                        ['color' => '#10B981', 'label' => 'Done', 'desc' => 'Completed successfully'],
                        ['color' => '#EF4444', 'label' => 'Delayed', 'desc' => 'Behind schedule'],
                    ] as $s)
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="width:10px;height:10px;border-radius:50%;background:{{ $s['color'] }};flex-shrink:0;"></div>
                        <div>
                            <span style="font-size:12px;font-weight:500;color:#1C1917;">{{ $s['label'] }}</span>
                            <span style="font-size:11px;color:#78716C;margin-left:4px;">— {{ $s['desc'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            @if($runsheet && $runsheet->items->isNotEmpty())
            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:10px;">👥 Assigned</div>
                @php
                    $assignedStaff   = $runsheet->items->filter(fn($i) => $i->assigned_to)->pluck('assignedTo.name')->filter()->unique();
                    $assignedVendors = $runsheet->items->filter(fn($i) => $i->vendor_id)->pluck('vendor.name')->filter()->unique();
                @endphp
                @if($assignedStaff->isNotEmpty())
                <div style="margin-bottom:10px;">
                    <div style="font-size:10px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#A8A29E;margin-bottom:6px;">Staff</div>
                    @foreach($assignedStaff as $name)
                    <div style="font-size:12px;color:#7C3AED;padding:3px 0;">👤 {{ $name }}</div>
                    @endforeach
                </div>
                @endif
                @if($assignedVendors->isNotEmpty())
                <div>
                    <div style="font-size:10px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#A8A29E;margin-bottom:6px;">Vendors</div>
                    @foreach($assignedVendors as $name)
                    <div style="font-size:12px;color:#F59E0B;padding:3px 0;">🏢 {{ $name }}</div>
                    @endforeach
                </div>
                @endif
                @if($assignedStaff->isEmpty() && $assignedVendors->isEmpty())
                <div style="font-size:12px;color:#A8A29E;">No assignments yet.</div>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Delete Modal --}}
    @if($showDeleteModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Remove Item?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">
                This will permanently remove this runsheet item.
            </p>
            <div style="display:flex;gap:10px;">
                <button wire:click="deleteItem" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Remove</button>
                <button wire:click="$set('showDeleteModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif

</div>

<style>
@media (max-width: 768px) {
    #runsheet-main-grid  { grid-template-columns: 1fr !important; }
    #runsheet-help-panel { position: static !important; }
}
</style>