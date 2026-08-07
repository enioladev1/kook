<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/projects.php';
require __DIR__.'/webhook-endpoints.php';
require __DIR__.'/webhook-events.php';
require __DIR__.'/api-keys.php';
require __DIR__.'/providers.php';
require __DIR__.'/audit-logs.php';
require __DIR__.'/webhooks.php';
