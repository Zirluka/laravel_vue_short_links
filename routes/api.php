<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LinkController;

// API ROUTES

Route::middleware(['auth:sanctum'])->group(function () {
    // user controller
    Route::get('/user', [UserController::class, 'getUser']);
    Route::patch('/user', [UserController::class, 'update']);
    Route::delete('/user', [UserController::class, 'destroy']);

    // link controller
    Route::prefix('link')->group(function () {
        Route::patch('/{id}/password', [LinkController::class, 'setPassword']);
        Route::patch('/{id}/expired', [LinkController::class, 'setExpiresTime']);
        Route::patch('/{id}/active', [LinkController::class, 'setActive']);
        Route::delete('/{id}', [LinkController::class, 'destroy']);

        // Analytics
        Route::get('/{code}/analytics', [AnalyticsController::class, 'show']);
    });

    // Api tokens
    Route::prefix('tokens')->group(function () {
        Route::get('', [ApiTokenController::class, 'index']);
        Route::post('', [ApiTokenController::class, 'store']);
        Route::delete('/{tokenId}', [ApiTokenController::class, 'destroy']);
        Route::delete('', [ApiTokenController::class, 'destroyAll']);
    });
});

require __DIR__.'/auth.php';

Route::post("/", [LinkController::class, 'shortLink']);
Route::get("/{code}", [LinkController::class, 'getUrl']);
Route::post("/{code}/guard", [LinkController::class, 'getGuardedUrl']);

