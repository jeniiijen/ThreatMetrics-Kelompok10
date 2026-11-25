<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

/**
 * Filter data threat yang dipakai bersama oleh tabel DataTables dan ekspor.
 * Dibuat oleh Anggota 4.
 */
class ThreatFilter
{
    /**
     * Aturan validasi untuk filter tipe dan rentang tanggal.
     *
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'type' => ['nullable', 'string', 'max:20'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }

    /**
     * Terapkan pencarian, filter tipe, dan filter tanggal ke query Threat.
     */
    public static function apply(
        Builder $query,
        ?string $search,
        ?string $type,
        ?string $dateFrom,
        ?string $dateTo
    ): Builder {
        if ($search !== null && $search !== '') {
            $like = '%'.$search.'%';
            $lowerLike = '%'.mb_strtolower($search).'%';

            $query->where(function (Builder $q) use ($like, $lowerLike) {
                $q->where('threat_id', 'like', $like)
                    ->orWhere('user', 'like', $like)
                    ->orWhere('type', 'like', $like)
                    ->orWhere('value', 'like', $like)
                    ->orWhere('tweet', 'like', $like)
                    ->orWhereRaw('LOWER(CAST(tags AS CHAR)) LIKE ?', [$lowerLike]);
            });
        }

        if ($type !== null && $type !== '') {
            $query->whereRaw('LOWER(type) = ?', [mb_strtolower($type)]);
        }

        if ($dateFrom !== null && $dateFrom !== '') {
            $query->whereDate('date', '>=', $dateFrom);
        }

        if ($dateTo !== null && $dateTo !== '') {
            $query->whereDate('date', '<=', $dateTo);
        }

        return $query;
    }
}
