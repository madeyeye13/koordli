<div x-data="{ showModal: false }">
    <div style="margin-bottom:24px;">
        <a href="{{ route('tenant.events.show', $event->slug) }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">← Back to {{ $event->name }}</a>
        <h2 class="krd-heading-3" style="color:#1C1917;margin-top:8px;">Moodboards</h2>
    </div>

    @if($canManage)
    <div style="margin-bottom:16px;">
        @if($limitReached)
        <x-ui.upgrade-prompt feature="more moodboards" message="You've used all {{ $moodboardLimit }} moodboards included in your plan." />
        @else
        <button x-on:click="showModal = true" class="krd-btn krd-btn-primary krd-btn-sm">+ New Moodboard</button>
        @if($moodboardLimit > 0)
        <span style="font-size:11px;color:#A8A29E;margin-left:8px;">{{ $moodboardCount }} / {{ $moodboardLimit }} used</span>
        @endif
        @endif
    </div>
    @endif

    @if($moodboards->isEmpty())
    <div class="krd-card" style="padding:40px;text-align:center;color:#A8A29E;">No moodboards yet for this event.</div>
    @else
    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(240px, 1fr));gap:14px;">
        @foreach($moodboards as $board)
        <div class="krd-card" style="padding:16px;position:relative;">
            <a href="{{ route('tenant.moodboards.edit', $board->id) }}" wire:navigate style="text-decoration:none;">
                <div style="width:100%;height:100px;background:#F5F5F4;border-radius:6px;margin-bottom:10px;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                    @if($board->coverDocument)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk($board->coverDocument->disk)->url($board->coverDocument->path) }}" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#D6D3D1" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                    @endif
                </div>
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:4px;">{{ $board->title }}</div>
            </a>
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;gap:4px;">
                    <span class="krd-badge" style="font-size:9px;background:{{ $board->statusColor() }}22;color:{{ $board->statusColor() }};">{{ $board->statusLabel() }}</span>
                    @if($board->is_client_visible)
                    <span class="krd-badge" style="font-size:9px;background:#7C3AED22;color:#7C3AED;">Shared</span>
                    @endif
                </div>
                @if($canManage)
                <div x-data="{ confirmOpen: false }">
                    <button type="button" x-on:click="confirmOpen = true" title="Delete moodboard" style="background:none;border:none;color:#DC2626;cursor:pointer;padding:2px;display:flex;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4h6v2"/>
                        </svg>
                    </button>
                    <template x-teleport="body">
                    <div x-show="confirmOpen" x-cloak style="position:fixed;inset:0;z-index:70;">
                        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
                        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
                            <div style="background:#fff;border-radius:8px;padding:24px;max-width:380px;width:100%;">
                                <p style="font-size:13px;color:#1C1917;margin-bottom:20px;line-height:1.6;">Delete "<strong>{{ $board->title }}</strong>"? This removes every item on it and cannot be undone.</p>
                                <div style="display:flex;gap:10px;">
                                    <button wire:click="delete({{ $board->id }})" x-on:click="confirmOpen = false" class="krd-btn krd-btn-danger" style="flex:1;">Delete</button>
                                    <button type="button" x-on:click="confirmOpen = false" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    </template>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <template x-teleport="body">
    <div x-show="showModal" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:420px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:12px;">New Moodboard</h3>
                <input wire:model="newTitle" type="text" class="krd-input" placeholder="Title (e.g. Overall Vision)" style="margin-bottom:8px;">
                <textarea wire:model="newDescription" class="krd-input" rows="2" placeholder="Description (optional)" style="margin-bottom:8px;"></textarea>

                @if($templates->isNotEmpty())
                <div x-data="{ ddOpen: false, label: 'Start blank' }" style="position:relative;margin-bottom:8px;">
                    <button type="button" x-on:click="ddOpen = !ddOpen" class="krd-dropdown-trigger" style="width:100%;">
                        <span x-text="label"></span>
                        <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="ddOpen" x-cloak x-on:click.outside="ddOpen = false" class="krd-dropdown-menu">
                        <div class="krd-dropdown-option" x-on:click="label = 'Start blank'; ddOpen = false; $wire.set('fromTemplateId', null)">Start blank</div>
                        @foreach($templates as $t)
                        <div class="krd-dropdown-option" x-on:click="label = 'Use template: {{ $t->title }}'; ddOpen = false; $wire.set('fromTemplateId', {{ $t->id }})">Use template: {{ $t->title }}</div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button wire:click="create" wire:loading.attr="disabled" wire:target="create" class="krd-btn krd-btn-primary" style="flex:1;">
                        <span wire:loading.remove wire:target="create">Create</span>
                        <span wire:loading wire:target="create">Creating...</span>
                    </button>
                    <button x-on:click="showModal = false" class="krd-btn krd-btn-ghost" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>
</div>