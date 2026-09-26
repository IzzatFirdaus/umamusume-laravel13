<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingRun>
 */
class TrainingRunFactory extends Factory
{
    protected $model = TrainingRun::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'umamusume_id' => Umamusume::factory(),
            'scenario' => null,
            'status' => RunStatus::Active,
            'notes' => null,
        ];
    }
}
