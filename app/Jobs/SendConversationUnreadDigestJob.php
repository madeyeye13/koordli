<?php

namespace App\Jobs;

use App\Models\Central\Client;
use App\Models\Central\VendorAccount;
use App\Models\Tenant\Conversation;
use App\Models\Tenant\ConversationParticipant;
use App\Services\FeatureGateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendConversationUnreadDigestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $conversationId,
        public string $participantType,
        public int $participantId,
        public string $lockKey,
        public int $attempt = 1,
    ) {}

    public function handle(): void
    {
        Cache::forget($this->lockKey);

        $participant = ConversationParticipant::where('conversation_id', $this->conversationId)
            ->where('participant_type', $this->participantType)
            ->where('participant_id', $this->participantId)
            ->whereNull('left_at')
            ->first();

        if (!$participant) return;

        $conversation = Conversation::with('event.tenant')->find($this->conversationId);
        if (!$conversation) return;

        $unreadCount = $conversation->messages()
            ->when($participant->last_read_at, fn($q) => $q->where('created_at', '>', $participant->last_read_at))
            ->where(function ($q) {
                $q->where('sender_type', '!=', $this->participantType)->orWhere('sender_id', '!=', $this->participantId);
            })
            ->count();

        if ($unreadCount === 0) return;

        $recipient = $this->participantType === 'client'
            ? Client::find($this->participantId)
            : VendorAccount::find($this->participantId);

                if (!$recipient || !$recipient->email) return;

        $pref = \App\Models\Tenant\NotificationPreference::where('notifiable_type', $this->participantType)
            ->where('notifiable_id', $this->participantId)
            ->where('category', 'conversations')
            ->first();

        // No row = default enabled. A row exists specifically to record an opt-out.
        if ($pref && !in_array('email', $pref->channels ?? ['email'])) {
            return;
        }

        $tenant = $conversation->event->tenant;
        $whiteLabel = app(FeatureGateService::class)->canAccess($tenant, 'white_label');

        Mail::to($recipient->email)->queue(new \App\Mail\ConversationUnreadDigestMail(
            recipientName: $recipient->name,
            conversationName: $conversation->name ?: 'a conversation',
            eventName: $conversation->event->name,
            unreadCount: $unreadCount,
            portalUrl: $this->participantType === 'client'
                ? route('client.conversations.show', $conversation->uuid)
                : route('vendor.conversations.show', $conversation->uuid),
            companyName: $tenant->name,
            whiteLabel: $whiteLabel,
        ));

        // Escalate up to 2 more times if still unread — 3 total touches max.
        $nextDelays = [2 => 30, 3 => 120];

        if (isset($nextDelays[$this->attempt + 1])) {
            $newLockKey = 'conv-notify-pending:' . $this->conversationId . ':' . $this->participantType . ':' . $this->participantId . ':attempt' . ($this->attempt + 1);
            Cache::put($newLockKey, true, now()->addMinutes($nextDelays[$this->attempt + 1] + 1));

            self::dispatch($this->conversationId, $this->participantType, $this->participantId, $newLockKey, $this->attempt + 1)
                ->delay(now()->addMinutes($nextDelays[$this->attempt + 1]));
        }
    }
}