<div>
    <div style="margin-bottom:24px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('tenant.invoices') }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">← Back to Invoices</a>
        </div>
        <div class="krd-label" style="margin-bottom:4px;">Vendor Invoices</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">New Invoice</h2>
    </div>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;" id="create-invoice-grid">
        <div>
            <div class="krd-card" style="padding:24px;margin-bottom:16px;">
                <div class="krd-label" style="margin-bottom:16px;">Invoice Details</div>

                <div class="krd-grid-2" style="gap:12px;">
                    <div class="krd-input-group">
                        <label class="krd-label-text">Vendor <span style="color:#EF4444;">*</span></label>
                        <x-ui.dropdown wire="vendor_id" placeholder="Select vendor"
                            selected="{{ $vendor_id ? ($vendors->firstWhere('id', $vendor_id)?->name ?? 'Select vendor') : 'Select vendor' }}">
                            @foreach($vendors as $v)
                            <div class="krd-dropdown-option {{ $vendor_id == $v->id ? 'selected' : '' }}" x-on:click="select(@js($v->name), {{ $v->id }})">{{ $v->name }}</div>
                            @endforeach
                        </x-ui.dropdown>
                        @error('vendor_id') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>

                    <div class="krd-input-group">
                        <label class="krd-label-text">Event <span style="color:#A8A29E;font-weight:400;">(optional)</span></label>
                        <x-ui.dropdown wire="event_id" placeholder="General invoice (no event)"
                            selected="{{ $event_id ? ($events->firstWhere('id', $event_id)?->name ?? 'General invoice (no event)') : 'General invoice (no event)' }}">
                            @foreach($events as $e)
                            <div class="krd-dropdown-option {{ $event_id == $e->id ? 'selected' : '' }}" x-on:click="select(@js($e->name), {{ $e->id }})">{{ $e->name }}</div>
                            @endforeach
                        </x-ui.dropdown>
                    </div>
                </div>

                @if($vendor_id && $contracts->isNotEmpty())
                <div class="krd-input-group">
                    <label class="krd-label-text">Link to Contract <span style="color:#A8A29E;font-weight:400;">(optional)</span></label>
                    <x-ui.dropdown wire="vendor_contract_id" placeholder="No contract"
                        selected="{{ $vendor_contract_id ? ($contracts->firstWhere('id', $vendor_contract_id)?->title ?? 'No contract') : 'No contract' }}">
                        @foreach($contracts as $c)
                        <div class="krd-dropdown-option {{ $vendor_contract_id == $c->id ? 'selected' : '' }}" x-on:click="select(@js($c->title), {{ $c->id }})">{{ $c->title }}</div>
                        @endforeach
                    </x-ui.dropdown>
                </div>
                @endif

                <div class="krd-input-group">
                    <label class="krd-label-text">Invoice Title <span style="color:#A8A29E;font-weight:400;">(optional)</span></label>
                    <input wire:model="title" type="text" class="krd-input" placeholder="e.g. Deposit Invoice, Final Balance" />
                    <span class="krd-input-hint">Helps distinguish multiple invoices from the same vendor (e.g. Deposit, Progress, Final).</span>
                </div>

                <div class="krd-grid-2" style="gap:12px;">
                    <div class="krd-input-group">
                        <label class="krd-label-text">Issue Date <span style="color:#EF4444;">*</span></label>
                        <input wire:model="issue_date" type="date" class="krd-input" />
                        @error('issue_date') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>
                    <div class="krd-input-group" style="margin-bottom:0;">
                        <label class="krd-label-text">Due Date</label>
                        <input wire:model="due_date" type="date" class="krd-input" />
                        @error('due_date') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="krd-card" style="padding:24px;margin-bottom:16px;">
                <div class="krd-label" style="margin-bottom:16px;">Amount</div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Base Amount <span style="color:#EF4444;">*</span></label>
                    <input wire:model="amount" type="number" step="0.01" min="0" class="krd-input" placeholder="0.00" />
                    <span class="krd-input-hint">The agreed cost for the vendor's service, before tax or discount.</span>
                    @error('amount') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                <div class="krd-grid-2" style="gap:12px;">
                    <div class="krd-input-group">
                        <label class="krd-label-text">Tax <span style="color:#A8A29E;font-weight:400;">(optional)</span></label>
                        <input wire:model="tax_amount" type="number" step="0.01" min="0" class="krd-input" placeholder="0.00" />
                        <span class="krd-input-hint">Any applicable tax, added to the total.</span>
                    </div>
                    <div class="krd-input-group" style="margin-bottom:0;">
                        <label class="krd-label-text">Discount <span style="color:#A8A29E;font-weight:400;">(optional)</span></label>
                        <input wire:model="discount_amount" type="number" step="0.01" min="0" class="krd-input" placeholder="0.00" />
                        <span class="krd-input-hint">Any reduction agreed with the vendor, subtracted from the total.</span>
                    </div>
                </div>

                @if($amount)
                <div style="margin-top:14px;background:#F5F3FF;border:1px solid #DDD6FE;border-radius:6px;padding:12px 16px;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;color:#5B21B6;font-weight:500;">Total Amount</span>
                    <span style="font-size:18px;font-weight:700;color:#7C3AED;">
                        {{ \App\Helpers\CurrencyHelper::forTenant() }}{{ number_format((float)$amount + (float)($tax_amount ?: 0) - (float)($discount_amount ?: 0), 2) }}
                    </span>
                </div>
                @endif
            </div>

            <div class="krd-card" style="padding:24px;margin-bottom:16px;">
                <div class="krd-label" style="margin-bottom:16px;">Additional Details</div>
                <div class="krd-input-group">
                    <label class="krd-label-text">Notes</label>
                    <textarea wire:model="notes" class="krd-input" rows="3" placeholder="Any additional details about this invoice..."></textarea>
                </div>
                <div class="krd-input-group" style="margin-bottom:0;">
                    <label class="krd-label-text">Attachment <span style="color:#A8A29E;font-weight:400;">(optional)</span></label>
                    <input wire:model="attachment" type="file" accept=".pdf,.jpg,.jpeg,.png" class="krd-input" style="padding:8px;" />
                    <span class="krd-input-hint">Upload the invoice document the vendor sent you (PDF, JPG, or PNG).</span>
                    @error('attachment') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>
            </div>

            <button wire:click="save" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                <span wire:loading.remove wire:target="save">Create Invoice</span>
                <span wire:loading wire:target="save">Creating...</span>
            </button>
        </div>

        {{-- Right sidebar --}}
        <div style="display:flex;flex-direction:column;gap:12px;position:sticky;top:80px;" id="create-invoice-tips">
            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;">💡 How invoicing works</div>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    @foreach([
                        'Log invoices your vendors send you — via email, WhatsApp, or paper — to keep everything in one place.',
                        'A vendor can have multiple invoices per event: e.g. Deposit, Progress, and Final Balance.',
                        'Once created, this invoice automatically appears in that event\'s Budget breakdown.',
                        'Record payments as they come in — the balance and status update automatically.',
                        'The invoice becomes "Paid" once payments cover the full total amount.',
                    ] as $tip)
                    <div style="display:flex;gap:8px;align-items:flex-start;">
                        <div style="width:5px;height:5px;border-radius:50%;background:#7C3AED;flex-shrink:0;margin-top:6px;"></div>
                        <p style="font-size:12px;color:#78716C;line-height:1.6;">{{ $tip }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="krd-card" style="padding:20px;background:#F5F3FF;border-color:#DDD6FE;">
                <div style="font-size:13px;font-weight:600;color:#5B21B6;margin-bottom:10px;">🧮 Amount Breakdown</div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <div style="font-size:12px;color:#57534E;line-height:1.6;">
                        <strong style="color:#1C1917;">Base Amount</strong> — the raw agreed cost, before adjustments.
                    </div>
                    <div style="font-size:12px;color:#57534E;line-height:1.6;">
                        <strong style="color:#1C1917;">Tax</strong> — added on top of the base amount.
                    </div>
                    <div style="font-size:12px;color:#57534E;line-height:1.6;">
                        <strong style="color:#1C1917;">Discount</strong> — subtracted from the base amount.
                    </div>
                    <div style="font-size:12px;color:#57534E;line-height:1.6;padding-top:8px;border-top:1px solid #DDD6FE;">
                        <strong style="color:#7C3AED;">Total</strong> = Base + Tax − Discount
                    </div>
                </div>
            </div>

            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:10px;">📋 Invoice Statuses</div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @foreach([
                        ['color' => '#A8A29E', 'label' => 'Draft', 'desc' => 'Created but not sent'],
                        ['color' => '#3B82F6', 'label' => 'Sent', 'desc' => 'Delivered to vendor'],
                        ['color' => '#F59E0B', 'label' => 'Partially Paid', 'desc' => 'Some payment received'],
                        ['color' => '#10B981', 'label' => 'Paid', 'desc' => 'Fully settled'],
                        ['color' => '#EF4444', 'label' => 'Overdue', 'desc' => 'Past due date, unpaid'],
                    ] as $s)
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="width:8px;height:8px;border-radius:50%;background:{{ $s['color'] }};flex-shrink:0;"></div>
                        <div>
                            <span style="font-size:12px;font-weight:500;color:#1C1917;">{{ $s['label'] }}</span>
                            <span style="font-size:11px;color:#78716C;margin-left:4px;">— {{ $s['desc'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width:768px) { #create-invoice-grid { grid-template-columns:1fr !important; } #create-invoice-tips { position:static !important; } }
</style>