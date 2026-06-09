<?php

namespace App\Services;

use App\Models\Central\Tenant;

class TenantContext
{
    protected ?Tenant $tenant = null;

    public function set(Tenant $tenant): void
    {
        $this->tenant = $tenant;
        app()->instance('tenant.id', $tenant->id);
        app()->instance('tenant', $tenant);
    }

    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?int
    {
        return $this->tenant?->id;
    }

    public function clear(): void
    {
        $this->tenant = null;
        app()->forgetInstance('tenant.id');
        app()->forgetInstance('tenant');
    }

    public function resolved(): bool
    {
        return $this->tenant !== null;
    }
}