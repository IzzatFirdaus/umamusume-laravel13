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
        return [
            'training_run_id' => TrainingRun::factory(),
            'turn' => fake()->unique()->numberBetween(1, 9999),
            'speed' => fake()->numberBetween(0, 1200),
            'stamina' => fake()->numberBetween(0, 1200),
            'power' => fake()->numberBetween(0, 1200),
            'guts' => fake()->numberBetween(0, 1200),
            'wit' => fake()->numberBetween(0, 1200),
            'sp' => fake()->numberBetween(0, 300),
            'condition' => null,
        ];
    }
}
