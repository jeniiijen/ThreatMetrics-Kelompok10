<?php

namespace App\Http\Controllers;

use App\Models\Threat;
use App\Services\ThreatFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoint JSON untuk DataTables (server-side processing).
 * Dibuat oleh Anggota 4.
 */
class ThreatDataTableController extends Controller
{
    /**
     * Kolom yang boleh diurutkan. Indeks mengikuti urutan kolom di tabel.
     *
     * @var array<int, string>
     */
    private const SORTABLE_COLUMNS = [
        0 => 'id',
        1 => 'date',
        2 => 'user',
        3 => 'type',
        4 => 'value',
    ];

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'draw' => ['nullable', 'integer', 'min:0'],
            'start' => ['nullable', 'integer', 'min:0'],
            'length' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search.value' => ['nullable', 'string', 'max:255'],
            'order.0.column' => ['nullable', 'integer', 'min:0', 'max:6'],
            'order.0.dir' => ['nullable', 'in:asc,desc'],
            ...ThreatFilter::rules(),
        ]);

        $query = ThreatFilter::apply(
            Threat::query(),
            $validated['search']['value'] ?? null,
            $validated['type'] ?? null,
            $validated['date_from'] ?? null,
            $validated['date_to'] ?? null
        );

        $recordsFiltered = (clone $query)->count();

        $column = self::SORTABLE_COLUMNS[(int) ($validated['order'][0]['column'] ?? 1)] ?? 'date';
        $direction = ($validated['order'][0]['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $threats = $query
            ->orderBy($column, $direction)
            ->orderByDesc('id')
            ->skip((int) ($validated['start'] ?? 0))
            ->take((int) ($validated['length'] ?? 10))
            ->get();

        return response()->json([
            'draw' => (int) ($validated['draw'] ?? 0),
            'recordsTotal' => Threat::count(),
            'recordsFiltered' => $recordsFiltered,
            'data' => $threats->map(fn (Threat $threat) => $this->formatRow($threat))->all(),
            'types' => $this->availableTypes(),
        ]);
    }

    /**
     * Data satu baris tabel. Nilai dikirim mentah; JavaScript yang meng-escape HTML.
     *
     * @return array<string, mixed>
     */
    private function formatRow(Threat $threat): array
    {
        return [
            'id' => $threat->id,
            'threat_id' => $threat->threat_id ?? 'TH-'.str_pad((string) $threat->id, 4, '0', STR_PAD_LEFT),
            'date' => $threat->date?->format('d M Y, H:i') ?? '-',
            'user' => (string) $threat->user,
            'type' => strtoupper((string) $threat->type),
            'value' => (string) $threat->value,
            'tags' => array_values(array_map('strval', (array) ($threat->tags ?? []))),
            'show_url' => route('threatmetrics.show', ['threatmetric' => $threat->id]),
            'edit_url' => route('threatmetrics.edit', ['threatmetric' => $threat->id]),
            'destroy_url' => route('threatmetrics.destroy', ['threatmetric' => $threat->id]),
        ];
    }

    /**
     * Daftar tipe unik untuk dropdown filter.
     *
     * @return array<int, string>
     */
    private function availableTypes(): array
    {
        return Threat::query()
            ->distinct()
            ->pluck('type')
            ->map(fn ($type) => strtoupper((string) $type))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
