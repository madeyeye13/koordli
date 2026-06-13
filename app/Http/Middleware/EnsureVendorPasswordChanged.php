<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureVendorPasswordChanged
{
    public function handle(Request $request, Closure $next)
    {
        $vendor = Auth::guard('vendor')->user();

        if (!$vendor) {
            return $next($request);
        }

        if (!$vendor->password_changed && !$request->routeIs('vendor.onboarding')) {
            return redirect()->route('vendor.onboarding');
        }

        if ($vendor->password_changed && $request->routeIs('vendor.onboarding')) {
            return redirect()->route('vendor.dashboard');
        }

        return $next($request);
    }
}