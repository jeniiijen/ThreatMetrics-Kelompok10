<?php

namespace App\Http\Controllers;

use App\Models\Threat;
use App\Models\ApiSyncState;
use App\Models\Ioc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Validasi filter tanggal
        $validated = $request->validate([
            'start_date' => 'nullable|date|required_with:end_date',
            'end_date' => 'nullable|date|required_with:start_date|after_or_equal:start_date',
        ]);

        // 2. Ambil tanggal paling awal dan paling akhir dari database
        $firstThreatDate = Threat::min('date');
        $lastThreatDate = Threat::max('date');

        // Default: tampilkan seluruh periode data yang tersedia
        $defaultStart = $firstThreatDate
            ? Carbon::parse($firstThreatDate)->toDateString()
            : now('Asia/Jakarta')->subDays(6)->toDateString();

        $defaultEnd = $lastThreatDate
            ? Carbon::parse($lastThreatDate)->toDateString()
            : now('Asia/Jakarta')->toDateString();

        $startDate = $validated['start_date'] ?? $defaultStart;
        $endDate = $validated['end_date'] ?? $defaultEnd;

        // 3. Query utama berdasarkan rentang tanggal
        $query = Threat::query()
            ->whereBetween('date', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59',
            ]);

        // 4. Kartu metrik
        $totalThreats = (clone $query)->count();

        
        // Distribusi IOC menggunakan data hasil integrasi API
        $iocTableExists = Schema::hasTable('iocs');

        if ($iocTableExists) {

            $iocQuery = Ioc::query()
                ->whereBetween('source_date', [
                    $startDate . ' 00:00:00',
                    $endDate . ' 23:59:59'
                ]);

            // Total indikator unik dari API
            $totalIndicators = (clone $iocQuery)
                ->whereNotNull('value')
                ->distinct()
                ->count('value');

            // Distribusi tipe IOC
            $typeCounts = (clone $iocQuery)
                ->selectRaw('UPPER(type) as type, COUNT(*) as total')
                ->groupByRaw('UPPER(type)')
                ->orderBy('type')
                ->get();

        } else {

            // Fallback jika tabel IOC belum tersedia
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


        $totalUsers = (clone $query)
            ->whereNotNull('user')
            ->where('user', '!=', '')
            ->distinct()
            ->count('user');



        // =====================================
        // THREAT ACTIVITY CHART
        // Pengelompokan data secara dinamis
        // =====================================

        $rangeStart = Carbon::parse($startDate)->startOfDay();
        $rangeEnd = Carbon::parse($endDate)->startOfDay();

        $rangeDays = $rangeStart->diffInDays($rangeEnd) + 1;

        $trendLabels = [];
        $trendValues = [];

        // Jika rentang lebih dari 31 hari,
        // tampilkan grafik per bulan.
        if ($rangeDays > 31) {

            $trendGrouping = 'month';

            $monthlyCounts = (clone $query)
                ->selectRaw(
                    "DATE_FORMAT(date, '%Y-%m-01') as bucket, COUNT(*) as total"
                )
                ->groupByRaw(
                    "DATE_FORMAT(date, '%Y-%m-01')"
                )
                ->orderByRaw(
                    "DATE_FORMAT(date, '%Y-%m-01')"
                )
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

        // Jika rentang 31 hari atau kurang,
        // tampilkan grafik per hari.
        } else {

            $trendGrouping = 'day';

            $dailyCounts = (clone $query)
                ->selectRaw('DATE(date) as bucket, COUNT(*) as total')
                ->groupByRaw('DATE(date)')
                ->orderByRaw('DATE(date)')
                ->pluck('total', 'bucket');

            $period = CarbonPeriod::create(
                $rangeStart,
                '1 day',
                $rangeEnd
            );

            foreach ($period as $day) {

                $key = $day->format('Y-m-d');

                $trendLabels[] = $day->format('d M');

                $trendValues[] = (int) ($dailyCounts[$key] ?? 0);
            }
        }




        // 7. Delapan threat terbaru dalam periode terpilih
        $recentThreats = (clone $query)
            ->orderByDesc('date')
            ->limit(8)
            ->get();

        // 8. Periksa status API TweetFeed
        $apiTableExists = Schema::hasTable('api_sync_states');
        $apiState = null;

        if ($apiTableExists) {
            $apiState = ApiSyncState::query()
                ->latest('updated_at')
                ->first();
        }

        $apiStatus = strtolower(
            (string) ($apiState?->status ?? 'unknown')
        );

        if (!in_array($apiStatus, ['online', 'offline'], true)) {
            $apiStatus = 'unknown';
        }

        // Grafik status koneksi API
        $apiStatusValues = [
            $apiStatus === 'online' ? 1 : 0,
            $apiStatus === 'offline' ? 1 : 0,
        ];

        // 9. Timestamp sinkronisasi terakhir yang berhasil
        $lastApiUpdate = $apiState?->last_success_at
            ? $apiState->last_success_at->copy()->timezone('Asia/Jakarta')
            : null;

        // 10. Waktu lokal Indonesia Barat
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
