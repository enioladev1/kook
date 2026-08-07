<?php

use App\Http\Controllers\ProviderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('providers', [ProviderController::class, 'index'])->name('providers.index');
});
