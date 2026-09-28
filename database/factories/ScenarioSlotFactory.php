<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ScenarioSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScenarioSlot>
 */
class ScenarioSlotFactory extends Factory
{
    protected $model = ScenarioSlot::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'scenario_key' => 'ura_finale',
            'kind' => 'goal_race',
            'source_key' => null,
            'slot_label' => 'G1',
            'title' => 'Test Race',
            'description' => null,
            'month' => 4,
            'half' => 'Late',
            'tier' => 'G1',
            'fans_needed' => 12000,
            'is_mandatory' => true,
            'is_maiden_gated' => false,
            'sort_order' => 0,
            'source_url' => null,
            'snapshot_path' => null,
            'fetched_at' => null,
            'source_timezone' => null,
            'is_manual' => false,
        ];
    }

    public function goalRace(): static
    {
        return $this->state(fn (array $attributes) => [
            'kind' => 'goal_race',
        ]);
    }

    public function teamRace(): static
    {
        return $this->state(fn (array $attributes) => [
            'kind' => 'team_race',
        ]);
    }

    public function gradeDeadline(): static
    {
        return $this->state(fn (array $attributes) => [
            'kind' => 'grade_deadline',
        ]);
    }

    public function scriptedEvent(): static
    {
        return $this->state(fn (array $attributes) => [
            'kind' => 'scripted_event',
        ]);
    }

    public function forScenario(string $scenarioKey): static
    {
        return $this->state(fn (array $attributes) => [
            'scenario_key' => $scenarioKey,
        ]);
    }

    public function mandatory(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_mandatory' => true,
        ]);
    }

    public function maidenGated(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_maiden_gated' => true,
        ]);
    }

    public function inMonth(int $month, string $half): static
    {
        return $this->state(fn (array $attributes) => [
            'month' => $month,
            'half' => $half,
        ]);
    }
}
