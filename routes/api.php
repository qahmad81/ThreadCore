<?php

use App\Http\Controllers\Api\GatewayThreadController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.key')->prefix('v1')->group(function () {
    Route::post('/threads', [GatewayThreadController::class, 'store']);
    Route::post('/threads/{public_id}/messages', [GatewayThreadController::class, 'message']);
});
