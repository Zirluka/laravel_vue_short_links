<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LinkController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// API ROUTES
Route::post("/", [LinkController::class, 'shortLink']);
Route::get("/{code}", [LinkController::class, 'getUrl']);

require __DIR__.'/auth.php';
