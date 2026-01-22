<?php

use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\WebLoginController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/rate/{unit}', function ($unitCode) {
    return view('visitor.rate', ['unitCode' => $unitCode]);
})->name('visitor.rate');

Route::get('/thank-you', function () {
    return view('visitor.thank-you');
})->name('visitor.thank-you');

Route::get('/browse', function () {
    return view('visitor.browse');
})->name('visitor.browse');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/admin/units', function () {
        return view('admin.units.index');
    })->name('admin.units.index');

    Route::get('/admin/units/create', function () {
        return view('admin.units.create');
    })->name('admin.units.create');

    Route::get('/admin/units/{unit}/edit', function ($unit) {
        return view('admin.units.edit', ['unitId' => $unit]);
    })->name('admin.units.edit');

    Route::get('/admin/employees', function () {
        return view('admin.employees.index');
    })->name('admin.employees.index');

    Route::get('/admin/messages', function () {
        return view('admin.messages.index');
    })->name('admin.messages.index');

    Route::get('/admin/ratings', function () {
        return view('admin.ratings.index');
    })->name('admin.ratings.index');

    Route::get('/admin/ratings/{rating}', function ($rating) {
        return view('admin.ratings.show', ['ratingId' => $rating]);
    })->name('admin.ratings.show');

    Route::get('/admin/reports', function () {
        return view('admin.reports.index');
    })->name('admin.reports.index');

    Route::get('/admin/reports/{report}', function ($report) {
        return view('admin.reports.show', ['reportId' => $report]);
    })->name('admin.reports.show');

    Route::get('/admin/analytics', function () {
        return view('admin.analytics.index');
    })->name('admin.analytics.index');

    Route::get('/admin/export', function () {
        return view('admin.export.index');
    })->name('admin.export.index');

    Route::get('/admin/users', function () {
        return view('admin.users.index');
    })->name('admin.users.index');

    Route::get('/admin/settings', function () {
        return view('admin.settings.index');
    })->name('admin.settings.index');

    Route::get('/admin/audit-logs', function () {
        return view('admin.audit-logs.index');
    })->name('admin.audit-logs.index');

    Route::get('/admin/{any?}', function () {
        return view('admin.app');
    })->where('any', '.*')->name('admin.spa');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', [WebLoginController::class, 'login']);

    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
    Route::post('/logout-all', [LogoutController::class, 'logoutAll'])->name('logout.all');

    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');

    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');

    Route::get('/reset-password/{token}', function ($token) {
        return view('auth.reset-password', ['token' => $token]);
    })->name('password.reset');
});

Route::get('/docs', function () {
    return view('docs.index');
})->name('docs.index');

Route::get('/docs/api', function () {
    return view('docs.api');
})->name('docs.api');

Route::get('/help', function () {
    return view('help.index');
})->name('help.index');

Route::get('/faq', function () {
    return view('help.faq');
})->name('help.faq');

Route::get('/404', function () {
    return view('errors.404');
})->name('404');

Route::get('/500', function () {
    return view('errors.500');
})->name('500');

Route::get('/403', function () {
    return view('errors.403');
})->name('403');

Route::get('/419', function () {
    return view('errors.419');
})->name('419');

Route::get('/contact', function () {
    return view('contact.index');
})->name('contact.index');

Route::get('/support', function () {
    return view('contact.support');
})->name('contact.support');

Route::get('/privacy-policy', function () {
    return view('legal.privacy');
})->name('privacy');

Route::get('/terms-of-service', function () {
    return view('legal.terms');
})->name('terms');

Route::get('/cookie-policy', function () {
    return view('legal.cookies');
})->name('cookies');