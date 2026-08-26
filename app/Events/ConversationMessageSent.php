<?php

namespace App\Events;

use App\Models\Tenant\ConversationMessage;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationMessageSent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public ConversationMessage $message, public string $conversationUuid) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('conversation.' . $this->conversationUuid)];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        $replyTo = $this->message->replyTo;

        return [
            'id'              => $this->message->id,
            'sender_type'     => $this->message->sender_type,
            'sender_id'       => $this->message->sender_id,
            'sender_name'     => $this->message->senderName(),
            'body'            => $this->message->body,
            'rendered'        => $this->message->renderedBody(),
            'created_at'      => $this->message->created_at->format('g:i A'),
            'reply_to'        => $replyTo ? [
                'sender_name' => $replyTo->senderName(),
                'snippet'     => \Illuminate\Support\Str::limit($replyTo->body, 60),
            ] : null,
            'attachments'     => $this->message->attachments->map(fn($att) => [
                'url'       => \Illuminate\Support\Facades\Storage::url($att->file_path),
                'name'      => $att->file_name,
                'mime_type' => str_starts_with($att->file_name, 'voice-note-') ? 'audio/webm' : $att->mime_type,
                'is_audio'  => str_starts_with($att->mime_type ?? '', 'audio/') || str_starts_with($att->file_name, 'voice-note-'),
            ])->values(),
            'shared_moodboard_html' => $this->message->sharedMoodboardCardHtml(),
        ];
    }
}