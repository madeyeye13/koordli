<div x-data="{ activeTab: 'breakdown', showAddForm: @entangle('showAddForm').live, showDeleteModal: false, showDeletePayment: false, showFeeForm: false }">

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
                <button type="button" x-on:click="showAddForm = !showAddForm" class="krd-btn krd-btn-primary krd-btn-sm">
                    <span x-text="showAddForm ? '✕ Cancel' : '+ Add Item'"></span>
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
        $netPosition = $budget ? $budget->plannerNetPosition() : 0;
        $feeRevenue  = $budget ? $budget->feeRevenueCollected() : 0;
        $borneCost   = $budget ? $budget->plannerBorneCost() : $estimated;
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

            @if($budget && !$budget->fee_amount)
            <div class="krd-card" style="padding:14px;border-left:3px solid #A8A29E;">
                <div class="krd-label" style="margin-bottom:5px;">Your Net Position</div>
                <div style="font-size:13px;font-weight:600;color:#78716C;line-height:1.4;">Set your fee below to see this</div>
            </div>
            @else
            <div class="krd-card" style="padding:14px;border-left:3px solid {{ $netPosition >= 0 ? '#10B981' : '#EF4444' }};">
                <div class="krd-label" style="margin-bottom:5px;">Your Net Position</div>
                <div style="font-size:18px;font-weight:700;color:{{ $netPosition >= 0 ? '#10B981' : '#EF4444' }};">
                    {{ $netPosition < 0 ? '-' : '' }}{{ $symbol }}{{ number_format(abs($netPosition), 2) }}
                </div>
                <div style="font-size:10px;color:#A8A29E;margin-top:2px;">
                    {{ $symbol }}{{ number_format($feeRevenue, 2) }} fee collected − {{ $symbol }}{{ number_format($borneCost, 2) }} your costs
                </div>
            </div>
            @endif
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

    {{-- Professional Fee --}}
    <div class="krd-card" style="padding:16px;margin-bottom:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:{{ $budget && $budget->fee_amount ? '12px' : '0' }};">
            <div style="font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#A8A29E;">Your Professional Fee</div>
            <button x-on:click="showFeeForm = true; $wire.openFeeForm()" class="krd-btn krd-btn-secondary krd-btn-sm">
                {{ $budget && $budget->fee_amount ? 'Edit Fee' : '+ Set Fee' }}
            </button>
        </div>
        @if($budget && $budget->fee_amount)
        <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:10px;">
            <div>
                <div style="font-size:10px;color:#A8A29E;margin-bottom:2px;">Fee Amount</div>
                <div style="font-size:15px;font-weight:700;color:#7C3AED;">{{ $symbol }}{{ number_format($budget->fee_amount, 2) }}</div>
                @if($budget->fee_note)<div style="font-size:10px;color:#A8A29E;">{{ $budget->fee_note }}</div>@endif
            </div>
            <div>
                <div style="font-size:10px;color:#A8A29E;margin-bottom:2px;">Collected</div>
                <div style="font-size:15px;font-weight:700;color:#10B981;">{{ $symbol }}{{ number_format($budget->feeRevenueCollected(), 2) }}</div>
            </div>
            <div>
                <div style="font-size:10px;color:#A8A29E;margin-bottom:2px;">Outstanding</div>
                <div style="font-size:15px;font-weight:700;color:{{ $budget->feeOutstanding() > 0 ? '#EF4444' : '#10B981' }};">{{ $symbol }}{{ number_format($budget->feeOutstanding(), 2) }}</div>
            </div>
            <div>
                <div style="font-size:10px;color:#A8A29E;margin-bottom:2px;">{{ $budget->fee_source === 'from_budget' ? 'Available for Costs' : "Client's Total" }}</div>
                <div style="font-size:15px;font-weight:700;color:#7C3AED;">{{ $symbol }}{{ number_format($budget->fee_source === 'from_budget' ? $budget->availableForEventCosts() : $budget->totalClientCommitment(), 2) }}</div>
            </div>
        </div>
        @endif
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
        <div x-show="showAddForm" x-cloak class="krd-card" style="padding:20px;margin-bottom:16px;border:2px solid #7C3AED;">
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
            <div class="krd-input-group">
                <label class="krd-label-text">Who's Responsible For This?</label>
                @php
                    $newResponsiblePartyLabel = match($newResponsibleParty) {
                        'client' => 'Client Pays Directly',
                        'planner_pocket' => 'Your Own Money',
                        default => "From Client's Budget",
                    };
                @endphp
                <x-ui.dropdown wire="newResponsibleParty"
                    selected="{{ $newResponsiblePartyLabel }}">
                    @foreach(['client_budget' => "From Client's Budget", 'client' => 'Client Pays Directly', 'planner_pocket' => 'Your Own Money'] as $val => $label)
                    <div class="krd-dropdown-option {{ $newResponsibleParty === $val ? 'selected' : '' }}"
                        x-on:click="select('{{ $label }}', '{{ $val }}')">{{ $label }}</div>
                    @endforeach
                </x-ui.dropdown>
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
                <button x-on:click="showAddForm = false" type="button" class="krd-btn krd-btn-ghost">Cancel</button>
            </div>
        </div>

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
                            <th>Responsible</th>
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
                            <td></td>
                            <td style="min-width:130px;">
                                @php
                                    $editResponsiblePartyLabel = match($editResponsibleParty) {
                                        'client' => 'Client Direct',
                                        'planner_pocket' => 'Your Own Money',
                                        default => "Client's Budget",
                                    };
                                @endphp
                                <x-ui.dropdown wire="editResponsibleParty"
                                    selected="{{ $editResponsiblePartyLabel }}">
                                    @foreach(['client_budget' => "From Client's Budget", 'client' => 'Client Pays Directly', 'planner_pocket' => 'Your Own Money'] as $val => $label)
                                    <div class="krd-dropdown-option {{ $editResponsibleParty === $val ? 'selected' : '' }}"
                                        x-on:click="select('{{ $label }}', '{{ $val }}')">{{ $label }}</div>
                                    @endforeach
                                </x-ui.dropdown>
                            </td>
                            <td></td>
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
                                <span class="krd-badge {{ match($item->effectiveResponsibleParty()) { 'client' => 'krd-badge-blue', 'planner_pocket' => 'krd-badge-amber', default => 'krd-badge-stone' } }}" style="font-size:10px;">{{ $item->responsiblePartyLabel() }}</span>
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
                                    <button x-on:click="showDeleteModal = true" wire:click="confirmDelete({{ $item->id }})" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">
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
                            <td colspan="3"></td>
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
                    <div style="display:flex;gap:4px;">
                        <span class="krd-badge {{ match($item->effectiveResponsibleParty()) { 'client' => 'krd-badge-blue', 'planner_pocket' => 'krd-badge-amber', default => 'krd-badge-stone' } }}" style="font-size:9px;">{{ $item->responsiblePartyLabel() }}</span>
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
                                    <button x-on:click="showDeleteModal = true" wire:click="confirmDelete({{ $item->id }})" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">Delete</button>
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
            <div class="krd-input-group">
                <label class="krd-label-text">What Is This Payment For?</label>
                <x-ui.dropdown wire="paymentPurpose" placeholder="General"
                    selected="{{ match($paymentPurpose) { 'planner_fee' => 'Your Professional Fee', 'event_costs' => 'Event Costs', default => 'General' } }}">
                    @foreach(['unspecified' => 'General', 'event_costs' => 'Event Costs', 'planner_fee' => 'Your Professional Fee'] as $val => $label)
                    <div class="krd-dropdown-option {{ $paymentPurpose === $val ? 'selected' : '' }}"
                        x-on:click="select('{{ $label }}', '{{ $val }}')">{{ $label }}</div>
                    @endforeach
                </x-ui.dropdown>
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
                        @if($payment->effectivePurpose() !== 'unspecified')
                        <span style="font-size:11px;color:#A8A29E;">·</span>
                        <span class="krd-badge krd-badge-violet" style="font-size:9px;">{{ $payment->purposeLabel() }}</span>
                        @endif
                        @if($payment->description)
                        <span style="font-size:11px;color:#A8A29E;">·</span>
                        <span style="font-size:11px;color:#78716C;">{{ $payment->description }}</span>
                        @endif
                    </div>
                </div>
                <button x-on:click="showDeletePayment = true" wire:click="confirmDeletePayment({{ $payment->id }})"
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

    {{-- Professional Fee Modal --}}
    <template x-teleport="body">
    <div x-cloak x-bind:style="showFeeForm ? 'position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;' : 'display:none;'">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:420px;width:100%;box-sizing:border-box;margin:0 auto;align-self:center;"
            x-data="{
                type: '{{ $feeType }}',
                source: '{{ $feeSource }}',
                helper: 0,
                percent: '',
                feeAmountLocal: {{ $feeAmount !== '' ? $feeAmount : 0 }},
                agreedBudget: {{ $agreed }},
                vendorActual: {{ $actual }},
                calc() {
                    const budget = {{ $agreed }};
                    const guests = {{ $this->confirmedGuestCount() }};
                    const days = 1;
                    if (this.type === 'percentage_of_budget') this.helper = budget;
                    if (this.type === 'percentage_of_vendor_costs') this.helper = this.vendorActual;
                    if (this.type === 'per_guest') this.helper = guests;
                    if (this.type === 'per_day') this.helper = days;
                },
                percentResult() {
                    const pct = parseFloat(this.percent) || 0;
                    return (this.helper * pct) / 100;
                },
                usePercentResult() {
                    const amount = this.percentResult();
                    this.feeAmountLocal = amount;
                    $wire.set('feeAmount', amount.toFixed(2));
                }
            }">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:4px;">Your Professional Fee</h3>
            <p style="font-size:11.5px;color:#A8A29E;margin-bottom:16px;line-height:1.5;">One confirmed amount — this never changes automatically, even if the budget changes later.</p>

            <div class="krd-input-group">
                <label class="krd-label-text">How is this fee structured?</label>
                <x-ui.dropdown wire="feeType"
                    selected="{{ match($feeType) { 'percentage_of_budget' => 'Percentage of Budget', 'percentage_of_vendor_costs' => 'Percentage of Vendor Costs', 'per_guest' => 'Per Guest', 'per_day' => 'Per Day', 'package' => 'Package', 'custom' => 'Custom', default => 'Fixed Amount' } }}">
                    @foreach(['fixed' => 'Fixed Amount', 'percentage_of_budget' => 'Percentage of Budget', 'percentage_of_vendor_costs' => 'Percentage of Vendor Costs', 'per_guest' => 'Per Guest', 'per_day' => 'Per Day', 'package' => 'Package', 'custom' => 'Custom'] as $val => $label)
                    <div class="krd-dropdown-option {{ $feeType === $val ? 'selected' : '' }}"
                        x-on:click="select('{{ $label }}', '{{ $val }}'); type = '{{ $val }}'; calc()">{{ $label }}</div>
                    @endforeach
                </x-ui.dropdown>
            </div>

            {{-- Percentage types get a REAL percentage input + live result --}}
            <template x-if="['percentage_of_budget', 'percentage_of_vendor_costs'].includes(type)">
                <div style="background:#F5F3FF;border-radius:6px;padding:12px;margin-bottom:14px;">
                    <label class="krd-label-text" x-text="type === 'percentage_of_budget' ? 'What percentage of the agreed budget?' : 'What percentage of vendor costs?'"></label>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <input x-model="percent" type="number" step="0.1" min="0" max="100" class="krd-input" placeholder="15" style="max-width:100px;">
                        <span style="font-size:13px;color:#57534E;">%</span>
                        <span style="font-size:12px;color:#7C3AED;flex:1;">
                            = {{ $symbol }}<span x-text="percentResult().toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})"></span>
                        </span>
                    </div>
                    <button type="button" x-on:click="usePercentResult()" class="krd-btn krd-btn-secondary krd-btn-sm" style="margin-top:8px;width:100%;">
                        Use This Amount
                    </button>
                </div>
            </template>

            {{-- Non-percentage helper types keep their simple reference hint --}}
            <template x-if="['per_guest', 'per_day'].includes(type)">
                <div style="background:#F5F3FF;border-radius:6px;padding:10px 12px;margin-bottom:14px;font-size:11.5px;color:#7C3AED;">
                    <span x-show="type === 'per_guest'">Confirmed guests: <span x-text="helper"></span> — enter your rate × guests below.</span>
                    <span x-show="type === 'per_day'">Enter your day-rate × number of days below.</span>
                </div>
            </template>

            <div class="krd-input-group">
                <label class="krd-label-text">Confirmed Fee Amount <span style="color:#EF4444;">*</span></label>
                <input wire:model="feeAmount" x-on:input="feeAmountLocal = parseFloat($event.target.value) || 0" type="number" step="0.01" min="0" class="krd-input @error('feeAmount') krd-input-error @enderror" placeholder="0.00" />
                @error('feeAmount') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            {{-- Fee source — the on-top vs. from-budget choice --}}
            <div class="krd-input-group">
                <label class="krd-label-text">How does this fee relate to the budget?</label>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <label style="display:flex;align-items:flex-start;gap:10px;padding:10px 12px;border:1.5px solid #E7E5E4;border-radius:6px;cursor:pointer;"
                        x-bind:style="source === 'on_top' ? 'display:flex;align-items:flex-start;gap:10px;padding:10px 12px;border:1.5px solid #7C3AED;background:#F5F3FF;border-radius:6px;cursor:pointer;' : 'display:flex;align-items:flex-start;gap:10px;padding:10px 12px;border:1.5px solid #E7E5E4;border-radius:6px;cursor:pointer;'">
                        <input type="radio" x-model="source" value="on_top" style="margin-top:2px;accent-color:#7C3AED;">
                        <span>
                            <span style="display:block;font-size:12.5px;font-weight:600;color:#1C1917;">Added on top of the budget</span>
                            <span style="display:block;font-size:11px;color:#78716C;margin-top:2px;">The client pays {{ $symbol }}{{ number_format($agreed, 2) }} for the event, plus your fee separately.</span>
                        </span>
                    </label>
                    <label style="display:flex;align-items:flex-start;gap:10px;padding:10px 12px;border:1.5px solid #E7E5E4;border-radius:6px;cursor:pointer;"
                        x-bind:style="source === 'from_budget' ? 'display:flex;align-items:flex-start;gap:10px;padding:10px 12px;border:1.5px solid #7C3AED;background:#F5F3FF;border-radius:6px;cursor:pointer;' : 'display:flex;align-items:flex-start;gap:10px;padding:10px 12px;border:1.5px solid #E7E5E4;border-radius:6px;cursor:pointer;'">
                        <input type="radio" x-model="source" value="from_budget" style="margin-top:2px;accent-color:#7C3AED;">
                        <span>
                            <span style="display:block;font-size:12.5px;font-weight:600;color:#1C1917;">Taken out of the budget</span>
                            <span style="display:block;font-size:11px;color:#78716C;margin-top:2px;">Your fee comes out of the {{ $symbol }}{{ number_format($agreed, 2) }} — it's included in what the client already agreed to pay.</span>
                        </span>
                    </label>
                </div>
            </div>

            {{-- Live summary — updates as you type, before saving --}}
            <div style="background:#FAFAF9;border:1px solid #E7E5E4;border-radius:6px;padding:10px 12px;margin-bottom:14px;font-size:12px;">
                <template x-if="source === 'on_top'">
                    <div style="color:#1C1917;">Client's total: <strong>{{ $symbol }}<span x-text="(agreedBudget + feeAmountLocal).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})"></span></strong></div>
                </template>
                <template x-if="source === 'from_budget'">
                    <div style="color:#1C1917;">Available for event costs: <strong>{{ $symbol }}<span x-text="Math.max(0, agreedBudget - feeAmountLocal).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})"></span></strong></div>
                </template>
            </div>

            <div class="krd-input-group" style="margin-bottom:0;">
                <label class="krd-label-text">Note (optional)</label>
                <input wire:model="feeNote" type="text" class="krd-input" placeholder="e.g. 15% of budget, Gold package..." />
            </div>

            <div style="display:flex;gap:10px;margin-top:16px;">
                <button type="button" wire:loading.attr="disabled" x-on:click="$wire.set('feeSource', source).then(() => $wire.saveFee())" class="krd-btn krd-btn-primary" style="flex:1;">
                    <span wire:loading.remove wire:target="saveFee">Save Fee</span>
                    <span wire:loading wire:target="saveFee">Saving...</span>
                </button>
                <button type="button" x-on:click="showFeeForm = false" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    </template>

    {{-- Delete Budget Item Modal --}}
    <template x-teleport="body">
    <div x-show="showDeleteModal" x-cloak style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Delete Budget Item?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">This will permanently remove this item from the budget.</p>
            <div style="display:flex;gap:10px;">
                <button wire:click="deleteItem" x-on:click="showDeleteModal = false" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Delete</button>
                <button type="button" x-on:click="showDeleteModal = false; $wire.cancelDelete()" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    </template>

    {{-- Delete Payment Modal --}}
    <template x-teleport="body">
    <div x-show="showDeletePayment" x-cloak style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Delete Payment Record?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">This will remove this payment from the record. The client outstanding balance will be updated.</p>
            <div style="display:flex;gap:10px;">
                <button wire:click="deletePayment" x-on:click="showDeletePayment = false" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Delete</button>
                <button type="button" x-on:click="showDeletePayment = false; $wire.cancelDeletePayment()" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    </template>

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