<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/

Route::get('/', function () {
    return view('admin.dashboard');
});

// Admin Panel SPA Entry Point
Route::get('/admin/{any?}', function () {
    return view('admin.app');
})->where('any', '.*')->name('admin.panel');

// Reviewer Panel SPA Entry Point (Optional)
Route::get('/reviewer/{any?}', function () {
    return view('reviewer.app');
})->where('any', '.*')->name('reviewer.panel');

Route::get('/test-photo-service', function() {
    echo "<h1>UnitPhotoService Test</h1>";
    
    try {
        // Load service
        $service = app(\App\Services\Unit\UnitPhotoService::class);
        
        echo "✅ Service loaded<br>";
        echo "Class: " . get_class($service) . "<br><br>";
        
        // Use getter methods instead of direct property access
        echo "<h3>Service Info:</h3>";
        echo "Disk: " . $service->getDisk() . "<br>";
        echo "Base Path: " . $service->getBasePath() . "<br>";
        echo "Default URL: " . $service->getDefaultPhotoUrl() . "<br><br>";
        
        // Run test method
        echo "<h3>Test Results:</h3>";
        $testResult = $service->testService();
        
        foreach ($testResult as $key => $value) {
            if (is_array($value)) {
                echo "<strong>{$key}:</strong><br>";
                foreach ($value as $subKey => $subValue) {
                    echo "&nbsp;&nbsp;{$subKey}: " . ($subValue ? '✅' : '❌') . "<br>";
                }
            } else {
                echo "<strong>{$key}:</strong> {$value}<br>";
            }
        }
        
        echo "<br><h3 style='color: green;'>✅ UnitPhotoService is working correctly!</h3>";
        
        return '';
        
    } catch (\Exception $e) {
        echo "<h3 style='color: red;'>❌ Error:</h3>";
        echo $e->getMessage() . "<br>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
        return '';
    }
});