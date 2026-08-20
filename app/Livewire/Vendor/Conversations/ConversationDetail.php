<?php

namespace App\Livewire\Vendor\Conversations;

use App\Models\Tenant\Conversation;
use App\Models\Tenant\ConversationMessage;
use App\Models\Tenant\ConversationMessageAttachment;
use App\Models\Tenant\ConversationParticipant;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.vendor')]
class ConversationDetail extends Component
{
    use WithToast, WithFileUploads;

    public Conversation $conversation;
    public string $body = '';
    public array  $attachments = [];
    public ?int   $replyingToId = null;

    public function mount(string $uuid): void
    {
        $this->conversation = Conversation::where('uuid', $uuid)
            ->with(['event', 'participants', 'messages.attachments', 'messages.replyTo'])
            ->firstOrFail();

        abort_unless($this->conversation->hasParticipant('vendor_account', auth('vendor')->id()), 403);

        $this->markRead();
    }

    public function markRead(): void
    {
        ConversationParticipant::where('conversation_id', $this->conversation->id)
            ->where('participant_type', 'vendor_account')
            ->where('participant_id', auth('vendor')->id())
            ->update(['last_read_at' => now()]);
    }

    public function replyToMessage(int $messageId): void
    {
        $this->replyingToId = $messageId;
    }

    public function cancelReply(): void
    {
        $this->replyingToId = null;
    }

    public function sendMessage(): void
    {
        $this->validate([
            'body'          => 'nullable|string|max:3000',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        if (empty(trim($this->body)) && empty($this->attachments)) {
            $this->addError('body', 'Write a message or attach a file.');
            return;
        }

        $message = ConversationMessage::create([
            'tenant_id'           => $this->conversation->tenant_id,
            'conversation_id'     => $this->conversation->id,
            'reply_to_message_id' => $this->replyingToId,
            'sender_type'         => 'vendor_account',
            'sender_id'           => auth('vendor')->id(),
            'body'                => $this->body ?: '',
        ]);

        foreach ($this->attachments as $file) {
            $path = $file->store('conversation-attachments', 'public');
            ConversationMessageAttachment::create([
                'tenant_id'  => $this->conversation->tenant_id,
                'message_id' => $message->id,
                'file_path'  => $path,
                'file_name'  => $file->getClientOriginalName(),
                'file_size'  => $file->getSize(),
                'mime_type'  => $file->getMimeType(),
            ]);
        }

        $replyToSnippet = $message->reply_to_message_id
            ? ConversationMessage::find($message->reply_to_message_id)
            : null;

        $this->conversation->touch();
        $this->body = '';
        $this->attachments = [];
        $this->replyingToId = null;

        $message->load('attachments');

        $this->dispatch('local-message-sent', message: [
            'id'          => $message->id,
            'sender_type' => $message->sender_type,
            'sender_id'   => $message->sender_id,
            'sender_name' => $message->senderName(),
            'body'        => $message->body,
            'rendered'    => $message->renderedBody(),
            'created_at'  => $message->created_at->format('g:i A'),
            'reply_to'    => $replyToSnippet ? [
                'sender_name' => $replyToSnippet->senderName(),
                'snippet'     => \Illuminate\Support\Str::limit($replyToSnippet->body, 60),
            ] : null,
            'attachments' => $message->attachments->map(fn($att) => [
                'url'       => \Illuminate\Support\Facades\Storage::url($att->file_path),
                'name'      => $att->file_name,
                'mime_type' => str_starts_with($att->file_name, 'voice-note-') ? 'audio/webm' : $att->mime_type,
                'is_audio'  => str_starts_with($att->mime_type ?? '', 'audio/') || str_starts_with($att->file_name, 'voice-note-'),
            ])->values(),
        ]);

        broadcast(new \App\Events\ConversationMessageSent($message, $this->conversation->uuid))->toOthers();

        $this->markRead();
    }

    public function render()
    {
        return view('livewire.vendor.conversations.conversation-detail');
    }
}