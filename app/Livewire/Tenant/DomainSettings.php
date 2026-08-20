<?php

namespace App\Livewire\Tenant;

use App\Services\DomainVerificationService;
use App\Services\FeatureGateService;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class DomainSettings extends Component
{
    use WithToast;

    public string $subdomain     = '';
    public string $custom_domain = '';

    public function mount(): void
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'domain-settings.manage'),
            403
        );

        $tenant = auth()->user()->tenant;
        $this->subdomain     = $tenant->subdomain ?? '';
        $this->custom_domain = $tenant->custom_domain ?? '';
    }

    public function saveSubdomain(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'domain-settings.manage')) {
            $this->toastError('You do not have permission to manage domain settings.');
            return;
        }

        $tenant = auth()->user()->tenant;
        $gate   = app(FeatureGateService::class);

        if (!$gate->canAccess($tenant, 'custom_subdomain')) {
            $this->toastError('Custom subdomains are not available on your current plan.');
            return;
        }

        $this->validate([
            'subdomain' => 'required|alpha_dash|min:3|max:50|unique:tenants,subdomain,' . $tenant->id,
        ]);

        $tenant->update(['subdomain' => strtolower($this->subdomain)]);
        $this->toastSuccess('Subdomain saved.');
    }

    public function saveCustomDomain(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'domain-settings.manage')) {
            $this->toastError('You do not have permission to manage domain settings.');
            return;
        }

        $tenant = auth()->user()->tenant;
        $gate   = app(FeatureGateService::class);

        if (!$gate->canAccess($tenant, 'custom_domain')) {
            $this->toastError('Custom domains are not available on your current plan.');
            return;
        }

        $this->validate([
            'custom_domain' => 'required|string|max:255|unique:tenants,custom_domain,' . $tenant->id,
        ]);

        $tenant->update([
            'custom_domain'              => strtolower($this->custom_domain),
            'domain_status'              => 'pending',
            'domain_verification_token'  => (new DomainVerificationService())->generateVerificationToken(),
            'domain_verified_at'         => null,
        ]);

        $this->toastSuccess('Domain added. Follow the DNS instructions below, then click Verify Now.');
    }

    public function verifyNow(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'domain-settings.manage')) {
            $this->toastError('You do not have permission to manage domain settings.');
            return;
        }

        $tenant = auth()->user()->tenant;
        $result = app(DomainVerificationService::class)->verify($tenant);

        if ($result['success']) {
            $this->toastSuccess($result['message']);
        } else {
            $this->toastError($result['message']);
        }
    }

    public function removeCustomDomain(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'domain-settings.manage')) {
            $this->toastError('You do not have permission to manage domain settings.');
            return;
        }

        auth()->user()->tenant->update([
            'custom_domain'             => null,
            'domain_status'             => 'unverified',
            'domain_verification_token' => null,
            'domain_verified_at'        => null,
        ]);
        $this->custom_domain = '';
        $this->toastSuccess('Custom domain removed.');
    }

    public function render()
    {
        $tenant = \App\Models\Central\Tenant::find(auth()->user()->tenant_id);
        $gate   = app(FeatureGateService::class);

        $appHost = parse_url(config('app.url'), PHP_URL_HOST) ?? 'koordli.com';

        return view('livewire.tenant.domain-settings', [
            'tenant'             => $tenant,
            'appHost'            => $appHost,
            'canSubdomain'       => $gate->canAccess($tenant, 'custom_subdomain'),
            'canCustomDomain'    => $gate->canAccess($tenant, 'custom_domain'),
            'canWhiteLabel'      => $gate->canAccess($tenant, 'white_label'),
        ]);
    }
}