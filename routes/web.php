<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Platform Routes
|--------------------------------------------------------------------------
*/
Route::prefix('platform')->name('platform.')->group(function () {

    Route::middleware('guest:platform')->group(function () {
        Route::get('/login', \App\Livewire\Platform\Auth\Login::class)->name('login');
    });

    Route::middleware('auth.platform')->group(function () {
        Route::get('/dashboard', \App\Livewire\Platform\Dashboard::class)->name('dashboard');
        Route::get('/tenants', \App\Livewire\Platform\Tenants\TenantList::class)->name('tenants');
        Route::get('/tenants/create', \App\Livewire\Platform\Tenants\CreateTenant::class)->name('tenants.create');
        Route::get('/plans', \App\Livewire\Platform\Plans\PlanList::class)->name('plans');
        Route::get('/plans/create', \App\Livewire\Platform\Plans\CreatePlan::class)->name('plans.create');
        Route::get('/plans/{plan}/edit', \App\Livewire\Platform\Plans\CreatePlan::class)->name('plans.edit');
        Route::post('/logout', function () {
            Auth::guard('platform')->logout();
            return redirect()->route('platform.login');
        })->name('logout');
    });

});

/*
|--------------------------------------------------------------------------
| Client Routes
|--------------------------------------------------------------------------
*/
Route::prefix('client')->name('client.')->group(function () {

    Route::middleware('guest:client')->group(function () {
        Route::get('/login', \App\Livewire\Client\Auth\Login::class)->name('login');
    });

    Route::middleware('auth.client')->group(function () {
        Route::get('/onboarding', \App\Livewire\Client\Onboarding::class)->name('onboarding');

        Route::middleware('client.password.check')->group(function () {
            Route::get('/dashboard', \App\Livewire\Client\Dashboard::class)->name('dashboard');
        });

        Route::post('/logout', function () {
            Auth::guard('client')->logout();
            return redirect()->route('client.login');
        })->name('logout');
    });

});

/*
|--------------------------------------------------------------------------
| Vendor Routes
|--------------------------------------------------------------------------
*/
Route::prefix('vendor')->name('vendor.')->group(function () {

    Route::middleware('guest:vendor')->group(function () {
        Route::get('/login', \App\Livewire\Vendor\Auth\Login::class)->name('login');
    });

    Route::middleware('auth.vendor')->group(function () {
        Route::get('/onboarding', \App\Livewire\Vendor\Onboarding::class)->name('onboarding');

        Route::middleware('vendor.password.check')->group(function () {
            Route::get('/dashboard', \App\Livewire\Vendor\Dashboard::class)->name('dashboard');
            Route::get('/profile', \App\Livewire\Vendor\Profile::class)->name('profile');
        });

        Route::post('/logout', function () {
            Auth::guard('vendor')->logout();
            return redirect()->route('vendor.login');
        })->name('logout');
    });

});

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/vendors/{slug}/register', \App\Livewire\Public\VendorRegister::class)
    ->name('vendor.public.register');

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['tenant.resolve'])->group(function () {

    Route::middleware('guest:web')->group(function () {
        Route::get('/login', \App\Livewire\Tenant\Auth\Login::class)->name('tenant.login');
    });

    Route::middleware(['auth.tenant', 'onboarding.check'])->group(function () {
        Route::get('/dashboard', \App\Livewire\Tenant\Dashboard::class)->name('tenant.dashboard');
        Route::get('/onboarding', \App\Livewire\Tenant\Onboarding::class)->name('tenant.onboarding');

        Route::post('/logout', function () {
            Auth::guard('web')->logout();
            return redirect()->route('tenant.login');
        })->name('tenant.logout');

        Route::get('/events', \App\Livewire\Tenant\Events\EventList::class)->name('tenant.events');
        Route::get('/events/create', \App\Livewire\Tenant\Events\CreateEvent::class)->name('tenant.events.create');
        Route::get('/events/{slug}/edit', \App\Livewire\Tenant\Events\CreateEvent::class)->name('tenant.events.edit');
        Route::get('/events/{slug}', \App\Livewire\Tenant\Events\EventDetail::class)->name('tenant.events.show');
        Route::get('/events/{slug}/budget', \App\Livewire\Tenant\Budget\EventBudget::class)->name('tenant.events.budget');

        Route::get('/tasks', \App\Livewire\Tenant\Tasks\TaskCenter::class)->name('tenant.tasks');
        Route::get('/tasks/create', \App\Livewire\Tenant\Tasks\CreateTask::class)->name('tenant.tasks.create');
        Route::get('/tasks/{id}/edit', \App\Livewire\Tenant\Tasks\CreateTask::class)->name('tenant.tasks.edit');

        Route::get('/staff', \App\Livewire\Tenant\Staff\StaffList::class)->name('tenant.staff');
        Route::get('/staff/invite', \App\Livewire\Tenant\Staff\InviteStaff::class)->name('tenant.staff.invite');
        Route::get('/staff/{id}/edit', \App\Livewire\Tenant\Staff\InviteStaff::class)->name('tenant.staff.edit');

        Route::get('/budget', \App\Livewire\Tenant\Budget\BudgetOverview::class)->name('tenant.budget');

        Route::get('/vendors', \App\Livewire\Tenant\Vendors\VendorDirectory::class)->name('tenant.vendors');
        Route::get('/vendors/create', \App\Livewire\Tenant\Vendors\CreateVendor::class)->name('tenant.vendors.create');
        Route::get('/vendors/{id}/edit', \App\Livewire\Tenant\Vendors\CreateVendor::class)->name('tenant.vendors.edit');
        Route::get('/vendors/{id}', \App\Livewire\Tenant\Vendors\VendorDetail::class)->name('tenant.vendors.show');
        Route::get('/vendor-applications', \App\Livewire\Tenant\Vendors\VendorApplications::class)->name('tenant.vendor.applications');
    });

    Route::get('/register', \App\Livewire\Auth\Register::class)->name('register');

});

/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('tenant.login');
});