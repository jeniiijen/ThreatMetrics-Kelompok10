<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Services\TweetFeedSyncService;
use Illuminate\Http\Request;

class TweetFeedSyncController extends Controller
{
    public function __invoke(Request $request, TweetFeedSyncService $syncService)
    {
        $range = (string) $request->input('range', 'month');

        try {
            $result = $syncService->sync($range);
            $inserted = $result['inserted'] ?? 0;
            $updated = $result['updated'] ?? 0;
            $message = "Sinkronisasi API selesai: {$inserted} data baru, {$updated} diperbarui.";

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => $result,
                ]);
            }

            return redirect()->route('dashboard')->with('success', $message);
        } catch (\Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal melakukan sinkronisasi API: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->route('dashboard')->with('error', 'Gagal melakukan sinkronisasi API: '.$e->getMessage());
        }
    }
}