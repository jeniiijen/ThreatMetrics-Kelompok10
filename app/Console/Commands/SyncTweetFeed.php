<?php

namespace App\Console\Commands;

use App\Services\TweetFeedSyncService;
use Illuminate\Console\Command;

class SyncTweetFeed extends Command
{
    protected $signature = 'tweetfeed:sync {--range=today : Rentang data API (today/week/month)}';

    protected $description = 'Sinkronisasi data threat intelligence dari TweetFeed API ke database tanpa duplikat';

    public function handle(TweetFeedSyncService $syncService): int
    {
        $range = (string) $this->option('range');
        $this->info("Memulai sinkronisasi TweetFeed ({$range})...");

        try {
            $result = $syncService->sync($range);
            $this->info("Selesai! Ditarik: {$result['total_fetched']}, Baru: {$result['inserted']}, Diperbarui: {$result['updated']}.");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Sinkronisasi gagal: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}