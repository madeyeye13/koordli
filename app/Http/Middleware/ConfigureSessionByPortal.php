<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfigureSessionByPortal
{
    public function handle(Request $request, Closure $next): Response
    {
        $prefix = explode('/', ltrim($request->getPathInfo(), '/'))[0] ?? '';

        $cookieName = match($prefix) {
            'platform' => 'koordli_platform_session',
            'client'   => 'koordli_client_session',
            'vendor'   => 'koordli_vendor_session',
            default    => 'koordli_session',
        };

        config(['session.cookie' => $cookieName]);

        return $next($request);
    }
}