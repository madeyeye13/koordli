<?php

namespace App\Livewire\Platform\Support;

use App\Jobs\SendSupportTicketReplyJob;
use App\Models\Central\SupportAgent;
use App\Models\Central\SupportTicket;
use App\Models\Central\SupportTicketAttachment;
use App\Models\Central\SupportTicketMessage;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.platform')]
class PlatformTicketDetail extends Component
{
    use WithToast, WithFileUploads;

    public SupportTicket $ticket;
    public string $reply = '';
    public array  $attachments = [];

    public bool $showHandoffModal = false;
    public ?int $handoffAgentId   = null;
    public string $handoffReason  = '';

    public bool $showDeleteModal = false;

    public function mount(string $uuid): void
    {
        abort_unless(auth('platform')->user()?->can('support.tickets.view'), 403);

        $this->ticket = SupportTicket::where('uuid', $uuid)
            ->with(['messages.attachments', 'tenant', 'assignedAgent.platformUser', 'assignmentHistory.toAgent.platformUser'])
            ->firstOrFail();
    }

    /**
     * True ownership gate for agents specifically — managers/admins/owners
     * (who hold support.agents.manage) can act on ANY ticket; a plain
     * agent can only act on a ticket currently assigned to them, or an
     * unassigned one (to claim it). support.tickets.manage alone can't
     * express this distinction, since both roles share that permission.
     */
    private function canActOnThisTicket(): bool
    {
        $user = auth('platform')->user();
        if (!$user->can('support.tickets.manage')) return false;
        if ($user->can('support.agents.manage')) return true; // managers/admins: unrestricted

        $agent = SupportAgent::where('platform_user_id', $user->id)->first();
        return $agent && (
            $this->ticket->assigned_agent_id === $agent->id
            || $this->ticket->assigned_agent_id === null
        );
    }

    public function endChatByAgent(): void
    {
        if (!$this->canActOnThisTicket()) { $this->toastError('You are not assigned to this ticket.'); return; }

        $chatSession = \App\Models\Central\SupportChatSession::where('ticket_id', $this->ticket->id)->first();

        if ($this->ticket->source !== 'chat' || !$chatSession) {
            return;
        }

        $chatSession->update(['status' => 'ended', 'ended_by' => 'agent', 'ended_at' => now()]);
        $this->ticket->update(['status' => 'resolved', 'resolved_at' => now()]);

        if ($this->ticket->assignedAgent) {
            $this->ticket->assignedAgent->recalculateActiveChatCount();
        }

        $msg = \App\Models\Central\SupportTicketMessage::create([
            'ticket_id'   => $this->ticket->id,
            'sender_type' => 'system',
            'message'     => 'Chat ended by ' . auth('platform')->user()->name . '. Should you wish to continue this conversation, feel free to reach out to us again or raise a support ticket.',
        ]);
        broadcast(new \App\Events\SupportChatMessageSent($msg, $this->ticket->uuid));
        broadcast(new \App\Events\SupportChatEnded($this->ticket->uuid));

        $this->ticket->refresh();
        $this->toastSuccess('Chat ended.');
    }


    public function claimTicket(): void
    {
        abort_unless(auth('platform')->user()?->can('support.tickets.manage'), 403);

        $agent = SupportAgent::where('platform_user_id', auth('platform')->id())->first();

        if (!$agent) {
            $this->toastError('You must be a registered support agent (toggle "Available for Support" first).');
            return;
        }

        $this->ticket->assignTo($agent, $this->ticket->assignedAgent, 'Claimed from inbox', auth('platform')->id());
        $this->ticket->refresh();

        if ($this->ticket->source === 'chat' && $this->ticket->chatSession) {
            broadcast(new \App\Events\SupportChatAccepted($this->ticket->uuid, $agent));
        }

        $this->toastSuccess('Ticket assigned to you.');
    }

    public function refreshMessages(): void
    {
        $this->ticket->load('messages.attachments');
    }


