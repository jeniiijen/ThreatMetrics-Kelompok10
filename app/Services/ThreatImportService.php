<?php

namespace App\Services;

use App\Models\Threat;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use RuntimeException;
use Throwable;

/**
 * Impor data threat dari file .xlsx / .csv.
 * Setiap baris mendapat salah satu status: berhasil, ditolak, atau tidak valid.
 * Dibuat oleh Anggota 4.
 */
class ThreatImportService
{
    public const STATUS_SUCCESS = 'berhasil';

    public const STATUS_REJECTED = 'ditolak';

    public const STATUS_INVALID = 'tidak valid';

    public const MAX_ROWS = 5000;

    public const MAX_DETAIL_ROWS = 200;

    /** @var array<int, string> */
    public const ALLOWED_TYPES = ['MD5', 'SHA256', 'DOMAIN', 'URL', 'IP'];

    /**
     * Nama header yang diterima (huruf kecil, tanpa spasi/simbol).
     *
     * @var array<string, array<int, string>>
     */
    private const COLUMN_ALIASES = [
        'threat_id' => ['threatid', 'id'],
        'date' => ['date', 'datetime', 'tanggal', 'waktu'],
        'user' => ['user', 'username', 'pengguna', 'reporter'],
        'type' => ['type', 'tipe', 'jenis', 'ioctype'],
        'value' => ['value', 'indicator', 'indicatorvalue', 'nilai', 'ioc'],
        'tags' => ['tags', 'tag'],
        'tweet' => ['tweet', 'description', 'deskripsi', 'catatan'],
    ];

    /** @var array<int, string> */
    private const REQUIRED_COLUMNS = ['date', 'user', 'type', 'value'];

    /** @var array<int, string> */
    private const DATE_FORMATS = [
        'Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d\TH:i:s', 'Y-m-d\TH:i', 'Y-m-d',
        'd/m/Y H:i:s', 'd/m/Y H:i', 'd/m/Y',
        'd-m-Y H:i:s', 'd-m-Y H:i', 'd-m-Y',
        'Y/m/d H:i:s', 'Y/m/d H:i', 'Y/m/d',
    ];

    /**
     * Baca file, validasi tiap baris, lalu simpan baris yang lolos.
     *
     * @return array{summary: array<string, int>, details: array<int, array<string, mixed>>, hidden_details: int}
     *
     * @throws RuntimeException untuk kesalahan tingkat file (tidak terbaca, kolom wajib hilang, terlalu banyak baris)
     */
    public function import(string $path): array
    {
        $matrix = $this->readSheet($path);
        [$columns, $headerIndex] = $this->mapHeader($matrix);

        $now = new DateTimeImmutable;
        $results = [];
        $valid = [];
        $rowCount = 0;

        foreach ($matrix as $index => $cells) {
            if ($index <= $headerIndex || $this->isEmptyRow($cells)) {
                continue;
            }

            $rowCount++;

            if ($rowCount > self::MAX_ROWS) {
                throw new RuntimeException('File memiliki lebih dari '.self::MAX_ROWS.' baris data. Pecah file menjadi beberapa bagian.');
            }

            $rowNumber = $index + 1;
            $raw = [];

            foreach ($columns as $field => $columnIndex) {
                $raw[$field] = $cells[$columnIndex] ?? null;
            }

            $checked = $this->validateRow($raw, $now);

            if ($checked['data'] === null) {
                $results[$rowNumber] = $this->result(
                    self::STATUS_INVALID,
                    implode('; ', $checked['errors']),
                    $this->cell($raw['type'] ?? null),
                    $this->cell($raw['value'] ?? null)
                );

                continue;
            }

            $valid[$rowNumber] = $checked['data'];
        }

        if ($rowCount === 0) {
            throw new RuntimeException('File tidak berisi baris data di bawah header.');
        }

        $pending = $this->rejectDuplicates($valid, $results);

        DB::transaction(function () use ($pending, &$results) {
            foreach ($pending as $rowNumber => $data) {
                Threat::create($data);

                $results[$rowNumber] = $this->result(
                    self::STATUS_SUCCESS,
                    'Data berhasil disimpan.',
                    $data['type'],
                    $data['value']
                );
            }
        });

        ksort($results);

        return $this->buildReport($results);
    }

