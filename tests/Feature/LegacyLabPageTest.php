<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use App\Models\Veteran;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The Legacy Lab's three surfaces and its one write (SCREEN-006; PRD FR-G, ADR-0020 §3, ADR-0010).
 *
 * These assert the props the Inertia pages receive, not the markup they render: the pages are Vue
 * components now (ADR-0020 §1), so the server contract is the payload. The copy, the 44px targets, the
 * keyboard path and the compare alignment are asserted against the rendered DOM in
 * `tests/browser/legacy.spec.ts`.
 *
 * The cases below are the slice's acceptance: the filters C3 actually answers, the six-node graph with
 * its six names in the client's order, and the "Confirm Inheritance" write round-tripping through
 * `LegacySelectionPayload`. They also pin the record-only boundary as a *behaviour* rather than as a
 * comment, because that is the line this screen fails on if it is ever crossed: the probability and
 * affinity props are asserted to be null with a reason, so a future slice that turns one of them into
 * a number has to delete a test rather than slip past a code review.
 *
 * Rows come from factories, never the seeder, so each case names the data it asserts on.
 */

/**
 * A completed run recorded as a Veteran, with a selectable name.
 */
function legacyVeteran(string $name = 'Special Week'): Veteran
{
    return Veteran::factory()->create([
        'training_run_id' => TrainingRun::factory()->create([
            'umamusume_id' => Umamusume::factory()->create(['name' => $name])->id,
            'status' => RunStatus::Completed,
        ])->id,
    ]);
}

/**
 * The payload a Trainer records: two parents, four ancestors, one Spark each, one Affinity grade.
 *
 * @return array<string, mixed>
 */
function sixNodePayload(): array
{
    return [
        'legacies' => [
            [
                'rank' => 3,
                'is_guest' => false,
                'ancestors' => ['Grass Wonder', 'El Condor Pasa'],
                'sparks' => [
                    ['kind' => 'blue', 'target' => 'Speed', 'stars' => 2],
                    ['kind' => 'white', 'target' => null, 'stars' => 1],
                ],
            ],
            [
                'rank' => 1,
                'is_guest' => true,
                'ancestors' => ['Tokai Teio', 'Gold Ship'],
                'sparks' => [
                    ['kind' => 'pink', 'target' => 'Mile', 'stars' => 3],
                ],
            ],
        ],
        'affinity' => '◎',
    ];
}

/**
 * An `Active` run the builder will accept.
 */
function activeRun(string $name = 'Symboli Rudolf', array $attributes = []): TrainingRun
{
    return TrainingRun::factory()->create([
        'umamusume_id' => Umamusume::factory()->create(['name' => $name])->id,
        'status' => RunStatus::Active,
        ...$attributes,
    ]);
}

it('browses the Veteran library through the three filters C3 answers', function (): void {
    $mileSpeed = legacyVeteran('Special Week');
    $turfStamina = legacyVeteran('Nice Nature');

    Veteran::query()->update(['tags' => null]);

    $mileSpeed->update(['tags' => ['Mile', 'Turf', 'Speed']]);
    $turfStamina->update(['tags' => ['Long', 'Turf', 'Stamina']]);

    test()->get('/legacy')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Legacy/Index')
            ->count('veterans.data', 2)
            // The three facets are the three `ListVeterans` implements. `spark`, `style` and `rating`
            // are absent from the props on purpose: no query answers them (plan §7 C3), and a facet
            // that returns every row is a filter that does not filter.
            ->has('filters')
            ->has('scenarios')
            ->has('trainees')
            ->where('totalCount', 2)
            ->where('notice', 'Record only. This screen stores and compares what you enter. It does not compute inheritance.')
        );

    // The tag facet narrows: one tag matches both, two match neither, and the one that matches one
    // proves the query filters rather than answering everything.
    test()->get('/legacy?tag=Turf')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->count('veterans.data', 2));

    test()->get('/legacy?tag=Mile')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->count('veterans.data', 1)
            ->where('veterans.data.0.id', $mileSpeed->id)
            ->where('filters.tag', 'Mile'));

    // A scenario that does not exist is refused and named, not ignored (ADR-0018's reasoning).
    test()->get('/legacy?scenario=not_a_scenario')
        ->assertRedirect(route('legacy.index'))
        ->assertSessionHasErrors('scenario');

    test()->get('/legacy?trainee=999999')
        ->assertRedirect(route('legacy.index'))
        ->assertSessionHasErrors('trainee');
});

