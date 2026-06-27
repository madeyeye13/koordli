<?php

namespace App\Livewire\Tenant\Runsheet;

use App\Enums\RunsheetItemStatus;
use App\Models\Tenant\Event;
use App\Models\Tenant\Runsheet;
use App\Models\Tenant\RunsheetItem;
use App\Models\Tenant\User;
use App\Models\Tenant\Vendor;
use App\Traits\WithToast;
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

    // Delete
    public bool $showDeleteModal = false;
    public ?int $deleteItemId    = null;

    public function mount(string $slug): void
    {
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
        if (!$this->runsheet) {
            $this->toastError('Save the runsheet first.');
            return;
        }
        $this->reset(['item_title', 'item_desc', 'item_start', 'item_end', 'item_notes', 'item_assigned_to', 'item_vendor_id', 'editItemId']);
        $this->item_status  = 'pending';
        $this->showItemForm = true;
    }

    public function saveItem(): void
    {
        $this->validate([
            'item_title'       => 'required|string|min:2|max:200',
            'item_start'       => 'nullable|date_format:H:i',
            'item_end'         => 'nullable|date_format:H:i',
            'item_status'      => 'required|in:pending,in_progress,done,delayed',
            'item_assigned_to' => 'nullable|exists:users,id',
            'item_vendor_id'   => 'nullable|exists:vendors,id',
        ]);

        $sortOrder = RunsheetItem::where('runsheet_id', $this->runsheet->id)->max('sort_order') + 1;

        if ($this->editItemId) {
            RunsheetItem::find($this->editItemId)?->update([
                'title'       => $this->item_title,
                'description' => $this->item_desc ?: null,
                'start_time'  => $this->item_start ?: null,
                'end_time'    => $this->item_end ?: null,
                'status'      => $this->item_status,
                'notes'       => $this->item_notes ?: null,
                'assigned_to' => $this->item_assigned_to,
                'vendor_id'   => $this->item_vendor_id,
            ]);
            $this->toastSuccess('Item updated.');
        } else {
            RunsheetItem::create([
                'tenant_id'   => auth()->user()->tenant_id,
                'runsheet_id' => $this->runsheet->id,
                'title'       => $this->item_title,
                'description' => $this->item_desc ?: null,
                'start_time'  => $this->item_start ?: null,
                'end_time'    => $this->item_end ?: null,
                'status'      => $this->item_status,
                'notes'       => $this->item_notes ?: null,
                'assigned_to' => $this->item_assigned_to,
                'vendor_id'   => $this->item_vendor_id,
                'sort_order'  => $sortOrder,
            ]);
            $this->toastSuccess('Item added.');
        }

        $this->showItemForm = false;
        $this->reset(['item_title', 'item_desc', 'item_start', 'item_end', 'item_notes', 'item_assigned_to', 'item_vendor_id', 'editItemId']);
        $this->refreshRunsheet();
    }

    public function editItem(int $id): void
    {
        $item = RunsheetItem::find($id);
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
        $this->showItemForm    = true;
    }

    public function updateItemStatus(int $id, string $status): void
    {
        RunsheetItem::find($id)?->update(['status' => $status]);
        $this->refreshRunsheet();
        $this->toastSuccess('Status updated.');
    }

    public function moveUp(int $id): void
    {
        $item = RunsheetItem::find($id);
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
        $item = RunsheetItem::find($id);
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
        $this->deleteItemId    = $id;
        $this->showDeleteModal = true;
    }

    public function deleteItem(): void
    {
        RunsheetItem::find($this->deleteItemId)?->delete();
        $this->showDeleteModal = false;
        $this->deleteItemId    = null;
        $this->refreshRunsheet();
        $this->toastSuccess('Item removed.');
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

        return view('livewire.tenant.runsheet.runsheet-manager', compact('staff', 'vendors'));
    }
}