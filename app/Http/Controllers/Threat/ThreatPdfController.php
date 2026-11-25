<?php

namespace App\Http\Controllers\Threat;

use App\Http\Controllers\Controller;
use App\Models\Threat;
use Carbon\Carbon;
use FPDF;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ThreatPdfController extends Controller
{
    // Export filtered threat records as a report PDF matching spreadsheet columns
    public function __invoke(Request $request, $threatmetric = null): Response
    {
        $search = trim((string) $request->query('search', ''));
        $type = strtoupper(trim((string) $request->query('type', '')));
        $tag = trim((string) $request->query('tag', ''));
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $query = Threat::query()->orderByDesc('date')->orderByDesc('id');

        if ($type !== '') {
            $query->whereRaw('UPPER(type) = ?', [$type]);
        }

        if ($tag !== '') {
            $cleanTag = ltrim($tag, '#');
            $query->where('tags', 'like', '%' . $cleanTag . '%');
        }

        if (!empty($dateFrom)) {
            $query->whereDate('date', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $query->whereDate('date', '<=', $dateTo);
        }

        if ($search !== '') {
            $query->where(function ($sub) use ($search) {
                $sub->where('threat_id', 'like', '%' . $search . '%')
                    ->orWhere('user', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%')
                    ->orWhere('value', 'like', '%' . $search . '%')
                    ->orWhere('tags', 'like', '%' . $search . '%');
            });
        }

        $records = $query->get();

        $periodParts = [];
        if (!empty($dateFrom) || !empty($dateTo)) {
            $periodParts[] = 'Tanggal: ' . ($dateFrom ?: 'Awal') . ' s/d ' . ($dateTo ?: 'Akhir');
        } else {
            $periodParts[] = 'Tanggal: Semua';
        }
        if ($type !== '') {
            $periodParts[] = 'Type: ' . $type;
        }
        if ($tag !== '') {
            $periodParts[] = 'Tag: ' . $tag;
        }
        if ($search !== '') {
            $periodParts[] = 'Cari: "' . $search . '"';
        }

        $pdf = new class extends FPDF {
            public string $reportTitle = 'THREATMETRICS - IOC REPORT';
            public string $filterSummary = 'Tanggal: Semua';

            public function Header(): void
            {
                $this->SetFont('Arial', 'B', 13);
                $this->Cell(0, 8, $this->reportTitle, 0, 1, 'L');

                $this->SetFont('Arial', '', 8.5);
                $this->Cell(0, 5.5, $this->filterSummary, 0, 1, 'L');
                $this->Cell(0, 5.5, 'Dicetak: ' . date('d M Y H:i') . ' WIB', 0, 1, 'L');
                $this->Ln(2);

                // Table header aligned with XLSX export columns
                $this->SetFont('Arial', 'B', 8);
                $this->SetFillColor(21, 34, 53);
                $this->SetTextColor(255, 255, 255);
                $this->Cell(22, 7, 'ID', 1, 0, 'L', true);
                $this->Cell(30, 7, 'DATE TIME', 1, 0, 'L', true);
                $this->Cell(28, 7, 'USER', 1, 0, 'L', true);
                $this->Cell(20, 7, 'TYPE', 1, 0, 'L', true);
                $this->Cell(58, 7, 'INDICATOR / VALUE', 1, 0, 'L', true);
                $this->Cell(32, 7, 'TAGS', 1, 1, 'L', true);
                $this->SetTextColor(0, 0, 0);
            }

            public function Footer(): void
            {
                $this->SetY(-13);
                $this->SetFont('Arial', 'I', 8);
                $this->Cell(95, 7, 'ThreatMetrics Report Export', 0, 0, 'L');
                $this->Cell(95, 7, 'Halaman ' . $this->PageNo(), 0, 0, 'R');
            }
        };

        $pdf->filterSummary = implode('  |  ', $periodParts);
        $pdf->AddPage('P', 'A4');
        $pdf->SetFont('Arial', '', 7.5);

        if ($records->isEmpty()) {
            $pdf->Cell(190, 8, 'Tidak ada data ancaman sesuai filter yang dipilih.', 1, 1, 'C');
        } else {
            foreach ($records as $item) {
                $threatCode = $item->threat_id
                    ?: ($item->id ? 'TH-' . str_pad((string) $item->id, 4, '0', STR_PAD_LEFT) : '-');

                $dateFormatted = !empty($item->date)
                    ? Carbon::parse($item->date)->format('d M Y H:i')
                    : '-';

                $rawTags = $item->tags;
                if (is_array($rawTags)) {
                    $tagList = $rawTags;
                } elseif (is_string($rawTags) && trim($rawTags) !== '') {
                    $decoded = json_decode($rawTags, true);
                    $tagList = is_array($decoded) ? $decoded : explode(',', $rawTags);
                } else {
                    $tagList = [];
                }

                $tagsText = collect($tagList)
                    ->map(fn ($t) => trim(str_replace('"', '', (string) $t)))
                    ->filter()
                    ->implode(', ');

                $pdf->Cell(22, 7, $this->fitText((string) $threatCode, 14), 1, 0, 'L');
                $pdf->Cell(30, 7, $this->fitText((string) $dateFormatted, 20), 1, 0, 'L');
                $pdf->Cell(28, 7, $this->fitText((string) ($item->user ?? '-'), 18), 1, 0, 'L');
                $pdf->Cell(20, 7, $this->fitText(strtoupper((string) ($item->type ?? '-')), 12), 1, 0, 'L');
                $pdf->Cell(58, 7, $this->fitText((string) ($item->value ?? '-'), 42), 1, 0, 'L');
                $pdf->Cell(32, 7, $this->fitText($tagsText !== '' ? $tagsText : '-', 22), 1, 1, 'L');
            }
        }

        $fromSlug = !empty($dateFrom) ? $dateFrom : 'all';
        $toSlug = !empty($dateTo) ? $dateTo : 'all';
        $filename = "threatmetrics-report-{$fromSlug}-to-{$toSlug}.pdf";

        return response($pdf->Output('S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // Truncate long strings cleanly so single-line table cells stay aligned
    private function fitText(string $text, int $maxLen): string
    {
        $clean = preg_replace('/\s+/', ' ', trim($text)) ?? '';
        if (mb_strlen($clean) <= $maxLen) {
            return $clean;
        }

        return mb_substr($clean, 0, max(1, $maxLen - 3)) . '...';
    }
}