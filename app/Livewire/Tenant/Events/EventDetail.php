<?php

namespace App\Livewire\Tenant\Events;

use App\Helpers\CurrencyHelper;
use App\Models\Tenant\Event;
use App\Models\Tenant\TenantEventStatus;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;

#[Layout('layouts.tenant')]
class EventDetail extends Component
{
    use WithToast;

    public Event $event;

    public function mount(string $slug): void
    {
        $this->event = Event::with([
            'eventType', 'status', 'tasks', 'rsvpResponses', 'team',
            'vendorAssignments.vendor.category',
            'budget.items', 'budget.clientPayments',
        ])->where('slug', $slug)->firstOrFail();
    }

    #[Renderless]
    public function updateStatus(int $statusId): void
    {
        $this->event->update(['status_id' => $statusId]);
        $this->toastSuccess('Status updated.');
    }

    public function render()
    {
        return view('livewire.tenant.events.event-detail', [
            'statuses' => TenantEventStatus::orderBy('sort_order')->get(),
            'symbol'   => CurrencyHelper::forTenant(),
        ]);
    }
}