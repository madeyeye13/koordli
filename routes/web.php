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

    // Authenticated only
    Route::middleware('auth.platform')->group(function () {
        Route::get('/dashboard', function () {
            return view('platform.dashboard');
        })->name('dashboard');
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
    Route::middleware('auth.tenant')->group(function () {
        Route::get('/dashboard', function () {
            return view('tenant.dashboard');
        })->name('tenant.dashboard');
    });

});

/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('tenant.login');
});