<?php

namespace App\Http\Middleware;

use App\Models\Central\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantByDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $appHost = parse_url(config('app.url'), PHP_URL_HOST) ?? 'koordli.com';

        // Skip resolution entirely if this is the main app domain (no subdomain/custom domain)
        if ($host === $appHost || $host === 'www.' . $appHost) {
            return $next($request);
        }

        $tenant = null;

        // Case 1: subdomain of koordli.com, e.g. haywhy-events.koordli.com
        if (str_ends_with($host, '.' . $appHost)) {
            $subdomain = str_replace('.' . $appHost, '', $host);
            $tenant = Tenant::where('subdomain', $subdomain)->first();
        } else {
            // Case 2: fully custom domain, must be verified
            $tenant = Tenant::where('custom_domain', $host)
                ->where('domain_status', 'verified')
                ->first();
        }

        if ($tenant) {
            app()->instance('resolvedTenant', $tenant);
            app(\App\Services\TenantContext::class)->set($tenant);
        }

        return $next($request);
    }
}