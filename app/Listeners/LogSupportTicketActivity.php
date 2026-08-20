<?php

namespace App\Listeners;

use App\Events\SupportTicketCreated;
use App\Models\Tenant\ActivityTimeline;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogSupportTicketActivity implements ShouldQueue
{
    public function handle(SupportTicketCreated $event): void
    {
        // Support tickets are CENTRAL data (support_tickets isn't tenant-scoped like
        // your other models) — timeline logging is skipped here since ActivityTimeline
        // is tenant-scoped and expects a tenant Eloquent model, not a central one.
        // In-app notification to the tenant is handled separately since support tickets
        // already have their own dedicated email flow (Phase Support System) — this
        // listener exists mainly as the hook point for the future reminder rule below.
    }
}