<?php

namespace App\Services;

use App\Models\Threat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;
use ZipArchive;

/**
 * Pembuat file ekspor (.xlsx, .csv, paket SPSS) dan template impor.
 * Dibuat oleh Anggota 4.
 */
class ThreatExportService
{
    /**
     * Header kolom. Sama dengan header yang dibaca oleh ThreatImportService.
     *
     * @var array<int, string>
     */
    public const HEADERS = ['threat_id', 'date', 'user', 'type', 'value', 'tags', 'tweet'];

    public const SPSS_CSV_NAME = 'threats_spss.csv';

    public const SPSS_SYNTAX_NAME = 'threats_spss.sps';

    /**
     * Ubah satu model Threat menjadi baris datar untuk CSV.
     *
     * @return array<int, string>
     */
    public function toRow(Threat $threat): array
    {
        return [
            $threat->threat_id ?? 'TH-'.str_pad((string) $threat->id, 4, '0', STR_PAD_LEFT),
            $threat->date?->format('Y-m-d H:i:s') ?? '',
            (string) $threat->user,
            (string) $threat->type,
            (string) $threat->value,
            implode(', ', array_map('strval', (array) ($threat->tags ?? []))),
            (string) ($threat->tweet ?? ''),
        ];
    }

    /**
     * Tulis CSV (UTF-8 + BOM agar Excel membaca karakter dengan benar).
     * Sel yang diawali = + - @ diberi tanda ' di depan untuk mencegah CSV/formula injection.
     *
     * @param  resource  $out
     * @param  iterable<int, array<int, string>>  $rows
     */
    public function writeCsv($out, iterable $rows): void
    {
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, self::HEADERS, ',', '"', '');

