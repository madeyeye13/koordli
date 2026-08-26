<?php

namespace App\Jobs;

use App\Models\Tenant\PushSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class SendWebPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $subscriptionId,
        public array $payload,
    ) {}

    public function handle(): void
    {
        // withoutGlobalScope('tenant'): this runs inside a queue worker
        // process, which has no request-bound tenant context at all —
        // same reasoning already applied elsewhere in this codebase
        // (e.g. RunsheetManager, EventDetail) for queries that must work
        // regardless of ambient tenant state.
        $subscription = PushSubscription::withoutGlobalScope('tenant')->find($this->subscriptionId);
        if (!$subscription) return;

        $webPush = new WebPush([
            'VAPID' => [
                'subject'    => config('services.vapid.subject'),
                'publicKey'  => config('services.vapid.public_key'),
                'privateKey' => config('services.vapid.private_key'),
            ],
        ]);

        $webPush->queueNotification(
            Subscription::create([
                'endpoint'  => $subscription->endpoint,
                'publicKey' => $subscription->p256dh_key,
                'authToken' => $subscription->auth_token,
            ]),
            json_encode($this->payload)
        );

        foreach ($webPush->flush() as $report) {
            if (!$report->isSuccess()) {
                $statusCode = $report->getResponse()?->getStatusCode();

                // 404/410 means the subscription is genuinely gone
                // (unsubscribed, browser data cleared, uninstalled) —
                // remove it so we stop wasting queue cycles retrying a
                // dead endpoint forever.
                if (in_array($statusCode, [404, 410])) {
                    $subscription->delete();
                }
            }
        }
    }
}