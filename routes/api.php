<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LinkController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [UserController::class, 'getUser']);
    Route::patch('/user', [UserController::class, 'update']);
    Route::delete('/user', [UserController::class, 'destroy']);
});

// API ROUTES
Route::post("/", [LinkController::class, 'shortLink']);
Route::get("/{code}", [LinkController::class, 'getUrl']);

require __DIR__.'/auth.php';
