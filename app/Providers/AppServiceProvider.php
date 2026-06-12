<?php

namespace App\Providers;

use App\Services\AuthService;
use App\Services\TenantContext;
use App\Services\TenantService;
use Illuminate\Support\ServiceProvider;
use App\Services\FeatureGateService;

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

        $this->app->singleton(TenantService::class, function () {
            return new TenantService();
        });

        $this->app->singleton(FeatureGateService::class, function () {
            return new FeatureGateService();
        });
    }

    public function boot(): void
    {
        \Illuminate\Support\Facades\Blade::directive('canFeature', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->tenant && app(\App\Services\FeatureGateService::class)->canAccess(auth()->user()->tenant, $expression)): ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('cannotFeature', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->tenant && !app(\App\Services\FeatureGateService::class)->canAccess(auth()->user()->tenant, $expression)): ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('endFeature', function () {
            return "<?php endif; ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('upgradePrompt', function ($expression) {
            return "<?php echo view('components.ui.upgrade-prompt', ['feature' => $expression])->render(); ?>";
        });
    }
}