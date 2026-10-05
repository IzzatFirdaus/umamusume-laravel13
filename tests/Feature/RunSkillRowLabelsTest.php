<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Enums\SkillAcquisition;
use App\Models\Skill;
use App\Models\TrainingRun;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * KI-36 and the repeater half of KI-33, both on the run screen's skills form.
 *
 * KI-36: one `<label>` wrapped three controls (`show.blade.php:445-461`), so the status `<select>`
 * and the turn `<input>` had no accessible name at all — a placeholder is not a name, it disappears
 * once the field has a value, and this one has a value whenever a Trainer is editing a turn.
 *
 * The repeater: the form hard-indexed `skills[0]` three times. One row, one submit, no way to add
 * another. That was harmless while the table held ten names and a run carried one or two skills; it
 * stopped being harmless the moment Slice A pre-populated four or five rows from a card, because the
 * list the pre-populate seeds is exactly the list a Trainer then cannot edit. KI-33 names this as a
 * required part of itself, not a follow-on, and both defects live in the same seventeen lines, which
 * is why they land together.
 *
 * The port moved the form into `Runs/Show.vue`. The label/`for`/`id` pairing and the control sizing
 * are measured against the live page now; what the server is asserted for here is the payload every
 * row is built from: one row per stored skill, the status it sits under, and the turn it was given.
 */

function labelledRun(): TrainingRun
{
    $run = TrainingRun::factory()->create();

    foreach ([200512, 201352, 200732] as $id) {
        Skill::create([
            'export_id' => $id,
            'name' => 'Row Skill '.$id,
            'match_key' => 'rowskill'.$id,
            'release_status' => ReleaseStatus::GlobalReleased->value,
            'name_is_client' => true,
        ]);
    }

    foreach (Skill::orderBy('export_id')->get() as $skill) {
        $run->setSkillStatus($skill, SkillAcquisition::Suggested);
    }

    return $run->refresh();
}

