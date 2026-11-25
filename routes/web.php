
<?php

use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\ThreatController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.store');

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('register.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/account/settings', [AccountSettingsController::class, 'show'])
        ->name('account.settings');

    Route::put('/account/settings/password', [AccountSettingsController::class, 'updatePassword'])
        ->middleware('throttle:5,1')
        ->name('account.password.update');

    // Dashboard: Admin dan User
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Threat Records - Admin Only
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin')->group(function () {

        Route::get('/threatmetrics/create', [ThreatController::class, 'create'])
            ->name('threatmetrics.create');

        Route::post('/threatmetrics', [ThreatController::class, 'store'])
            ->name('threatmetrics.store');

        Route::get('/threatmetrics/{threatmetric}/edit', [ThreatController::class, 'edit'])
            ->name('threatmetrics.edit');

        Route::put('/threatmetrics/{threatmetric}', [ThreatController::class, 'update'])
            ->name('threatmetrics.update');

        Route::patch('/threatmetrics/{threatmetric}', [ThreatController::class, 'update']);

        Route::delete('/threatmetrics/{threatmetric}', [ThreatController::class, 'destroy'])
            ->name('threatmetrics.destroy');

        // Refresh TweetFeed API: Admin Only
        Route::post('/tweetfeed/sync', SyncController::class)
            ->name('tweetfeed.sync');

    });

    /*
    |--------------------------------------------------------------------------
    | Threat Records - Admin and User (Read Only)
    |--------------------------------------------------------------------------
    */

    Route::get('/threatmetrics', [ThreatController::class, 'index'])
        ->name('threatmetrics.index');

    Route::get('/threatmetrics/{threatmetric}', [ThreatController::class, 'show'])
        ->name('threatmetrics.show');

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

});
