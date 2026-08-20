<?php

namespace App\Jobs;

use App\Models\Tenant\Conversation;
use App\Models\Tenant\ConversationParticipant;
use App\Models\Tenant\User;
use App\Services\Notifications\NotificationDispatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class CheckTenantUserConversationReadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $conversationId,
        public int $userId,
        public string $lockKey,
        public int $attempt = 1,
    ) {}

    public function handle(): void
    {
        Cache::forget($this->lockKey);

        $participant = ConversationParticipant::where('conversation_id', $this->conversationId)
            ->where('participant_type', 'tenant_user')
            ->where('participant_id', $this->userId)
            ->whereNull('left_at')
            ->first();

        if (!$participant) return;

        $conversation = Conversation::with('event')->find($this->conversationId);
        if (!$conversation) return;

        $unreadCount = $conversation->messages()
            ->when($participant->last_read_at, fn($q) => $q->where('created_at', '>', $participant->last_read_at))
            ->where(function ($q) {
                $q->where('sender_type', '!=', 'tenant_user')->orWhere('sender_id', '!=', $this->userId);
            })
            ->count();

        if ($unreadCount === 0) return; // they caught up on their own — no notification needed

        $user = User::withoutGlobalScope('tenant')->find($this->userId);
        if (!$user) return;

        app(NotificationDispatchService::class)->notify(
            notifiable: $user,
            category: 'conversations',
            notificationType: 'conversation_unread_digest',
            templateKey: 'conversation_unread_digest',
            placeholders: [
                'user_name'  => $user->name,
                'task_name'  => $conversation->name ?: 'a conversation',
                'event_name' => $conversation->event->name ?? '',
            ],
            priority: 'normal',
            actionUrl: route('tenant.conversations.show', $conversation->uuid),
            actionLabel: 'View Conversation',
            tenantId: $conversation->tenant_id,
        );

        // Escalate up to 2 more times if still unread — 3 total touches max,
        // then stop (never nag indefinitely). Intervals: +30 min, then +2h.
        $nextDelays = [2 => 30, 3 => 120]; // attempt => minutes from now

        if (isset($nextDelays[$this->attempt + 1])) {
            $newLockKey = 'conv-notify-pending:' . $this->conversationId . ':tenant_user:' . $this->userId . ':attempt' . ($this->attempt + 1);
            Cache::put($newLockKey, true, now()->addMinutes($nextDelays[$this->attempt + 1] + 1));

            self::dispatch($this->conversationId, $this->userId, $newLockKey, $this->attempt + 1)
                ->delay(now()->addMinutes($nextDelays[$this->attempt + 1]));
        }
    }
}