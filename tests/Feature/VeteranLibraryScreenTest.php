<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use App\Models\Veteran;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The Veteran library screens (`SCREEN-021`, PRD FR-G-2, plan §8 D16's read half).
 *
 * `tests/Feature/VeteranLibraryTest.php` proves `ListVeterans` and `RecordVeteran` answer their own
 * queries. This file is about the two surfaces that read them, and the props contract they hand the
 * pages: the empty state, the facets the query can actually answer, the order toggle, and the row shape
 * `App\Services\Legacy\VeteranRow` owns.
 */

/**
 * One filed career, built through the run it points at.
 *
 * The run is written first and the Veteran points at it, which is the only direction the schema admits:
 * `Veteran` holds a `training_run_id` and nothing else of the career (`Veteran`'s docblock). A
 * factory-built `Veteran` already carries a Completed run, so passing no run means "any trainee, no
 * scenario".
 */
function veteranScreenFiled(?Umamusume $trainee = null, ?string $scenario = null, array $tags = [], ?string $notes = null): Veteran
{
    $run = TrainingRun::factory()->create(array_filter([
        'umamusume_id' => $trainee?->id,
        'scenario' => $scenario,
        'status' => RunStatus::Completed,
    ], static fn (mixed $value): bool => $value !== null));

    return Veteran::factory()->create([
        'training_run_id' => $run->id,
        'tags' => $tags,
        'notes' => $notes,
    ]);
}

it('shows an empty library as nothing filed yet, with the reason on the page', function (): void {
    // A fresh install lands here, and the plan gates Save Veteran behind D15, so the empty state is the
    // state most Trainers meet. It is not an error and it does not pretend to be a filter result.
    test()->get(route('veterans.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Veterans/Index')
            ->where('veterans.data', [])
            ->where('totalCount', 0)
            ->where('order', 'newest')
            ->has('filingNotice')
            ->has('notice')
            ->has('absences', 4)
            ->where('searchAction', route('veterans.index')));
});

it('names each thing the library cannot do instead of offering an empty control', function (): void {
    // SCREEN-021 asks for favorite, archive and delete, and for sorts by Spark quality, skill coverage
    // and overall usefulness. None is answerable from a column, so the page says which is missing and why
    // rather than printing a control that returns everything (AGENTS.md §13).
    test()->get(route('veterans.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('absences.0.label')
            ->has('absences.0.reason')
            ->where('absences.0.label', 'Favorite, archive, delete'));
});

it('prints one filed career through the row the Legacy Lab prints', function (): void {
    // The reason `VeteranRow` exists: the two lists read one shape, so a Veteran cannot mean one thing on
    // the library and another thing on the browse surface. The shared keys are asserted value by value.
    $veteran = veteranScreenFiled(
        Umamusume::factory()->create(['name' => 'Measured March']),
        'ura_finale',
        ['Mile', 'Speed'],
        'Ran the 2026 spring schedule.',
    );

    $library = test()->get(route('veterans.index'))->assertOk()->inertiaProps('veterans.data');
    $legacy = test()->get(route('legacy.index'))->assertOk()->inertiaProps('veterans.data');

    expect($library[0])->toMatchArray($legacy[0])
        ->and($library[0]['trainee'])->toBe('Measured March')
        ->and($library[0]['scenario_label'])->toBe('URA Finale')
        ->and($library[0]['status_label'])->toBe($veteran->trainingRun->status->label())
        ->and($library[0]['tags'])->toBe(['Mile', 'Speed'])
        ->and($library[0]['notes'])->toBe('Ran the 2026 spring schedule.')
        // The page's own Compare control is built from this key, so a row that loses it silently loses the
        // comparison entry rather than failing loudly.
        ->and($library[0]['run_id'])->toBe($veteran->training_run_id)
        ->and($library[0]['veteran_url'])->toBe(route('veterans.show', $veteran));
});

it('opens one Veteran with the career facts it was built from', function (): void {
    $trainee = Umamusume::factory()->create(['name' => 'Long Held Record']);
    $veteran = veteranScreenFiled($trainee, 'trackblazer', ['Long'], 'Filed after the December cup.');

    $run = $veteran->trainingRun;
    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'energy' => 62,
        'fans' => 209245,
    ]);

    test()->get(route('veterans.show', $veteran))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Veterans/Show')
            ->where('veteran.trainee', 'Long Held Record')
            ->where('veteran.scenario_label', 'Trackblazer')
            ->where('career.turns', 1)
            ->where('career.energy', 62)
            ->where('career.fans', 209245)
            ->where('counts.skills', 0)
            ->where('counts.races', 0)
            // No parents recorded: the page prints its named absence rather than a blank slot.
            ->where('parents.a', null)
            ->where('parents.b', null)
            ->where('runUrl', route('runs.show', $run))
            ->where('traineeUrl', route('catalog.show', $trainee->slug))
            ->has('absences', 4));
});

