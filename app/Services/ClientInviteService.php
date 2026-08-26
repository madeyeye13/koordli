<?php

namespace App\Services;

use App\Jobs\SendClientInviteJob;
use App\Models\Central\Client;
use App\Models\Central\Tenant;
use App\Models\Tenant\ClientEventAccess;
use App\Models\Tenant\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientInviteService
{
    /**
     * Extracted from EventDetail::inviteClient() so this exact logic can
     * be reused by the new tenant-wide Clients page, rather than
     * duplicating the "existing client vs new client" branching in two
     * places.
     */
    public function inviteForEvent(Event $event, int $grantedByUserId): array
    {
        if (empty($event->client_email) || empty($event->client_name)) {
            return ['status' => 'error', 'message' => 'This event has no client email or name set.'];
        }

        $tenant = Tenant::find($event->tenant_id);

        $existing = Client::where('tenant_id', $tenant->id)
            ->where('email', $event->client_email)
            ->first();

        if ($existing) {
            $alreadyGranted = ClientEventAccess::where('client_id', $existing->id)
                ->where('event_id', $event->id)
                ->exists();

            if ($alreadyGranted) {
                return ['status' => 'warning', 'message' => 'This client already has access to this event.'];
            }

            ClientEventAccess::create([
                'tenant_id'  => $tenant->id,
                'client_id'  => $existing->id,
                'event_id'   => $event->id,
                'granted_by' => $grantedByUserId,
            ]);

            return ['status' => 'success', 'message' => $existing->name . ' already has a Koordli account — granted access to this event using their existing login.'];
        }

        $password = Str::random(10);

        $client = Client::create([
            'tenant_id' => $tenant->id,
            'name'      => $event->client_name,
            'email'     => $event->client_email,
            'password'  => Hash::make($password),
            'phone'     => $event->client_phone,
            'is_active' => true,
        ]);

        ClientEventAccess::create([
            'tenant_id'  => $tenant->id,
            'client_id'  => $client->id,
            'event_id'   => $event->id,
            'granted_by' => $grantedByUserId,
        ]);

        SendClientInviteJob::dispatch(
            $event->client_email,
            $event->client_name,
            $password,
            $tenant->name,
            $event->name,
            app(FeatureGateService::class)->canAccess($tenant, 'white_label'),
        );

        return ['status' => 'success', 'message' => 'Client invited successfully. Login credentials sent to ' . $event->client_email];
    }
}