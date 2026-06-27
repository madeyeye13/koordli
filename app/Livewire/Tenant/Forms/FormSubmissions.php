<?php

namespace App\Livewire\Tenant\Forms;

use App\Models\Tenant\ConsultationBooking;
use App\Models\Tenant\Form;
use App\Models\Tenant\FormSubmission;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class FormSubmissions extends Component
{
    use WithToast;

    public Form   $form;
    public string $statusFilter = '';
    public string $activeTab    = 'submissions';

    public bool $showDeleteModal = false;
    public ?int $deleteId        = null;

    public function mount(int $id): void
    {
        $this->form = Form::with('fields')->findOrFail($id);
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId        = $id;
        $this->showDeleteModal = true;
    }

    public function deleteSubmission(): void
    {
        FormSubmission::find($this->deleteId)?->delete();
        $this->showDeleteModal = false;
        $this->deleteId        = null;
        $this->toastSuccess('Submission deleted.');
    }

    public function updateBookingStatus(int $bookingId, string $status): void
    {
        ConsultationBooking::find($bookingId)?->update(['status' => $status]);
        $this->toastSuccess('Booking status updated.');
    }

    public function render()
    {
        $submissions = FormSubmission::where('form_id', $this->form->id)
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->with(['values.field'])
            ->orderByDesc('submitted_at')
            ->get();

        $bookings = $this->form->type === 'consultation'
            ? ConsultationBooking::where('form_id', $this->form->id)
                ->orderByDesc('booking_date')
                ->get()
            : collect();

        $stats = [
            'total'     => $submissions->count(),
            'new'       => $submissions->where('status', 'new')->count(),
            'confirmed' => $bookings->where('status', 'confirmed')->count(),
            'pending'   => $bookings->where('status', 'pending')->count(),
        ];

        return view('livewire.tenant.forms.form-submissions', compact('submissions', 'bookings', 'stats'));
    }
}