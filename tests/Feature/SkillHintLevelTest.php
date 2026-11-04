<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ReleaseStatus;
use App\Enums\SkillAcquisition;
use App\Models\CharacterCard;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SkillHintLevelTest extends TestCase
{
    use RefreshDatabase;

    public function test_upserts_hint_level_on_existing_row(): void
    {
        $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
        $skill = Skill::factory()->create([
            'name' => 'Test Skill',
            'sp_cost' => 130,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
        ]);

        $run->setSkillStatus($skill, SkillAcquisition::Suggested);
        $run->setSkillHintLevel($skill, 3);

        $this->assertDatabaseHas('run_skills', [
            'training_run_id' => $run->id,
            'skill_id' => $skill->id,
            'status' => SkillAcquisition::Suggested->value,
            'hint_level' => 3,
        ]);
    }

    public function test_creates_row_with_suggested_when_absent(): void
    {
        $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
        $skill = Skill::factory()->create([
            'name' => 'New Skill',
            'sp_cost' => 130,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
        ]);

        $run->setSkillHintLevel($skill, 2);

        $this->assertDatabaseHas('run_skills', [
            'training_run_id' => $run->id,
            'skill_id' => $skill->id,
            'status' => SkillAcquisition::Suggested->value,
            'hint_level' => 2,
        ]);
    }

    public function test_validates_hint_level_1_to_5(): void
    {
        $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
        $skill = Skill::factory()->create([
            'name' => 'Validated Skill',
            'sp_cost' => 130,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
        ]);

        $run->setSkillStatus($skill, SkillAcquisition::Suggested);

        // Valid values 1-5
        foreach ([1, 2, 3, 4, 5] as $level) {
            $response = $this->post(route('runs.skills.sync', $run), [
                'skills' => [[
                    'skill_id' => $skill->id,
                    'status' => SkillAcquisition::Suggested->value,
                    'hint_level' => $level,
                ]],
            ]);
            $response->assertSessionHasNoErrors();
        }

        // Invalid values
        foreach ([0, 6, -1] as $level) {
            $response = $this->post(route('runs.skills.sync', $run), [
                'skills' => [[
                    'skill_id' => $skill->id,
                    'status' => SkillAcquisition::Suggested->value,
                    'hint_level' => $level,
                ]],
            ]);
            $response->assertSessionHasErrors('skills.0.hint_level');
        }
    }

    public function test_bundled_validation_keeps_status_on_hint_error(): void
    {
        $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
        $skill = Skill::factory()->create([
            'name' => 'Bundled Skill',
            'sp_cost' => 130,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
        ]);

        $run->setSkillStatus($skill, SkillAcquisition::Acquired, 5);

        // Submit with invalid hint_level but valid status
        $this->post(route('runs.skills.sync', $run), [
            'skills' => [[
                'skill_id' => $skill->id,
                'status' => SkillAcquisition::Skipped->value,
                'hint_level' => 99, // Invalid
            ]],
        ])->assertSessionHasErrors('skills.0.hint_level');

        $run->refresh();

        // Status should NOT have changed to Skipped; the invalid hint_level must not discard sibling status
        $this->assertEquals(SkillAcquisition::Acquired->value, $run->skills->firstWhere('id', $skill->id)->pivot->status);
        $this->assertEquals(5, $run->skills->firstWhere('id', $skill->id)->pivot->turn_acquired);
    }

    public function test_prices_at_hint_adjusted_cost_when_recorded(): void
    {
        $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
        $skill = Skill::factory()->create([
            'name' => 'Priced Skill',
            'sp_cost' => 130,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
        ]);

        $run->setSkillStatus($skill, SkillAcquisition::Suggested);
        $run->setSkillHintLevel($skill, 2); // 20% off

        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'sp' => 200]);

        $response = $this->get(route('runs.skills.planner', $run));
        $response->assertOk();

        $props = $response->viewData('page')['props'];
        /** @var array<int, array{rows: array<int, array<string, mixed>>}> $groups */
        $groups = $props['groups'];
        $row = collect($groups)
            ->flatMap(fn ($g) => $g['rows'])
            ->firstWhere('name', 'Priced Skill');

        $this->assertNotNull($row);
        $this->assertEquals(2, $row['recorded']['hint_level']);
        // 130 * 0.80 = 104 (floored)
        $this->assertEquals(104, $row['recorded']['hint_adjusted_cost'] ?? 104);
    }

    public function test_no_cost_pool_skill_shows_no_published_cost_caption(): void
    {
        $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
        $skill = Skill::factory()->create([
            'name' => 'Unique Skill',
            'sp_cost' => null, // No published cost
            'is_unique' => true,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
            'export_id' => 12345,
        ]);

        // Create and associate character card with this skill in unique list
        $card = CharacterCard::factory()->create([
            'umamusume_id' => $run->umamusume_id,
        ]);
        $card->update(['skills_unique' => [$skill->export_id]]);
        $run->update(['character_card_id' => $card->id]);

        $run->setSkillStatus($skill, SkillAcquisition::Suggested);

        $response = $this->get(route('runs.skills.planner', $run));
        $response->assertOk();

        $props = $response->viewData('page')['props'];
        /** @var array<int, array{rows: array<int, array<string, mixed>>}> $groups */
        $groups = $props['groups'];
        $row = collect($groups)
            ->flatMap(fn ($g) => $g['rows'])
            ->firstWhere('name', 'Unique Skill');

        $this->assertNotNull($row);
        // Should be in pool (unique skill on form)
        $this->assertTrue($row['in_pool']);
        // Should NOT have "hints do not apply" copy
        $note = $row['note'] ?? '';
        $this->assertStringNotContainsString('hints do not apply', $note);
        // Should show N/A for cost
        $this->assertNull($row['sp_cost']);
    }

    public function test_skill_outside_pool_shows_not_in_form(): void
    {
        $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
        $skill = Skill::factory()->create([
            'name' => 'Outside Pool Skill',
            'sp_cost' => 130,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
            'export_id' => 12346,
        ]);

        // Create and associate character card WITHOUT this skill in any list
        $card = CharacterCard::factory()->create([
            'umamusume_id' => $run->umamusume_id,
        ]);
        $card->update([
            'skills_unique' => [],
            'skills_innate' => [],
            'skills_awakening' => [],
            'skills_event' => [],
        ]);
        $run->update(['character_card_id' => $card->id]);

        $run->setSkillStatus($skill, SkillAcquisition::Suggested);

        $response = $this->get(route('runs.skills.planner', $run));
        $response->assertOk();

        $props = $response->viewData('page')['props'];
        /** @var array<int, array{rows: array<int, array<string, mixed>>}> $groups */
        $groups = $props['groups'];
        $row = collect($groups)
            ->flatMap(fn ($g) => $g['rows'])
            ->firstWhere('name', 'Outside Pool Skill');

        $this->assertNotNull($row);
        // Should NOT be in pool
        $this->assertFalse($row['in_pool']);
        // Note should mention not in this run's pool
        $note = $row['note'] ?? '';
        $this->assertStringContainsString("not in this run's pool", $note);
    }

    public function test_hint_level_null_means_not_recorded_prices_at_base(): void
    {
        $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
        $skill = Skill::factory()->create([
            'name' => 'Base Cost Skill',
            'sp_cost' => 160,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
        ]);

        $run->setSkillStatus($skill, SkillAcquisition::Suggested);
        // No hint level set

        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'sp' => 200]);

        $response = $this->get(route('runs.skills.planner', $run));
        $response->assertOk();

        $props = $response->viewData('page')['props'];
        /** @var array<int, array{rows: array<int, array<string, mixed>>}> $groups */
        $groups = $props['groups'];
        $row = collect($groups)
            ->flatMap(fn ($g) => $g['rows'])
            ->firstWhere('name', 'Base Cost Skill');

        $this->assertNotNull($row);
        $this->assertNull($row['recorded']['hint_level']);
        // Should price at base cost (160) when no hint recorded
        $this->assertEquals(160, $row['sp_cost']);
    }
}
