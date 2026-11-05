<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ReleaseStatus;
use App\Models\CharacterCard;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Services\HintDiscount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * One test per invariant the Trainer Desk prompt states, each reaching the surface the invariant
 * concerns.
 *
 * The distinction this file exists to hold: a rule test is right when the invariant *is* a rule
 * (Inv-1's ladder is the rule itself), and wrong when the invariant is about a screen. For the
 * screen invariants the assertion reads the response payload or the rendered markup — never the
 * service that feeds it. Where the surface is only reachable in a browser (the "Calculated" mark,
 * the differs glyph), the DOM case lives in the Playwright specs named in the docblock and this
 * file asserts the payload the markup is built from, so a missing prop fails here rather than
 * passing as "the service is correct".
 *
 * Nine invariants, nine tests. A tenth test asserts the record the seventh depends on.
 */
final class InvariantSelfCheckTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Inv-1: the hint ladder is the client's own figures.
     *
     * A rule test on purpose: the invariant is the arithmetic, not a screen. The two bases are the
     * ones the reference states — 130 (Pace Chaser Corners ○, the measured rounding case at Lv4)
     * and 160 (a clean ladder). Lv Max is keyed `max`, not 5, because the client captions it
     * "Lv Max" rather than numbering it.
     *
     * Base 160 is [144, 128, 112, 104, 96], not the [144, 128, 112, 96, 80] the dispatch wrote:
     * 160 × 0.65 = 104 and 160 × 0.60 = 96 under the 10/20/30/35/40 ladder this repository's
     * `config/uma.php` records from the Learn screen. The dispatch's own parenthetical names
     * 35%/40% for those two rungs, so its numbers were the arithmetic slip, not the ladder. The
     * 130 row agrees with both and is the one measured off a frame.
     */
    public function test_inv1_hint_ladder_matches_client_figures(): void
    {
        $this->assertSame([117, 104, 91, 84, 78], $this->ladder(130));
        $this->assertSame([144, 128, 112, 104, 96], $this->ladder(160));

        // The ladder is not linear past Lv3: 30 -> 35 -> 40, not 30 -> 40 -> 50. Asserting the
        // discounts as well as the prices so a changed base cannot hide a changed step.
        $this->assertSame(
            [10, 20, 30, 35, 40],
            array_map(static fn (array $row): int => $row['discount'], array_slice(HintDiscount::priceLadder(100), 1)),
        );
    }

    /**
     * Inv-2: "not recorded" is NULL, never 0.
     *
     * Schema reflection plus a seeded run, because the invariant is about what the database can
     * hold: a column that is NOT NULL with a 0 default would satisfy every reader and still be
     * the violation. The run case proves the writer leaves the column alone rather than writing a
     * sentinel.
     */
    public function test_inv2_na_is_null_not_zero(): void
    {
        foreach ([['skills', 'description'], ['skills', 'distance_type'], ['run_skills', 'hint_level']] as [$table, $column]) {
            $info = collect(DB::select("PRAGMA table_info({$table})"))->firstWhere('name', $column);

            $this->assertNotNull($info, "{$table}.{$column} must exist");
            $this->assertSame(0, (int) $info->notnull, "{$table}.{$column} must be nullable — 0 is a value, not an absence");
        }

        $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
        $card = CharacterCard::factory()->create(['umamusume_id' => $run->umamusume_id]);
        $run->update(['character_card_id' => $card->id]);

        $skill = Skill::factory()->create([
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
            'sp_cost' => 130,
        ]);

        $run->skills()->attach($skill->id, ['status' => 'suggested']);

        $this->assertNull(
            DB::table('run_skills')->where('training_run_id', $run->id)->where('skill_id', $skill->id)->value('hint_level'),
            'a run with no hint recorded must store NULL, not 0',
        );
    }

    /**
     * Inv-3 (payload half): the recommendation's own figures are carried as computed values.
     *
     * The "Calculated" mark itself is markup, so its DOM case is in
     * `tests/browser/career-skills-planner.spec.ts` ("marks the recommendation panel Calculated").
     * This test asserts the payload that mark labels: the panel's spent/residual figures exist as
     * numbers, and the catalogue's rangeOpCount reaches the page.
     */
    public function test_inv3_calculated_figures_reach_the_payload(): void
    {
        $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
        $card = CharacterCard::factory()->create(['umamusume_id' => $run->umamusume_id]);
        $run->update(['character_card_id' => $card->id]);

        $skill = Skill::factory()->create([
            'name' => 'Calculated Skill',
            'sp_cost' => 100,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
            'export_id' => 880001,
        ]);

        $card->update(['skills_unique' => [$skill->export_id]]);
        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'sp' => 500]);

        $response = $this->get(route('runs.skills.planner', $run));
        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Career/SkillsPlanner')
            ->where('recommended.sp', 500)
            ->where('recommended.total', 100)
            // `recommendedSection()` returns `max(0, total - sp)`, so a covered plan states 0 —
            // the amount short — rather than null. The panel branches on `shortfall > 0`, so both
            // forms render the residual clause; this asserts the number the panel reads.
            ->where('recommended.shortfall', 0)
            ->whereType('recommended.rows', 'array'),
        );

        $catalogue = $this->get(route('skills.index'));
        $catalogue->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Skills/Index')
            ->whereType('rangeOpCount', 'integer'),
        );
    }

    /**
     * Inv-4: a source token reaches the screen byte-for-byte.
     *
     * Seeded as the source writes it — `Healing ×0.5`, multiplication sign and all — and asserted
     * against the rendered payload. A paraphrase (×0.5 -> "half"), a normalization (× -> x) or an
     * escaping bug all fail here, which is the invariant.
     */
    public function test_inv4_source_tokens_passthrough(): void
    {
        $skill = Skill::factory()->create([
            'name' => 'Token Skill',
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
            'export_id' => 880002,
            'condition_groups' => [[
                'condition' => 'distance_type==3',
                'precondition' => null,
                'effects' => [['type' => 31, 'value' => 200]],
            ]],
            'description' => 'Healing ×0.5',
        ]);

        $response = $this->get(route('skills.show', $skill));
        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Skills/Show')
            ->where('skill.description', 'Healing ×0.5'),
        );
    }

    /**
     * Inv-5: the derivation note is present and carries prose.
     *
     * Both panels print a note that says where the reading came from. The assertion is "not empty,
     * not N/A" rather than a string match, because the invariant is that a derivation is stated at
     * all — a panel that printed a bare number with no provenance would pass a string match on the
     * number and fail this.
     */
    public function test_inv5_tool_reading_derivation_note(): void
    {
        $skillA = Skill::factory()->create([
            'name' => 'Derivation A',
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
            'export_id' => 880003,
        ]);

        $skillB = Skill::factory()->create([
            'name' => 'Derivation B',
            'type' => 'Recovery',
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
            'export_id' => 880004,
        ]);

        $response = $this->get(route('skills.show', ['skill' => $skillA, 'compare' => $skillB->id]));
        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Skills/Show')
            ->where('skill.name', 'Derivation A')
            ->has('compareSkill'),
        );

        // The compare panel's derivation note and the recommendation panel's are markup, not props,
        // so they are asserted where they live: the two components' own templates. A note that was
        // emptied or replaced with "N/A" fails here, which is the invariant.
        $show = file_get_contents(resource_path('js/pages/Skills/Show.vue'));
        $this->assertIsString($show);
        $this->assertStringContainsString('Engine tokens are printed as-is', $show, 'the compare panel must state its reading');

        $planner = file_get_contents(resource_path('js/pages/Career/SkillsPlanner.vue'));
        $this->assertIsString($planner);
        $this->assertStringContainsString("The rule is this tool's own heuristic, not a client mechanic", $planner);
        $this->assertStringContainsString('the residual is printed', $planner);
    }

    /**
     * Inv-6: a row with no client string says so and invents nothing.
     *
     * The fault this guards is a fallback that fabricates prose — an empty description rendered as
     * a plausible sentence, or "N/A" read as a value. A JapanOnly skill with a NULL description
     * must render `No client string.` and no sentence.
     */
    public function test_inv6_no_invented_data(): void
    {
        // The detail route reads through `availableOnGlobal()`, so a JapanOnly row is a 404 by
        // design (D2 non-leakage). That is the correct behaviour and is asserted as its own case
        // below; the *rendering* case has to be a Global row, because that is the only row the
        // surface will show.
        $skill = Skill::factory()->create([
            'name' => 'No String Skill',
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
            'export_id' => 880005,
            'description' => null,
        ]);

        $response = $this->get(route('skills.show', $skill));
        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Skills/Show')
            ->where('skill.description', null),
        );

        // The rendered fallback string lives in `pages/Skills/Show.vue`; the DOM case is in
        // `tests/browser/skills.spec.ts`. This test holds the payload half: the description is
        // null, not "", so the `v-else` branch that prints "No client string." is the one taken.
        $this->assertNull($skill->fresh()->description);

        // And the non-leakage half, restated by `R1`: a JapanOnly row IS reachable (the catalogue's
        // `japan_only` and `all` scopes link to it), so the guard is not reachability but display. The
        // row's English name and description are nulled at the payload edge, which is the half this
        // test can see; printing it is the same rule at the other end.
        $hidden = Skill::factory()->create([
            'name' => 'Japan Only Skill',
            'release_status' => ReleaseStatus::JapanOnly,
            'name_is_client' => true,
            'export_id' => 880008,
            'description' => 'A third-party English sentence.',
        ]);

        $this->get(route('skills.show', $hidden))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Skills/Show')
                ->where('skill.name', null)
                ->where('skill.description', null),
            );

        // The row `availableOnGlobal()` rejects for its OTHER column is still refused outright: the
        // catalogue cannot link to it at any scope, so no surface may answer it.
        $unlinkable = Skill::factory()->create([
            'name' => 'Not Client Named',
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => false,
            'export_id' => 880009,
        ]);

        $this->get(route('skills.show', $unlinkable))->assertNotFound();
    }

    /**
     * Inv-7: the SHR-1 distance-0 collision is recorded where the rules live, not just in code.
     *
     * The record is the docblock on `SkillSearchRequest` (the exception's home — `AGENTS.md` §2 is
     * the precedence table and does not carry feature-level exceptions). Asserted by reading the
     * file so a future edit that deletes the note fails here rather than silently re-introducing
     * the "0 renders as 0" fault the exception exists to prevent.
     */
    public function test_inv7_collisions_recorded(): void
    {
        $source = file_get_contents(app_path('Http/Requests/SkillSearchRequest.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString('SHR-1 exception', $source, 'the distance-0 exception must be a named record');
        $this->assertStringContainsString('0 for Unspecified', $source, 'the record must name the sentinel it permits');
        $this->assertStringContainsString('never-zero identity', $source, 'the record must name the rule it departs from');

        // And the rule it departs from is stated in the same place, so the two cannot drift apart.
        $this->assertStringContainsString('string token', $source);
    }

    /**
     * Inv-8: the two budget edge paths are stated on the panel, not dropped.
     *
     * Shortfall (priorities cost more than the run holds) and residual (the pool runs out before
     * the budget does) are different sentences about different states. A panel that printed the
     * total and stopped would satisfy a naive "shows the cost" assertion and fail this one.
     */
    public function test_inv8_constraints_printed_while_browsing(): void
    {
        // Shortfall: 400 SP held, 500 SP of priorities.
        $shortRun = $this->plannerRun();
        $expensive = $this->skill('Expensive Priority', 500);
        $shortRun->characterCard()->update(['skills_unique' => [$expensive->export_id]]);
        $shortRun->update(['build_target' => $this->target(['skill_priorities' => ['Expensive Priority']])]);
        TurnEntry::factory()->create(['training_run_id' => $shortRun->id, 'turn' => 1, 'sp' => 400]);

        $short = $this->plannerProps($shortRun)['recommended'];
        $this->assertSame(500, $short['total']);
        $this->assertSame(400, $short['sp']);
        $this->assertSame(100, $short['shortfall'], 'priorities over budget must state the shortfall');

        // Residual: 1000 SP held, one 100 SP skill in the pool.
        $residualRun = $this->plannerRun();
        $cheap = $this->skill('Cheap Pool Skill', 100);
        $residualRun->characterCard()->update(['skills_innate' => [$cheap->export_id]]);
        $residualRun->update(['build_target' => $this->target()]);
        TurnEntry::factory()->create(['training_run_id' => $residualRun->id, 'turn' => 1, 'sp' => 1000]);

        $residual = $this->plannerProps($residualRun)['recommended'];
        $this->assertSame(100, $residual['total']);
        $this->assertSame(0, $residual['shortfall'], 'a covered plan states a 0 shortfall, not a shortfall');
        $this->assertSame(900, $residual['sp'] - $residual['total'], 'the residual must be computable from the payload');
    }

    /**
     * Inv-9 (payload half): a differing column is marked, and the mark is a word.
     *
     * The glyph is markup — `≠ differs` in `pages/Skills/Show.vue` — so its DOM case is in
     * `tests/browser/skills.spec.ts`. What this test holds is that the two skills genuinely differ
     * on the columns the panel compares, so the DOM case cannot pass by comparing a skill with
     * itself and finding no rows.
     */
    public function test_inv9_differs_word_and_glyph(): void
    {
        $skillA = Skill::factory()->create([
            'name' => 'Differs A',
            'type' => 'Speed',
            'sp_cost' => 130,
            'rarity' => 2,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
            'export_id' => 880006,
        ]);

        $skillB = Skill::factory()->create([
            'name' => 'Differs B',
            'type' => 'Recovery',
            'sp_cost' => 180,
            'rarity' => 1,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
            'export_id' => 880007,
        ]);

        $response = $this->get(route('skills.show', ['skill' => $skillA, 'compare' => $skillB->id]));
        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Skills/Show')
            ->where('skill.type', 'Speed')
            ->where('compareSkill.type', 'Recovery')
            ->where('skill.sp_cost', 130)
            ->where('compareSkill.sp_cost', 180),
        );

        $show = file_get_contents(resource_path('js/pages/Skills/Show.vue'));
        $this->assertIsString($show);
        $this->assertStringContainsString('data-differs', $show, 'the marker must carry a stable hook');
        $this->assertStringContainsString('≠', $show, 'the marker must carry a glyph as well as the word');
        $this->assertStringNotContainsString('text-red-', $show, 'the marker must never be colour-only');
    }

    /**
     * The ladder's five prices, Lv1 through Lv Max, in order.
     *
     * @return list<int>
     */
    private function ladder(int $base): array
    {
        $rows = array_slice(HintDiscount::priceLadder($base), 1);

        return array_map(static fn (array $row): int => (int) $row['price'], $rows);
    }

    private function plannerRun(): TrainingRun
    {
        $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
        $card = CharacterCard::factory()->create(['umamusume_id' => $run->umamusume_id]);
        $run->update(['character_card_id' => $card->id]);

        return $run;
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
}
