<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Skill;
use App\Models\SkillLevel;
use App\Models\TrainingRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SkillLevel>
 */
class SkillLevelFactory extends Factory
{
    protected $model = SkillLevel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'training_run_id' => TrainingRun::factory(),
            'skill_id' => Skill::factory(),
            'current_level' => fake()->numberBetween(1, 6),
        ];
    }
}
