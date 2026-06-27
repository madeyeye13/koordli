<?php

namespace App\Livewire\Tenant\Forms;

use App\Models\Tenant\Form;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class FormList extends Component
{
    use WithToast;

    public string $search    = '';
    public string $typeFilter = '';

    public bool   $showDeleteModal = false;
    public ?int   $deleteId        = null;
    public string $deleteName      = '';

    public function confirmDelete(int $id, string $name): void
    {
        $this->deleteId        = $id;
        $this->deleteName      = $name;
        $this->showDeleteModal = true;
    }

    public function deleteForm(): void
    {
        Form::find($this->deleteId)?->delete();
        $this->showDeleteModal = false;
        $this->deleteId        = null;
        $this->toastSuccess('Form deleted.');
    }

    public function toggleStatus(int $id): void
    {
        $form = Form::find($id);
        if (!$form) return;
        $newStatus = $form->status === 'active' ? 'inactive' : 'active';
        $form->update(['status' => $newStatus]);
        $this->toastSuccess($newStatus === 'active' ? 'Form activated.' : 'Form deactivated.');
    }

    public function render()
    {
        $forms = Form::query()
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', '%' . $this->search . '%'))
            ->when($this->typeFilter, fn($q) =>
                $q->where('type', $this->typeFilter))
            ->withCount('submissions')
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.tenant.forms.form-list', compact('forms'));
    }
}