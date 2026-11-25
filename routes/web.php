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

// Anggota 4 - DataTables, impor, dan ekspor Threat
// Harus berada SEBELUM Route::resource agar tidak tertangkap route {threatmetric}.
Route::prefix('threatmetrics')->name('threatmetrics.')->group(function () {
    Route::get('data', \App\Http\Controllers\ThreatDataTableController::class)
        ->name('data');

    Route::get('export/{format}', \App\Http\Controllers\ThreatExportController::class)
        ->whereIn('format', ['xlsx', 'csv', 'spss'])
        ->middleware('throttle:30,1')
        ->name('export');

    Route::get('import/template/{format}', [\App\Http\Controllers\ThreatImportController::class, 'template'])
        ->whereIn('format', ['xlsx', 'csv'])
        ->name('import.template');

    Route::post('import', [\App\Http\Controllers\ThreatImportController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('import');

    // Anggota 5 - Unduh laporan PDF (FPDF)
    Route::get('{threatmetric}/pdf', \App\Http\Controllers\ThreatPdfController::class)
        ->middleware('throttle:30,1')
        ->name('pdf');
});


// CRUD Threat - jangan dihapus
Route::resource('threatmetrics', ThreatController::class)
    ->names('threatmetrics');

