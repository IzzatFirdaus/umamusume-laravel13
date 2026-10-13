<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TraineeGoalType;
use App\Models\TraineeGoal;
use App\Models\Umamusume;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TraineeGoal>
 */
class TraineeGoalFactory extends Factory
{
    protected $model = TraineeGoal::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'umamusume_id' => Umamusume::factory(),
            'race_catalog_slot_id' => null,
            'goal_type' => TraineeGoalType::Placement,
            'required_placement' => 3,
            'fan_threshold' => null,
            'career_year' => 3,
            'turn' => 54,
            'source_order' => 5,
            'source_race_choice' => 0,
            'source_cond_id' => 81,
            'source_cond_type' => 1,
            'source_cond_value' => 3,
            'source_cond_value_2' => 0,
            'source_target_type' => 1,
            'source_race_type' => 0,
            'source_race_name' => null,
            'global_unreleased' => false,
            'source_url' => 'https://gametora.com/data/umamusume/ura-objectives.ead4bbfe.json',
            'snapshot_path' => 'snapshots/ura-objectives.ead4bbfe.json',
            'fetched_at' => now(),
            'source_timezone' => 'Asia/Tokyo',
            'is_manual' => false,
        ];
    }

    /**
     * A fan-count objective: a threshold and a deadline, and no race to attach to.
     *
     * @return $this
     */
    public function fan(int $threshold = 5000, int $turn = 37): static
    {
        return $this->state(fn (): array => [
            'goal_type' => TraineeGoalType::Fan,
            'race_catalog_slot_id' => null,
            'required_placement' => null,
            'fan_threshold' => $threshold,
            'career_year' => intdiv($turn - 1, 24) + 1,
            'turn' => $turn,
            'source_cond_type' => 3,
            'source_cond_value' => $threshold,
        ]);
    }

    /**
     * The debut form of an objective: run the race, no placing asked.
     *
     * @return $this
     */
    public function participate(): static
    {
        return $this->state(fn (): array => [
            'goal_type' => TraineeGoalType::Participate,
            'required_placement' => 0,
            'fan_threshold' => null,
            'source_cond_type' => 1,
            'source_cond_value' => 0,
        ]);
    }

    /**
     * A row whose kind the export does not explain (`cond_type` 2 or 12).
     *
     * @return $this
     */
    public function undecoded(): static
    {
        return $this->state(fn (): array => [
            'goal_type' => TraineeGoalType::Undecoded,
            'required_placement' => 3,
            'fan_threshold' => null,
            'source_cond_type' => 2,
            'source_cond_value' => 3,
            'source_cond_value_2' => 3,
        ]);
    }

    /**
     * A goal on a race the source itself marks unreleased on Global.
     *
     * @return $this
     */
    public function notOnGlobal(): static
    {
        return $this->state(fn (): array => [
            'global_unreleased' => true,
            'race_catalog_slot_id' => null,
            'source_race_name' => 'Queen Elizabeth Cup',
        ]);
    }
}
