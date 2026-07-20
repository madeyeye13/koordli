<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @font-face {
        font-family: 'Satoshi';
        src: url('{{ storage_path('fonts/Satoshi-Regular.ttf') }}') format('truetype');
        font-weight: normal;
        font-style: normal;
    }
    @font-face {
        font-family: 'Satoshi';
        src: url('{{ storage_path('fonts/Satoshi-Bold.ttf') }}') format('truetype');
        font-weight: bold;
        font-style: normal;
    }
    @font-face {
        font-family: 'Satoshi';
        src: url('{{ storage_path('fonts/Satoshi-Italic.ttf') }}') format('truetype');
        font-weight: normal;
        font-style: italic;
    }
    @font-face {
        font-family: 'Satoshi';
        src: url('{{ storage_path('fonts/Satoshi-BoldItalic.ttf') }}') format('truetype');
        font-weight: bold;
        font-style: italic;
    }
    @font-face {
        font-family: 'Spline Sans';
        src: url('{{ storage_path('fonts/SplineSans-Regular.ttf') }}') format('truetype');
        font-weight: normal;
    }
    @font-face {
        font-family: 'Spline Sans';
        src: url('{{ storage_path('fonts/SplineSans-Medium.ttf') }}') format('truetype');
        font-weight: 500;
    }
    @font-face {
        font-family: 'Spline Sans';
        src: url('{{ storage_path('fonts/SplineSans-Bold.ttf') }}') format('truetype');
        font-weight: bold;
    }

    @page {
        margin: 0;
    }
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    body {
        font-family: 'Satoshi', sans-serif;
        font-size: 11px;
        color: #1C1917;
        line-height: 1.7;
    }

    /* Header band */
    .header {
        background: {{ $primaryColor }};
        padding: 28px 48px;
        display: table;
        width: 100%;
    }
    .header-logo-cell {
        display: table-cell;
        vertical-align: middle;
        width: 60px;
    }
    .header-logo {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        object-fit: cover;
    }
    .header-text-cell {
        display: table-cell;
        vertical-align: middle;
        padding-left: 16px;
    }
    .header-company {
        font-family: 'Spline Sans', sans-serif;
        font-size: 16px;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: -0.01em;
    }
    .header-tag {
        font-size: 9px;
        color: rgba(255,255,255,0.7);
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-top: 2px;
    }
    .header-accent-bar {
        height: 4px;
        background: {{ $accentColor }};
        width: 100%;
    }

    /* Body */
    .content-wrap {
        padding: 40px 48px 60px 48px;
    }
    .doc-title {
        font-family: 'Spline Sans', sans-serif;
        font-size: 22px;
        font-weight: 700;
        color: #1C1917;
        letter-spacing: -0.01em;
        margin-bottom: 4px;
    }
    .doc-subtitle {
        font-size: 10px;
        color: #A8A29E;
        margin-bottom: 24px;
        padding-bottom: 20px;
        border-bottom: 2px solid {{ $primaryColor }};
    }

    .contract-body h2 {
        font-family: 'Spline Sans', sans-serif;
        font-size: 15px;
        font-weight: 700;
        color: {{ $primaryColor }};
        margin-top: 22px;
        margin-bottom: 8px;
    }
    .contract-body h3 {
        font-family: 'Spline Sans', sans-serif;
        font-size: 12.5px;
        font-weight: 700;
        color: #1C1917;
        margin-top: 16px;
        margin-bottom: 6px;
    }
    .contract-body p {
        margin-bottom: 10px;
        color: #292524;
    }
    .contract-body ul, .contract-body ol {
        margin: 8px 0 12px 20px;
    }
    .contract-body li {
        margin-bottom: 4px;
    }
    .contract-body strong {
        color: #1C1917;
    }

    /* Meta info box */
    .meta-box {
        background: #F5F5F4;
        border-left: 3px solid {{ $primaryColor }};
        border-radius: 4px;
        padding: 14px 18px;
        margin-bottom: 24px;
    }
    .meta-row {
        display: table;
        width: 100%;
        padding: 4px 0;
    }
    .meta-key {
        display: table-cell;
        width: 40%;
        font-size: 10px;
        color: #78716C;
    }
    .meta-val {
        display: table-cell;
        font-size: 10.5px;
        font-weight: 600;
        color: #1C1917;
    }

    /* Footer */
    .footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 16px 48px;
        border-top: 1px solid #E7E5E4;
        font-size: 8.5px;
        color: #A8A29E;
        display: table;
        width: 100%;
    }
    .footer-left {
        display: table-cell;
        text-align: left;
    }
    .footer-right {
        display: table-cell;
        text-align: right;
    }
