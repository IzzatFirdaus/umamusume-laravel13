<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RosterDistance;
use App\Models\RosterMember;
use App\Models\TrainingRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RosterMember>
 */
class RosterMemberFactory extends Factory
{
    protected $model = RosterMember::class;

    /**
     * A named member with no readings. Every figure is nullable on the model because a row the
     * Trainer added by name has no stat line yet (D-220), so the default is deliberately bare rather
     * than a plausible-looking fabricated one.
     *
     * `position` defaults to 0 and the tests that need an order say so; a random default would make
     * any test that builds a roster read an arbitrary sequence.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'training_run_id' => TrainingRun::factory(),
            'umamusume_id' => null,
            'name' => 'Guest '.$this->faker->unique()->numberBetween(1, 9999),
            'grade' => null,
            'speed' => null,
            'stamina' => null,
            'power' => null,
            'guts' => null,
            'wit' => null,
            'distance_assignment' => null,
            'is_ace' => false,
            'is_trainee' => false,
            'position' => 0,
        ];
    }

    /**
     * The row that is the trainee herself, linked to the run's own trainee.
     */
    public function trainee(TrainingRun $run): static
    {
        return $this->state(fn (): array => [
            'training_run_id' => $run->id,
            'umamusume_id' => $run->umamusume_id,
            'name' => (string) $run->umamusume?->name,
            'is_trainee' => true,
        ]);
    }

    public function inColumn(RosterDistance $distance): static
    {
        return $this->state(fn (): array => ['distance_assignment' => $distance]);
    }

    public function ace(): static
    {
        return $this->state(fn (): array => ['is_ace' => true]);
    }

    public function atPosition(int $position): static
    {
        return $this->state(fn (): array => ['position' => $position]);
    }

    /**
     * A guest with a full recorded stat line, linked to a catalogue row when one is named.
     */
    public function recorded(
        int $speed,
        int $stamina,
        int $power,
        int $guts,
        int $wit,
        ?int $umamusumeId = null,
    ): static {
        return $this->state(fn (): array => [
            'speed' => $speed,
            'stamina' => $stamina,
            'power' => $power,
            'guts' => $guts,
            'wit' => $wit,
            'umamusume_id' => $umamusumeId,
        ]);
    }
}
