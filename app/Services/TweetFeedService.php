<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TweetFeedService
{
    protected string $primaryApiUrl = 'https://tweetfeed.live/api/';

    protected string $fallbackApiBase = 'https://api.tweetfeed.live/v1';

    public function fetchFeeds(string $range = 'today'): array
    {
        $cleanRange = in_array(strtolower($range), ['today', 'week', 'month'], true)
            ? strtolower($range)
            : 'today';

        $candidates = [
            rtrim($this->primaryApiUrl, '/').'/'.$cleanRange,
            $this->primaryApiUrl,
            $this->fallbackApiBase.'/'.$cleanRange,
        ];

        $lastError = null;

        foreach ($candidates as $url) {
            try {
                $response = Http::timeout(25)
                    ->retry(2, 500)
                    ->withoutVerifying()
                    ->acceptJson()
                    ->get($url);

                if (! $response->successful()) {
                    $lastError = new \RuntimeException("HTTP {$response->status()} dari {$url}");
                    continue;
                }

                $payload = $response->json();
                $rawItems = $this->extractItems($payload);

                if (! is_array($rawItems)) {
                    $lastError = new \RuntimeException("Format respons API tidak valid dari {$url}");
                    continue;
                }

                $mapped = array_values(array_filter(array_map(
                    fn ($item) => is_array($item) ? $this->mapThreatData($item) : null,
                    $rawItems
                )));

                return $mapped;
            } catch (\Throwable $e) {
                $lastError = $e;
                Log::warning("Gagal mengambil data dari {$url}: ".$e->getMessage());
            }
        }

        if ($lastError !== null) {
            throw $lastError;
        }

        return [];
    }

    protected function extractItems(mixed $payload): ?array
    {
        if (! is_array($payload)) {
            return null;
        }

        if (array_is_list($payload)) {
            return $payload;
        }

        foreach (['data', 'items', 'feeds', 'results', 'threats'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                return $payload[$key];
            }
        }

        return null;
    }

    public function mapThreatData(array $item): ?array
    {
        $value = trim((string) ($item['value'] ?? $item['indicator'] ?? ''));
        $rawType = strtolower(trim((string) ($item['type'] ?? '')));

        $type = match ($rawType) {
            'ipv4', 'ipv6', 'ip' => 'ip',
            'domain', 'fqdn' => 'domain',
            'url', 'link' => 'url',
            'sha256', 'sha-256' => 'sha256',
            'md5' => 'md5',
            default => '',
        };

        if ($value === '' || $type === '') {
            return null;
        }

        $tags = $item['tags'] ?? [];
        if (is_string($tags)) {
            $tags = array_values(array_filter(array_map('trim', explode(',', $tags))));
        } elseif (! is_array($tags)) {
            $tags = [];
        }

        $sourceDate = now();
        $rawDate = $item['date'] ?? $item['created_at'] ?? null;
        if (! empty($rawDate)) {
            try {
                $sourceDate = Carbon::parse((string) $rawDate);
            } catch (\Throwable) {
                $sourceDate = now();
            }
        }

        $normalizedValue = in_array($type, ['domain', 'ip', 'sha256', 'md5'], true)
            ? strtolower($value)
            : $value;

        $sourceKey = hash('sha256', "{$type}|{$normalizedValue}");

        return [
            'source_key' => $sourceKey,
            'type' => $type,
            'value' => $value,
            'source_user' => $item['user'] ?? 'TweetFeedAPI',
            'source_tweet' => $item['tweet'] ?? $item['description'] ?? null,
            'tags' => $tags,
            'category' => $this->inferCategory($tags),
            'severity' => $this->inferSeverity($type, $tags),
            'status' => 'active',
            'source_date' => $sourceDate->format('Y-m-d H:i:s'),
            'source' => 'tweetfeed',
        ];
    }

    protected function inferCategory(array $tags): string
    {
        if (empty($tags)) {
            return 'Uncategorized';
        }

        return ucfirst(strtolower(ltrim((string) $tags[0], '#')));
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
