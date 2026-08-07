<?php

use App\Http\Controllers\ApiKeyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::post('projects/{project}/api-keys', [ApiKeyController::class, 'store'])
        ->name('api-keys.store');

    Route::post('api-keys/{api_key}/revoke', [ApiKeyController::class, 'revoke'])
        ->name('api-keys.revoke');

    Route::delete('api-keys/{api_key}', [ApiKeyController::class, 'destroy'])
        ->name('api-keys.destroy');
});
