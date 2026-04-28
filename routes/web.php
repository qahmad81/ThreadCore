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
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\ThreadController as CustomerThreadController;
use App\Http\Controllers\Customer\UsageController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\SitePageController as PublicSitePageController;
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
    Route::redirect('/', '/'.config('threadcore.admin.path').'/resource')->name('home');
    Route::get('/resource', [ProviderController::class, 'index'])->name('resources.index');
    Route::resource('pages', SitePageController::class)->except('show');
    Route::get('/providers', [ProviderController::class, 'index'])->name('providers.index');
    Route::patch('/providers/{provider}/toggle-enabled', [ProviderController::class, 'toggleEnabled'])->name('providers.toggle-enabled');
    Route::resource('providers', ProviderController::class)->except(['index', 'show']);
    Route::get('/provider-models', fn () => redirect()->route('admin.resources.index'))->name('provider-models.index');
    Route::patch('/provider-models/{providerModel}/toggle-enabled', [ProviderModelController::class, 'toggleEnabled'])->name('provider-models.toggle-enabled');
    Route::resource('provider-models', ProviderModelController::class)->except(['index', 'show']);
    Route::delete('/family-agents/{familyAgent}', [FamilyAgentController::class, 'destroy'])->name('family-agents.destroy');
    Route::resource('family-agents', FamilyAgentController::class)->except(['show', 'destroy']);
    Route::get('/customers', [CustomerAccountController::class, 'index'])->name('customers.index');
    Route::get('/api-keys', [AdminApiKeyController::class, 'index'])->name('api-keys.index');
    Route::get('/threads/{thread:public_id}/export', [ThreadController::class, 'export'])->name('threads.export');
    Route::get('/threads/{thread:public_id}', [ThreadController::class, 'show'])->name('threads.show');
    Route::get('/threads', [ThreadController::class, 'index'])->name('threads.index');
    Route::get('/usage', AdminUsageController::class)->name('usage.index');
});

Route::middleware('auth')->prefix('customer')->name('customer.')->group(function () {
    Route::redirect('/', '/customer/dashboard')->name('home');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/threads/{publicId}/export', [CustomerThreadController::class, 'export'])->name('threads.export');
    Route::get('/threads/{publicId}', [CustomerThreadController::class, 'show'])->name('threads.show');
    Route::get('/threads', [CustomerThreadController::class, 'index'])->name('threads.index');
    Route::get('/api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('/api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
    Route::delete('/api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');
    Route::get('/usage', UsageController::class)->name('usage');
    Route::get('/docs', DocsController::class)->name('docs');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
});

Route::get('/{slug}', PublicSitePageController::class)
    ->where('slug', '[A-Za-z0-9-]+')
    ->name('site.page');
