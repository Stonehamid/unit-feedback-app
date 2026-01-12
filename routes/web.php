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
    return view('welcome');
});

// Admin Panel SPA Entry Point
Route::get('/admin/{any?}', function () {
    return view('admin.app');
})->where('any', '.*')->name('admin.panel');

// Reviewer Panel SPA Entry Point (Optional)
Route::get('/reviewer/{any?}', function () {
    return view('reviewer.app');
})->where('any', '.*')->name('reviewer.panel');