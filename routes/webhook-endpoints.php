<?php

use App\Http\Controllers\WebhookEndpointController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::post('projects/{project}/webhook-endpoints', [WebhookEndpointController::class, 'store'])
        ->name('webhook-endpoints.store');

    Route::get('webhook-endpoints/{webhook_endpoint}', [WebhookEndpointController::class, 'show'])
        ->name('webhook-endpoints.show');

    Route::put('webhook-endpoints/{webhook_endpoint}', [WebhookEndpointController::class, 'update'])
        ->name('webhook-endpoints.update');

    Route::delete('webhook-endpoints/{webhook_endpoint}', [WebhookEndpointController::class, 'destroy'])
        ->name('webhook-endpoints.destroy');

    Route::post('webhook-endpoints/{webhook_endpoint}/regenerate-signing-secret', [WebhookEndpointController::class, 'regenerateSigningSecret'])
        ->name('webhook-endpoints.regenerate-signing-secret');
});
