<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ThreatController;

Route::get('/', function () {
    return redirect()->route('threatmetrics.index');
});

Route::resource('threatmetrics', ThreatController::class)
    ->names('threatmetrics');