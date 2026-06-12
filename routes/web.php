<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Platform Routes (SaaS Owner)
|--------------------------------------------------------------------------
*/
Route::prefix('platform')->name('platform.')->group(function () {

    // Guest only
    Route::middleware('guest:platform')->group(function () {
        Route::get('/login', \App\Livewire\Platform\Auth\Login::class)
            ->name('login');
    });

    Route::middleware('auth.platform')->group(function () {
    Route::get('/dashboard', \App\Livewire\Platform\Dashboard::class)
        ->name('dashboard');
    Route::get('/tenants', \App\Livewire\Platform\Tenants\TenantList::class)
        ->name('tenants');
    Route::get('/tenants/create', \App\Livewire\Platform\Tenants\CreateTenant::class)
        ->name('tenants.create');
    Route::post('/logout', function () {
        Auth::guard('platform')->logout();
        return redirect()->route('platform.login');
    })->name('logout');

    Route::get('/plans', \App\Livewire\Platform\Plans\PlanList::class)
    ->name('plans');
    Route::get('/plans/create', \App\Livewire\Platform\Plans\CreatePlan::class)
        ->name('plans.create');
    Route::get('/plans/{plan}/edit', \App\Livewire\Platform\Plans\CreatePlan::class)
        ->name('plans.edit');
});

});

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['tenant.resolve'])->group(function () {

    // Guest only
    Route::middleware('guest:web')->group(function () {
        Route::get('/login', \App\Livewire\Tenant\Auth\Login::class)
            ->name('tenant.login');
    });

    // Authenticated only
    Route::middleware(['auth.tenant', 'onboarding.check'])->group(function () {
        Route::get('/dashboard', \App\Livewire\Tenant\Dashboard::class)
            ->name('tenant.dashboard');

        Route::get('/onboarding', \App\Livewire\Tenant\Onboarding::class)
            ->name('tenant.onboarding');

        Route::post('/logout', function () {
            Auth::guard('web')->logout();
            return redirect()->route('tenant.login');
        })->name('tenant.logout');


        // Events
        Route::get('/events', \App\Livewire\Tenant\Events\EventList::class)->name('tenant.events');
        Route::get('/events/create', \App\Livewire\Tenant\Events\CreateEvent::class)->name('tenant.events.create');
        Route::get('/events/{uuid}/edit', \App\Livewire\Tenant\Events\CreateEvent::class)->name('tenant.events.edit');
        Route::get('/events/{uuid}', \App\Livewire\Tenant\Events\EventDetail::class)->name('tenant.events.show');



    });

    Route::get('/register', \App\Livewire\Auth\Register::class)
    ->name('register');

});

/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('tenant.login');
});

