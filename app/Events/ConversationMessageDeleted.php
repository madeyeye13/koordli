<?php
namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationMessageDeleted implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public string $conversationUuid, public int $messageId) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('conversation.' . $this->conversationUuid)];
    }

    public function broadcastAs(): string
    {
        return 'message.deleted';
    }

    public function broadcastWith(): array
    {
        return ['message_id' => $this->messageId];
    }
}