it('states the two empty states apart on the browse surface', function (): void {
    // No records at all: what is missing, why it matters, what to do.
    test()->get('/legacy')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->count('veterans.data', 0)
            ->where('totalCount', 0));

    // Records exist but the filter excludes them: a different absence, and pointing a Trainer at
    // "finish a run" here would be the wrong instruction (design-2.0 §29).
    legacyVeteran('Special Week');

    test()->get('/legacy?tag=Nostalgic')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->count('veterans.data', 0)
            ->where('totalCount', 1));
});

it('draws the six-node graph in the client order, from the run and its payload', function (): void {
    $run = activeRun('Symboli Rudolf', [
        'inheritance_parent_a_id' => Umamusume::factory()->create(['name' => 'Special Week'])->id,
        'inheritance_parent_b_id' => Umamusume::factory()->create(['name' => 'Tokai Teio'])->id,
        'legacy_selection' => sixNodePayload(),
    ]);

    test()->get("/legacy/{$run->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Legacy/Builder')
            ->where('hasSelection', true)
            ->where('trainee.name', 'Symboli Rudolf')
            // Six nodes: the trainee, two parents, four grandparents. The trainee and the two parents
            // are read from the run's own columns (ADR-0010 kept those foreign keys as the parents'
            // identity); the four grandparents are the payload's `ancestors` names, which is the
            // shape D-268 specified and the reason there is no ancestor table.
            ->where('graph.trainee.name', 'Symboli Rudolf')
            ->where('graph.parents.0.slot', 'parent_a')
            ->where('graph.parents.0.name', 'Special Week')
            ->where('graph.parents.0.rank', 3)
            ->where('graph.parents.0.is_guest', false)
            ->where('graph.parents.0.ancestors.0.name', 'Grass Wonder')
            ->where('graph.parents.0.ancestors.1.name', 'El Condor Pasa')
            ->where('graph.parents.1.slot', 'parent_b')
            ->where('graph.parents.1.name', 'Tokai Teio')
            ->where('graph.parents.1.rank', 1)
            ->where('graph.parents.1.is_guest', true)
            ->where('graph.parents.1.ancestors.0.name', 'Tokai Teio')
            ->where('graph.parents.1.ancestors.1.name', 'Gold Ship')
            // The Spark list as recorded, each with the client kind's own label and its star count.
            ->where('graph.parents.0.sparks.0.kind_label', 'Blue')
            ->where('graph.parents.0.sparks.0.target', 'Speed')
            ->where('graph.parents.0.sparks.0.stars', 2)
            ->where('graph.parents.0.sparks.1.kind_label', 'White')
            ->where('graph.parents.1.sparks.0.kind_label', 'Pink')
            ->where('graph.parents.1.sparks.0.stars', 3)
            // The stored Affinity grade travels as its own prop, because `Legacy/Builder.vue` seeds its
            // edit form from the payload. The graph is read-only display; the form is what a re-confirm
            // posts, and a form that opened blank would write blank over a recorded selection.
            ->where('affinity', '◎')
        );
});

it('reports no Legacy selection as a named absence rather than an empty graph', function (): void {
    $run = activeRun();

    test()->get("/legacy/{$run->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // D-220: "you never opened this screen" is a disclosure, and two empty parent records
            // would be a claim about a client screen that cannot be completed that way.
            ->where('hasSelection', false)
            ->where('graph.parents.0.name', null)
            ->where('graph.parents.0.rank', null)
            ->where('graph.parents.0.ancestors.0.name', null)
            ->where('graph.parents.0.sparks', [])
            // No payload, so no grade to seed: the select opens on "Not recorded".
            ->where('affinity', null)
        );
});

it('states no star-roll chance and no priced Affinity, and names both absences', function (): void {
    $run = activeRun(attributes: ['legacy_selection' => sixNodePayload()]);

    test()->get("/legacy/{$run->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // The record-only boundary as a behaviour. The corpus prices star-roll odds only as a wiki
            // pair flagged stale (§1.5.3) and nothing in this tree holds them as data, so the honest
            // answer is null plus a reason rather than an `Estimated` label on a number this tool
            // would have had to source from nowhere. Affinity is stored and never priced (ADR-0010).
            ->where('graph.parents.0.probability.value', null)
            ->where('graph.parents.1.probability.value', null)
            ->has('graph.parents.0.probability.title')
            ->count('affinityGrades', 3)
        );
});

it('opens the builder only on a run that is still being planned', function (RunStatus $status): void {
    // D-260 fixes the Legacy Select result for the life of the run, and `RecordVeteran` applies the
    // same lifecycle rule at the other end. A finished or abandoned run has no builder, and the
    // refusal is a 404 rather than a disabled page, so the URL cannot be shared as if it worked.
    $run = TrainingRun::factory()->create(['status' => $status]);

    test()->get("/legacy/{$run->id}")->assertNotFound();
    test()->put("/legacy/{$run->id}", [])->assertNotFound();
})->with([
    'Completed' => RunStatus::Completed,
    'Retired' => RunStatus::Retired,
]);

