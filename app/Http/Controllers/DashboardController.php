<?php

namespace App\Http\Controllers;

use App\Models\ApiSyncState;
use App\Models\Ioc;
use App\Models\Threat;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date|required_with:end_date',
            'end_date' => 'nullable|date|required_with:start_date|after_or_equal:start_date',
        ]);

        $firstThreatDate = Threat::min('date');
        $lastThreatDate = Threat::max('date');

        $defaultStart = $firstThreatDate
            ? Carbon::parse($firstThreatDate)->toDateString()
            : now('Asia/Jakarta')->subDays(6)->toDateString();

        $defaultEnd = $lastThreatDate
            ? Carbon::parse($lastThreatDate)->toDateString()
            : now('Asia/Jakarta')->toDateString();

        $startDate = $validated['start_date'] ?? $defaultStart;
        $endDate = $validated['end_date'] ?? $defaultEnd;

        $query = Threat::query()
            ->whereBetween('date', [
                $startDate.' 00:00:00',
                $endDate.' 23:59:59',
            ]);

        $totalThreats = (clone $query)->count();

        // IOC summary
        $iocTableExists = Schema::hasTable('iocs');

        if ($iocTableExists) {
            $iocQuery = Ioc::query()
                ->whereBetween('source_date', [
                    $startDate.' 00:00:00',
                    $endDate.' 23:59:59',
                ]);

            $totalIndicators = (clone $iocQuery)
                ->whereNotNull('value')
                ->distinct()
                ->count('value');

            $typeCounts = (clone $iocQuery)
                ->selectRaw('UPPER(type) as type, COUNT(*) as total')
                ->groupByRaw('UPPER(type)')
                ->orderBy('type')
                ->get();

            if ($typeCounts->isEmpty()) {
                $typeCounts = (clone $query)
                    ->selectRaw('UPPER(type) as type, COUNT(*) as total')
                    ->groupByRaw('UPPER(type)')
                    ->orderBy('type')
                    ->get();
            }
        } else {
            $totalIndicators = (clone $query)
                ->whereNotNull('value')
                ->distinct()
                ->count('value');

            $typeCounts = (clone $query)
                ->selectRaw('UPPER(type) as type, COUNT(*) as total')
                ->groupByRaw('UPPER(type)')
                ->orderBy('type')
                ->get();
        }

        if ($totalIndicators === 0) {
            $totalIndicators = (clone $query)
                ->whereNotNull('value')
                ->distinct()
                ->count('value');
        }

        $totalUsers = (clone $query)
            ->whereNotNull('user')
            ->where('user', '!=', '')
            ->distinct()
            ->count('user');

        // Chart trend
        $rangeStart = Carbon::parse($startDate)->startOfDay();
        $rangeEnd = Carbon::parse($endDate)->startOfDay();
        $rangeDays = $rangeStart->diffInDays($rangeEnd) + 1;

        $trendLabels = [];
        $trendValues = [];

        if ($rangeDays > 31) {
            $trendGrouping = 'month';

            $monthBucket = DB::connection()->getDriverName() === 'sqlite'
                ? "strftime('%Y-%m-01', date)"
                : "DATE_FORMAT(date, '%Y-%m-01')";

            $monthlyCounts = (clone $query)
                ->selectRaw("{$monthBucket} as bucket, COUNT(*) as total")
                ->groupByRaw($monthBucket)
                ->orderByRaw($monthBucket)
                ->pluck('total', 'bucket');

            $period = CarbonPeriod::create(
                $rangeStart->copy()->startOfMonth(),
                '1 month',
                $rangeEnd->copy()->startOfMonth()
            );

            foreach ($period as $month) {
                $key = $month->format('Y-m-01');
                $trendLabels[] = $month->format('M Y');
                $trendValues[] = (int) ($monthlyCounts[$key] ?? 0);
            }
        } else {
            $trendGrouping = 'day';

            $dailyCounts = (clone $query)
                ->selectRaw('DATE(date) as bucket, COUNT(*) as total')
                ->groupByRaw('DATE(date)')
                ->orderByRaw('DATE(date)')
                ->pluck('total', 'bucket');

            $period = CarbonPeriod::create($rangeStart, '1 day', $rangeEnd);

            foreach ($period as $day) {
                $key = $day->format('Y-m-d');
                $trendLabels[] = $day->format('d M');
                $trendValues[] = (int) ($dailyCounts[$key] ?? 0);
            }
        }

        $recentThreats = (clone $query)
            ->orderByDesc('date')
            ->limit(8)
            ->get();

        // API status
        $apiTableExists = Schema::hasTable('api_sync_states');
        $apiState = null;

        if ($apiTableExists) {
            $apiState = ApiSyncState::query()
                ->latest('updated_at')
                ->first();
        }

        $apiStatus = strtolower((string) ($apiState?->status ?? 'unknown'));

        if (! in_array($apiStatus, ['online', 'offline'], true)) {
            $apiStatus = 'unknown';
        }

        $apiStatusValues = [
            $apiStatus === 'online' ? 1 : 0,
            $apiStatus === 'offline' ? 1 : 0,
        ];

        $lastApiUpdate = $apiState?->last_success_at
            ? $apiState->last_success_at->copy()->timezone('Asia/Jakarta')
            : null;

        $nowWib = now('Asia/Jakarta');

        return view('dashboard.index', compact(
            'startDate',
            'endDate',
            'totalThreats',
            'totalIndicators',
            'totalUsers',
            'typeCounts',
            'trendLabels',
            'trendValues',
            'trendGrouping',
            'recentThreats',
            'apiStatus',
            'apiTableExists',
            'apiStatusValues',
            'lastApiUpdate',
            'nowWib'
        ));
    }
}
