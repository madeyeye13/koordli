<div x-data="clientMediaLibraryUI({{ $event->id }}, {{ $currentFolderId ?? 'null' }})" x-init="init()" x-on:keydown.window="onKeydown">

    <div style="margin-bottom:24px;">
        <h2 class="krd-heading-3" style="color:#1C1917;">Media Library — {{ $event->name }}</h2>
        <p style="font-size:12px;color:#78716C;margin-top:4px;">Photos, videos, and documents shared for this event.</p>
    </div>

    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;align-items:center;">
        @if($currentFolder)
        <button wire:click="openFolder(null)" class="krd-btn krd-btn-ghost krd-btn-sm">← All Files</button>
        <span style="font-size:13px;font-weight:600;color:#1C1917;">{{ $currentFolder->name }}</span>
        @endif

        @if($documents->isNotEmpty())
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
            <div style="margin-left:auto;">
                <button type="button" onclick="document.getElementById('krd-client-file-input').click()" class="krd-btn krd-btn-primary krd-btn-sm">Upload Files</button>
                <input type="file" id="krd-client-file-input" multiple style="display:none;"
                    onchange="window.KoordliUploader.enqueue(this.files, {{ $event->id }}, {{ $currentFolderId ?? 'null' }}, 'file')">
            </div>
        </template>
    </div>

    @if($folders->isNotEmpty())
    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(140px, 1fr));gap:12px;margin-bottom:20px;">
        @foreach($folders as $folder)
        <div class="krd-card" style="padding:14px;cursor:pointer;text-align:center;" wire:click="openFolder({{ $folder->id }})">
            <div style="margin-bottom:6px;display:flex;justify-content:center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#7C3AED" stroke-width="1.5">
                    <path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/>
                </svg>
            </div>
            <div style="font-size:12px;font-weight:600;color:#1C1917;word-break:break-word;">{{ $folder->name }}</div>
        </div>
        @endforeach
    </div>
    @endif

    <div id="krd-upload-progress" style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:20px;"></div>

    @if($documents->isEmpty())
        <div class="krd-card" style="padding:40px;text-align:center;color:#A8A29E;">
            <div style="margin-bottom:8px;display:flex;justify-content:center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="#D6D3D1" stroke-width="1.5">
                    <path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/>
                </svg>
            </div>
            <div style="font-size:13px;">No files here yet.</div>
        </div>
    @else
    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(160px, 1fr));gap:12px;">
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
                    <div style="text-align:center;margin-bottom:6px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#3B82F6" stroke-width="1.5" style="display:inline-block;">
                            <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/>
                            <path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/>
                        </svg>
                    </div>
                    <div style="font-size:12px;font-weight:600;color:#1C1917;word-break:break-word;">{{ $doc->name }}</div>
                </a>
            @elseif($doc->isImage())
                @php $thumbUrl = $doc->thumbnail_path ? \Illuminate\Support\Facades\Storage::disk($doc->disk)->url($doc->thumbnail_path) : \Illuminate\Support\Facades\Storage::disk($doc->disk)->url($doc->path); @endphp
                <div style="position:relative;">
                    <img src="{{ $thumbUrl }}" style="width:100%;height:100px;object-fit:cover;border-radius:6px;cursor:pointer;margin-bottom:6px;"
                        onclick="event.stopPropagation(); window.KoordliUploader.openLightbox({{ $mediaIndex }})">
                    @if($doc->compression_status === 'pending' || $doc->compression_status === 'processing')
                    <span style="position:absolute;top:4px;right:4px;background:#1C1917CC;color:#fff;font-size:9px;padding:2px 6px;border-radius:4px;">Processing…</span>
                    @endif
                </div>
                <div style="font-size:11px;color:#1C1917;word-break:break-word;">{{ $doc->name }}</div>
                @php $mediaIndex++; @endphp
            @elseif($doc->isVideo())
                @php $videoThumbUrl = $doc->thumbnail_path ? \Illuminate\Support\Facades\Storage::disk($doc->disk)->url($doc->thumbnail_path) : null; @endphp
                <div style="position:relative;width:100%;height:100px;background:#1C1917;border-radius:6px;display:flex;align-items:center;justify-content:center;cursor:pointer;margin-bottom:6px;background-size:cover;background-position:center;{{ $videoThumbUrl ? 'background-image:url(' . $videoThumbUrl . ');' : '' }}"
                    onclick="event.stopPropagation(); window.KoordliUploader.openLightbox({{ $mediaIndex }})">
                    <span style="color:#fff;font-size:24px;text-shadow:0 1px 4px rgba(0,0,0,0.5);">▶</span>
                    @if($doc->compression_status === 'pending' || $doc->compression_status === 'processing')
                    <span style="position:absolute;top:4px;right:4px;background:#1C1917CC;color:#fff;font-size:9px;padding:2px 6px;border-radius:4px;">Processing…</span>
                    @endif
                </div>
                <div style="font-size:11px;color:#1C1917;word-break:break-word;">{{ $doc->name }}</div>
                @php $mediaIndex++; @endphp
            @else
                <a href="{{ route('media.download', $doc->id) }}" target="_blank" style="display:block;text-decoration:none;" onclick="event.stopPropagation();">
                    <div style="text-align:center;margin-bottom:6px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#78716C" stroke-width="1.5" style="display:inline-block;">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                    </div>
                    <div style="font-size:11px;color:#1C1917;word-break:break-word;">{{ $doc->name }}</div>
                </a>
            @endif

            @if($doc->type !== 'link')
            <a x-show="!selectMode" href="{{ route('media.download', $doc->id) }}" target="_blank" title="Download" onclick="event.stopPropagation();"
                style="display:inline-flex;align-items:center;margin-top:6px;margin-right:8px;color:#78716C;font-size:11px;text-decoration:none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="margin-right:3px;">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Download
            </a>
            @endif

            <button x-show="!selectMode" wire:click="confirmDelete({{ $doc->id }})" onclick="event.stopPropagation();" style="margin-top:6px;background:none;border:none;color:#DC2626;font-size:11px;cursor:pointer;">Delete</button>
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
                <h3 style="font-size:15px;font-weight:600;margin-bottom:8px;">Delete <span x-text="selectedIds.length"></span> file(s)?</h3>
                <p style="font-size:12px;color:#78716C;margin-bottom:20px;">This cannot be undone.</p>
                <div style="display:flex;gap:10px;">
                    <button x-on:click="$wire.bulkDelete(); selectedIds = []; selectMode = false;" class="krd-btn krd-btn-danger" style="flex:1;">Delete</button>
                    <button wire:click="$set('showBulkDeleteModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>

</div>

<script>
    function clientMediaLibraryUI(eventId, currentFolderId) {
        return {
            mediaItems: [],
            lightbox: { open: false, index: 0 },
            selectMode: false,
            selectedIds: [],

            init() {
                const raw = document.getElementById('krd-media-items-data').textContent;
                this.mediaItems = JSON.parse(raw);
                window.KoordliUploader.onComplete = () => { $wire.$refresh(); };
                window.KoordliUploader.openLightbox = (index) => {
                    this.lightbox = { open: true, index };
                };
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

            downloadSelected() {
                if (this.selectedIds.length === 0) return;
                $wire.set('selectedDocumentIds', this.selectedIds).then(() => $wire.downloadSelected());
            },

            confirmBulkDelete() {
                if (this.selectedIds.length === 0) return;
                $wire.set('selectedDocumentIds', this.selectedIds).then(() => $wire.confirmBulkDelete());
            },
        };
    }
</script>

<script type="application/json" id="krd-media-items-data">
    {!! $mediaItemsJson !!}
</script>