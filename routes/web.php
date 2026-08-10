<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Kook is self-hosted, so there's no marketing landing page - go straight to
// the login screen, which itself redirects to /register on a fresh install
// with no admin account yet (see FortifyServiceProvider::configureViews()).
Route::redirect('/', '/login')->name('home');

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
