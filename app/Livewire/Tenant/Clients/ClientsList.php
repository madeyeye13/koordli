<?php

namespace App\Livewire\Tenant\Clients;

use App\Models\Central\Client;
use App\Models\Tenant\ClientEventAccess;
use App\Models\Tenant\Event;
use App\Services\ClientInviteService;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class ClientsList extends Component
{
    use WithToast;

    public function mount(): void
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'clients.manage'),
            403
        );
    }

    private function requireManage(): bool
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'clients.manage')) {
            $this->toastError('You do not have permission to manage clients.');
            return false;
        }
        return true;
    }

    public function invite(int $eventId): void
    {
        if (!$this->requireManage()) return;

        $event = Event::find($eventId);
        if (!$event) return;

        $result = app(ClientInviteService::class)->inviteForEvent($event, auth()->id());

        match ($result['status']) {
            'error'   => $this->toastError($result['message']),
            'warning' => $this->toastWarning($result['message']),
            default   => $this->toastSuccess($result['message']),
        };
    }

    /**
     * Revokes this client's access to ONE specific event — does NOT
     * delete the client's login account, since they may have access to
     * other events. A per-row delete icon deleting the whole account
     * would be unexpectedly destructive for that case.
     */
    public function revokeAccess(int $accessId): void
    {
        if (!$this->requireManage()) return;

        ClientEventAccess::find($accessId)?->delete();
        $this->toastSuccess('Access revoked.');
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id;

        $events = Event::where('tenant_id', $tenantId)
            ->whereNotNull('client_email')
            ->where('client_email', '!=', '')
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'slug', 'client_name', 'client_email', 'client_phone']);

        $clientsByEmail = Client::where('tenant_id', $tenantId)->get()->keyBy('email');

        $rows = $events->map(function ($event) use ($clientsByEmail) {
            $client = $clientsByEmail->get($event->client_email);
            $access = $client
                ? ClientEventAccess::where('client_id', $client->id)->where('event_id', $event->id)->first()
                : null;

            return [
                'event_id'     => $event->id,
                'event_name'   => $event->name,
                'event_slug'   => $event->slug,
                'client_name'  => $event->client_name,
                'client_email' => $event->client_email,
                'invited'      => (bool) $access,
                'access_id'    => $access?->id,
            ];
        });

        return view('livewire.tenant.clients.clients-list', compact('rows'));
    }
}