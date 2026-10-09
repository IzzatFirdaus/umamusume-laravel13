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
 * states and their precedence, the Skill Point coverage warning, the race-fit cells and the reorder
 * write. Rendered copy, keyboard reorder and the 44px sweep are the browser spec's; the reasons a
 * figure is absent are asserted here beside the figure.
 *
 * The hint-discount ladder was removed on the owner's ruling (A5): `SkillSpendCoverageTest` owns
 * both the coverage arithmetic the ladder used to sit beside and the proof that no ladder survives
 * on the props, so the case that priced it is gone from this file rather than kept asserting a
 * feature that no longer ships.
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

    public function test_offers_the_three_acquisition_statuses_the_editor_renders(): void
    {
        // F2 (plan §9.6 ruling 5): the acquisition editor's status picker mirrors the record
        // screen's vocabulary rather than inventing one, so the planner ships the same three cases
        // and points its submit at the run-scoped sync route.
        $run = $this->plannerRun();

        $this->get(route('runs.skills.planner', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('acquisition_options', fn (Collection $options): bool => $options
                    ->pluck('value')
                    ->sort()
                    ->values()
                    ->all() === ['Acquired', 'Skipped', 'Suggested'])
                ->where('skills_sync_url', route('runs.skills.sync', $run)));
    }

    public function test_offers_each_catalogue_backed_skill_once_with_its_recorded_status_and_turn(): void
    {
        $run = $this->plannerRun();
        $first = $this->skill('Row Skill First', 100);
        $second = $this->skill('Row Skill Second', 100);
        $third = $this->skill('Row Skill Third', 100);

        $run->setSkillStatus($first, SkillAcquisition::Acquired, 9);
        $run->setSkillStatus($second, SkillAcquisition::Suggested);
        $run->setSkillStatus($third, SkillAcquisition::Suggested);

        $this->get(route('runs.skills.planner', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                // Every catalogue-backed row is offered exactly once, so the editor posts each
                // skill under its own id rather than duplicating one. A row with no catalogue id
                // (a priority the catalogue cannot name) is not offered, so it is filtered here.
                ->where('groups', fn (Collection $groups): bool => $groups
                    ->flatMap(static fn (array $group): Collection => collect($group['rows']))
                    ->filter(static fn (array $row): bool => $row['id'] !== null)
                    ->pluck('id')
                    ->unique()
                    ->count() === 3)
                // The recorded status and turn ride on the learned row the status editor reads them
                // from; the two Suggested rows still to learn sit under Available.
                ->where('groups.2.rows', fn (Collection $rows): bool => $rows->contains(
                    static fn (array $row): bool => $row['id'] === $first->id
                        && $row['recorded']['status'] === 'Acquired'
                        && $row['recorded']['turn'] === 9
                ))
                ->where('groups.1.rows', fn (Collection $rows): bool => $rows->count() === 2));
    }

    public function test_saves_several_skill_statuses_in_one_submit_through_the_planner_write(): void
    {
        $run = $this->plannerRun();
        $first = $this->skill('Row Skill First', 100);
        $second = $this->skill('Row Skill Second', 100);
        $third = $this->skill('Row Skill Third', 100);

        foreach ([$first, $second, $third] as $skill) {
            $run->setSkillStatus($skill, SkillAcquisition::Suggested);
        }

        $this->post(route('runs.skills.sync', $run), [
            'skills' => [
                0 => ['skill_id' => $first->id, 'status' => 'Acquired', 'turn_acquired' => 4],
                1 => ['skill_id' => $second->id, 'status' => 'Skipped', 'turn_acquired' => null],
            ],
        ])->assertSessionHasNoErrors();

        $run->refresh();

        expect($run->skills()->count())->toBe(3)
            ->and($run->skills->firstWhere('id', $first->id)->pivot->status)->toBe('Acquired')
            ->and($run->skills->firstWhere('id', $first->id)->pivot->turn_acquired)->toBe(4)
            ->and($run->skills->firstWhere('id', $second->id)->pivot->status)->toBe('Skipped')
            // Untouched by the submit: the third skill keeps the state the pre-populate gave it.
            ->and($run->skills->firstWhere('id', $third->id)->pivot->status)->toBe('Suggested');
    }

    public function test_accepts_a_submit_where_the_spare_row_was_left_empty(): void
    {
        // The form always ships one row nobody has filled in. If the validator treated that as a
        // missing skill_id, every honest save would fail on the row the Trainer did not use.
        $run = $this->plannerRun();
        $first = $this->skill('Row Skill First', 100);
        $second = $this->skill('Row Skill Second', 100);
        $third = $this->skill('Row Skill Third', 100);

        foreach ([$first, $second, $third] as $skill) {
            $run->setSkillStatus($skill, SkillAcquisition::Suggested);
        }

        $this->post(route('runs.skills.sync', $run), [
            'skills' => [
                0 => ['skill_id' => $first->id, 'status' => 'Acquired', 'turn_acquired' => 2],
                1 => ['skill_id' => '', 'status' => 'Suggested', 'turn_acquired' => ''],
            ],
        ])->assertSessionHasNoErrors();

        expect($run->refresh()->skills()->count())->toBe(3)
            ->and($run->skills->firstWhere('id', $first->id)->pivot->turn_acquired)->toBe(2);
    }

    public function test_adds_a_skill_through_the_spare_row_without_disturbing_the_rows_above(): void
    {
        $run = $this->plannerRun();
        $first = $this->skill('Row Skill First', 100);
        $second = $this->skill('Row Skill Second', 100);
        $third = $this->skill('Row Skill Third', 100);

        foreach ([$first, $second, $third] as $skill) {
            $run->setSkillStatus($skill, SkillAcquisition::Suggested);
        }

        // A brand new Global skill, not yet on the run, to be added through the spare row.
        $extra = $this->skill('Added Through Spare', 100);

        $this->post(route('runs.skills.sync', $run), [
            'skills' => [
                0 => ['skill_id' => $first->id, 'status' => 'Suggested', 'turn_acquired' => null],
                1 => ['skill_id' => $second->id, 'status' => 'Suggested', 'turn_acquired' => null],
                2 => ['skill_id' => $third->id, 'status' => 'Acquired', 'turn_acquired' => 11],
                3 => ['skill_id' => $extra->id, 'status' => 'Suggested', 'turn_acquired' => null],
            ],
        ])->assertSessionHasNoErrors();

        $run->refresh();

        expect($run->skills()->count())->toBe(4)
            ->and($run->skills->pluck('id')->all())->toBe($run->skills->pluck('id')->unique()->all())
            ->and($run->skills->firstWhere('id', $extra->id)->pivot->status)->toBe('Suggested')
            ->and($run->skills->firstWhere('id', $third->id)->pivot->turn_acquired)->toBe(11);
    }

    public function test_names_the_refused_row_in_the_error_envelope_and_half_writes_nothing(): void
    {
        // Row 0 is edited to a different skill and status, row 1 carries the refusal (`min:1`
        // refuses a turn of 0), so the rejection is genuine and row 0's edits are the input at
        // risk. The Referer is sent because a real browser sends one from the planner page and
        // `back()` has no other way to find it; one round trip with `followingRedirects()`, because
        // that is the only way the error bag reaches the page the redirect lands on.
        $run = $this->plannerRun();
        $first = $this->skill('Row Skill First', 100);
        $second = $this->skill('Row Skill Second', 100);
        $third = $this->skill('Row Skill Third', 100);

        foreach ([$first, $second, $third] as $skill) {
            $run->setSkillStatus($skill, SkillAcquisition::Suggested);
        }

        $response = $this->followingRedirects()
            ->post(
                route('runs.skills.sync', $run),
                [
                    'skills' => [
                        0 => ['skill_id' => $third->id, 'status' => 'Acquired', 'turn_acquired' => 17],
                        1 => ['skill_id' => $first->id, 'status' => 'Suggested', 'turn_acquired' => 0],
                        2 => ['skill_id' => $second->id, 'status' => 'Suggested', 'turn_acquired' => null],
                    ],
                ],
                ['HTTP_REFERER' => route('runs.skills.planner', $run)],
            );

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Career/SkillsPlanner')
                // The envelope is keyed by the field name verbatim, which is the key the editor
                // reads back; nothing re-indexes because no row was empty.
                ->where('errors', fn (Collection $errors): bool => str_contains($errors['skills.1.turn_acquired'] ?? '', 'at least 1')));

        // The refusal must not have half-written either: the row the edit was aimed at still holds
        // what the pre-populate gave it, because validation runs before the controller body.
        $run->refresh();

        expect($run->skills->firstWhere('id', $first->id)->pivot->status)->toBe('Suggested')
            ->and($run->skills->firstWhere('id', $first->id)->pivot->turn_acquired)->toBeNull();
    }

    public function test_marks_a_unique_skill_and_states_its_cost_in_the_recorded_groups(): void
    {
        // Ported from the retired `Runs/Show` `skillGroups` (F2, plan §9.6 ruling 5): the four state
        // groups moved to the planner, where a row carries the same `is_unique`, `sp_cost` and
        // recorded status/turn the run screen read from its own groups.
        $run = $this->plannerRun();
        $unique = $this->skill('Certain Victory', null, isUnique: true);
        $priced = $this->skill('Gourmand', 180);

        $run->setSkillStatus($unique, SkillAcquisition::Acquired, 12);
        $run->setSkillStatus($priced, SkillAcquisition::Suggested);

        $this->get(route('runs.skills.planner', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Career/SkillsPlanner')
                // Learned holds the outcome: the unique mark and the turn the run recorded.
                ->where('groups.2.rows.0.name', 'Certain Victory')
                ->where('groups.2.rows.0.is_unique', true)
                ->where('groups.2.rows.0.recorded.status', 'Acquired')
                ->where('groups.2.rows.0.recorded.turn', 12)
                // Available holds the skill still to learn, priced where the row states it.
                ->where('groups.1.rows.0.name', 'Gourmand')
                ->where('groups.1.rows.0.sp_cost', 180));
    }

    public function test_offers_only_client_named_global_skills_and_states_what_it_cannot_offer(): void
    {
        // Ported from the retired `Runs/Show` `skillCatalog` (F2, plan §9.6 ruling 5). The planner
        // renders no full catalogue: the status surface offers only rows the `availableOnGlobal`
        // scope resolves, and a priority that scope cannot name keeps its row with the figures
        // stated as the absence they are. The write gate is that same scope.
        $run = $this->plannerRun();
        $offered = $this->skill('Gourmand', 180);

        // A client-shaped English name on a row the server has not shipped is not client copy
        // (ADR-0011 §2), so the surface must not offer it.
        $unoffered = Skill::factory()->create([
            'name' => 'Off Global',
            'sp_cost' => 180,
            'release_status' => ReleaseStatus::GlobalAnnounced,
            'name_is_client' => true,
        ]);

        $run->update(['build_target' => $this->target([
            'skill_priorities' => ['Gourmand', 'Off Global'],
        ])]);

        $this->get(route('runs.skills.planner', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                // The Global row is offered, priced where the row states it.
                ->where('groups.0.rows.0.name', 'Gourmand')
                ->where('groups.0.rows.0.id', $offered->id)
                ->where('groups.0.rows.0.sp_cost', 180)
                // The row the scope cannot name is kept and states the absence, not a price.
                ->where('groups.0.rows.1.name', 'Off Global')
                ->where('groups.0.rows.1.id', null)
                ->where('groups.0.rows.1.sp_cost', null));

        // The same scope gates the write: an id the surface cannot offer is refused.
        $this->post(route('runs.skills.sync', $run), [
            'skills' => [
                0 => ['skill_id' => $offered->id, 'status' => 'Suggested', 'turn_acquired' => null],
                1 => ['skill_id' => $unoffered->id, 'status' => 'Suggested', 'turn_acquired' => null],
            ],
        ])->assertSessionHasErrors('skills.1.skill_id');
    }

    private function plannerRun(): TrainingRun
    {
        return TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    }

    private function skill(string $name, ?int $sp, ?array $conditionGroups = null, bool $isUnique = false): Skill
    {
        return Skill::factory()->create([
            'name' => $name,
            'sp_cost' => $sp,
            'condition_groups' => $conditionGroups,
            'is_unique' => $isUnique,
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
