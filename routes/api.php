<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
| Scaffolded — will be built out in a later phase
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    // API routes will go here in Phase API
});

// External form submission endpoint
Route::post('/forms/{token}/submit', [\App\Http\Controllers\Api\FormSubmissionController::class, 'submit'])
    ->name('api.forms.submit');
Route::post('/consult/{token}/submit', [\App\Http\Controllers\Api\ConsultationSubmissionController::class, 'submit'])
    ->name('api.consult.submit');