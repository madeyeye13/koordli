<?php

namespace App\Providers;

use App\Livewire\Hooks\SubscriptionLockHook;
use App\Services\AuthService;
use App\Services\TenantContext;
use App\Services\TenantService;
use Illuminate\Support\ServiceProvider;
use App\Services\FeatureGateService;
use Livewire\Livewire;

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

        $this->app->singleton(\App\Services\PermissionService::class, function () {
            return new \App\Services\PermissionService();
        });

        // Register Livewire component hook early — before LivewireServiceProvider::boot()
        Livewire::componentHook(SubscriptionLockHook::class);
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

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\TaskAssigned::class,
            \App\Listeners\SendTaskAssignedNotification::class,
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\ContractSent::class,
            [\App\Listeners\LogContractActivity::class, 'handleSent'],
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\ContractFullySigned::class,
            [\App\Listeners\LogContractActivity::class, 'handleFullySigned'],
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\InvoiceFullyPaid::class,
            \App\Listeners\LogInvoiceActivity::class,
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\PlannerFeeFullyCollected::class,
            \App\Listeners\SendFeeFullyCollectedNotification::class,
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\BookingSubmitted::class,
            \App\Listeners\LogBookingActivity::class,
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\RsvpSubmitted::class,
            \App\Listeners\LogRsvpActivity::class,
        );


        \Illuminate\Support\Facades\Event::listen(
            \App\Events\SupportTicketCreated::class,
            \App\Listeners\LogSupportTicketActivity::class,
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\VendorApplicationSubmitted::class,
            \App\Listeners\LogVendorApplicationActivity::class,
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\RunsheetItemDelayed::class,
            \App\Listeners\CascadeRunsheetDelayWarning::class,
        );
    }
}