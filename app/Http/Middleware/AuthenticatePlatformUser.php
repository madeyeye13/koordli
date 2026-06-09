<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatePlatformUser
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('platform')->check()) {
            return redirect()->route('platform.login');
        }

        return $next($request);
    }
}