<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\IndependentTrainingPolicy;
use App\Models\IndependentTrainingRun;
use App\Models\TrainingRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IndependentTrainingRun>
 */
class IndependentTrainingRunFactory extends Factory
{
    protected $model = IndependentTrainingRun::class;

    /**
     * A balanced idle session whose 50-minute window opened now and has not ended.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'training_run_id' => TrainingRun::factory(),
            'policy' => IndependentTrainingPolicy::Balanced,
            'priority_skills' => null,
            'rotation' => null,
            'started_at' => now(),
            'completes_at' => now()->addMinutes(IndependentTrainingRun::DURATION_MINUTES),
            'completed_at' => null,
            'abandoned_at' => null,
        ];
    }

    /**
     * A session whose window has passed but which has not been ended by the scheduler yet.
     */
    public function expired(): static
    {
        return $this->state(fn (): array => [
            'started_at' => now()->subMinutes(IndependentTrainingRun::DURATION_MINUTES + 5),
            'completes_at' => now()->subMinutes(5),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'completed_at' => now(),
        ]);
    }

    public function abandoned(): static
    {
        return $this->state(fn (): array => [
            'abandoned_at' => now(),
        ]);
    }
}
