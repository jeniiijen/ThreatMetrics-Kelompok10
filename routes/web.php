<?php

use App\Http\Controllers\AnalystNoteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Integration\TweetFeedSyncController;
use App\Http\Controllers\Threat\ThreatDataTableController;
use App\Http\Controllers\ThreatMetricController;
use App\Http\Controllers\ThreatPdfController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Main dashboard and IOC overview
Route::get('/', [DashboardController::class, 'index'])
    ->name('dashboard');

// TweetFeed synchronization
Route::post('/tweetfeed/sync', TweetFeedSyncController::class)
    ->name('tweetfeed.sync');

// Server-side DataTables endpoint
Route::get('/threats/data', [ThreatDataTableController::class, 'index'])
    ->name('threatmetrics.data');

// Filtered report exports (.xlsx, .csv, .zip SPSS, and .pdf)
Route::get('/threats/export/pdf', [ThreatPdfController::class, 'export'])
    ->name('threatmetrics.export.pdf');

// Import template download (.xlsx / .csv)
Route::get('/threats/import/template/{format?}', function (Request $request, ?string $format = null) {
    $ext = strtolower((string) ($format ?? $request->query('format', 'xlsx')));
    if (! in_array($ext, ['xlsx', 'csv'], true)) {
        $ext = 'xlsx';
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('IOC_Template');

    $headers = ['threat_id', 'date', 'user', 'type', 'value', 'tags'];
    $sheet->fromArray($headers, null, 'A1');

    $sampleRows = [
        ['TH-0901', '2026-11-26 02:00:00', 'Analyst_A2', 'IP', '185.220.101.4', 'tor,scanner'],
        ['TH-0902', '2026-11-26 02:15:00', 'Analyst_A2', 'DOMAIN', 'malicious-login-check.example', 'phishing,credential'],
        ['TH-0903', '2026-11-26 02:30:00', 'Analyst_A2', 'URL', 'http://185.220.101.4/payload.sh', 'malware,loader'],
    ];
    $sheet->fromArray($sampleRows, null, 'A2');

    $tempFile = tempnam(sys_get_temp_dir(), 'tm_tpl_') . '.' . $ext;

    if ($ext === 'csv') {
        $writer = new Csv($spreadsheet);
        $writer->setDelimiter(',');
        $writer->setEnclosure('"');
        $writer->setLineEnding("\r\n");
        $writer->save($tempFile);
        $mime = 'text/csv; charset=UTF-8';
    } else {
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);
        $mime = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    }

    return response()->download($tempFile, "threatmetrics_import_template.{$ext}", [
        'Content-Type' => $mime,
    ])->deleteFileAfterSend(true);
})->name('threatmetrics.import.template');

// Import threat IOC file endpoint
Route::post('/threats/import', function (Request $request) {
    $request->validate([
        'file' => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:10240'],
    ]);

    return redirect()->route('dashboard')
        ->with('success', 'File import diterima (' . $request->file('file')->getClientOriginalName() . ').');
})->name('threatmetrics.import');

// IOC CRUD routes
Route::resource('threatmetrics', ThreatMetricController::class);

// Analyst notes endpoints
Route::get('/analyst-notes', [AnalystNoteController::class, 'index'])
    ->name('analyst-notes.index');
Route::post('/analyst-notes', [AnalystNoteController::class, 'store'])
    ->name('analyst-notes.store');
Route::delete('/analyst-notes/{analystNote}', [AnalystNoteController::class, 'destroy'])
    ->name('analyst-notes.destroy');