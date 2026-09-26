<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LinkController;
use Illuminate\Routing\Middleware\ThrottleRequests;

// API ROUTES

Route::middleware(['auth:sanctum'])->group(function () {
    // user controller
    Route::get('/user', [UserController::class, 'getUser']);
    Route::patch('/user', [UserController::class, 'update']);
    Route::delete('/user', [UserController::class, 'destroy']);

    // link controller
    Route::prefix('link')->group(function () {
        Route::get('/', [LinkController::class, 'getUserUrls']);
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

Route::post("/create", [LinkController::class, 'shortLink'])->middleware('throttle:20,1');
Route::get("/{code}", [LinkController::class, 'getUrl'])->whereAlphaNumeric('code')->middleware('throttle:120,1');
Route::post("/{code}/guard", [LinkController::class, 'getGuardedUrl'])->whereAlphaNumeric('code')->middleware('throttle:10,1');
