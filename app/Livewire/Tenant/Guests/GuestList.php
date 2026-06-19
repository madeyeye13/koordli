<?php

namespace App\Livewire\Tenant\Guests;

use App\Models\Tenant\Event;
use App\Models\Tenant\Guest;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;

#[Layout('layouts.tenant')]
class GuestList extends Component
{
    use WithToast;

    public Event $event;

    // Add guest form
    public bool   $showAddForm  = false;
    public string $name         = '';
    public string $email        = '';
    public string $phone        = '';
    public string $category     = '';
    public string $notes        = '';

    // Edit
    public ?int   $editId       = null;
    public string $editName     = '';
    public string $editEmail    = '';
    public string $editPhone    = '';
    public string $editCategory = '';
    public string $editNotes    = '';

    // Delete
    public bool $showDeleteModal = false;
    public ?int $deleteId        = null;

    // Filters
    public string $search         = '';
    public string $statusFilter   = '';
    public string $categoryFilter = '';

    // Guest count
    public string $expectedGuests  = '';
    public bool   $editingCount    = false;

    public function mount(string $slug): void
    {
        $this->event = Event::where('slug', $slug)->firstOrFail();
        $this->expectedGuests = $this->event->max_guests ?? '';
    }

    public function toggleAddForm(): void
    {
        $this->showAddForm = !$this->showAddForm;
        if (!$this->showAddForm) {
            $this->reset(['name', 'email', 'phone', 'category', 'notes']);
        }
    }

    public function addGuest(): void
    {
        $this->validate([
            'name'     => 'required|string|min:2|max:100',
            'email'    => 'nullable|email|max:150',
            'phone'    => 'nullable|string|max:20',
            'category' => 'nullable|string|max:50',
            'notes'    => 'nullable|string|max:500',
        ]);

        Guest::create([
            'tenant_id' => auth()->user()->tenant_id,
            'event_id'  => $this->event->id,
            'name'      => $this->name,
            'email'     => $this->email ?: null,
            'phone'     => $this->phone ?: null,
            'category'  => $this->category ?: null,
            'notes'     => $this->notes ?: null,
            'rsvp_status' => 'pending',
        ]);

        $this->reset(['name', 'email', 'phone', 'category', 'notes']);
        $this->showAddForm = false;
        $this->toastSuccess('Guest added.');
    }

    public function startEdit(int $id): void
    {
        $guest = Guest::find($id);
        if (!$guest) return;
        $this->editId       = $id;
        $this->editName     = $guest->name;
        $this->editEmail    = $guest->email ?? '';
        $this->editPhone    = $guest->phone ?? '';
        $this->editCategory = $guest->category ?? '';
        $this->editNotes    = $guest->notes ?? '';
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editName'  => 'required|string|min:2|max:100',
            'editEmail' => 'nullable|email|max:150',
            'editPhone' => 'nullable|string|max:20',
        ]);

        Guest::find($this->editId)?->update([
            'name'     => $this->editName,
            'email'    => $this->editEmail ?: null,
            'phone'    => $this->editPhone ?: null,
            'category' => $this->editCategory ?: null,
            'notes'    => $this->editNotes ?: null,
        ]);

        $this->reset(['editId', 'editName', 'editEmail', 'editPhone', 'editCategory', 'editNotes']);
        $this->toastSuccess('Guest updated.');
    }

    public function cancelEdit(): void
    {
        $this->reset(['editId', 'editName', 'editEmail', 'editPhone', 'editCategory', 'editNotes']);
    }

    
    public function updateRsvpStatus(int $id, string $status): void
    {
        Guest::find($id)?->update(['rsvp_status' => $status]);
        $this->toastSuccess('RSVP status updated.');
    }

    
    public function checkIn(int $id): void
    {
        $guest = Guest::find($id);
        if (!$guest) return;
        $guest->update([
            'checked_in'    => !$guest->checked_in,
            'checked_in_at' => !$guest->checked_in ? now() : null,
        ]);
        $this->toastSuccess($guest->checked_in ? 'Guest checked in.' : 'Check-in removed.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId        = $id;
        $this->showDeleteModal = true;
    }

    public function deleteGuest(): void
    {
        Guest::find($this->deleteId)?->delete();
        $this->showDeleteModal = false;
        $this->deleteId        = null;
        $this->toastSuccess('Guest removed.');
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deleteId        = null;
    }

    public function saveGuestCount(): void
    {
        $this->validate([
            'expectedGuests' => 'nullable|integer|min:0|max:999999',
        ]);

        $this->event->update(['max_guests' => $this->expectedGuests ?: null]);
        $this->editingCount = false;
        $this->toastSuccess('Expected guest count updated.');
    }

    public function render()
    {
        $guests = Guest::where('event_id', $this->event->id)
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
            )
            ->when($this->statusFilter, fn($q) =>
                $q->where('rsvp_status', $this->statusFilter)
            )
            ->when($this->categoryFilter, fn($q) =>
                $q->where('category', $this->categoryFilter)
            )
            ->orderBy('name')
            ->get();

        $categories = Guest::where('event_id', $this->event->id)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        $stats = [
            'total'     => $guests->count(),
            'confirmed' => $guests->where('rsvp_status', 'confirmed')->count(),
            'declined'  => $guests->where('rsvp_status', 'declined')->count(),
            'pending'   => $guests->where('rsvp_status', 'pending')->count(),
            'checked_in'=> $guests->where('checked_in', true)->count(),
        ];

        return view('livewire.tenant.guests.guest-list', compact('guests', 'stats', 'categories'));
    }
}