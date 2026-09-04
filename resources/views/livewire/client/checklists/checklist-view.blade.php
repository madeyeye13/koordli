<div>
    <div style="margin-bottom:24px;">
        <h2 class="krd-heading-3" style="color:#1C1917;">Planning Checklist — {{ $event->name }}</h2>
        <p style="font-size:12px;color:#78716C;margin-top:4px;">Here's where things stand in your event's planning journey.</p>
    </div>

    @if(!$checklist)
    <div class="krd-card" style="padding:40px;text-align:center;color:#A8A29E;">Nothing has been shared with you yet.</div>
    @else
    <div class="krd-card" style="padding:20px;margin-bottom:20px;">
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
            <span style="font-size:13px;font-weight:600;color:#1C1917;">Overall Progress</span>
            <span style="font-size:13px;font-weight:600;color:#10B981;">{{ $overall['total'] > 0 ? round(($overall['completed'] / $overall['total']) * 100) : 0 }}%</span>
        </div>
        <div style="height:10px;background:#E7E5E4;border-radius:5px;overflow:hidden;">
            <div style="height:100%;width:{{ $overall['total'] > 0 ? round(($overall['completed'] / $overall['total']) * 100) : 0 }}%;background:#10B981;border-radius:5px;"></div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:10px;">
        @forelse($phaseProgress as $row)
        @php
            $pct = $row['total'] > 0 ? round(($row['completed'] / $row['total']) * 100) : 0;
            $isDone = $pct === 100;
        @endphp
        <div class="krd-card" style="padding:16px;">
            <div style="display:flex;align-items:center;justify-content:between;gap:12px;">
                <div style="flex:1;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                        @if($isDone)
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                        @endif
                        <span style="font-size:13.5px;font-weight:600;color:{{ $isDone ? '#10B981' : '#1C1917' }};">{{ $row['phase']->label() }}</span>
                    </div>
                    <div style="height:6px;background:#E7E5E4;border-radius:3px;overflow:hidden;">
                        <div style="height:100%;width:{{ $pct }}%;background:{{ $isDone ? '#10B981' : '#7C3AED' }};border-radius:3px;"></div>
                    </div>
                </div>
                <span style="font-size:12px;color:#A8A29E;flex-shrink:0;">{{ $row['completed'] }} / {{ $row['total'] }}</span>
            </div>
        </div>
        @empty
        <div class="krd-card" style="padding:24px;text-align:center;color:#A8A29E;">No checklist items yet.</div>
        @endforelse
    </div>
    @endif
</div>