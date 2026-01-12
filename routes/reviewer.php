<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\UnitController;

/*
|--------------------------------------------------------------------------
| Reviewer Panel API Routes
|--------------------------------------------------------------------------
|
| Routes specifically for reviewer panel with reviewer-specific features
| and limited access compared to admin panel.
|
*/

Route::middleware(['auth:sanctum', 'reviewer.access', 'api.format'])->prefix('reviewer')->group(function () {
    
    // Dashboard for reviewer
    Route::get('dashboard', function () {
        return [
            'message' => 'Reviewer Dashboard',
            'stats' => [
                'total_ratings' => auth()->user()->ratings()->count(),
                'total_messages' => auth()->user()->messages()->count(),
            ]
        ];
    });
    
    // Unit routes for reviewer
    Route::get('units', [UnitController::class, 'index']);
    Route::get('units/{unit}', [UnitController::class, 'show']);
    
    // Rating routes
    Route::apiResource('ratings', RatingController::class)->only(['index', 'show', 'store']);
    Route::get('my-ratings', [RatingController::class, 'myRatings']);
    
    // Message routes
    Route::apiResource('messages', MessageController::class)->only(['index', 'show', 'store']);
    Route::get('my-messages', [MessageController::class, 'myMessages']);
    
    // Profile
    Route::get('profile', function (Request $request) {
        return $request->user();
    });
});