<div x-data="{ activeTab: 'breakdown' }">

    {{-- Header --}}
    <div style="margin-bottom:24px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('tenant.events.show', $event->slug) }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">
                ← Back to {{ Str::limit($event->name, 30) }}
            </a>
        </div>
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <div class="krd-label" style="margin-bottom:4px;">Budget Tracker</div>
                <h2 class="krd-heading-3" style="color:#1C1917;">{{ Str::limit($event->name, 40) }}</h2>
                @if($event->agreed_budget)
                <div style="font-size:12px;color:#78716C;margin-top:3px;">
                    Agreed budget: <strong>{{ $this->getCurrencySymbol() }}{{ number_format($event->agreed_budget, 2) }}</strong>
                </div>
                @endif
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                @if($budget && $budget->clientOutstanding() > 0 && $event->client_email)
                <button wire:click="sendOutstandingReminder" wire:loading.attr="disabled"
                    class="krd-btn krd-btn-sm"
                    style="background:#FEF3C7;color:#D97706;border-color:#FDE68A;">
                    <span wire:loading.remove wire:target="sendOutstandingReminder">📧 Send Reminder</span>
                    <span wire:loading wire:target="sendOutstandingReminder">Sending...</span>
                </button>
                @endif
                <button x-on:click="activeTab = 'payments'; $wire.set('showPaymentForm', true)"
                    class="krd-btn krd-btn-secondary krd-btn-sm">
                    + Record Payment
                </button>
                <button wire:click="$toggle('showAddForm')" class="krd-btn krd-btn-primary krd-btn-sm">
                    {{ $showAddForm ? '✕ Cancel' : '+ Add Item' }}
                </button>
            </div>
        </div>
    </div>

    {{-- Financial Summary Cards --}}
    @php
        $symbol    = $this->getCurrencySymbol();
        $agreed    = $budget ? $budget->agreedBudget()      : (float)($event->agreed_budget ?? 0);
        $estimated = $budget ? $budget->totalEstimated()    : 0;
        $actual    = $budget ? $budget->totalActual()       : 0;
        $vendorPaid= $budget ? $budget->totalVendorPaid()   : 0;
        $clientPaid= $budget ? $budget->totalClientPaid()   : 0;
        $outstanding = $budget ? $budget->clientOutstanding() : $agreed;
        $vendorBal = $budget ? $budget->totalVendorBalance(): 0;
        $profit    = $budget ? $budget->grossProfit()       : 0;
        $projProfit= $budget ? $budget->projectedProfit()   : ($agreed - $estimated);
        $spent     = $budget ? $budget->spentPercentage()   : 0;
        $collected = $budget ? $budget->collectedPercentage(): 0;
        $variance  = $budget ? $budget->variance()          : 0;
    @endphp

    {{-- Client Financial Summary --}}
    <div style="margin-bottom:16px;">
        <div style="font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#A8A29E;margin-bottom:10px;">Client Financials</div>
        <div class="krd-budget-summary-grid">
            <div class="krd-card" style="padding:14px;border-left:3px solid #7C3AED;">
                <div class="krd-label" style="margin-bottom:5px;">Agreed Budget</div>
                <div style="font-size:18px;font-weight:700;color:#7C3AED;">{{ $symbol }}{{ number_format($agreed, 2) }}</div>
            </div>
            <div class="krd-card" style="padding:14px;border-left:3px solid #10B981;">
                <div class="krd-label" style="margin-bottom:5px;">Client Paid</div>
                <div style="font-size:18px;font-weight:700;color:#10B981;">{{ $symbol }}{{ number_format($clientPaid, 2) }}</div>
                @if($agreed > 0)
                <div style="font-size:10px;color:#A8A29E;margin-top:2px;">{{ $collected }}% collected</div>
                @endif
            </div>
            <div class="krd-card" style="padding:14px;border-left:3px solid {{ $outstanding > 0 ? '#EF4444' : '#10B981' }};">
                <div class="krd-label" style="margin-bottom:5px;">Outstanding</div>
                <div style="font-size:18px;font-weight:700;color:{{ $outstanding > 0 ? '#EF4444' : '#10B981' }};">
                    {{ $symbol }}{{ number_format($outstanding, 2) }}
                </div>
                @if($outstanding <= 0)
                <div style="font-size:10px;color:#10B981;margin-top:2px;">Fully paid ✓</div>
                @endif
            </div>
            <div class="krd-card" style="padding:14px;border-left:3px solid {{ $profit >= 0 ? '#10B981' : '#EF4444' }};">
                <div class="krd-label" style="margin-bottom:5px;">Gross Profit</div>
                <div style="font-size:18px;font-weight:700;color:{{ $profit >= 0 ? '#10B981' : '#EF4444' }};">
                    {{ $profit < 0 ? '-' : '' }}{{ $symbol }}{{ number_format(abs($profit), 2) }}
                </div>
                <div style="font-size:10px;color:#A8A29E;margin-top:2px;">
                    Projected: {{ $projProfit >= 0 ? '' : '-' }}{{ $symbol }}{{ number_format(abs($projProfit), 2) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Vendor Financial Summary --}}
    <div style="margin-bottom:20px;">
        <div style="font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#A8A29E;margin-bottom:10px;">Vendor / Cost Breakdown</div>
        <div class="krd-budget-summary-grid">
            <div class="krd-card" style="padding:14px;border-left:3px solid #3B82F6;">
                <div class="krd-label" style="margin-bottom:5px;">Estimated Costs</div>
                <div style="font-size:18px;font-weight:700;color:#3B82F6;">{{ $symbol }}{{ number_format($estimated, 2) }}</div>
                @if($agreed > 0 && $variance != 0)
                <div style="font-size:10px;color:{{ $variance >= 0 ? '#10B981' : '#EF4444' }};margin-top:2px;">
                    {{ $variance >= 0 ? '↓ ' . $symbol . number_format(abs($variance), 2) . ' under budget' : '↑ ' . $symbol . number_format(abs($variance), 2) . ' over budget' }}
                </div>
                @endif
            </div>
            <div class="krd-card" style="padding:14px;border-left:3px solid #F59E0B;">
                <div class="krd-label" style="margin-bottom:5px;">Actual Spent</div>
                <div style="font-size:18px;font-weight:700;color:#F59E0B;">{{ $symbol }}{{ number_format($actual, 2) }}</div>
                @if($agreed > 0)
                <div style="font-size:10px;color:#A8A29E;margin-top:2px;">{{ $spent }}% of agreed budget</div>
                @endif
            </div>
            <div class="krd-card" style="padding:14px;border-left:3px solid #10B981;">
                <div class="krd-label" style="margin-bottom:5px;">Paid to Vendors</div>
                <div style="font-size:18px;font-weight:700;color:#10B981;">{{ $symbol }}{{ number_format($vendorPaid, 2) }}</div>
            </div>
            <div class="krd-card" style="padding:14px;border-left:3px solid {{ $vendorBal > 0 ? '#EF4444' : '#10B981' }};">
                <div class="krd-label" style="margin-bottom:5px;">Vendor Balance</div>
                <div style="font-size:18px;font-weight:700;color:{{ $vendorBal > 0 ? '#EF4444' : '#10B981' }};">
                    {{ $symbol }}{{ number_format(abs($vendorBal), 2) }}
                </div>
                @if($vendorBal > 0)
                <div style="font-size:10px;color:#EF4444;margin-top:2px;">Still owed to vendors</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Progress Bars --}}
    @if($agreed > 0)
    <div class="krd-card" style="padding:16px;margin-bottom:20px;">
        <div style="margin-bottom:14px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                <span style="font-size:12px;color:#57534E;font-weight:500;">Client payments collected</span>
                <span style="font-size:12px;font-weight:600;color:#10B981;">{{ $collected }}%</span>
            </div>
            <div style="height:8px;background:#E7E5E4;border-radius:4px;overflow:hidden;">
                <div style="height:100%;width:{{ min(100,$collected) }}%;background:#10B981;border-radius:4px;transition:width 400ms ease;"></div>
            </div>
        </div>
        <div>
            <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                <span style="font-size:12px;color:#57534E;font-weight:500;">Budget utilisation (actual spent)</span>
                <span style="font-size:12px;font-weight:600;color:{{ $spent > 100 ? '#EF4444' : '#F59E0B' }};">{{ $spent }}%</span>
            </div>
            <div style="height:8px;background:#E7E5E4;border-radius:4px;overflow:hidden;">
                <div style="height:100%;width:{{ min(100,$spent) }}%;background:{{ $spent > 100 ? '#EF4444' : ($spent > 80 ? '#F59E0B' : '#7C3AED') }};border-radius:4px;transition:width 400ms ease;"></div>
            </div>
        </div>
    </div>
    @endif

    {{-- Tabs --}}
    <div style="display:flex;gap:4px;border-bottom:1px solid #E7E5E4;margin-bottom:16px;">
        <button type="button"
            x-on:click="activeTab = 'breakdown'"
            :style="activeTab === 'breakdown'
                ? 'padding:10px 16px;font-size:13px;font-weight:500;border:none;background:none;cursor:pointer;border-bottom:2px solid #7C3AED;color:#7C3AED;margin-bottom:-1px;'
                : 'padding:10px 16px;font-size:13px;font-weight:500;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;color:#78716C;margin-bottom:-1px;'"
        >Cost Breakdown</button>
        <button type="button"
            x-on:click="activeTab = 'payments'"
            :style="activeTab === 'payments'
                ? 'padding:10px 16px;font-size:13px;font-weight:500;border:none;background:none;cursor:pointer;border-bottom:2px solid #7C3AED;color:#7C3AED;margin-bottom:-1px;'
                : 'padding:10px 16px;font-size:13px;font-weight:500;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;color:#78716C;margin-bottom:-1px;'"
        >Client Payments
            @if($budget && $budget->clientPayments->count() > 0)
            <span style="font-size:10px;background:#EDE9FE;color:#7C3AED;padding:1px 6px;border-radius:10px;font-weight:600;margin-left:4px;">{{ $budget->clientPayments->count() }}</span>
            @endif
        </button>
    </div>

    {{-- ══ TAB: Cost Breakdown ══ --}}
    <div x-show="activeTab === 'breakdown'">

        {{-- Add Item Form --}}
        @if($showAddForm)
        <div class="krd-card" style="padding:20px;margin-bottom:16px;border:2px solid #7C3AED;">
            <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:16px;">New Budget Item</div>
            <div class="krd-input-group">
                <label class="krd-label-text">Category <span style="color:#EF4444;">*</span></label>
                <input wire:model="newCategory" type="text" class="krd-input @error('newCategory') krd-input-error @enderror"
                    placeholder="e.g. Venue, Catering, Photography" autofocus />
                @error('newCategory') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>
            <div class="krd-budget-form-grid">
                <div class="krd-input-group">
                    <label class="krd-label-text">Estimated <span style="color:#EF4444;">*</span></label>
                    <input wire:model="newEstimated" type="number" step="0.01" min="0" class="krd-input @error('newEstimated') krd-input-error @enderror" placeholder="0.00" />
                    @error('newEstimated') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>
                <div class="krd-input-group">
                    <label class="krd-label-text">Actual Spent</label>
                    <input wire:model="newActual" type="number" step="0.01" min="0" class="krd-input" placeholder="0.00" />
                </div>
                <div class="krd-input-group">
                    <label class="krd-label-text">Paid to Vendor</label>
                    <input wire:model="newPaid" type="number" step="0.01" min="0" class="krd-input" placeholder="0.00" />
                </div>
            </div>
            <div class="krd-input-group" style="margin-bottom:0;">
                <label class="krd-label-text">Notes</label>
                <input wire:model="newNotes" type="text" class="krd-input" placeholder="Optional notes..." />
            </div>
            <div style="display:flex;gap:10px;margin-top:16px;flex-wrap:wrap;">
                <button wire:click="addItem" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                    <span wire:loading.remove wire:target="addItem">Add Item</span>
                    <span wire:loading wire:target="addItem">Adding...</span>
                </button>
                <button wire:click="$set('showAddForm', false)" type="button" class="krd-btn krd-btn-ghost">Cancel</button>
            </div>
        </div>
        @endif

        @if(!$budget || $budget->items->count() === 0)
        <div class="krd-card">
            <div class="krd-empty-state">
                <div class="krd-empty-state-icon">💰</div>
                <div class="krd-empty-state-title">No budget items yet</div>
                <div class="krd-empty-state-desc">Add cost items like venue, catering, photography to track your spending.</div>
            </div>
        </div>
        @else

        {{-- Desktop Table --}}
        <div class="krd-card" style="padding:0;overflow:hidden;" id="budget-table-desktop">
            <div class="krd-table-wrap">
                <table class="krd-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th style="text-align:right;">Estimated</th>
                            <th style="text-align:right;">Actual</th>
                            <th style="text-align:right;">Paid</th>
                            <th style="text-align:right;">Vendor Balance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($budget->items as $item)
                        @php
                            $itemBalance = (float)$item->actual - (float)$item->paid;
                            $isOver = (float)$item->actual > (float)$item->estimated;
                        @endphp

                        @if($editItemId === $item->id)
                        <tr style="background:#F5F3FF;">
                            <td><input wire:model="editCategory" type="text" class="krd-input" style="min-width:130px;" /></td>
                            <td><input wire:model="editEstimated" type="number" step="0.01" min="0" class="krd-input" style="min-width:90px;" /></td>
                            <td><input wire:model="editActual" type="number" step="0.01" min="0" class="krd-input" style="min-width:90px;" /></td>
                            <td><input wire:model="editPaid" type="number" step="0.01" min="0" class="krd-input" style="min-width:90px;" /></td>
                            <td></td><td></td>
                            <td>
                                <div style="display:flex;gap:6px;">
                                    <button wire:click="saveEdit" class="krd-btn krd-btn-primary krd-btn-sm">Save</button>
                                    <button wire:click="cancelEdit" class="krd-btn krd-btn-ghost krd-btn-sm">Cancel</button>
                                </div>
                            </td>
                        </tr>
                        @else
                        <tr>
                            <td>
                                <div style="font-size:13px;font-weight:500;color:#1C1917;">{{ $item->category }}</div>
                                @if($item->notes)<div style="font-size:11px;color:#A8A29E;">{{ $item->notes }}</div>@endif
                            </td>
                            <td style="text-align:right;font-size:13px;color:#3B82F6;">{{ $symbol }}{{ number_format($item->estimated, 2) }}</td>
                            <td style="text-align:right;font-size:13px;color:{{ $isOver ? '#EF4444' : '#57534E' }};">{{ $symbol }}{{ number_format($item->actual, 2) }}</td>
                            <td style="text-align:right;font-size:13px;color:#10B981;">{{ $symbol }}{{ number_format($item->paid, 2) }}</td>
                            <td style="text-align:right;font-size:13px;font-weight:500;color:{{ $itemBalance > 0 ? '#EF4444' : '#10B981' }};">
                                {{ $symbol }}{{ number_format(abs($itemBalance), 2) }}
                                @if($itemBalance > 0)<span style="font-size:10px;font-weight:400;"> due</span>@endif
                            </td>
                            <td>
                                @if((float)$item->actual === 0.0)
                                    <span class="krd-badge krd-badge-stone">Not Started</span>
                                @elseif($itemBalance <= 0)
                                    <span class="krd-badge krd-badge-green">Paid</span>
                                @elseif((float)$item->paid > 0)
                                    <span class="krd-badge krd-badge-amber">Partial</span>
                                @else
                                    <span class="krd-badge krd-badge-red">Unpaid</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;gap:6px;">
                                    <button wire:click="startEdit({{ $item->id }})" class="krd-btn krd-btn-secondary krd-btn-sm">Edit</button>
                                    <button wire:click="confirmDelete({{ $item->id }})" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4h6v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @endforeach

                        {{-- Totals --}}
                        <tr style="background:#F5F5F4;font-weight:600;">
                            <td style="font-size:12px;color:#57534E;">TOTALS</td>
                            <td style="text-align:right;font-size:13px;color:#3B82F6;">{{ $symbol }}{{ number_format($budget->totalEstimated(), 2) }}</td>
                            <td style="text-align:right;font-size:13px;color:#F59E0B;">{{ $symbol }}{{ number_format($budget->totalActual(), 2) }}</td>
                            <td style="text-align:right;font-size:13px;color:#10B981;">{{ $symbol }}{{ number_format($budget->totalVendorPaid(), 2) }}</td>
                            <td style="text-align:right;font-size:13px;color:{{ $budget->totalVendorBalance() > 0 ? '#EF4444' : '#10B981' }};">{{ $symbol }}{{ number_format(abs($budget->totalVendorBalance()), 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile Cards --}}
        <div id="budget-cards-mobile" style="display:flex;flex-direction:column;gap:10px;">
            @foreach($budget->items as $item)
            @php
                $itemBalance = (float)$item->actual - (float)$item->paid;
                $isOver = (float)$item->actual > (float)$item->estimated;
            @endphp
            <div class="krd-card" style="padding:16px;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:12px;">
                    <div>
                        <div style="font-size:13px;font-weight:600;color:#1C1917;">{{ $item->category }}</div>
                        @if($item->notes)<div style="font-size:11px;color:#A8A29E;margin-top:2px;">{{ $item->notes }}</div>@endif
                    </div>
                    @if((float)$item->actual === 0.0)
                        <span class="krd-badge krd-badge-stone">Not Started</span>
                    @elseif($itemBalance <= 0)
                        <span class="krd-badge krd-badge-green">Paid</span>
                    @elseif((float)$item->paid > 0)
                        <span class="krd-badge krd-badge-amber">Partial</span>
                    @else
                        <span class="krd-badge krd-badge-red">Unpaid</span>
                    @endif
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;">
                    <div style="background:#F5F5F4;border-radius:6px;padding:10px;">
                        <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#A8A29E;margin-bottom:2px;">Estimated</div>
                        <div style="font-size:14px;font-weight:700;color:#3B82F6;">{{ $symbol }}{{ number_format($item->estimated, 2) }}</div>
                    </div>
                    <div style="background:#F5F5F4;border-radius:6px;padding:10px;">
                        <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#A8A29E;margin-bottom:2px;">Actual</div>
                        <div style="font-size:14px;font-weight:700;color:{{ $isOver ? '#EF4444' : '#F59E0B' }};">{{ $symbol }}{{ number_format($item->actual, 2) }}</div>
                    </div>
                    <div style="background:#F5F5F4;border-radius:6px;padding:10px;">
                        <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#A8A29E;margin-bottom:2px;">Paid</div>
                        <div style="font-size:14px;font-weight:700;color:#10B981;">{{ $symbol }}{{ number_format($item->paid, 2) }}</div>
                    </div>
                    <div style="background:#F5F5F4;border-radius:6px;padding:10px;">
                        <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#A8A29E;margin-bottom:2px;">Balance</div>
                        <div style="font-size:14px;font-weight:700;color:{{ $itemBalance > 0 ? '#EF4444' : '#10B981' }};">{{ $symbol }}{{ number_format(abs($itemBalance), 2) }}</div>
                    </div>
                </div>
                <div style="display:flex;gap:8px;">
                    <button wire:click="startEdit({{ $item->id }})" class="krd-btn krd-btn-secondary krd-btn-sm">Edit</button>
                    <button wire:click="confirmDelete({{ $item->id }})" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">Delete</button>
                </div>
            </div>
            @endforeach

            {{-- Mobile Totals --}}
            <div class="krd-card" style="padding:16px;background:#F5F3FF;border-color:#DDD6FE;">
                <div style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#7C3AED;margin-bottom:12px;">Cost Summary</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    <div>
                        <div style="font-size:10px;color:#A8A29E;margin-bottom:2px;">Total Estimated</div>
                        <div style="font-size:15px;font-weight:700;color:#3B82F6;">{{ $symbol }}{{ number_format($budget->totalEstimated(), 2) }}</div>
                    </div>
                    <div>
                        <div style="font-size:10px;color:#A8A29E;margin-bottom:2px;">Total Actual</div>
                        <div style="font-size:15px;font-weight:700;color:#F59E0B;">{{ $symbol }}{{ number_format($budget->totalActual(), 2) }}</div>
                    </div>
                    <div>
                        <div style="font-size:10px;color:#A8A29E;margin-bottom:2px;">Paid to Vendors</div>
                        <div style="font-size:15px;font-weight:700;color:#10B981;">{{ $symbol }}{{ number_format($budget->totalVendorPaid(), 2) }}</div>
                    </div>
                    <div>
                        <div style="font-size:10px;color:#A8A29E;margin-bottom:2px;">Vendor Balance</div>
                        <div style="font-size:15px;font-weight:700;color:{{ $budget->totalVendorBalance() > 0 ? '#EF4444' : '#10B981' }};">{{ $symbol }}{{ number_format(abs($budget->totalVendorBalance()), 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- ══ TAB: Client Payments ══ --}}
    <div x-show="activeTab === 'payments'">

        {{-- Add Payment Form --}}
        @if($showPaymentForm)
        <div class="krd-card" style="padding:20px;margin-bottom:16px;border:2px solid #10B981;">
            <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:16px;">Record Client Payment</div>
            <div class="krd-budget-form-grid">
                <div class="krd-input-group">
                    <label class="krd-label-text">Amount <span style="color:#EF4444;">*</span></label>
                    <input wire:model="paymentAmount" type="number" step="0.01" min="1"
                        class="krd-input @error('paymentAmount') krd-input-error @enderror" placeholder="0.00" />
                    @error('paymentAmount') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>
                <div class="krd-input-group">
                    <label class="krd-label-text">Date <span style="color:#EF4444;">*</span></label>
                    <input wire:model="paymentDate" type="date" class="krd-input @error('paymentDate') krd-input-error @enderror" />
                    @error('paymentDate') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>
                <div class="krd-input-group">
                    <label class="krd-label-text">Method</label>
                    <x-ui.dropdown wire="paymentMethod" placeholder="Transfer"
                        selected="{{ match($paymentMethod) { 'transfer' => 'Bank Transfer', 'cash' => 'Cash', 'cheque' => 'Cheque', 'pos' => 'POS', 'ussd' => 'USSD', default => 'Bank Transfer' } }}">
                        @foreach(['transfer' => 'Bank Transfer', 'cash' => 'Cash', 'cheque' => 'Cheque', 'pos' => 'POS', 'ussd' => 'USSD'] as $val => $label)
                        <div class="krd-dropdown-option {{ $paymentMethod === $val ? 'selected' : '' }}"
                            x-on:click="select('{{ $label }}', '{{ $val }}')">{{ $label }}</div>
                        @endforeach
                    </x-ui.dropdown>
                </div>
            </div>
            <div class="krd-input-group" style="margin-bottom:0;">
                <label class="krd-label-text">Description / Reference</label>
                <input wire:model="paymentDescription" type="text" class="krd-input"
                    placeholder="e.g. Initial deposit, Final payment, Ref: TRF123..." />
            </div>
            <div style="display:flex;gap:10px;margin-top:16px;flex-wrap:wrap;">
                <button wire:click="addPayment" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                    <span wire:loading.remove wire:target="addPayment">Record Payment</span>
                    <span wire:loading wire:target="addPayment">Saving...</span>
                </button>
                <button wire:click="$set('showPaymentForm', false)" type="button" class="krd-btn krd-btn-ghost">Cancel</button>
            </div>
        </div>
        @else
        <div style="margin-bottom:16px;">
            <button wire:click="$set('showPaymentForm', true)" class="krd-btn krd-btn-primary krd-btn-sm">
                + Record Payment
            </button>
        </div>
        @endif

        {{-- Outstanding Alert --}}
        @if($outstanding > 0)
        <div style="background:#FEF3C7;border:1px solid #FDE68A;border-radius:8px;padding:14px 16px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:20px;">⚠️</span>
                <div>
                    <div style="font-size:13px;font-weight:600;color:#92400E;">Outstanding Balance</div>
                    <div style="font-size:12px;color:#92400E;">Client still owes {{ $symbol }}{{ number_format($outstanding, 2) }}</div>
                </div>
            </div>
            @if($event->client_email)
            <button wire:click="sendOutstandingReminder" wire:loading.attr="disabled"
                class="krd-btn krd-btn-sm"
                style="background:#F59E0B;color:#fff;border-color:#D97706;flex-shrink:0;">
                <span wire:loading.remove wire:target="sendOutstandingReminder">📧 Send Reminder Email</span>
                <span wire:loading wire:target="sendOutstandingReminder">Sending...</span>
            </button>
            @else
            <span style="font-size:11px;color:#92400E;">Add client email to send reminder</span>
            @endif
        </div>
        @endif

        {{-- Payment History --}}
        @if(!$budget || $budget->clientPayments->count() === 0)
        <div class="krd-card">
            <div class="krd-empty-state">
                <div class="krd-empty-state-icon">💳</div>
                <div class="krd-empty-state-title">No payments recorded</div>
                <div class="krd-empty-state-desc">Record client payments to track what has been collected.</div>
            </div>
        </div>
        @else
        <div class="krd-card" style="padding:0;overflow:hidden;">
            @foreach($budget->clientPayments->sortByDesc('paid_on') as $payment)
            <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;border-bottom:1px solid #E7E5E4;flex-wrap:wrap;">
                <div style="width:36px;height:36px;border-radius:8px;background:#D1FAE5;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#059669" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:14px;font-weight:600;color:#10B981;">{{ $symbol }}{{ number_format($payment->amount, 2) }}</div>
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:2px;">
                        <span style="font-size:11px;color:#78716C;">{{ $payment->paid_on->format('M d, Y') }}</span>
                        <span style="font-size:11px;color:#A8A29E;">·</span>
                        <span style="font-size:11px;color:#78716C;">{{ ucfirst($payment->payment_method) }}</span>
                        @if($payment->description)
                        <span style="font-size:11px;color:#A8A29E;">·</span>
                        <span style="font-size:11px;color:#78716C;">{{ $payment->description }}</span>
                        @endif
                    </div>
                </div>
                <button wire:click="confirmDeletePayment({{ $payment->id }})"
                    class="krd-btn krd-btn-sm"
                    style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;flex-shrink:0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4h6v2"/></svg>
                </button>
            </div>
            @endforeach

            {{-- Total paid row --}}
            <div style="padding:14px 16px;background:#F0FDF4;display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:13px;font-weight:600;color:#166534;">Total Received</span>
                <span style="font-size:16px;font-weight:700;color:#10B981;">{{ $symbol }}{{ number_format($clientPaid, 2) }}</span>
            </div>
        </div>
        @endif
    </div>

    {{-- Delete Budget Item Modal --}}
    @if($showDeleteModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Delete Budget Item?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">This will permanently remove this item from the budget.</p>
            <div style="display:flex;gap:10px;">
                <button wire:click="deleteItem" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Delete</button>
                <button wire:click="cancelDelete" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Payment Modal --}}
    @if($showDeletePayment)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Delete Payment Record?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">This will remove this payment from the record. The client outstanding balance will be updated.</p>
            <div style="display:flex;gap:10px;">
                <button wire:click="deletePayment" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Delete</button>
                <button wire:click="cancelDeletePayment" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif

</div>

<style>
.krd-budget-summary-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

.krd-budget-form-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
    margin-bottom: 0;
}

@media (min-width: 640px) {
    .krd-budget-form-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 768px) {
    .krd-budget-summary-grid {
        grid-template-columns: repeat(4, 1fr);
    }
    #budget-table-desktop { display: block !important; }
    #budget-cards-mobile  { display: none !important; }
}

@media (max-width: 767px) {
    #budget-table-desktop { display: none !important; }
    #budget-cards-mobile  { display: flex !important; }
}
</style>