<div>
    {{-- Header --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <div class="krd-label" style="margin-bottom:4px;">Vendors</div>
            <h2 class="krd-heading-3" style="color:#1C1917;">Vendor Applications</h2>
        </div>
    </div>

    {{-- Filter Tabs — Alpine owns active state for instant UI --}}
    <div style="display:flex;gap:4px;border-bottom:1px solid #E7E5E4;margin-bottom:20px;"
         x-data="{ activeTab: '{{ $filter }}' }">
        @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $val => $label)
        <button type="button"
            x-on:click="activeTab = '{{ $val }}'; $wire.setFilter('{{ $val }}')"
            :style="activeTab === '{{ $val }}'
                ? 'padding:10px 16px;font-size:13px;font-weight:500;border:none;background:none;cursor:pointer;border-bottom:2px solid #7C3AED;color:#7C3AED;margin-bottom:-1px;'
                : 'padding:10px 16px;font-size:13px;font-weight:500;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;color:#78716C;margin-bottom:-1px;'"
        >
            {{ $label }}
            @if($counts[$val] > 0)
            <span
                :style="activeTab === '{{ $val }}'
                    ? 'font-size:10px;background:#EDE9FE;color:#7C3AED;padding:1px 7px;border-radius:10px;font-weight:600;margin-left:5px;'
                    : 'font-size:10px;background:#F5F5F4;color:#78716C;padding:1px 7px;border-radius:10px;font-weight:600;margin-left:5px;'"
            >{{ $counts[$val] }}</span>
            @endif
        </button>
        @endforeach
    </div>

    @if($applications->isEmpty())
    <div class="krd-card">
        <div class="krd-empty-state">
            <div class="krd-empty-state-icon">📋</div>
            <div class="krd-empty-state-title">No {{ $filter }} applications</div>
            <div class="krd-empty-state-desc">
                @if($filter === 'pending') Vendor applications will appear here when submitted.
                @elseif($filter === 'approved') Approved vendors will appear here.
                @else Rejected applications will appear here.
                @endif
            </div>
        </div>
    </div>
    @else

    {{-- Desktop Table --}}
    <div class="krd-card" style="padding:0;overflow:hidden;" id="vendor-apps-desktop">
        <div class="krd-table-wrap">
            <table class="krd-table">
                <thead>
                    <tr>
                        <th>Business</th>
                        <th>Contact</th>
                        <th>Category</th>
                        <th>Applied</th>
                        <th>Status</th>
                        @if($filter === 'pending') <th>Actions</th> @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($applications as $app)
                    <tr>
                        <td>
                            <div style="font-size:13px;font-weight:500;color:#1C1917;">{{ $app->business_name }}</div>
                            @if($app->service_description)
                            <div style="font-size:11px;color:#A8A29E;margin-top:2px;">{{ Str::limit($app->service_description, 60) }}</div>
                            @endif
                            <div style="display:flex;gap:8px;margin-top:4px;flex-wrap:wrap;">
                                @if($app->instagram)
                                <span style="font-size:11px;color:#7C3AED;">{{ $app->instagram }}</span>
                                @endif
                                @if($app->website)
                                <a href="{{ $app->website }}" target="_blank" style="font-size:11px;color:#3B82F6;text-decoration:none;">Website ↗</a>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div style="font-size:13px;color:#1C1917;">{{ $app->contact_name }}</div>
                            <div style="font-size:11px;color:#A8A29E;">{{ $app->email }}</div>
                            @if($app->phone)
                            <div style="font-size:11px;color:#A8A29E;">{{ $app->phone }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="krd-badge krd-badge-violet">{{ $app->category?->name ?? 'Uncategorized' }}</span>
                        </td>
                        <td style="font-size:12px;color:#78716C;">{{ $app->created_at->format('M d, Y') }}</td>
                        <td>
                            @if($app->status === 'pending')
                                <span class="krd-badge krd-badge-amber">Pending</span>
                            @elseif($app->status === 'approved')
                                <span class="krd-badge krd-badge-green">Approved</span>
                            @else
                                <span class="krd-badge krd-badge-red">Rejected</span>
                            @endif
                        </td>
                        @if($filter === 'pending')
                        <td>
                            <div style="display:flex;gap:6px;">
                                <button wire:click="approve({{ $app->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="approve({{ $app->id }})"
                                    class="krd-btn krd-btn-sm"
                                    style="background:#D1FAE5;color:#065F46;border-color:#6EE7B7;">
                                    <span wire:loading.remove wire:target="approve({{ $app->id }})">✓ Approve</span>
                                    <span wire:loading wire:target="approve({{ $app->id }})">...</span>
                                </button>
                                <button wire:click="openRejectModal({{ $app->id }})"
                                    class="krd-btn krd-btn-sm"
                                    style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">
                                    ✕ Reject
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile Cards --}}
    <div id="vendor-apps-mobile" style="display:flex;flex-direction:column;gap:12px;">
        @foreach($applications as $app)
        <div class="krd-card" style="padding:16px;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:12px;">
                <div>
                    <div style="font-size:14px;font-weight:600;color:#1C1917;">{{ $app->business_name }}</div>
                    <div style="font-size:12px;color:#78716C;margin-top:2px;">{{ $app->contact_name }} · {{ $app->email }}</div>
                </div>
                @if($app->status === 'pending')
                    <span class="krd-badge krd-badge-amber">Pending</span>
                @elseif($app->status === 'approved')
                    <span class="krd-badge krd-badge-green">Approved</span>
                @else
                    <span class="krd-badge krd-badge-red">Rejected</span>
                @endif
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
                <span class="krd-badge krd-badge-violet">{{ $app->category?->name ?? 'Uncategorized' }}</span>
                <span style="font-size:11px;color:#A8A29E;">Applied {{ $app->created_at->format('M d, Y') }}</span>
            </div>
            @if($app->service_description)
            <p style="font-size:12px;color:#78716C;line-height:1.6;margin-bottom:12px;">{{ Str::limit($app->service_description, 100) }}</p>
            @endif
            @if($filter === 'pending')
            <div style="display:flex;gap:8px;">
                <button wire:click="approve({{ $app->id }})"
                    wire:loading.attr="disabled"
                    wire:target="approve({{ $app->id }})"
                    class="krd-btn krd-btn-sm"
                    style="background:#D1FAE5;color:#065F46;border-color:#6EE7B7;flex:1;">
                    <span wire:loading.remove wire:target="approve({{ $app->id }})">✓ Approve</span>
                    <span wire:loading wire:target="approve({{ $app->id }})">Processing...</span>
                </button>
                <button wire:click="openRejectModal({{ $app->id }})"
                    class="krd-btn krd-btn-sm"
                    style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;flex:1;">
                    ✕ Reject
                </button>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    {{-- Reject Modal --}}
    @if($showRejectModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:440px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Reject Application</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:16px;line-height:1.6;">Optionally provide a reason for rejection.</p>
            <div class="krd-input-group">
                <label class="krd-label-text">Reason (optional)</label>
                <textarea wire:model="rejectionReason" class="krd-input" rows="3"
                    placeholder="e.g. We already have vendors in this category..."></textarea>
            </div>
            <div style="display:flex;gap:10px;margin-top:16px;">
                <button wire:click="confirmReject" class="krd-btn krd-btn-danger" style="flex:1;">Confirm Reject</button>
                <button wire:click="cancelReject" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif

</div>

<style>
@media (min-width: 768px) {
    #vendor-apps-desktop { display: block !important; }
    #vendor-apps-mobile  { display: none !important; }
}
@media (max-width: 767px) {
    #vendor-apps-desktop { display: none !important; }
    #vendor-apps-mobile  { display: flex !important; }
}
</style>