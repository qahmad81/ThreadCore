<?php

use App\Http\Controllers\Admin\CustomerAccountController;
use App\Http\Controllers\Admin\ApiKeyController as AdminApiKeyController;
use App\Http\Controllers\Admin\FamilyAgentController;
use App\Http\Controllers\Admin\ProviderModelController;
use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Admin\SitePageController;
use App\Http\Controllers\Admin\ThreadController;
use App\Http\Controllers\Admin\UsageController as AdminUsageController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Customer\ApiKeyController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\DocsController;
use App\Http\Controllers\Customer\UsageController;
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
    Route::redirect('/', '/'.config('threadcore.admin.path').'/providers')->name('home');
    Route::resource('pages', SitePageController::class)->except('show');
    Route::resource('providers', ProviderController::class)->except('show');
    Route::resource('provider-models', ProviderModelController::class)->except('show');
    Route::resource('family-agents', FamilyAgentController::class)->except(['show', 'destroy']);
    Route::get('/customers', [CustomerAccountController::class, 'index'])->name('customers.index');
    Route::get('/api-keys', [AdminApiKeyController::class, 'index'])->name('api-keys.index');
    Route::get('/threads', [ThreadController::class, 'index'])->name('threads.index');
    Route::get('/usage', AdminUsageController::class)->name('usage.index');
});

Route::middleware('auth')->prefix('customer')->name('customer.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('/api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
    Route::delete('/api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');
    Route::get('/usage', UsageController::class)->name('usage');
    Route::get('/docs', DocsController::class)->name('docs');
});
