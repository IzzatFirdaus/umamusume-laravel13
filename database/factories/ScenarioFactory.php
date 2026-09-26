<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Scenario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Scenario>
 */
class ScenarioFactory extends Factory
{
    protected $model = Scenario::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->slug(),
            'name' => 'URA Finale',
            'name_ja' => '新設！URAファイナルズ',
            'order' => 1,
            'jp_start_date' => '2021-02-24',
            'global_start_date' => '2025-06-26',
            'cap_speed' => 1400,
            'cap_stamina' => 1400,
            'cap_power' => 1400,
            'cap_guts' => 1400,
            'cap_wit' => 1400,
            'hard_cap' => 2000,
            'caps_reworked_at' => '2026-07-01',
            'source_url' => 'https://gametora.com/data/umamusume/scenarios.test.json',
            'fetched_at' => now(),
            'is_manual' => false,
        ];
    }
}
