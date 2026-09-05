<?php

namespace App\Livewire\Tenant\Contracts;

use App\Models\Tenant\Vendor;
use App\Models\Tenant\VendorContract;
use App\Services\PermissionService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.tenant')]
class ContractList extends Component
{
    #[Url] public string $statusFilter = '';
    #[Url] public string $search       = '';

    public function render()
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'contracts.view')
                || app(PermissionService::class)->userCan(auth()->user(), 'contracts.manage'),
            403
        );

        $tenantId = auth()->user()->tenant_id;
        $contracts = VendorContract::where('tenant_id', $tenantId)->with(['vendor', 'event'])
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, fn($q) => $q->where('title', 'like', '%' . $this->search . '%')
                ->orWhereHas('vendor', fn($v) => $v->where('name', 'like', '%' . $this->search . '%')))
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'total'     => VendorContract::where('tenant_id', $tenantId)->count(),
            'draft'     => VendorContract::where('tenant_id', $tenantId)->where('status', 'draft')->count(),
            'sent'      => VendorContract::where('tenant_id', $tenantId)->where('status', 'sent')->count(),
            'signed'    => VendorContract::where('tenant_id', $tenantId)->where('status', 'signed')->count(),
            'expiring'  => VendorContract::where('tenant_id', $tenantId)->whereNotNull('expires_at')
                ->whereNotIn('status', ['signed', 'cancelled'])
                ->where('expires_at', '<=', now()->addDays(14))
                ->where('expires_at', '>=', now())
                ->count(),
        ];

        return view('livewire.tenant.contracts.contract-list', compact('contracts', 'stats'));
    }
}