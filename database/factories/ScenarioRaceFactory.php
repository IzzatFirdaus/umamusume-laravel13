<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ScenarioRace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScenarioRace>
 */
class ScenarioRaceFactory extends Factory
{
    protected $model = ScenarioRace::class;

    /**
     * Provenance is always populated: a calendar row without a source is a rule
     * violation on arrival (ADR-0003, AGENTS.md Data Engineer rules).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'scenario_key' => 'ura-finale',
            'slot_label' => 'Classic Year Late May',
            'race_name' => 'Japanese Derby',
            'tier' => 'G1',
            'fans_needed' => 12000,
            'mandatory' => true,
            'maiden_gated' => false,
            'source_url' => 'https://gametora.com/data/umamusume/ura-races.test.json',
            'snapshot_path' => null,
            'fetched_at' => now(),
            'source_timezone' => 'Asia/Tokyo',
        ];
    }

    /**
     * The Debut/Maiden lock, a boolean predicate over race history and a
     * different kind of gate from the numeric fan threshold (ADR-0003 §4).
     *
     * @return $this
     */
    public function maidenGated(): static
    {
        return $this->state(fn (): array => [
            'maiden_gated' => true,
            'fans_needed' => null,
        ]);
    }
}
