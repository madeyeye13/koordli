<?php

namespace App\Livewire\Tenant;

use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.tenant')]
class BrandingSettings extends Component
{
    use WithFileUploads, WithToast;

    public string $primary_color = '#7C3AED';
    public string $accent_color = '#F59E0B';
    public $logo = null;
    public ?string $current_logo = null;

    public function mount(): void
    {
        $branding = auth()->user()->tenant->branding ?? [];

        $this->primary_color = $branding['primary_color'] ?? $this->primary_color;
        $this->accent_color = $branding['accent_color'] ?? $this->accent_color;
        $this->current_logo = $branding['logo'] ?? null;
    }

    public function save(): void
    {
        $this->validate([
            'primary_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'accent_color'  => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'logo'          => 'nullable|image|max:2048',
        ]);

        $tenant = auth()->user()->tenant;
        $branding = $tenant->branding ?? [];

        $branding['primary_color'] = $this->primary_color;
        $branding['accent_color'] = $this->accent_color;

        if ($this->logo) {
            $branding['logo'] = $this->logo->store('tenants/' . $tenant->id . '/branding', 'public');
            $this->current_logo = $branding['logo'];
            $this->reset('logo');
        }

        $tenant->update(['branding' => $branding]);
        $this->toastSuccess('Branding saved.');
    }

    public function render()
    {
        return view('livewire.tenant.branding-settings');
    }
}
