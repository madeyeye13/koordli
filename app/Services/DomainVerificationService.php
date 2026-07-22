<?php

namespace App\Services;

use App\Models\Central\Tenant;
use Illuminate\Support\Str;

class DomainVerificationService
{
    /**
     * The DNS TXT record value the tenant must add to prove ownership.
     */
    public function generateVerificationToken(): string
    {
        return 'koordli-verify-' . Str::random(32);
    }

    /**
     * Check if the tenant's custom domain has:
     * 1. A CNAME pointing to the Koordli app domain, AND
     * 2. A TXT record matching their verification token
     */
    public function verify(Tenant $tenant): array
    {
        if (!$tenant->custom_domain) {
            return ['success' => false, 'message' => 'No custom domain set.'];
        }

        $domain = $tenant->custom_domain;
        $appHost = parse_url(config('app.url'), PHP_URL_HOST) ?? 'koordli.com';

        // Check CNAME
        $cnameValid = false;
        $cnameRecords = @dns_get_record($domain, DNS_CNAME);
        if ($cnameRecords) {
            foreach ($cnameRecords as $record) {
                if (isset($record['target']) && rtrim($record['target'], '.') === $appHost) {
                    $cnameValid = true;
                    break;
                }
            }
        }

        // Check TXT verification token
        $txtValid = false;
        $txtRecords = @dns_get_record('_koordli-verify.' . $domain, DNS_TXT);
        if ($txtRecords) {
            foreach ($txtRecords as $record) {
                if (isset($record['txt']) && $record['txt'] === $tenant->domain_verification_token) {
                    $txtValid = true;
                    break;
                }
            }
        }

        $tenant->domain_last_checked_at = now();

        if ($cnameValid && $txtValid) {
            $tenant->domain_status      = 'verified';
            $tenant->domain_verified_at = $tenant->domain_verified_at ?? now();
            $tenant->save();
            return ['success' => true, 'message' => 'Domain verified successfully.'];
        }

        $tenant->domain_status = $tenant->domain_verified_at ? 'failed' : 'pending';
        $tenant->save();

        $missing = [];
        if (!$cnameValid) $missing[] = 'CNAME record';
        if (!$txtValid)   $missing[] = 'TXT verification record';

        return [
            'success' => false,
            'message' => 'Verification failed. Missing or incorrect: ' . implode(' and ', $missing) . '.',
        ];
    }
}