<?php

namespace App\Livewire\Tenant\Support;

use App\Models\Central\SupportTicket;
use App\Models\Central\SupportTicketAttachment;
use App\Models\Central\SupportTicketMessage;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.tenant')]
class TicketDetail extends Component
{
    use WithToast, WithFileUploads;

    public SupportTicket $ticket;
    public string $reply = '';
    public array  $attachments = [];

    public bool $showRatingForm = false;
    public int  $ratingValue = 5;
    public string $ratingComment = '';

    public function mount(string $uuid): void
    {
        $this->ticket = SupportTicket::where('uuid', $uuid)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->with(['messages.attachments', 'assignedAgent.platformUser'])
            ->firstOrFail();
    }

    public function sendReply(): void
    {
        $this->validate([
            'reply'         => 'required_without:attachments|nullable|string|max:3000',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        if (empty($this->reply) && empty($this->attachments)) {
            $this->addError('reply', 'Write a message or attach a file.');
            return;
        }

        $message = SupportTicketMessage::create([
            'ticket_id'   => $this->ticket->id,
            'sender_type' => 'tenant',
            'sender_id'   => auth()->id(),
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
                'uploaded_by_type' => 'tenant',
                'uploaded_by_id'   => auth()->id(),
            ]);
        }

        // Reopen if it had been resolved and tenant is following up
        if ($this->ticket->status === 'resolved') {
            $this->ticket->update(['status' => 'in_progress']);
        }

        $this->reply = '';
        $this->attachments = [];
        $this->ticket->load('messages.attachments');
        $this->toastSuccess('Message sent.');
    }

    public function showRating(): void
    {
        $this->showRatingForm = true;
    }

    public function submitRating(): void
    {
        $this->validate(['ratingValue' => 'required|integer|min:1|max:5']);

        $this->ticket->update([
            'rating'         => $this->ratingValue,
            'rating_comment' => $this->ratingComment ?: null,
            'rated_at'       => now(),
        ]);

        $this->showRatingForm = false;
        $this->toastSuccess('Thanks for your feedback!');
    }

    public function render()
    {
        return view('livewire.tenant.support.ticket-detail');
    }
}