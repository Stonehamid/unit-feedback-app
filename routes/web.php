<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RatingController;
use App\Http\Controllers\Admin\ReportController;

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', [DashboardController::class, 'index']);

    Route::get('/units', [UnitController::class, 'index'])->name('admin.units.view');

    // Route lainnya...
    Route::get('/units/create', [UnitController::class, 'create'])->name('admin.units.create');
    Route::get('/units/{id}', [UnitController::class, 'show'])->name('admin.units.show');
    Route::get('/units/{id}/edit', [UnitController::class, 'edit'])->name('admin.units.edit');
    Route::post('/units', [UnitController::class, 'store'])->name('admin.units.store');
    Route::put('/units/{id}', [UnitController::class, 'update'])->name('admin.units.update');
    Route::delete('/units/{id}', [UnitController::class, 'destroy'])->name('admin.units.destroy');
});

Route::get('/reviewer/{any?}', function () {
    return view('reviewer.app');
})->where('any', '.*');