<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

class AuthenticatePlatformUser
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('platform')->check()) {
            return redirect()->route('platform.login');
        }

        // Platform roles use the sentinel tenant_id = 0 (not NULL, since
        // model_has_roles.tenant_id is part of a composite primary key
        // and MySQL forbids NULL there — see the migration that made
        // this table nullable-safe). Without setting this on every real
        // request, every $user->can()/hasPermissionTo() check across the
        // whole platform area silently evaluates against the wrong team
        // context and finds nothing, even when the role/permissions are
        // genuinely, correctly assigned.
        app(PermissionRegistrar::class)->setPermissionsTeamId(0);

        return $next($request);
    }
}