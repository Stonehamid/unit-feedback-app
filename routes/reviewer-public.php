<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Reviewer\DashboardController;
use App\Http\Controllers\Reviewer\RatingController;
use App\Http\Controllers\Reviewer\ReportController;

/*
|--------------------------------------------------------------------------
| Public Reviewer Routes
|--------------------------------------------------------------------------
|
| Routes for anonymous reviewers to submit ratings and view public data
| No authentication required.
|
*/

// Public dashboard
Route::get('reviewer/dashboard', [DashboardController::class, 'index']);
Route::get('reviewer/dashboard/units/search', [DashboardController::class, 'searchUnits']);
Route::get('reviewer/dashboard/units/{unit}/statistics', [DashboardController::class, 'unitStatistics']);

// Public ratings
Route::post('reviewer/ratings', [RatingController::class, 'store']);
Route::get('reviewer/ratings/recent', [RatingController::class, 'recentRatings']);
Route::get('reviewer/ratings/top-units', [RatingController::class, 'topRatedUnits']);
Route::get('reviewer/units/{unit}/ratings', [RatingController::class, 'unitRatings']);

// Public reports
Route::get('reviewer/reports', [ReportController::class, 'index']);
Route::get('reviewer/reports/statistics', [ReportController::class, 'statistics']);
Route::get('reviewer/reports/{report}', [ReportController::class, 'show']);
Route::get('reviewer/units/{unit}/reports', [ReportController::class, 'unitReports']);

// Public units (alternatif dari api/units dengan lebih banyak info)
Route::get('reviewer/units', [DashboardController::class, 'searchUnits']);