it('renders the unrecorded career figures as absent rather than as zero', function (): void {
    // A career that logged no turn has no Energy and no Fans. `0` would be a claim about the run, and the
    // page is required to say the figure is not there (D-220, AGENTS.md §5).
    $veteran = veteranScreenFiled();

    test()->get(route('veterans.show', $veteran))
        ->assertInertia(fn (Assert $page) => $page
            ->where('career.turns', 0)
            ->where('career.energy', null)
            ->where('career.fans', null));
});

it('filters the library by the three facets the query answers', function (): void {
    $march = Umamusume::factory()->create(['name' => 'March Of The Mile']);
    $other = Umamusume::factory()->create(['name' => 'Other One']);

    $kept = veteranScreenFiled($march, 'ura_finale', ['Mile']);
    veteranScreenFiled($other, 'trackblazer', ['Long']);

    test()->get(route('veterans.index', ['trainee' => $march->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('veterans.data', 1)
            ->where('veterans.data.0.id', $kept->id)
            ->where('filters.trainee', $march->id));

    test()->get(route('veterans.index', ['scenario' => 'trackblazer']))
        ->assertInertia(fn (Assert $page) => $page->has('veterans.data', 1));

    // The all-of rule is `ListVeterans`'s, and one submitted tag is one filter, so `Mile` keeps the row
    // tagged Mile and `Mud` keeps nothing.
    test()->get(route('veterans.index', ['tag' => 'Mile']))
        ->assertInertia(fn (Assert $page) => $page->has('veterans.data', 1));

    test()->get(route('veterans.index', ['tag' => 'Mud']))
        ->assertInertia(fn (Assert $page) => $page->has('veterans.data', 0));
});

it('refuses a scenario the config does not declare and sends the Trainer back to the library', function (): void {
    // An unknown facet is a refusal, not an ignored key: a list that looked filtered and was filtered by
    // nothing they typed is the defect `ADR-0018` closes for the catalog too.
    test()->get(route('veterans.index', ['scenario' => 'grand_masters']))
        ->assertRedirect(route('veterans.index'))
        ->assertSessionHasErrors('scenario');
});

it('refuses an order the action cannot apply', function (): void {
    test()->get(route('veterans.index', ['order' => 'spark_quality']))
        ->assertSessionHasErrors('order');
});

it('orders the library by its own records and not by a completion date it does not hold', function (): void {
    $first = veteranScreenFiled();
    $second = veteranScreenFiled();

    test()->get(route('veterans.index'))
        ->assertInertia(fn (Assert $page) => $page->where('order', 'newest'))
        ->inertiaProps('veterans.data');

    expect(array_column(test()->get(route('veterans.index'))->inertiaProps('veterans.data'), 'id'))
        ->toBe([$second->id, $first->id]);

    $oldest = test()->get(route('veterans.index', ['order' => 'oldest']))
        ->assertInertia(fn (Assert $page) => $page->where('order', 'oldest'))
        ->inertiaProps('veterans.data');

    expect(array_column($oldest, 'id'))->toBe([$first->id, $second->id]);
});

it('offers the builder only on a career that has a Legacy read-back', function (): void {
    // `legacySelection()` distinguishes "never opened that screen" from "opened it and recorded nothing",
    // and a builder link on the first is a dead end with a label on it.
    $bare = veteranScreenFiled();
    $readBack = veteranScreenFiled();

    $readBack->trainingRun->update([
        // The stored shape `LegacySelectionPayload` declares and refuses anything else about: two
        // legacies, each `rank, is_guest, ancestors, sparks`, plus the affinity grade. A hand-written
        // payload that misses a key throws on the way in, which is the point of the reader.
        'legacy_selection' => [
            'legacies' => [
                ['rank' => 3, 'is_guest' => false, 'ancestors' => [], 'sparks' => []],
                ['rank' => 2, 'is_guest' => true, 'ancestors' => [], 'sparks' => []],
            ],
            'affinity' => '○',
        ],
    ]);

    $rows = test()->get(route('veterans.index'))->inertiaProps('veterans.data');
    $byId = collect($rows)->keyBy('id');

    expect($byId[$bare->id]['hasSelection'])->toBeFalse()
        ->and($byId[$bare->id]['builder_url'])->toBeNull()
        ->and($byId[$readBack->id]['hasSelection'])->toBeTrue()
        ->and($byId[$readBack->id]['builder_url'])->toBe(route('legacy.builder', $readBack->training_run_id));
});
