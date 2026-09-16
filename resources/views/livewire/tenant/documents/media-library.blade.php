<div x-data="mediaLibraryUI({{ $event->id }}, {{ $currentFolderId ?? 'null' }}, {{ $selectMode ? 'true' : 'false' }}, @js($selectedDocumentIds))" x-init="init()" x-on:keydown.window="onKeydown">

    <div style="margin-bottom:24px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('tenant.events.show', $event->slug) }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">
                ← Back to {{ $event->name }}
            </a>
        </div>
        <h2 class="krd-heading-3" style="color:#1C1917;">Media Library</h2>
    </div>

    {{-- Event Logo slot --}}
    <div class="krd-card media-event-logo-card" style="padding:20px;margin-bottom:16px;display:flex;align-items:center;gap:16px;">
        <div style="width:64px;height:64px;border-radius:8px;background:#F5F5F4;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">
            @if($logo)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk($logo->disk)->url($logo->path) }}" style="width:100%;height:100%;object-fit:cover;">
            @else
                <span style="font-size:11px;color:#A8A29E;">No logo</span>
            @endif
        </div>
        <div class="media-event-logo-copy" style="flex:1;min-width:0;">
            <div style="font-size:13px;font-weight:600;color:#1C1917;">Event Logo</div>
            <div style="font-size:11px;color:#78716C;">Optional — one designated image for this event, separate from the file gallery below.</div>
        </div>
        @if($canUpload)
        <div class="media-event-logo-actions" style="display:flex;gap:8px;flex-wrap:wrap;">
            <button type="button" onclick="document.getElementById('krd-logo-input').click()" class="krd-btn krd-btn-secondary krd-btn-sm">
                {{ $logo ? 'Replace' : 'Upload' }}
            </button>
            @if($logo && $canDelete)
            <button wire:click="removeLogo" class="krd-btn krd-btn-ghost krd-btn-sm">Remove</button>
            @endif
        </div>
        <input type="file" id="krd-logo-input" accept="image/*" style="display:none;" onchange="window.KoordliUploader.enqueue(this.files, {{ $event->id }}, null, 'logo')">
        @endif
    </div>

    @if($canUpload)
    <div class="krd-card" style="padding:16px;margin-bottom:16px;background:#FFFBEB;border-color:#FDE68A;">
        <div style="font-size:12px;color:#92400E;">
            💡 Create folders to keep things organized, drag files in directly, or paste an external link (like a Google Drive share) as a saved reference. Up to 20 files per upload, 750MB each.
        </div>
    </div>
    @endif

    {{-- Toolbar --}}
    <div class="media-toolbar" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;align-items:center;">
        @if($currentFolder)
        <button wire:click="openFolder(null)" class="krd-btn krd-btn-ghost krd-btn-sm">← All Files</button>
        <span style="font-size:13px;font-weight:600;color:#1C1917;">{{ $currentFolder->name }}</span>
        @endif

                @if($canDelete && $documents->isNotEmpty())
        <button x-on:click="toggleSelectMode()" class="krd-btn krd-btn-secondary krd-btn-sm">
            <span x-text="selectMode ? 'Cancel Select' : 'Select'"></span>
        </button>
        @endif

        <template x-if="selectMode">
            <div style="display:flex;gap:8px;align-items:center;">
                <span style="font-size:12px;color:#78716C;" x-text="selectedIds.length + ' selected'"></span>
                <button x-on:click="downloadSelected()" class="krd-btn krd-btn-secondary krd-btn-sm" x-bind:disabled="selectedIds.length === 0">
                    Download Selected
                </button>
                <button x-on:click="confirmBulkDelete()" class="krd-btn krd-btn-danger krd-btn-sm" x-bind:disabled="selectedIds.length === 0">
                    Delete Selected
                </button>
            </div>
        </template>
        <template x-if="!selectMode">
        <div>
        @if($canUpload)
            <div class="media-toolbar-actions" style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap;">
            <button type="button" onclick="document.getElementById('krd-file-input').click()" class="krd-btn krd-btn-primary krd-btn-sm">Upload Files</button>
            <input type="file" id="krd-file-input" multiple style="display:none;"
                onchange="window.KoordliUploader.enqueue(this.files, {{ $event->id }}, {{ $currentFolderId ?? 'null' }}, 'file')">
            <button wire:click="showCreateFolder" class="krd-btn krd-btn-secondary krd-btn-sm">New Folder</button>
            <button wire:click="showAddLink" class="krd-btn krd-btn-secondary krd-btn-sm">Add Link</button>
            <button wire:click="openShareLinkManager" class="krd-btn krd-btn-secondary krd-btn-sm">Guest Upload Link</button>
        </div>
        @endif
        </div>
        </template>
    </div>

    {{-- Folders (root level only) --}}
    @if($folders->isNotEmpty())
    <div class="media-folder-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(140px, 1fr));gap:12px;margin-bottom:20px;">
        @foreach($folders as $folder)
        <div class="krd-card" style="padding:14px;cursor:pointer;text-align:center;" wire:click="openFolder({{ $folder->id }})">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#7C3AED" stroke-width="1.5" style="margin-bottom:6px;">
                <path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/>
            </svg>
            <div style="font-size:12px;font-weight:600;color:#1C1917;word-break:break-word;">{{ $folder->name }}</div>
            @if($canDelete)
            <button wire:click.stop="confirmDeleteFolder({{ $folder->id }})" style="margin-top:6px;background:none;border:none;color:#DC2626;font-size:11px;cursor:pointer;">Delete</button>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    {{-- Upload progress widget target — populated entirely by JS, independent of Livewire's lifecycle --}}
    <div id="krd-upload-progress" style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:20px;"></div>

    {{-- Files --}}
    @if($documents->isEmpty())
        <div class="krd-card" style="padding:40px;text-align:center;color:#A8A29E;">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="#D6D3D1" stroke-width="1.5" style="margin:0 auto 8px;display:block;">
                <path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/>
            </svg>
            <div style="font-size:13px;">No files here yet. Upload something, or paste an external link to save it as a reference.</div>
        </div>
    @else
    <div class="media-file-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(160px, 1fr));gap:12px;">
        @php $mediaIndex = 0; @endphp
        @foreach($documents as $doc)
        <div class="krd-card" style="padding:12px;position:relative;"
            x-bind:style="selectMode ? (isSelected({{ $doc->id }}) ? 'padding:12px;position:relative;cursor:pointer;border:2px solid #7C3AED;' : 'padding:12px;position:relative;cursor:pointer;') : 'padding:12px;position:relative;'"
            x-on:click="selectMode && toggleSelected({{ $doc->id }})">

            <template x-if="selectMode">
                <div x-bind:style="isSelected({{ $doc->id }}) ? 'position:absolute;top:8px;left:8px;width:18px;height:18px;border-radius:4px;border:2px solid #7C3AED;background:#7C3AED;z-index:2;display:flex;align-items:center;justify-content:center;' : 'position:absolute;top:8px;left:8px;width:18px;height:18px;border-radius:4px;border:2px solid #D6D3D1;background:#fff;z-index:2;display:flex;align-items:center;justify-content:center;'">
                    <svg x-show="isSelected({{ $doc->id }})" xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
                </div>
            </template>

            @if($doc->type === 'link')
                <a href="{{ $doc->external_url }}" target="_blank" style="display:block;text-decoration:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#3B82F6" stroke-width="1.5" style="display:block;margin:0 auto 6px;">
                        <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/>
                        <path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/>
                    </svg>
                    <div style="font-size:12px;font-weight:600;color:#1C1917;word-break:break-word;">{{ $doc->name }}</div>
                </a>
            @elseif($doc->isImage())
                @php $thumbUrl = $doc->thumbnail_path ? \Illuminate\Support\Facades\Storage::disk($doc->disk)->url($doc->thumbnail_path) : \Illuminate\Support\Facades\Storage::disk($doc->disk)->url($doc->path); @endphp
                <div style="position:relative;">
                    <img src="{{ $thumbUrl }}"
                        style="width:100%;height:100px;object-fit:cover;border-radius:6px;cursor:pointer;margin-bottom:6px;"
                        onclick="window.KoordliUploader.openLightbox({{ $mediaIndex }})">
                    @if($doc->compression_status === 'pending' || $doc->compression_status === 'processing')
                    <span style="position:absolute;top:4px;right:4px;background:#1C1917CC;color:#fff;font-size:9px;padding:2px 6px;border-radius:4px;">Processing…</span>
                    @endif
                </div>
                <div style="font-size:11px;color:#1C1917;word-break:break-word;">{{ $doc->name }}</div>
                @php $mediaIndex++; @endphp
            @elseif($doc->isVideo())
                @php $videoThumbUrl = $doc->thumbnail_path ? \Illuminate\Support\Facades\Storage::disk($doc->disk)->url($doc->thumbnail_path) : null; @endphp
                <div style="position:relative;width:100%;height:100px;background:#1C1917;border-radius:6px;display:flex;align-items:center;justify-content:center;cursor:pointer;margin-bottom:6px;background-size:cover;background-position:center;{{ $videoThumbUrl ? 'background-image:url(' . $videoThumbUrl . ');' : '' }}"
                    onclick="window.KoordliUploader.openLightbox({{ $mediaIndex }})">
                    <span style="color:#fff;font-size:24px;text-shadow:0 1px 4px rgba(0,0,0,0.5);">▶</span>
                    @if($doc->compression_status === 'pending' || $doc->compression_status === 'processing')
                    <span style="position:absolute;top:4px;right:4px;background:#1C1917CC;color:#fff;font-size:9px;padding:2px 6px;border-radius:4px;">Processing…</span>
                    @endif
                </div>
                <div style="font-size:11px;color:#1C1917;word-break:break-word;">{{ $doc->name }}</div>
                @php $mediaIndex++; @endphp
            @else
                <a href="{{ route('media.download', $doc->id) }}" target="_blank" style="display:block;text-decoration:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#78716C" stroke-width="1.5" style="display:block;margin:0 auto 6px;">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                    <div style="font-size:11px;color:#1C1917;word-break:break-word;">{{ $doc->name }}</div>
                </a>
            @endif

            @if($doc->type !== 'link')
            <a x-show="!selectMode" href="{{ route('media.download', $doc->id) }}" target="_blank" title="Download" style="display:inline-flex;align-items:center;margin-top:6px;margin-right:8px;color:#78716C;font-size:11px;text-decoration:none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="margin-right:3px;">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Download
            </a>
            @endif

            @if($canDelete)
            <button x-show="!selectMode" wire:click="confirmDelete({{ $doc->id }})" style="margin-top:6px;background:none;border:none;color:#DC2626;font-size:11px;cursor:pointer;">Delete</button>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    {{-- Lightbox with prev/next/download --}}
    <template x-teleport="body">
        <div x-show="lightbox.open" x-cloak style="position:fixed;inset:0;z-index:70;">
            <div style="position:absolute;inset:0;background:rgba(0,0,0,0.85);" x-on:click="lightbox.open=false"></div>
            <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:24px;">

                <button x-show="mediaItems.length > 1" x-on:click.stop="prev()"
                    style="position:absolute;left:40px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.15);border:none;color:#fff;width:44px;height:44px;border-radius:50%;cursor:pointer;padding:0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:block;margin:0 auto;"><polyline points="15 18 9 12 15 6"/></svg>
                </button>

                <template x-if="current.kind === 'image'">
                    <img :src="current.url" style="max-width:80vw;max-height:80vh;border-radius:8px;">
                </template>
                <template x-if="current.kind === 'video'">
                    <video :src="current.url" controls autoplay style="max-width:80vw;max-height:80vh;border-radius:8px;"></video>
                </template>

                <button x-show="mediaItems.length > 1" x-on:click.stop="next()"
                    style="position:absolute;right:40px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.15);border:none;color:#fff;width:44px;height:44px;border-radius:50%;cursor:pointer;padding:0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:block;margin:0 auto;"><polyline points="9 18 15 12 9 6"/></svg>
                </button>

                <div style="position:absolute;top:20px;right:24px;display:flex;gap:20px;align-items:center;">
                    <a :href="current.downloadUrl" target="_blank" title="Download" style="color:#fff;display:flex;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                    </a>
                    <button x-on:click="lightbox.open=false" style="background:none;border:none;color:#fff;font-size:24px;cursor:pointer;line-height:1;">✕</button>
                </div>

                <div x-show="mediaItems.length > 1" style="position:absolute;bottom:16px;left:50%;transform:translateX(-50%);color:#fff;font-size:12px;">
                    <span x-text="(lightbox.index + 1) + ' / ' + mediaItems.length"></span>
                </div>
            </div>
        </div>
    </template>

    {{-- Share Link Modal --}}
    <template x-teleport="body">
    <div x-show="$wire.showShareLinkModal" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:440px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:4px;">Guest Upload Link</h3>
                <p style="font-size:12px;color:#78716C;margin-bottom:16px;">Anyone with this link can upload files here without logging in.</p>

                @if($activeShareLink && $activeShareLink->isUsable())
                    <div style="background:#F5F3FF;border:1px solid #DDD6FE;border-radius:6px;padding:12px;margin-bottom:12px;">
                        <div style="font-size:11px;color:#7C3AED;font-weight:600;margin-bottom:6px;">Active — expires {{ $activeShareLink->expires_at->format('M j, Y') }}</div>
                        <input readonly value="{{ route('public.media-upload', $activeShareLink->token) }}" onclick="this.select()" class="krd-input" style="font-size:11px;">
                    </div>
                    <button wire:click="disableShareLink({{ $activeShareLink->id }})" class="krd-btn krd-btn-danger" style="width:100%;">Disable Link</button>
                @else
                    <div class="krd-input-group">
                        <label class="krd-label-text">Expires after</label>
                        <select wire:model="shareLinkExpiryDays" class="krd-input">
                            <option value="30">30 days</option>
                            <option value="60">60 days</option>
                            <option value="90">90 days</option>
                        </select>
                    </div>
                    <button wire:click="generateShareLink" class="krd-btn krd-btn-primary" style="width:100%;">Generate Link</button>
                @endif

                <button wire:click="$set('showShareLinkModal', false)" class="krd-btn krd-btn-ghost" style="width:100%;margin-top:8px;">Close</button>
            </div>
        </div>
    </div>
    </template>

    {{-- Folder Modal --}}
    <template x-teleport="body">
    <div x-show="$wire.showFolderForm" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:380px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:12px;">New Folder</h3>
                <input wire:model="newFolderName" type="text" class="krd-input" placeholder="Folder name">
                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button wire:click="createFolder" class="krd-btn krd-btn-primary" style="flex:1;">Create</button>
                    <button wire:click="$set('showFolderForm', false)" class="krd-btn krd-btn-ghost" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- Link Modal --}}
    <template x-teleport="body">
    <div x-show="$wire.showLinkForm" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:380px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:12px;">Add Link</h3>
                <input wire:model="newLinkName" type="text" class="krd-input" placeholder="Link name" style="margin-bottom:8px;">
                <input wire:model="newLinkUrl" type="url" class="krd-input" placeholder="https://...">
                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button wire:click="addLink" class="krd-btn krd-btn-primary" style="flex:1;">Save</button>
                    <button wire:click="$set('showLinkForm', false)" class="krd-btn krd-btn-ghost" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- Delete Document Modal --}}
    <template x-teleport="body">
    <div x-show="$wire.showDeleteModal" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:380px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:8px;">Delete this file?</h3>
                <p style="font-size:12px;color:#78716C;margin-bottom:20px;">This cannot be undone.</p>
                <div style="display:flex;gap:10px;">
                    <button wire:click="deleteDocument" class="krd-btn krd-btn-danger" style="flex:1;">Delete</button>
                    <button wire:click="$set('showDeleteModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- Bulk Delete Modal --}}
    <template x-teleport="body">
    <div x-show="$wire.showBulkDeleteModal" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:380px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:8px;">Delete {{ count($selectedDocumentIds) }} file(s)?</h3>
                <p style="font-size:12px;color:#78716C;margin-bottom:20px;">This cannot be undone.</p>
                <div style="display:flex;gap:10px;">
                    <button x-on:click="$wire.bulkDelete(); selectedIds = []; selectMode = false;" class="krd-btn krd-btn-danger" style="flex:1;">Delete</button>
                    <button wire:click="$set('showBulkDeleteModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- Delete Folder Modal --}}
    <template x-teleport="body">
    <div x-show="$wire.showDeleteFolderModal" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:380px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:8px;">Delete this folder?</h3>
                <p style="font-size:12px;color:#78716C;margin-bottom:20px;">Blocked if it still contains files.</p>
                <div style="display:flex;gap:10px;">
                    <button wire:click="deleteFolder" class="krd-btn krd-btn-danger" style="flex:1;">Delete</button>
                    <button wire:click="$set('showDeleteFolderModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>

