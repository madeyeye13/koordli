<div x-data="{
        showAddModal: false,
        showTemplateModal: false,
        convertingItemId: null,
        editingItemId: null,
        selectedIds: [],
        toggleSelect(id) {
            const idx = this.selectedIds.indexOf(id);
            if (idx > -1) { this.selectedIds.splice(idx, 1); } else { this.selectedIds.push(id); }
            this.$wire.selectedItemIds = this.selectedIds;
        }
     }">
    <div style="margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
        <div>
            <a href="{{ route('tenant.events.show', $event->slug) }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">← Back to {{ $event->name }}</a>
            <h2 class="krd-heading-3" style="color:#1C1917;margin-top:6px;">Event Checklist</h2>
        </div>
        @if($canManage)
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button wire:click="toggleClientVisible" class="krd-btn krd-btn-secondary krd-btn-sm">
                {{ $checklist->is_client_visible ? '✓ Shared with Client' : 'Share with Client' }}
            </button>
            <button x-on:click="showTemplateModal = true; $wire.applyTemplateId = null" class="krd-btn krd-btn-secondary krd-btn-sm">Apply Template</button>
            <a href="{{ route('tenant.checklists.export', $checklist->id) }}" target="_blank" class="krd-btn krd-btn-ghost krd-btn-sm">Export PDF</a>
            <button x-on:click="showAddModal = true" class="krd-btn krd-btn-primary krd-btn-sm">+ Add Item</button>
        </div>
        @endif
    </div>

    <div class="krd-card" style="padding:16px;margin-bottom:20px;">
        <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
            <span style="font-size:12px;color:#57534E;">Overall Progress</span>
            <span style="font-size:12px;font-weight:600;color:#10B981;">{{ $overall['total'] > 0 ? round(($overall['completed'] / $overall['total']) * 100) : 0 }}%</span>
        </div>
        <div style="height:8px;background:#E7E5E4;border-radius:4px;overflow:hidden;">
            <div style="height:100%;width:{{ $overall['total'] > 0 ? round(($overall['completed'] / $overall['total']) * 100) : 0 }}%;background:#10B981;border-radius:4px;"></div>
        </div>
    </div>

    @if($canManage)
    <div x-show="selectedIds.length > 0" x-cloak style="position:sticky;top:0;z-index:40;background:#1C1917;border-radius:8px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
        <span style="color:#fff;font-size:13px;" x-text="selectedIds.length + ' selected'"></span>
        <div style="display:flex;gap:8px;">
            <button x-on:click="$wire.openBulkConvert()" class="krd-btn krd-btn-sm" style="background:#7C3AED;color:#fff;">Convert Selected to Tasks</button>
            <button x-on:click="selectedIds = []; $wire.selectedItemIds = []" class="krd-btn krd-btn-sm" style="background:#57534E;color:#fff;">Clear</button>
        </div>
    </div>
    @endif

    @foreach($phases as $phase)
    @php $items = $itemsByPhase[$phase->value]; @endphp
    @if($items->isNotEmpty())
    <div style="margin-bottom:24px;">
        <div class="krd-label" style="margin-bottom:10px;">{{ $phase->label() }}</div>
        <div style="display:flex;flex-direction:column;gap:8px;">
            @foreach($items as $item)
            <div class="krd-card" style="padding:14px;display:flex;align-items:flex-start;gap:12px;">
                @if($canManage && !$item->isConverted())
                <div x-on:click="toggleSelect({{ $item->id }})"
                    :style="selectedIds.includes({{ $item->id }}) ? 'width:16px;height:16px;border-radius:4px;border:2px solid #7C3AED;background:#7C3AED;cursor:pointer;flex-shrink:0;margin-top:4px;display:flex;align-items:center;justify-content:center;' : 'width:16px;height:16px;border-radius:4px;border:2px solid #D6D3D1;cursor:pointer;flex-shrink:0;margin-top:4px;'">
                    <svg x-show="selectedIds.includes({{ $item->id }})" xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
                </div>
                @endif
                @if(!$item->isConverted())
                <div x-data="{
                        done: {{ $item->is_completed ? 'true' : 'false' }},
                        toggling: false,
                        toggle() {
                            if (this.toggling) return; // prevents a double-click race while a call is in flight
                            const previous = this.done;
                            this.done = !this.done; // instant, zero-wait visual flip
                            this.toggling = true;
                            $wire.toggleComplete({{ $item->id }})
                                .catch(() => { this.done = previous; }) // real rollback on genuine failure
                                .finally(() => { this.toggling = false; });
                        }
                     }">
                    <button x-on:click="toggle()"
                        :style="done ? 'width:20px;height:20px;border-radius:5px;border:2px solid #10B981;background:#10B981;flex-shrink:0;margin-top:2px;display:flex;align-items:center;justify-content:center;cursor:pointer;' : 'width:20px;height:20px;border-radius:5px;border:2px solid #D6D3D1;background:#fff;flex-shrink:0;margin-top:2px;cursor:pointer;'">
                        <svg x-show="done" xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
                    </button>
                </div>
                @else
                <div style="width:20px;height:20px;border-radius:5px;border:2px solid #10B981;background:#10B981;flex-shrink:0;margin-top:2px;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
                </div>
                @endif
                <div style="flex:1;min-width:0;">
                    <div style="font-size:13.5px;font-weight:500;color:#1C1917;{{ $item->is_completed ? 'text-decoration:line-through;color:#A8A29E;' : '' }}">{{ $item->title }}</div>
                    @if($item->description)<div style="font-size:11.5px;color:#78716C;margin-top:2px;">{{ $item->description }}</div>@endif
                    @if($item->isConverted())
                    <a href="{{ route('tenant.tasks.edit', $item->task_id) }}" wire:navigate style="font-size:10.5px;color:#7C3AED;text-decoration:none;">
                        → Linked Task: {{ $item->task->title }} ({{ $item->task->status->label() }})
                    </a>
                    @endif
                </div>
                @if($canManage)
                <div style="display:flex;gap:14px;flex-shrink:0;align-items:center;">
                    @if(!$item->isConverted())
                    <button x-on:click="convertingItemId = {{ $item->id }}; $wire.convertingItemId = {{ $item->id }}" title="Convert to Task" style="background:none;border:none;color:#7C3AED;cursor:pointer;display:flex;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                    </button>
                    <button x-on:click="editingItemId = {{ $item->id }}; $wire.startEdit({{ $item->id }})" title="Edit" style="background:none;border:none;color:#78716C;cursor:pointer;display:flex;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    @endif
                    <x-ui.confirm-button
                        action="deleteItem({{ $item->id }})"
                        message="Delete this checklist item? This cannot be undone."
                        icon='<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4h6v2"/></svg>'
                        style="background:none;border:none;color:#DC2626;cursor:pointer;padding:2px;display:flex;align-items:center;"
                    />
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endforeach

    @if($overall['total'] === 0)
    <div class="krd-card" style="padding:40px;text-align:center;color:#A8A29E;">No checklist items yet. Add one manually or apply a template.</div>
    @endif

    {{-- Add Item Modal --}}
    <template x-teleport="body">
    <div x-show="showAddModal" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:420px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:12px;">Add Checklist Item</h3>
                <input wire:model="newTitle" type="text" class="krd-input" placeholder="Title" style="margin-bottom:8px;">
                <textarea wire:model="newDescription" class="krd-input" rows="2" placeholder="Description (optional)" style="margin-bottom:8px;"></textarea>
                <div x-data="{ open: false, labels: {{ Js::from(collect($phases)->mapWithKeys(fn($p) => [$p->value => $p->label()])) }} }" style="position:relative;">
                    <button type="button" x-on:click="open = !open" class="krd-dropdown-trigger" style="width:100%;">
                        <span x-text="labels[$wire.newPhase]"></span>
                        <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-cloak x-on:click.outside="open = false" class="krd-dropdown-menu">
                        @foreach($phases as $phase)
                        <div class="krd-dropdown-option" x-on:click="$wire.set('newPhase', '{{ $phase->value }}'); open = false">{{ $phase->label() }}</div>
                        @endforeach
                    </div>
                </div>
                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button wire:click="addItem" x-on:click="showAddModal = false" class="krd-btn krd-btn-primary" style="flex:1;">Add</button>
                    <button type="button" x-on:click="showAddModal = false" class="krd-btn krd-btn-ghost" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- Edit Item Modal --}}
    <template x-teleport="body">
    <div x-show="editingItemId" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:420px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:12px;">Edit Item</h3>
                <input wire:model="editTitle" type="text" class="krd-input" style="margin-bottom:8px;">
                <textarea wire:model="editDescription" class="krd-input" rows="2" style="margin-bottom:8px;"></textarea>
                <div x-data="{ open: false, labels: {{ Js::from(collect($phases)->mapWithKeys(fn($p) => [$p->value => $p->label()])) }} }" style="position:relative;">
                    <button type="button" x-on:click="open = !open" class="krd-dropdown-trigger" style="width:100%;">
                        <span x-text="labels[$wire.editPhase]"></span>
                        <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-cloak x-on:click.outside="open = false" class="krd-dropdown-menu">
                        @foreach($phases as $phase)
                        <div class="krd-dropdown-option" x-on:click="$wire.set('editPhase', '{{ $phase->value }}'); open = false">{{ $phase->label() }}</div>
                        @endforeach
                    </div>
                </div>
                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button wire:click="saveEdit" x-on:click="editingItemId = null" class="krd-btn krd-btn-primary" style="flex:1;">Save</button>
                    <button type="button" x-on:click="editingItemId = null" class="krd-btn krd-btn-ghost" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- Convert to Task Modal (single item) --}}
    <template x-teleport="body">
    <div x-show="convertingItemId" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:12px;">Convert to Task</h3>
                <label class="krd-label-text">Due Date</label>
                <input wire:model="convertDueDate" type="date" class="krd-input" style="margin-bottom:10px;">
                <div x-data="{ open: false, label: 'Unassigned' }" style="position:relative;">
                    <button type="button" x-on:click="open = !open" class="krd-dropdown-trigger" style="width:100%;">
                        <span x-text="label"></span>
                        <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-cloak x-on:click.outside="open = false" class="krd-dropdown-menu">
                        <div class="krd-dropdown-option" x-on:click="label = 'Unassigned'; open = false; $wire.set('convertAssignee', null)">Unassigned</div>
                        @foreach($eligibleStaff as $staff)
                        <div class="krd-dropdown-option" x-on:click="label = @js($staff->name); open = false; $wire.set('convertAssignee', {{ $staff->id }})">{{ $staff->name }}</div>
                        @endforeach
                    </div>
                </div>
                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button wire:click="confirmConvert" x-on:click="convertingItemId = null" class="krd-btn krd-btn-primary" style="flex:1;">Convert</button>
                    <button type="button" x-on:click="convertingItemId = null" class="krd-btn krd-btn-ghost" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- Bulk Convert to Task Modal --}}
    <template x-teleport="body">
    <div x-show="$wire.bulkConverting" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:4px;">Convert Selected to Tasks</h3>
                <p style="font-size:12px;color:#A8A29E;margin-bottom:12px;" x-text="selectedIds.length + ' item(s) will become tasks'"></p>
                <label class="krd-label-text">Due Date (applies to all)</label>
                <input wire:model="convertDueDate" type="date" class="krd-input" style="margin-bottom:10px;">
                <div x-data="{ open: false, label: 'Unassigned' }" style="position:relative;">
                    <button type="button" x-on:click="open = !open" class="krd-dropdown-trigger" style="width:100%;">
                        <span x-text="label"></span>
                        <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-cloak x-on:click.outside="open = false" class="krd-dropdown-menu">
                        <div class="krd-dropdown-option" x-on:click="label = 'Unassigned'; open = false; $wire.set('convertAssignee', null)">Unassigned</div>
                        @foreach($eligibleStaff as $staff)
                        <div class="krd-dropdown-option" x-on:click="label = @js($staff->name); open = false; $wire.set('convertAssignee', {{ $staff->id }})">{{ $staff->name }}</div>
                        @endforeach
                    </div>
                </div>
                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button wire:click="confirmConvert" x-on:click="selectedIds = []" class="krd-btn krd-btn-primary" style="flex:1;">Convert All</button>
                    <button wire:click="cancelBulkConvert" class="krd-btn krd-btn-ghost" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- Apply Template Modal --}}
    <template x-teleport="body">
    <div x-show="showTemplateModal" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:420px;width:100%;max-height:70vh;overflow-y:auto;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:12px;">Apply a Template</h3>
                @if($templates->isEmpty())
                <p style="font-size:12px;color:#A8A29E;">No templates yet for this event type.</p>
                @else
                <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:16px;">
                    @foreach($templates as $t)
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;">
                        <input type="radio" wire:model="applyTemplateId" value="{{ $t->id }}" style="accent-color:#7C3AED;">
                        {{ $t->title }}
                    </label>
                    @endforeach
                </div>
                @endif
                <div style="display:flex;gap:10px;">
                    <button wire:click="applyTemplate" x-on:click="showTemplateModal = false" class="krd-btn krd-btn-primary" style="flex:1;">Apply</button>
                    <button type="button" x-on:click="showTemplateModal = false" class="krd-btn krd-btn-ghost" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>
</div>