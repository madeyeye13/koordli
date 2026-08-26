<?php

namespace App\Notifications\Channels;

use App\Jobs\SendWebPushNotificationJob;
use App\Models\Tenant\PushSubscription;
use Illuminate\Notifications\Notification;

class WebPushChannel
{
    public function send($notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toPush')) return;

        $payload = $notification->toPush($notifiable);

        $subscriptions = PushSubscription::withoutGlobalScope('tenant')
            ->where('notifiable_type', get_class($notifiable))
            ->where('notifiable_id', $notifiable->id)
            ->get();

        foreach ($subscriptions as $subscription) {
            SendWebPushNotificationJob::dispatch($subscription->id, $payload);
        }
    }
}