<div>
    {{-- Header --}}
    <div style="margin-bottom:28px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('tenant.tasks') }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">
                ← Back to Task Center
            </a>
        </div>
        <div class="krd-label" style="margin-bottom:4px;">Operations</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">
            {{ $task ? 'Edit Task' : 'New Task' }}
        </h2>
    </div>

    <div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;">

        {{-- Left — Form --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            <div class="krd-card" style="padding:24px;">
                <div class="krd-label" style="margin-bottom:16px;">Task Details</div>

                {{-- Title --}}
                <div class="krd-input-group">
                    <label class="krd-label-text">Task Title <span style="color:#EF4444;">*</span></label>
                    <input wire:model="title" type="text" class="krd-input @error('title') krd-input-error @enderror"
                        placeholder="e.g. Confirm venue booking" autofocus />
                    @error('title') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                {{-- Description --}}
                <div class="krd-input-group">
                    <label class="krd-label-text">Description</label>
                    <textarea wire:model="description" class="krd-input @error('description') krd-input-error @enderror"
                        rows="3" placeholder="Any additional details about this task..."></textarea>
                    @error('description') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                {{-- Priority + Status --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="krd-input-group">
                        <label class="krd-label-text">Priority</label>
                        <x-ui.dropdown
                            wire="priority"
                            placeholder="Select priority"
                            selected="{{ collect($priorities)->firstWhere('value', $priority)?->label() ?? 'Normal' }}"
                        >
                            @foreach($priorities as $p)
                            <div class="krd-dropdown-option {{ $priority === $p->value ? 'selected' : '' }}"
                                x-on:click="select('{{ $p->label() }}', '{{ $p->value }}')">
                                <span style="width:8px;height:8px;border-radius:50%;background:{{ $p->color() }};flex-shrink:0;display:inline-block;"></span>
                                {{ $p->label() }}
                            </div>
                            @endforeach
                        </x-ui.dropdown>
                    </div>

                    <div class="krd-input-group">
                        <label class="krd-label-text">Status</label>
                        <x-ui.dropdown
                            wire="status"
                            placeholder="Select status"
                            selected="{{ collect($statuses)->firstWhere('value', $status)?->label() ?? 'To Do' }}"
                        >
                            @foreach($statuses as $s)
                            <div class="krd-dropdown-option {{ $status === $s->value ? 'selected' : '' }}"
                                x-on:click="select('{{ $s->label() }}', '{{ $s->value }}')">
                                <span style="width:8px;height:8px;border-radius:50%;background:{{ $s->color() }};flex-shrink:0;display:inline-block;"></span>
                                {{ $s->label() }}
                            </div>
                            @endforeach
                        </x-ui.dropdown>
                    </div>
                </div>

                {{-- Due Date --}}
                <div class="krd-input-group">
                    <label class="krd-label-text">Due Date</label>
                    <input wire:model="due_date" type="date" class="krd-input @error('due_date') krd-input-error @enderror" />
                    @error('due_date') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                {{-- Event --}}
                <div class="krd-input-group">
                    <label class="krd-label-text">
                        Linked Event
                        <span style="color:#A8A29E;font-weight:400;"> — leave blank for company task</span>
                    </label>
                    <x-ui.dropdown
                        wire="event_id"
                        placeholder="No event (company task)"
                        selected="{{ $event_id ? ($events->firstWhere('id', $event_id)?->name ?? 'No event') : 'No event (company task)' }}"
                    >
                        <div class="krd-dropdown-option" x-on:click="select('No event (company task)', null); $wire.set('event_id', null)">
                            — No event (company task) —
                        </div>
                        @foreach($events as $event)
                        <div class="krd-dropdown-option {{ $event_id == $event->id ? 'selected' : '' }}"
                            x-on:click="select('{{ $event->name }}', {{ $event->id }})">
                            {{ $event->name }}
                        </div>
                        @endforeach
                    </x-ui.dropdown>
                </div>

                {{-- Category + Assigned To --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="krd-input-group" style="margin-bottom:0;">
                        <label class="krd-label-text">Category</label>
                        <x-ui.dropdown
                            wire="task_category_id"
                            placeholder="Select category"
                            selected="{{ $task_category_id ? ($categories->firstWhere('id', $task_category_id)?->name ?? 'Select category') : 'Select category' }}"
                        >
                            <div class="krd-dropdown-option" x-on:click="select('Select category', null); $wire.set('task_category_id', null)">
                                — None —
                            </div>
                            @foreach($categories as $category)
                            <div class="krd-dropdown-option {{ $task_category_id == $category->id ? 'selected' : '' }}"
                                x-on:click="select('{{ $category->name }}', {{ $category->id }})">
                                {{ $category->name }}
                            </div>
                            @endforeach
                        </x-ui.dropdown>
                    </div>

                    <div class="krd-input-group" style="margin-bottom:0;">
                        <label class="krd-label-text">Assign To</label>
                        <x-ui.dropdown
                            wire="assigned_to"
                            placeholder="Unassigned"
                            selected="{{ $assigned_to ? ($users->firstWhere('id', $assigned_to)?->name ?? 'Unassigned') : 'Unassigned' }}"
                        >
                            <div class="krd-dropdown-option" x-on:click="select('Unassigned', null); $wire.set('assigned_to', null)">
                                — Unassigned —
                            </div>
                            @foreach($users as $user)
                            <div class="krd-dropdown-option {{ $assigned_to == $user->id ? 'selected' : '' }}"
                                x-on:click="select('{{ $user->name }}', {{ $user->id }})">
                                {{ $user->name }}
                            </div>
                            @endforeach
                        </x-ui.dropdown>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div style="display:flex;gap:10px;">
                <button wire:click="save" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-lg">
                    <span wire:loading.remove wire:target="save">{{ $task ? 'Update Task' : 'Create Task' }}</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
                <a href="{{ route('tenant.tasks') }}" wire:navigate class="krd-btn krd-btn-ghost">Cancel</a>
            </div>

        </div>

        {{-- Right — Tips --}}
        <div style="display:flex;flex-direction:column;gap:12px;position:sticky;top:80px;">
            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;">💡 Task types</div>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div style="padding:12px;background:#F5F3FF;border-radius:6px;border-left:3px solid #7C3AED;">
                        <div style="font-size:12px;font-weight:600;color:#7C3AED;margin-bottom:4px;">Event Task</div>
                        <div style="font-size:11px;color:#78716C;line-height:1.6;">Link to an event. Examples: confirm venue, send invites, brief photographer.</div>
                    </div>
                    <div style="padding:12px;background:#FEF3C7;border-radius:6px;border-left:3px solid #F59E0B;">
                        <div style="font-size:12px;font-weight:600;color:#D97706;margin-bottom:4px;">Company Task</div>
                        <div style="font-size:11px;color:#78716C;line-height:1.6;">No event linked. Examples: renew insurance, update pricing, staff review.</div>
                    </div>
                </div>
            </div>

            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;">Priority Guide</div>
                @foreach($priorities as $p)
                <div style="display:flex;align-items:center;gap:8px;padding:5px 0;">
                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $p->color() }};flex-shrink:0;"></span>
                    <span style="font-size:12px;color:#57534E;font-weight:500;">{{ $p->label() }}</span>
                    <span style="font-size:11px;color:#A8A29E;">
                        @if($p->value === 'urgent') Needs immediate attention
                        @elseif($p->value === 'high') Important, do soon
                        @elseif($p->value === 'normal') Standard priority
                        @else Can wait
                        @endif
                    </span>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>