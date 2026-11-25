<?php

namespace App\Services;

use App\Models\ApiSyncState;
use App\Models\Ioc;
use App\Models\Threat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TweetFeedSyncService
{
    protected string $endpoint = 'https://tweetfeed.live/api/';

    public function __construct(
        protected TweetFeedService $tweetFeedService
    ) {}

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
                        'tags' => ! empty($data['tags']) ? $data['tags'] : $ioc->tags,
                        'source_tweet' => $data['source_tweet'] ?? $ioc->source_tweet,
                        'source_date' => $data['source_date'],
                    ]);
                    $updated++;
                } else {
                    Ioc::create($data);
                    $inserted++;
                }

                $threatType = strtoupper($data['type']);
                $threatUser = (string) ($data['source_user'] ?? 'TweetFeedAPI');
                $existingThreat = Threat::where('date', $data['source_date'])
                    ->where('user', $threatUser)
                    ->where('type', $threatType)
                    ->where('value', $data['value'])
                    ->first();

                if (! $existingThreat) {
                    Threat::create([
                        'date' => $data['source_date'],
                        'user' => $threatUser,
                        'type' => $threatType,
                        'value' => $data['value'],
                        'tags' => $data['tags'],
                        'tweet' => $data['source_tweet'],
                    ]);
                }
            }

            $now = now();

            ApiSyncState::updateOrCreate(
                ['id' => 1],
                [
                    'endpoint' => $this->endpoint,
                    'status' => 'online',
                    'pipeline_stale' => empty($feeds),
                    'last_window_end' => $now,
                    'last_success_at' => $now,
                    'records_last_sync' => $inserted + $updated,
                    'last_error' => null,
                    'status_payload' => [
                        'range' => $range,
                        'total_fetched' => count($feeds),
                        'inserted' => $inserted,
                        'updated' => $updated,
                    ],
                ]
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Sinkronisasi TweetFeed gagal: '.$e->getMessage());

            ApiSyncState::updateOrCreate(
                ['id' => 1],
                [
                    'endpoint' => $this->endpoint,
                    'status' => 'offline',
                    'pipeline_stale' => true,
                    'records_last_sync' => 0,
                    'last_error' => $e->getMessage(),
                ]
            );

            throw $e;
        }

        return [
            'total_fetched' => count($feeds),
            'inserted' => $inserted,
            'updated' => $updated,
        ];
    }
}
