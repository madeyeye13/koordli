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
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth.platform'          => AuthenticatePlatformUser::class,
            'auth.tenant'            => AuthenticateTenantUser::class,
            'auth.client'            => AuthenticateClient::class,
            'auth.vendor'            => AuthenticateVendor::class,
            'tenant.resolve'         => ResolveTenantFromUser::class,
            'onboarding.check'       => EnsureOnboardingComplete::class,
            'vendor.password.check'  => EnsureVendorPasswordChanged::class,
            'client.password.check'  => EnsureClientPasswordChanged::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();