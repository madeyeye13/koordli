<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureOnboardingComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('web')->user();

        if (!$user) {
            return $next($request);
        }

        // If onboarding is NOT complete and user is NOT on onboarding page
        if (!$user->onboarding_completed && !$request->routeIs('tenant.onboarding')) {
            return redirect()->route('tenant.onboarding');
        }

        // If onboarding IS complete and user IS trying to visit onboarding
        if ($user->onboarding_completed && $request->routeIs('tenant.onboarding')) {
            return redirect()->route('tenant.dashboard');
        }

        return $next($request);
    }
}