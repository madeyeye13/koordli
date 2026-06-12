<?php

namespace App\Livewire\Tenant\Events;

use App\Models\Tenant\Event;
use App\Models\Tenant\TenantEventStatus;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class EventDetail extends Component
{
    use WithToast;

    public Event $event;
    public string $uuid = '';

    public function mount(string $uuid): void
    {
        $this->uuid  = $uuid;
        $this->event = Event::with([
            'eventType',
            'status',
            'tasks',
            'guests',
            'team',
        ])->where('uuid', $uuid)->firstOrFail();
    }

    public function updateStatus(int $statusId): void
    {
        $this->event->update(['status_id' => $statusId]);
        $this->event->refresh();
        $this->toastSuccess('Status updated.');
    }

    public function render()
    {
        return view('livewire.tenant.events.event-detail', [
            'statuses' => TenantEventStatus::orderBy('sort_order')->get(),
        ]);
    }
}