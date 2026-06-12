<?php

namespace App\Livewire\Tenant\Events;

use App\Models\Tenant\Event;
use App\Models\Tenant\EventType;
use App\Models\Tenant\TenantEventStatus;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class CreateEvent extends Component
{
    use WithToast;

    // Form fields
    public string  $name          = '';
    public ?int    $event_type_id = null;
    public ?int    $status_id     = null;
    public string  $date          = '';
    public string  $venue         = '';
    public ?int    $max_guests    = null;

    // Edit mode
    public ?string $eventUuid = null;
    public ?Event  $event     = null;

    public function mount(?string $uuid = null): void
    {
        if ($uuid) {
            $this->eventUuid = $uuid;
            $this->event     = Event::where('uuid', $uuid)->firstOrFail();
            $this->name          = $this->event->name;
            $this->event_type_id = $this->event->event_type_id;
            $this->status_id     = $this->event->status_id;
            $this->date          = $this->event->date?->format('Y-m-d') ?? '';
            $this->venue         = $this->event->venue ?? '';
            $this->max_guests    = $this->event->max_guests;
        } else {
            // Set default status
            $default = TenantEventStatus::where('is_default', true)->first();
            $this->status_id = $default?->id;
        }
    }

    public function save(): void
    {
        $this->validate([
            'name'          => 'required|string|min:2|max:200',
            'event_type_id' => 'nullable|exists:event_types,id',
            'status_id'     => 'nullable|exists:tenant_event_statuses,id',
            'date'          => 'nullable|date',
            'venue'         => 'nullable|string|max:300',
            'max_guests'    => 'nullable|integer|min:1',
        ]);

        // ── Feature Gate: max guests per plan ──
        if ($this->max_guests) {
            $gate      = app(\App\Services\FeatureGateService::class);
            $tenant    = auth()->user()->tenant;
            $planLimit = $gate->getLimit($tenant, 'max_guests');

            if ($planLimit > 0 && $this->max_guests > $planLimit) {
                $this->addError('max_guests',
                    "Your current plan allows a maximum of {$planLimit} guests per event. " .
                    "Upgrade your plan to increase this limit."
                );
                return;
            }
        }

        $data = [
            'name'          => $this->name,
            'event_type_id' => $this->event_type_id,
            'status_id'     => $this->status_id,
            'date'          => $this->date ?: null,
            'venue'         => $this->venue ?: null,
            'max_guests'    => $this->max_guests,
        ];

        if ($this->event) {
            $this->event->update($data);
            $this->toastSuccess('Event updated successfully.');
            $this->redirect(route('tenant.events.show', $this->event->uuid), navigate: true);
        } else {
            $event = Event::create($data);
            $this->toastSuccess('Event created successfully.');
            $this->redirect(route('tenant.events.show', $event->uuid), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.tenant.events.create-event', [
            'eventTypes' => EventType::where('is_active', true)->orderBy('sort_order')->get(),
            'statuses'   => TenantEventStatus::orderBy('sort_order')->get(),
        ]);
    }
}