it('sends the three per-row values the skills form labels name', function (): void {
    $run = labelledRun();

    test()->get(route('runs.show', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            // KI-36: the label/`for`/`id` pairing is the component's markup, checked on the live page.
            // The server owes every row a pickable skill, a status and a turn for those three controls
            // to name, so none of them is rendered unlabelled against nothing.
            ->has('skillCatalog', 3)
            ->where('acquisitionOptions', fn (Collection $options): bool => $options->pluck('value')->sort()->values()->all() === ['Acquired', 'Skipped', 'Suggested'])
            ->where('skillGroups', function (Collection $groups): bool {
                $rows = $groups->flatMap(fn (array $group): array => $group['skills']);

                return $rows->count() === 3
                    && $rows->every(fn (array $row): bool => array_key_exists('turn_acquired', $row));
            }));
});

it('sends one editable row per skill on the run, each posting its own field', function (): void {
    $run = labelledRun();

    // Re-pointed 2026-10-03 (B.6): a closed row still posts its skill under its own field name, so the
    // row set is the union of the open picker and the closed rows. The claim is unchanged: one row per
    // seeded skill, no duplicates. The spare row is the component's own, so the browser spec counts it.
    test()->get(route('runs.show', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('skillGroups', function (Collection $groups): bool {
                $rows = $groups->flatMap(fn (array $group): array => $group['skills']);

                return $rows->count() === 3 && $rows->pluck('id')->unique()->count() === 3;
            }));
});

it('carries each existing skill under the status and turn the Trainer gave it', function (): void {
    $run = labelledRun();
    $first = Skill::orderBy('export_id')->first();
    $run->setSkillStatus($first, SkillAcquisition::Acquired, 9);

    // The status is the group a row sits in and the turn rides on the row itself, which is what the
    // status select and the turn input are built from.
    test()->get(route('runs.show', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('skillGroups', function (Collection $groups) use ($first): bool {
                $acquired = $groups->firstWhere('key', SkillAcquisition::Acquired->value);
                $suggested = $groups->firstWhere('key', SkillAcquisition::Suggested->value);

                return $acquired['skills'][0]['id'] === $first->id
                    && $acquired['skills'][0]['turn_acquired'] === 9
                    && count($suggested['skills']) === 2;
            }));
});

it('names the refused row in the error envelope and half-writes nothing', function (): void {
    // The last thing a Trainer does before hitting save is re-read a row they just fixed. When the
    // server refuses the submit for some other row, the re-rendered form used to hand back the
    // *stored* values instead, so the fix they just made vanished and the row they were re-reading
    // said the opposite. The deck form rehydrates from `old()` since D-3; this form's rows are the
    // component's own state, which is what keeps the typed edits, and the server's half is that the
    // refusal names the refused row and nothing half-writes.
    $run = labelledRun();
    $skills = Skill::orderBy('export_id')->get();

    // Every row from 0 up is filled, deliberately. `prepareForValidation` filters the unused rows
    // out and re-indexes with `array_values`, so a submit that skips a middle row validates one
    // index while the flashed old input keeps the index the Trainer sent, and the two stop
    // describing the same row. That misalignment is a separate defect from the one under test here,
    // so it is reported on its own rather than folded into this fix.
    //
    // Within the payload: row 0 is edited to a different skill, a different status, and a turn; row
    // 1 carries the refusal (`min:1` refuses a turn of 0), so the rejection is genuine and row 0's
    // edits are the input at risk. The Referer is sent because a real browser sends one from the
    // form page and `back()` has no other way to find the run. Nothing gets filtered, so the
    // re-index is a no-op and the error key and the old-input key are the same key.
    //
    // One round trip with `followingRedirects()`, because that is what the browser does and it is
    // the only way the flashed input and the error bag reach the page the redirect lands on.
    $response = $this->followingRedirects()
        ->post(
            route('runs.skills.sync', $run),
            [
                'skills' => [
                    0 => ['skill_id' => $skills[2]->id, 'status' => 'Acquired', 'turn_acquired' => 17],
                    1 => ['skill_id' => $skills[0]->id, 'status' => 'Suggested', 'turn_acquired' => 0],
                    2 => ['skill_id' => $skills[1]->id, 'status' => 'Suggested', 'turn_acquired' => null],
                ],
            ],
            ['HTTP_REFERER' => route('runs.show', $run)],
        );

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            // The refusal has to be visible on the row that caused it, or the Trainer is left staring
            // at a form that looks like it saved. The envelope is keyed by the field name verbatim,
            // which is the key the form reads back.
            ->where('errors', fn (Collection $errors): bool => str_contains($errors['skills.1.turn_acquired'] ?? '', 'at least 1')));

    // The refusal must not have half-written either: the row the edit was aimed at still holds what
    // the pre-populate gave it, because validation runs before the controller body.
    $run->refresh();

    expect($run->skills->firstWhere('id', $skills[0]->id)->pivot->status)->toBe('Suggested')
        ->and($run->skills->firstWhere('id', $skills[0]->id)->pivot->turn_acquired)->toBeNull();
});

it('saves several rows in one submit', function (): void {
    $run = labelledRun();
    $skills = Skill::orderBy('export_id')->get();

    $this->post(route('runs.skills.sync', $run), [
        'skills' => [
            0 => ['skill_id' => $skills[0]->id, 'status' => 'Acquired', 'turn_acquired' => 4],
            1 => ['skill_id' => $skills[1]->id, 'status' => 'Skipped', 'turn_acquired' => null],
        ],
    ])->assertSessionHasNoErrors();

    $run->refresh();

    expect($run->skills()->count())->toBe(3)
        ->and($run->skills->firstWhere('id', $skills[0]->id)->pivot->status)->toBe('Acquired')
        ->and($run->skills->firstWhere('id', $skills[0]->id)->pivot->turn_acquired)->toBe(4)
        ->and($run->skills->firstWhere('id', $skills[1]->id)->pivot->status)->toBe('Skipped')
        // Untouched by the submit: the third skill keeps the state the pre-populate gave it.
        ->and($run->skills->firstWhere('id', $skills[2]->id)->pivot->status)->toBe('Suggested');
});

it('accepts a submit where the spare row was left empty', function (): void {
    // The form always ships one row nobody has filled in. If the validator treated that as a
    // missing skill_id, every honest save would fail on the row the Trainer did not use.
    $run = labelledRun();
    $skills = Skill::orderBy('export_id')->get();

    $this->post(route('runs.skills.sync', $run), [
        'skills' => [
            0 => ['skill_id' => $skills[0]->id, 'status' => 'Acquired', 'turn_acquired' => 2],
            1 => ['skill_id' => '', 'status' => 'Suggested', 'turn_acquired' => ''],
        ],
    ])->assertSessionHasNoErrors();

    expect($run->refresh()->skills()->count())->toBe(3)
        ->and($run->skills->firstWhere('id', $skills[0]->id)->pivot->turn_acquired)->toBe(2);
});

it('adds a skill through the spare row without disturbing the rows above it', function (): void {
    $run = labelledRun();
    $skills = Skill::orderBy('export_id')->get();

    // A brand new Global skill, not yet on the run, to be added through the spare row.
    $extra = Skill::create([
        'export_id' => 202001,
        'name' => 'Added Through Spare',
        'match_key' => 'addedthroughspare',
        'release_status' => ReleaseStatus::GlobalReleased->value,
        'name_is_client' => true,
    ]);

    $this->post(route('runs.skills.sync', $run), [
        'skills' => [
            0 => ['skill_id' => $skills[0]->id, 'status' => 'Suggested', 'turn_acquired' => null],
            1 => ['skill_id' => $skills[1]->id, 'status' => 'Suggested', 'turn_acquired' => null],
            2 => ['skill_id' => $skills[2]->id, 'status' => 'Acquired', 'turn_acquired' => 11],
            3 => ['skill_id' => $extra->id, 'status' => 'Suggested', 'turn_acquired' => null],
        ],
    ])->assertSessionHasNoErrors();

    $run->refresh();

    expect($run->skills()->count())->toBe(4)
        ->and($run->skills->pluck('id')->all())->toBe($run->skills->pluck('id')->unique()->all())
        ->and($run->skills->firstWhere('id', $extra->id)->pivot->status)->toBe('Suggested')
        ->and($run->skills->firstWhere('id', $skills[2]->id)->pivot->turn_acquired)->toBe(11);
});

it('sends the non-empty control surface the 44px sweep measures', function (): void {
    // KI-37. The 44px sizing itself is a browser claim now: the component carries `h-11` on every
    // control and `min-h-11` on each "Change row" link, and only a rendered box can confirm what a
    // class does. What the server owes is that every control the sweep measures has its data: the
    // one picker's catalogue, the statuses its rows offer, and the rows themselves.
    $run = labelledRun();

    test()->get(route('runs.show', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->has('skillCatalog', 3)
            ->has('acquisitionOptions', 3)
            ->where('skillGroups', fn (Collection $groups): bool => $groups->sum(
                fn (array $group): int => count($group['skills'])
            ) === 3));
});

it('sends an empty catalogue when no skill is offerable, and the rows that keep the form submittable', function (): void {
    // KI-51's UI consequence, and the state this section had no copy for at all. On a fresh clone
    // `DatabaseSeeder` reaches `SkillSeeder`, which writes nine rows and sets neither `release_status` nor
    // `name_is_client` (its own docblock says the omission is deliberate), so the scope at
    // `Skill::scopeAvailableOnGlobal()` matches none of them and no tracked writer in this tree produces a
    // row this picker can offer. The empty picker is therefore the ordinary first state, not an edge case,
    // and the component names both catalogue commands for it; the copy is measured on the live page.
    $run = TrainingRun::factory()->create();

    test()->get(route('runs.show', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            // The state must not be a dead control: the groups report their absences rather than
            // offering nothing silently, and the form still renders a submittable row set.
            ->has('skillCatalog', 0)
            ->where('skillGroups', fn (Collection $groups): bool => $groups->every(
                fn (array $group): bool => $group['skills'] === [] && $group['absent'] === 'None.'
            )));
});
