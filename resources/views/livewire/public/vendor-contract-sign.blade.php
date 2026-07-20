<div style="min-height:100vh;background:#FAFAF9;padding:40px 20px;">
<div style="max-width:720px;margin:0 auto;">

    @if($notFound)
    <div style="background:#fff;border-radius:10px;padding:48px;text-align:center;">
        <div style="font-size:40px;margin-bottom:12px;">🔍</div>
        <h2 style="font-family:'Fraunces',serif;font-size:22px;color:#1C1917;margin-bottom:8px;">Contract Not Found</h2>
        <p style="color:#78716C;font-size:14px;">This signing link is invalid or no longer available.</p>
    </div>

    @elseif($expired)
    <div style="background:#fff;border-radius:10px;padding:48px;text-align:center;">
        <div style="font-size:40px;margin-bottom:12px;">⏰</div>
        <h2 style="font-family:'Fraunces',serif;font-size:22px;color:#1C1917;margin-bottom:8px;">Signing Link Expired</h2>
        <p style="color:#78716C;font-size:14px;">This contract's signing period has ended. Please contact {{ $contract->vendor->name ?? 'the sender' }} directly for assistance.</p>
    </div>

    @elseif($error)
    <div style="background:#fff;border-radius:10px;padding:48px;text-align:center;">
        <div style="font-size:40px;margin-bottom:12px;">⚠️</div>
        <h2 style="font-family:'Fraunces',serif;font-size:22px;color:#1C1917;margin-bottom:8px;">Cannot Sign</h2>
        <p style="color:#78716C;font-size:14px;">{{ $error }}</p>
    </div>

    @elseif($signed)
    <div style="background:#fff;border-radius:10px;padding:48px;text-align:center;">
        <div style="width:72px;height:72px;border-radius:50%;background:#F0FDF4;border:2px solid #86EFAC;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
        </div>
        <h2 style="font-family:'Fraunces',serif;font-size:26px;color:#1C1917;margin-bottom:8px;">Signed Successfully!</h2>
        <p style="color:#78716C;font-size:14px;margin-bottom:28px;">
            Thank you, {{ $contract->vendor_signature_name }}. Your signature has been recorded and time-stamped.
        </p>
        <button wire:click="downloadSignedCopy" style="background:#1C1917;color:#fff;padding:13px 28px;border-radius:6px;border:none;font-size:14px;font-weight:600;cursor:pointer;font-family:'Spline Sans',sans-serif;">
            Download Your Copy →
        </button>
    </div>

    @else
    {{-- Header --}}
    <div style="text-align:center;margin-bottom:24px;">
        <div style="font-family:'Fraunces',serif;font-size:24px;font-weight:600;color:#1C1917;">{{ $contract->title }}</div>
        <div style="font-size:13px;color:#78716C;margin-top:4px;">Please review and sign below</div>
    </div>

    {{-- Contract preview --}}
    <div style="background:#fff;border-radius:10px;padding:40px;margin-bottom:20px;max-height:500px;overflow-y:auto;font-size:13px;line-height:1.8;color:#1C1917;font-family:'Spline Sans',sans-serif;">
        {!! $contract->content !!}
    </div>

    {{-- Signature form --}}
    <div style="background:#fff;border-radius:10px;padding:32px;">
        <h3 style="font-family:'Fraunces',serif;font-size:18px;color:#1C1917;margin-bottom:16px;">Your Signature</h3>

        @if($error)
        <div style="background:#FEE2E2;border:1px solid #FECACA;border-radius:6px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#DC2626;">
            {{ $error }}
        </div>
        @endif

        <div class="krd-input-group">
            <label class="krd-label-text">Full Name</label>
            <input wire:model="vendor_signature_full_name" type="text" class="krd-input" />
            @error('vendor_signature_full_name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
        </div>

        <div class="krd-input-group" style="margin-bottom:20px;">
            <x-ui.signature-pad wireModel="vendor_signature_data" label="Draw or Type Your Signature" />
            @error('vendor_signature_data') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
        </div>

        <p style="font-size:11px;color:#A8A29E;margin-bottom:16px;line-height:1.6;">
            By signing, you confirm you have read and agree to the terms of this contract. Your IP address and timestamp will be recorded as part of this signature for verification purposes.
        </p>

        <button wire:click="submitSignature" wire:loading.attr="disabled"
            style="width:100%;background:#1C1917;color:#fff;padding:14px;border-radius:6px;border:none;font-size:14px;font-weight:600;cursor:pointer;font-family:'Spline Sans',sans-serif;">
            <span wire:loading.remove wire:target="submitSignature">Sign Contract →</span>
            <span wire:loading wire:target="submitSignature">Submitting...</span>
        </button>
    </div>
    @endif

</div>
</div>