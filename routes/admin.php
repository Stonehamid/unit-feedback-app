<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RatingController;

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Units Management
    Route::get('/units', [UnitController::class, 'adminIndex']);
    Route::post('/units', [UnitController::class, 'store']);
    Route::get('/units/{unit}', [UnitController::class, 'adminShow']);
    Route::put('/units/{unit}', [UnitController::class, 'update']);
    Route::delete('/units/{unit}', [UnitController::class, 'destroy']); 
    
    // Users Management
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index']);
    
    // Ratings Management
    Route::get('/ratings', [RatingController::class, 'adminIndex']);
});