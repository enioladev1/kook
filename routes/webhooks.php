<?php

use App\Http\Controllers\WebhookIngestController;
use Illuminate\Support\Facades\Route;

Route::post('webhooks/{ingestToken}', [WebhookIngestController::class, 'store'])
    ->middleware('throttle:webhook-ingest')
    ->name('webhooks.ingest');
