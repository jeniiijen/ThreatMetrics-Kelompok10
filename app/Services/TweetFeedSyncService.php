<?php

namespace App\Services;

use App\Models\Ioc;
use App\Models\ApiSyncState;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TweetFeedSyncService
{
    protected string $endpoint = 'https://api.tweetfeed.live/v1';

    public function __construct(
        protected TweetFeedService $tweetFeedService
    ) {}

    public function checkAndAutoSync(int $intervalMinutes = 15): void
    {
        $cacheKey = 'tweetfeed_auto_sync_lock';

        if (Cache::has($cacheKey)) {
            return;
        }

        $state = ApiSyncState::find(1) ?? ApiSyncState::where('endpoint', $this->endpoint)->first();

        if ($state && $state->last_success_at && $state->last_success_at->gt(now()->subMinutes($intervalMinutes))) {
            Cache::put($cacheKey, true, now()->addMinutes($intervalMinutes));
            return;
        }

        try {
            $this->sync('today');
        } catch (\Throwable $e) {
            Log::warning('Auto-sync dashboard gagal: ' . $e->getMessage());
        } finally {
            Cache::put($cacheKey, true, now()->addMinutes($intervalMinutes));
        }
    }

    public function getLastUpdatedText(): string
    {
        $state = ApiSyncState::find(1) ?? ApiSyncState::where('endpoint', $this->endpoint)->first();

        if (!$state || !$state->last_success_at) {
            return 'Belum pernah disinkronkan';
        }

        return $state->last_success_at
            ->timezone('Asia/Jakarta')
            ->format('d M Y, H:i') . ' WIB (' . $state->last_success_at->diffForHumans() . ')';
    }

    public function sync(string $range = 'today'): array
    {
        $feeds = $this->tweetFeedService->fetchFeeds($range);

        $inserted = 0;
        $updated = 0;

        DB::beginTransaction();
        try {
            foreach ($feeds as $data) {
                $ioc = Ioc::where('source_key', $data['source_key'])->first();

                if ($ioc) {
                    $ioc->update([
                        'tags'         => !empty($data['tags']) ? $data['tags'] : $ioc->tags,
                        'source_tweet' => $data['source_tweet'] ?? $ioc->source_tweet,
                        'source_date'  => $data['source_date'],
                    ]);
                    $updated++;
                } else {
                    Ioc::create($data);
                    $inserted++;
                }
            }

            $now = now();

            ApiSyncState::updateOrCreate(
                ['id' => 1],
                [
                    'endpoint'          => $this->endpoint,
                    'status'            => 'online',
                    'pipeline_stale'    => empty($feeds),
                    'last_window_end'   => $now,
                    'last_success_at'   => $now,
                    'records_last_sync' => $inserted + $updated,
                    'last_error'        => null,
                    'status_payload'    => [
                        'range'         => $range,
                        'total_fetched' => count($feeds),
                        'inserted'      => $inserted,
                        'updated'       => $updated,
                    ],
                ]
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Sinkronisasi TweetFeed gagal: ' . $e->getMessage());

            ApiSyncState::updateOrCreate(
                ['id' => 1],
                [
                    'endpoint'          => $this->endpoint,
                    'status'            => 'offline',
                    'pipeline_stale'    => true,
                    'records_last_sync' => 0,
                    'last_error'        => $e->getMessage(),
                ]
            );

            throw $e;
        }

        return [
            'total_fetched' => count($feeds),
            'inserted'      => $inserted,
            'updated'       => $updated,
        ];
    }
}