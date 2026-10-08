<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ReleaseStatus;
use App\Enums\SkillAcquisition;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * The Skills Planner's resolved props (plan §8 D13, `Career/SkillsPlanner.vue`). The four skill
 * states and their precedence, the Skill Point coverage warning, the sourced hint-discount ladder,
 * the race-fit cells and the reorder write. Rendered copy, keyboard reorder and the 44px sweep are
 * the browser spec's; the reasons a figure is absent are asserted here beside the figure.
 *
 * Top-level helpers carry the file's own prefix on purpose: a bare `eventRun()` in another slice's
 * untracked test file collides with the same name in `TurnEventTypePayloadsTest` and aborts the
 * whole suite at load time, so nothing here declares an unprefixed global function.
 */
final class CareerSkillsPlannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_names_the_four_states_and_where_each_is_read_from(): void
    {
        $run = $this->plannerRun();
        $withPivot = $this->skill('Required With Pivot', 100);
        $withoutPivot = $this->skill('Required No Pivot', 100);
        $markedOnly = $this->skill('Marked Only', 100);
        $learned = $this->skill('Wanted And Learned', 100);
        $skipped = $this->skill('Skipped Aside', 100);

        $run->update(['build_target' => $this->target([
            'skill_priorities' => ['Required With Pivot', 'Required No Pivot', 'Wanted And Learned'],
        ])]);
        $run->setSkillStatus($withPivot, SkillAcquisition::Suggested);
        $run->setSkillStatus($markedOnly, SkillAcquisition::Suggested);
        $run->setSkillStatus($learned, SkillAcquisition::Acquired, 7);
        $run->setSkillStatus($skipped, SkillAcquisition::Skipped);

        $this->get(route('runs.skills.planner', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Career/SkillsPlanner')
                // Required is the priority list's own order, not the catalogue's.
                ->where('groups.0.key', 'required')
                ->where('groups.0.label', 'Required')
                ->where('groups.0.rows.0.name', 'Required With Pivot')
                ->where('groups.0.rows.0.recorded.status', 'Suggested')
                ->where('groups.0.rows.1.name', 'Required No Pivot')
                ->where('groups.0.rows.1.recorded', null)
                // Learned wins over Required: the outcome is decisive, and a learned skill no
                // longer costs anything.
                ->where('groups.0.rows', fn (Collection $rows): bool => ! $rows->pluck('name')->contains('Wanted And Learned'))
                ->where('groups.1.key', 'available')
                ->where('groups.1.rows.0.name', 'Marked Only')
                ->where('groups.2.key', 'learned')
                ->where('groups.2.rows.0.name', 'Wanted And Learned')
                ->where('groups.2.rows.0.recorded.turn', 7)
                // No column holds the skills a Legacy configuration passes down, so the state
                // exists in the vocabulary and its list is the named absence.
                ->where('groups.3.key', 'inherited')
                ->where('groups.3.rows', [])
                ->where('groups.3.absent', fn (string $absent): bool => $absent !== '')
                // A Skipped row no priority names belongs to the run screen's own list. No type
                // hints on these closures: Inertia's macro hands nested values back as arrays
                // and Collections alternating by depth, and a hint fights the messenger.
                ->where('groups', fn ($groups): bool => ! $groups
                    ->flatMap(static fn ($group) => collect($group['rows']))
                    ->pluck('name')
                    ->contains('Skipped Aside'))
                // The one write: reordering persists through the run-scoped build target write.
                ->where('target.save_url', route('runs.build-target.update', $run)));
    }

    public function test_warns_when_the_skills_still_to_learn_cost_more_than_the_run_holds(): void
    {
        $run = $this->plannerRun();
        $high = $this->skill('Priced High', 200);
        $higher = $this->skill('Priced Higher', 160);
        $learned = $this->skill('Already Learned', 100);

        $run->update(['build_target' => $this->target([
            'skill_priorities' => ['Priced High', 'Priced Higher', 'Already Learned'],
        ])]);
        $run->setSkillStatus($learned, SkillAcquisition::Acquired, 3);
        TurnEntry::factory()->for($run)->create(['turn' => 12, 'sp' => 300]);

        $this->get(route('runs.skills.planner', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                // The learned skill is excluded: the money is spent, the sum covers what is left.
                ->where('coverage.total', 360)
                ->where('coverage.sp', 300)
                ->where('coverage.warn', true)
                ->where('coverage.text', fn (string $text): bool => str_contains($text, '360')
                    && str_contains($text, '300')));
    }

    public function test_does_not_warn_while_the_recorded_skill_points_cover_the_base_prices(): void
    {
        $run = $this->plannerRun();
        $first = $this->skill('Priced High', 200);
        $second = $this->skill('Priced Higher', 160);

        $run->update(['build_target' => $this->target([
            'skill_priorities' => ['Priced High', 'Priced Higher'],
        ])]);
        TurnEntry::factory()->for($run)->create(['turn' => 12, 'sp' => 400]);

        $this->get(route('runs.skills.planner', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('coverage.total', 360)
                ->where('coverage.sp', 400)
                ->where('coverage.warn', false));
    }

    public function test_renders_the_skill_point_total_as_na_while_no_turn_has_been_logged(): void
    {
        $run = $this->plannerRun();
        $run->update(['build_target' => $this->target(['skill_priorities' => ['Priced High']])]);
        $this->skill('Priced High', 200);

        $this->get(route('runs.skills.planner', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('coverage.sp', null)
                ->where('coverage.sp_title', fn (?string $title): bool => $title !== null && $title !== '')
                ->where('coverage.total', 200)
                ->where('coverage.warn', false));
    }

    public function test_cannot_state_the_required_total_when_a_priority_skill_carries_no_price(): void
    {
        $run = $this->plannerRun();
        $run->update(['build_target' => $this->target(['skill_priorities' => ['Unpriced Skill', 'Priced High']])]);
        $this->skill('Unpriced Skill', null);
        $this->skill('Priced High', 200);

        $this->get(route('runs.skills.planner', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                // A missing price is not a zero: the sum is refused, never silently shortened.
                ->where('coverage.total', null)
                ->where('coverage.total_title', fn (?string $title): bool => (string) $title !== ''
                    && str_contains((string) $title, 'Unpriced Skill')));
    }

    public function test_prices_the_hint_ladder_off_the_sourced_percentages_floored(): void
    {
        $run = $this->plannerRun();
        $run->update(['build_target' => $this->target(['skill_priorities' => ['Priced Skill']])]);
        $this->skill('Priced Skill', 200);

        $this->get(route('runs.skills.planner', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                // The ladder is the client's own caption set (REFERENCE §1.1.4); the displayed
                // cost is base x (1 - discount) floored, which 200 reconciles at every level.
                ->where('ladder', [
                    ['level' => 'Hint Lvl 1', 'percent' => 10],
                    ['level' => 'Hint Lvl 2', 'percent' => 20],
                    ['level' => 'Hint Lvl 3', 'percent' => 30],
                    ['level' => 'Hint Lvl 4', 'percent' => 35],
                    ['level' => 'Hint Lvl Max', 'percent' => 40],
                ])
                ->where('groups.0.rows.0.costs', [
                    ['level' => 'Hint Lvl 1', 'percent' => 10, 'cost' => 180],
                    ['level' => 'Hint Lvl 2', 'percent' => 20, 'cost' => 160],
                    ['level' => 'Hint Lvl 3', 'percent' => 30, 'cost' => 140],
                    ['level' => 'Hint Lvl 4', 'percent' => 35, 'cost' => 130],
                    ['level' => 'Hint Lvl Max', 'percent' => 40, 'cost' => 120],
                ]));
    }

    public function test_compares_only_the_dimensions_a_source_lets_it_compare(): void
    {
        $run = $this->plannerRun();
        $fitSkill = $this->skill('Constrained Skill', 100, [
            ['base_time' => 30000, 'condition' => 'distance_type==3&ground_type==1&running_style==2&phase>=2&weather==3', 'precondition' => null, 'effects' => []],
        ]);

        $run->update(['build_target' => $this->target()]);
        $run->setSkillStatus($fitSkill, SkillAcquisition::Suggested);

        $this->get(route('runs.skills.planner', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('groups.1.rows.0.conditions', 'distance_type==3&ground_type==1&running_style==2&phase>=2&weather==3')
                // Distance and surface: both number maps are sourced in REFERENCE §1.2, so every
                // evaluated cell, match or mismatch, cites the map's home.
                ->where('groups.1.rows.0.fit.0', fn ($cell): bool => $cell['state'] === 'matches'
                    && str_contains((string) $cell['title'], 'REFERENCE'))
                ->where('groups.1.rows.0.fit.1', fn ($cell): bool => $cell['state'] === 'matches'
                    && str_contains((string) $cell['title'], 'REFERENCE'))
                // Style: the condition records a number, but no sourced map turns it into the
                // client's labels, so no comparison is claimed (PRD OQ-5).
                ->where('groups.1.rows.0.fit.2', fn ($cell): bool => $cell['state'] === 'unrecorded'
                    && str_contains((string) $cell['title'], 'OQ-5'))
                ->where('groups.1.rows.0.fit', fn (Collection $fit): bool => $fit->pluck('key')->all() === [
                    'distance', 'surface', 'style', 'course', 'weather', 'ground', 'phase',
                ])
                ->where('groups.1.rows.0.fit.5', fn ($cell): bool => $cell['state'] === 'unrecorded'
                    && str_contains((string) $cell['title'], 'build target')));
    }

    public function test_says_does_not_match_when_the_constraint_and_the_target_disagree(): void
    {
        $run = $this->plannerRun();
        $sprintSkill = $this->skill('Sprint Only Skill', 100, [
            ['base_time' => 30000, 'condition' => 'distance_type==1', 'precondition' => null, 'effects' => []],
        ]);

        $run->update(['build_target' => $this->target()]);
        $run->setSkillStatus($sprintSkill, SkillAcquisition::Suggested);

        $this->get(route('runs.skills.planner', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('groups.1.rows.0.fit.0', fn ($cell): bool => $cell['state'] === 'mismatch'
                    && str_contains((string) $cell['title'], 'REFERENCE')));
    }

    public function test_keeps_a_priority_the_catalogue_has_no_row_for_and_states_it(): void
    {
        $run = $this->plannerRun();
        $run->update(['build_target' => $this->target(['skill_priorities' => ['Vanished Skill Name']])]);

        $this->get(route('runs.skills.planner', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('groups.0.rows.0.name', 'Vanished Skill Name')
                ->where('groups.0.rows.0.id', null)
                ->where('groups.0.rows.0.sp_cost', null)
                ->where('groups.0.rows.0.conditions', null)
                ->where('groups.0.rows.0.fit', fn (Collection $fit): bool => $fit->count() === 7
                    && $fit->pluck('state')->unique()->all() === ['unrecorded']));
    }

    public function test_renders_the_no_target_state_for_a_run_with_none(): void
    {
        $run = $this->plannerRun();

        $this->get(route('runs.skills.planner', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('target', null)
                ->where('groups.0.rows', [])
                ->where('groups.0.absent', fn (string $absent): bool => str_contains($absent, 'build target')));
    }

    public function test_saves_a_reordered_priority_list_through_the_run_scoped_build_target_write(): void
    {
        $run = $this->plannerRun();
        $run->update(['build_target' => $this->target(['skill_priorities' => ['First Skill', 'Second Skill']])]);

        $this->from(route('runs.skills.planner', $run))
            ->put(route('runs.build-target.update', $run), $this->target([
                'skill_priorities' => ['Second Skill', 'First Skill'],
            ]))
            ->assertRedirect(route('runs.skills.planner', $run));

        expect($run->fresh()->buildTarget()?->skillPriorities)->toBe(['Second Skill', 'First Skill']);
    }

    public function test_or_merged_distance_atoms_match_both_medium_and_long_targets(): void
    {
        // A skill whose condition groups contain distance_type==3 @ distance_type==4 (Medium or Long)
        // must render "Matches" for either target, not drop one disjunct. Regression for the
        // last-wins bug where `$atoms[$key] = $value` kept only the final group's value.
        $skill = $this->skill('Dual Distance Skill', 500, [
            [
                'precondition' => '',
                'base_time' => 50000,
                'condition' => 'distance_type==3',
                'effects' => [['type' => 27, 'value' => 100]],
            ],
            [
                'precondition' => '',
                'base_time' => 50000,
                'condition' => 'distance_type==4',
                'effects' => [['type' => 27, 'value' => 100]],
            ],
        ]);

        $run = $this->plannerRun();
        $run->skills()->attach($skill);
        $run->update(['build_target' => $this->target(['distance' => 'Medium'])]);

        $response = $this->get(route('runs.skills.planner', $run));
        $response->assertOk();

        // Verify the skill appears in Available group and its distance fit cell matches Medium (3)
        $response->assertInertia(fn (Assert $page) => $page
            ->where('groups.1.rows.0.name', 'Dual Distance Skill')
            ->where('groups.1.rows.0.fit', fn (Collection $fit): bool => count($fit) === 7 && $fit[0]['state'] === 'matches'));

        // Same skill against Long target — still matches via the second disjunct (4)
        $run->update(['build_target' => $this->target(['distance' => 'Long'])]);

        $this->get(route('runs.skills.planner', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('groups.1.rows.0.fit', fn (Collection $fit): bool => count($fit) === 7 && $fit[0]['state'] === 'matches'));
    }

    private function plannerRun(): TrainingRun
    {
        return TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    }

    private function skill(string $name, ?int $sp, ?array $conditionGroups = null): Skill
    {
        return Skill::factory()->create([
            'name' => $name,
            'sp_cost' => $sp,
            'condition_groups' => $conditionGroups,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
        ]);
    }

    /**
     * A complete build target, the shape `StoreBuildTargetRequest::payload()` writes.
     *
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
