<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DiaryEntryController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('register', [AuthController::class, 'register'])
        ->middleware('throttle:6,1');

    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1');

    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])
        ->middleware('throttle:6,1');

    Route::post('reset-password', [AuthController::class, 'resetPassword'])
        ->middleware('throttle:6,1');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('profile', [ProfileController::class, 'show']);
    });
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::apiResource('diary-entries', DiaryEntryController::class);
});
