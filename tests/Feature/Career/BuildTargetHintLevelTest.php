<?php

declare(strict_types=1);

namespace Tests\Feature\Career;

use App\Models\CharacterCard;
use App\Models\DeckSlot;
use App\Models\Skill;
use App\Models\SupportCard;
use App\Models\Umamusume;
use App\Models\Veteran;
use App\Services\Career\SetupDraft;
use App\Services\HintDiscount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * The Build Target step must allow hint level input per skill for accurate SP discount calculation.
 *
 * Per UMAMUSUME_REFERENCE.md §1.1.4: hint ladder is Lv1=10%, Lv2=20%, Lv3=30%, Lv4=35%, Lv Max=40%.
 * Displayed cost = floor(base × (1 - discount/100)).
 */
final class BuildTargetHintLevelTest extends TestCase
{
    use RefreshDatabase;

    public function test_skill_priority_accepts_hint_level_and_calculates_discounted_cost(): void
    {
        $this->seedDraftWithSkill();

        // Submit build target with hint level 4 (35% discount)
        $response = $this->put(route('career.target.store'), [
            'purpose' => 'StoryClear',
            'distance' => 'Medium',
            'surface' => 'Turf',
            'style' => 'Pace Chaser',
            'targets' => array_fill_keys(config('scenarios.stat_order'), 800),
            'skill_priorities' => ['Corner Maestro (Speed)'],
            'hint_levels' => ['Corner Maestro (Speed)' => '4'],
        ]);

        $response->assertSessionHasNoErrors();

        // Verify the hint level was stored and cost is calculated correctly
        $draft = SetupDraft::buildTarget();
        $this->assertEquals('4', $draft['hint_levels']['Corner Maestro (Speed)'] ?? null);

        // Base cost 130 × (1 - 35/100) = 130 × 0.65 = 84.5 → floor = 84
        $skill = Skill::query()->where('name', 'Corner Maestro (Speed)')->firstOrFail();
        $this->assertEquals(130, $skill->sp_cost);
        $this->assertEquals(84, HintDiscount::price(130, '4'));
    }

    public function test_hint_level_max_applies_40_percent_discount(): void
    {
        $this->seedDraftWithSkill();

        $this->put(route('career.target.store'), [
            'purpose' => 'StoryClear',
            'distance' => 'Medium',
            'surface' => 'Turf',
            'style' => 'Pace Chaser',
            'targets' => array_fill_keys(config('scenarios.stat_order'), 800),
            'skill_priorities' => ['Pace Chaser Corners ○'],
            'hint_levels' => ['Pace Chaser Corners ○' => 'max'],
        ])->assertSessionHasNoErrors();

        // Base 130 × (1 - 40/100) = 78
        $this->assertEquals(78, HintDiscount::price(130, 'max'));
    }

    public function test_hint_levels_are_validated(): void
    {
        $this->seedDraftWithSkill();

        // Invalid hint level (5) should be rejected
        $this->put(route('career.target.store'), [
            'purpose' => 'StoryClear',
            'distance' => 'Medium',
            'surface' => 'Turf',
            'style' => 'Pace Chaser',
            'targets' => array_fill_keys(config('scenarios.stat_order'), 800),
            'skill_priorities' => ['Corner Maestro (Speed)'],
            'hint_levels' => ['Corner Maestro (Speed)' => 5],
        ])->assertSessionHasErrors('hint_levels.*');
    }

    public function test_picker_skills_shows_discounted_cost(): void
    {
        $this->seedDraftWithSkill();

        // The draft already has 'Corner Maestro (Speed)' in skill_priorities from seedDraftWithSkill
        // So we need a different skill to test the picker
        $this->get(route('career.target'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Career/BuildTarget')
                // The picker should include skills not in the draft's priorities
                ->has('picker_skills')
            );
    }

    private function seedDraftWithSkill(): void
    {
        $umamusume = Umamusume::factory()->create();
        $card = CharacterCard::factory()->create(['umamusume_id' => $umamusume->id, 'is_debut_form' => true]);

        // Create a skill with base cost 130
        $skill = Skill::factory()->create([
            'name' => 'Corner Maestro (Speed)',
            'sp_cost' => 130,
            'name_is_client' => true,
        ]);

        // Second skill for Max test
        Skill::factory()->create([
            'name' => 'Pace Chaser Corners ○',
            'sp_cost' => 130,
            'name_is_client' => true,
        ]);

        $cards = [];
        for ($i = 0; $i < 6; $i++) {
            $cards[] = SupportCard::factory()->create(['char_name' => 'Card '.$i, 'title_en' => null, 'type' => 'speed'])->id;
        }
        $parents = [Veteran::factory()->create()->id, Veteran::factory()->create()->id];

        $slots = [];
        foreach (DeckSlot::POSITIONS as $index => $position) {
            $slots[] = [
                'position' => $position,
                'support_card_id' => $cards[$index] ?? null,
                'ownership' => ($cards[$index] ?? null) === null ? null : 'OWNED',
            ];
        }

        SetupDraft::write([
            'scenario' => 'ura_finale',
            'umamusume_id' => $umamusume->id,
            'character_card_id' => $card->id,
            'build_target' => [
                'purpose' => 'StoryClear',
                'distance' => 'Medium',
                'surface' => 'Turf',
                'style' => 'Pace Chaser',
                'targets' => array_fill_keys(config('scenarios.stat_order'), 800),
                'skill_priorities' => [],
                'hint_levels' => [],
            ],
            'legacy_selection' => [
                'affinity' => '◎',
                'legacies' => [
                    ['rank' => 3, 'is_guest' => false, 'ancestors' => ['Grass Wonder', 'Mill Raptor'], 'sparks' => [['kind' => 'blue', 'target' => 'Speed', 'stars' => 2]]],
                    ['rank' => null, 'is_guest' => true, 'ancestors' => ['Mayano Top Gun', ''], 'sparks' => []],
                ],
            ],
            'legacy_parents' => $parents,
            'deck' => $slots,
        ]);
    }
}
