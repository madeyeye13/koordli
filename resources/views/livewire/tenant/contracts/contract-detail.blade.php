<div>
    <div style="margin-bottom:24px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('tenant.contracts') }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">← Back to Contracts</a>
        </div>
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <div class="krd-label" style="margin-bottom:4px;">Vendor Contract</div>
                <h2 class="krd-heading-3" style="color:#1C1917;">{{ $contract->title }}</h2>
                <div style="display:flex;align-items:center;gap:8px;margin-top:6px;flex-wrap:wrap;">
                    <span class="krd-badge" style="background:{{ $contract->statusColor() }}1a;color:{{ $contract->statusColor() }};">
                        {{ $contract->statusLabel() }}
                    </span>
                    <span style="font-size:12px;color:#78716C;">{{ $contract->vendor->name }}</span>
                    @if($contract->event)
                    <span style="font-size:12px;color:#78716C;">· {{ $contract->event->name }}</span>
                    @endif
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button wire:click="downloadPdf" class="krd-btn krd-btn-secondary krd-btn-sm">⬇ Download PDF</button>
                @if(!in_array($contract->status, ['signed', 'cancelled']))
                <button wire:click="startEdit" class="krd-btn krd-btn-secondary krd-btn-sm">Edit</button>
                @endif
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;" id="contract-detail-grid">
        <div>
            {{-- Content --}}
            <div class="krd-card" style="padding:24px;margin-bottom:16px;">
                @if($editing)
                <div class="krd-input-group">
                    <label class="krd-label-text">Title</label>
                    <input wire:model="title" type="text" class="krd-input" />
                </div>
                <div class="krd-grid-2" style="gap:12px;">
                    <div class="krd-input-group">
                        <label class="krd-label-text">Amount</label>
                        <input wire:model="contract_amount" type="number" step="0.01" class="krd-input" />
                    </div>
                    <div class="krd-input-group">
                        <label class="krd-label-text">Expires On</label>
                        <input wire:model="expires_at" type="date" class="krd-input" />
                    </div>
                </div>
                <div class="krd-input-group">
                    <label class="krd-label-text">Payment Schedule</label>
                    <input wire:model="payment_schedule" type="text" class="krd-input" />
                </div>
                <div class="krd-input-group" style="margin-bottom:0;">
                    <label class="krd-label-text">Content</label>
                    <div style="display:flex;gap:4px;padding:8px;background:#F5F5F4;border:1px solid #E7E5E4;border-bottom:none;border-radius:6px 6px 0 0;flex-wrap:wrap;">
                        <button type="button" onclick="document.execCommand('bold')" class="krd-btn krd-btn-ghost krd-btn-sm" style="font-weight:700;">B</button>
                        <button type="button" onclick="document.execCommand('italic')" class="krd-btn krd-btn-ghost krd-btn-sm" style="font-style:italic;">I</button>
                        <button type="button" onclick="document.execCommand('underline')" class="krd-btn krd-btn-ghost krd-btn-sm" style="text-decoration:underline;">U</button>
                    </div>
                    <div contenteditable="true" x-data x-init="$el.innerHTML = @js($content)"
                        x-on:input="$wire.set('content', $el.innerHTML, false)"
                        style="min-height:350px;padding:16px;border:1px solid #E7E5E4;border-radius:0 0 6px 6px;font-size:13px;line-height:1.7;background:#fff;">
                    </div>
                </div>
                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button wire:click="saveEdit" class="krd-btn krd-btn-primary">Save Changes</button>
                    <button wire:click="$set('editing', false)" class="krd-btn krd-btn-ghost">Cancel</button>
                </div>
                @else
                <div style="font-size:13px;line-height:1.8;color:#1C1917;">
                    {!! $contract->content !!}
                </div>
                @endif
            </div>

            {{-- Signed file --}}
            @if($contract->signed_file_path)
            <div class="krd-card" style="padding:20px;margin-bottom:16px;background:#F0FDF4;border-color:#86EFAC;">
                <div style="font-size:13px;font-weight:600;color:#166534;margin-bottom:8px;">✅ Signed Copy on File</div>
                <a href="{{ Storage::url($contract->signed_file_path) }}" target="_blank" class="krd-btn krd-btn-secondary krd-btn-sm">View Signed Document</a>
            </div>
            @endif

            {{-- Status History --}}
            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:14px;">Status History</div>
                <div style="display:flex;flex-direction:column;gap:0;">
                    @foreach($contract->statusHistory as $history)
                    <div style="display:flex;gap:12px;padding:10px 0;border-bottom:1px solid #F5F5F4;">
                        <div style="width:8px;height:8px;border-radius:50%;background:#7C3AED;margin-top:5px;flex-shrink:0;"></div>
                        <div style="flex:1;">
                            <div style="font-size:12px;font-weight:600;color:#1C1917;">{{ $history->label() }}</div>
                            <div style="font-size:11px;color:#A8A29E;">
                                {{ $history->created_at->format('D, d M Y g:i A') }}
                                @if($history->changedBy) · {{ $history->changedBy->name }} @endif
                            </div>
                            @if($history->note)
                            <div style="font-size:11px;color:#78716C;margin-top:2px;">{{ $history->note }}</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Right sidebar --}}
        <div style="display:flex;flex-direction:column;gap:12px;position:sticky;top:80px;" id="contract-detail-actions">

            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:14px;">Actions</div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @if($contract->status === 'draft')
                    <button wire:click="confirmSend" class="krd-btn krd-btn-primary krd-btn-sm">Send to Vendor</button>
                    @endif
                    @if(!$contract->planner_signed_at)
                    <button wire:click="showSignPanel" class="krd-btn krd-btn-secondary krd-btn-sm">✍️ Add Your Signature</button>
                    @else
                    <button wire:click="removePlannerSignature" class="krd-btn krd-btn-sm" style="background:#F5F5F4;color:#78716C;">✓ Signed — Remove</button>
                    @endif
                    @if(in_array($contract->status, ['draft', 'sent']))
                    <button wire:click="showUploadSigned" class="krd-btn krd-btn-secondary krd-btn-sm">Upload Signed Copy</button>
                    @endif
                    @if($contract->isFullySigned())
                    <button wire:click="downloadSignedPdf" class="krd-btn krd-btn-secondary krd-btn-sm">⬇ Download Signed PDF</button>
                    @endif
                    @if(!in_array($contract->status, ['signed', 'cancelled']))
                    <button wire:click="confirmCancel" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">Cancel Contract</button>
                    @endif
                </div>
            </div>

            {{-- Signature Status --}}
            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:12px;">Signatures</div>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="font-size:16px;">{{ $contract->planner_signed_at ? '✅' : '⭕' }}</span>
                        <div>
                            <div style="font-size:12px;font-weight:600;color:#1C1917;">You ({{ auth()->user()->tenant->name }})</div>
                            <div style="font-size:11px;color:#A8A29E;">{{ $contract->planner_signed_at ? $contract->planner_signed_at->format('d M Y g:i A') : 'Not signed yet' }}</div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="font-size:16px;">{{ $contract->vendor_signed_at ? '✅' : '⭕' }}</span>
                        <div>
                            <div style="font-size:12px;font-weight:600;color:#1C1917;">{{ $contract->vendor->name }}</div>
                            <div style="font-size:11px;color:#A8A29E;">{{ $contract->vendor_signed_at ? $contract->vendor_signed_at->format('d M Y g:i A') : 'Not signed yet' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:12px;">Details</div>
                <div style="display:flex;flex-direction:column;gap:8px;font-size:12px;">
                    <div style="display:flex;justify-content:space-between;"><span style="color:#A8A29E;">Created by</span><span style="color:#1C1917;font-weight:500;">{{ $contract->createdBy?->name ?? '—' }}</span></div>
                    <div style="display:flex;justify-content:space-between;"><span style="color:#A8A29E;">Created</span><span style="color:#1C1917;font-weight:500;">{{ $contract->created_at->format('d M Y') }}</span></div>
                    @if($contract->sent_at)
                    <div style="display:flex;justify-content:space-between;"><span style="color:#A8A29E;">Sent</span><span style="color:#1C1917;font-weight:500;">{{ $contract->sent_at->format('d M Y') }}</span></div>
                    @endif
                    @if($contract->signed_at)
                    <div style="display:flex;justify-content:space-between;"><span style="color:#A8A29E;">Signed</span><span style="color:#1C1917;font-weight:500;">{{ $contract->signed_at->format('d M Y') }}</span></div>
                    @endif
                    @if($contract->expires_at)
                    <div style="display:flex;justify-content:space-between;"><span style="color:#A8A29E;">Expires</span><span style="color:{{ $contract->isExpiringSoon() ? '#F59E0B' : '#1C1917' }};font-weight:500;">{{ $contract->expires_at->format('d M Y') }}</span></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Send Modal --}}
    @if($showSendModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:420px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Send Contract</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:20px;line-height:1.6;">
                This will email a PDF of the contract to <strong>{{ $contract->vendor->email }}</strong>.
            </p>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <button wire:click="sendContract" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                    <span wire:loading.remove wire:target="sendContract">📧 Send Email with PDF</span>
                    <span wire:loading wire:target="sendContract">Sending...</span>
                </button>
                <button wire:click="markSentManually" class="krd-btn krd-btn-secondary">Mark as Sent (delivered outside Koordli)</button>
                <button wire:click="$set('showSendModal', false)" class="krd-btn krd-btn-ghost">Cancel</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Upload Signed Modal --}}
    @if($showSignedUpload)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:420px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:16px;">Upload Signed Contract</h3>
            <div class="krd-input-group" style="margin-bottom:0;">
                <label class="krd-label-text">File (PDF, JPG, or PNG)</label>
                <input wire:model="signedFile" type="file" accept=".pdf,.jpg,.jpeg,.png" class="krd-input" style="padding:8px;" />
                @error('signedFile') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>
            <div style="display:flex;gap:10px;margin-top:20px;">
                <button wire:click="uploadSigned" wire:loading.attr="disabled" class="krd-btn krd-btn-primary" style="flex:1;">
                    <span wire:loading.remove wire:target="uploadSigned">Upload</span>
                    <span wire:loading wire:target="uploadSigned">Uploading...</span>
                </button>
                <button wire:click="$set('showSignedUpload', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif
{{-- Sign Modal --}}
    @if($showSignModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;overflow-y:auto;">
        <div style="background:#fff;border-radius:8px;padding:28px;max-width:460px;width:100%;max-height:90vh;overflow-y:auto;">
            <h3 style="font-size:17px;font-weight:600;color:#1C1917;margin-bottom:16px;">Add Your Signature</h3>

            <div class="krd-input-group">
                <label class="krd-label-text">Full Name</label>
                <input wire:model="planner_signature_full_name" type="text" class="krd-input" />
                @error('planner_signature_full_name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="krd-input-group" style="margin-bottom:0;">
                <x-ui.signature-pad wireModel="planner_signature_data" label="Draw or Type Your Signature" />
                @error('planner_signature_data') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            <div style="display:flex;gap:10px;margin-top:20px;">
                <button wire:click="savePlannerSignature" wire:loading.attr="disabled" class="krd-btn krd-btn-primary" style="flex:1;">
                    <span wire:loading.remove wire:target="savePlannerSignature">Save Signature</span>
                    <span wire:loading wire:target="savePlannerSignature">Saving...</span>
                </button>
                <button wire:click="$set('showSignModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Cancel Modal --}}
    @if($showCancelModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Cancel Contract?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;">This will mark the contract as cancelled. This cannot be undone.</p>
            <div style="display:flex;gap:10px;">
                <button wire:click="cancelContract" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Cancel</button>
                <button wire:click="$set('showCancelModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Back</button>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
@media (max-width:768px) { #contract-detail-grid { grid-template-columns:1fr !important; } #contract-detail-actions { position:static !important; } }
</style>