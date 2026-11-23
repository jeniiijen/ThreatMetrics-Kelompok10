<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Threat>
 */
class ThreatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'url' => 'http://' . $this->faker->domainName() . '/' . $this->faker->slug() . '.exe',
            'threat_name' => $this->faker->randomElement(['payload_delivery', 'malware_download', 'phishing']),
            'status_numeric' => $this->faker->randomElement([0, 1]), // 1 = Online, 0 = Offline
            'reporter' => $this->faker->userName(),
            'date_added' => $this->faker->dateTimeBetween('-1 months', 'now')->format('Y-m-d'),
        ];
    }
}