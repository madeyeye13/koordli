<div x-data="{ showDeleteModal: false, deleteId: null }">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <div class="krd-label" style="margin-bottom:4px;">Operations</div>
            <h2 class="krd-heading-3" style="color:#1C1917;">{{ term_title('asset_plural', 'Assets') }}</h2>
        </div>
        <a href="{{ route('tenant.assets.create') }}" wire:navigate class="krd-btn krd-btn-primary">
            + Add {{ term_title('asset', 'Asset') }}
        </a>
    </div>

    <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
        <input wire:model.live.debounce.300ms="search" type="text" class="krd-input" placeholder="Search {{ term('asset_plural', 'assets') }}..." style="max-width:240px;" />

        <x-ui.dropdown wire="categoryFilter" placeholder="All categories"
            selected="{{ $categoryFilter ? ($categories->firstWhere('id', (int)$categoryFilter)?->name ?? 'All categories') : 'All categories' }}"
            max-width="180px">
            @foreach($categories as $cat)
            <div class="krd-dropdown-option {{ $categoryFilter == $cat->id ? 'selected' : '' }}" x-on:click="select(@js($cat->name), '{{ $cat->id }}')">{{ $cat->name }}</div>
            @endforeach
        </x-ui.dropdown>

        <x-ui.dropdown wire="statusFilter" placeholder="All statuses"
            selected="{{ $statusFilter ? ucfirst($statusFilter) : 'All statuses' }}"
            max-width="160px">
            <div class="krd-dropdown-option {{ $statusFilter === 'available' ? 'selected' : '' }}" x-on:click="select('Available', 'available')">Available</div>
            <div class="krd-dropdown-option {{ $statusFilter === 'reserved' ? 'selected' : '' }}" x-on:click="select('Reserved', 'reserved')">Reserved</div>
            <div class="krd-dropdown-option {{ $statusFilter === 'maintenance' ? 'selected' : '' }}" x-on:click="select('Maintenance', 'maintenance')">Maintenance</div>
        </x-ui.dropdown>
    </div>

    @if($assets->isEmpty())
    <div class="krd-card">
        <div class="krd-empty-state">
            <div class="krd-empty-state-icon">📦</div>
            <div class="krd-empty-state-title">No {{ term('asset_plural', 'assets') }} found</div>
            <div class="krd-empty-state-desc">Add cameras, speakers, chairs, or any resource you manage.</div>
        </div>
    </div>
    @else

    {{-- Desktop --}}
    <div class="krd-card" style="padding:0;overflow:hidden;" id="assets-desktop">
        <div class="krd-table-wrap">
            <table class="krd-table">
                <thead><tr><th>Name</th><th>Category</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @foreach($assets as $asset)
                    <tr style="cursor:pointer;" onclick="window.location.href='{{ route('tenant.assets.show', $asset->id) }}'">
                        <td style="font-size:13px;font-weight:500;color:#1C1917;">{{ $asset->name }}</td>
                        <td style="font-size:12px;color:#78716C;">{{ $asset->category?->name ?? '—' }}</td>
                        <td><span class="krd-badge" style="background:{{ $asset->statusColor() }}1a;color:{{ $asset->statusColor() }};">{{ $asset->statusLabel() }}</span></td>
                        <td onclick="event.stopPropagation();">
                            <div style="display:flex;gap:6px;">
                                <a href="{{ route('tenant.assets.edit', $asset->id) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">Edit</a>
                                <button x-on:click="showDeleteModal = true; deleteId = {{ $asset->id }}" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;">✕</button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile --}}
    <div id="assets-mobile" style="display:flex;flex-direction:column;gap:10px;">
        @foreach($assets as $asset)
        <a href="{{ route('tenant.assets.show', $asset->id) }}" wire:navigate class="krd-card" style="padding:16px;text-decoration:none;display:block;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:6px;">
                <div style="font-size:14px;font-weight:600;color:#1C1917;">{{ $asset->name }}</div>
                <span class="krd-badge" style="background:{{ $asset->statusColor() }}1a;color:{{ $asset->statusColor() }};flex-shrink:0;">{{ $asset->statusLabel() }}</span>
            </div>
            <div style="font-size:12px;color:#78716C;">{{ $asset->category?->name ?? '—' }}</div>
        </a>
        @endforeach
    </div>
    @endif

    <div x-show="showDeleteModal" x-cloak style="position:fixed;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:60;">
        <div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);background:#fff;border-radius:8px;padding:28px;max-width:400px;width:90%;box-shadow:0 20px 50px rgba(28,25,23,0.2);">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Delete {{ term_title('asset', 'Asset') }}?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">
                This will permanently delete this {{ strtolower(term_title('asset', 'asset')) }}. This cannot be undone.
            </p>
            <div style="display:flex;gap:10px;">
                <button x-on:click="showDeleteModal = false; deleteId = null; $wire.delete(deleteId)" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Delete</button>
                <button x-on:click="showDeleteModal = false; deleteId = null" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>

</div>

<style>
@media (min-width:768px) { #assets-desktop { display:block !important; } #assets-mobile { display:none !important; } }
@media (max-width:767px) { #assets-desktop { display:none !important; } #assets-mobile { display:flex !important; } }
</style>