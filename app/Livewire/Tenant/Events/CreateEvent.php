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

    public string  $name          = '';
    public ?int    $event_type_id = null;
    public ?int    $status_id     = null;
    public string  $date          = '';
    public string  $start_time    = '';
    public string  $end_date      = '';
    public string  $end_time      = '';
    public string  $venue         = '';
    public string  $location      = '';
    public ?int    $max_guests    = null;
    public string  $client_name   = '';
    public string  $client_phone  = '';
    public string  $client_email  = '';
    public ?float  $agreed_budget = null;
    public string  $notes         = '';
    public bool $rsvp_enabled = false;

    public ?string $eventSlug = null;
    public ?Event  $event     = null;

    public function mount(?string $slug = null): void
    {
        if ($slug) {
            $this->event         = Event::where('slug', $slug)->firstOrFail();
            $this->eventSlug     = $this->event->slug;
            $this->name          = $this->event->name;
            $this->event_type_id = $this->event->event_type_id;
            $this->status_id     = $this->event->status_id;
            $this->date          = $this->event->date?->format('Y-m-d') ?? '';
            $this->start_time    = $this->event->start_time ?? '';
            $this->end_date      = $this->event->end_date?->format('Y-m-d') ?? '';
            $this->end_time      = $this->event->end_time ?? '';
            $this->venue         = $this->event->venue ?? '';
            $this->location      = $this->event->location ?? '';
            $this->max_guests    = $this->event->max_guests;
            $this->client_name   = $this->event->client_name ?? '';
            $this->client_phone  = $this->event->client_phone ?? '';
            $this->client_email  = $this->event->client_email ?? '';
            $this->agreed_budget = $this->event->agreed_budget;
            $this->notes         = $this->event->notes ?? '';
            $this->rsvp_enabled  = (bool) $this->event->rsvp_enabled;
        } else {
            $default         = TenantEventStatus::where('is_default', true)->first();
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
            'start_time'    => 'nullable',
            'end_date'      => 'nullable|date|after_or_equal:date',
            'end_time'      => 'nullable',
            'venue'         => 'nullable|string|max:300',
            'location'      => 'nullable|string|max:200',
            'max_guests'    => 'nullable|integer|min:1',
            'client_name'   => 'nullable|string|max:200',
            'client_phone'  => 'nullable|string|max:30',
            'client_email'  => 'nullable|email|max:200',
            'agreed_budget' => 'nullable|numeric|min:0',
            'notes'         => 'nullable|string|max:2000',
            'rsvp_enabled'  => 'boolean',
        ]);

        // Plan gate: max guests
        if ($this->max_guests) {
            $gate      = app(\App\Services\FeatureGateService::class);
            $tenant    = auth()->user()->tenant;
            $planLimit = $gate->getLimit($tenant, 'max_guests');
            if ($planLimit > 0 && $this->max_guests > $planLimit) {
                $this->addError('max_guests',
                    "Your current plan allows a maximum of {$planLimit} guests per event. Upgrade to increase this limit."
                );
                return;
            }
        }

        $data = [
            'name'          => $this->name,
            'event_type_id' => $this->event_type_id,
            'status_id'     => $this->status_id,
            'date'          => $this->date ?: null,
            'start_time'    => $this->start_time ?: null,
            'end_date'      => $this->end_date ?: null,
            'end_time'      => $this->end_time ?: null,
            'venue'         => $this->venue ?: null,
            'location'      => $this->location ?: null,
            'max_guests'    => $this->max_guests,
            'client_name'   => $this->client_name ?: null,
            'client_phone'  => $this->client_phone ?: null,
            'client_email'  => $this->client_email ?: null,
            'agreed_budget' => $this->agreed_budget,
            'notes'         => $this->notes ?: null,
            'rsvp_enabled'  => (bool) $this->rsvp_enabled,
        ];

        if ($this->event) {
            $this->event->update($data);
            $this->toastSuccess('Event updated successfully.');
            $this->redirect(route('tenant.events.show', $this->event->slug), navigate: true);
        } else {
            $event = Event::create($data);
            $this->toastSuccess('Event created successfully.');
            $this->redirect(route('tenant.events.show', $event->slug), navigate: true);
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