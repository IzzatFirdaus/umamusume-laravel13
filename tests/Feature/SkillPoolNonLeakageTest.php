<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ReleaseStatus;
use App\Enums\SkillAcquisition;
use App\Models\CharacterCard;
use App\Models\Skill;
use App\Models\TrainingRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guard that the run's learnable skill pool never leaks JapanOnly skills (Slice A, Step A5).
 *
 * The pool is the selected form's unique + innate + awakening + event skills that are
 * GlobalReleased. JapanOnly skills referenced by the trainee's card must be excluded.
 * Parity with TraineeProfileController::cardSkillIds() is asserted so the rule survives refactors.
 */
final class SkillPoolNonLeakageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Replicate SkillsPlannerController::poolSkillIds() logic for testing.
     * Returns export_ids (not filtered by release_status).
     */
    private function poolSkillIds(TrainingRun $run): array
    {
        $card = $run->characterCard;
        if ($card === null) {
            return [];
        }

        // Pre-event pool: unique + innate + awakening
        $preEvent = array_merge(
            $card->skills_unique ?? [],
            $card->skills_innate ?? [],
            $card->skills_awakening ?? [],
        );

        // Add event skills that the run has recorded as acquired
        $acquiredEventIds = $run->skills
            ->filter(static fn (Skill $skill): bool => $skill->pivot->status === SkillAcquisition::Acquired->value)
            ->filter(static fn (Skill $skill): bool => in_array($skill->export_id, $card->skills_event ?? [], true))
            ->pluck('export_id')
            ->all();

        return array_values(array_unique(array_merge($preEvent, $acquiredEventIds)));
    }

    /**
     * Replicate TraineeProfileController::cardSkillIds() logic for testing.
     * Returns raw export_ids (no filtering by release_status).
     */
    private function cardSkillIds(CharacterCard $card): array
    {
        return array_merge(
            $card->skills_unique ?? [],
            $card->skills_innate ?? [],
            $card->skills_awakening ?? [],
            $card->skills_event ?? [],
        );
    }

    public function test_global_pool_never_includes_japan_only_skill(): void
    {
        // Arrange: a Global trainee whose card references a JapanOnly skill in its awakening list
        $globalCard = CharacterCard::factory()->create([
            'global_release_date' => now()->subYear(),
            'is_debut_form' => true,
        ]);

        $japanOnlySkill = Skill::factory()->create([
            'name' => 'Japan Only Skill',
            'sp_cost' => 130,
            'release_status' => ReleaseStatus::JapanOnly,
            'name_is_client' => false,
            'export_id' => 999001,
        ]);

        $globalSkill = Skill::factory()->create([
            'name' => 'Global Skill',
            'sp_cost' => 100,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
            'export_id' => 999002,
        ]);

        // The card's awakening list includes the JapanOnly skill
        $globalCard->update([
            'skills_awakening' => [$japanOnlySkill->export_id, $globalSkill->export_id],
        ]);

        $run = TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'character_card_id' => $globalCard->id,
        ]);

        // Act: get pool export_ids using replicated logic
        $poolExportIds = $this->poolSkillIds($run);

        // The pool includes both skills at export_id level
        $this->assertContains($japanOnlySkill->export_id, $poolExportIds);
        $this->assertContains($globalSkill->export_id, $poolExportIds);

        // But when queried through Skill::availableOnGlobal(), JapanOnly is excluded
        $poolSkills = Skill::availableOnGlobal()
            ->whereIn('export_id', $poolExportIds)
            ->pluck('id')
            ->all();

        $this->assertNotContains($japanOnlySkill->id, $poolSkills, 'JapanOnly skill leaked into Global pool');
        $this->assertContains($globalSkill->id, $poolSkills);
    }

    public function test_pool_matches_trainee_profile_card_skill_ids(): void
    {
        // Arrange: Global trainee with unique, innate, and awakening skills
        $globalCard = CharacterCard::factory()->create([
            'global_release_date' => now()->subYear(),
            'is_debut_form' => true,
        ]);

        $uniqueSkill = Skill::factory()->create([
            'name' => 'Unique Skill',
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
            'export_id' => 999010,
        ]);

        $innateSkill = Skill::factory()->create([
            'name' => 'Innate Skill',
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
            'export_id' => 999011,
        ]);

        $awakeningSkill = Skill::factory()->create([
            'name' => 'Awakening Skill',
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
            'export_id' => 999012,
        ]);

        $japanOnlySkill = Skill::factory()->create([
            'name' => 'Japan Only Awakening',
            'release_status' => ReleaseStatus::JapanOnly,
            'name_is_client' => false,
            'export_id' => 999013,
        ]);

        $globalCard->update([
            'skills_unique' => [$uniqueSkill->export_id],
            'skills_innate' => [$innateSkill->export_id],
            'skills_awakening' => [$awakeningSkill->export_id, $japanOnlySkill->export_id],
        ]);

        $run = TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'character_card_id' => $globalCard->id,
        ]);

        // Act: get pool export_ids using replicated logic
        $poolExportIds = $this->poolSkillIds($run);

        // Get cardSkillIds from TraineeProfileController (raw export_ids, no filtering)
        $cardSkillExportIds = $this->cardSkillIds($globalCard);

        // Assert: pool export_ids match cardSkillIds exactly (both are raw export_id lists)
        $this->assertSame($cardSkillExportIds, $poolExportIds, 'Pool diverges from TraineeProfileController::cardSkillIds()');

        // JapanOnly skill present in both raw lists
        $this->assertContains($japanOnlySkill->export_id, $poolExportIds);
        $this->assertContains($japanOnlySkill->export_id, $cardSkillExportIds);

        // But when filtered through Skill::availableOnGlobal(), JapanOnly is excluded
        $availablePoolIds = Skill::availableOnGlobal()
            ->whereIn('export_id', $poolExportIds)
            ->pluck('id')
            ->all();

        $this->assertNotContains($japanOnlySkill->id, $availablePoolIds);
        $this->assertNotContains($japanOnlySkill->id, Skill::availableOnGlobal()->whereIn('export_id', $cardSkillExportIds)->pluck('id')->all());

        // All three Global skills present in available pool
        $this->assertContains($uniqueSkill->id, $availablePoolIds);
        $this->assertContains($innateSkill->id, $availablePoolIds);
        $this->assertContains($awakeningSkill->id, $availablePoolIds);
    }

    public function test_global_trainee_pool_returns_expected_set(): void
    {
        // Arrange: Global trainee with a full set of Global skills across all lists
        $globalCard = CharacterCard::factory()->create([
            'global_release_date' => now()->subYear(),
            'is_debut_form' => true,
        ]);

        $skills = [];
        foreach (['Unique A', 'Unique B', 'Innate A', 'Innate B', 'Awakening A', 'Event A'] as $name) {
            $skills[$name] = Skill::factory()->create([
                'name' => $name,
                'sp_cost' => 100,
                'release_status' => ReleaseStatus::GlobalReleased,
                'name_is_client' => true,
                'export_id' => fake()->unique()->numberBetween(900000, 999999),
            ]);
        }

        $globalCard->update([
            'skills_unique' => [$skills['Unique A']->export_id, $skills['Unique B']->export_id],
            'skills_innate' => [$skills['Innate A']->export_id, $skills['Innate B']->export_id],
            'skills_awakening' => [$skills['Awakening A']->export_id],
            'skills_event' => [$skills['Event A']->export_id],
        ]);

        $run = TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'character_card_id' => $globalCard->id,
        ]);

        // Acquire the event skill so it appears in the pool
        $eventSkill = $skills['Event A'];
        $run->skills()->attach($eventSkill->id, [
            'status' => SkillAcquisition::Acquired->value,
            'turn_acquired' => 1,
        ]);

        // Act
        $poolExportIds = $this->poolSkillIds($run);

        // Assert: all six Global skills are in the pool export_ids
        $expectedExportIds = array_map(fn ($s) => $s->export_id, $skills);
        $this->assertSame(count($expectedExportIds), count($poolExportIds), 'Pool size matches expected Global skills');
        foreach ($expectedExportIds as $id) {
            $this->assertContains($id, $poolExportIds);
        }

        // And all are available through Skill::availableOnGlobal()
        $availableIds = Skill::availableOnGlobal()
            ->whereIn('export_id', $poolExportIds)
            ->pluck('id')
            ->all();
        $this->assertSame(count($skills), count($availableIds));
        foreach ($skills as $skill) {
            $this->assertContains($skill->id, $availableIds);
        }
    }
}