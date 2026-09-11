<?php

use App\Http\Middleware\AuthenticateClient;
use App\Http\Middleware\AuthenticatePlatformUser;
use App\Http\Middleware\AuthenticateTenantUser;
use App\Http\Middleware\AuthenticateVendor;
use App\Http\Middleware\EnsureClientPasswordChanged;
use App\Http\Middleware\EnsureOnboardingComplete;
use App\Http\Middleware\EnsureVendorPasswordChanged;
use App\Http\Middleware\ResolveTenantFromUser;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Traefik terminates HTTPS and forwards plain HTTP internally —
        // trusting it as a proxy lets Laravel correctly read the real
        // X-Forwarded-Proto header instead of assuming http, which was
        // causing every generated URL (asset(), route(), url()) to be
        // built as http:// even though the actual visitor connection
        // was https://.
        $middleware->trustProxies(
            at: '*',
            headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->alias([
            'auth.platform'          => AuthenticatePlatformUser::class,
            'auth.tenant'            => AuthenticateTenantUser::class,
            'auth.client'            => AuthenticateClient::class,
            'auth.vendor'            => AuthenticateVendor::class,
            'tenant.resolve'         => ResolveTenantFromUser::class,
            'onboarding.check'       => EnsureOnboardingComplete::class,
            'vendor.password.check'  => EnsureVendorPasswordChanged::class,
            'client.password.check'  => EnsureClientPasswordChanged::class,
            'tenant.active' => \App\Http\Middleware\EnsureTenantActive::class,
            'tenant.byDomain' => \App\Http\Middleware\ResolveTenantByDomain::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