it('records a confirmed six-node selection and reads it back unchanged', function (): void {
    $run = activeRun();
    $parentA = legacyVeteran('Special Week');
    $parentB = legacyVeteran('Tokai Teio');

    $response = test()->put("/legacy/{$run->id}", [
        'affinity' => '◎',
        'legacies' => [
            [
                'legacy_id' => $parentA->id,
                'rank' => 3,
                'is_guest' => false,
                'ancestors' => ['Grass Wonder', 'El Condor Pasa'],
                'sparks' => [
                    ['kind' => 'blue', 'target' => 'Speed', 'stars' => 2],
                ],
            ],
            [
                'legacy_id' => $parentB->id,
                'rank' => 1,
                'is_guest' => true,
                'ancestors' => ['Tokai Teio', 'Gold Ship'],
                'sparks' => [
                    ['kind' => 'pink', 'target' => 'Mile', 'stars' => 3],
                ],
            ],
        ],
    ]);

    $response->assertRedirect(route('legacy.compare', ['runs' => [$run->id]]))
        ->assertSessionHas('status', 'Inheritance recorded.');

    // The column stores exactly the `LegacySelectionPayload` shape, so `legacySelection()` reads it
    // back with no translation. This is the D-260 / D-268 contract and the whole of the write's claim.
    expect($run->fresh()->legacy_selection)->toBe([
        'legacies' => [
            [
                'rank' => 3,
                'is_guest' => false,
                'ancestors' => ['Grass Wonder', 'El Condor Pasa'],
                'sparks' => [['kind' => 'blue', 'target' => 'Speed', 'stars' => 2]],
            ],
            [
                'rank' => 1,
                'is_guest' => true,
                'ancestors' => ['Tokai Teio', 'Gold Ship'],
                'sparks' => [['kind' => 'pink', 'target' => 'Mile', 'stars' => 3]],
            ],
        ],
        'affinity' => '◎',
    ])->and($run->fresh()->legacySelection()?->legacies)->toHaveCount(2);

    // The parent foreign keys travel in the same write, because ADR-0010 kept them as the parents'
    // identity and the graph's two parent names read from them.
    expect($run->fresh()->inheritance_parent_a_id)
        ->toBe($parentA->fresh()->trainingRun->umamusume_id)
        ->and($run->fresh()->inheritance_parent_b_id)
        ->toBe($parentB->fresh()->trainingRun->umamusume_id);
});

it('records a half-read selection rather than refusing it', function (): void {
    // A Trainer who has read one parent's rank and nothing else has a real screen state, and every
    // figure in the payload is nullable for that reason (ADR-0010, `keeps a payload with an unread
    // figure out of the exception path`).
    $run = activeRun();

    test()->put("/legacy/{$run->id}", [
        'affinity' => null,
        'legacies' => [
            [
                'legacy_id' => null,
                'rank' => null,
                'is_guest' => false,
                'ancestors' => [null, null],
                'sparks' => [],
            ],
        ],
    ])->assertSessionHasNoErrors();

    expect($run->fresh()->legacySelection()?->legacies[0])->toBe([
        'rank' => null,
        'is_guest' => false,
        'ancestors' => [null, null],
        'sparks' => [],
    ])->and($run->fresh()->inheritance_parent_a_id)->toBeNull();
});

it('refuses a selection the client would refuse', function (): void {
    // Both rules are `REFERENCE` §1.5.4, enforced at the boundary rather than stored as a
    // configuration the game rejects: a run may not name its own trainee as a parent, and the two
    // parents must be different Umamusume.
    $trainee = Umamusume::factory()->create(['name' => 'Symboli Rudolf']);
    $run = TrainingRun::factory()->create([
        'umamusume_id' => $trainee->id,
        'status' => RunStatus::Active,
    ]);

    $ownRun = TrainingRun::factory()->create([
        'umamusume_id' => $trainee->id,
        'status' => RunStatus::Completed,
    ]);
    $ownVeteran = Veteran::factory()->create(['training_run_id' => $ownRun->id]);

    $otherVeteran = legacyVeteran('Special Week');

    test()->put("/legacy/{$run->id}", [
        'legacies' => [[
            'legacy_id' => $ownVeteran->id,
            'is_guest' => false,
            'ancestors' => [],
            'sparks' => [],
        ]],
    ])->assertSessionHasErrors('legacies.0.legacy_id');

    test()->put("/legacy/{$run->id}", [
        'legacies' => [
            ['legacy_id' => $otherVeteran->id, 'is_guest' => false, 'ancestors' => [], 'sparks' => []],
            ['legacy_id' => $otherVeteran->id, 'is_guest' => false, 'ancestors' => [], 'sparks' => []],
        ],
    ])->assertSessionHasErrors('legacies.1.legacy_id');

    expect($run->fresh()->legacy_selection)->toBeNull();
});

