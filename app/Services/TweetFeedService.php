<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TweetFeedService
{
    protected string $baseUrl = 'https://api.tweetfeed.live/v1';

    public function fetchFeeds(string $range = 'today'): array
    {
        try {
            $request = Http::timeout(25)
                ->retry(2, 1000)
                ->acceptJson();

            if (app()->environment('local')) {
                $request = $request->withoutVerifying();
            }

            $response = $request->get("{$this->baseUrl}/{$range}");

            if ($response->failed()) {
                Log::error('TweetFeed API gagal merespons', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                throw new \RuntimeException("TweetFeed API HTTP {$response->status()}");
            }

            $rawItems = $response->json();

            if (!is_array($rawItems)) {
                return [];
            }

            return array_values(array_filter(array_map(
                fn ($item) => $this->mapThreatData($item),
                $rawItems
            )));
        } catch (\Throwable $e) {
            Log::error('Exception saat mengambil data TweetFeed API: ' . $e->getMessage());
            throw $e;
        }
    }

    public function mapThreatData(array $item): ?array
    {
        $value = trim((string) ($item['value'] ?? ''));
        $type = strtolower(trim((string) ($item['type'] ?? '')));

        $allowedTypes = ['url', 'domain', 'ip', 'sha256', 'md5'];

        if ($value === '' || !in_array($type, $allowedTypes, true)) {
            return null;
        }

        $tags = $item['tags'] ?? [];
        if (is_string($tags)) {
            $tags = array_values(array_filter(array_map('trim', explode(',', $tags))));
        } elseif (!is_array($tags)) {
            $tags = [];
        }

        $sourceDate = now();
        if (!empty($item['date'])) {
            try {
                $sourceDate = Carbon::parse($item['date']);
            } catch (\Throwable $e) {
                $sourceDate = now();
            }
        }

        $normalizedValue = in_array($type, ['domain', 'ip', 'sha256', 'md5'], true)
            ? strtolower($value)
            : $value;

        $sourceKey = hash('sha256', "{$type}|{$normalizedValue}");

        return [
            'source_key'   => $sourceKey,
            'type'         => $type,
            'value'        => $value,
            'source_user'  => $item['user'] ?? null,
            'source_tweet' => $item['tweet'] ?? null,
            'tags'         => $tags,
            'category'     => $this->inferCategory($tags),
            'severity'     => $this->inferSeverity($type, $tags),
            'status'       => 'active',
            'source_date'  => $sourceDate->format('Y-m-d H:i:s'),
            'source'       => 'tweetfeed',
        ];
    }

    protected function inferCategory(array $tags): string
    {
        if (empty($tags)) {
            return 'Uncategorized';
        }

        return ucfirst(strtolower((string) $tags[0]));
    }

    protected function inferSeverity(string $type, array $tags): string
    {
        $joinedTags = strtolower(implode(' ', $tags));

        if (str_contains($joinedTags, 'ransomware') || str_contains($joinedTags, 'c2') || str_contains($joinedTags, 'cobalt')) {
            return 'critical';
        }

        if (in_array($type, ['sha256', 'md5', 'url'], true) || str_contains($joinedTags, 'phishing') || str_contains($joinedTags, 'malware')) {
            return 'warning';
        }

        return 'info';
    }
}