<?php

namespace App\Livewire\Public;

use App\Jobs\SendVendorApplicationReceivedJob;
use App\Models\Central\Tenant;
use App\Models\Tenant\VendorApplication;
use App\Models\Tenant\VendorCategory;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Stevebauman\Location\Facades\Location;

#[Layout('layouts.auth')]
class VendorRegister extends Component
{
    public Tenant $tenant;

    public string $business_name        = '';
    public string $contact_name         = '';
    public string $email                = '';
    public string $phone                = '';
    public string $service_description  = '';
    public string $instagram            = '';
    public string $website              = '';
    public ?int   $vendor_category_id   = null;
    public string $categoryLabel        = '';
    public bool   $available_to_travel  = false;

    // Country/location
    public string $country     = '';
    public string $countryName = '';

    public bool   $submitted = false;
    public string $error     = '';

    public function mount(string $slug): void
    {
        $this->tenant = Tenant::where('slug', $slug)
            ->where('status', '!=', 'suspended')
            ->firstOrFail();

        // IP detection
        try {
            $position = Location::get(request()->ip());
            if ($position && $position->countryCode) {
                $this->country     = $position->countryCode;
                $this->countryName = $position->countryName;
            }
        } catch (\Exception $e) {
            // Silent fail — country stays empty
        }
    }

    public function selectCategory(int $id, string $name): void
    {
        $this->vendor_category_id = $id;
        $this->categoryLabel      = $name;
    }

    public function setCountry(string $code, string $name): void
    {
        $this->country     = $code;
        $this->countryName = $name;
    }

    public function submit(): void
    {
        $key = 'vendor-register:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->error = 'Too many attempts. Please try again later.';
            return;
        }
        RateLimiter::hit($key, 3600);

        $this->validate([
            'business_name'       => 'required|string|min:2|max:150',
            'contact_name'        => 'required|string|min:2|max:100',
            'email'               => 'required|email',
            'phone'               => 'nullable|string|max:20',
            'service_description' => 'nullable|string|max:1000',
            'instagram'           => 'nullable|string|max:100',
            'website'             => 'nullable|url|max:200',
            'vendor_category_id'  => 'nullable|integer',
            'available_to_travel' => 'boolean',
        ]);

        $exists = VendorApplication::withoutGlobalScope('tenant')
            ->where('tenant_id', $this->tenant->id)
            ->where('email', $this->email)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($exists) {
            $this->error = 'An application with this email already exists for this company.';
            return;
        }

        VendorApplication::withoutGlobalScope('tenant')->create([
            'uuid'                => Str::uuid(),
            'tenant_id'           => $this->tenant->id,
            'vendor_category_id'  => $this->vendor_category_id,
            'business_name'       => $this->business_name,
            'contact_name'        => $this->contact_name,
            'email'               => $this->email,
            'phone'               => $this->phone,
            'service_description' => $this->service_description,
            'instagram'           => $this->instagram,
            'website'             => $this->website,
            'available_to_travel' => $this->available_to_travel,
            'status'              => 'pending',
        ]);

        SendVendorApplicationReceivedJob::dispatch(
            $this->email,
            $this->contact_name,
            $this->business_name,
            $this->tenant->name,
        );

        $this->submitted = true;
        $this->error     = '';
    }

    public function render()
    {
        $categories = VendorCategory::withoutGlobalScope('tenant')
            ->where('tenant_id', $this->tenant->id)
            ->orderBy('sort_order')
            ->get();

        return view('livewire.public.vendor-register', compact('categories'));
    }
}