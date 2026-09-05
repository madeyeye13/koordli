<?php

namespace App\Livewire\Tenant\Runsheet;

use App\Enums\RunsheetItemStatus;
use App\Models\Tenant\Event;
use App\Models\Tenant\Runsheet;
use App\Models\Tenant\RunsheetItem;
use App\Models\Tenant\User;
use App\Models\Tenant\Vendor;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;

#[Layout('layouts.tenant')]
class RunsheetManager extends Component
{
    use WithToast;

    public Event      $event;
    public ?Runsheet  $runsheet = null;

    // Runsheet meta
    public string  $title  = '';
    public string  $date   = '';
    public string  $notes  = '';
    public string  $status = 'draft';

    // Active tab
    public string $activeTab = 'timeline';

    // Item form
    public bool   $showItemForm   = false;
    public ?int   $editItemId     = null;
    public string $item_title     = '';
    public string $item_desc      = '';
    public string $item_start     = '';
    public string $item_end       = '';
    public string $item_status    = 'pending';
    public string $item_notes     = '';
    public ?int   $item_assigned_to = null;
    public ?int   $item_vendor_id   = null;
    public ?int   $item_location_id = null;

    // Locations (multi-location support)
    public bool   $showLocationForm = false;
    public ?int   $editLocationId   = null;
    public string $location_name    = '';
    public string $location_address = '';
    public string $location_date    = '';
    public string $location_notes   = '';

    // Delete
    public bool $showDeleteModal = false;
    public ?int $deleteItemId    = null;

    public bool $showDeleteLocationModal = false;
    public ?int $deleteLocationId        = null;

