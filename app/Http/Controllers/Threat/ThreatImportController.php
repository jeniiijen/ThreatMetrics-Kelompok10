<?php

namespace App\Http\Controllers\Threat;

use App\Http\Controllers\Controller;
use App\Services\ThreatExportService;
use App\Services\ThreatImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class ThreatImportController extends Controller
{
    public function store(Request $request, ThreatImportService $importer): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'extensions:xlsx,csv', 'mimes:xlsx,csv,txt', 'max:5120'],
        ], [
            'file.required' => 'Pilih file .xlsx atau .csv terlebih dahulu.',
            'file.extensions' => 'File harus berekstensi .xlsx atau .csv.',
            'file.mimes' => 'Isi file harus berupa spreadsheet .xlsx atau teks .csv.',
            'file.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        try {
            $report = $importer->import($request->file('file')->getRealPath());
        } catch (RuntimeException $e) {
            return redirect()
                ->route('dashboard')
                ->with('error', $e->getMessage());
        }

        $summary = $report['summary'];
        $message = 'Import selesai: '.$summary[ThreatImportService::STATUS_SUCCESS].' berhasil, '
            .$summary[ThreatImportService::STATUS_REJECTED].' ditolak (duplikat), '
            .$summary[ThreatImportService::STATUS_INVALID].' tidak valid dari '.$summary['total'].' baris.';

        if ($report['details'] !== []) {
            $first = $report['details'][0];
            $message .= ' Contoh masalah baris '.$first['row'].': '.$first['reason'].'.';
        }

        return redirect()
            ->route('dashboard')
            ->with($summary[ThreatImportService::STATUS_SUCCESS] > 0 ? 'success' : 'error', $message)
            ->with('import_report', $report);
    }

    public function template(string $format, ThreatExportService $exporter): Response
    {
        if ($format === 'csv') {
            return response()->streamDownload(function () use ($exporter) {
                $out = fopen('php://output', 'w');
                $exporter->writeCsv($out, $exporter->templateRows());
                fclose($out);
            }, 'template_impor_threat.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return response()->streamDownload(function () use ($exporter) {
            $spreadsheet = $exporter->buildTemplateXlsx();
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'template_impor_threat.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}