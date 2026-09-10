<?php

namespace App\Livewire\Tenant\Contracts;

use App\Models\Tenant\VendorContractTemplate;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class ContractTemplates extends Component
{
    use WithToast;

    public bool   $showForm = false;
    public ?int   $editId   = null;
    public string $name     = '';
    public string $category = '';
    public string $content  = '';
    public bool   $is_active = true;

    public bool $showDeleteModal = false;
    public ?int $deleteId        = null;

    public function showCreate(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'contracts.manage')) {
            $this->toastError('You do not have permission to manage contract templates.');
            return;
        }

        $this->reset(['editId', 'name', 'category', 'content']);
        $this->is_active = true;
        $this->content   = $this->defaultTemplate();
        $this->showForm  = true;
    }

    public function showEdit(int $id): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'contracts.manage')) {
            $this->toastError('You do not have permission to manage contract templates.');
            return;
        }

        $template = VendorContractTemplate::find($id);
        if (!$template) return;

        $this->editId    = $id;
        $this->name      = $template->name;
        $this->category  = $template->category ?? '';
        $this->content   = $template->content;
        $this->is_active = $template->is_active;
        $this->showForm  = true;
    }

    public function save(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'contracts.manage')) {
            $this->toastError('You do not have permission to manage contract templates.');
            return;
        }

        $this->validate([
            'name'    => 'required|string|min:2|max:150',
            'content' => 'required|string|min:20',
        ]);

        $data = [
            'tenant_id' => auth()->user()->tenant_id,
            'name'      => $this->name,
            'category'  => $this->category ?: null,
            'content'   => $this->content,
            'is_active' => $this->is_active,
        ];

        if ($this->editId) {
            VendorContractTemplate::find($this->editId)?->update($data);
            $this->toastSuccess('Template updated.');
        } else {
            $data['created_by'] = auth()->id();
            VendorContractTemplate::create($data);
            $this->toastSuccess('Template created.');
        }

        $this->showForm = false;
    }

    public function confirmDelete(int $id): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'contracts.manage')) {
            $this->toastError('You do not have permission to manage contract templates.');
            return;
        }

        $this->deleteId        = $id;
        $this->showDeleteModal = true;
    }

    public function delete(?int $id = null): void
    {
        if (!is_null($id)) {
            $this->deleteId = $id;
        }

        if (!app(PermissionService::class)->userCan(auth()->user(), 'contracts.manage')) {
            $this->toastError('You do not have permission to manage contract templates.');
            $this->showDeleteModal = false;
            return;
        }

        VendorContractTemplate::find($this->deleteId)?->delete();
        $this->showDeleteModal = false;
        $this->deleteId        = null;
        $this->toastSuccess('Template deleted.');
    }

    public function toggleActive(int $id): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'contracts.manage')) {
            $this->toastError('You do not have permission to manage contract templates.');
            return;
        }

        $template = VendorContractTemplate::find($id);
        $template?->update(['is_active' => !$template->is_active]);
    }

    private function defaultTemplate(): string
    {
        return "<h2>Vendor Service Agreement</h2>
<p>This agreement is entered into on {{generated_date}} between <strong>{{company_name}}</strong> (\"the Client\") and <strong>{{vendor_name}}</strong> (\"the Vendor\").</p>

<h3>1. Services</h3>
<p>The Vendor agrees to provide {{service_category}} services for the event: <strong>{{event_name}}</strong>, scheduled for {{event_date}} at {{event_location}}.</p>

<h3>2. Payment</h3>
<p>The total contract amount is <strong>{{contract_amount}}</strong>. Payment schedule: {{payment_schedule}}.</p>

<h3>3. Terms</h3>
<p>Both parties agree to fulfill their obligations as outlined in this agreement. Any changes must be agreed to in writing by both parties.</p>

<h3>4. Signatures</h3>
<p>Planner: {{planner_name}}<br>Date: {{generated_date}}</p>";
    }

    public function render()
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'contracts.manage'),
            403
        );

        $templates = VendorContractTemplate::orderByDesc('created_at')->get();
        return view('livewire.tenant.contracts.contract-templates', compact('templates'));
    }
}