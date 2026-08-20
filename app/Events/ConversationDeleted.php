<?php
namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationDeleted implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public string $conversationUuid, public string $eventSlug) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('conversation.' . $this->conversationUuid)];
    }

    public function broadcastAs(): string
    {
        return 'conversation.deleted';
    }

    public function broadcastWith(): array
    {
        return ['event_slug' => $this->eventSlug];
    }
}