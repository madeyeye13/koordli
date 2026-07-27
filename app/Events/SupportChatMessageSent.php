<?php

namespace App\Events;

use App\Models\Central\SupportTicketMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public SupportTicketMessage $message, public string $ticketUuid) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('support-ticket.' . $this->ticketUuid)];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'          => $this->message->id,
            'sender_type' => $this->message->sender_type,
            'sender_name' => $this->message->senderName(),
            'message'     => $this->message->message,
            'rendered'    => $this->message->renderedMessage($this->message->sender_type === 'agent'),
            'created_at'  => $this->message->created_at->format('g:i A'),
        ];
    }
}