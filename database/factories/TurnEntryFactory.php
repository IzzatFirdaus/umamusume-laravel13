<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TurnEntry>
 */
class TurnEntryFactory extends Factory
{
    protected $model = TurnEntry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hardCap = (int) config('scenarios.hard_cap', 2000);

        return [
            'training_run_id' => TrainingRun::factory(),
            'turn' => fake()->unique()->numberBetween(1, 9999),
            'speed' => fake()->numberBetween(0, $hardCap),
            'stamina' => fake()->numberBetween(0, $hardCap),
            'power' => fake()->numberBetween(0, $hardCap),
            'guts' => fake()->numberBetween(0, $hardCap),
            'wit' => fake()->numberBetween(0, $hardCap),
            'sp' => fake()->numberBetween(0, 300),
            'condition' => null,
            'energy' => null,
            'energy_state' => null,
            'energy_band' => null,
            'mood' => null,
            'fans' => null,
            'facility_speed' => null,
            'facility_stamina' => null,
            'facility_power' => null,
            'facility_guts' => null,
            'facility_wit' => null,
            'failure_rate' => null,
            'preview_gains' => null,
            'choice' => null,
            'decision_snapshot' => null,
            'corrected_from' => null,
            'superseded_by' => null,
        ];
    }
}
