<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PreseasonRound;
use App\Models\TrainingRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PreseasonRound>
 */
class PreseasonRoundFactory extends Factory
{
    protected $model = PreseasonRound::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'training_run_id' => TrainingRun::factory(),
            'round' => 1,
            // Null by default: an unrecorded round is the honest default, not a loss.
            'outcome' => null,
        ];
    }

    public function win(): static
    {
        return $this->state(fn (array $attributes): array => [
            'outcome' => 'WIN',
        ]);
    }

    public function loss(): static
    {
        return $this->state(fn (array $attributes): array => [
            'outcome' => 'LOSS',
        ]);
    }

    public function round(int $round): static
    {
        return $this->state(fn (array $attributes): array => [
            'round' => $round,
        ]);
    }
}
