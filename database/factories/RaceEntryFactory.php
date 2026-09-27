<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RaceEntryStatus;
use App\Models\RaceEntry;
use App\Models\TrainingRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RaceEntry>
 */
class RaceEntryFactory extends Factory
{
    protected $model = RaceEntry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'training_run_id' => TrainingRun::factory(),
            // Null by default. `scenario_races` is frozen by ADR-0003 R1.3, so a
            // race entry does not belong to one unless a test names it, and a
            // Trackblazer race has no calendar row to name.
            'scenario_race_id' => null,
            'status' => RaceEntryStatus::Entered,
            'placement' => null,
            'fans_gain' => null,
        ];
    }

    /**
     * A declined race: a stored row, not an absent one (ADR-0003).
     *
     * @return $this
     */
    public function skipped(): static
    {
        return $this->state(fn (): array => [
            'status' => RaceEntryStatus::Skipped,
            'placement' => null,
            'fans_gain' => null,
        ]);
    }
}
