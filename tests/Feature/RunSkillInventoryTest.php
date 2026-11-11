<?php

declare(strict_types=1);

/*
 * The Skills Planner's catalogue picker (Slice 10): the skills a Trainer can add to a run's own
 * inventory. Before this slice the planner could only edit the status of rows already in one of its
 * four groups, so a run with no build target and no rows had no way to gain its first skill — the
 * workaround is recorded in `tests/browser/support/riceShowerHelpers.ts:12-14`, which posts through
 * the sync endpoint by hand for exactly that reason.
 *
 * What is asserted here is the picker's own contract. The write path itself is already pinned in
 * `CareerSkillsPlannerTest` and is not duplicated: adding through the spare row (`:468`), the
 * duplicate-uniqueness case, and the refusal of a skill the Global scope does not offer (`:604`).
 *
 * Two shapes this file records rather than assumes. The three-state vocabulary is authoritative (the
 * owner ruled against the five-state set), so the enum is pinned here as the boundary a future change
 * would have to break. And a refusal on these web routes is a redirect carrying session errors, not a
 * 422: `bootstrap/app.php` renders JSON only for `api/*` or an `Accept: application/json` request.
 *
 * Top-level helpers carry this file's own prefix, per `CareerSkillsPlannerTest`'s docblock: an
 * unprefixed global function collides with another slice's and aborts the suite at load time.
 */

use App\Enums\ReleaseStatus;
use App\Enums\SkillAcquisition;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Inertia\Testing\AssertableInertia as Assert;

function inventoryRun(): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => 'unity_cup']);
}

function inventorySkill(string $name, ReleaseStatus $release = ReleaseStatus::GlobalReleased): Skill
{
    return Skill::factory()->create([
        'name' => $name,
        'sp_cost' => 100,
        'release_status' => $release,
        'name_is_client' => true,
    ]);
}

