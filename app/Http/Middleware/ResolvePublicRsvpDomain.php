<?php

namespace App\Http\Middleware;

use App\Models\Central\PublicDomain;
use App\Models\Central\Tenant;
use App\Models\Tenant\RsvpForm;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolvePublicRsvpDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower(rtrim($request->getHost(), '.'));

        // The wildcard host route also matches legacy tenant domains. Keep
        // those requests on their existing root behavior instead of letting
        // the event-domain resolver intercept them.
        if (Tenant::where('custom_domain', $host)->exists()) {
            return redirect()->to(rtrim((string) config('app.url'), '/') . '/');
        }

        $publicDomain = PublicDomain::where('domain', $host)
            ->where('status', 'verified')
            ->first();

        abort_unless($publicDomain, 404);

        $form = RsvpForm::withoutGlobalScope('tenant')
            ->with('event')
            ->where('id', $publicDomain->rsvp_form_id)
            ->where('tenant_id', $publicDomain->tenant_id)
            ->first();

        abort_unless($form && $form->is_active && $form->event?->rsvp_enabled, 404);

        $tenant = $publicDomain->tenant;
        abort_unless($tenant, 404);

        app(TenantContext::class)->set($tenant);
        app()->instance('resolvedTenant', $tenant);
        app()->instance('publicRsvpForm', $form);
        app()->instance('publicRsvpDomain', $publicDomain);

        if ($request->route()) {
            $request->route()->setParameter('slug', $form->slug);
        }

        return $next($request);
    }
}
