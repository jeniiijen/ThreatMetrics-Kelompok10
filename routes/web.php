<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ThreatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SyncController;


Route::get('/', function () {
    return redirect()->route('threatmetrics.index');
});

// Dashboard Anggota 3
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

// Refresh API TweetFeed
Route::post('/tweetfeed/sync', SyncController::class)
    ->name('tweetfeed.sync');


// CRUD Threat - jangan dihapus
Route::resource('threatmetrics', ThreatController::class)
    ->names('threatmetrics');