it('offers the catalogue skills a Global Trainer can meet and that the run does not already hold', function (): void {
    $run = inventoryRun();
    $alreadyOnRun = inventorySkill('Already Marked');
    inventorySkill('Not Yet Marked');
    inventorySkill('Withdrawn Skill', ReleaseStatus::JapanOnly);

    $run->setSkillStatus($alreadyOnRun, SkillAcquisition::Suggested);

    $this->get(route('runs.skills.planner', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('picker_skills', fn ($rows): bool => collect($rows)->pluck('name')->contains('Not Yet Marked'))
            // On the run already, so adding it again would be the duplicate the composite key refuses.
            ->where('picker_skills', fn ($rows): bool => ! collect($rows)->pluck('name')->contains('Already Marked'))
            // Not offered to a Global Trainer anywhere else on this screen, so not offered here either.
            ->where('picker_skills', fn ($rows): bool => ! collect($rows)->pluck('name')->contains('Withdrawn Skill')));
});

it('offers the whole eligible catalogue on a run whose inventory is empty', function (): void {
    $run = inventoryRun();
    inventorySkill('First Eligible');
    inventorySkill('Second Eligible');

    $this->get(route('runs.skills.planner', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('picker_skills', fn ($rows): bool => collect($rows)->pluck('name')->sort()->values()->all()
                === ['First Eligible', 'Second Eligible'])
            // Nothing is on the run, so the group the added row would land in starts empty and states so.
            ->where('groups.1.key', 'available')
            ->where('groups.1.rows', []));
});

it('carries an id and a name per offered skill, which is all the picker posts with', function (): void {
    $run = inventoryRun();
    $skill = inventorySkill('Named For The Picker');

    $this->get(route('runs.skills.planner', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('picker_skills', function ($rows) use ($skill): bool {
                $row = collect($rows)->firstWhere('id', $skill->id);

                return $row !== null && $row['name'] === 'Named For The Picker';
            }));
});

it('writes only the run its route names, leaving a second run untouched', function (): void {
    $run = inventoryRun();
    $other = inventoryRun();
    $skill = inventorySkill('Shared Catalogue Skill');

    $other->setSkillStatus($skill, SkillAcquisition::Suggested);

    // A catalogue skill on another run is still a catalogue skill: it stays eligible here, because the
    // filter is this run's own rows and not any run's.
    $this->get(route('runs.skills.planner', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('picker_skills', fn ($rows): bool => collect($rows)->pluck('name')->contains('Shared Catalogue Skill')));

    $this->post(route('runs.skills.sync', $run), [
        'skills' => [['skill_id' => $skill->id, 'status' => 'Suggested', 'turn_acquired' => null]],
    ])->assertSessionHasNoErrors();

    expect($run->refresh()->skills()->count())->toBe(1)
        ->and($other->refresh()->skills()->count())->toBe(1);
});

it('refuses a state outside the three-state vocabulary', function (): void {
    $run = inventoryRun();
    $skill = inventorySkill('Candidate Skill');

    // `owned` is one of the five states that were proposed and not authorised. The endpoint takes the
    // shipped three and nothing else.
    $this->post(route('runs.skills.sync', $run), [
        'skills' => [['skill_id' => $skill->id, 'status' => 'owned', 'turn_acquired' => null]],
    ])->assertSessionHasErrors('skills.0.status');

    expect($run->refresh()->skills()->count())->toBe(0);
});

it('pins the acquisition vocabulary to the three states that ship', function (): void {
    expect(array_map(static fn (SkillAcquisition $case): string => $case->value, SkillAcquisition::cases()))
        ->toBe(['Suggested', 'Acquired', 'Skipped']);
});

/*
 * UX-18a (P1 = planning state), the server half. The planner computes and gates its plan client-side,
 * but the write is the authority on whether the plan fits the run's recorded Skill Points, exactly as
 * the audit's budget-guard acceptance asks. These cases pin the three behaviours the guard owns:
 * refusal with both numbers named, Suggested rows costing nothing, and a run with no recorded SP
 * refusing nothing (the page already prints that absence rather than a zero).
 */
it('refuses an over-budget plan with both numbers named and writes no row', function (): void {
    $run = inventoryRun();
    $skill = inventorySkill('Over Budget Skill');

    TurnEntry::factory()->create(['training_run_id' => $run->id, 'sp' => 99]);

    $this->post(route('runs.skills.sync', $run), [
        'skills' => [['skill_id' => $skill->id, 'status' => 'Acquired', 'turn_acquired' => 12]],
    ]);

    // The default bag: the planner's picker reads any error whose key starts with `skills`, so the
    // refusal reaches the Trainer beside the control they pressed, not on a second surface. The test
    // session stores the bag's own array under its name (`default`).
    /** @var array<string, mixed> $errors */
    $errors = session('errors');

    expect($errors)->not->toBeNull()
        ->and((string) $errors['default']['messages']['skills'][0])
        ->toBe("The plan exceeds this run's Skill Points: 100 planned against 99 recorded.");

    // The refusal left the inventory untouched: the skill was never attached.
    expect($run->refresh()->skills()->count())->toBe(0);
});

it('accepts a plan the recorded Skill Points cover, taking the hint discount the planner prices', function (): void {
    $run = inventoryRun();
    $skill = inventorySkill('Hinted Skill');

    TurnEntry::factory()->create(['training_run_id' => $run->id, 'sp' => 80]);

    // Hint level 3 prices the skill's 100-sp base at 70 (`HintDiscount::price` floor), the same
    // figure the planner's Cost column prints, so the bar and the refusal quote one number.
    $this->post(route('runs.skills.sync', $run), [
        'skills' => [['skill_id' => $skill->id, 'status' => 'Acquired', 'turn_acquired' => 12, 'hint_level' => 3]],
    ])->assertSessionHasNoErrors();

    expect($run->refresh()->skills()->count())->toBe(1);
});

it('prices a Suggested row at nothing, because the client charges nothing for a plan', function (): void {
    $run = inventoryRun();
    $skill = inventorySkill('Planned Only Skill');

    // Ten sp recorded, a 100-sp skill marked Suggested: the write takes it, because the plan is the
    // state P1 approved and the client's own bar spends nothing on it.
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'sp' => 10]);

    $this->post(route('runs.skills.sync', $run), [
        'skills' => [['skill_id' => $skill->id, 'status' => 'Suggested', 'turn_acquired' => null]],
    ])->assertSessionHasNoErrors();

    expect($run->refresh()->skills()->count())->toBe(1);
});

it('refuses nothing on a run with no recorded Skill Points, naming no invented budget', function (): void {
    $run = inventoryRun();
    $skill = inventorySkill('No Budget Yet Skill');

    $this->post(route('runs.skills.sync', $run), [
        'skills' => [['skill_id' => $skill->id, 'status' => 'Acquired', 'turn_acquired' => 1]],
    ])->assertSessionHasNoErrors();

    expect($run->refresh()->skills()->count())->toBe(1);
});
