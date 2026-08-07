<?php

use App\Http\Controllers\Api\WebhookEndpointController;
use App\Http\Controllers\Api\WebhookEventController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.key', 'throttle:api-key'])->prefix('v1')->group(function () {
    Route::get('webhook-endpoints', [WebhookEndpointController::class, 'index']);

    Route::get('webhook-endpoints/{webhook_endpoint}/events', [WebhookEventController::class, 'index']);
    Route::get('events/{webhook_event}', [WebhookEventController::class, 'show']);
    Route::post('events/{webhook_event}/replay', [WebhookEventController::class, 'replay'])
        ->middleware('idempotent');
});
