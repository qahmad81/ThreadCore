<?php

use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\LandingPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPageController::class)->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [SessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'admin'])->prefix(config('threadcore.admin.path'))->name('admin.')->group(function () {
    Route::get('/providers', [ProviderController::class, 'index'])->name('providers.index');
});
