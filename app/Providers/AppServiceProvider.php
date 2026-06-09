<?php

namespace App\Providers;

use App\Services\AuthService;
use App\Services\TenantContext;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantContext::class, function () {
            return new TenantContext();
        });

        $this->app->singleton(AuthService::class, function () {
            return new AuthService();
        });
    }

    public function boot(): void
    {
        //
    }
}