<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <div class="krd-label" style="margin-bottom:4px;">Vendor Contracts</div>
            <h2 class="krd-heading-3" style="color:#1C1917;">All Contracts</h2>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('tenant.contract-templates') }}" wire:navigate class="krd-btn krd-btn-secondary">Templates</a>
            <a href="{{ route('tenant.contracts.create') }}" wire:navigate class="krd-btn krd-btn-primary">+ New Contract</a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="krd-grid-4" style="gap:10px;margin-bottom:20px;" id="contract-stats-grid">
        <div class="krd-card" style="padding:14px;border-left:3px solid #78716C;">
            <div class="krd-label" style="margin-bottom:4px;">Draft</div>
            <div style="font-size:22px;font-weight:700;color:#78716C;">{{ $stats['draft'] }}</div>
        </div>
        <div class="krd-card" style="padding:14px;border-left:3px solid #3B82F6;">
            <div class="krd-label" style="margin-bottom:4px;">Sent</div>
            <div style="font-size:22px;font-weight:700;color:#3B82F6;">{{ $stats['sent'] }}</div>
        </div>
        <div class="krd-card" style="padding:14px;border-left:3px solid #10B981;">
            <div class="krd-label" style="margin-bottom:4px;">Signed</div>
            <div style="font-size:22px;font-weight:700;color:#10B981;">{{ $stats['signed'] }}</div>
        </div>
        <div class="krd-card" style="padding:14px;border-left:3px solid #F59E0B;">
            <div class="krd-label" style="margin-bottom:4px;">Expiring Soon</div>
            <div style="font-size:22px;font-weight:700;color:#F59E0B;">{{ $stats['expiring'] }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
        <input wire:model.live.debounce.300ms="search" type="text" class="krd-input" placeholder="Search contracts or vendors..." style="max-width:260px;" />
        <div x-data="{
                open: false,
                label: '{{ $statusFilter ? ucfirst($statusFilter) : 'All Statuses' }}',
                pick(val, label) { this.label = label; this.open = false; $wire.set('statusFilter', val); }
            }"
            x-on:click.outside="open = false" style="position:relative;min-width:160px;">
            <button type="button" x-on:click="open = !open" x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'" style="width:100%;">
                <span x-text="label"></span>
                <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div x-show="open" x-cloak class="krd-dropdown-menu">
                <div class="krd-dropdown-option" x-on:click="pick('', 'All Statuses')">All Statuses</div>
                <div class="krd-dropdown-option" x-on:click="pick('draft', 'Draft')">Draft</div>
                <div class="krd-dropdown-option" x-on:click="pick('sent', 'Sent')">Sent</div>
                <div class="krd-dropdown-option" x-on:click="pick('signed', 'Signed')">Signed</div>
                <div class="krd-dropdown-option" x-on:click="pick('expired', 'Expired')">Expired</div>
                <div class="krd-dropdown-option" x-on:click="pick('cancelled', 'Cancelled')">Cancelled</div>
            </div>
        </div>
    </div>

    @if($contracts->isEmpty())
    <div class="krd-card">
        <div class="krd-empty-state">
            <div class="krd-empty-state-icon">📝</div>
            <div class="krd-empty-state-title">No contracts yet</div>
            <div class="krd-empty-state-desc">Create your first vendor contract to get started.</div>
        </div>
    </div>
    @else

    {{-- Desktop --}}
    <div class="krd-card" style="padding:0;overflow:hidden;" id="contracts-desktop">
        <div class="krd-table-wrap">
            <table class="krd-table">
                <thead>
                    <tr>
                        <th>Contract</th>
                        <th>Vendor</th>
                        <th>Event</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Expires</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contracts as $contract)
                    <tr style="cursor:pointer;" onclick="window.location.href='{{ route('tenant.contracts.show', $contract->uuid) }}'">
                        <td style="font-size:13px;font-weight:500;color:#1C1917;">{{ $contract->title }}</td>
                        <td style="font-size:13px;color:#57534E;">{{ $contract->vendor->name }}</td>
                        <td style="font-size:12px;color:#78716C;">{{ $contract->event?->name ?? '— General —' }}</td>
                        <td style="font-size:13px;font-weight:500;color:#1C1917;">
                            {{ $contract->contract_amount ? \App\Helpers\CurrencyHelper::forTenant() . number_format($contract->contract_amount, 2) : '—' }}
                        </td>
                        <td>
                            <span class="krd-badge" style="background:{{ $contract->statusColor() }}1a;color:{{ $contract->statusColor() }};">
                                {{ $contract->statusLabel() }}
                            </span>
                            @if($contract->isExpiringSoon())
                            <span title="Expiring soon" style="margin-left:4px;">⚠️</span>
                            @endif
                        </td>
                        <td style="font-size:12px;color:#78716C;">{{ $contract->expires_at?->format('d M Y') ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile --}}
    <div id="contracts-mobile" style="display:flex;flex-direction:column;gap:10px;">
        @foreach($contracts as $contract)
        <a href="{{ route('tenant.contracts.show', $contract->uuid) }}" wire:navigate class="krd-card" style="padding:16px;text-decoration:none;display:block;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:6px;">
                <div style="font-size:14px;font-weight:600;color:#1C1917;">{{ $contract->title }}</div>
                <span class="krd-badge" style="background:{{ $contract->statusColor() }}1a;color:{{ $contract->statusColor() }};flex-shrink:0;">
                    {{ $contract->statusLabel() }}
                </span>
            </div>
            <div style="font-size:12px;color:#78716C;">{{ $contract->vendor->name }} · {{ $contract->event?->name ?? 'General' }}</div>
            @if($contract->contract_amount)
            <div style="font-size:12px;color:#1C1917;font-weight:500;margin-top:4px;">{{ \App\Helpers\CurrencyHelper::forTenant() }}{{ number_format($contract->contract_amount, 2) }}</div>
            @endif
        </a>
        @endforeach
    </div>
    @endif
</div>

<style>
@media (min-width:768px) { #contracts-desktop { display:block !important; } #contracts-mobile { display:none !important; } }
@media (max-width:767px) { #contracts-desktop { display:none !important; } #contracts-mobile { display:flex !important; } }
</style>