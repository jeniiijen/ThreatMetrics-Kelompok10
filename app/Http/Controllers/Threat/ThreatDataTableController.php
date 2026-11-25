<?php

namespace App\Http\Controllers\Threat;

use App\Http\Controllers\Controller;
use App\Models\Threat;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ThreatDataTableController extends Controller
{
    public function index(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 10);
        if ($length <= 0 || $length > 500) {
            $length = 10;
        }

        $baseQuery = Threat::query();
        $recordsTotal = (clone $baseQuery)->count();

        $dateFrom = trim((string) $request->input('date_from', ''));
        $dateTo = trim((string) $request->input('date_to', ''));
        $typeFilter = strtoupper(trim((string) $request->input('type', '')));
        $tagFilter = ltrim(trim((string) $request->input('tag', '')), '#');
        $searchValue = trim((string) $request->input('search.value', ''));

        if ($dateFrom !== '') {
            try {
                $fromDate = Carbon::parse($dateFrom)->startOfDay()->format('Y-m-d H:i:s');
                $baseQuery->where('date', '>=', $fromDate);
            } catch (\Throwable) {
                // Ignore invalid date_from input
            }
        }

        if ($dateTo !== '') {
            try {
                $toDate = Carbon::parse($dateTo)->endOfDay()->format('Y-m-d H:i:s');
                $baseQuery->where('date', '<=', $toDate);
            } catch (\Throwable) {
                // Ignore invalid date_to input
            }
        }

        if ($typeFilter !== '') {
            $baseQuery->whereRaw('UPPER(type) = ?', [$typeFilter]);
        }

        if ($tagFilter !== '') {
            $baseQuery->where('tags', 'LIKE', '%' . $tagFilter . '%');
        }

        if ($searchValue !== '') {
            $like = '%' . $searchValue . '%';
            $baseQuery->where(function ($q) use ($like) {
                $q->where('threat_id', 'LIKE', $like)
                    ->orWhere('user', 'LIKE', $like)
                    ->orWhere('type', 'LIKE', $like)
                    ->orWhere('value', 'LIKE', $like)
                    ->orWhere('tags', 'LIKE', $like);
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();

        $columnsMap = [
            0 => 'id',
            1 => 'date',
            2 => 'user',
            3 => 'type',
            4 => 'value',
        ];

        $orderColIdx = (int) $request->input('order.0.column', 1);
        $orderDir = strtolower((string) $request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $orderColumn = $columnsMap[$orderColIdx] ?? 'date';

        $rows = $baseQuery
            ->orderBy($orderColumn, $orderDir)
            ->orderByDesc('id')
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (Threat $threat) {
            $displayId = $threat->threat_id ?: ('TH-' . str_pad((string) $threat->id, 4, '0', STR_PAD_LEFT));

            $formattedDate = '-';
            if (! empty($threat->date)) {
                try {
                    $formattedDate = Carbon::parse($threat->date)
                        ->timezone('Asia/Jakarta')
                        ->format('d M Y, H:i');
                } catch (\Throwable) {
                    $formattedDate = (string) $threat->date;
                }
            }

            $rawTags = $threat->tags;
            if (is_string($rawTags)) {
                $decoded = json_decode($rawTags, true);
                $tagList = is_array($decoded) ? $decoded : explode(',', $rawTags);
            } elseif (is_array($rawTags)) {
                $tagList = $rawTags;
            } else {
                $tagList = [];
            }

            $cleanTags = [];
            foreach ($tagList as $t) {
                $item = trim(str_replace('"', '', (string) $t));
                if ($item !== '') {
                    $cleanTags[] = $item;
                }
            }

            return [
                'id' => $threat->id,
                'threat_id' => $displayId,
                'date' => $formattedDate,
                'user' => (string) ($threat->user ?? '-'),
                'type' => strtoupper((string) ($threat->type ?? 'UNKNOWN')),
                'value' => (string) ($threat->value ?? ''),
                'tags' => $cleanTags,
                'show_url' => route('threatmetrics.show', ['threatmetric' => $threat->id]),
                'edit_url' => route('threatmetrics.edit', ['threatmetric' => $threat->id]),
                'destroy_url' => route('threatmetrics.destroy', ['threatmetric' => $threat->id]),
            ];
        })->values()->all();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
}