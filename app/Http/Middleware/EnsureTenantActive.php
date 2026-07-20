<?php

namespace App\Http\Middleware;

use App\Models\Central\Subscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('web')->user();
        if (!$user) return $next($request);

        $tenant = $user->tenant;
        if (!$tenant) return $next($request);

        // Platform-manually-activated tenants with no subscription bypass billing
        if ($tenant->status === 'active' && !$tenant->subscriptions()->exists()) {
            return $next($request);
        }

        $subscription = Subscription::where('tenant_id', $tenant->id)
            ->orderByDesc('created_at')
            ->first();

        if (!$subscription) {
            return $next($request);
        }

        // Always share for banner
        view()->share('tenantSubscription', $subscription);

        \Log::info('EnsureTenantActive', [
            'path'        => $request->path(),
            'method'      => $request->method(),
            'is_locked'   => $subscription->isLocked(),
            'is_livewire' => str_contains($request->path(), 'livewire'),
        ]);

        if (!$subscription->isLocked()) {
            view()->share('tenantLocked', false);
            return $next($request);
        }

        // Account is locked
        view()->share('tenantLocked', true);

        // Allow GET — read-only browsing
        if ($request->isMethod('GET')) {
            return $next($request);
        }

        // Detect Livewire v4 request
        $isLivewire = str_contains($request->path(), 'livewire')
            || $request->header('X-Livewire')
            || ($request->isJson() && str_contains($request->path(), 'livewire'));

        if ($isLivewire) {
            $components = $request->json('components', []);
            $responseComponents = [];

            foreach ($components as $component) {
                $responseComponents[] = [
                    'snapshot' => $component['snapshot'] ?? '{}',
                    'effects'  => [
                        'dispatch' => [
                            [
                                'name'   => 'subscription-locked',
                                'params' => [],
                            ],
                        ],
                    ],
                    'html' => '',
                ];
            }

            return response()->json(['components' => $responseComponents]);
        }

        // Regular form POST — redirect
        return redirect()->route('tenant.billing.upgrade');
    }
}