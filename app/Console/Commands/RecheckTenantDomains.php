<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use App\Services\DomainVerificationService;
use Illuminate\Console\Command;

class RecheckTenantDomains extends Command
{
    protected $signature   = 'koordli:recheck-domains';
    protected $description = 'Re-verify all tenant custom domains to catch DNS changes or misconfiguration';

    public function handle(DomainVerificationService $service): void
    {
        $tenants = Tenant::whereNotNull('custom_domain')->get();

        foreach ($tenants as $tenant) {
            $result = $service->verify($tenant);
            $this->info("{$tenant->name}: " . ($result['success'] ? '✓ verified' : '✗ ' . $result['message']));
        }
    }
}