<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RunMode;
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
            'character_card_id' => null,
            'scenario' => null,
            'status' => RunStatus::Active,
            'mode' => RunMode::NewCareer,
            'inheritance_parent_a_id' => null,
            'inheritance_parent_b_id' => null,
            'legacy_selection' => null,
            'build_target' => null,
            'notes' => null,
            'current_objective_index' => null,
            'shop_resets_in' => null,
            'imported_at' => null,
            'import_source' => null,
            'career_position' => null,
            'career_position_source' => null,
            'trainee_rarity' => null,
            'potential_level' => null,
            'growth_rate' => null,
            'cap_speed' => null,
            'cap_stamina' => null,
            'cap_power' => null,
            'cap_guts' => null,
            'team_name' => null,
            'team_motto' => null,
            'team_league_placement' => null,
            'team_preseason_wins' => null,
            'unity_trainings_count' => null,
            'spirit_bursts_count' => null,
            'extreme_bursts_count' => null,
            'team_stat_grades' => null,
        ];
    }
}