        foreach ($rows as $row) {
            fputcsv($out, array_map([$this, 'neutralizeFormula'], $row), ',', '"', '');
        }
    }

    /**
     * Ubah model Threat menjadi baris mentah untuk buildSpss().
     * Tanggal memakai pemisah "/" (yyyy/mm/dd) agar terbaca format SDATE SPSS.
     *
     * @return array{threat_id: string, date: string, time: string, user: string, type: string, value: string, tags: array<int, string>}
     */
    public function toSpssRow(Threat $threat): array
    {
        return [
            'threat_id' => $threat->threat_id ?? 'TH-'.str_pad((string) $threat->id, 4, '0', STR_PAD_LEFT),
            'date' => $threat->date?->format('Y/m/d') ?? '',
            'time' => $threat->date?->format('H:i:s') ?? '',
            'user' => (string) $threat->user,
            'type' => (string) $threat->type,
            'value' => (string) $threat->value,
            'tags' => array_map('strval', (array) ($threat->tags ?? [])),
        ];
    }

    /**
     * Cegah sel dibaca Excel sebagai rumus.
     */
    public function neutralizeFormula(string $value): string
    {
        if ($value !== '' && preg_match('/^[=+\-@\t\r]/', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }

    /**
     * Buat file Excel dari data threat. Semua teks ditulis sebagai string
     * eksplisit sehingga tidak pernah dievaluasi sebagai rumus.
     *
     * @param  iterable<int, Threat>  $threats
     */
    public function buildXlsx(iterable $threats): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Threats');

        $this->writeHeader($sheet);

        $rowNumber = 1;

        foreach ($threats as $threat) {
            $rowNumber++;
            $row = $this->toRow($threat);

            $sheet->setCellValueExplicit('A'.$rowNumber, $row[0], DataType::TYPE_STRING);

            if ($threat->date !== null) {
                $sheet->setCellValue('B'.$rowNumber, Date::dateTimeToExcel($threat->date));
            }

            $sheet->setCellValueExplicit('C'.$rowNumber, $row[2], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D'.$rowNumber, $row[3], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E'.$rowNumber, $row[4], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('F'.$rowNumber, $row[5], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('G'.$rowNumber, mb_substr($row[6], 0, 32000), DataType::TYPE_STRING);
        }

        if ($rowNumber > 1) {
            $sheet->getStyle('B2:B'.$rowNumber)
                ->getNumberFormat()
                ->setFormatCode('yyyy-mm-dd hh:mm:ss');

            $sheet->setAutoFilter('A1:G'.$rowNumber);
        }

        $this->applyLayout($sheet);

        return $spreadsheet;
    }

    /**
     * Template kosong untuk impor, berisi dua baris contoh.
     */
    public function buildTemplateXlsx(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template');

        $this->writeHeader($sheet);

        foreach ($this->templateRows() as $index => $row) {
            foreach ($row as $column => $value) {
                $sheet->setCellValueExplicit(
                    chr(65 + $column).($index + 2),
                    $value,
                    DataType::TYPE_STRING
                );
            }
        }

        $this->applyLayout($sheet);

        return $spreadsheet;
    }

    /**
     * Baris contoh untuk template impor.
     *
     * @return array<int, array<int, string>>
     */
    public function templateRows(): array
    {
        return [
            ['', '2026-11-25 14:30:00', 'PhishStats', 'domain', 'login-secure-update.com', '#phishing, #credential-theft', 'Contoh catatan tweet'],
            ['', '2026-11-25 15:05:00', 'MalwareHunter', 'ip', '45.33.22.11', '#botnet', ''],
        ];
    }

    /**
     * Buat paket SPSS: CSV bernilai numerik terkode + syntax (.sps) yang
     * membaca CSV, memberi label variabel, dan label nilai.
     *
     * @param  array<int, array{threat_id: string, date: string, time: string, user: string, type: string, value: string, tags: array<int, string>}>  $rows
     * @return array{csv: string, syntax: string}
     */
    public function buildSpss(array $rows): array
    {
        $userCodes = $this->buildCodeMap(array_column($rows, 'user'));
        $typeCodes = $this->buildCodeMap(array_map('strtoupper', array_column($rows, 'type')));

        $maxId = 1;
        $maxValue = 1;
        $maxTags = 1;

        $stream = fopen('php://temp', 'w+');
        fputcsv($stream, [
            'threat_id', 'tanggal', 'jam', 'pengguna', 'tipe',
            'nilai', 'tags', 'jumlah_tag', 'panjang_nilai',
        ], ',', '"', '');

        foreach ($rows as $row) {
            $id = $this->singleLine($row['threat_id']);
            $value = $this->singleLine($row['value']);
            $tags = $this->singleLine(implode(';', $row['tags']));

            $maxId = max($maxId, strlen($id));
            $maxValue = max($maxValue, strlen($value));
            $maxTags = max($maxTags, strlen($tags));

            fputcsv($stream, [
                $id,
                $row['date'],
                $row['time'],
                $userCodes[(string) $row['user']],
                $typeCodes[strtoupper($row['type'])],
                $value,
                $tags,
                count($row['tags']),
                mb_strlen($value),
            ], ',', '"', '');
        }

        rewind($stream);
        $csv = (string) stream_get_contents($stream);
        fclose($stream);

        $syntax = $this->buildSpssSyntax(
            $userCodes,
            $typeCodes,
            min($maxId, 32767),
            min($maxValue, 32767),
            min($maxTags, 32767)
        );

        return ['csv' => $csv, 'syntax' => $syntax];
    }

    /**
     * Bungkus CSV dan syntax SPSS ke dalam satu file ZIP sementara.
     * Pemanggil bertanggung jawab menghapus file yang dikembalikan.
     *
     * @param  array{csv: string, syntax: string}  $spss
     */
    public function zipSpss(array $spss): string
    {
        $path = tempnam(sys_get_temp_dir(), 'tm_spss_');

        if ($path === false) {
            throw new RuntimeException('Tidak dapat membuat file sementara untuk ekspor SPSS.');
        }

        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Tidak dapat membuat file ZIP untuk ekspor SPSS.');
        }

        $zip->addFromString(self::SPSS_CSV_NAME, $spss['csv']);
        $zip->addFromString(self::SPSS_SYNTAX_NAME, $spss['syntax']);
        $zip->close();

        return $path;
    }

    /**
     * Header tebal, latar biru, dan baris pertama dibekukan.
     */
    private function writeHeader(Worksheet $sheet): void
    {
        $sheet->fromArray(self::HEADERS, null, 'A1');

        $headerStyle = $sheet->getStyle('A1:G1');
        $headerStyle->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1688D4');

        $sheet->freezePane('A2');
    }

    /**
     * Lebar kolom tetap agar nilai panjang tidak melebarkan sheet.
     */
    private function applyLayout(Worksheet $sheet): void
    {
        foreach (['A' => 12, 'B' => 20, 'C' => 18, 'D' => 10, 'E' => 55, 'F' => 30, 'G' => 60] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
    }

    /**
     * Petakan nilai unik (urut alfabet) ke kode 1..n.
     *
     * @param  array<int, mixed>  $values
     * @return array<string, int>
     */
    private function buildCodeMap(array $values): array
    {
        $unique = array_values(array_unique(array_map('strval', $values)));
        sort($unique, SORT_NATURAL | SORT_FLAG_CASE);

        $map = [];

        foreach ($unique as $index => $value) {
            $map[$value] = $index + 1;
        }

        return $map;
    }

    /**
     * Hilangkan baris baru karena syntax SPSS membaca satu kasus per baris.
     */
    private function singleLine(string $value): string
    {
        return trim(str_replace(["\r\n", "\r", "\n"], ' ', $value));
    }

    /**
     * Escape petik tunggal dan potong label sesuai batas SPSS (120 byte).
     */
    private function spssLabel(string $label): string
    {
        $label = mb_strcut($this->singleLine($label), 0, 120, 'UTF-8');

        return "'".str_replace("'", "''", $label)."'";
    }

    /**
     * @param  array<string, int>  $userCodes
     * @param  array<string, int>  $typeCodes
     */
    private function buildSpssSyntax(
        array $userCodes,
        array $typeCodes,
        int $idWidth,
        int $valueWidth,
        int $tagsWidth
    ): string {
        $lines = [
            '* ThreatMetrics - syntax SPSS (dibuat otomatis '.date('Y-m-d H:i:s').').',
            '* Cara pakai: ekstrak ZIP, buka file ini di SPSS, lalu ubah path pada baris FILE',
            "* jika file CSV tidak berada di folder kerja SPSS, kemudian Run > All.",
            '',
            'GET DATA',
            '  /TYPE=TXT',
            "  /FILE='".self::SPSS_CSV_NAME."'",
            "  /ENCODING='UTF8'",
            '  /DELCASE=LINE',
            '  /DELIMITERS=","',
            "  /QUALIFIER='\"'",
            '  /ARRANGEMENT=DELIMITED',
            '  /FIRSTCASE=2',
            '  /VARIABLES=',
            '  threat_id A'.$idWidth,
            '  tanggal SDATE10',
            '  jam TIME8',
            '  pengguna F8.0',
            '  tipe F8.0',
            '  nilai A'.$valueWidth,
            '  tags A'.$tagsWidth,
            '  jumlah_tag F5.0',
            '  panjang_nilai F8.0.',
            'CACHE.',
            'EXECUTE.',
            '',
            'VARIABLE LABELS',
            "  threat_id 'ID threat'",
            "  tanggal 'Tanggal threat'",
            "  jam 'Jam threat'",
            "  pengguna 'Pengguna pelapor'",
            "  tipe 'Tipe indikator (IOC)'",
            "  nilai 'Nilai indikator'",
            "  tags 'Tag (dipisah titik koma)'",
            "  jumlah_tag 'Jumlah tag'",
            "  panjang_nilai 'Panjang karakter nilai indikator'.",
            '',
            'VALUE LABELS',
            '  pengguna',
        ];

        foreach ($userCodes as $label => $code) {
            $lines[] = '    '.$code.' '.$this->spssLabel((string) $label);
        }

        $lines[] = '  /tipe';

        foreach ($typeCodes as $label => $code) {
            $lines[] = '    '.$code.' '.$this->spssLabel((string) $label);
        }

        $lines[] = '  .';
        $lines[] = '';
        $lines[] = 'VARIABLE LEVEL pengguna tipe (NOMINAL)';
        $lines[] = '  /jumlah_tag panjang_nilai (SCALE).';
        $lines[] = 'EXECUTE.';
        $lines[] = '';
        $lines[] = "* Untuk menyimpan sebagai .sav, hapus tanda * lalu ubah path:";
        $lines[] = "* SAVE OUTFILE='threats.sav'.";

        return implode("\r\n", $lines)."\r\n";
    }
}
