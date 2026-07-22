<?php

namespace App\Livewire\Tenant\Support;

use App\Models\Central\SupportTicket;
use App\Models\Central\SupportTicketAttachment;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.tenant')]
class CreateTicket extends Component
{
    use WithToast, WithFileUploads;

    public string $subject     = '';
    public string $description = '';
    public string $priority    = 'medium';
    public string $category    = 'technical';
    public array  $attachments = [];

    protected array $categories = [
        'billing'    => 'Billing',
        'technical'  => 'Technical Issue',
        'feature'    => 'Feature Request',
        'account'    => 'Account',
        'other'      => 'Other',
    ];

    public function submit(): void
    {
        $this->validate([
            'subject'       => 'required|string|min:3|max:150',
            'description'   => 'required|string|min:10|max:3000',
            'priority'      => 'required|in:low,medium,high,urgent',
            'category'      => 'required|string',
            'attachments.*' => 'nullable|file|max:10240', // 10MB per file
        ]);

        $tenant = auth()->user()->tenant;

        $ticket = SupportTicket::create([
            'tenant_id'          => $tenant->id,
            'created_by_user_id' => auth()->id(),
            'subject'            => $this->subject,
            'description'        => $this->description,
            'priority'           => $this->priority,
            'category'           => $this->category,
            'status'             => 'open',
            'source'             => 'ticket',
        ]);

        // First message = the description, so it shows in the conversation thread too
        $message = \App\Models\Central\SupportTicketMessage::create([
            'ticket_id'   => $ticket->id,
            'sender_type' => 'tenant',
            'sender_id'   => auth()->id(),
            'message'     => $this->description,
        ]);

        foreach ($this->attachments as $file) {
            $path = $file->store('support-attachments', 'public');
            SupportTicketAttachment::create([
                'ticket_id'        => $ticket->id,
                'message_id'       => $message->id,
                'file_path'        => $path,
                'file_name'        => $file->getClientOriginalName(),
                'file_size'        => $file->getSize(),
                'mime_type'        => $file->getMimeType(),
                'uploaded_by_type' => 'tenant',
                'uploaded_by_id'   => auth()->id(),
            ]);
        }

        $this->toastSuccess('Ticket created. We\'ll get back to you soon.');
        $this->redirect(route('tenant.support.tickets.show', $ticket->uuid), navigate: true);
    }

    public function render()
    {
        return view('livewire.tenant.support.create-ticket', ['categories' => $this->categories]);
    }
}