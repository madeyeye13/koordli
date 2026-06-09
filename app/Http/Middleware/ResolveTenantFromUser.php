<?php

namespace App\Http\Middleware;

use App\Models\Central\Tenant;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResolveTenantFromUser
{
    public function __construct(protected TenantContext $tenantContext) {}

    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            if ($user && $user->tenant_id) {
                $tenant = Tenant::find($user->tenant_id);

                if ($tenant) {
                    $this->tenantContext->set($tenant);
                }
            }
        }

        return $next($request);
    }
}