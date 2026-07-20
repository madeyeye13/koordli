<div>
    <div style="margin-bottom:24px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('tenant.invoices') }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">← Back to Invoices</a>
        </div>
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <div class="krd-label" style="margin-bottom:4px;">Vendor Invoice</div>
                <h2 class="krd-heading-3" style="color:#1C1917;">{{ $invoice->invoice_number }}</h2>
                @if($invoice->title)<div style="font-size:13px;color:#78716C;margin-top:2px;">{{ $invoice->title }}</div>@endif
                <div style="margin-top:8px;">
                    <span class="krd-badge" style="background:{{ $invoice->statusColor() }}1a;color:{{ $invoice->statusColor() }};">
                        {{ $invoice->statusLabel() }}
                    </span>
                </div>
            </div>
            @if($invoice->status === 'draft')
            <button wire:click="markAsSent" class="krd-btn krd-btn-primary krd-btn-sm">Mark as Sent</button>
            @endif
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;" id="invoice-detail-grid">
        <div>
            {{-- Summary --}}
            <div class="krd-card" style="padding:24px;margin-bottom:16px;">
                <div class="krd-grid-2" style="gap:16px;margin-bottom:20px;">
                    <div>
                        <div class="krd-label" style="margin-bottom:4px;">Vendor</div>
                        <div style="font-size:14px;font-weight:600;color:#1C1917;">{{ $invoice->vendor->name }}</div>
                    </div>
                    <div>
                        <div class="krd-label" style="margin-bottom:4px;">Event</div>
                        <div style="font-size:14px;font-weight:600;color:#1C1917;">{{ $invoice->event?->name ?? '— General —' }}</div>
                    </div>
                    <div>
                        <div class="krd-label" style="margin-bottom:4px;">Issue Date</div>
                        <div style="font-size:14px;color:#1C1917;">{{ $invoice->issue_date->format('d M Y') }}</div>
                    </div>
                    <div>
                        <div class="krd-label" style="margin-bottom:4px;">Due Date</div>
                        <div style="font-size:14px;color:{{ $invoice->isOverdue() ? '#EF4444' : '#1C1917' }};">{{ $invoice->due_date?->format('d M Y') ?? '—' }}</div>
                    </div>
                </div>

                <div class="krd-divider" style="margin:16px 0;"></div>

                <div style="display:flex;flex-direction:column;gap:8px;">
                    <div style="display:flex;justify-content:space-between;font-size:13px;">
                        <span style="color:#78716C;">Amount</span>
                        <span style="color:#1C1917;">{{ \App\Helpers\CurrencyHelper::forTenant() }}{{ number_format($invoice->amount, 2) }}</span>
                    </div>
                    @if($invoice->tax_amount > 0)
                    <div style="display:flex;justify-content:space-between;font-size:13px;">
                        <span style="color:#78716C;">Tax</span>
                        <span style="color:#1C1917;">+ {{ \App\Helpers\CurrencyHelper::forTenant() }}{{ number_format($invoice->tax_amount, 2) }}</span>
                    </div>
                    @endif
                    @if($invoice->discount_amount > 0)
                    <div style="display:flex;justify-content:space-between;font-size:13px;">
                        <span style="color:#78716C;">Discount</span>
                        <span style="color:#EF4444;">- {{ \App\Helpers\CurrencyHelper::forTenant() }}{{ number_format($invoice->discount_amount, 2) }}</span>
                    </div>
                    @endif
                    <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:700;padding-top:8px;border-top:1px solid #E7E5E4;">
                        <span style="color:#1C1917;">Total</span>
                        <span style="color:#7C3AED;">{{ \App\Helpers\CurrencyHelper::forTenant() }}{{ number_format($invoice->total_amount, 2) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;">
                        <span style="color:#78716C;">Paid</span>
                        <span style="color:#10B981;font-weight:600;">{{ \App\Helpers\CurrencyHelper::forTenant() }}{{ number_format($invoice->totalPaid(), 2) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:14px;font-weight:700;">
                        <span style="color:#1C1917;">Balance</span>
                        <span style="color:{{ $invoice->balance() > 0 ? '#EF4444' : '#10B981' }};">{{ \App\Helpers\CurrencyHelper::forTenant() }}{{ number_format($invoice->balance(), 2) }}</span>
                    </div>
                </div>

                @if($invoice->notes)
                <div style="margin-top:16px;padding-top:16px;border-top:1px solid #E7E5E4;">
                    <div class="krd-label" style="margin-bottom:6px;">Notes</div>
                    <p style="font-size:13px;color:#57534E;line-height:1.6;">{{ $invoice->notes }}</p>
                </div>
                @endif

                @if($invoice->attachment_path)
                <div style="margin-top:16px;">
                    <a href="{{ Storage::url($invoice->attachment_path) }}" target="_blank" class="krd-btn krd-btn-secondary krd-btn-sm">📎 View Attachment</a>
                </div>
                @endif
            </div>

            {{-- Payments --}}
            <div class="krd-card" style="padding:24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                    <div class="krd-label" style="margin-bottom:0;">Payment History</div>
                    @if(!$invoice->isPaid() && $invoice->status !== 'cancelled')
                    <button wire:click="showAddPayment" class="krd-btn krd-btn-primary krd-btn-sm">+ Record Payment</button>
                    @endif
                </div>

                @if($showPaymentForm)
                <div style="background:#F5F3FF;border:1px solid #DDD6FE;border-radius:6px;padding:16px;margin-bottom:16px;">
                    <div class="krd-grid-2" style="gap:12px;">
                        <div class="krd-input-group">
                            <label class="krd-label-text">Amount</label>
                            <input wire:model="pay_amount" type="number" step="0.01" class="krd-input" />
                            @error('pay_amount') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                        </div>
                        <div class="krd-input-group">
                            <label class="krd-label-text">Date Paid</label>
                            <input wire:model="pay_date" type="date" class="krd-input" />
                        </div>
                    </div>
                    <div class="krd-input-group">
                        <label class="krd-label-text">Payment Method</label>
                        <x-ui.dropdown wire="pay_method" placeholder="Bank Transfer"
                            selected="{{ match($pay_method) { 'cash' => 'Cash', 'card' => 'Card', 'other' => 'Other', default => 'Bank Transfer' } }}">
                            <div class="krd-dropdown-option {{ $pay_method === 'bank_transfer' ? 'selected' : '' }}" x-on:click="select('Bank Transfer', 'bank_transfer')">Bank Transfer</div>
                            <div class="krd-dropdown-option {{ $pay_method === 'cash' ? 'selected' : '' }}" x-on:click="select('Cash', 'cash')">Cash</div>
                            <div class="krd-dropdown-option {{ $pay_method === 'card' ? 'selected' : '' }}" x-on:click="select('Card', 'card')">Card</div>
                            <div class="krd-dropdown-option {{ $pay_method === 'other' ? 'selected' : '' }}" x-on:click="select('Other', 'other')">Other</div>
                        </x-ui.dropdown>
                    </div>
                    <div class="krd-input-group">
                        <label class="krd-label-text">Reference <span style="color:#A8A29E;font-weight:400;">(optional)</span></label>
                        <input wire:model="pay_reference" type="text" class="krd-input" placeholder="e.g. transaction ID" />
                    </div>
                    <div class="krd-input-group">
                        <label class="krd-label-text">Receipt <span style="color:#A8A29E;font-weight:400;">(optional)</span></label>
                        <input wire:model="pay_receipt" type="file" accept=".pdf,.jpg,.jpeg,.png" class="krd-input" style="padding:8px;" />
                    </div>
                    <div class="krd-input-group" style="margin-bottom:0;">
                        <label class="krd-label-text">Notes</label>
                        <input wire:model="pay_notes" type="text" class="krd-input" />
                    </div>
                    <div style="display:flex;gap:10px;margin-top:16px;">
                        <button wire:click="recordPayment" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-sm">
                            <span wire:loading.remove wire:target="recordPayment">Save Payment</span>
                            <span wire:loading wire:target="recordPayment">Saving...</span>
                        </button>
                        <button wire:click="$set('showPaymentForm', false)" class="krd-btn krd-btn-ghost krd-btn-sm">Cancel</button>
                    </div>
                </div>
                @endif

                @if($invoice->payments->isEmpty())
                <div style="font-size:13px;color:#A8A29E;text-align:center;padding:16px 0;">No payments recorded yet.</div>
                @else
                <div style="display:flex;flex-direction:column;gap:0;">
                    @foreach($invoice->payments as $payment)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid #F5F5F4;gap:12px;flex-wrap:wrap;">
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:14px;font-weight:600;color:#10B981;">{{ \App\Helpers\CurrencyHelper::forTenant() }}{{ number_format($payment->amount, 2) }}</div>
                            <div style="font-size:11px;color:#78716C;margin-top:2px;">
                                {{ $payment->paid_on->format('d M Y') }} · {{ $payment->methodLabel() }}
                                @if($payment->reference) · Ref: {{ $payment->reference }} @endif
                                @if($payment->recordedBy) · by {{ $payment->recordedBy->name }} @endif
                            </div>
                            @if($payment->notes)
                            <div style="font-size:11px;color:#A8A29E;margin-top:2px;">{{ $payment->notes }}</div>
                            @endif
                        </div>
                        <div style="display:flex;gap:6px;flex-shrink:0;">
                            @if($payment->receipt_path)
                            <a href="{{ Storage::url($payment->receipt_path) }}" target="_blank" class="krd-btn krd-btn-ghost krd-btn-sm">Receipt</a>
                            @endif
                            <button wire:click="confirmDeletePayment({{ $payment->id }})"
                                class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Right --}}
        <div style="display:flex;flex-direction:column;gap:12px;position:sticky;top:80px;" id="invoice-detail-actions">
            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:14px;">Details</div>
                <div style="display:flex;flex-direction:column;gap:8px;font-size:12px;">
                    <div style="display:flex;justify-content:space-between;"><span style="color:#A8A29E;">Created by</span><span style="color:#1C1917;font-weight:500;">{{ $invoice->createdBy?->name ?? '—' }}</span></div>
                    <div style="display:flex;justify-content:space-between;"><span style="color:#A8A29E;">Created</span><span style="color:#1C1917;font-weight:500;">{{ $invoice->created_at->format('d M Y') }}</span></div>
                    @if($invoice->contract)
                    <div style="display:flex;justify-content:space-between;"><span style="color:#A8A29E;">Contract</span><a href="{{ route('tenant.contracts.show', $invoice->contract->uuid) }}" wire:navigate style="color:#7C3AED;">{{ Str::limit($invoice->contract->title, 20) }}</a></div>
                    @endif
                </div>
            </div>

            @if(!in_array($invoice->status, ['cancelled', 'paid']))
            <div class="krd-card" style="padding:20px;">
                <button wire:click="confirmCancel" class="krd-btn krd-btn-sm" style="width:100%;background:#FEE2E2;color:#DC2626;border-color:#FECACA;">Cancel Invoice</button>
            </div>
            @endif
        </div>
    </div>

    {{-- Delete Payment Modal --}}
    @if($showDeletePaymentModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Remove Payment?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;">This will reduce the total paid amount and may change the invoice status.</p>
            <div style="display:flex;gap:10px;">
                <button wire:click="deletePayment" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Remove</button>
                <button wire:click="$set('showDeletePaymentModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Cancel Modal --}}
    @if($showCancelModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Cancel Invoice?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;">This will also remove the linked budget item. This cannot be undone.</p>
            <div style="display:flex;gap:10px;">
                <button wire:click="cancelInvoice" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Cancel</button>
                <button wire:click="$set('showCancelModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Back</button>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
@media (max-width:768px) { #invoice-detail-grid { grid-template-columns:1fr !important; } #invoice-detail-actions { position:static !important; } }
</style>