<?php

namespace App\Helpers;

class TerminologyHelper
{
    /**
     * Resolve a terminology label for the current tenant, in priority order:
     * 1. Tenant's own override (tenants.branding.terminology_overrides)
     * 2. Industry profile default (industry_profiles.terminology)
     * 3. Hardcoded English default passed by the caller
     */
    public static function term(string $key, string $default): string
    {
        $tenant = auth()->check() ? auth()->user()?->tenant : null;

        if (!$tenant) {
            return $default;
        }

        // 1. Tenant override
        $overrides = $tenant->branding['terminology_overrides'] ?? [];
        if (!empty($overrides[$key])) {
            return $overrides[$key];
        }

        // 2. Industry profile default
        if ($tenant->industry_profile_id) {
            $profile = $tenant->relationLoaded('industryProfile')
                ? $tenant->industryProfile
                : \App\Models\Central\IndustryProfile::find($tenant->industry_profile_id);

            if ($profile && !empty($profile->terminology[$key])) {
                return $profile->terminology[$key];
            }
        }

        // 3. Fallback
        return $default;
    }

    /**
     * Same as term() but capitalizes the first letter of every word —
     * useful for headings/labels vs inline sentence text.
     */
    public static function termTitle(string $key, string $default): string
    {
        return ucwords(static::term($key, $default));
    }

    /**
     * Pluralized form — since English pluralization isn't always a simple 's',
     * callers should pass the correct plural default explicitly (e.g. term('event_plural', 'Events')).
     */
}