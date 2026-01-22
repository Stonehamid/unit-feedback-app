<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Visitor;
use Illuminate\Support\Facades\Route;

// ============================================================================
// API ROUTES - UNIT RATING FEEDBACK SYSTEM
// ============================================================================
// Semua endpoint API menggunakan API versioning (v1)
// Visitor endpoints: Tidak memerlukan authentication (anonymous system)
// Admin endpoints: Memerlukan Sanctum authentication dengan role admin
// ============================================================================

// API VERSION 1 - Semua endpoints dalam namespace v1
Route::prefix('v1')->group(function () {
    
    // ------------------------------------------------------------------------
    // PUBLIC ENDPOINTS
    // ------------------------------------------------------------------------
    // Endpoint yang dapat diakses tanpa authentication
    // Untuk semua pengunjung website/mobile app
    // ------------------------------------------------------------------------
    
    // Unit Information - Informasi unit yang dapat diakses publik
    Route::get('/units', [Visitor\UnitController::class, 'index']);
    Route::get('/units/{unit}', [Visitor\UnitController::class, 'show']);
    Route::get('/units/search/{query}', [Visitor\UnitController::class, 'search']);
    Route::get('/units/type/{type}', [Visitor\UnitController::class, 'byType']);
    Route::get('/units/nearby', [Visitor\UnitController::class, 'nearby']);
    Route::get('/units/popular', [Visitor\UnitController::class, 'popular']);
    
    // Unit Ratings - Data rating unit (read-only untuk publik)
    Route::get('/units/{unit}/ratings', [Visitor\RatingController::class, 'getUnitRatings']);
    Route::get('/units/{unit}/rating-categories', [Visitor\RatingController::class, 'getRatingCategories']);
    
    // ------------------------------------------------------------------------
    // VISITOR ENDPOINTS
    // ------------------------------------------------------------------------
    // Endpoint untuk pengunjung anonymous
    // Menggunakan middleware rate limiting untuk mencegah abuse
    // ------------------------------------------------------------------------
    Route::prefix('visitor')->middleware(['guest.rate_limit'])->group(function () {
        
        // Session Management - Manajemen session untuk visitor tanpa login
        Route::post('/session/init', [Visitor\SessionController::class, 'init']);
        Route::put('/session/update', [Visitor\SessionController::class, 'update']);
        Route::get('/session/info', [Visitor\SessionController::class, 'info']);
        Route::get('/session/activities', [Visitor\SessionController::class, 'activities']);
        
        // Rating Submission - Submit rating dengan cooldown 24 jam per unit
        Route::middleware(['visitor.cooldown'])->group(function () {
            Route::post('/units/{unit}/ratings', [Visitor\RatingController::class, 'store']);
        });
        
        // Rating Validation - Validasi apakah visitor dapat memberikan rating
        Route::get('/units/{unit}/can-rate', [Visitor\RatingController::class, 'checkCooldown']);
        Route::get('/units/{unit}/validate-rating', [Visitor\SessionController::class, 'validateRating']);
        
        // Visit Tracking - Melacak kunjungan visitor ke unit
        Route::post('/units/{unit}/visits', [Visitor\UnitController::class, 'trackVisit']);
        Route::put('/visits/{visit}/end', [Visitor\UnitController::class, 'endVisit']);
        
        // Report Submission - Submit laporan/masukan dari visitor
        Route::post('/reports', [Visitor\ReportController::class, 'store']);
        
        // Report Management - Melihat laporan yang telah dikirim
        Route::get('/reports/my', [Visitor\ReportController::class, 'myReports']);
        Route::get('/reports/{report}/status', [Visitor\ReportController::class, 'checkStatus']);
        
        // Form Metadata - Data untuk form dropdowns
        Route::get('/report-types', [Visitor\ReportController::class, 'getReportTypes']);
        Route::get('/priority-levels', [Visitor\ReportController::class, 'getPriorityLevels']);
    });
    
    // ------------------------------------------------------------------------
    // AUTHENTICATION ENDPOINTS
    // ------------------------------------------------------------------------
    // Endpoint untuk autentikasi admin
    // Sanctum digunakan untuk token-based authentication
    // ------------------------------------------------------------------------
    Route::prefix('auth')->group(function () {
        // Login & Authentication - Login untuk admin
        Route::post('/login', [LoginController::class, 'login']);
        Route::get('/check', [LoginController::class, 'check']);
        
        // Protected Routes - Memerlukan Sanctum authentication
        Route::middleware('auth:sanctum')->group(function () {
            // Logout - Menghapus Sanctum token
            Route::post('/logout', [Auth\LogoutController::class, 'logout']);
            Route::post('/logout-all', [Auth\LogoutController::class, 'logoutAll']);
            
            // Profile Management - Manajemen profil admin
            Route::get('/profile', [Auth\ProfileController::class, 'show']);
            Route::put('/profile', [Auth\ProfileController::class, 'update']);
            Route::put('/profile/password', [Auth\ProfileController::class, 'updatePassword']);
        });
    });
    
    // ------------------------------------------------------------------------
    // ADMIN ENDPOINTS
    // ------------------------------------------------------------------------
    // Semua endpoint admin memerlukan:
    // 1. Sanctum authentication (auth:sanctum)
    // 2. Admin role verification (admin middleware)
    // 3. Rate limiting (throttle:60,1)
    // ------------------------------------------------------------------------
    Route::middleware(['auth:sanctum', 'admin', 'throttle:60,1'])->group(function () {
        
        // Dashboard - Statistik dan data dashboard
        Route::prefix('admin/dashboard')->group(function () {
            Route::get('/stats', [Admin\DashboardController::class, 'stats']);
            Route::get('/charts', [Admin\DashboardController::class, 'charts']);
            Route::get('/overview', [Admin\DashboardController::class, 'overview']);
        });
        
        // Ratings Management - Mengelola rating dari visitor
        Route::prefix('admin/ratings')->group(function () {
            // CRUD Operations - Operasi dasar untuk rating
            Route::get('/', [Admin\RatingController::class, 'index']);
            Route::get('/{rating}', [Admin\RatingController::class, 'show']);
            Route::delete('/{rating}', [Admin\RatingController::class, 'destroy']);
            
            // Reply & Status Management - Membalas dan mengubah status rating
            Route::post('/{rating}/reply', [Admin\RatingController::class, 'reply']);
            Route::put('/{rating}/status', [Admin\RatingController::class, 'updateStatus']);
            
            // Statistics - Statistik untuk rating
            Route::get('/stats/summary', [Admin\RatingController::class, 'stats']);
        });
        
        // Unit Management - CRUD untuk unit universitas
        Route::apiResource('admin/units', Admin\UnitController::class);
        Route::put('/admin/units/{unit}/toggle-status', [Admin\UnitController::class, 'toggleStatus']);
        Route::get('/admin/units/{unit}/categories', [Admin\UnitController::class, 'categories']);
        
        // Employee Management - Mengelola pekerja per unit
        Route::prefix('admin/units/{unit}/employees')->group(function () {
            Route::get('/', [Admin\EmployeeController::class, 'index']);
            Route::post('/', [Admin\EmployeeController::class, 'store']);
            Route::get('/{employee}', [Admin\EmployeeController::class, 'show']);
            Route::put('/{employee}', [Admin\EmployeeController::class, 'update']);
            Route::delete('/{employee}', [Admin\EmployeeController::class, 'destroy']);
            Route::put('/{employee}/status', [Admin\EmployeeController::class, 'updateStatus']);
        });
        
        // Reports Management - Mengelola laporan dari visitor
        Route::prefix('admin/reports')->group(function () {
            // CRUD Operations - Operasi dasar untuk laporan
            Route::get('/', [Admin\ReportController::class, 'index']);
            Route::get('/{report}', [Admin\ReportController::class, 'show']);
            
            // Reply & Status Management - Menanggapi dan mengubah status laporan
            Route::post('/{report}/reply', [Admin\ReportController::class, 'reply']);
            Route::put('/{report}/status', [Admin\ReportController::class, 'updateStatus']);
            Route::put('/{report}/priority', [Admin\ReportController::class, 'updatePriority']);
            Route::put('/{report}/assign', [Admin\ReportController::class, 'assign']);
            
            // Statistics - Statistik untuk laporan
            Route::get('/stats/summary', [Admin\ReportController::class, 'stats']);
        });
        
        // Messages Management - Pesan internal dari admin ke unit
        Route::apiResource('admin/messages', Admin\MessageController::class);
        Route::put('/admin/messages/{message}/mark-read', [Admin\MessageController::class, 'markAsRead']);
        Route::post('/admin/messages/mark-all-read', [Admin\MessageController::class, 'markAllAsRead']);
        
        // Export Management - Generate laporan dalam format PDF/Excel
        Route::prefix('admin/export')->group(function () {
            // Export Ratings - Ekspor data rating
            Route::get('/ratings/pdf', [Admin\ExportController::class, 'exportRatingsPdf']);
            Route::get('/ratings/excel', [Admin\ExportController::class, 'exportRatingsExcel']);
            
            // Export Units - Ekspor data unit
            Route::get('/units/pdf', [Admin\ExportController::class, 'exportUnitsPdf']);
            Route::get('/units/excel', [Admin\ExportController::class, 'exportUnitsExcel']);
            
            // Export Visits - Ekspor data kunjungan
            Route::get('/visits/pdf', [Admin\ExportController::class, 'exportVisitsPdf']);
            Route::get('/visits/excel', [Admin\ExportController::class, 'exportVisitsExcel']);
            
            // Export Reports - Ekspor data laporan
            Route::get('/reports/pdf', [Admin\ExportController::class, 'exportReportsPdf']);
            Route::get('/reports/excel', [Admin\ExportController::class, 'exportReportsExcel']);
            
            // Export Dashboard Statistics - Ekspor statistik dashboard
            Route::get('/dashboard/stats', [Admin\ExportController::class, 'exportDashboardStats']);
        });
    });
});

// ----------------------------------------------------------------------------
// DEFAULT ROUTE
// ----------------------------------------------------------------------------
// Route default untuk memberikan informasi tentang API
// ----------------------------------------------------------------------------
Route::get('/', function () {
    return response()->json([
        'message' => 'Unit Rating Feedback System API',
        'version' => '1.0.0',
        'description' => 'Sistem rating anonymous untuk unit universitas',
        'documentation' => '/api/documentation',
        'authentication' => 'Sanctum token-based authentication untuk admin endpoints',
        'endpoints' => [
            'public' => '/api/v1/units',
            'visitor' => '/api/v1/visitor',
            'admin' => '/api/v1/admin (requires authentication)',
            'auth' => '/api/v1/auth',
        ]
    ]);
});



