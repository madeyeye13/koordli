<?php

namespace App\Livewire\Tenant\Conversations;

use App\Models\Central\Client;
use App\Models\Central\VendorAccount;
use App\Models\Tenant\Conversation;
use App\Models\Tenant\ConversationMessage;
use App\Models\Tenant\ConversationMessageAttachment;
use App\Models\Tenant\ConversationParticipant;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.tenant')]
class ConversationDetail extends Component
{
    use WithToast, WithFileUploads;

    public Conversation $conversation;
    public string $body = '';
    public array  $attachments = [];

        public ?int $replyingToId = null;

    public bool  $selectMode = false;
    public array $selectedMessageIds = [];

    public bool $showDeleteConvoModal = false;

    public bool $showAddParticipantForm = false;
    public array $add_participant_keys = [];

    public bool $showRemoveModal = false;
    public ?int $removeParticipantId = null;

    public function mount(string $uuid): void
    {
        $this->conversation = Conversation::where('uuid', $uuid)
            ->with(['event', 'participants', 'messages.attachments', 'messages.replyTo', 'messages.mentions'])
            ->firstOrFail();

        // Mark read on open
        $this->markRead();
    }

        public function isTenantAdmin(): bool
    {
        return auth()->user()->hasRole('company_owner');
    }

    private function myParticipantRow(): ?ConversationParticipant
    {
        return $this->conversation->participants
            ->where('participant_type', 'tenant_user')
            ->where('participant_id', auth()->id())
            ->first();
    }

