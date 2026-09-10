<?php

use App\Http\Controllers\LinkController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// API ROUTES
Route::post("/", [LinkController::class, 'shortLink']);