it('refuses a Spark outside the five the Global client renders, and stars past the ceiling', function (): void {
    $run = activeRun();

    test()->put("/legacy/{$run->id}", [
        'legacies' => [[
            'legacy_id' => null,
            'is_guest' => false,
            'ancestors' => [],
            'sparks' => [['kind' => 'gold', 'target' => null, 'stars' => 1]],
        ]],
    ])->assertSessionHasErrors('legacies.0.sparks.0.kind');

    // Three is the ceiling and the count is rolled rather than chosen (§1.5.3).
    test()->put("/legacy/{$run->id}", [
        'legacies' => [[
            'legacy_id' => null,
            'is_guest' => false,
            'ancestors' => [],
            'sparks' => [['kind' => 'blue', 'target' => 'Speed', 'stars' => 4]],
        ]],
    ])->assertSessionHasErrors('legacies.0.sparks.0.stars');

    expect($run->fresh()->legacy_selection)->toBeNull();
});

it('aligns up to four recorded selections in rows and never ranks them', function (): void {
    $first = activeRun('Symboli Rudolf', ['legacy_selection' => sixNodePayload()]);
    $second = activeRun('Kitasan Black', [
        'legacy_selection' => [
            'legacies' => [[
                'rank' => 2,
                'is_guest' => false,
                'ancestors' => ['Mejiro McQueen'],
                'sparks' => [['kind' => 'green', 'target' => 'Marching Twinkle', 'stars' => 1]],
            ]],
            'affinity' => null,
        ],
    ]);

    $response = test()->get("/legacy/compare?runs[]={$first->id}&runs[]={$second->id}");

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Legacy/Compare')
            ->count('columns', 2)
            // The Trainer's own order, not the database's: `runsInOrder()` re-keys `whereIn`, because
            // a shuffled column set is a comparison of the wrong two things.
            ->where('columns.0.run_id', $first->id)
            ->where('columns.0.trainee', 'Symboli Rudolf')
            ->where('columns.1.run_id', $second->id)
            ->where('columns.1.trainee', 'Kitasan Black')
            // Per-kind counts, straight off the recorded list. A sum across kinds would be a number
            // this tool invented, so the props carry the per-kind counts and nothing else.
            ->where('columns.0.graph.parents.0.spark_counts.0.kind', 'blue')
            ->where('columns.0.graph.parents.0.spark_counts.0.count', 1)
            ->where('columns.0.graph.parents.0.spark_counts.0.kind_label', 'Blue')
            ->where('columns.0.graph.parents.1.spark_counts.0.count', 0)
            ->where('columns.0.graph.parents.1.spark_counts.2.kind_label', 'Green')
            ->where('columns.1.graph.parents.0.spark_counts.2.count', 1)
            // The same null-plus-reason on the compare surface as on the builder.
            ->where('columns.0.graph.parents.0.probability.value', null)
        );
});

it('caps the comparison at four and names the refusal', function (): void {
    $runs = collect(range(1, 5))->map(fn (int $index): TrainingRun => activeRun("Trainee {$index}"));

    $query = $runs->map(fn (TrainingRun $run): string => 'runs[]='.$run->id)->implode('&');

    test()->get("/legacy/compare?{$query}")
        ->assertRedirect(route('legacy.index'))
        ->assertSessionHasErrors('runs');
});

it('lists no comparable run when none carries a selection', function (): void {
    // A run with no payload would print a column of N/A, which is a screen that looks compared and
    // is not, so it is not offered at all.
    activeRun('Symboli Rudolf');
    $recorded = activeRun('Kitasan Black', ['legacy_selection' => sixNodePayload()]);

    test()->get('/legacy/compare')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->count('columns', 0)
            ->count('comparable', 1)
            ->where('comparable.0.id', $recorded->id)
        );
});

it('does not let the literal compare segment bind to the run route', function (): void {
    // `/legacy/compare` is declared first and `{run}` is `whereNumber`, the same pair of decisions
    // `runs.import` and `runs.{run}` already make. Without one of the two this is a 404 on a number
    // binding, which would read as "the screen is broken" rather than as a routing mistake.
    test()->get('/legacy/compare')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Legacy/Compare'));
});
