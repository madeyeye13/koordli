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

    public bool $showShareMoodboardModal = false;
    public ?int $sharingMoodboardId = null;

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

    private function canDeleteConversation(): bool
    {
        return app(\App\Services\PermissionService::class)->userCan(auth()->user(), 'conversations.delete');
    }

    private function canManageParticipants(): bool
    {
        return app(\App\Services\PermissionService::class)->userCan(auth()->user(), 'conversations.manage_participants');
    }

    /**
     * @deprecated kept for backward compatibility — previously the ONLY gate
     * (hasRole('company_owner')) covering both delete and remove-participant.
     * Now split into two separate permissions; this returns true only if
     * BOTH are held, matching prior behavior exactly for the default
     * company_owner-only case.
     */
    public function isTenantAdmin(): bool
    {
        return $this->canDeleteConversation() && $this->canManageParticipants();
    }

    private function myParticipantRow(): ?ConversationParticipant
    {
        return $this->conversation->participants
            ->where('participant_type', 'tenant_user')
            ->where('participant_id', auth()->id())
            ->first();
    }

        /**
     * The ID of the current user's own most recent message — read receipts
     * are only shown under this one, not every message (matches WhatsApp).
     */
    public function myLastMessageId(): ?int
    {
        return $this->conversation->messages
            ->where('sender_type', 'tenant_user')
            ->where('sender_id', auth()->id())
            ->last()?->id;
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

    public function openShareMoodboard(): void
    {
        $this->showShareMoodboardModal = true;
    }

    public function selectMoodboardToShare(int $moodboardId): void
    {
        $this->sharingMoodboardId = $moodboardId;
        $this->showShareMoodboardModal = false;
    }

    public function cancelShareMoodboard(): void
    {
        $this->sharingMoodboardId = null;
    }

    /**
     * True only if this conversation has NO client participant at all,
     * OR the client-participant case where the moodboard is genuinely
     * safe to show them — i.e. moodboards restricted to
     * is_client_visible = true whenever any client is present. A staff-
     * only or staff+vendor conversation may share any moodboard
     * regardless of its client-visibility flag.
     */
    private function hasClientParticipant(): bool
    {
        return $this->conversation->participants->contains(fn($p) => $p->participant_type === 'client');
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

        if (empty(trim($this->body)) && empty($this->attachments) && !$this->sharingMoodboardId) {
            $this->addError('body', 'Write a message, attach a file, or share a moodboard.');
            return;
        }

        $message = ConversationMessage::create([
            'tenant_id'            => auth()->user()->tenant_id,
            'conversation_id'      => $this->conversation->id,
            'reply_to_message_id'  => $this->replyingToId,
            'shared_moodboard_id'  => $this->sharingMoodboardId,
            'sender_type'          => 'tenant_user',
            'sender_id'            => auth()->id(),
            'body'                 => $this->body ?: '',
        ]);

        $this->sharingMoodboardId = null;

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
            'shared_moodboard_html' => $message->sharedMoodboardCardHtml(alignRight: true),
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

        \App\Services\Conversations\ConversationNotifier::notifyOthers($this->conversation, $message);

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
        if (!$this->canDeleteConversation()) {
            $this->toastError('You do not have permission to delete this conversation.');
            return;
        }
        $this->showDeleteConvoModal = true;
    }

    public function deleteConversation(): void
    {
        if (!$this->canDeleteConversation()) {
            $this->toastError('You do not have permission to delete this conversation.');
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

            \App\Services\Conversations\ConversationNotifier::notifyAdded($this->conversation, $type, (int) $id, auth()->user()->name);
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
        if (!$this->canManageParticipants()) {
            $this->toastError('You do not have permission to remove participants.');
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
        $this->conversation->load('participants', 'messages.attachments', 'messages.replyTo', 'messages.mentions');
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

        $moodboardsQuery = \App\Models\Tenant\Moodboard::where('event_id', $event->id)
            ->where('is_template', false);

        // Client-safety gate: if ANY participant in this conversation is a
        // client, only client-visible moodboards are selectable — prevents
        // staff from accidentally exposing a private/internal board to a
        // client who's part of this conversation.
        if ($this->hasClientParticipant()) {
            $moodboardsQuery->where('is_client_visible', true);
        }

        $shareableMoodboards = $moodboardsQuery->orderByDesc('updated_at')->get();

        return view('livewire.tenant.conversations.conversation-detail', [
            'participantNames'    => $participantNames,
            'eligibleToAdd'       => $eligible,
            'visibleMessages'     => $visibleMessages,
            'isAdmin'             => $this->isTenantAdmin(),
            'shareableMoodboards' => $shareableMoodboards,
        ]);
    }
}