<?php

namespace App\Http\Controllers;

use App\Models\Threat;
use FPDF;
use Illuminate\Http\Request;

/**
 * Anggota 5: Generator laporan PDF menggunakan FPDF.
 *
 * Menghasilkan laporan detail ancaman beserta catatan/analisis
 * yang ditulis dengan Quill Editor (HTML diubah ke teks PDF).
 */
class ThreatPdfController extends Controller
{
    public function __invoke(Threat $threatmetric)
    {
        $threat = $threatmetric;

        $pdf = new class extends FPDF {
            // Judul yang ditampilkan di header setiap halaman.
            public string $reportTitle = 'Laporan Threat';
            public string $threatId = '';

            public function Header(): void
            {
                $this->SetFont('Helvetica', 'B', 16);
                $this->SetTextColor(21, 155, 232);
                $this->Cell(0, 10, $this->reportTitle, 0, 1, 'L');

                $this->SetFont('Helvetica', '', 10);
                $this->SetTextColor(141, 164, 187);
                $this->Cell(0, 6, 'ThreatMetrics - Threat Intelligence Report', 0, 1, 'L');

                if ($this->threatId) {
                    $this->SetFont('Helvetica', 'B', 11);
                    $this->SetTextColor(240, 244, 248);
                    $this->Cell(0, 7, 'ID: ' . $this->threatId, 0, 1, 'L');
                }

                $this->SetDrawColor(52, 68, 83);
                $this->Line(10, $this->GetY() + 1, 200, $this->GetY() + 1);
                $this->Ln(8);
            }

            public function Footer(): void
            {
                $this->SetY(-15);
                $this->SetFont('Helvetica', 'I', 8);
                $this->SetTextColor(141, 164, 187);
                $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'C');
            }

            /**
             * Menulis teks panjang dengan wrap dan menangani beberapa
             * tag HTML umum dari Quill (bold, italic, underline, link, dll.).
             */
            public function WriteHtml(string $html): void
            {
                // Normalisasi whitespace dan hapus tag yang tidak dikenali.
                $html = preg_replace('/\r\n|\r/', "\n", $html);
                $html = preg_replace('/\n\s+\n/', "\n", $html);
                $html = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $html);
                $html = preg_replace('/<\s*p\s*>/i', '', $html);
                $html = preg_replace('/<\s*\/\s*p\s*>/i', "\n", $html);
                $html = preg_replace('/<\s*\/\s*(h[1-6]|div|li|blockquote|pre)\s*>/i', "\n", $html);

                $tokens = preg_split(
                    '/(<[^>]+>)/',
                    $html,
                    -1,
                    PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
                );

                $style = '';
                $inList = false;
                $listCounter = 0;

                foreach ($tokens as $token) {
                    if (preg_match('/^<\s*([a-z][a-z0-9]*)\b[^>]*>$/i', $token, $m)) {
                        $tag = strtolower($m[1]);
                        switch ($tag) {
                            case 'b':
                            case 'strong':
                                $style .= 'B';
                                $this->SetFont('Helvetica', $style ?: '', $this->FontSize);
                                break;
                            case 'i':
                            case 'em':
                                $style .= 'I';
                                $this->SetFont('Helvetica', $style ?: '', $this->FontSize);
                                break;
                            case 'u':
                                $style .= 'U';
                                $this->SetFont('Helvetica', $style ?: '', $this->FontSize);
                                break;
                            case 'h1':
                                $this->Ln(4);
                                $this->SetFont('Helvetica', 'B', 16);
                                $this->SetTextColor(255, 255, 255);
                                break;
                            case 'h2':
                                $this->Ln(3);
                                $this->SetFont('Helvetica', 'B', 14);
                                $this->SetTextColor(255, 255, 255);
                                break;
                            case 'h3':
                                $this->Ln(2);
                                $this->SetFont('Helvetica', 'B', 12);
                                $this->SetTextColor(232, 240, 247);
                                break;
                            case 'ul':
                                $inList = true;
                                $listCounter = 0;
                                $this->Ln(2);
                                break;
                            case 'ol':
                                $inList = true;
                                $listCounter = 0;
                                $this->Ln(2);
                                break;
                            case 'li':
                                $listCounter++;
                                $this->SetX($this->lMargin + 5);
                                $bullet = ($token === strip_tags($token) || stripos($html, '<ol') !== false)
                                    ? $listCounter . '. '
                                    : chr(149) . ' ';
                                $this->Write(5, $bullet);
                                break;
                            case 'blockquote':
                                $this->Ln(2);
                                $this->SetX($this->lMargin + 8);
                                $this->SetTextColor(184, 216, 238);
                                break;
                            case 'code':
                            case 'pre':
                                $this->SetFont('Courier', '', 9);
                                $this->SetTextColor(255, 255, 255);
                                break;
                            case 'a':
                                $this->SetTextColor(21, 155, 232);
                                $style .= 'U';
                                $this->SetFont('Helvetica', $style ?: '', $this->FontSize);
                                break;
                        }
                        continue;
                    }

                    if (preg_match('/^<\s*\/\s*([a-z][a-z0-9]*)\s*>$/i', $token, $m)) {
                        $tag = strtolower($m[1]);
                        switch ($tag) {
                            case 'b':
                            case 'strong':
                            case 'i':
                            case 'em':
                            case 'u':
                            case 'a':
                                $style = '';
                                $this->SetFont('Helvetica', $style, $this->FontSize);
                                $this->SetTextColor(240, 244, 248);
                                break;
                            case 'h1':
                            case 'h2':
                            case 'h3':
                                $this->SetFont('Helvetica', '', 11);
                                $this->SetTextColor(240, 244, 248);
                                $this->Ln(3);
                                break;
                            case 'ul':
                            case 'ol':
                                $inList = false;
                                $this->Ln(2);
                                break;
                            case 'blockquote':
                                $this->SetX($this->lMargin);
                                $this->SetTextColor(240, 244, 248);
                                $this->Ln(2);
                                break;
                            case 'code':
                            case 'pre':
                                $this->SetFont('Helvetica', $style ?: '', 11);
                                $this->SetTextColor(240, 244, 248);
                                break;
                        }
                        continue;
                    }

                    // Teks biasa.
                    $text = html_entity_decode(strip_tags($token), ENT_QUOTES, 'UTF-8');
                    if ($text !== '') {
                        $this->Write(5, $text);
                    }
                }

                // Reset ke default.
                $this->SetFont('Helvetica', '', 11);
                $this->SetTextColor(240, 244, 248);
                $this->Ln(2);
            }

