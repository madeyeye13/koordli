<?php

namespace App\Livewire\Tenant;

use App\Models\Central\VendorAccount;
use App\Models\Tenant\ActivityTimeline;
use App\Models\Tenant\QuickAccessLink;
use App\Models\Tenant\User;
use App\Services\FeatureGateService;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class QuickAccessAdmin extends Component
{
    use WithToast;

    public bool $showAuditFor = false;
    public ?int $auditLinkId  = null;

    public function mount(): void
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'quick-access.manage'),
            403
        );

        abort_unless(
            app(FeatureGateService::class)->canAccess(auth()->user()->tenant, 'quick_access_links'),
            403
        );
    }

    private function requireManage(): bool
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'quick-access.manage')) {
            $this->toastError('You do not have permission to manage quick access links.');
            return false;
        }
        return true;
    }

    public function togglePin(int $linkId): void
    {
        if (!$this->requireManage()) return;

        $link = QuickAccessLink::find($linkId);
        if (!$link) return;

        $link->update([
            'pin_enabled' => !$link->pin_enabled,
            'pin_hash'    => null, // clearing forces re-setup under the new requirement, never carries an old PIN across a policy change
        ]);

        $this->toastSuccess($link->pin_enabled ? 'PIN requirement enabled.' : 'PIN requirement disabled.');
    }

    public function regenerate(string $personType, int $personId): void
    {
        if (!$this->requireManage()) return;

        QuickAccessLink::regenerateFor($personType, $personId, auth()->user()->tenant_id, initiatedBySelf: false);
        $this->toastSuccess('Link regenerated. The previous link no longer works.');
    }

    public function deactivate(int $linkId): void
    {
        if (!$this->requireManage()) return;

        QuickAccessLink::find($linkId)?->update(['is_active' => false]);
        $this->toastSuccess('Link deactivated.');
    }

    public function reactivate(int $linkId): void
    {
        if (!$this->requireManage()) return;

        QuickAccessLink::find($linkId)?->update(['is_active' => true]);
        $this->toastSuccess('Link reactivated.');
    }

    /**
     * $keys are composite "PersonType:PersonId" strings — safe to split
     * on the first colon since a PHP class name never contains one.
     */
    public function bulkGenerate(array $keys): void
    {
        if (!$this->requireManage()) return;

        $tenantId = auth()->user()->tenant_id;
        $created = 0;

        foreach ($keys as $key) {
            [$type, $id] = explode(':', $key, 2);

            $exists = QuickAccessLink::withoutGlobalScope('tenant')
                ->where('person_type', $type)
                ->where('person_id', $id)
                ->where('tenant_id', $tenantId)
                ->exists();

            if (!$exists) {
                QuickAccessLink::create([
                    'tenant_id'   => $tenantId,
                    'person_type' => $type,
                    'person_id'   => $id,
                ]);
                $created++;
            }
        }

        $this->toastSuccess("{$created} link(s) generated.");
    }

    public function bulkDeactivate(array $keys): void
    {
        if (!$this->requireManage()) return;

        $tenantId = auth()->user()->tenant_id;
        $count = 0;

        foreach ($keys as $key) {
            [$type, $id] = explode(':', $key, 2);

            $updated = QuickAccessLink::withoutGlobalScope('tenant')
                ->where('person_type', $type)
                ->where('person_id', $id)
                ->where('tenant_id', $tenantId)
                ->update(['is_active' => false]);

            $count += $updated;
        }

        $this->toastSuccess("{$count} link(s) deactivated.");
    }

    public function viewAudit(int $linkId): void
    {
        $this->auditLinkId  = $linkId;
        $this->showAuditFor = true;
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id;

        $staff = User::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get(['id', 'name', 'email']);

        $vendors = VendorAccount::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get(['id', 'name', 'business_name']);

        $links = QuickAccessLink::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->get()
            ->keyBy(fn($l) => $l->person_type . ':' . $l->person_id);

        $rows = collect();

        foreach ($staff as $s) {
            $rows->push([
                'person_type' => User::class,
                'person_id'   => $s->id,
                'name'        => $s->name,
                'sub'         => $s->email,
                'role'        => 'Staff',
                'link'        => $links->get(User::class . ':' . $s->id),
            ]);
        }

        foreach ($vendors as $v) {
            $rows->push([
                'person_type' => VendorAccount::class,
                'person_id'   => $v->id,
                'name'        => $v->name,
                'sub'         => $v->business_name,
                'role'        => 'Vendor',
                'link'        => $links->get(VendorAccount::class . ':' . $v->id),
            ]);
        }

        $auditLog = collect();
        if ($this->auditLinkId) {
            $link = QuickAccessLink::withoutGlobalScope('tenant')->find($this->auditLinkId);
            if ($link) {
                $auditLog = ActivityTimeline::where('tenant_id', $tenantId)
                    ->where('actor_type', $link->person_type)
                    ->where('actor_id', $link->person_id)
                    ->whereIn('event_type', ['task_status_updated', 'runsheet_status_updated'])
                    ->orderByDesc('created_at')
                    ->limit(50)
                    ->get();
            }
        }

        $rowsJson = $rows->map(fn($r) => [
            'key'  => $r['person_type'] . ':' . $r['person_id'],
            'name' => $r['name'],
            'has_link' => (bool) $r['link'],
        ])->values()->toJson();

        return view('livewire.tenant.quick-access-admin', compact('rows', 'auditLog', 'rowsJson'));
    }
}