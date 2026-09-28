<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RaceCatalogSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RaceCatalogSlot>
 */
class RaceCatalogSlotFactory extends Factory
{
    protected $model = RaceCatalogSlot::class;

    /**
     * A Classic-year G1 on a real turn: the shape the grid and picker both read.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'scenario_key' => null,
            'year' => RaceCatalogSlot::YEAR_CLASSIC,
            'month' => 4,
            'half' => 'Early',
            'turn' => 7,
            'slot_label' => 'Early April',
            'title' => 'Oka Sho',
            'tier' => 'G1',
            'grade_code' => 100,
            'distance' => 1600,
            'distance_band' => 'Mile',
            'surface' => 'Turf',
            'track_id' => 10008,
            'race_id' => 100801,
            'fans_needed' => 4500,
            'fans_gain_curve' => 21,
            'is_mandatory' => false,
            'is_special_race' => false,
            'did_not_exist' => null,
            'external_ref' => '100801',
            'sort_order' => 1,
            'source_url' => null,
            'snapshot_path' => null,
            'fetched_at' => null,
            'source_timezone' => null,
            'is_manual' => false,
        ];
    }

    /**
     * The debut: mandatory for every trainee, and the row that must not gain a Goal
     * pennant unless a character's own objectives name it.
     */
    public function debut(): static
    {
        return $this->state(fn (): array => [
            'year' => RaceCatalogSlot::YEAR_JUNIOR,
            'month' => 6,
            'half' => 'Late',
            'turn' => 12,
            'slot_label' => 'Late June',
            'title' => 'Junior Make Debut',
            'tier' => 'Debut',
            'grade_code' => 900,
            'distance' => null,
            'distance_band' => null,
            'surface' => null,
            'track_id' => null,
            'race_id' => null,
            'fans_needed' => null,
            'fans_gain_curve' => null,
            'is_mandatory' => true,
            'is_special_race' => true,
            'external_ref' => 'debut',
            'sort_order' => 2,
        ]);
    }

    /**
     * One of the nine rows whose payout curve [Global] publishes no entry for.
     *
     * The curve id is real and present in the export; it is the 1-50 range of
     * `en/race-fans` that stops short of it, which is why the state sets a value
     * above that ceiling rather than leaving the column empty.
     */
    public function withoutKnownFanGain(): static
    {
        return $this->state(fn (): array => [
            'title' => 'Zen-Nippon Junior Yushun',
            'tier' => 'G1',
            'grade_code' => 100,
            'distance' => 1600,
            'distance_band' => 'Mile',
            'surface' => 'Dirt',
            'track_id' => 10013,
            'fans_needed' => 1000,
            'fans_gain_curve' => 54,
            'did_not_exist' => 'pre_nar',
        ]);
    }

    public function scenarioFinal(string $scenarioKey): static
    {
        return $this->state(fn (): array => [
            'scenario_key' => $scenarioKey,
            'year' => RaceCatalogSlot::YEAR_FINALE,
            'month' => null,
            'half' => null,
            'turn' => null,
            'slot_label' => 'after Senior December',
            'title' => 'URA Finals Final',
            'tier' => 'G1',
            'grade_code' => 100,
            'distance' => null,
            'distance_band' => null,
            'surface' => null,
            'track_id' => null,
            'fans_needed' => null,
            'fans_gain_curve' => null,
            'is_mandatory' => true,
            'is_special_race' => true,
            'sort_order' => 3,
        ]);
    }
}