</style>
</head>
<body>

    <div class="header">
        <div class="header-logo-cell">
            @if($logoUrl)
            <img src="{{ $logoUrl }}" class="header-logo" />
            @endif
        </div>
        <div class="header-text-cell">
            <div class="header-company">{{ $companyName }}</div>
            <div class="header-tag">Vendor Service Agreement</div>
        </div>
    </div>
    <div class="header-accent-bar"></div>

    <div class="content-wrap">
        <div class="doc-title">{{ $contract->title }}</div>
        <div class="doc-subtitle">
            Contract Reference: {{ strtoupper(substr($contract->uuid, 0, 8)) }} &nbsp;·&nbsp; Generated {{ now()->format('F j, Y') }}
        </div>

        <div class="meta-box">
            <div class="meta-row">
                <div class="meta-key">Vendor</div>
                <div class="meta-val">{{ $contract->vendor->name }}</div>
            </div>
            @if($contract->event)
            <div class="meta-row">
                <div class="meta-key">Event</div>
                <div class="meta-val">{{ $contract->event->name }} @if($contract->event->date) — {{ $contract->event->date->format('F j, Y') }} @endif</div>
            </div>
            @endif
            @if($contract->contract_amount)
            <div class="meta-row">
                <div class="meta-key">Contract Amount</div>
                <div class="meta-val">{{ \App\Helpers\CurrencyHelper::formatForPdf($contract->contract_amount, $currencyCode) }}</div>
            </div>
            @endif
            @if($contract->payment_schedule)
            <div class="meta-row">
                <div class="meta-key">Payment Schedule</div>
                <div class="meta-val">{{ $contract->payment_schedule }}</div>
            </div>
            @endif
            @if($contract->expires_at)
            <div class="meta-row">
                <div class="meta-key">Valid Until</div>
                <div class="meta-val">{{ $contract->expires_at->format('F j, Y') }}</div>
            </div>
            @endif
        </div>

       <div class="contract-body">
            {!! $contract->content !!}
        </div>

        {{-- Signature Block --}}
        <div style="margin-top:40px;padding-top:24px;border-top:2px solid #E7E5E4;">
            <div style="display:table;width:100%;">
                <div style="display:table-cell;width:48%;vertical-align:top;padding-right:2%;">
                    <div style="font-size:9px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#A8A29E;margin-bottom:10px;">
                        {{ $companyName }}
                    </div>
                    <div style="height:60px;border-bottom:1.5px solid #1C1917;margin-bottom:6px;display:flex;align-items:flex-end;">
                        @if($contract->planner_signature_data)
                            @if($contract->planner_signature_type === 'draw')
                            <img src="{{ $contract->planner_signature_data }}" style="max-height:55px;max-width:100%;" />
                            @else
                            <div style="font-family:'Satoshi',sans-serif;font-style:italic;font-size:22px;color:#1C1917;">{{ $contract->planner_signature_data }}</div>
                            @endif
                        @endif
                    </div>
                    <div style="font-size:10px;font-weight:600;color:#1C1917;">{{ $contract->planner_signature_name ?? 'Not signed yet' }}</div>
                    @if($contract->planner_signed_at)
                    <div style="font-size:8.5px;color:#A8A29E;margin-top:2px;">Signed {{ $contract->planner_signed_at->format('M j, Y g:i A') }}</div>
                    @endif
                </div>
                <div style="display:table-cell;width:48%;vertical-align:top;padding-left:2%;">
                    <div style="font-size:9px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#A8A29E;margin-bottom:10px;">
                        {{ $contract->vendor->name }}
                    </div>
                    <div style="height:60px;border-bottom:1.5px solid #1C1917;margin-bottom:6px;display:flex;align-items:flex-end;">
                        @if($contract->vendor_signature_data)
                            @if($contract->vendor_signature_type === 'draw')
                            <img src="{{ $contract->vendor_signature_data }}" style="max-height:55px;max-width:100%;" />
                            @else
                            <div style="font-family:'Satoshi',sans-serif;font-style:italic;font-size:22px;color:#1C1917;">{{ $contract->vendor_signature_data }}</div>
                            @endif
                        @endif
                    </div>
                    <div style="font-size:10px;font-weight:600;color:#1C1917;">{{ $contract->vendor_signature_name ?? 'Not signed yet' }}</div>
                    @if($contract->vendor_signed_at)
                    <div style="font-size:8.5px;color:#A8A29E;margin-top:2px;">Signed {{ $contract->vendor_signed_at->format('M j, Y g:i A') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="footer-left">{{ $companyName }} · Generated via Koordli</div>
        <div class="footer-right">Contract Ref: {{ strtoupper(substr($contract->uuid, 0, 8)) }}</div>
    </div>

</body>
</html>