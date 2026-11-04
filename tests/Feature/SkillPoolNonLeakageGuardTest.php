<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ReleaseStatus;
use App\Models\CharacterCard;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guard: a run's skill pool is built from export ids the trainee profile owns, and the planner
 * resolves those ids against the Global catalogue only. A Japan-only skill must never leak into
 * the recommendation or the picker, even when its export id is present on the form's card.
 *
 * The resolution matches on the catalogue's canonical key (`export_id`), not on a loose name, so
 * a Japan-only row that happens to share a display name with a Global one cannot slip through.
 */
final class SkillPoolNonLeakageGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_global_form_never_recommends_or_offers_a_japan_only_skill(): void
    {
        $run = $this->plannerRun();

        $global = $this->skill('Global Skill', 100, ReleaseStatus::GlobalReleased);
        $japanOnly = $this->skill('Japan Only Skill', 100, ReleaseStatus::JapanOnly);

        $run->characterCard()->update([
            'skills_unique' => [$global->export_id, $japanOnly->export_id],
        ]);

        // No priorities: the recommendation is made entirely of fill candidates from the pool.
        $run->update(['build_target' => $this->target([])]);

        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'sp' => 500]);

        $props = $this->get(route('runs.skills.planner', $run))
            ->assertOk()
            ->viewData('page')['props'];

        $recommendedNames = array_column($props['recommended']['rows'], 'name');
        $this->assertNotContains($japanOnly->name, $recommendedNames);

        $pickerNames = array_column($props['picker_skills'], 'name');
        $this->assertNotContains($japanOnly->name, $pickerNames);

        foreach ($props['groups'] as $group) {
            $this->assertNotContains($japanOnly->name, array_column($group['rows'], 'name'));
        }
    }

    private function plannerRun(): TrainingRun
    {
        $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
        $card = CharacterCard::factory()->create(['umamusume_id' => $run->umamusume_id]);
        $run->update(['character_card_id' => $card->id]);

        return $run;
    }

    private function skill(string $name, int $sp, ReleaseStatus $status): Skill
    {
        return Skill::factory()->create([
            'name' => $name,
            'sp_cost' => $sp,
            'release_status' => $status,
            'name_is_client' => $status === ReleaseStatus::GlobalReleased,
            'export_id' => fake()->unique()->numberBetween(10000, 99999),
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function target(array $overrides = []): array
    {
        return [
            'purpose' => 'StoryClear',
            'distance' => 'Medium',
            'surface' => 'Turf',
            'style' => 'Pace Chaser',
            'targets' => ['Speed' => 900, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600, 'Wit' => 500],
            'skill_priorities' => [],
            ...$overrides,
        ];
    }
}
