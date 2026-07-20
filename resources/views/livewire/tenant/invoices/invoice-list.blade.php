<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <div class="krd-label" style="margin-bottom:4px;">Vendor Management</div>
            <h2 class="krd-heading-3" style="color:#1C1917;">Vendor Invoices</h2>
        </div>
        <a href="{{ route('tenant.invoices.create') }}" wire:navigate class="krd-btn krd-btn-primary">+ New Invoice</a>
    </div>

    {{-- Stats --}}
    <div class="krd-grid-4" style="gap:10px;margin-bottom:20px;" id="invoice-stats-grid">
        <div class="krd-card" style="padding:14px;border-left:3px solid #7C3AED;">
            <div class="krd-label" style="margin-bottom:4px;">Total Invoiced</div>
            <div style="font-size:18px;font-weight:700;color:#7C3AED;">{{ \App\Helpers\CurrencyHelper::forTenant() }}{{ number_format($stats['total_invoiced'], 0) }}</div>
        </div>
        <div class="krd-card" style="padding:14px;border-left:3px solid #10B981;">
            <div class="krd-label" style="margin-bottom:4px;">Total Paid</div>
            <div style="font-size:18px;font-weight:700;color:#10B981;">{{ \App\Helpers\CurrencyHelper::forTenant() }}{{ number_format($stats['total_paid'], 0) }}</div>
        </div>
        <div class="krd-card" style="padding:14px;border-left:3px solid #F59E0B;">
            <div class="krd-label" style="margin-bottom:4px;">Outstanding</div>
            <div style="font-size:18px;font-weight:700;color:#F59E0B;">{{ \App\Helpers\CurrencyHelper::forTenant() }}{{ number_format($stats['total_outstanding'], 0) }}</div>
        </div>
        <div class="krd-card" style="padding:14px;border-left:3px solid #EF4444;">
            <div class="krd-label" style="margin-bottom:4px;">Overdue</div>
            <div style="font-size:22px;font-weight:700;color:#EF4444;">{{ $stats['overdue_count'] }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
        <input wire:model.live.debounce.300ms="search" type="text" class="krd-input" placeholder="Search invoice # or vendor..." style="max-width:260px;" />
        <div x-data="{
                open: false,
                label: '{{ $statusFilter ? \App\Models\Tenant\VendorInvoice::make(['status' => $statusFilter])->statusLabel() : 'All Statuses' }}',
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
                <div class="krd-dropdown-option" x-on:click="pick('partially_paid', 'Partially Paid')">Partially Paid</div>
                <div class="krd-dropdown-option" x-on:click="pick('paid', 'Paid')">Paid</div>
                <div class="krd-dropdown-option" x-on:click="pick('overdue', 'Overdue')">Overdue</div>
                <div class="krd-dropdown-option" x-on:click="pick('cancelled', 'Cancelled')">Cancelled</div>
            </div>
        </div>
    </div>

    @if($invoices->isEmpty())
    <div class="krd-card">
        <div class="krd-empty-state">
            <div class="krd-empty-state-icon">🧾</div>
            <div class="krd-empty-state-title">No invoices yet</div>
            <div class="krd-empty-state-desc">Create your first vendor invoice to start tracking payments.</div>
        </div>
    </div>
    @else

    {{-- Desktop --}}
    <div class="krd-card" style="padding:0;overflow:hidden;" id="invoices-desktop">
        <div class="krd-table-wrap">
            <table class="krd-table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Vendor</th>
                        <th>Event</th>
                        <th>Amount</th>
                        <th>Balance</th>
                        <th>Due</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                    <tr style="cursor:pointer;" onclick="window.location.href='{{ route('tenant.invoices.show', $invoice->uuid) }}'">
                        <td style="font-size:13px;font-weight:500;color:#1C1917;">
                            {{ $invoice->invoice_number }}
                            @if($invoice->title)<div style="font-size:11px;color:#A8A29E;">{{ $invoice->title }}</div>@endif
                        </td>
                        <td style="font-size:13px;color:#57534E;">{{ $invoice->vendor->name }}</td>
                        <td style="font-size:12px;color:#78716C;">{{ $invoice->event?->name ?? '— General —' }}</td>
                        <td style="font-size:13px;font-weight:500;color:#1C1917;">{{ \App\Helpers\CurrencyHelper::forTenant() }}{{ number_format($invoice->total_amount, 2) }}</td>
                        <td style="font-size:13px;font-weight:600;color:{{ $invoice->balance() > 0 ? '#EF4444' : '#10B981' }};">
                            {{ \App\Helpers\CurrencyHelper::forTenant() }}{{ number_format($invoice->balance(), 2) }}
                        </td>
                        <td style="font-size:12px;color:{{ $invoice->isOverdue() ? '#EF4444' : '#78716C' }};">{{ $invoice->due_date?->format('d M Y') ?? '—' }}</td>
                        <td>
                            <span class="krd-badge" style="background:{{ $invoice->statusColor() }}1a;color:{{ $invoice->statusColor() }};">
                                {{ $invoice->statusLabel() }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile --}}
    <div id="invoices-mobile" style="display:flex;flex-direction:column;gap:10px;">
        @foreach($invoices as $invoice)
        <a href="{{ route('tenant.invoices.show', $invoice->uuid) }}" wire:navigate class="krd-card" style="padding:16px;text-decoration:none;display:block;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:6px;">
                <div style="font-size:14px;font-weight:600;color:#1C1917;">{{ $invoice->invoice_number }}</div>
                <span class="krd-badge" style="background:{{ $invoice->statusColor() }}1a;color:{{ $invoice->statusColor() }};flex-shrink:0;">
                    {{ $invoice->statusLabel() }}
                </span>
            </div>
            <div style="font-size:12px;color:#78716C;">{{ $invoice->vendor->name }} · {{ $invoice->event?->name ?? 'General' }}</div>
            <div style="display:flex;justify-content:space-between;margin-top:8px;">
                <span style="font-size:12px;color:#1C1917;font-weight:500;">{{ \App\Helpers\CurrencyHelper::forTenant() }}{{ number_format($invoice->total_amount, 2) }}</span>
                <span style="font-size:12px;color:{{ $invoice->balance() > 0 ? '#EF4444' : '#10B981' }};font-weight:600;">Bal: {{ \App\Helpers\CurrencyHelper::forTenant() }}{{ number_format($invoice->balance(), 2) }}</span>
            </div>
        </a>
        @endforeach
    </div>
    @endif
</div>

<style>
@media (min-width:768px) { #invoices-desktop { display:block !important; } #invoices-mobile { display:none !important; } }
@media (max-width:767px) { #invoices-desktop { display:none !important; } #invoices-mobile { display:flex !important; } }
</style>