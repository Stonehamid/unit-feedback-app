<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\RatingController;
use App\Http\Controllers\Admin\ReportController;

/*
|--------------------------------------------------------------------------
| Admin Panel API Routes
|--------------------------------------------------------------------------
|
| Routes specifically for admin panel interface with enhanced features,
| statistics, bulk operations, and admin-only functionalities.
|
*/

Route::middleware(['auth:sanctum', 'admin.only', 'api.format'])->group(function () {
    
    // Dashboard routes
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('dashboard/statistics', [DashboardController::class, 'statistics']);
    Route::get('dashboard/export', [DashboardController::class, 'export']);
    
    // User management routes
    Route::apiResource('users', UserController::class);
    Route::post('users/bulk-action', [UserController::class, 'bulkAction']);
    Route::get('users/{user}/activities', [UserController::class, 'activities']);
    
    // Unit management routes
    Route::apiResource('units', UnitController::class);
    Route::get('units/{unit}/statistics', [UnitController::class, 'statistics']);
    Route::post('units/bulk-action', [UnitController::class, 'bulkAction']);
    Route::get('units/export', [UnitController::class, 'export']);
    
    // Rating management routes
    Route::apiResource('ratings', RatingController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::post('ratings/{rating}/approve', [RatingController::class, 'approve']);
    Route::post('ratings/{rating}/reject', [RatingController::class, 'reject']);
    Route::post('ratings/bulk-action', [RatingController::class, 'bulkAction']);
    Route::get('ratings/statistics', [RatingController::class, 'statistics']);
    
    // Report management routes
    Route::apiResource('reports', ReportController::class);
    Route::get('reports/{report}/pdf', [ReportController::class, 'generatePdf']);
    Route::post('reports/bulk-action', [ReportController::class, 'bulkAction']);
    Route::get('reports/statistics', [ReportController::class, 'statistics']);
});