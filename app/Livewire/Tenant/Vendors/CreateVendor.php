<?php

namespace App\Livewire\Tenant\Vendors;

use App\Models\Tenant\Vendor;
use App\Models\Tenant\VendorCategory;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class CreateVendor extends Component
{
    use WithToast;

    public string $name         = '';
    public ?int   $vendor_category_id = null;
    public string $contact_name = '';
    public string $phone        = '';
    public string $email        = '';
    public string $website      = '';
    public string $instagram    = '';
    public string $description  = '';
    public string $notes        = '';
    public ?int   $rating       = null;
    public bool   $is_preferred = false;
    public bool   $is_active    = true;

    public ?int    $vendorId = null;
    public ?Vendor $vendor   = null;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->vendor             = Vendor::findOrFail($id);
            $this->vendorId           = $id;
            $this->name               = $this->vendor->name;
            $this->vendor_category_id = $this->vendor->vendor_category_id;
            $this->contact_name       = $this->vendor->contact_name ?? '';
            $this->phone              = $this->vendor->phone ?? '';
            $this->email              = $this->vendor->email ?? '';
            $this->website            = $this->vendor->website ?? '';
            $this->instagram          = $this->vendor->instagram ?? '';
            $this->description        = $this->vendor->description ?? '';
            $this->notes              = $this->vendor->notes ?? '';
            $this->rating             = $this->vendor->rating;
            $this->is_preferred       = $this->vendor->is_preferred;
            $this->is_active          = $this->vendor->is_active;
        }
    }

    public function save(): void
    {
        $this->validate([
            'name'               => 'required|string|min:2|max:200',
            'vendor_category_id' => 'nullable|exists:vendor_categories,id',
            'contact_name'       => 'nullable|string|max:200',
            'phone'              => 'nullable|string|max:30',
            'email'              => 'nullable|email|max:200',
            'website'            => 'nullable|url|max:300',
            'instagram'          => 'nullable|string|max:100',
            'description'        => 'nullable|string|max:1000',
            'notes'              => 'nullable|string|max:1000',
            'rating'             => 'nullable|integer|min:1|max:5',
        ]);

        $data = [
            'tenant_id'          => auth()->user()->tenant_id,
            'name'               => $this->name,
            'vendor_category_id' => $this->vendor_category_id,
            'contact_name'       => $this->contact_name ?: null,
            'phone'              => $this->phone ?: null,
            'email'              => $this->email ?: null,
            'website'            => $this->website ?: null,
            'instagram'          => $this->instagram ?: null,
            'description'        => $this->description ?: null,
            'notes'              => $this->notes ?: null,
            'rating'             => $this->rating,
            'is_preferred'       => $this->is_preferred,
            'is_active'          => $this->is_active,
        ];

        if ($this->vendor) {
            $this->vendor->update($data);
            $this->toastSuccess('Vendor updated.');
            $this->redirect(route('tenant.vendors.show', $this->vendor->id), navigate: true);
        } else {
            $vendor = Vendor::create($data);
            $this->toastSuccess('Vendor added to directory.');
            $this->redirect(route('tenant.vendors.show', $vendor->id), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.tenant.vendors.create-vendor', [
            'categories' => VendorCategory::orderBy('sort_order')->get(),
        ]);
    }
}