    public function sendReply(): void
    {
        if (!$this->canActOnThisTicket()) { $this->toastError('You are not assigned to this ticket.'); return; }

        $this->validate([
            'reply'         => 'nullable|string|max:3000',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        if (empty($this->reply) && empty($this->attachments)) {
            $this->addError('reply', 'Write a message or attach a file.');
            return;
        }

        $agent = SupportAgent::where('platform_user_id', auth('platform')->id())->first();

        $message = SupportTicketMessage::create([
            'ticket_id'   => $this->ticket->id,
            'sender_type' => 'agent',
            'sender_id'   => $agent?->id,
            'message'     => $this->reply ?: '(attachment)',
        ]);

        foreach ($this->attachments as $file) {
            $path = $file->store('support-attachments', 'public');
            SupportTicketAttachment::create([
                'ticket_id'        => $this->ticket->id,
                'message_id'       => $message->id,
                'file_path'        => $path,
                'file_name'        => $file->getClientOriginalName(),
                'file_size'        => $file->getSize(),
                'mime_type'        => $file->getMimeType(),
                'uploaded_by_type' => 'agent',
                'uploaded_by_id'   => $agent?->id,
            ]);
        }

        if ($this->ticket->status === 'open') {
            $this->ticket->update(['status' => 'in_progress']);
        }

        // Query the chat session's CURRENT status directly — never trust $this->ticket->chatSession,
        // since Livewire caches loaded relations across requests and this can go stale mid-conversation
        // (e.g. the tenant flips it to 'active' in a completely separate request).
        $currentChatStatus = $this->ticket->source === 'chat'
            ? \App\Models\Central\SupportChatSession::where('ticket_id', $this->ticket->id)->value('status')
            : null;

        // Live chat: broadcast instantly, skip the email (tenant is right there watching)
        if ($currentChatStatus === 'active') {
            broadcast(new \App\Events\SupportChatMessageSent($message, $this->ticket->uuid));
        } else {
            // Async ticket: notify tenant by email
            $tenantUser = \App\Models\Tenant\User::withoutGlobalScopes()->find($this->ticket->created_by_user_id);
            if ($tenantUser?->email) {
                SendSupportTicketReplyJob::dispatch(
                    $tenantUser->email,
                    $tenantUser->name,
                    $this->ticket->subject,
                    $this->reply ?: 'You have a new attachment on your support ticket.',
                    route('tenant.support.tickets.show', $this->ticket->uuid),
                );
            }
        }

        $this->reply = '';
        $this->attachments = [];
        $this->ticket->load('messages.attachments');
        $this->toastSuccess('Reply sent.');
    }

    public function updateStatus(string $status): void
    {
        if (!$this->canActOnThisTicket()) { $this->toastError('You are not assigned to this ticket.'); return; }

        $wasActive = !in_array($this->ticket->status, ['resolved', 'closed']);
        $becomingInactive = in_array($status, ['resolved', 'closed']);

        $this->ticket->update([
            'status'      => $status,
            'resolved_at' => $status === 'resolved' ? now() : $this->ticket->resolved_at,
        ]);

        if ($wasActive && $becomingInactive && $this->ticket->assignedAgent) {
            $this->ticket->assignedAgent->recalculateActiveChatCount();
        }

        $this->ticket->refresh();
        $this->toastSuccess('Status updated to ' . $this->ticket->statusLabel() . '.');
    }


    public function confirmDelete(): void
    {
        abort_unless(auth('platform')->user()?->can('support.tickets.delete'), 403);
        $this->showDeleteModal = true;
    }

    public function deleteTicket(): void
    {
        abort_unless(auth('platform')->user()?->can('support.tickets.delete'), 403);

        $this->ticket->delete(); // cascades to messages/attachments/history/chat session via FK constraints
        $this->toastSuccess('Ticket permanently deleted.');
        $this->redirect(route('platform.support.tickets'), navigate: true);
    }

    public function archiveTicket(): void
    {
        if (!$this->canActOnThisTicket()) { $this->toastError('You are not assigned to this ticket.'); return; }

        $this->ticket->update(['status' => 'closed']);
        $this->ticket->refresh();
        $this->toastSuccess('Ticket archived (closed).');
    }

    public function openHandoff(): void
    {
        abort_unless(auth('platform')->user()?->can('support.tickets.handoff'), 403);
        $this->showHandoffModal = true;
    }

    public function handoff(): void
    {
        abort_unless(auth('platform')->user()?->can('support.tickets.handoff'), 403);
        $this->validate(['handoffAgentId' => 'required|exists:support_agents,id']);

        $newAgent = SupportAgent::find($this->handoffAgentId);
        $this->ticket->assignTo($newAgent, $this->ticket->assignedAgent, $this->handoffReason ?: null, auth('platform')->id());
        $this->ticket->refresh();

        $this->showHandoffModal = false;
        $this->toastSuccess('Ticket handed off to ' . $newAgent->platformUser->name . '.');
    }

    public function render()
    {
        return view('livewire.platform.support.platform-ticket-detail', [
            'otherAgents' => SupportAgent::where('platform_user_id', '!=', auth('platform')->id())->with('platformUser')->get(),
        ]);
    }
}