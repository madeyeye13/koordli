<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @font-face {
        font-family: 'Satoshi';
        src: url('{{ storage_path('fonts/Satoshi-Regular.ttf') }}') format('truetype');
        font-weight: normal;
    }
    @font-face {
        font-family: 'Satoshi';
        src: url('{{ storage_path('fonts/Satoshi-Bold.ttf') }}') format('truetype');
        font-weight: bold;
    }
    @font-face {
        font-family: 'Spline Sans';
        src: url('{{ storage_path('fonts/SplineSans-Bold.ttf') }}') format('truetype');
        font-weight: bold;
    }

    @page { margin: 50px 0; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Satoshi', sans-serif;
        font-size: 10.5px;
        color: #1C1917;
        line-height: 1.5;
        padding: 0 40px;
    }

    /* Defensive: a hairline transparent border on the first element of the
       flowing content stops DomPDF from collapsing its top margin into the
       @page margin on page 2+, which is what was making continuation pages
       start flush against the top edge. */
    .content-wrap { border-top: 1px solid transparent; }

    /* ===== Header =====
       Background/border-radius live on a plain block div (reliable in DomPDF).
       A REAL <table> inside handles the two-column logo+text layout instead
       of CSS display:table, which DomPDF can shrink-to-fit rather than
       stretch to width:100% — that was the cause of the header not
       reaching full width. */
        /* Full-bleed: negative margin exactly cancels out body's own 40px
       side padding, so the header spans the TRUE physical page edge to
       edge — the only element in the document that does this; everything
       else keeps the normal inset margin from body's padding. */
    .header { background: {{ $primaryColor }}; width: calc(100% + 80px); margin: 0 -40px 24px -40px; overflow: hidden; }
    .header-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .header-logo-cell { width: 74px; padding: 18px 0 18px 40px; vertical-align: middle; }
    .header-logo { width: 40px; height: 40px; border-radius: 8px; }
    .header-text-cell { padding: 18px 40px 18px 8px; vertical-align: middle; }
    .header-company { font-family: 'Spline Sans', sans-serif; font-size: 14px; font-weight: bold; color: #fff; }
    .header-tag { font-size: 8px; color: rgba(255,255,255,0.75); letter-spacing: 0.1em; text-transform: uppercase; margin-top: 2px; }

    .title-block { margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px solid {{ $primaryColor }}; }
    .doc-label { font-size: 9px; font-weight: bold; letter-spacing: 0.14em; text-transform: uppercase; color: {{ $primaryColor }}; margin-bottom: 5px; }
    .event-name { font-family: 'Spline Sans', sans-serif; font-size: 20px; font-weight: bold; color: #1C1917; }
    .event-date { font-size: 10.5px; color: #78716C; margin-top: 4px; }

    /* ===== Progress card =====
       Same pattern: block wrapper for bg/border/radius, real table inside
       for the ring + bar columns. */
    .progress-card { background: #FAFAF9; border: 1px solid #E7E5E4; border-radius: 8px; padding: 16px 18px; margin-bottom: 22px; width: 100%; }
    .progress-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .progress-ring-cell { width: 70px; vertical-align: middle; }
    .progress-ring-num { font-family: 'Spline Sans', sans-serif; font-size: 18px; font-weight: bold; color: {{ $primaryColor }}; }
    .progress-ring-pct { font-size: 8px; color: #A8A29E; text-transform: uppercase; letter-spacing: 0.06em; }
    .progress-detail-cell { vertical-align: middle; padding-left: 16px; }
    .progress-label-row { font-size: 10px; color: #57534E; margin-bottom: 6px; }
    .progress-track { background: #E7E5E4; border-radius: 4px; height: 6px; width: 100%; }
    .progress-fill { background: {{ $accentColor }}; border-radius: 4px; height: 6px; }

    /* Phase group — kept together across a page break where possible */
    .phase-group { page-break-inside: avoid; margin-bottom: 14px; border-top: 1px solid transparent; }
    .phase-title {
        font-family: 'Spline Sans', sans-serif;
        font-size: 10.5px;
        font-weight: bold;
        color: #1C1917;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding-left: 10px;
        border-left: 3px solid {{ $primaryColor }};
        margin-bottom: 10px;
    }

    /* ===== Item row =====
       Real <table> with vertical-align:top on the <td>s. This replaces the
       old float + overflow:hidden hack, which is a known trouble spot in
       DomPDF: float clearing inside overflow:hidden doesn't always compute
       row height correctly, which is what was throwing the checkbox and
       text out of alignment (especially on multi-line items). */
    table.item-row { width: 100%; border-collapse: collapse; page-break-inside: avoid; }
    table.item-row td { padding: 7px 0; border-bottom: 1px solid #F5F5F4; vertical-align: top; }
    .checkbox {
        display: block;
        width: 11px;
        height: 11px;
        margin-top: 2px;
        border: 1.5px solid #A8A29E;
        border-radius: 3px;
    }
    .checkbox-done { background: {{ $accentColor }}; border-color: {{ $accentColor }}; }
    .item-text-cell { padding-left: 4px; }
    .item-title { font-weight: 500; color: #1C1917; line-height: 1.4; }
    .item-title-done { color: #A8A29E; text-decoration: line-through; }
    .item-desc { color: #78716C; font-size: 9px; margin-top: 2px; line-height: 1.5; }

    /* ===== Footer ===== */
    .footer-table { margin-top: 14px; padding-top: 10px; border-top: 1px solid #E7E5E4; width: 100%; border-collapse: collapse; table-layout: fixed; }
    .footer-table td { font-size: 7.5px; color: #A8A29E; vertical-align: top; }
    .footer-right { text-align: right; }
</style>
</head>
<body>

    <div class="header">
        <table class="header-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="header-logo-cell">
                    @if($logoUrl)<img src="{{ $logoUrl }}" class="header-logo" />@endif
                </td>
                <td class="header-text-cell">
                    <div class="header-company">{{ $companyName }}</div>
                    <div class="header-tag">Event Checklist</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="content-wrap">

        <div class="title-block">
            <div class="doc-label">Planning Checklist</div>
            <div class="event-name">{{ $checklist->event->name }}</div>
            <div class="event-date">
                @if($checklist->event->date){{ $checklist->event->date->format('l, F j, Y') }}@endif
                @if($checklist->event->venue) &nbsp;·&nbsp; {{ $checklist->event->venue }} @endif
            </div>
        </div>

        @php
            $overall = $checklist->overallProgress();
            $pct = $overall['total'] > 0 ? round(($overall['completed'] / $overall['total']) * 100) : 0;
        @endphp
        <div class="progress-card">
            <table class="progress-table" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="progress-ring-cell">
                        <div class="progress-ring-num">{{ $pct }}%</div>
                        <div class="progress-ring-pct">Complete</div>
                    </td>
                    <td class="progress-detail-cell">
                        <div class="progress-label-row">{{ $overall['completed'] }} of {{ $overall['total'] }} items completed</div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width:{{ $pct }}%;"></div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        @foreach($itemsByPhase as $phaseLabel => $items)
        <div class="phase-group">
            <div class="phase-title">{{ $phaseLabel }}</div>
            @foreach($items as $item)
            <table class="item-row" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="item-check-cell" width="24">
                        <span class="checkbox {{ $item->is_completed ? 'checkbox-done' : '' }}"></span>
                    </td>
                    <td class="item-text-cell">
                        <span class="item-title {{ $item->is_completed ? 'item-title-done' : '' }}">{{ $item->title }}</span>
                        @if($item->description)<div class="item-desc">{{ $item->description }}</div>@endif
                    </td>
                </tr>
            </table>
            @endforeach
        </div>
        @endforeach

        <table class="footer-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="footer-left">{{ $companyName }} · Generated {{ now()->format('M j, Y g:i A') }}</td>
                <td class="footer-right">Koordli Checklist</td>
            </tr>
        </table>

    </div>

</body>
</html>