<?php

namespace App\Services;

use App\Models\Central\PublicDomain;
use App\Models\Central\Tenant;
use App\Models\Tenant\RsvpForm;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PublicDomainService
{
    public function normalize(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('/^https?:\\/\\//', '', $domain) ?? $domain;
        $domain = explode('/', $domain, 2)[0];

        return rtrim($domain, '.');
    }

    public function createForForm(RsvpForm $form, string $domain, string $domainType): PublicDomain
    {
        $domain = $this->normalize($domain);
        $this->assertType($domainType);
        $this->assertAvailable($domain, $form);

        return PublicDomain::updateOrCreate(
            ['rsvp_form_id' => $form->id],
            [
                'tenant_id' => $form->tenant_id,
                'domain' => $domain,
                'domain_type' => $domainType,
                'status' => 'pending',
                'verification_token' => 'koordli-verify-' . Str::random(32),
                'verified_at' => null,
                'last_checked_at' => null,
                'failure_code' => null,
                'failure_message' => null,
                'observed_dns_value' => null,
                'expected_dns_value' => null,
                'last_notified_at' => null,
                'notification_fingerprint' => null,
            ]
        );
    }

    public function removeFromForm(RsvpForm $form): void
    {
        PublicDomain::where('rsvp_form_id', $form->id)->delete();
    }

    public function forForm(?RsvpForm $form): ?PublicDomain
    {
        if (!$form) {
            return null;
        }

        return PublicDomain::where('rsvp_form_id', $form->id)->first();
    }

    public function publicBaseUrl(RsvpForm $form): string
    {
        $domain = $this->forForm($form);

        if ($domain?->isVerified()) {
            return 'https://' . $domain->domain;
        }

        $tenant = Tenant::find($form->tenant_id);

        return $tenant?->resolvePublicBaseUrl() ?? config('app.url');
    }

    public function publicUrl(RsvpForm $form): string
    {
        $base = rtrim($this->publicBaseUrl($form), '/');
        $domain = $this->forForm($form);

        return $domain?->isVerified()
            ? $base . '/'
            : $base . '/rsvp/' . $form->slug;
    }

    public function editUrl(RsvpForm $form, string $token): string
    {
        $base = rtrim($this->publicBaseUrl($form), '/');
        $domain = $this->forForm($form);

        return $domain?->isVerified()
            ? $base . '/edit/' . $token
            : $base . '/rsvp/' . $form->slug . '/edit/' . $token;
    }

    public function ticketUrl(RsvpForm $form, string $token): string
    {
        $base = rtrim($this->publicBaseUrl($form), '/');
        $domain = $this->forForm($form);

        return $domain?->isVerified()
            ? $base . '/ticket/' . $token
            : $base . '/rsvp/ticket/' . $token;
    }

    public function assertAvailable(string $domain, RsvpForm $form): void
    {
        $existing = PublicDomain::where('domain', $domain)
            ->where('rsvp_form_id', '!=', $form->id)
            ->exists();

        $tenantClaimed = Tenant::where('custom_domain', $domain)->exists();

        if ($existing || $tenantClaimed) {
            throw ValidationException::withMessages([
                'domain' => 'That domain is already connected to another Koordli account or RSVP.',
            ]);
        }
    }

    private function assertType(string $domainType): void
    {
        if (!in_array($domainType, config('public_domains.types', []), true)) {
            throw ValidationException::withMessages([
                'domain_type' => 'Choose either a subdomain or an apex domain.',
            ]);
        }
    }
}