</div>

<script type="application/json" id="krd-media-items-data">
    {!! $mediaItemsJson !!}
</script>

<script>
    function mediaLibraryUI(eventId, currentFolderId, initialSelectMode, initialSelectedIds) {
        return {
            mediaItems: [],
            lightbox: { open: false, index: 0 },
            selectMode: initialSelectMode,
            selectedIds: [...initialSelectedIds],

            init() {
                const raw = document.getElementById('krd-media-items-data').textContent;
                this.mediaItems = JSON.parse(raw);
                window.KoordliUploader.onComplete = () => { $wire.$refresh(); };
                window.KoordliUploader.openLightbox = (index) => {
                    this.lightbox = { open: true, index };
                };
            },

            toggleSelectMode() {
                this.selectMode = !this.selectMode;
                this.selectedIds = [];
            },

            isSelected(id) {
                return this.selectedIds.includes(id);
            },

            toggleSelected(id) {
                if (this.isSelected(id)) {
                    this.selectedIds = this.selectedIds.filter((i) => i !== id);
                } else {
                    this.selectedIds.push(id);
                }
            },

            // Selection stays purely client-side (instant, no round-trip)
            // until an actual action is taken — at that point Livewire
            // needs the real IDs, so we sync just before calling it.
            downloadSelected() {
                if (this.selectedIds.length === 0) return;
                $wire.set('selectedDocumentIds', this.selectedIds).then(() => $wire.downloadSelected());
            },

            confirmBulkDelete() {
                if (this.selectedIds.length === 0) return;
                $wire.set('selectedDocumentIds', this.selectedIds).then(() => $wire.confirmBulkDelete());
            },

            get current() {
                return this.mediaItems[this.lightbox.index] || { url: '', kind: 'image', downloadUrl: '#' };
            },

            next() {
                if (this.mediaItems.length === 0) return;
                this.lightbox.index = (this.lightbox.index + 1) % this.mediaItems.length;
            },

            prev() {
                if (this.mediaItems.length === 0) return;
                this.lightbox.index = (this.lightbox.index - 1 + this.mediaItems.length) % this.mediaItems.length;
            },

            onKeydown(e) {
                if (!this.lightbox.open) return;
                if (e.key === 'ArrowRight') this.next();
                if (e.key === 'ArrowLeft') this.prev();
                if (e.key === 'Escape') this.lightbox.open = false;
            },
        };
    }
</script>

<style>
@media (max-width: 640px) {
    .media-event-logo-card {
        align-items: flex-start !important;
        flex-wrap: wrap;
        gap: 12px !important;
        padding: 16px !important;
    }

    .media-event-logo-copy {
        flex-basis: calc(100% - 80px);
    }

    .media-event-logo-actions {
        width: 100%;
        padding-left: 76px;
    }

    .media-toolbar {
        align-items: stretch !important;
    }

    .media-toolbar-actions {
        width: 100%;
        margin-left: 0 !important;
    }

    .media-toolbar-actions .krd-btn {
        flex: 1 1 auto;
    }

    .media-folder-grid,
    .media-file-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 8px !important;
    }
}
</style>