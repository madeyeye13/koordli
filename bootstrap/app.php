<?php

use App\Http\Middleware\AuthenticatePlatformUser;
use App\Http\Middleware\AuthenticateTenantUser;
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
            'auth.platform'      => AuthenticatePlatformUser::class,
            'auth.tenant'        => AuthenticateTenantUser::class,
            'tenant.resolve'     => ResolveTenantFromUser::class,
            'onboarding.check'   => \App\Http\Middleware\EnsureOnboardingComplete::class, // ← add
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();