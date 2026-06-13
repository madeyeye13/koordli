<?php

namespace App\Livewire\Tenant\Events;

use App\Helpers\CurrencyHelper;
use App\Models\Tenant\Event;
use App\Models\Tenant\TenantEventStatus;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use App\Models\Central\Client;
use App\Jobs\SendClientInviteJob;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

    public function inviteClient(): void
    {
        if (empty($this->event->client_email) || empty($this->event->client_name)) {
            $this->toastError('This event has no client email or name set. Edit the event first.');
            return;
        }

        $tenant = auth()->user()->tenant;

        // Check if client already exists for this tenant+email
        $existing = Client::where('tenant_id', $tenant->id)
            ->where('email', $this->event->client_email)
            ->first();

        if ($existing) {
            $this->toastWarning('A client portal account already exists for this email.');
            return;
        }

        $password = Str::random(10);

        Client::create([
            'tenant_id' => $tenant->id,
            'name'      => $this->event->client_name,
            'email'     => $this->event->client_email,
            'password'  => Hash::make($password),
            'phone'     => $this->event->client_phone,
            'is_active' => true,
        ]);

        SendClientInviteJob::dispatch(
            $this->event->client_email,
            $this->event->client_name,
            $password,
            $tenant->name,
            $this->event->name,
        );

        $this->toastSuccess('Client invited successfully. Login credentials sent to ' . $this->event->client_email);
    }

    public function render()
    {
        return view('livewire.tenant.events.event-detail', [
            'statuses' => TenantEventStatus::orderBy('sort_order')->get(),
            'symbol'   => CurrencyHelper::forTenant(),
        ]);
    }
}