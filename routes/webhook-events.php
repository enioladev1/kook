<?php

use App\Http\Controllers\WebhookEventController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::get('events/{webhook_event}', [WebhookEventController::class, 'show'])
        ->name('webhook-events.show');
});

Route::middleware(['auth', 'throttle:20,1'])->group(function () {
    Route::post('events/{webhook_event}/replay', [WebhookEventController::class, 'replay'])
        ->name('webhook-events.replay');
});
