<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Main API routes for mobile apps, frontend SPA, or third-party integrations.
| All API responses are automatically formatted by ApiResponseFormatter middleware.
|
*/

// =========================================================================
// PUBLIC ROUTES - No authentication required
// =========================================================================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('units', [UnitController::class, 'index']);
Route::get('units/{unit}', [UnitController::class, 'show']);

// =========================================================================
// PROTECTED ROUTES - Sanctum token authentication required
// =========================================================================

Route::middleware(['auth:sanctum', 'api.format'])->group(function () {
    
    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Admin-only routes
    Route::middleware('admin.only')->group(function () {
        Route::post('units', [UnitController::class, 'store']);
        Route::put('units/{unit}', [UnitController::class, 'update']);
        Route::patch('units/{unit}', [UnitController::class, 'update']);
        Route::delete('units/{unit}', [UnitController::class, 'destroy']);
        
        Route::delete('ratings/{rating}', [RatingController::class, 'destroy']);
        Route::delete('messages/{message}', [MessageController::class, 'destroy']);
        
        Route::apiResource('reports', ReportController::class);
    });
    
    // Reviewer-only routes
    Route::middleware('reviewer.only')->group(function () {
        Route::post('units/{unit}/ratings', [RatingController::class, 'store']);
    });
    
    // Mixed permission routes
    Route::post('units/{unit}/messages', [MessageController::class, 'store']);
    
    // Reviewer + Admin routes
    Route::middleware('reviewer.access')->group(function () {
        Route::get('my-ratings', [RatingController::class, 'myRatings']);
        Route::get('my-messages', [MessageController::class, 'myMessages']);
    });
});