<div x-data="moodboardCanvas({{ $moodboard->id }})" x-init="init()">
    <div style="margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
        <div>
            <a href="{{ route('tenant.events.moodboards', $moodboard->event->slug) }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">← Back to Moodboards</a>
            <h2 class="krd-heading-3" style="color:#1C1917;margin-top:6px;">{{ $moodboard->title }}</h2>
        </div>
        @if($canManage)
        <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">

            <div style="display:flex;gap:2px;background:#F5F5F4;border-radius:8px;padding:3px;">
                <button x-on:click="undo()" title="Undo (Ctrl+Z)" x-bind:disabled="undoStack.length === 0"
                    x-bind:style="undoStack.length === 0 ? 'opacity:0.35;cursor:default;' : 'opacity:1;cursor:pointer;'"
                    style="background:#fff;border:1px solid #E7E5E4;border-radius:6px;padding:6px 10px;font-size:13px;display:flex;align-items:center;gap:4px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 00-15-6.7L3 13"/></svg>
                </button>
                <button x-on:click="redo()" title="Redo (Ctrl+Shift+Z)" x-bind:disabled="redoStack.length === 0"
                    x-bind:style="redoStack.length === 0 ? 'opacity:0.35;cursor:default;' : 'opacity:1;cursor:pointer;'"
                    style="background:#fff;border:1px solid #E7E5E4;border-radius:6px;padding:6px 10px;font-size:13px;display:flex;align-items:center;gap:4px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 7v6h-6"/><path d="M3 17a9 9 0 0115-6.7L21 13"/></svg>
                </button>
            </div>

            <div style="width:1px;height:22px;background:#E7E5E4;"></div>

            <button type="button" onclick="document.getElementById('mb-file-input').click()" title="Upload Image/File" class="krd-btn krd-btn-secondary krd-btn-sm" style="display:inline-flex;align-items:center;gap:6px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Upload
            </button>
            <input type="file" id="mb-file-input" style="display:none;" onchange="uploadToBoard(this.files[0])">
            <button x-on:click="showPexels = true; $nextTick(() => $refs.pexelsQuery.focus())" title="Search stock photos" class="krd-btn krd-btn-secondary krd-btn-sm" style="display:inline-flex;align-items:center;gap:6px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                Stock Photos
            </button>
            <button x-on:click="addNote()" wire:loading.attr="disabled" wire:target="addNote" title="Add a sticky note" class="krd-btn krd-btn-secondary krd-btn-sm" style="display:inline-flex;align-items:center;gap:6px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M14 3v4a1 1 0 001 1h4"/><path d="M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>
                <span wire:loading.remove wire:target="addNote">Note</span>
                <span wire:loading wire:target="addNote">Adding...</span>
            </button>
            <button x-on:click="showAddModal = true" title="Add text, color, or link" class="krd-btn krd-btn-secondary krd-btn-sm">+ Item</button>

            <div x-data="{ open: false, confirmLayout: null }" style="position:relative;">
                <button x-on:click="open = !open" class="krd-btn krd-btn-secondary krd-btn-sm">+ Layout</button>
                <div x-show="open" x-cloak x-on:click.outside="open = false" style="position:absolute;top:32px;left:0;background:#fff;border:1px solid #E7E5E4;border-radius:8px;box-shadow:0 8px 20px rgba(0,0,0,0.1);z-index:50;width:170px;">
                    <div x-on:click="confirmLayout = 'grid_3'; open = false" style="padding:9px 14px;font-size:12.5px;cursor:pointer;">3-box collage</div>
                    <div x-on:click="confirmLayout = 'grid_6'; open = false" style="padding:9px 14px;font-size:12.5px;cursor:pointer;">6-box collage</div>
                    <div x-on:click="confirmLayout = 'grid_7'; open = false" style="padding:9px 14px;font-size:12.5px;cursor:pointer;">7-box collage</div>
                    <div x-on:click="confirmLayout = 'grid_9'; open = false" style="padding:9px 14px;font-size:12.5px;cursor:pointer;">9-box grid</div>
                    <div x-on:click="confirmLayout = 'grid_10'; open = false" style="padding:9px 14px;font-size:12.5px;cursor:pointer;">10-box collage</div>
                    <div x-on:click="confirmLayout = 'grid_12'; open = false" style="padding:9px 14px;font-size:12.5px;cursor:pointer;">12-box grid</div>
                </div>

                <template x-teleport="body">
                <div x-show="confirmLayout" x-cloak style="position:fixed;inset:0;z-index:70;">
                    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
                    <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
                        <div style="background:#fff;border-radius:8px;padding:24px;max-width:380px;width:100%;">
                            <p style="font-size:13px;color:#1C1917;margin-bottom:20px;line-height:1.6;">This adds new empty boxes to the board <strong>alongside</strong> what's already there — it won't remove anything you've already added. Continue?</p>
                            <div style="display:flex;gap:10px;">
                                <button x-on:click="$wire.addPlaceholderLayout(confirmLayout); confirmLayout = null" class="krd-btn krd-btn-primary" style="flex:1;">Add Layout</button>
                                <button x-on:click="confirmLayout = null" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
                </template>
            </div>

            <button x-on:click="$wire.showSectionModal = true" class="krd-btn krd-btn-secondary krd-btn-sm">+ Section</button>

            <div style="width:1px;height:22px;background:#E7E5E4;"></div>

            <div x-data="{ open: false }" style="position:relative;">
                <button x-on:click="open = !open" class="krd-btn krd-btn-secondary krd-btn-sm">Status: {{ $moodboard->statusLabel() }}</button>
                <div x-show="open" x-cloak x-on:click.outside="open = false" style="position:absolute;top:32px;right:0;background:#fff;border:1px solid #E7E5E4;border-radius:8px;box-shadow:0 8px 20px rgba(0,0,0,0.1);z-index:50;width:160px;">
                    @foreach(['draft'=>'Draft','in_review'=>'In Review','approved'=>'Approved','archived'=>'Archived'] as $val => $label)
                    <div wire:click="openStatusChange('{{ $val }}')" x-on:click="open = false" style="padding:10px 14px;font-size:12.5px;cursor:pointer;">{{ $label }}</div>
                    @endforeach
                </div>
            </div>
            <button wire:click="toggleClientVisible" class="krd-btn krd-btn-secondary krd-btn-sm">
                {{ $moodboard->is_client_visible ? '✓ Shared' : 'Share with Client' }}
            </button>
            <button x-on:click="$wire.showHistoryModal = true" class="krd-btn krd-btn-ghost krd-btn-sm" style="position:relative;">
                History
                @php $latestNote = $moodboard->statusHistory->first(); @endphp
                @if($latestNote && $latestNote->note && str_contains($latestNote->note, 'requested changes') && $moodboard->status !== 'approved')
                <span style="position:absolute;top:-4px;right:-4px;width:8px;height:8px;background:#EF4444;border-radius:50%;"></span>
                @endif
            </button>
            <a href="{{ route('tenant.moodboards.export', $moodboard->id) }}" target="_blank" class="krd-btn krd-btn-ghost krd-btn-sm" style="display:inline-flex;align-items:center;gap:6px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export PDF
            </a>
            <button x-on:click="$wire.templateTitle = @js($moodboard->title) + ' Template'; $wire.showSaveTemplateModal = true" class="krd-btn krd-btn-ghost krd-btn-sm">Save as Template</button>

            <div style="width:1px;height:22px;background:#E7E5E4;"></div>

            <div x-data="{ open: false }" style="position:relative;">
                <button x-on:click="open = !open" class="krd-btn krd-btn-secondary krd-btn-sm" x-text="Math.round(zoom * 100) + '%'"></button>
                <div x-show="open" x-cloak x-on:click.outside="open = false" style="position:absolute;top:32px;right:0;background:#fff;border:1px solid #E7E5E4;border-radius:8px;box-shadow:0 8px 20px rgba(0,0,0,0.1);z-index:50;width:220px;padding:14px;">
                    <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:4px;">Zoom</div>
                    <div style="font-size:11px;color:#A8A29E;margin-bottom:12px;">Tip: hold Ctrl and use mouse wheel to zoom</div>
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                        <button type="button" x-on:click="setZoom(zoom - 0.1)" class="krd-btn krd-btn-secondary krd-btn-sm">−</button>
                        <span style="flex:1;text-align:center;font-size:13px;font-weight:600;" x-text="Math.round(zoom * 100) + '%'"></span>
                        <button type="button" x-on:click="setZoom(zoom + 0.1)" class="krd-btn krd-btn-secondary krd-btn-sm">+</button>
                    </div>
                    <button type="button" x-on:click="scaleToFit()" class="krd-btn krd-btn-ghost krd-btn-sm" style="width:100%;">Scale to fit</button>
                </div>
            </div>
        </div>
        @endif
    </div>

        {{-- DESKTOP: free-form canvas --}}
    <div class="mb-desktop-canvas" x-on:dragover.prevent x-on:drop.prevent="handleDrop($event)" style="display:none;position:relative;width:100%;height:calc(100vh - 260px);min-height:600px;margin-top:16px;background:#FAFAF9;border:1px dashed #E7E5E4;border-radius:8px;overflow:auto;">
    <div class="mb-canvas-zoom-layer" style="position:relative;transform-origin:top left;padding-top:20px;min-width:100%;min-height:100%;" x-bind:style="`transform: scale(${zoom}); transform-origin: top left; padding-top:20px; min-width:100%; min-height:100%;`">
        @foreach($moodboard->sections as $section)
        <div class="mb-section" data-section-id="{{ $section->id }}"
            style="position:absolute;left:{{ $section->pos_x }}px;top:{{ $section->pos_y }}px;width:{{ $section->width }}px;height:{{ $section->height }}px;border:2px dashed #DDD6FE;border-radius:8px;background:#F5F3FF33;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div class="mb-section-drag" style="cursor:move;padding:6px 10px;font-size:11px;font-weight:600;color:#7C3AED;flex:1;">{{ $section->title }}</div>
                @if($canManage)
                <button x-on:click="deleteSectionWithUndo({{ $section->id }})" title="Delete section" style="background:none;border:none;cursor:pointer;color:#7C3AED;padding:4px 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
                @endif
            </div>
        </div>
        @endforeach

        @foreach($moodboard->items as $item)
        <div class="mb-item" data-item-id="{{ $item->id }}"
            style="position:absolute;left:{{ $item->pos_x }}px;top:{{ $item->pos_y }}px;width:{{ $item->width }}px;height:{{ $item->height }}px;background:#fff;border:1px solid #E7E5E4;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.06);overflow:hidden;">
            <div class="mb-item-drag" style="cursor:move;padding:4px 6px;display:flex;justify-content:space-between;align-items:center;background:#F5F5F4;">
                <span style="font-size:9px;color:#A8A29E;text-transform:uppercase;">{{ $item->type }}</span>
                @if($canManage)
                <div style="display:flex;gap:4px;">
                    <button wire:click="duplicateItem({{ $item->id }})" wire:loading.attr="disabled" wire:target="duplicateItem({{ $item->id }})" style="background:none;border:none;cursor:pointer;font-size:10px;color:#78716C;">
                        <span wire:loading.remove wire:target="duplicateItem({{ $item->id }})">Duplicate</span>
                        <span wire:loading wire:target="duplicateItem({{ $item->id }})">...</span>
                    </button>
                    <button x-on:click="editingItemPreview = {{ Js::from($item->data ?? []) }}; $wire.startEditItem({{ $item->id }})" style="background:none;border:none;cursor:pointer;font-size:10px;color:#7C3AED;">Edit</button>
                    <button x-on:click="$el.closest('.mb-item').style.opacity = '0.15'; $el.closest('.mb-item').style.pointerEvents = 'none'; deleteItemWithUndo({{ $item->id }})" style="background:none;border:none;cursor:pointer;font-size:10px;color:#DC2626;">✕</button>
                </div>
                @endif
            </div>
            <div
                @if(!in_array($item->type, ['empty']) && $canManage) x-on:dblclick="$wire.startEditItem({{ $item->id }})" @endif
                style="padding:8px;height:calc(100% - 26px);overflow:hidden;{{ (!in_array($item->type, ['empty']) && $canManage) ? ' cursor:pointer;' : '' }}">
                @include('livewire.tenant.moodboards.partials.item-content', ['item' => $item])
            </div>
            @if($canManage)
            <div class="mb-resize-handle" style="position:absolute;bottom:0;right:0;width:14px;height:14px;cursor:nwse-resize;background:linear-gradient(135deg, transparent 50%, #D6D3D1 50%);"></div>
            @endif
        </div>
        @endforeach
    </div>
    </div>

    <div class="mb-mobile-only" style="display:none;">
        <x-ui.dismissible-tip id="moodboard-mobile-desktop-hint" text="This board is best viewed on a larger screen — desktop gives you full drag-and-drop positioning. On mobile you can still add, edit, and reorder items using this simplified list." />
    </div>

    {{-- MOBILE: simplified reorderable list --}}
    <div class="mb-mobile-list" style="display:flex;flex-direction:column;gap:10px;">
        @foreach($moodboard->items as $item)
        <div class="krd-card" style="padding:12px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <span style="font-size:10px;color:#A8A29E;text-transform:uppercase;">{{ $item->type }}{{ $item->section ? ' · ' . $item->section->title : '' }}</span>
                @if($canManage)
                <div style="display:flex;gap:6px;">
                    <button wire:click="moveItemUp({{ $item->id }})" style="background:none;border:none;cursor:pointer;color:#78716C;">↑</button>
                    <button wire:click="moveItemDown({{ $item->id }})" style="background:none;border:none;cursor:pointer;color:#78716C;">↓</button>
                    <button wire:click="duplicateItem({{ $item->id }})" wire:loading.attr="disabled" wire:target="duplicateItem({{ $item->id }})" style="background:none;border:none;cursor:pointer;color:#78716C;font-size:11px;">
                        <span wire:loading.remove wire:target="duplicateItem({{ $item->id }})">Duplicate</span>
                        <span wire:loading wire:target="duplicateItem({{ $item->id }})">...</span>
                    </button>
                    <button wire:click="startEditItem({{ $item->id }})" style="background:none;border:none;cursor:pointer;color:#7C3AED;font-size:11px;">Edit</button>
                    <button wire:click="duplicateItem({{ $item->id }})" style="background:none;border:none;cursor:pointer;font-size:10px;color:#78716C;">Duplicate</button>
                </div>
                @endif
            </div>
            <div @if(!in_array($item->type, ['empty']) && $canManage) x-on:dblclick="$wire.startEditItem({{ $item->id }})" @endif>
                @include('livewire.tenant.moodboards.partials.item-content', ['item' => $item])
            </div>
        </div>
        @endforeach
    </div>

    <style>
        @media (min-width: 900px) {
            .mb-desktop-canvas { display: block !important; }
            .mb-mobile-list { display: none !important; }
            .mb-mobile-only { display: none !important; }
        }
        @media (max-width: 899px) {
            .mb-mobile-only { display: block !important; }
        }
    </style>

    {{-- Add Item Modal --}}
    <template x-teleport="body">
    <div x-show="showAddModal" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:420px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:12px;">Add Item</h3>
                <div x-data="{ localType: 'text', open: false }" x-init="$watch('localType', v => $wire.set('newItemType', v))">
                    <div style="position:relative;margin-bottom:12px;">
                        <button type="button" x-on:click="open = !open" class="krd-dropdown-trigger" style="width:100%;">
                            <span x-text="localType.charAt(0).toUpperCase() + localType.slice(1)"></span>
                            <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div x-show="open" x-cloak x-on:click.outside="open = false" class="krd-dropdown-menu">
                            <div class="krd-dropdown-option" x-on:click="localType = 'text'; open = false">Text</div>
                            <div class="krd-dropdown-option" x-on:click="localType = 'color'; open = false">Color</div>
                            <div class="krd-dropdown-option" x-on:click="localType = 'link'; open = false">Link</div>
                        </div>
                    </div>

                    <div x-show="localType === 'text'">
                        <input wire:model="newItemData.heading" type="text" class="krd-input" placeholder="Heading" style="margin-bottom:8px;">
                        <textarea wire:model="newItemData.content" class="krd-input" rows="3" placeholder="Content"></textarea>
                    </div>
                    <div x-show="localType === 'color'">
                        <input wire:model="newItemData.value" type="color" class="krd-input" style="margin-bottom:8px;height:44px;">
                        <input wire:model="newItemData.name" type="text" class="krd-input" placeholder="Color name (optional)" style="margin-bottom:8px;">
                        <input wire:model="newItemData.note" type="text" class="krd-input" placeholder="Note (optional)">
                    </div>
                    <div x-show="localType === 'link'">
                        <input wire:model="newItemData.url" type="url" class="krd-input" placeholder="https://..." style="margin-bottom:8px;">
                        <input wire:model="newItemData.title" type="text" class="krd-input" placeholder="Title" style="margin-bottom:8px;">
                        <textarea wire:model="newItemData.description" class="krd-input" rows="2" placeholder="Description (optional)"></textarea>
                    </div>

                    <div style="display:flex;gap:10px;margin-top:16px;">
                        <button wire:click="addItem" wire:loading.attr="disabled" wire:target="addItem" x-on:click="showAddModal = false" class="krd-btn krd-btn-primary" style="flex:1;">Add</button>
                        <button x-on:click="showAddModal = false" class="krd-btn krd-btn-ghost" style="flex:1;">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- Edit Item Modal --}}
    <template x-teleport="body">
    <div x-show="$wire.editingItemId" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:420px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:12px;">Edit Item</h3>
                @if(in_array($editingItemType, ['image', 'file']))
                <button type="button" x-on:click="window.MoodboardReplaceUpload.open($wire.editingItemId)" class="krd-btn krd-btn-secondary krd-btn-sm" style="width:100%;margin-bottom:12px;">
                    ↻ Replace {{ $editingItemType === 'image' ? 'Image' : 'File' }}
                </button>
                @endif
                <textarea wire:model="newItemData.caption" class="krd-input" rows="2" placeholder="Caption / text"></textarea>
                <div x-data="{ open: false, label: 'No section' }" style="position:relative;margin-top:8px;">
                    <button type="button" x-on:click="open = !open" class="krd-dropdown-trigger" style="width:100%;">
                        <span x-text="label"></span>
                        <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-cloak x-on:click.outside="open = false" class="krd-dropdown-menu">
                        <div class="krd-dropdown-option" x-on:click="label = 'No section'; open = false; $wire.assignSection($wire.editingItemId, null)">No section</div>
                        @foreach($moodboard->sections as $s)
                        <div class="krd-dropdown-option" x-on:click="label = @js($s->title); open = false; $wire.assignSection($wire.editingItemId, {{ $s->id }})">{{ $s->title }}</div>
                        @endforeach
                    </div>
                </div>
                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button wire:click="saveItemEdit" class="krd-btn krd-btn-primary" style="flex:1;">Save</button>
                    <button wire:click="$set('editingItemId', null)" class="krd-btn krd-btn-ghost" style="flex:1;">Close</button>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- Slot Fill Modal (text/color/link into an empty slot) --}}
    <template x-teleport="body">
    <div x-show="$wire.fillingSlotId && ['text','color','link'].includes($wire.fillingType)" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:420px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:12px;">Add {{ ucfirst($fillingType) }}</h3>

                @if($fillingType === 'text')
                <input wire:model="newItemData.heading" type="text" class="krd-input" placeholder="Heading" style="margin-bottom:8px;">
                <textarea wire:model="newItemData.content" class="krd-input" rows="3" placeholder="Content"></textarea>
                @elseif($fillingType === 'color')
                <input wire:model="newItemData.value" type="color" class="krd-input" style="margin-bottom:8px;height:44px;">
                <input wire:model="newItemData.name" type="text" class="krd-input" placeholder="Color name (optional)" style="margin-bottom:8px;">
                <input wire:model="newItemData.note" type="text" class="krd-input" placeholder="Note (optional)">
                @elseif($fillingType === 'link')
                <input wire:model="newItemData.url" type="url" class="krd-input" placeholder="https://..." style="margin-bottom:8px;">
                <input wire:model="newItemData.title" type="text" class="krd-input" placeholder="Title" style="margin-bottom:8px;">
                <textarea wire:model="newItemData.description" class="krd-input" rows="2" placeholder="Description (optional)"></textarea>
                @endif

                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button wire:click="fillTextColorOrLinkSlot" wire:loading.attr="disabled" wire:target="fillTextColorOrLinkSlot" class="krd-btn krd-btn-primary" style="flex:1;">Add</button>
                    <button wire:click="cancelSlotFill" class="krd-btn krd-btn-ghost" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- History Modal --}}
    <template x-teleport="body">
    <div x-show="$wire.showHistoryModal" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:480px;width:100%;max-height:70vh;display:flex;flex-direction:column;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:12px;">Status History</h3>
                <div style="overflow-y:auto;flex:1;">
                    @forelse($moodboard->statusHistory as $entry)
                    <div style="padding:12px 0;border-bottom:1px solid #F5F5F4;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                            <span class="krd-badge" style="font-size:9px;background:{{ match($entry->status){'in_review'=>'#F59E0B','approved'=>'#10B981','archived'=>'#A8A29E',default=>'#78716C'} }}22;color:{{ match($entry->status){'in_review'=>'#F59E0B','approved'=>'#10B981','archived'=>'#A8A29E',default=>'#78716C'} }};">
                                {{ ucfirst(str_replace('_', ' ', $entry->status)) }}
                            </span>
                            <span style="font-size:11px;color:#A8A29E;">{{ $entry->created_at->diffForHumans() }}</span>
                        </div>
                        @if($entry->note)
                        <p style="font-size:12.5px;color:#1C1917;line-height:1.5;">{{ $entry->note }}</p>
                        @endif
                        <div style="font-size:10.5px;color:#A8A29E;margin-top:4px;">
                            {{ $entry->changedBy?->name ?? 'Client' }}
                        </div>
                    </div>
                    @empty
                    <div style="text-align:center;color:#A8A29E;font-size:12px;padding:24px;">No status changes yet.</div>
                    @endforelse
                </div>
                <button wire:click="$set('showHistoryModal', false)" class="krd-btn krd-btn-ghost" style="width:100%;margin-top:12px;">Close</button>
            </div>
        </div>
    </div>
    </template>

    {{-- Section Modal --}}
    <template x-teleport="body">
    <div x-show="$wire.showSectionModal" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:380px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:12px;">New Section</h3>
                <input wire:model="newSectionTitle" type="text" class="krd-input" placeholder="Section title">
                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button wire:click="createSection" class="krd-btn krd-btn-primary" style="flex:1;">Create</button>
                    <button wire:click="$set('showSectionModal', false)" class="krd-btn krd-btn-ghost" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- Stock Photos (Pexels) Panel --}}
    <template x-teleport="body">
    <div x-show="showPexels" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);" x-on:click="showPexels = false"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:640px;width:100%;max-height:80vh;display:flex;flex-direction:column;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                    <h3 style="font-size:15px;font-weight:600;">Search Stock Photos</h3>
                    <button type="button" x-on:click="showPexels = false" style="background:none;border:none;cursor:pointer;color:#78716C;">✕</button>
                </div>
                <div style="display:flex;gap:8px;margin-bottom:16px;">
                    <input x-ref="pexelsQuery" x-model="pexelsQuery" x-on:keydown.enter="searchPexels()" type="text" class="krd-input" placeholder="Search e.g. 'wedding flowers', 'city lights'..." style="flex:1;">
                    <button type="button" x-on:click="searchPexels()" class="krd-btn krd-btn-primary krd-btn-sm">Search</button>
                </div>
                <div style="overflow-y:auto;flex:1;">
                    <template x-if="pexelsLoading">
                        <div style="text-align:center;padding:32px;color:#A8A29E;font-size:13px;">Searching...</div>
                    </template>
                    <template x-if="!pexelsLoading && pexelsResults.length === 0">
                        <div style="text-align:center;padding:32px;color:#A8A29E;font-size:13px;">Search for photos to add to your board.</div>
                    </template>
                    <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:8px;">
                        <template x-for="photo in pexelsResults" :key="photo.id">
                            <div x-on:click="selectPexelsPhoto(photo); showPexels = false" style="cursor:pointer;border-radius:6px;overflow:hidden;position:relative;">
                                <img :src="photo.thumbnail" :alt="photo.alt" style="width:100%;height:100px;object-fit:cover;display:block;">
                            </div>
                        </template>
                    </div>
                </div>
                <p style="font-size:10px;color:#A8A29E;margin-top:12px;text-align:center;">Photos provided by <a href="https://www.pexels.com" target="_blank" style="color:#7C3AED;">Pexels</a></p>
            </div>
        </div>
    </div>
    </template>

    {{-- Status Change Modal --}}
    <template x-teleport="body">
    <div x-show="$wire.showStatusModal" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:380px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:12px;">Change Status</h3>
                <textarea wire:model="statusNote" class="krd-input" rows="2" placeholder="Note (optional)"></textarea>
                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button wire:click="confirmStatusChange" class="krd-btn krd-btn-primary" style="flex:1;">Confirm</button>
                    <button wire:click="$set('showStatusModal', false)" class="krd-btn krd-btn-ghost" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- Save Template Modal --}}
    <template x-teleport="body">
    <div x-show="$wire.showSaveTemplateModal" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:380px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:12px;">Save as Template</h3>
                <input wire:model="templateTitle" type="text" class="krd-input" placeholder="Template name">
                <p style="font-size:11px;color:#A8A29E;margin-top:8px;">Uploaded images/files are not copied — a board created from this template starts with empty image slots.</p>
                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button wire:click="saveAsTemplate" class="krd-btn krd-btn-primary" style="flex:1;">Save Template</button>
                    <button wire:click="$set('showSaveTemplateModal', false)" class="krd-btn krd-btn-ghost" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>