    public function markRead(): void
    {
        $this->myParticipantRow()?->update(['last_read_at' => now()]);
        broadcast(new \App\Events\ConversationParticipantsChanged($this->conversation->uuid));
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
            'tenant_id'            => auth()->user()->tenant_id,
            'conversation_id'      => $this->conversation->id,
            'reply_to_message_id'  => $this->replyingToId,
            'sender_type'          => 'tenant_user',
            'sender_id'            => auth()->id(),
            'body'                 => $this->body ?: '',
        ]);

        // Resolve @mentions against CURRENT conversation participants only —
        // you can't mention someone who isn't actually in this conversation
        $this->processMentions($message);

        $this->replyingToId = null;

        foreach ($this->attachments as $file) {
            $path = $file->store('conversation-attachments', 'public');
            ConversationMessageAttachment::create([
                'tenant_id'  => auth()->user()->tenant_id,
                'message_id' => $message->id,
                'file_path'  => $path,
                'file_name'  => $file->getClientOriginalName(),
                'file_size'  => $file->getSize(),
                'mime_type'  => $file->getMimeType(),
            ]);
        }

        // Capture the reply snippet BEFORE resetting replyingToId below —
        // computing it after would always yield null (real bug, fixed here)
        $replyToSnippet = $message->reply_to_message_id
            ? ConversationMessage::find($message->reply_to_message_id)
            : null;

        $this->conversation->touch();
        $this->body = '';
        $this->attachments = [];

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

    private function processMentions(ConversationMessage $message): void
    {
        foreach ($this->conversation->participants as $participant) {
            $model = $participant->resolveParticipant();
            if (!$model) continue;

            if (str_contains($this->body, '@' . $model->name)) {
                \App\Models\Tenant\ConversationMessageMention::create([
                    'tenant_id'        => auth()->user()->tenant_id,
                    'message_id'       => $message->id,
                    'participant_type' => $participant->participant_type,
                    'participant_id'   => $participant->participant_id,
                ]);

                // Notify the mentioned person — only tenant_user is wired into
                // the notification engine currently (see Workflow Automation
                // Rule 61 — Client/Vendor notification preferences deferred)
                if ($participant->participant_type === 'tenant_user' && $model->id !== auth()->id()) {
                    app(\App\Services\Notifications\NotificationDispatchService::class)->notify(
                        notifiable: $model,
                        category: 'conversations',
                        notificationType: 'conversation_mention',
                        templateKey: 'conversation_mention',
                        placeholders: [
                            'user_name'  => $model->name,
                            'task_name'  => auth()->user()->name,
                            'event_name' => $this->conversation->event->name,
                        ],
                        priority: 'high',
                        actionUrl: route('tenant.conversations.show', $this->conversation->uuid),
                        actionLabel: 'View Conversation',
                        subject: $message,
                        tenantId: auth()->user()->tenant_id,
                    );
                }
            }
        }
    }

        // ── Message Deletion ──────────────────────────────────────────
    public function toggleSelectMode(): void
    {
        $this->selectMode = !$this->selectMode;
        $this->selectedMessageIds = [];
    }

    public function toggleMessageSelected(int $messageId): void
    {
        if (in_array($messageId, $this->selectedMessageIds)) {
            $this->selectedMessageIds = array_values(array_diff($this->selectedMessageIds, [$messageId]));
        } else {
            $this->selectedMessageIds[] = $messageId;
        }
    }

    public function bulkDeleteForMe(): void
    {
        foreach ($this->selectedMessageIds as $id) {
            \App\Models\Tenant\ConversationMessageDeletion::firstOrCreate([
                'tenant_id'        => auth()->user()->tenant_id,
                'message_id'       => $id,
                'participant_type' => 'tenant_user',
                'participant_id'   => auth()->id(),
            ]);
        }

        $this->selectedMessageIds = [];
        $this->selectMode = false;
        $this->conversation->load('messages.attachments', 'messages.replyTo', 'messages.mentions');
        $this->toastSuccess('Deleted for you.');
    }

    public function bulkDeleteForEveryone(): void
    {
        $messages = ConversationMessage::whereIn('id', $this->selectedMessageIds)->get();

        foreach ($messages as $message) {
            if (!$message->canDeleteForEveryone('tenant_user', auth()->id())) continue;

            $message->update(['deleted_at' => now(), 'body' => '']);
            broadcast(new \App\Events\ConversationMessageDeleted($this->conversation->uuid, $message->id));
        }

        $this->selectedMessageIds = [];
        $this->selectMode = false;
        $this->conversation->load('messages.attachments', 'messages.replyTo', 'messages.mentions');
        $this->toastSuccess('Deleted for everyone.');
    }

    // ── Conversation Deletion (tenant/admin only) ───────────────────
    public function confirmDeleteConversation(): void
    {
        if (!$this->isTenantAdmin()) {
            $this->toastError('Only the account owner can delete a conversation.');
            return;
        }
        $this->showDeleteConvoModal = true;
    }

    public function deleteConversation(): void
    {
        if (!$this->isTenantAdmin()) {
            $this->toastError('Only the account owner can delete a conversation.');
            return;
        }

        $eventSlug = $this->conversation->event->slug;
        $uuid = $this->conversation->uuid;

        broadcast(new \App\Events\ConversationDeleted($uuid, $eventSlug));

        $this->conversation->delete(); // cascades to messages/attachments/participants/mentions/deletions

        $this->redirect(route('tenant.events.show', $eventSlug), navigate: true);
    }

    public function showAddParticipant(): void
    {
        $this->add_participant_keys = [];
        $this->showAddParticipantForm = true;
    }

    public function addParticipants(): void
    {
        foreach ($this->add_participant_keys as $key) {
            [$type, $id] = explode(':', $key);

            $exists = ConversationParticipant::where('conversation_id', $this->conversation->id)
                ->where('participant_type', $type)
                ->where('participant_id', $id)
                ->whereNull('left_at')
                ->exists();

            if ($exists) continue;

            ConversationParticipant::create([
                'tenant_id'        => auth()->user()->tenant_id,
                'conversation_id'  => $this->conversation->id,
                'participant_type' => $type,
                'participant_id'   => (int) $id,
                'added_by'         => auth()->id(),
            ]);
        }

        $this->conversation->load('participants');
        $this->showAddParticipantForm = false;
        $this->toastSuccess('Participant(s) added.');
        broadcast(new \App\Events\ConversationParticipantsChanged($this->conversation->uuid));
    }

    public function confirmRemoveParticipant(int $participantId): void
    {
        $this->removeParticipantId = $participantId;
        $this->showRemoveModal = true;
    }

    public function removeParticipant(): void
    {
        if (!$this->isTenantAdmin()) {
            $this->toastError('Only the account owner can remove participants.');
            $this->showRemoveModal = false;
            return;
        }

        ConversationParticipant::find($this->removeParticipantId)?->update(['left_at' => now()]);
        $this->conversation->load('participants');
        $this->showRemoveModal = false;
        $this->toastSuccess('Participant removed.');
        broadcast(new \App\Events\ConversationParticipantsChanged($this->conversation->uuid));
    }

        public function handleMessageDeleted(): void
    {
        $this->conversation->load('messages.attachments', 'messages.replyTo', 'messages.mentions');
    }

    public function refreshParticipants(): void
    {
        $this->conversation->load('participants');
    }

        public function render()
    {
        $visibleMessages = $this->conversation->messages->reject(
            fn($msg) => $msg->isHiddenFor('tenant_user', auth()->id())
        );

        $participantNames = [];
        foreach ($this->conversation->participants as $p) {
            $model = $p->resolveParticipant();
            $participantNames[$p->id] = [
                'name' => $model?->name ?? 'Unknown',
                'type' => $p->participant_type,
            ];
        }

        // Eligible-to-add: everyone with EventTeam/VendorEventAssignment/ClientEventAccess for this event,
        // minus who's already an active participant — same eligibility source as EventDetail's creation form
        $event = $this->conversation->event;
        $alreadyIn = $this->conversation->participants->map(fn($p) => $p->participant_type . ':' . $p->participant_id)->toArray();

        $eligible = collect();
        foreach ($event->team()->with('user')->get() as $team) {
            $key = 'tenant_user:' . $team->user_id;
            if (!in_array($key, $alreadyIn)) {
                $eligible->push(['key' => $key, 'label' => ($team->user->name ?? 'Unknown') . ' (Staff)']);
            }
        }
        foreach (\App\Models\Tenant\ClientEventAccess::where('event_id', $event->id)->with('client')->get() as $access) {
            $key = 'client:' . $access->client_id;
            if ($access->client && !in_array($key, $alreadyIn)) {
                $eligible->push(['key' => $key, 'label' => $access->client->name . ' (Client)']);
            }
        }
        foreach ($event->vendorAssignments as $assignment) {
            $vendorAccount = VendorAccount::where('vendor_id', $assignment->vendor_id)->first();
            if ($vendorAccount) {
                $key = 'vendor_account:' . $vendorAccount->id;
                if (!in_array($key, $alreadyIn)) {
                    $eligible->push(['key' => $key, 'label' => $vendorAccount->business_name . ' (Vendor)']);
                }
            }
        }

        return view('livewire.tenant.conversations.conversation-detail', [
            'participantNames' => $participantNames,
            'eligibleToAdd'    => $eligible,
            'visibleMessages'  => $visibleMessages,
            'isAdmin'          => $this->isTenantAdmin(),
        ]);
    }
}