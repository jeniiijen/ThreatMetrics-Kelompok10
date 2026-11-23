<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Threat;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            'PhishStats',
            'MalwareHunter',
            'ThreatIntel',
            'CyberSecFeed',
            'AbuseIPDB',
            'C2Tracker',
        ];

        $samples = [
            [
                'type' => 'domain',
                'value' => 'login-secure-update.com',
                'tags' => ['#phishing'],
            ],
            [
                'type' => 'sha256',
                'value' => hash('sha256', 'malware-sample-01'),
                'tags' => ['#malware', '#trojan'],
            ],
            [
                'type' => 'ip',
                'value' => '45.33.22.11',
                'tags' => ['#botnet'],
            ],
            [
                'type' => 'url',
                'value' => 'https://example.com/suspicious-login',
                'tags' => ['#phishing'],
            ],
            [
                'type' => 'md5',
                'value' => md5('malware-sample-01'),
                'tags' => ['#ransomware'],
            ],
            [
                'type' => 'ip',
                'value' => '103.245.192.1',
                'tags' => ['#command-control'],
            ],
            [
                'type' => 'domain',
                'value' => 'verify-account-info.net',
                'tags' => ['#phishing'],
            ],
            [
                'type' => 'sha256',
                'value' => hash('sha256', 'malware-sample-02'),
                'tags' => ['#keylogger'],
            ],
            [
                'type' => 'url',
                'value' => 'https://example.org/security-alert',
                'tags' => ['#malicious-url'],
            ],
            [
                'type' => 'md5',
                'value' => md5('malware-sample-02'),
                'tags' => ['#adware'],
            ],
        ];

        $startDate = Carbon::create(
            2026, 1, 1, 8, 15, 0
        );

        for ($i = 1; $i <= 50; $i++) {
            $sample = $samples[($i - 1) % count($samples)];

            $date = $startDate->copy()
                ->addDays(($i - 1) * 6)
                ->setTime(
                    rand(7, 22),
                    rand(0, 59),
                    rand(0, 59)
                );

            if ($i === 50) {
                $date = Carbon::create(
                    2026, 11, 11, 21, 45, 10
                );
            }

            Threat::create([
                'date' => $date,
                'user' => $users[array_rand($users)],
                'type' => $sample['type'],
                'value' => $sample['value'],
                'tags' => $sample['tags'],
                'tweet' => null,
            ]);
        }
    }
}
