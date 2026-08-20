<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;" id="dash-widgets-grid">

    {{-- Today's Tasks --}}
    <div class="krd-card" style="padding:18px;">
        <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:10px;">📋 Today's Tasks</div>
        @forelse($todaysTasks as $t)
        <a href="{{ route('tenant.tasks.edit', $t->id) }}" wire:navigate style="display:block;padding:7px 0;border-bottom:1px solid #F5F5F4;text-decoration:none;color:#1C1917;font-size:12.5px;">{{ $t->title }}</a>
        @empty
        <div style="font-size:12px;color:#A8A29E;">Nothing due today.</div>
        @endforelse
    </div>

    {{-- Overdue --}}
    <div class="krd-card" style="padding:18px;">
        <div style="font-size:13px;font-weight:600;color:#EF4444;margin-bottom:10px;">⚠️ Overdue Items</div>
        @forelse($overdueTasks as $t)
        <a href="{{ route('tenant.tasks.edit', $t->id) }}" wire:navigate style="display:block;padding:7px 0;border-bottom:1px solid #F5F5F4;text-decoration:none;color:#1C1917;font-size:12.5px;">{{ $t->title }} <span style="color:#A8A29E;">· {{ $t->due_date->diffForHumans() }}</span></a>
        @empty
        <div style="font-size:12px;color:#A8A29E;">Nothing overdue. 🎉</div>
        @endforelse
    </div>

    {{-- Upcoming Deadlines --}}
    <div class="krd-card" style="padding:18px;">
        <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:10px;">📅 Upcoming Deadlines</div>
        @forelse($upcomingDeadlines as $t)
        <a href="{{ route('tenant.tasks.edit', $t->id) }}" wire:navigate style="display:block;padding:7px 0;border-bottom:1px solid #F5F5F4;text-decoration:none;color:#1C1917;font-size:12.5px;">{{ $t->title }} <span style="color:#A8A29E;">· {{ $t->due_date->format('D, d M') }}</span></a>
        @empty
        <div style="font-size:12px;color:#A8A29E;">Nothing due in the next 7 days.</div>
        @endforelse
    </div>

    {{-- Escalated --}}
    <div class="krd-card" style="padding:18px;">
        <div style="font-size:13px;font-weight:600;color:#F59E0B;margin-bottom:10px;">🚨 Escalated Items</div>
        @forelse($escalatedTasks as $t)
        <a href="{{ route('tenant.tasks.edit', $t->id) }}" wire:navigate style="display:block;padding:7px 0;border-bottom:1px solid #F5F5F4;text-decoration:none;color:#1C1917;font-size:12.5px;">{{ $t->title }}</a>
        @empty
        <div style="font-size:12px;color:#A8A29E;">Nothing escalated right now.</div>
        @endforelse
    </div>

    {{-- Recent Notifications --}}
    <div class="krd-card" style="padding:18px;">
        <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:10px;">🔔 Recent Notifications</div>
        @forelse($recentNotifications as $n)
        <div style="padding:7px 0;border-bottom:1px solid #F5F5F4;font-size:12px;color:{{ $n->read_at ? '#A8A29E' : '#1C1917' }};">{{ $n->data['subject'] ?? '' }}</div>
        @empty
        <div style="font-size:12px;color:#A8A29E;">No notifications yet.</div>
        @endforelse
    </div>

    {{-- Today's Runsheet + Pending Approvals --}}
    <div class="krd-card" style="padding:18px;">
        @if($pendingVendorApprovals > 0)
        <div style="margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid #E7E5E4;">
            <div style="font-size:13px;font-weight:600;color:#7C3AED;margin-bottom:4px;">✋ Pending Vendor Approvals</div>
            <a href="{{ route('tenant.vendor.applications') }}" wire:navigate style="font-size:12.5px;color:#1C1917;text-decoration:none;">{{ $pendingVendorApprovals }} awaiting review →</a>
        </div>
        @endif
        <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:10px;">⏱️ Today's Runsheet Activities</div>
        @forelse($todaysRunsheetItems as $item)
        <div style="padding:7px 0;border-bottom:1px solid #F5F5F4;font-size:12.5px;color:#1C1917;">
            {{ $item->start_time?->format('g:i A') ?? '—' }} · {{ $item->title }}
        </div>
        @empty
        <div style="font-size:12px;color:#A8A29E;">No live runsheet activity today.</div>
        @endforelse
    </div>
</div>

<style>
@media (max-width:768px) { #dash-widgets-grid { grid-template-columns:1fr !important; } }
</style>