    /**
     * Validasi dan normalisasi satu baris. Mengembalikan data bersih, atau
     * data null beserta daftar alasan bila baris tidak valid.
     *
     * @param  array<string, mixed>  $raw
     * @return array{data: array<string, mixed>|null, errors: array<int, string>}
     */
    public function validateRow(array $raw, DateTimeImmutable $now): array
    {
        $errors = [];

        $date = $this->parseDate($raw['date'] ?? null, $now);

        if ($this->cell($raw['date'] ?? null) === '') {
            $errors[] = 'Tanggal wajib diisi';
        } elseif ($date === null) {
            $errors[] = 'Format atau rentang tanggal tidak valid (contoh: 2026-11-25 14:30:00)';
        }

        $user = $this->cell($raw['user'] ?? null);

        if ($user === '') {
            $errors[] = 'User wajib diisi';
        } elseif (mb_strlen($user) > 255) {
            $errors[] = 'User maksimal 255 karakter';
        }

        $type = strtoupper($this->cell($raw['type'] ?? null));
        $value = $this->cell($raw['value'] ?? null);

        if ($type === '') {
            $errors[] = 'Tipe wajib diisi';
        } elseif (! in_array($type, self::ALLOWED_TYPES, true)) {
            $errors[] = 'Tipe "'.mb_strimwidth($type, 0, 30, '...').'" tidak dikenal (gunakan: '.implode(', ', self::ALLOWED_TYPES).')';
        }

        if ($value === '') {
            $errors[] = 'Value wajib diisi';
        } elseif (mb_strlen($value) > 2048) {
            $errors[] = 'Value maksimal 2048 karakter';
        } elseif (in_array($type, self::ALLOWED_TYPES, true)) {
            $value = $this->normalizeValue($type, $value);

            if (! $this->isValidValue($type, $value)) {
                $errors[] = 'Value tidak sesuai format tipe '.$type;
            }
        }

        $tags = $this->parseTags($this->cell($raw['tags'] ?? null), $errors);

        $tweet = $this->cell($raw['tweet'] ?? null, true);

        if (mb_strlen($tweet) > 5000) {
            $errors[] = 'Tweet maksimal 5000 karakter';
        }

        if ($errors !== []) {
            return ['data' => null, 'errors' => $errors];
        }

        return [
            'data' => [
                'date' => $date->format('Y-m-d H:i:s'),
                'user' => $user,
                'type' => $type,
                'value' => $value,
                'tags' => $tags,
                'tweet' => $tweet === '' ? null : $tweet,
            ],
            'errors' => [],
        ];
    }

    /**
     * Baca sheet pertama menjadi matriks. Hanya reader Xlsx dan Csv yang diizinkan,
     * rumus tidak dievaluasi, dan pembacaan dibatasi MAX_ROWS + header + 1 baris.
     *
     * @return array<int, array<int, mixed>>
     */
    private function readSheet(string $path): array
    {
        try {
            $reader = IOFactory::createReaderForFile($path, [IOFactory::READER_XLSX, IOFactory::READER_CSV]);
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);

            if ($reader instanceof Csv) {
                $reader->setInputEncoding(Csv::GUESS_ENCODING);
                $reader->setDelimiter(null);
            }

            $reader->setReadFilter(new class(self::MAX_ROWS + 2) implements IReadFilter
            {
                public function __construct(private int $lastRow) {}

                public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
                {
                    return $row <= $this->lastRow;
                }
            });

            $spreadsheet = $reader->load($path);
            $matrix = $spreadsheet->getSheet(0)->toArray(null, false, false, false);
            $spreadsheet->disconnectWorksheets();
        } catch (Throwable $e) {
            Log::warning('Impor threat: file tidak terbaca. '.$e->getMessage());

            throw new RuntimeException('File tidak dapat dibaca. Pastikan formatnya .xlsx atau .csv yang valid.', 0, $e);
        }

