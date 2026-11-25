<?php

namespace App\Http\Controllers\Threat;

use App\Http\Controllers\Controller;
use App\Models\Threat;
use App\Services\ThreatExportService;
use App\Services\ThreatFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

class ThreatExportController extends Controller
{
    public function __invoke(Request $request, string $format, ThreatExportService $exporter): Response|RedirectResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            ...ThreatFilter::rules(),
        ]);

        $query = ThreatFilter::apply(
            Threat::query(),
            $validated['search'] ?? null,
            $validated['type'] ?? null,
            $validated['date_from'] ?? null,
            $validated['date_to'] ?? null
        )->orderByDesc('date')->orderByDesc('id');

        if (! (clone $query)->exists()) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Tidak ada data yang cocok dengan filter untuk diekspor.');
        }

        $filename = 'threatmetrics_'.now()->format('Ymd_His');

        return match ($format) {
            'xlsx' => $this->downloadXlsx($query, $exporter, $filename.'.xlsx'),
            'csv' => $this->downloadCsv($query, $exporter, $filename.'.csv'),
            default => $this->downloadSpss($query, $exporter, $filename.'_spss.zip'),
        };
    }

    private function downloadXlsx(Builder $query, ThreatExportService $exporter, string $filename): Response
    {
        return response()->streamDownload(function () use ($query, $exporter) {
            $spreadsheet = $exporter->buildXlsx($query->lazy(500));
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function downloadCsv(Builder $query, ThreatExportService $exporter, string $filename): Response
    {
        return response()->streamDownload(function () use ($query, $exporter) {
            $out = fopen('php://output', 'w');
            $exporter->writeCsv($out, $query->lazy(500)->map(
                fn (Threat $threat) => $exporter->toRow($threat)
            ));
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function downloadSpss(Builder $query, ThreatExportService $exporter, string $filename): Response
    {
        $rows = $query->lazy(500)
            ->map(fn (Threat $threat) => $exporter->toSpssRow($threat))
            ->all();

        $path = $exporter->zipSpss($exporter->buildSpss($rows));

        return response()
            ->download($path, $filename, ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend(true);
    }
}