            /**
             * Mencetak baris label + nilai untuk detail ancaman.
             */
            public function DetailRow(string $label, string $value): void
            {
                $this->SetFont('Helvetica', 'B', 10);
                $this->SetTextColor(145, 164, 183);
                $this->Cell(35, 7, $label . ':', 0, 0, 'L');

                $this->SetFont('Helvetica', '', 10);
                $this->SetTextColor(240, 244, 248);

                $x = $this->GetX();
                $y = $this->GetY();
                $this->MultiCell(155, 7, $value, 0, 'L');

                // Pastikan posisi kembali rata setelah MultiCell.
                if ($this->GetY() < $y + 7) {
                    $this->SetY($y + 7);
                }
            }
        };

        $pdf->AliasNbPages();
        $pdf->threatId = $threat->threat_id ?? ('TH-' . str_pad($threat->id, 4, '0', STR_PAD_LEFT));
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AddPage();

        // Latar belakang halaman (tema gelap).
        $pdf->SetFillColor(23, 37, 52);
        $pdf->Rect(0, 0, 210, 297, 'F');

        // --- Informasi Umum ---
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->SetTextColor(21, 155, 232);
        $pdf->Cell(0, 8, 'Informasi Ancaman', 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->DetailRow('ID Threat', $threat->threat_id ?? ('TH-' . str_pad($threat->id, 4, '0', STR_PAD_LEFT)));
        $pdf->DetailRow('Tanggal', $threat->date ? $threat->date->format('d M Y, H:i') : '-');
        $pdf->DetailRow('User', $threat->user ?? '-');
        $pdf->DetailRow('Tipe', $threat->type ?? '-');
        $pdf->DetailRow('Nilai Indikator', $threat->value ?? '-');

        $tags = $threat->tags;
        if (is_string($tags)) {
            $decoded = json_decode($tags, true);
            $tags = is_array($decoded) ? $decoded : explode(',', $tags);
        }
        $pdf->DetailRow('Tags', is_array($tags) && count($tags) ? implode(', ', $tags) : '-');

        $pdf->DetailRow('Tweet / Deskripsi', $threat->tweet ?: '-');

        $pdf->Ln(4);

        // --- Catatan / Analisis (Quill HTML -> PDF) ---
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->SetTextColor(21, 155, 232);
        $pdf->Cell(0, 8, 'Catatan / Analisis Threat', 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->SetFont('Helvetica', '', 11);
        $pdf->SetTextColor(240, 244, 248);

        if (!empty($threat->notes)) {
            $pdf->WriteHtml($threat->notes);
        } else {
            $pdf->SetTextColor(141, 164, 187);
            $pdf->MultiCell(0, 7, 'Belum ada catatan/analisis untuk ancaman ini.', 0, 'L');
        }

        $pdf->Ln(6);

        // --- Footer laporan ---
        $pdf->SetDrawColor(52, 68, 83);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(3);
        $pdf->SetFont('Helvetica', 'I', 9);
        $pdf->SetTextColor(141, 164, 187);
        $pdf->Cell(0, 6, 'Dihasilkan oleh ThreatMetrics pada ' . now('Asia/Jakarta')->format('d M Y H:i') . ' WIB', 0, 1, 'L');

        $filename = 'threat-report-' . ($threat->threat_id ?? $threat->id) . '.pdf';

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
