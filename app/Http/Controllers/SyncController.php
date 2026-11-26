<?php

namespace App\Http\Controllers;

use App\Services\TweetFeedSyncService;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function __invoke(Request $request, TweetFeedSyncService $syncService)
    {
        $range = $request->input('range', 'today');

        try {
            $result = $syncService->sync($range);

            $message = "Sinkronisasi selesai: {$result['inserted']} data baru, {$result['updated']} diperbarui.";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data'    => $result,
                ]);
            }

            return back()->with('success', $message);
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal melakukan sinkronisasi dengan TweetFeed API.',
                ], 500);
            }

            return back()->with('error', 'Gagal melakukan sinkronisasi dengan TweetFeed API.');
        }
    }
}