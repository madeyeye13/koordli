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
        $this->ticket = SupportTicket::where('uuid', $uuid)
            ->with(['messages.attachments', 'tenant', 'assignedAgent.platformUser', 'assignmentHistory.toAgent.platformUser'])
            ->firstOrFail();
    }

    public function claimTicket(): void
    {
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

        // Live chat: broadcast instantly, skip the email (tenant is right there watching)
        if ($this->ticket->source === 'chat' && $this->ticket->chatSession?->status === 'active') {
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
        $this->ticket->update([
            'status'      => $status,
            'resolved_at' => $status === 'resolved' ? now() : $this->ticket->resolved_at,
        ]);
        $this->ticket->refresh();
        $this->toastSuccess('Status updated to ' . $this->ticket->statusLabel() . '.');
    }


    public function confirmDelete(): void
    {
        $this->showDeleteModal = true;
    }

    public function deleteTicket(): void
    {
        $this->ticket->delete(); // cascades to messages/attachments/history/chat session via FK constraints
        $this->toastSuccess('Ticket permanently deleted.');
        $this->redirect(route('platform.support.tickets'), navigate: true);
    }

    public function archiveTicket(): void
    {
        $this->ticket->update(['status' => 'closed']);
        $this->ticket->refresh();
        $this->toastSuccess('Ticket archived (closed).');
    }

    public function openHandoff(): void
    {
        $this->showHandoffModal = true;
    }

    public function handoff(): void
    {
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