</div>

<script>
    window.MoodboardSlotUpload = {
        pendingSlotId: null,
        open(slotId) {
            this.pendingSlotId = slotId;
            document.getElementById('mb-file-input').click();
        },
    };

    window.MoodboardReplaceUpload = {
        pendingItemId: null,
        open(itemId) {
            this.pendingItemId = itemId;
            document.getElementById('mb-file-input').click();
        },
    };

    function uploadToBoard(file) {
        if (!file) return;
        const moodboardId = {{ $moodboard->id }};
        const fd = new FormData();
        fd.append('file', file);
        const isImage = file.type.startsWith('image/');

        fetch(`/moodboards/${moodboardId}/upload`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' },
            body: fd,
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) { alert(data.error); return; }

            if (window.MoodboardReplaceUpload.pendingItemId) {
                const itemId = window.MoodboardReplaceUpload.pendingItemId;
                window.MoodboardReplaceUpload.pendingItemId = null;
                window.dispatchEvent(new CustomEvent('moodboard-replace-complete', {
                    detail: { itemId, type: isImage ? 'image' : 'file', documentId: data.document_id, name: data.name },
                }));
                return;
            }

            window.dispatchEvent(new CustomEvent('moodboard-upload-complete', {
                detail: { type: isImage ? 'image' : 'file', documentId: data.document_id, name: data.name },
            }));
        });
    }

    function moodboardCanvas(moodboardId) {
        return {
            showAddModal: false,
            undoStack: [],
            redoStack: [],
            zoom: 1,
            showPexels: false,
            pexelsQuery: '',
            pexelsResults: [],
            pexelsLoading: false,

            setZoom(value) {
                this.zoom = Math.min(2, Math.max(0.25, Math.round(value * 20) / 20));
            },

            handleDrop(e) {
                const files = e.dataTransfer?.files;
                if (!files || files.length === 0) return;
                // Uploads each dropped file in turn — reuses the exact
                // same server upload path as clicking the Upload button,
                // no separate logic.
                Array.from(files).forEach(file => uploadToBoard(file));
            },

            /**
             * Computes the actual zoom level needed to fit every item/
             * section on screen at once, then centers the content within
             * the visible canvas area — matching Milanote's real
             * fit-to-content behavior, not a flat reset to 100%.
             */
            scaleToFit() {
                // document.querySelector instead of this.$el — this method
                // is invoked from a button inside a NESTED x-data scope
                // (the zoom dropdown's own { open: false }), so $el there
                // refers to that inner element, not the outer moodboard
                // canvas component's root. Since there's only ever one
                // moodboard canvas on this page, searching from document
                // is safe and removes the ambiguity entirely.
                const canvas = document.querySelector('.mb-desktop-canvas');
                if (!canvas) return;

                const elements = [
                    ...canvas.querySelectorAll('.mb-item'),
                    ...canvas.querySelectorAll('.mb-section'),
                ];
                console.log('elements found:', elements.length);

                if (elements.length === 0) {
                    this.setZoom(1);
                    return;
                }

                // Bounding box of everything on the board, in the canvas's
                // own UN-scaled coordinate space (reading raw pos_x/pos_y/
                // width/height, not the currently-applied zoom transform).
                let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
                elements.forEach(el => {
                    const x = parseInt(el.style.left) || 0;
                    const y = parseInt(el.style.top) || 0;
                    const w = parseInt(el.style.width) || el.offsetWidth;
                    const h = parseInt(el.style.height) || el.offsetHeight;
                    minX = Math.min(minX, x);
                    minY = Math.min(minY, y);
                    maxX = Math.max(maxX, x + w);
                    maxY = Math.max(maxY, y + h);
                });

                const contentWidth = maxX - minX;
                const contentHeight = maxY - minY;
                const availWidth = canvas.clientWidth;
                const availHeight = canvas.clientHeight;

                // 0.9 leaves a visible margin around the content rather
                // than filling the viewport edge-to-edge.
                const fitZoom = Math.min(availWidth / contentWidth, availHeight / contentHeight) * 0.9;
                this.setZoom(fitZoom);

                this.$nextTick(() => {
                    const contentCenterX = (minX + maxX) / 2 * this.zoom;
                    const contentCenterY = (minY + maxY) / 2 * this.zoom;
                    canvas.scrollLeft = Math.max(0, contentCenterX - availWidth / 2);
                    canvas.scrollTop = Math.max(0, contentCenterY - availHeight / 2);
                });
            },

            searchPexels() {
                if (!this.pexelsQuery.trim()) return;
                this.pexelsLoading = true;
                fetch(`{{ route('tenant.moodboards.pexels-search') }}?q=${encodeURIComponent(this.pexelsQuery)}`)
                    .then(res => res.json())
                    .then(data => {
                        this.pexelsResults = data.results || [];
                        this.pexelsLoading = false;
                    })
                    .catch(() => { this.pexelsLoading = false; });
            },

            selectPexelsPhoto(photo) {
                fetch(`/moodboards/{{ $moodboard->id }}/pexels-select`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({ full_url: photo.full_url, alt: photo.alt }),
                })
                .then(res => res.json())
                .then(data => {
                    if (data.error) { alert(data.error); return; }
                    window.dispatchEvent(new CustomEvent('moodboard-upload-complete', {
                        detail: { type: 'image', documentId: data.document_id, name: data.name },
                    }));
                });
            },

            init() {
                this.$nextTick(() => this.bindDragHandlers());

                const canvas = this.$el.querySelector('.mb-desktop-canvas');
                if (canvas) {
                    canvas.addEventListener('wheel', (e) => {
                        if (!e.ctrlKey) return;
                        e.preventDefault();
                        this.setZoom(this.zoom + (e.deltaY < 0 ? 0.1 : -0.1));
                    }, { passive: false });
                }

                // Paste support — listens on the whole window rather than
                // just the canvas, since a pasted image's clipboard event
                // fires on whatever element currently has focus, which
                // isn't reliably the canvas itself.
                window.addEventListener('paste', (e) => {
                    const items = e.clipboardData?.items;
                    if (!items) return;
                    for (const item of items) {
                        if (item.type.startsWith('image/')) {
                            const file = item.getAsFile();
                            if (file) uploadToBoard(file);
                        }
                    }
                });
                window.addEventListener('moodboard-upload-complete', (e) => {
                    if (window.MoodboardSlotUpload.pendingSlotId) {
                        this.$wire.fillingSlotId = window.MoodboardSlotUpload.pendingSlotId;
                        window.MoodboardSlotUpload.pendingSlotId = null;
                    }
                    this.$wire.attachUploadedDocument(e.detail.type, e.detail.documentId, e.detail.name);
                });

                window.addEventListener('moodboard-replace-complete', (e) => {
                    this.$wire.replaceItemMedia(e.detail.itemId, e.detail.type, e.detail.documentId, e.detail.name);
                });

                window.addEventListener('keydown', (e) => {
                    const ctrlOrCmd = e.ctrlKey || e.metaKey;
                    if (!ctrlOrCmd) return;
                    if (e.key === 'z' && !e.shiftKey) { e.preventDefault(); this.undo(); }
                    if ((e.key === 'z' && e.shiftKey) || e.key === 'y') { e.preventDefault(); this.redo(); }
                });

                // THE ACTUAL FIX for "notes can't be dragged" and
                // "undo/redo/delete stop working after one action": every
                // Livewire re-render (add item, delete, undo, edit save)
                // replaces the DOM nodes inside this component. Drag/resize
                // listeners bound once at page load never transfer to new
                // or replaced nodes — so anything added/changed after the
                // very first render was silently un-draggable. Re-running
                // bindDragHandlers() after every Livewire update fixes this
                // for good, not just for whatever existed on initial load.
                Livewire.hook('morph.updated', () => {
                    this.$nextTick(() => this.bindDragHandlers());
                });
            },

            pushUndo(entry) {
                this.undoStack.push(entry);
                this.redoStack = []; // a new action always clears redo history
            },

            applyEntry(entry, direction) {
                // direction: 'undo' applies the entry's "before" state,
                // 'redo' re-applies its "after" state.
                // Always read this.$wire live here — never a cached
                // reference. Caching it once (as _wireRef) could capture
                // it before Livewire had fully hydrated the component,
                // producing "X is not a function" errors that look like
                // a missing method but are actually a stale/undefined
                // reference.
                const state = direction === 'undo' ? entry.before : entry.after;

                if (entry.type === 'move_item') {
                    const el = this.$el.querySelector(`.mb-item[data-item-id="${entry.id}"]`);
                    if (el) { el.style.left = state.x + 'px'; el.style.top = state.y + 'px'; }
                    this.$wire.updateItemPosition(entry.id, state.x, state.y);
                } else if (entry.type === 'move_section') {
                    const el = this.$el.querySelector(`.mb-section[data-section-id="${entry.id}"]`);
                    if (el) { el.style.left = state.x + 'px'; el.style.top = state.y + 'px'; }
                    this.$wire.updateSectionPosition(entry.id, state.x, state.y);
                } else if (entry.type === 'resize_item') {
                    const el = this.$el.querySelector(`.mb-item[data-item-id="${entry.id}"]`);
                    if (el) { el.style.width = state.width + 'px'; el.style.height = state.height + 'px'; }
                    this.$wire.updateItemSize(entry.id, state.width, state.height);
                } else if (entry.type === 'delete_item') {
                    direction === 'undo' ? this.$wire.restoreItem(entry.id) : this.$wire.deleteItem(entry.id);
                } else if (entry.type === 'delete_section') {
                    direction === 'undo' ? this.$wire.restoreSection(entry.id) : this.$wire.deleteSection(entry.id);
                }
            },

            undo() {
                const entry = this.undoStack.pop();
                if (!entry) return;
                this.applyEntry(entry, 'undo');
                this.redoStack.push(entry);
            },

            redo() {
                const entry = this.redoStack.pop();
                if (!entry) return;
                this.applyEntry(entry, 'redo');
                this.undoStack.push(entry);
            },

            deleteItemWithUndo(itemId) {
                this.pushUndo({ type: 'delete_item', id: itemId, before: null, after: null });
                this.$wire.deleteItem(itemId);
            },

            deleteSectionWithUndo(sectionId) {
                this.pushUndo({ type: 'delete_section', id: sectionId, before: null, after: null });
                this.$wire.deleteSection(sectionId);
            },

            addNote() {
                this.$wire.addNote();
            },

            /**
             * Self-heals any item/section already stuck at a negative
             * position (e.g. from before this clamp existed) — corrects
             * it visually AND persists the fix, so it never silently
             * re-breaks on the next reload.
             */
            clampOutOfBoundsItems(canvas) {
                canvas.querySelectorAll('.mb-item').forEach(el => {
                    const top = parseInt(el.style.top) || 0;
                    const left = parseInt(el.style.left) || 0;
                    if (top < 0 || left < 0) {
                        const fixedTop = Math.max(0, top);
                        const fixedLeft = Math.max(0, left);
                        el.style.top = fixedTop + 'px';
                        el.style.left = fixedLeft + 'px';
                        this.$wire.updateItemPosition(parseInt(el.dataset.itemId), fixedLeft, fixedTop);
                    }
                });
                canvas.querySelectorAll('.mb-section').forEach(el => {
                    const top = parseInt(el.style.top) || 0;
                    const left = parseInt(el.style.left) || 0;
                    if (top < 0 || left < 0) {
                        const fixedTop = Math.max(0, top);
                        const fixedLeft = Math.max(0, left);
                        el.style.top = fixedTop + 'px';
                        el.style.left = fixedLeft + 'px';
                        this.$wire.updateSectionPosition(parseInt(el.dataset.sectionId), fixedLeft, fixedTop);
                    }
                });
            },

            bindDragHandlers() {
                const canvas = this.$el.querySelector('.mb-desktop-canvas');
                if (!canvas) return;

                this.clampOutOfBoundsItems(canvas);

                // data-bound guard prevents attaching duplicate listeners
                // to elements Livewire's morph re-used rather than
                // replaced — without this, an item surviving several
                // re-renders would accumulate 2, 3, 4+ listeners, each
                // firing independently on the same drag.
                canvas.querySelectorAll('.mb-item:not([data-bound])').forEach(el => {
                    el.setAttribute('data-bound', '1');
                    this.makeDraggable(el, 'item');
                });
                canvas.querySelectorAll('.mb-section:not([data-bound])').forEach(el => {
                    el.setAttribute('data-bound', '1');
                    this.makeDraggable(el, 'section');
                });
                canvas.querySelectorAll('.mb-resize-handle:not([data-bound])').forEach(el => {
                    el.setAttribute('data-bound', '1');
                    this.makeResizable(el);
                });
            },

            makeDraggable(el, kind) {
                const handle = el.querySelector(kind === 'item' ? '.mb-item-drag' : '.mb-section-drag');
                if (!handle) return;
                const id = kind === 'item' ? el.dataset.itemId : el.dataset.sectionId;
                const self = this;

                let startX, startY, origLeft, origTop, dragging = false;

                const onDown = (e) => {
                    dragging = true;
                    const point = e.touches ? e.touches[0] : e;
                    startX = point.clientX;
                    startY = point.clientY;
                    origLeft = parseInt(el.style.left);
                    origTop = parseInt(el.style.top);
                    document.addEventListener('mousemove', onMove);
                    document.addEventListener('mouseup', onUp);
                    document.addEventListener('touchmove', onMove);
                    document.addEventListener('touchend', onUp);
                };

                const onMove = (e) => {
                    if (!dragging) return;
                    const point = e.touches ? e.touches[0] : e;
                    // Divided by zoom: at 50% zoom, 100px of real mouse
                    // movement should only move the item 200px in the
                    // canvas's UN-scaled coordinate space (since the whole
                    // layer is visually shrunk by half) — without this
                    // correction, dragging feels wrong at any zoom other
                    // than 100%.
                    const dx = (point.clientX - startX) / self.zoom;
                    const dy = (point.clientY - startY) / self.zoom;
                    el.style.left = Math.max(0, origLeft + dx) + 'px';
                    el.style.top = Math.max(0, origTop + dy) + 'px';
                };

                const onUp = () => {
                    if (!dragging) return;
                    dragging = false;
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup', onUp);
                    document.removeEventListener('touchmove', onMove);
                    document.removeEventListener('touchend', onUp);
                    const x = parseInt(el.style.left);
                    const y = parseInt(el.style.top);
                    const before = { x: origLeft, y: origTop };
                    const after = { x, y };

                    if (before.x === after.x && before.y === after.y) return; // no real movement, nothing to undo

                    self.pushUndo({ type: kind === 'item' ? 'move_item' : 'move_section', id: parseInt(id), before, after });

                    // self.$wire read live, never cached — see note above.
                    if (kind === 'item') {
                        self.$wire.updateItemPosition(parseInt(id), x, y);
                    } else {
                        self.$wire.updateSectionPosition(parseInt(id), x, y);
                    }
                };

                handle.addEventListener('mousedown', onDown);
                handle.addEventListener('touchstart', onDown);
            },

            makeResizable(handle) {
                const el = handle.closest('.mb-item');
                const id = el.dataset.itemId;
                const self = this;
                let startX, startY, origW, origH, resizing = false;

                const onDown = (e) => {
                    resizing = true;
                    const point = e.touches ? e.touches[0] : e;
                    startX = point.clientX;
                    startY = point.clientY;
                    origW = parseInt(el.style.width);
                    origH = parseInt(el.style.height);
                    document.addEventListener('mousemove', onMove);
                    document.addEventListener('mouseup', onUp);
                };

                const onMove = (e) => {
                    if (!resizing) return;
                    const point = e.touches ? e.touches[0] : e;
                    const dx = (point.clientX - startX) / self.zoom;
                    const dy = (point.clientY - startY) / self.zoom;
                    const newW = Math.max(80, origW + dx);
                    const newH = Math.max(80, origH + dy);
                    el.style.width = newW + 'px';
                    el.style.height = newH + 'px';
                };

                const onUp = () => {
                    if (!resizing) return;
                    resizing = false;
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup', onUp);
                    const width = parseInt(el.style.width);
                    const height = parseInt(el.style.height);
                    if (width === origW && height === origH) return;

                    self.pushUndo({ type: 'resize_item', id: parseInt(id), before: { width: origW, height: origH }, after: { width, height } });
                    self.$wire.updateItemSize(parseInt(id), width, height);
                };

                handle.addEventListener('mousedown', onDown);
            },
        };
    }

    document.addEventListener('livewire:navigated', () => {
        // Re-bind drag handlers after any Livewire re-render, since new
        // DOM elements (added/deleted items) need fresh listeners.
    });
</script>