    private function requireManage(): bool
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'runsheet.manage')) {
            $this->toastError('You do not have permission to manage the runsheet.');
            return false;
        }
        return true;
    }

    public function mount(string $slug): void
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'runsheet.view'),
            403
        );

        $this->event = Event::where('slug', $slug)->firstOrFail();

        $this->runsheet = Runsheet::where('event_id', $this->event->id)
            ->with(['items' => fn($q) => $q->orderBy('sort_order')->orderBy('start_time')])
            ->first();

        if ($this->runsheet) {
            $this->title  = $this->runsheet->title;
            $this->date   = $this->runsheet->date?->format('Y-m-d') ?? '';
            $this->notes  = $this->runsheet->notes ?? '';
            $this->status = $this->runsheet->status;
        } else {
            $this->title = $this->event->name . ' — Runsheet';
            $this->date  = $this->event->date?->format('Y-m-d') ?? '';
        }
    }

    #[Renderless]
    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function saveRunsheet(): void
    {
        if (!$this->requireManage()) return;

        $this->validate([
            'title'  => 'required|string|min:2|max:200',
            'date'   => 'nullable|date',
            'notes'  => 'nullable|string|max:2000',
            'status' => 'required|in:draft,active,completed',
        ]);

        if ($this->runsheet) {
            $this->runsheet->update([
                'title'  => $this->title,
                'date'   => $this->date ?: null,
                'notes'  => $this->notes ?: null,
                'status' => $this->status,
            ]);
        } else {
            $this->runsheet = Runsheet::create([
                'uuid'       => Str::uuid(),
                'tenant_id'  => auth()->user()->tenant_id,
                'event_id'   => $this->event->id,
                'title'      => $this->title,
                'date'       => $this->date ?: null,
                'notes'      => $this->notes ?: null,
                'status'     => $this->status,
                'created_by' => auth()->id(),
            ]);

            $this->runsheet->load(['items' => fn($q) => $q->orderBy('sort_order')->orderBy('start_time')]);
        }

        $this->toastSuccess('Runsheet saved.');
    }

    public function showAddItem(): void
    {
        if (!$this->requireManage()) return;

        if (!$this->runsheet) {
            $this->toastError('Save the runsheet first.');
            return;
        }
        $this->reset(['item_title', 'item_desc', 'item_start', 'item_end', 'item_notes', 'item_assigned_to', 'item_vendor_id', 'item_location_id', 'editItemId']);
        $this->item_status  = 'pending';
        $this->showItemForm = true;
    }

    public function saveItem(): void
    {
        if (!$this->requireManage()) return;

        $tenantId = auth()->user()->tenant_id;

        $this->validate([
            'item_title'       => 'required|string|min:2|max:200',
            'item_start'       => 'nullable|date_format:H:i',
            'item_end'         => 'nullable|date_format:H:i',
            'item_status'      => 'required|in:pending,in_progress,done,delayed',
            'item_assigned_to' => ['nullable', Rule::exists('users', 'id')->where('tenant_id', $tenantId)],
            'item_vendor_id'   => ['nullable', Rule::exists('vendors', 'id')->where('tenant_id', $tenantId)],
            'item_location_id' => ['nullable', Rule::exists('event_locations', 'id')->where('tenant_id', $tenantId)],
        ]);

        $sortOrder = RunsheetItem::where('runsheet_id', $this->runsheet->id)->max('sort_order') + 1;

        if ($this->editItemId) {
            RunsheetItem::where('tenant_id', auth()->user()->tenant_id)->where('runsheet_id', $this->runsheet->id)->find($this->editItemId)?->update([
                'title'             => $this->item_title,
                'description'       => $this->item_desc ?: null,
                'start_time'        => $this->item_start ?: null,
                'end_time'          => $this->item_end ?: null,
                'status'            => $this->item_status,
                'notes'             => $this->item_notes ?: null,
                'assigned_to'       => $this->item_assigned_to,
                'vendor_id'         => $this->item_vendor_id,
                'event_location_id' => $this->item_location_id,
            ]);
            $this->toastSuccess('Item updated.');
        } else {
            RunsheetItem::create([
                'tenant_id'         => auth()->user()->tenant_id,
                'runsheet_id'       => $this->runsheet->id,
                'title'             => $this->item_title,
                'description'       => $this->item_desc ?: null,
                'start_time'        => $this->item_start ?: null,
                'end_time'          => $this->item_end ?: null,
                'status'            => $this->item_status,
                'notes'             => $this->item_notes ?: null,
                'assigned_to'       => $this->item_assigned_to,
                'vendor_id'         => $this->item_vendor_id,
                'event_location_id' => $this->item_location_id,
                'sort_order'        => $sortOrder,
            ]);
            $this->toastSuccess('Item added.');
        }

        $this->showItemForm = false;
        $this->reset(['item_title', 'item_desc', 'item_start', 'item_end', 'item_notes', 'item_assigned_to', 'item_vendor_id', 'item_location_id', 'editItemId']);
        $this->refreshRunsheet();
    }

    public function editItem(int $id): void
    {
        if (!$this->requireManage()) return;

        $item = RunsheetItem::where('tenant_id', auth()->user()->tenant_id)->where('runsheet_id', $this->runsheet->id)->find($id);
        if (!$item) return;

        $this->editItemId      = $id;
        $this->item_title      = $item->title;
        $this->item_desc       = $item->description ?? '';
        $this->item_start      = $item->start_time ? $item->start_time->format('H:i') : '';
        $this->item_end        = $item->end_time ? $item->end_time->format('H:i') : '';
        $this->item_status     = $item->status->value;
        $this->item_notes      = $item->notes ?? '';
        $this->item_assigned_to = $item->assigned_to;
        $this->item_vendor_id  = $item->vendor_id;
        $this->item_location_id = $item->event_location_id;
        $this->showItemForm    = true;
    }

    public function updateItemStatus(int $id, string $status): void
    {
        if (!$this->requireManage()) return;

        $item = RunsheetItem::where('tenant_id', auth()->user()->tenant_id)->where('runsheet_id', $this->runsheet->id)->find($id);
        $item?->update(['status' => $status]);

        if ($status === 'delayed' && $item) {
            event(new \App\Events\RunsheetItemDelayed($item->fresh(['dependents.assignedTo', 'runsheet.event'])));
        }

        $this->refreshRunsheet();
        $this->toastSuccess('Status updated.');
    }

    public function moveUp(int $id): void
    {
        if (!$this->requireManage()) return;

        $item = RunsheetItem::where('tenant_id', auth()->user()->tenant_id)->where('runsheet_id', $this->runsheet->id)->find($id);
        if (!$item) return;
        $prev = RunsheetItem::where('runsheet_id', $item->runsheet_id)
            ->where('sort_order', '<', $item->sort_order)
            ->orderByDesc('sort_order')->first();
        if ($prev) {
            [$item->sort_order, $prev->sort_order] = [$prev->sort_order, $item->sort_order];
            $item->save(); $prev->save();
        }
        $this->refreshRunsheet();
    }

    public function moveDown(int $id): void
    {
        if (!$this->requireManage()) return;

        $item = RunsheetItem::where('tenant_id', auth()->user()->tenant_id)->where('runsheet_id', $this->runsheet->id)->find($id);
        if (!$item) return;
        $next = RunsheetItem::where('runsheet_id', $item->runsheet_id)
            ->where('sort_order', '>', $item->sort_order)
            ->orderBy('sort_order')->first();
        if ($next) {
            [$item->sort_order, $next->sort_order] = [$next->sort_order, $item->sort_order];
            $item->save(); $next->save();
        }
        $this->refreshRunsheet();
    }

    public function confirmDelete(int $id): void
    {
        if (!$this->requireManage()) return;

        $this->deleteItemId    = $id;
        $this->showDeleteModal = true;
    }

    public function deleteItem(): void
    {
        if (!$this->requireManage()) return;

        RunsheetItem::where('tenant_id', auth()->user()->tenant_id)->where('runsheet_id', $this->runsheet->id)->find($this->deleteItemId)?->delete();
        $this->showDeleteModal = false;
        $this->deleteItemId    = null;
        $this->refreshRunsheet();
        $this->toastSuccess('Item removed.');
    }

    public function showAddLocation(): void
    {
        if (!$this->requireManage()) return;

        $this->reset(['location_name', 'location_address', 'location_date', 'location_notes', 'editLocationId']);
        $this->showLocationForm = true;
    }

    public function editLocation(int $id): void
    {
        if (!$this->requireManage()) return;

        $location = \App\Models\Tenant\EventLocation::where('tenant_id', auth()->user()->tenant_id)->where('event_id', $this->event->id)->find($id);
        if (!$location) return;

        $this->editLocationId   = $id;
        $this->location_name    = $location->name;
        $this->location_address = $location->address ?? '';
        $this->location_date    = $location->date?->format('Y-m-d') ?? '';
        $this->location_notes   = $location->notes ?? '';
        $this->showLocationForm = true;
    }

    public function saveLocation(): void
    {
        if (!$this->requireManage()) return;

        $this->validate([
            'location_name' => 'required|string|min:2|max:150',
            'location_date' => 'nullable|date',
        ]);

        $data = [
            'tenant_id' => auth()->user()->tenant_id,
            'event_id'  => $this->event->id,
            'name'      => $this->location_name,
            'address'   => $this->location_address ?: null,
            'date'      => $this->location_date ?: null,
            'notes'     => $this->location_notes ?: null,
        ];

        if ($this->editLocationId) {
            \App\Models\Tenant\EventLocation::where('tenant_id', auth()->user()->tenant_id)->where('event_id', $this->event->id)->find($this->editLocationId)?->update($data);
            $this->toastSuccess('Location updated.');
        } else {
            $data['sort_order'] = \App\Models\Tenant\EventLocation::where('event_id', $this->event->id)->max('sort_order') + 1;
            \App\Models\Tenant\EventLocation::create($data);
            $this->toastSuccess('Location added.');
        }

        $this->showLocationForm = false;
        $this->event->refresh();
    }

    public function confirmDeleteLocation(int $id): void
    {
        if (!$this->requireManage()) return;

        $this->deleteLocationId       = $id;
        $this->showDeleteLocationModal = true;
    }

    public function deleteLocation(): void
    {
        if (!$this->requireManage()) return;

        \App\Models\Tenant\EventLocation::where('tenant_id', auth()->user()->tenant_id)->where('event_id', $this->event->id)->find($this->deleteLocationId)?->delete();
        $this->showDeleteLocationModal = false;
        $this->deleteLocationId        = null;
        $this->event->refresh();
        $this->refreshRunsheet();
        $this->toastSuccess('Location removed.');
    }

    public function downloadCallSheet()
    {
        // Read-only export — view tier is sufficient, doesn't require full manage.
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'runsheet.view'),
            403
        );

        $tenant   = auth()->user()->tenant;
        $branding = $tenant->branding ?? [];

        $logoUrl = null;
        if (!empty($branding['logo'])) {
            $logoPath = \Illuminate\Support\Facades\Storage::disk('public')->path($branding['logo']);
            if (file_exists($logoPath)) {
                $logoUrl = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $assignedStaff   = $this->runsheet->items->filter(fn($i) => $i->assigned_to)->pluck('assignedTo.name')->filter()->unique()->values();
        $assignedVendors = $this->runsheet->items->filter(fn($i) => $i->vendor_id)->pluck('vendor.name')->filter()->unique()->values();

        $pdf = Pdf::loadView('pdf.call-sheet-pdf', [
            'event'           => $this->event,
            'runsheet'        => $this->runsheet,
            'locations'       => $this->event->locations()->get(),
            'assignedStaff'   => $assignedStaff,
            'assignedVendors' => $assignedVendors,
            'companyName'     => $tenant->name,
            'primaryColor'    => $branding['primary_color'] ?? '#7C3AED',
            'accentColor'     => $branding['accent_color'] ?? '#F59E0B',
            'logoUrl'         => $logoUrl,
        ])->setPaper('a4');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'Call-Sheet-' . Str::slug($this->event->name) . '.pdf'
        );
    }

    private function refreshRunsheet(): void
    {
        $this->runsheet = Runsheet::where('event_id', $this->event->id)
            ->with(['items' => fn($q) => $q->orderBy('sort_order')->orderBy('start_time')])
            ->first();
    }

    public function render()
    {
        $staff   = User::withoutGlobalScope('tenant')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $vendors = Vendor::withoutGlobalScope('tenant')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $locations = $this->event->locations()->get();

        return view('livewire.tenant.runsheet.runsheet-manager', compact('staff', 'vendors', 'locations'));
    }
}