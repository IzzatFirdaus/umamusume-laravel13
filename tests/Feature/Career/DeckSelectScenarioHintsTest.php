<?php

declare(strict_types=1);

namespace Tests\Feature\Career;

use App\Models\DeckSlot;
use App\Models\SupportCard;
use App\Models\Umamusume;
use App\Models\Veteran;
use App\Services\Career\SetupDraft;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * The deck step must surface scenario-aware strategic hints.
 *
 * Per UMAMUSUME_REFERENCE.md §2.1, §2.2:
 * - Unity Cup: Wit cap is 1800, highlight Wit-heavy deck recommendation
 * - Trackblazer: No Race Goals, focus on Grade Point objectives
 * - Our Grand Concert: Speed cap is 1600, Lessons/Songs mechanic active
 */
final class DeckSelectScenarioHintsTest extends TestCase
{
    use RefreshDatabase;

    public function test_unity_cup_hint_highlights_wit(): void
    {
        $this->seedDraft('unity_cup');

        $this->get(route('career.deck'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Career/DeckSelect')
                ->where('scenarioKey', 'unity_cup')
                ->where('scenarioHints.0', 'Wit cap is 1800. Consider a Wit-heavy deck.')
            );
    }

    public function test_trackblazer_hint_prompts_grade_points(): void
    {
        $this->seedDraft('trackblazer');

        $this->get(route('career.deck'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Career/DeckSelect')
                ->where('scenarioKey', 'trackblazer')
                ->where('scenarioHints.0', 'No Race Goals. Focus on Grade Point objectives.')
            );
    }

    public function test_our_grand_concert_hint_mentions_lessons_and_songs(): void
    {
        $this->seedDraft('our_grand_concert');

        $this->get(route('career.deck'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Career/DeckSelect')
                ->where('scenarioKey', 'our_grand_concert')
                ->where('scenarioHints.0', 'Speed cap is 1600. Lessons/Songs mechanic active.')
            );
    }

    public function test_ura_finale_hint_is_baseline(): void
    {
        $this->seedDraft('ura_finale');

        $this->get(route('career.deck'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Career/DeckSelect')
                ->where('scenarioKey', 'ura_finale')
                ->where('scenarioHints.0', 'Standard scenario. No extra systems beyond training, racing, and objectives.')
            );
    }

    private function seedDraft(string $scenarioKey): void
    {
        $umamusume = Umamusume::factory()->create();
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
            'scenario' => $scenarioKey,
            'umamusume_id' => $umamusume->id,
            'build_target' => [
                'purpose' => 'StoryClear',
                'distance' => 'Medium',
                'surface' => 'Turf',
                'style' => 'Pace Chaser',
                'targets' => array_fill_keys(config('scenarios.stat_order'), 800),
                'skill_priorities' => [],
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
