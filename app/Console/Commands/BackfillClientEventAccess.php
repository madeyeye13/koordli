<?php

namespace App\Console\Commands;

use App\Models\Central\Client;
use App\Models\Tenant\ClientEventAccess;
use App\Models\Tenant\Event;
use Illuminate\Console\Command;

class BackfillClientEventAccess extends Command
{
    protected $signature   = 'koordli:backfill-client-event-access';
    protected $description = 'One-time backfill: create ClientEventAccess rows for existing client_email-matched events';

    public function handle(): void
    {
        $events = Event::withoutGlobalScope('tenant')->whereNotNull('client_email')->get();

        foreach ($events as $event) {
            $client = Client::where('tenant_id', $event->tenant_id)
                ->where('email', $event->client_email)
                ->first();

            if (!$client) continue;

            $exists = ClientEventAccess::where('client_id', $client->id)
                ->where('event_id', $event->id)
                ->exists();

            if ($exists) continue;

            ClientEventAccess::create([
                'tenant_id' => $event->tenant_id,
                'client_id' => $client->id,
                'event_id'  => $event->id,
            ]);

            $this->info("Backfilled access: {$client->name} → {$event->name}");
        }
    }
}