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

    @page { margin: 0; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Satoshi', sans-serif;
        font-size: 10.5px;
        color: #1C1917;
        line-height: 1.6;
    }

    .header {
        background: {{ $primaryColor }};
        padding: 22px 40px;
        display: table;
        width: 100%;
    }
    .header-logo-cell { display: table-cell; vertical-align: middle; width: 50px; }
    .header-logo { width: 40px; height: 40px; border-radius: 6px; object-fit: cover; }
    .header-text-cell { display: table-cell; vertical-align: middle; padding-left: 14px; }
    .header-company { font-family: 'Spline Sans', sans-serif; font-size: 14px; font-weight: bold; color: #fff; }
    .header-tag { font-size: 8px; color: rgba(255,255,255,0.7); letter-spacing: 0.08em; text-transform: uppercase; margin-top: 2px; }
    .accent-bar { height: 3px; background: {{ $accentColor }}; width: 100%; }

    .content { padding: 28px 40px 50px; }

    .title-block { margin-bottom: 18px; padding-bottom: 14px; border-bottom: 2px solid {{ $primaryColor }}; }
    .call-sheet-label { font-size: 9px; font-weight: bold; letter-spacing: 0.12em; text-transform: uppercase; color: {{ $primaryColor }}; margin-bottom: 4px; }
    .event-name { font-family: 'Spline Sans', sans-serif; font-size: 19px; font-weight: bold; color: #1C1917; }
    .event-date { font-size: 10.5px; color: #78716C; margin-top: 3px; }

    .section-title { font-family: 'Spline Sans', sans-serif; font-size: 11px; font-weight: bold; color: #1C1917; margin: 16px 0 8px; padding-bottom: 4px; border-bottom: 1px solid #E7E5E4; }

    .locations-grid { display: table; width: 100%; margin-bottom: 4px; }
    .location-box { display: table-cell; width: 33%; padding: 8px 10px; background: #F5F5F4; border-radius: 4px; }
    .location-name { font-size: 10px; font-weight: bold; color: #1C1917; }
    .location-date { font-size: 9px; color: {{ $primaryColor }}; margin-top: 2px; }
    .location-address { font-size: 8.5px; color: #78716C; margin-top: 2px; }

    table.schedule { width: 100%; border-collapse: collapse; }
    table.schedule th { text-align: left; font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.04em; color: #78716C; padding: 6px 8px; border-bottom: 1.5px solid #1C1917; }
    table.schedule td { font-size: 9.5px; padding: 7px 8px; border-bottom: 1px solid #F0F0F0; vertical-align: top; }
    .status-dot { display: inline-block; width: 6px; height: 6px; border-radius: 50%; margin-right: 4px; }

    .crew-grid { display: table; width: 100%; }
    .crew-col { display: table-cell; width: 50%; vertical-align: top; padding-right: 20px; }
    .crew-item { font-size: 9.5px; padding: 4px 0; border-bottom: 1px solid #F5F5F4; }

    .notes-box { background: #F5F5F4; border-left: 3px solid {{ $primaryColor }}; padding: 10px 14px; margin-top: 12px; font-size: 9.5px; color: #57534E; border-radius: 4px; }

    .footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 12px 40px; border-top: 1px solid #E7E5E4; font-size: 7.5px; color: #A8A29E; display: table; width: 100%; }
    .footer-left { display: table-cell; text-align: left; }
    .footer-right { display: table-cell; text-align: right; }
</style>
</head>
<body>

    <div class="header">
        <div class="header-logo-cell">
            @if($logoUrl)<img src="{{ $logoUrl }}" class="header-logo" />@endif
        </div>
        <div class="header-text-cell">
            <div class="header-company">{{ $companyName }}</div>
            <div class="header-tag">Call Sheet</div>
        </div>
    </div>
    <div class="accent-bar"></div>

    <div class="content">
        <div class="title-block">
            <div class="call-sheet-label">Call Sheet</div>
            <div class="event-name">{{ $event->name }}</div>
            <div class="event-date">
                @if($runsheet->date){{ $runsheet->date->format('l, F j, Y') }}@endif
                @if($event->venue) &nbsp;·&nbsp; {{ $event->venue }} @endif
            </div>
        </div>

        @if($locations->isNotEmpty())
        <div class="section-title">Locations</div>
        <div class="locations-grid">
            @@foreach($locations as $loc)
            <div class="location-box">
                <div class="location-name">{{ $loc->name }}</div>
                @if($loc->date)<div class="location-date">{{ $loc->date->format('D, M j') }}</div>@endif
                @if($loc->address)<div class="location-address">{{ $loc->address }}</div>@endif
            </div>
            @endforeach
        </div>
        @endif

        <div class="section-title">Schedule</div>
        <table class="schedule">
            <thead>
                <tr>
                    <th style="width:60px;">Time</th>
                    <th>Activity</th>
                    @if($locations->isNotEmpty())<th style="width:90px;">Location</th>@endif
                    <th style="width:110px;">Assigned</th>
                    <th style="width:60px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($runsheet->items as $item)
                @php
                    $dotColor = match($item->status) {
                        \App\Enums\RunsheetItemStatus::Done       => '#10B981',
                        \App\Enums\RunsheetItemStatus::InProgress => '#F59E0B',
                        \App\Enums\RunsheetItemStatus::Delayed    => '#EF4444',
                        default                                    => '#D6D3D1',
                    };
                @endphp
                <tr>
                    <td style="font-weight:bold;">
                        {{ $item->start_time?->format('g:i A') ?? '—' }}
                        @if($item->end_time)<br><span style="color:#A8A29E;font-weight:normal;font-size:8px;">to {{ $item->end_time->format('g:i A') }}</span>@endif
                    </td>
                    <td>
                        <strong>{{ $item->title }}</strong>
                        @if($item->description)<br><span style="color:#78716C;font-size:8.5px;">{{ $item->description }}</span>@endif
                    </td>
                    @if($locations->isNotEmpty())
                    <td>{{ $item->location?->name ?? '—' }}</td>
                    @endif
                    <td>
                        @if($item->assignedTo){{ $item->assignedTo->name }}@endif
                        @if($item->vendor)@if($item->assignedTo)<br>@endif{{ $item->vendor->name }}@endif
                        @if(!$item->assignedTo && !$item->vendor)—@endif
                    </td>
                    <td><span class="status-dot" style="background:{{ $dotColor }};"></span>{{ $item->statusLabel() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($assignedStaff->isNotEmpty() || $assignedVendors->isNotEmpty())
        <div class="section-title">Crew & Suppliers</div>
        <div class="crew-grid">
            @if($assignedStaff->isNotEmpty())
            <div class="crew-col">
                <div style="font-size:8.5px;font-weight:bold;color:#7C3AED;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:4px;">Staff</div>
                @foreach($assignedStaff as $name)
                <div class="crew-item">{{ $name }}</div>
                @endforeach
            </div>
            @endif
            @if($assignedVendors->isNotEmpty())
            <div class="crew-col">
                <div style="font-size:8.5px;font-weight:bold;color:#F59E0B;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:4px;">Suppliers</div>
                @foreach($assignedVendors as $name)
                <div class="crew-item">{{ $name }}</div>
                @endforeach
            </div>
            @endif
        </div>
        @endif

        @if($runsheet->notes)
        <div class="notes-box">
            <strong>Notes:</strong> {{ $runsheet->notes }}
        </div>
        @endif
    </div>

    <div class="footer">
        <div class="footer-left">{{ $companyName }} · Generated {{ now()->format('M j, Y g:i A') }}</div>
        <div class="footer-right">Koordli Call Sheet</div>
    </div>

</body>
</html>