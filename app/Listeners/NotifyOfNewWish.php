<?php
// app/Listeners/NotifyOfNewWish.php
namespace App\Listeners;

use App\Events\WishSubmitted;
use App\Mail\NewWishMail;
use App\Models\Central\Client;
use App\Models\Tenant\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class NotifyOfNewWish implements ShouldQueue
{
    public function handle(WishSubmitted $event): void
    {
        $wish = $event->wish;
        $event2 = $wish->event;

        $plannerUser = User::withoutGlobalScope('tenant')
            ->where('tenant_id', $wish->tenant_id)
            ->whereHas('roles', fn($q) => $q->where('is_system', true))->first();

        if ($plannerUser?->email) {
            Mail::to($plannerUser->email)->send(new NewWishMail($wish, $plannerUser->name));
        }

        if ($event2?->client_email) {
            $client = Client::where('tenant_id', $wish->tenant_id)->where('email', $event2->client_email)->first();
            if ($client) {
                Mail::to($client->email)->send(new NewWishMail($wish, $client->name));
            }
        }
    }
}