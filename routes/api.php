<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UnitController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/test', function () {
    return response()->json([
        'status' => 'API working',
        'session_id' => session()->getId(),
        'env' => [
            'app_url' => config('app.url'),
            'session_domain' => config('session.domain'),
        ]
    ]);
});

// Public routes
Route::get('/units', [UnitController::class, 'index']);
Route::get('/units/{unit}', [UnitController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Admin routes
    Route::prefix('admin')->middleware(['role:admin'])->group(function () {
        Route::get('/units', [UnitController::class, 'adminIndex']);
        Route::get('/units/{unit}', [UnitController::class, 'adminShow']);
        Route::post('/units', [UnitController::class, 'store']);
        Route::put('/units/{unit}', [UnitController::class, 'update']);
        Route::delete('/units/{unit}', [UnitController::class, 'destroy']);
    });
});