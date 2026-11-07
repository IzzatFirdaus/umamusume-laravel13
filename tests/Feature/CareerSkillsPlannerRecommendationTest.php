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

/**
 * The Skills Planner's P4 recommendation panel (`Career/SkillsPlanner.vue`,
 * `SkillsPlannerController::recommendedSection()`).
 *
 * The panel's rule is this tool's own heuristic, so every case here pins one clause of it: the
 * priorities come first at the run's recorded hint state, the fill is cheapest-displayed-cost
 * first, the fill stops at the budget instead of overrunning it, the two edge paths (shortfall,
 * residual) are stated rather than silently dropped, adding the recommendation writes plan
 * entries once, and those entries stay editable afterwards.
 *
 * The rendered panel — the closure statement, the add button firing once on a double click, the
 * budget bar moving — is `tests/browser/career-skills-planner.spec.ts`'s; a prop assertion cannot
 * prove a number reached the screen.
 */
final class CareerSkillsPlannerRecommendationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Item 1: the recommendation starts with every priority, in the Trainer's order, priced at the
     * hint level the run has recorded rather than at the catalogue base.
     */
    public function test_priorities_come_first_in_priority_order_at_the_recorded_hint_price(): void
    {
        $run = $this->plannerRun();
        $first = $this->skill('First Priority', 130);
        $second = $this->skill('Second Priority', 100);
        $third = $this->skill('Third Priority', 80);

        $run->characterCard()->update([
            'skills_unique' => [$first->export_id],
            'skills_innate' => [$second->export_id, $third->export_id],
        ]);

        $run->update(['build_target' => $this->target([
            'skill_priorities' => ['First Priority', 'Second Priority', 'Third Priority'],
        ])]);

        // Hint Lv2 is 20% off: 130 -> 104. The priority's displayed price is what the panel prints.
        $run->setSkillHintLevel($first, 2);

        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'sp' => 500]);

        $recommended = $this->recommended($run);

        $this->assertSame(
            ['First Priority', 'Second Priority', 'Third Priority'],
            array_column($recommended['rows'], 'name'),
        );
        $this->assertSame(130, $recommended['rows'][0]['sp_cost']);
        $this->assertSame(104, $recommended['rows'][0]['hint_adjusted_cost']);
        $this->assertSame(100, $recommended['rows'][1]['hint_adjusted_cost']);
        $this->assertSame(80, $recommended['rows'][2]['hint_adjusted_cost']);
    }

    /**
     * Item 2: the fill is ordered by displayed cost, so a base-130 skill at Hint Lv2 (104) sorts
     * ahead of a base-105 skill with no hint. The accent is on *displayed*: ordering on the base
     * would put the 105 first and quietly overrule the hint the run recorded.
     */
    public function test_fill_is_ordered_by_hint_adjusted_cost_not_base_cost(): void
    {
        $run = $this->plannerRun();
        $priority = $this->skill('Priority Skill', 100);
        $hinted = $this->skill('Hinted Fill', 130);
        $plain = $this->skill('Plain Fill', 105);

        $run->characterCard()->update([
            'skills_unique' => [$priority->export_id],
            'skills_innate' => [$hinted->export_id, $plain->export_id],
        ]);
        $run->update(['build_target' => $this->target(['skill_priorities' => ['Priority Skill']])]);

        $run->setSkillHintLevel($hinted, 2);

        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'sp' => 500]);

        $rows = $this->recommended($run)['rows'];

        $this->assertSame(['Priority Skill', 'Hinted Fill', 'Plain Fill'], array_column($rows, 'name'));
        $this->assertSame(130, $rows[1]['sp_cost']);
        $this->assertSame(104, $rows[1]['hint_adjusted_cost']);
        $this->assertSame(105, $rows[2]['hint_adjusted_cost']);
    }

    /**
     * Item 3: the fill stops at the budget. With 200 SP and a 100 SP priority, 60 SP fits and the
     * next cheapest (80) does not, so the recommendation totals 160 and never 240.
     */
    public function test_fill_stops_when_the_next_skill_would_exceed_the_budget(): void
    {
        $run = $this->plannerRun();
        $priority = $this->skill('Priority Skill', 100);
        $cheap = $this->skill('Cheap Fill', 60);
        $next = $this->skill('Next Fill', 80);

        $run->characterCard()->update([
            'skills_unique' => [$priority->export_id],
            'skills_innate' => [$cheap->export_id, $next->export_id],
        ]);
        $run->update(['build_target' => $this->target(['skill_priorities' => ['Priority Skill']])]);

        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'sp' => 200]);

        $recommended = $this->recommended($run);

        $this->assertSame(['Priority Skill', 'Cheap Fill'], array_column($recommended['rows'], 'name'));
        $this->assertSame(160, $recommended['total']);
        $this->assertLessThanOrEqual(200, $recommended['total']);
        $this->assertSame(0, $recommended['shortfall']);
    }

    /**
     * Item 4: when the priorities alone exceed the budget, the recommendation is exactly the
     * priorities — no fill rows — and the shortfall is stated.
     */
    public function test_shortfall_returns_priorities_only_and_states_the_shortfall(): void
    {
        $run = $this->plannerRun();
        $expensive = $this->skill('Expensive One', 200);
        $expensiveTwo = $this->skill('Expensive Two', 200);
        $cheap = $this->skill('Cheap Fill', 50);

        $run->characterCard()->update([
            'skills_unique' => [$expensive->export_id],
            'skills_innate' => [$expensiveTwo->export_id, $cheap->export_id],
        ]);
        $run->update(['build_target' => $this->target([
            'skill_priorities' => ['Expensive One', 'Expensive Two'],
        ])]);

        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'sp' => 250]);

        $recommended = $this->recommended($run);

        $this->assertSame(['Expensive One', 'Expensive Two'], array_column($recommended['rows'], 'name'));
        $this->assertSame(400, $recommended['total']);
        $this->assertSame(150, $recommended['shortfall']);
    }

    /**
     * Item 5: when the budget outlasts the pool, every priced pool skill is recommended and the
     * residual (SP left unspent) is what the panel prints.
     */
    public function test_residual_returns_the_whole_pool_and_states_the_residual(): void
    {
        $run = $this->plannerRun();
        $priority = $this->skill('Priority Skill', 100);
        $small = $this->skill('Small Fill', 30);
        $large = $this->skill('Large Fill', 50);

        $run->characterCard()->update([
            'skills_unique' => [$priority->export_id],
            'skills_innate' => [$small->export_id, $large->export_id],
        ]);
        $run->update(['build_target' => $this->target(['skill_priorities' => ['Priority Skill']])]);

        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'sp' => 500]);

        $recommended = $this->recommended($run);

        // Priorities first, then every priced pool skill, cheapest first.
        $this->assertSame(['Priority Skill', 'Small Fill', 'Large Fill'], array_column($recommended['rows'], 'name'));
        $this->assertSame(180, $recommended['total']);
        $this->assertSame(500 - 180, $recommended['sp'] - $recommended['total']);
        $this->assertSame(0, $recommended['shortfall']);
    }

    /**
     * Item 6: adding the recommendation twice writes one plan entry per skill. The endpoint
     * upserts on `(run, skill)`, so the second submit is a no-op rather than a second row.
     */
    public function test_adding_the_recommendation_twice_produces_one_plan_entry_per_skill(): void
    {
        $run = $this->plannerRun();
        $priority = $this->skill('Priority Skill', 100);
        $fill = $this->skill('Fill Skill', 50);

        $run->characterCard()->update([
            'skills_unique' => [$priority->export_id],
            'skills_innate' => [$fill->export_id],
        ]);
        $run->update(['build_target' => $this->target(['skill_priorities' => ['Priority Skill']])]);

        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'sp' => 200]);

        $ids = array_column($this->recommended($run)['rows'], 'id');
        $payload = ['skills' => array_map(static fn (int $id): array => [
            'skill_id' => $id,
            'status' => SkillAcquisition::Suggested->value,
        ], $ids)];

        $this->post(route('runs.skills.sync', $run), $payload)->assertSessionHasNoErrors();
        $this->post(route('runs.skills.sync', $run), $payload)->assertSessionHasNoErrors();

        $run->refresh();

        foreach ($ids as $id) {
            $this->assertSame(1, $run->skills->where('id', $id)->count(), "skill {$id} has more than one plan entry");
        }
    }

    /**
     * Item 7: after adding, the plan entries stay editable — the hint level can be recorded and
     * the status moved to Acquired — and the planner reprices and then drops an acquired skill
     * from the recommendation. Removal is local plan state, covered by the browser spec.
     */
    public function test_plan_entries_stay_editable_after_adding(): void
    {
        $run = $this->plannerRun();
        $priority = $this->skill('Priority Skill', 100);
        $fill = $this->skill('Fill Skill', 50);

        $run->characterCard()->update([
            'skills_unique' => [$priority->export_id],
            'skills_innate' => [$fill->export_id],
        ]);
        $run->update(['build_target' => $this->target(['skill_priorities' => ['Priority Skill']])]);

        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'sp' => 200]);

        $ids = array_column($this->recommended($run)['rows'], 'id');

        $this->post(route('runs.skills.sync', $run), [
            'skills' => array_map(static fn (int $id): array => [
                'skill_id' => $id,
                'status' => SkillAcquisition::Suggested->value,
            ], $ids),
        ])->assertSessionHasNoErrors();

        // The added rows render in the Available group, with the pool flag that offers the hint
        // selector on the row.
        $props = $this->plannerProps($run);
        $available = $props['groups'][1];
        $this->assertSame('available', $available['key']);
        $this->assertContains('Fill Skill', array_column($available['rows'], 'name'));
        $this->assertTrue($available['rows'][0]['in_pool']);

        // Hint: Lv2 on the 50 SP fill prices it at 40, and the recommendation picks that up.
        $this->post(route('runs.skills.sync', $run), [
            'skills' => [[
                'skill_id' => $fill->id,
                'status' => SkillAcquisition::Suggested->value,
                'hint_level' => 2,
            ]],
        ])->assertSessionHasNoErrors();

        $run->refresh();
        $this->assertSame(2, $run->skills->firstWhere('id', $fill->id)->pivot->hint_level);

        $rows = collect($this->recommended($run)['rows'])->keyBy('name');
        $this->assertSame(40, $rows['Fill Skill']['hint_adjusted_cost']);

        // Status: an acquired skill leaves the recommendation and the plan entirely.
        $this->post(route('runs.skills.sync', $run), [
            'skills' => [[
                'skill_id' => $fill->id,
                'status' => SkillAcquisition::Acquired->value,
                'turn_acquired' => 2,
                'hint_level' => 2,
            ]],
        ])->assertSessionHasNoErrors();

        $run->refresh();
        $this->assertSame(SkillAcquisition::Acquired->value, $run->skills->firstWhere('id', $fill->id)->pivot->status);
        $this->assertNotContains('Fill Skill', array_column($this->recommended($run)['rows'], 'name'));

        // The learned row keeps its recorded hint: the commit path carries it, so a write cannot
        // silently reset the price the recommendation had been using.
        $this->assertSame(2, $run->skills->firstWhere('id', $fill->id)->pivot->hint_level);
    }

    /**
     * The panel refuses to invent a recommendation when the run has never recorded SP: no rows,
     * no total, and no shortfall (the absence is not a zero).
     */
    public function test_recommendation_is_empty_when_no_sp_is_recorded(): void
    {
        $run = $this->plannerRun();
        $priority = $this->skill('Priority Skill', 100);

        $run->characterCard()->update(['skills_unique' => [$priority->export_id]]);
        $run->update(['build_target' => $this->target(['skill_priorities' => ['Priority Skill']])]);

        $recommended = $this->recommended($run);

        $this->assertSame([], $recommended['rows']);
        $this->assertSame(0, $recommended['total']);
        $this->assertSame(0, $recommended['count']);
        $this->assertNull($recommended['sp']);
        $this->assertNull($recommended['shortfall']);
    }

    private function plannerRun(): TrainingRun
    {
        $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
        $card = CharacterCard::factory()->create(['umamusume_id' => $run->umamusume_id]);
        $run->update(['character_card_id' => $card->id]);

        return $run;
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int, count: int, sp: int|null, shortfall: int|null}
     */
    private function recommended(TrainingRun $run): array
    {
        return $this->plannerProps($run)['recommended'];
    }

    /**
     * @return array<string, mixed>
     */
    private function purchasable(TrainingRun $run): array
    {
        return $this->plannerProps($run)['purchasable'];
    }

    /**
     * @return array<string, mixed>
     */
    private function plannerProps(TrainingRun $run): array
    {
        $response = $this->get(route('runs.skills.planner', $run));
        $response->assertOk();

        return $response->viewData('page')['props'];
    }

    private function skill(string $name, int $sp): Skill
    {
        return Skill::factory()->create([
            'name' => $name,
            'sp_cost' => $sp,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
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
            'hint_levels' => [],
            ...$overrides,
        ];
    }

    /**
     * R7-84: the panel said "no recorded SP" while the budget bar on the same screen showed 173.
     *
     * The recommendation read SP from `$run->turnEntries->sortByDesc('turn')->first()?->sp`, so a run
     * whose LATEST turn recorded no SP had no budget at all -- even though an earlier turn had recorded
     * one and the bar printed it. The run's SP is the most recent turn that actually carries a figure,
     * not the figure the newest row happens to hold.
     */
    public function test_reads_the_run_sp_from_the_latest_turn_that_recorded_one(): void
    {
        $run = $this->plannerRun();
        $skill = $this->skill('Budgeted Skill', 100);

        $run->characterCard()->update(['skills_innate' => [$skill->export_id]]);

        // Turn 1 records the pool of Skill Points; turn 2 is a race that records none.
        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'sp' => 173]);
        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 2, 'sp' => null]);

        $recommended = $this->recommended($run);

        $this->assertSame(173, $recommended['sp'], 'the run holds the SP its last recording turn states');
        $this->assertSame(0, $recommended['shortfall'], 'a 100 SP skill against 173 held is not short');
        $this->assertNotEmpty($recommended['rows'], 'a priced skill must be offered when the run holds SP');
    }

    /**
     * R7-85: the Purchasable panel listed the whole Global catalogue -- 476 priced skills and a 7140 SP
     * total on a run whose own pool is a handful -- because it queried `availableOnGlobal()` without the
     * pool filter the recommendation has always applied. The shortfall it stated was the catalogue's,
     * not the run's, so the figure could never be met by any play of that career.
     */
    public function test_purchasable_lists_the_run_pool_not_the_whole_catalogue(): void
    {
        $run = $this->plannerRun();
        $mine = $this->skill('Pool Skill', 100);
        $theirs = $this->skill('Catalogue Only Skill', 200);

        // Only the first is in this run's pool: the card's innate list names it, nothing names the other.
        $run->characterCard()->update(['skills_innate' => [$mine->export_id]]);

        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'sp' => 500]);

        $purchasable = $this->purchasable($run);

        $names = array_column($purchasable['rows'], 'name');

        $this->assertContains('Pool Skill', $names, 'the run\'s own pool skill must be offered');
        $this->assertNotContains('Catalogue Only Skill', $names, 'a skill outside the pool cannot be learned');
        $this->assertSame(1, $purchasable['count'], 'the count states the pool, not the catalogue');
    }
}
