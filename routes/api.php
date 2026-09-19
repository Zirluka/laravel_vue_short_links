<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LinkController;

// API ROUTES

Route::post("/", [LinkController::class, 'shortLink']);
Route::get("/{code}", [LinkController::class, 'getUrl']);
Route::post("/{code}/guard", [LinkController::class, 'getGuardedUrl']);


Route::middleware(['auth:sanctum'])->group(function () {
    // user controller
    Route::get('/user', [UserController::class, 'getUser']);
    Route::patch('/user', [UserController::class, 'update']);
    Route::delete('/user', [UserController::class, 'destroy']);

    // link controller
    Route::patch('/link/{id}/password', [LinkController::class, 'setPassword']);
    Route::patch('/link/{id}/expired', [LinkController::class, 'setExpiresTime']);
    Route::patch('/link/{id}/active', [LinkController::class, 'setActive']);
    Route::delete('/link/{id}', [LinkController::class, 'destroy']);
});

require __DIR__.'/auth.php';
