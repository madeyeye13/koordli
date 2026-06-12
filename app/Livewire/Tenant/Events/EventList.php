<?php

namespace App\Livewire\Tenant\Events;

use App\Models\Tenant\Event;
use App\Models\Tenant\EventType;
use App\Models\Tenant\TenantEventStatus;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Renderless;

#[Layout('layouts.tenant')]
class EventList extends Component
{
    use WithPagination, WithToast;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $typeFilter = '';

    #[Url]
    public string $view = 'list'; // list or grid

    public bool $showDeleteModal = false;
    public ?int $deleteId        = null;

    public function updatedSearch(): void   { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }
    public function updatedTypeFilter(): void   { $this->resetPage(); }

    #[Renderless]
    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId        = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $event = Event::find($this->deleteId);
        if ($event) {
            $event->delete();
            $this->toastSuccess('Event deleted.');
        }
        $this->showDeleteModal = false;
        $this->deleteId        = null;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deleteId        = null;
    }

    public function render()
    {
        $events = Event::with(['eventType', 'status', 'createdBy'])
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('venue', 'like', '%' . $this->search . '%')
            )
            ->when($this->statusFilter, fn($q) =>
                $q->where('status_id', $this->statusFilter)
            )
            ->when($this->typeFilter, fn($q) =>
                $q->where('event_type_id', $this->typeFilter)
            )
            ->orderBy('date', 'asc')
            ->paginate($this->view === 'grid' ? 12 : 15);

        return view('livewire.tenant.events.event-list', [
            'events'   => $events,
            'statuses' => TenantEventStatus::orderBy('sort_order')->get(),
            'types'    => EventType::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }
}