<div x-data="{ view: '{{ $view }}' }">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <div class="krd-label" style="margin-bottom:4px;">Operations</div>
            <h2 class="krd-heading-3" style="color:#1C1917;">Vendor Directory</h2>
            <div style="font-size:12px;color:#A8A29E;margin-top:3px;">{{ $totalCount }} {{ Str::plural('vendor', $totalCount) }} in your directory</div>
        </div>
        <a href="{{ route('tenant.vendors.create') }}" wire:navigate class="krd-btn krd-btn-primary">
            + Add Vendor
        </a>
    </div>

    {{-- Filters + View Toggle --}}
    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
        <div style="display:flex;gap:10px;flex-wrap:wrap;flex:1;">
            <input wire:model.live.debounce.300ms="search" type="text" class="krd-input"
                placeholder="Search vendors..." style="max-width:220px;" />

            <x-ui.dropdown wire="categoryFilter" placeholder="All categories"
                selected="{{ $categoryFilter ? ($categories->firstWhere('id', (int)$categoryFilter)?->name ?? 'All categories') : 'All categories' }}"
                max-width="180px">
                @foreach($categories as $cat)
                <div class="krd-dropdown-option {{ $categoryFilter == $cat->id ? 'selected' : '' }}"
                    x-on:click="select('{{ $cat->name }}', {{ $cat->id }})">
                    {{ $cat->name }}
                </div>
                @endforeach
            </x-ui.dropdown>

            <x-ui.dropdown wire="statusFilter" placeholder="All vendors"
                selected="{{ $statusFilter ? ucfirst($statusFilter) : 'All vendors' }}"
                max-width="160px">
                <div class="krd-dropdown-option {{ $statusFilter === 'preferred' ? 'selected' : '' }}"
                    x-on:click="select('Preferred', 'preferred')">⭐ Preferred</div>
                <div class="krd-dropdown-option {{ $statusFilter === 'active' ? 'selected' : '' }}"
                    x-on:click="select('Active', 'active')">Active</div>
                <div class="krd-dropdown-option {{ $statusFilter === 'inactive' ? 'selected' : '' }}"
                    x-on:click="select('Inactive', 'inactive')">Inactive</div>
            </x-ui.dropdown>

            @if($search || $categoryFilter || $statusFilter)
            <button wire:click="$set('search','');$set('categoryFilter','');$set('statusFilter','')"
                class="krd-btn krd-btn-ghost krd-btn-sm" style="color:#EF4444;">Clear</button>
            @endif
        </div>

        {{-- View Toggle --}}
        <div style="display:flex;border:1px solid #E7E5E4;border-radius:6px;overflow:hidden;flex-shrink:0;">
            <button type="button"
                x-on:click="view='grid';$wire.setView('grid')"
                :style="view==='grid'?'padding:7px 12px;background:#7C3AED;color:#fff;border:none;cursor:pointer;display:flex;align-items:center;':'padding:7px 12px;background:#fff;color:#78716C;border:none;cursor:pointer;display:flex;align-items:center;'">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                </svg>
            </button>
            <button type="button"
                x-on:click="view='list';$wire.setView('list')"
                :style="view==='list'?'padding:7px 12px;background:#7C3AED;color:#fff;border:none;cursor:pointer;display:flex;align-items:center;border-left:1px solid #E7E5E4;':'padding:7px 12px;background:#fff;color:#78716C;border:none;cursor:pointer;display:flex;align-items:center;border-left:1px solid #E7E5E4;'">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
                    <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Grid View --}}
    <div x-show="view === 'grid'">
        @if($vendors->isEmpty())
        <div class="krd-card">
            <div class="krd-empty-state">
                <div class="krd-empty-state-icon">🏢</div>
                <div class="krd-empty-state-title">No vendors found</div>
                <div class="krd-empty-state-desc">Add your first vendor to build your company directory.</div>
            </div>
        </div>
        @else
        <div class="krd-grid-3" style="margin-bottom:16px;">
            @foreach($vendors as $vendor)
            <div class="krd-card" style="padding:0;overflow:hidden;display:flex;flex-direction:column;">
                {{-- Top bar --}}
                <div style="height:4px;background:{{ $vendor->is_preferred ? '#F59E0B' : '#E7E5E4' }};"></div>
                <div style="padding:16px;flex:1;">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:10px;gap:8px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:36px;height:36px;border-radius:8px;background:#EDE9FE;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:700;color:#7C3AED;flex-shrink:0;">
                                {{ strtoupper(substr($vendor->name, 0, 1)) }}
                            </div>
                            <div style="min-width:0;">
                                <div style="font-size:13px;font-weight:600;color:#1C1917;line-height:1.3;">{{ $vendor->name }}</div>
                                @if($vendor->category)
                                <div style="font-size:11px;color:#A8A29E;">{{ $vendor->category->name }}</div>
                                @endif
                            </div>
                        </div>
                        <button type="button" wire:click="togglePreferred({{ $vendor->id }})"
                            style="background:none;border:none;cursor:pointer;font-size:16px;flex-shrink:0;padding:0;"
                            title="{{ $vendor->is_preferred ? 'Remove from preferred' : 'Mark as preferred' }}">
                            {{ $vendor->is_preferred ? '⭐' : '☆' }}
                        </button>
                    </div>

                    {{-- Rating --}}
                    @if($vendor->rating)
                    <div style="font-size:12px;color:#F59E0B;margin-bottom:8px;">{{ $vendor->ratingStars() }}</div>
                    @endif

                    {{-- Contact --}}
                    <div style="display:flex;flex-direction:column;gap:4px;margin-bottom:10px;">
                        @if($vendor->contact_name)
                        <div style="font-size:11px;color:#78716C;display:flex;align-items:center;gap:5px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            {{ $vendor->contact_name }}
                        </div>
                        @endif
                        @if($vendor->phone)
                        <div style="font-size:11px;color:#78716C;display:flex;align-items:center;gap:5px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.63A2 2 0 012 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                            {{ $vendor->phone }}
                        </div>
                        @endif
                        @if($vendor->email)
                        <div style="font-size:11px;color:#78716C;display:flex;align-items:center;gap:5px;overflow:hidden;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $vendor->email }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Stats --}}
                    <div style="display:flex;gap:10px;">
                        <div style="background:#F5F5F4;border-radius:4px;padding:6px 10px;flex:1;text-align:center;">
                            <div style="font-size:16px;font-weight:700;color:#7C3AED;">{{ $vendor->eventAssignments->count() }}</div>
                            <div style="font-size:10px;color:#A8A29E;">Events</div>
                        </div>
                        @if(!$vendor->is_active)
                        <span class="krd-badge krd-badge-stone" style="align-self:center;">Inactive</span>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div style="padding:12px 16px;border-top:1px solid #E7E5E4;display:flex;gap:8px;">
                    <a href="{{ route('tenant.vendors.show', $vendor->id) }}" wire:navigate
                        class="krd-btn krd-btn-secondary krd-btn-sm" style="flex:1;justify-content:center;">
                        View
                    </a>
                    <a href="{{ route('tenant.vendors.edit', $vendor->id) }}" wire:navigate
                        class="krd-btn krd-btn-secondary krd-btn-sm">Edit</a>
                    <button wire:click="confirmDelete({{ $vendor->id }})" class="krd-btn krd-btn-sm"
                        style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4h6v2"/>
                        </svg>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @if($vendors->hasPages())
        <div>{{ $vendors->links() }}</div>
        @endif
        @endif
    </div>

    {{-- List View --}}
    <div x-show="view === 'list'">
        <div class="krd-card" style="padding:0;overflow:hidden;">
            <div class="krd-table-wrap">
                <table class="krd-table">
                    <thead>
                        <tr>
                            <th>Vendor</th>
                            <th>Category</th>
                            <th>Contact</th>
                            <th>Rating</th>
                            <th>Events</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $vendor)
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div style="width:30px;height:30px;border-radius:6px;background:#EDE9FE;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#7C3AED;flex-shrink:0;">
                                        {{ strtoupper(substr($vendor->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-size:13px;font-weight:500;color:#1C1917;">
                                            {{ $vendor->name }}
                                            @if($vendor->is_preferred)<span style="font-size:11px;">⭐</span>@endif
                                        </div>
                                        @if($vendor->email)<div style="font-size:11px;color:#A8A29E;">{{ $vendor->email }}</div>@endif
                                    </div>
                                </div>
                            </td>
                            <td style="font-size:12px;color:#57534E;">{{ $vendor->category?->name ?? '—' }}</td>
                            <td style="font-size:12px;color:#57534E;">
                                @if($vendor->contact_name)<div>{{ $vendor->contact_name }}</div>@endif
                                @if($vendor->phone)<div style="color:#A8A29E;">{{ $vendor->phone }}</div>@endif
                            </td>
                            <td style="font-size:12px;color:#F59E0B;">{{ $vendor->ratingStars() }}</td>
                            <td style="font-size:13px;font-weight:600;color:#7C3AED;">{{ $vendor->eventAssignments->count() }}</td>
                            <td>
                                @if($vendor->is_active)
                                <span class="krd-badge krd-badge-green">Active</span>
                                @else
                                <span class="krd-badge krd-badge-stone">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;gap:6px;">
                                    <a href="{{ route('tenant.vendors.show', $vendor->id) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">View</a>
                                    <a href="{{ route('tenant.vendors.edit', $vendor->id) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">Edit</a>
                                    <button wire:click="confirmDelete({{ $vendor->id }})" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4h6v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <div class="krd-empty-state">
                                    <div class="krd-empty-state-icon">🏢</div>
                                    <div class="krd-empty-state-title">No vendors found</div>
                                    <div class="krd-empty-state-desc">Add your first vendor to build your directory.</div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($vendors->hasPages())
            <div style="padding:12px 16px;border-top:1px solid #E7E5E4;">{{ $vendors->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Delete Modal --}}
    @if($showDeleteModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Delete Vendor?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">
                This will remove the vendor from your directory and all event assignments. This cannot be undone.
            </p>
            <div style="display:flex;gap:10px;">
                <button wire:click="delete" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Delete</button>
                <button wire:click="cancelDelete" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif

</div>