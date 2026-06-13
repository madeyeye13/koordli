<div>
    {{-- Page Header --}}
    <div style="margin-bottom: 28px;">
        <div class="krd-label" style="margin-bottom: 6px;">Platform Overview</div>
        <h2 class="krd-heading-3" style="color: #1C1917;">Dashboard</h2>
    </div>

    {{-- KPI Cards --}}
    <div class="krd-grid-4" style="margin-bottom: 24px;">
        <div class="krd-card" style="text-align: center;">
            <div class="krd-label" style="margin-bottom: 8px;">Total Companies</div>
            <div style="font-size: 36px; font-weight: 700; color: #7C3AED; line-height: 1;">{{ $totalTenants }}</div>
        </div>
        <div class="krd-card" style="text-align: center;">
            <div class="krd-label" style="margin-bottom: 8px;">Active</div>
            <div style="font-size: 36px; font-weight: 700; color: #10B981; line-height: 1;">{{ $activeTenants }}</div>
        </div>
        <div class="krd-card" style="text-align: center;">
            <div class="krd-label" style="margin-bottom: 8px;">On Trial</div>
            <div style="font-size: 36px; font-weight: 700; color: #F59E0B; line-height: 1;">{{ $trialTenants }}</div>
        </div>
        <div class="krd-card" style="text-align: center;">
            <div class="krd-label" style="margin-bottom: 8px;">Plans</div>
            <div class="krd-stat-number" style="font-size: 36px; font-weight: 700; line-height: 1;">{{ $totalPlans }}</div>
        </div>
    </div>

    {{-- Recent Tenants --}}
    <div class="krd-card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
            <div class="krd-label">Recent Companies</div>
            <a href="{{ route('platform.tenants.create') }}" class="krd-btn krd-btn-primary krd-btn-sm">
                + Add Company
            </a>
        </div>

        @if($recentTenants->isEmpty())
        <div class="krd-empty-state">
            <div class="krd-empty-state-icon">🏢</div>
            <div class="krd-empty-state-title">No companies yet</div>
            <div class="krd-empty-state-desc">Create your first event company to get started.</div>
        </div>
        @else
        <div class="krd-table-wrap">
            <table class="krd-table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th class="krd-col-hide-mobile">Slug</th>
                        <th>Status</th>
                        <th class="krd-col-hide-mobile">Currency</th>
                        <th class="krd-col-hide-mobile">Created</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTenants as $tenant)
                    <tr>
                        <td>
                            <div style="font-weight: 500;">{{ $tenant->name }}</div>
                        </td>
                        <td class="krd-col-hide-mobile">
                            <span style="font-size: 12px; color: #78716C; font-family: monospace;">{{ $tenant->slug }}</span>
                        </td>
                        <td>
                            @if($tenant->status === 'active')
                                <span class="krd-badge krd-badge-green">Active</span>
                            @elseif($tenant->status === 'trial')
                                <span class="krd-badge krd-badge-amber">Trial</span>
                            @elseif($tenant->status === 'suspended')
                                <span class="krd-badge krd-badge-red">Suspended</span>
                            @else
                                <span class="krd-badge krd-badge-stone">{{ $tenant->status }}</span>
                            @endif
                        </td>
                        <td class="krd-col-hide-mobile" style="font-size: 12px; color: #78716C;">{{ $tenant->billing_currency }}</td>
                        <td class="krd-col-hide-mobile" style="font-size: 12px; color: #78716C;">{{ $tenant->created_at->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>