        return $matrix;
    }

    /**
     * Cari baris header dan petakan kolom ke nama field.
     *
     * @param  array<int, array<int, mixed>>  $matrix
     * @return array{0: array<string, int>, 1: int}
     */
    private function mapHeader(array $matrix): array
    {
        $aliasMap = [];

        foreach (self::COLUMN_ALIASES as $field => $aliases) {
            foreach ($aliases as $alias) {
                $aliasMap[$alias] = $field;
            }
        }

        foreach ($matrix as $index => $cells) {
            if ($this->isEmptyRow($cells)) {
                continue;
            }

            $columns = [];

            foreach ($cells as $columnIndex => $cell) {
                $name = preg_replace('/[^a-z0-9]/', '', strtolower($this->cell($cell))) ?? '';

                if (isset($aliasMap[$name]) && ! isset($columns[$aliasMap[$name]])) {
                    $columns[$aliasMap[$name]] = $columnIndex;
                }
            }

            $missing = array_diff(self::REQUIRED_COLUMNS, array_keys($columns));

            if ($missing !== []) {
                throw new RuntimeException(
                    'Kolom wajib tidak ditemukan di header: '.implode(', ', $missing).'. Gunakan template impor.'
                );
            }

            return [$columns, $index];
        }

        throw new RuntimeException('File kosong.');
    }

    /**
     * Tandai baris valid yang sudah ada di database atau muncul lebih dulu di file.
     * Kunci duplikat: tanggal + user + tipe + value.
     *
     * @param  array<int, array<string, mixed>>  $valid
     * @param  array<int, array<string, mixed>>  $results
     * @return array<int, array<string, mixed>> baris yang boleh disimpan
     */
    private function rejectDuplicates(array $valid, array &$results): array
    {
        $seen = $this->existingKeys(array_column($valid, 'value'));
        $pending = [];

        foreach ($valid as $rowNumber => $data) {
            $key = $this->duplicateKey($data['date'], $data['user'], $data['type'], $data['value']);

            if (isset($seen[$key])) {
                $results[$rowNumber] = $this->result(
                    self::STATUS_REJECTED,
                    'Duplikat: data dengan tanggal, user, tipe, dan value yang sama sudah ada',
                    $data['type'],
                    $data['value']
                );

                continue;
            }

            $seen[$key] = true;
            $pending[$rowNumber] = $data;
        }

        return $pending;
    }

    /**
     * @param  array<int, string>  $values
     * @return array<string, true>
     */
    private function existingKeys(array $values): array
    {
        $keys = [];

        foreach (array_chunk(array_values(array_unique($values)), 500) as $chunk) {
            $existing = Threat::query()
                ->whereIn('value', $chunk)
                ->get(['date', 'user', 'type', 'value']);

            foreach ($existing as $threat) {
                $keys[$this->duplicateKey(
                    $threat->date?->format('Y-m-d H:i:s') ?? '',
                    (string) $threat->user,
                    (string) $threat->type,
                    (string) $threat->value
                )] = true;
            }
        }

        return $keys;
    }

    private function duplicateKey(string $date, string $user, string $type, string $value): string
    {
        return mb_strtolower(implode('|', [$date, $user, $type, $value]));
    }

    /**
     * @return array{status: string, reason: string, type: string, value: string}
     */
    private function result(string $status, string $reason, string $type, string $value): array
    {
        return [
            'status' => $status,
            'reason' => $reason,
            'type' => $type,
            'value' => mb_strimwidth($value, 0, 80, '...'),
        ];
    }

    /**
     * Ringkasan jumlah per status, dan rincian baris bermasalah (dibatasi).
     *
     * @param  array<int, array<string, string>>  $results
     * @return array{summary: array<string, int>, details: array<int, array<string, mixed>>, hidden_details: int}
     */
    private function buildReport(array $results): array
    {
        $summary = [
            self::STATUS_SUCCESS => 0,
            self::STATUS_REJECTED => 0,
            self::STATUS_INVALID => 0,
            'total' => count($results),
        ];

        $problems = [];

        foreach ($results as $rowNumber => $result) {
            $summary[$result['status']]++;

            if ($result['status'] !== self::STATUS_SUCCESS) {
                $problems[] = ['row' => $rowNumber] + $result;
            }
        }

        return [
            'summary' => $summary,
            'details' => array_slice($problems, 0, self::MAX_DETAIL_ROWS),
            'hidden_details' => max(0, count($problems) - self::MAX_DETAIL_ROWS),
        ];
    }

    /**
     * @param  array<int, mixed>  $cells
     */
    private function isEmptyRow(array $cells): bool
    {
        foreach ($cells as $cell) {
            if ($this->cell($cell) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Ubah isi sel menjadi string bersih: trim, buang karakter kontrol, perbaiki
     * UTF-8, dan kembalikan tanda ' pelindung formula yang ditambahkan saat ekspor CSV.
     */
    private function cell(mixed $value, bool $keepNewlines = false): string
    {
        if ($value === null || is_bool($value)) {
            return '';
        }

        if (is_float($value) && floor($value) === $value && abs($value) < 1e15) {
            $value = (int) $value;
        }

        $text = mb_scrub((string) $value, 'UTF-8');
        $pattern = $keepNewlines ? '/[\x00-\x08\x0B\x0C\x0E-\x1F]/' : '/[\x00-\x1F]/';
        $text = trim(preg_replace($pattern, '', $text) ?? '');

        if (preg_match("/^'[=+\\-@]/", $text) === 1) {
            $text = substr($text, 1);
        }

        return $text;
    }

    /**
     * Terima tanggal teks (beberapa format) atau serial angka Excel.
     * Menolak tahun sebelum 2000 dan tanggal lebih dari 1 hari ke depan.
     */
    private function parseDate(mixed $cell, DateTimeImmutable $now): ?DateTimeImmutable
    {
        $date = null;

        if (is_int($cell) || is_float($cell)) {
            $date = $this->excelSerialToDate((float) $cell);
        } else {
            $text = $this->cell($cell);

            foreach (self::DATE_FORMATS as $format) {
                $candidate = DateTimeImmutable::createFromFormat('!'.$format, $text);
                $errors = DateTimeImmutable::getLastErrors();
                $clean = $errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0);

                if ($candidate !== false && $clean) {
                    $date = $candidate;
                    break;
                }
            }
        }

        if ($date === null) {
            return null;
        }

        if ((int) $date->format('Y') < 2000 || $date > $now->modify('+1 day')) {
            return null;
        }

        return $date;
    }

    /**
     * Serial Excel (hari sejak 1899-12-30) ke tanggal. Berlaku untuk tanggal setelah Maret 1900.
     */
    private function excelSerialToDate(float $serial): ?DateTimeImmutable
    {
        if ($serial < 61 || $serial > 2958465) {
            return null;
        }

        $days = (int) floor($serial);
        $seconds = (int) round(($serial - $days) * 86400);

        return (new DateTimeImmutable('1899-12-30 00:00:00'))
            ->modify('+'.$days.' days')
            ->modify('+'.$seconds.' seconds');
    }

    private function normalizeValue(string $type, string $value): string
    {
        return in_array($type, ['MD5', 'SHA256', 'DOMAIN'], true) ? strtolower($value) : $value;
    }

    private function isValidValue(string $type, string $value): bool
    {
        return match ($type) {
            'IP' => filter_var($value, FILTER_VALIDATE_IP) !== false,
            'MD5' => preg_match('/^[a-f0-9]{32}$/', $value) === 1,
            'SHA256' => preg_match('/^[a-f0-9]{64}$/', $value) === 1,
            'DOMAIN' => filter_var($value, FILTER_VALIDATE_IP) === false
                && preg_match('/^(?=.{1,253}$)(?!-)[a-z0-9-]{1,63}(?<!-)(\.(?!-)[a-z0-9-]{1,63}(?<!-))+$/', $value) === 1,
            'URL' => preg_match('~^(https?|ftp)://[^\s<>"]+$~i', $value) === 1,
            default => false,
        };
    }

    /**
     * Pecah teks tag (pemisah , ; |) menjadi array unik.
     *
     * @param  array<int, string>  $errors
     * @return array<int, string>
     */
    private function parseTags(string $raw, array &$errors): array
    {
        if ($raw === '') {
            return [];
        }

        $tags = [];

        foreach (preg_split('/[,;|]/', $raw) ?: [] as $tag) {
            $tag = trim($tag);

            if ($tag !== '') {
                $tags[$tag] = $tag;
            }
        }

        $tags = array_values($tags);

        if (count($tags) > 50) {
            $errors[] = 'Maksimal 50 tag per baris';
        }

        foreach ($tags as $tag) {
            if (mb_strlen($tag) > 100) {
                $errors[] = 'Setiap tag maksimal 100 karakter';
                break;
            }
        }

        return $tags;
    }
}
