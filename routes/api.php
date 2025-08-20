<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BucketController;
use App\Http\Controllers\ObjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    // Bucket Management
    Route::apiResource('buckets', BucketController::class);
    Route::prefix('buckets/{bucket}')->group(function () {
        Route::apiResource('objects', ObjectController::class)->only([
            'index', 'store', 'show', 'destroy'
        ]);
    });

    // Object Management (to be added later)
});
