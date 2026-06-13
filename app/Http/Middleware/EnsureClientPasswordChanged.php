<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureClientPasswordChanged
{
    public function handle(Request $request, Closure $next)
    {
        $client = Auth::guard('client')->user();

        if (!$client) {
            return $next($request);
        }

        if (!$client->password_changed && !$request->routeIs('client.onboarding')) {
            return redirect()->route('client.onboarding');
        }

        if ($client->password_changed && $request->routeIs('client.onboarding')) {
            return redirect()->route('client.dashboard');
        }

        return $next($request);
    }
}