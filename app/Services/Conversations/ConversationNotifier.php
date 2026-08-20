<?php

namespace App\Services\Conversations;

use App\Jobs\SendConversationUnreadDigestJob;
use App\Models\Tenant\Conversation;
use App\Models\Tenant\ConversationMessage;
use App\Services\Notifications\NotificationDispatchService;
use Illuminate\Support\Facades\Cache;

class ConversationNotifier
{
    private const DELAY_MINUTES = 3;

    public static function notifyOthers(Conversation $conversation, ConversationMessage $message): void
    {
        foreach ($conversation->participants as $participant) {
            $isSender = $participant->participant_type === $message->sender_type
                && $participant->participant_id === $message->sender_id;

            if ($isSender) continue;

            $lockKey = 'conv-notify-pending:' . $conversation->id . ':' . $participant->participant_type . ':' . $participant->participant_id;

            // Debounce: if a check is already queued for this participant on
            // this conversation, don't queue another one — the pending check
            // will naturally cover this newer message too when it runs
            if (Cache::has($lockKey)) continue;

            Cache::put($lockKey, true, now()->addMinutes(self::DELAY_MINUTES + 1));

            if ($participant->participant_type === 'tenant_user') {
                self::scheduleTenantUserCheck($conversation, $participant, $lockKey);
            } else {
                SendConversationUnreadDigestJob::dispatch(
                    $conversation->id,
                    $participant->participant_type,
                    $participant->participant_id,
                    $lockKey,
                )->delay(now()->addMinutes(self::DELAY_MINUTES));
            }
        }
    }

        public static function notifyAdded(Conversation $conversation, string $participantType, int $participantId, string $addedByName): void
    {
        if ($participantType === 'tenant_user') {
            $user = \App\Models\Tenant\User::withoutGlobalScope('tenant')->find($participantId);
            if (!$user) return;

            app(NotificationDispatchService::class)->notify(
                notifiable: $user,
                category: 'conversations',
                notificationType: 'conversation_added',
                templateKey: 'conversation_added',
                placeholders: [
                    'user_name'    => $user->name,
                    'task_name'    => $conversation->name ?: 'a conversation',
                    'event_name'   => $conversation->event->name ?? '',
                    'added_by'     => $addedByName,
                ],
                priority: 'normal',
                actionUrl: route('tenant.conversations.show', $conversation->uuid),
                actionLabel: 'View Conversation',
                tenantId: $conversation->tenant_id,
            );
            return;
        }

        // Client / Vendor — white-label aware email
        $recipient = $participantType === 'client'
            ? \App\Models\Central\Client::find($participantId)
            : \App\Models\Central\VendorAccount::find($participantId);

        if (!$recipient || !$recipient->email) return;

        $pref = \App\Models\Tenant\NotificationPreference::where('notifiable_type', $participantType)
            ->where('notifiable_id', $participantId)
            ->where('category', 'conversations')
            ->first();

        if ($pref && !in_array('email', $pref->channels ?? ['email'])) {
            return;
        }

        $tenant = $conversation->event->tenant;
        $whiteLabel = app(\App\Services\FeatureGateService::class)->canAccess($tenant, 'white_label');

        \Illuminate\Support\Facades\Mail::to($recipient->email)->queue(new \App\Mail\ConversationAddedMail(
            recipientName: $recipient->name,
            conversationName: $conversation->name ?: 'a conversation',
            eventName: $conversation->event->name,
            addedByName: $addedByName,
            portalUrl: $participantType === 'client'
                ? route('client.conversations.show', $conversation->uuid)
                : route('vendor.conversations.show', $conversation->uuid),
            companyName: $tenant->name,
            whiteLabel: $whiteLabel,
        ));
    }

    private static function scheduleTenantUserCheck($conversation, $participant, string $lockKey): void
    {
        \App\Jobs\CheckTenantUserConversationReadJob::dispatch(
            $conversation->id,
            $participant->participant_id,
            $lockKey,
        )->delay(now()->addMinutes(self::DELAY_MINUTES));
    }
}