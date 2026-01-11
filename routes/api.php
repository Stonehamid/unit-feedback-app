<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ReportController;

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (membutuhkan token)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth Routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Unit Routes (hanya admin yang bisa create/update/delete)
    Route::apiResource('units', UnitController::class)->except(['index', 'show'])->middleware('admin.only');
    Route::get('units', [UnitController::class, 'index']); // Publik bisa lihat daftar unit
    Route::get('units/{unit}', [UnitController::class, 'show']); // Publik bisa lihat detail unit

    // Rating Routes (reviewer atau admin yang bisa create)
    Route::post('units/{unit}/ratings', [RatingController::class, 'store'])->middleware('reviewer.only');
    Route::delete('ratings/{rating}', [RatingController::class, 'destroy'])->middleware('admin.only');

    // Message Routes (Publik bisa kirim, admin hapus)
    Route::post('units/{unit}/messages', [MessageController::class, 'store']); // Bisa dibuka publik
    Route::delete('messages/{message}', [MessageController::class, 'destroy'])->middleware('admin.only');

    // Report Routes (hanya admin)
    Route::apiResource('reports', ReportController::class)->middleware('admin.only');
});