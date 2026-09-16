<?php

namespace App\Services;

use App\Models\Central\PublicDomain;

class PublicDomainVerificationService
{
    public function verify(PublicDomain $publicDomain): array
    {
        $domain = $publicDomain->domain;
        $txtHost = config('public_domains.verification_txt_prefix') . '.' . $domain;
        $txtValid = $this->hasTxtValue($txtHost, $publicDomain->verification_token);
        $expected = $publicDomain->domain_type === 'apex'
            ? (string) config('public_domains.apex_ip')
            : rtrim((string) config('public_domains.cname_target'), '.');
        $observed = null;
        $recordValid = false;
        $failureCode = null;

        if ($publicDomain->domain_type === 'apex') {
            $aRecords = @dns_get_record($domain, DNS_A) ?: [];
            $addresses = array_values(array_filter(array_map(
                fn (array $record): ?string => $record['ip'] ?? null,
                $aRecords
            )));
            $observed = implode(', ', $addresses) ?: null;
            $recordValid = in_array($expected, $addresses, true);
            $failureCode = $recordValid ? null : 'apex_ip_mismatch';
        } else {
            $cnameRecords = @dns_get_record($domain, DNS_CNAME) ?: [];
            $targets = array_values(array_filter(array_map(
                fn (array $record): ?string => isset($record['target']) ? rtrim($record['target'], '.') : null,
                $cnameRecords
            )));
            $observed = implode(', ', $targets) ?: null;
            $recordValid = in_array($expected, $targets, true);
            $failureCode = $recordValid ? null : 'cname_mismatch';
        }

        $publicDomain->last_checked_at = now();
        $publicDomain->expected_dns_value = $expected;
        $publicDomain->observed_dns_value = $observed;

        if ($recordValid && $txtValid) {
            $publicDomain->status = 'verified';
            $publicDomain->verified_at ??= now();
            $publicDomain->failure_code = null;
            $publicDomain->failure_message = null;
            $publicDomain->save();

            return ['success' => true, 'message' => 'Domain verified successfully.'];
        }

        $publicDomain->status = $publicDomain->verified_at ? 'failed' : 'pending';
        $publicDomain->failure_code = !$recordValid ? $failureCode : 'txt_mismatch';
        $publicDomain->failure_message = !$recordValid
            ? ($publicDomain->domain_type === 'apex'
                ? 'The domain A record does not point to the current Koordli server.'
                : 'The domain CNAME does not point to the configured Koordli host.')
            : 'The DNS verification TXT record is missing or does not match.';
        $publicDomain->save();

        return [
            'success' => false,
            'message' => $publicDomain->failure_message,
            'failure_code' => $publicDomain->failure_code,
            'expected' => $expected,
            'observed' => $observed,
        ];
    }

    private function hasTxtValue(string $host, string $expected): bool
    {
        $records = @dns_get_record($host, DNS_TXT) ?: [];

        foreach ($records as $record) {
            if (($record['txt'] ?? null) === $expected) {
                return true;
            }
        }

        return false;
    }
}
