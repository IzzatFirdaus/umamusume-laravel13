<?php

declare(strict_types=1);

use App\Http\Controllers\LegacyController;
use App\Models\DeckSlot;
use App\Models\Legacy\LegacySelectionPayload;
use App\Models\SupportCard;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use App\Models\Veteran;
use App\Services\Career\SetupDraft;
use App\Services\Legacy\AncestryGraph;
use Inertia\Testing\AssertableInertia as Assert;

// SCR-CAR-008 and SCR-CAR-009 (`ADR-0020` §1 and §3; plan §8 D5-wizard and D6-wizard): the wizard's
// ancestry step and support-deck step, both entered before a run exists and both carried in the session
// draft. Props, the draft round trips, the refusals and the untouched run-scoped writes live here; the
// rendered copy, the keyboard paths, the 44px sweep, the 320px reflow and the step-4-to-step-5 carry live
// in `tests/browser/career-legacy-deck-steps.spec.ts`.

/**
 * A six-node payload aimed at two library rows. Named apart from the helpers in `StoreLegacySelectionRequestTest`
 * and `CareerBuildTargetTest` because Pest loads every test file into one process and a redeclared
 * function name is a fatal.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function draftLegacyPayload(array $overrides = []): array
{
    return array_merge([
        'affinity' => '◎',
        'legacies' => [
            [
                'legacy_id' => null,
                'rank' => 4,
                'is_guest' => false,
                'ancestors' => ['Grass Wonder', 'Mill Raptor'],
                'sparks' => [
                    ['kind' => 'blue', 'target' => 'Speed', 'stars' => 2],
                    ['kind' => 'scenario', 'target' => null, 'stars' => null],
                ],
            ],
            [
                'legacy_id' => null,
                'rank' => null,
                'is_guest' => true,
                'ancestors' => [''],
                'sparks' => [],
            ],
        ],
    ], $overrides);
}

/**
 * @return array<string, mixed>
 */
function draftDeckPayload(int $first, int $second, string $firstOwnership = 'OWNED'): array
{
    return [
        'deck' => [
            1 => ['support_card_id' => (string) $first, 'ownership' => $firstOwnership],
            2 => ['support_card_id' => (string) $second, 'ownership' => 'RENTED'],
            3 => ['support_card_id' => '', 'ownership' => ''],
            4 => ['support_card_id' => '', 'ownership' => ''],
            5 => ['support_card_id' => '', 'ownership' => ''],
            6 => ['support_card_id' => '', 'ownership' => ''],
        ],
    ];
}

/**
 * Two catalogue rows and the library rows a parent can be picked from.
 *
 * @return array{cards: list<int>, parents: list<int>, trainee: Umamusume}
 */
function wizardFixture(): array
{
    $cards = [
        SupportCard::factory()->create(['char_name' => 'Special Week', 'type' => 'speed'])->id,
        SupportCard::factory()->create(['char_name' => 'Silence Suzuka', 'type' => 'stamina'])->id,
    ];

    $parents = [
        Veteran::factory()->create()->id,
        Veteran::factory()->create()->id,
    ];

    return ['cards' => $cards, 'parents' => $parents, 'trainee' => Umamusume::factory()->create()];
}

it('renders the ancestry step with six nodes, the library it can pick from and nothing stored', function (): void {
    $fixture = wizardFixture();
    SetupDraft::write(['umamusume_id' => $fixture['trainee']->id]);

    $this->get(route('career.legacy'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/LegacySelect')
            ->where('trainee.id', $fixture['trainee']->id)
            ->where('trainee.name', $fixture['trainee']->name)
            ->where('scenarioLabel', null)
            ->where('hasSelection', false)
            ->where('affinity', null)
            // Six nodes, in the client's order, each with its two ancestors and no name until someone
            // enters one. The trainee row is the draft's own choice, not a stored seventh node.
            ->where('graph.trainee.name', $fixture['trainee']->name)
            ->has('graph.parents', 2)
            ->has('graph.parents.0.ancestors', 2)
            ->where('graph.parents.0.slot', 'parent_a')
            ->where('graph.parents.0.name', null)
            ->where('graph.parents.0.rank', null)
            ->where('graph.parents.1.ancestors.0.slot', 'Grandparent B1')
            // The chance a Spark rolls is an absent value with its reason attached, never a number.
            ->where('graph.parents.0.probability.value', null)
            ->has('graph.parents.0.probability.title')
            // Two library rows, and the pick controls select them by id.
            ->has('roster', 2)
            ->where('rosterTotal', 2)
            ->where('parents', [null, null])
            // The vocabularies arrive from their owners rather than being typed into the page.
            ->where('sparkKinds', AncestryGraph::SPARK_KIND_LABELS)
            ->where('affinityGrades', LegacySelectionPayload::AFFINITY_GRADES)
            ->where('notice', LegacyController::RECORD_ONLY_NOTICE));

    // Nothing this step reads or writes touches the database: no run, no deck row, no new Veteran.
    expect(TrainingRun::query()->whereNotNull('legacy_selection')->count())->toBe(0);
});

it('round-trips the six-node payload through the draft and reads it back on the next render', function (): void {
    $fixture = wizardFixture();
    SetupDraft::write(['umamusume_id' => $fixture['trainee']->id, 'scenario' => 'ura_finale']);

    $this->put(route('career.legacy.store'), draftLegacyPayload([
        'legacies' => [
            [
                'legacy_id' => $fixture['parents'][0],
                'rank' => 4,
                'is_guest' => false,
                'ancestors' => ['Grass Wonder', 'Mill Raptor'],
                'sparks' => [['kind' => 'blue', 'target' => 'Speed', 'stars' => 2]],
            ],
            [
                'legacy_id' => $fixture['parents'][1],
                'rank' => null,
                'is_guest' => true,
                'ancestors' => ['Mayano Top Gun', ''],
                'sparks' => [],
            ],
        ],
    ]))->assertRedirect(route('career.legacy'));

    // The payload is exactly what `LegacySelectionPayload` accepts, with no draft-only field smuggled in:
    // this is the value Preflight writes into `training_runs.legacy_selection`.
    $payload = LegacySelectionPayload::fromArray(SetupDraft::legacySelection());
    expect($payload->affinity)->toBe('◎')
        ->and($payload->legacies[0]['rank'])->toBe(4)
        ->and($payload->legacies[0]['ancestors'])->toBe(['Grass Wonder', 'Mill Raptor'])
        ->and($payload->legacies[0]['sparks'][0]['kind'])->toBe('blue')
        ->and($payload->legacies[1]['is_guest'])->toBeTrue()
        ->and($payload->legacies[1]['ancestors'])->toBe(['Mayano Top Gun', '']);

    // The two library picks are their own draft key, because the payload has no slot for a parent's
    // identity and a career with no run row has no foreign key to hold it either.
    expect(SetupDraft::legacyParents())->toBe($fixture['parents']);

    $this->get(route('career.legacy'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('hasSelection', true)
            ->where('affinity', '◎')
            ->where('parents', $fixture['parents'])
            ->where('graph.parents.0.rank', 4)
            ->where('graph.parents.0.sparks.0.kind_label', 'Blue')
            ->where('graph.parents.0.ancestors.1.name', 'Mill Raptor')
            // A parent's name is read through the library row she was picked from, the same way the run
            // screen reads it through its two foreign keys.
            ->where('graph.parents.1.name', Veteran::find($fixture['parents'][1])->trainingRun->umamusume->name)
            ->where('graph.parents.1.spark_counts.0.count', 0));

    expect(TrainingRun::count())->toBe(0);
});

it('refuses an unknown parent, a half-filled node and an off-dictionary Spark, leaving the draft untouched', function (): void {
    $fixture = wizardFixture();
    SetupDraft::write(['umamusume_id' => $fixture['trainee']->id]);

    // A library id that is not a row: the parent's identity is a lookup key, and a value that resolves to
    // nothing is refused rather than stored as a node that reads back blank.
    $this->put(route('career.legacy.store'), draftLegacyPayload([
        'legacies' => [array_merge(draftLegacyPayload()['legacies'][0], ['legacy_id' => 424242]), draftLegacyPayload()['legacies'][1]],
    ]))->assertSessionHasErrors('legacies.0.legacy_id');

    // A node with no ancestors key at all is a payload the reader cannot build.
    $missing = draftLegacyPayload();
    unset($missing['legacies'][0]['ancestors']);
    $this->put(route('career.legacy.store'), array_merge($missing, ['legacies' => [$missing['legacies'][0], draftLegacyPayload()['legacies'][1]]]))
        ->assertSessionHasErrors('legacies.0.ancestors');

    // A Spark kind outside the `[Global]` five, and a fourth star: both vocabularies belong to
    // `LegacySelectionPayload`, and neither is widened by a hand-made POST.
    $this->put(route('career.legacy.store'), draftLegacyPayload([
        'legacies' => [
            array_merge(draftLegacyPayload()['legacies'][0], ['sparks' => [['kind' => 'gold', 'target' => 'Speed', 'stars' => 1]]]),
            draftLegacyPayload()['legacies'][1],
        ],
    ]))->assertSessionHasErrors(['legacies.0.sparks.0.kind' => 'A Spark is Blue, Pink, Green, White or Scenario.']);

    $this->put(route('career.legacy.store'), draftLegacyPayload([
        'legacies' => [
            array_merge(draftLegacyPayload()['legacies'][0], ['sparks' => [['kind' => 'blue', 'target' => 'Speed', 'stars' => 4]]]),
            draftLegacyPayload()['legacies'][1],
        ],
    ]))->assertSessionHasErrors('legacies.0.sparks.0.stars');

    // A grade letter outside the three the reference publishes.
    $this->put(route('career.legacy.store'), draftLegacyPayload(['affinity' => 'X']))
        ->assertSessionHasErrors('affinity');

    expect(SetupDraft::legacySelection())->toBeNull()
        ->and(SetupDraft::legacyParents())->toBe([null, null]);

    // The two cross-field refusals the shape needs and a per-field rule cannot say, both reached through
    // the draft trainee rather than a run row.
    $this->put(route('career.legacy.store'), draftLegacyPayload([
        'legacies' => [
            array_merge(draftLegacyPayload()['legacies'][0], ['legacy_id' => $fixture['parents'][0]]),
            array_merge(draftLegacyPayload()['legacies'][1], ['legacy_id' => $fixture['parents'][0]]),
        ],
    ]))->assertSessionHasErrors(['legacies.1.legacy_id' => 'The two parents must be different Umamusume (REFERENCE §1.5.4).']);

    $ownRow = Veteran::factory()->create(['training_run_id' => TrainingRun::factory()->create(['umamusume_id' => $fixture['trainee']->id])->id]);

    $this->put(route('career.legacy.store'), draftLegacyPayload([
        'legacies' => [
            array_merge(draftLegacyPayload()['legacies'][0], ['legacy_id' => $ownRow->id]),
            draftLegacyPayload()['legacies'][1],
        ],
    ]))->assertSessionHasErrors(['legacies.0.legacy_id' => 'A run may not name its own trainee as a parent (REFERENCE §1.5.4).']);

    expect(SetupDraft::legacySelection())->toBeNull();
});

it('refuses a third ancestor and a third legacy the same way the run screen does', function (): void {
    $this->put(route('career.legacy.store'), draftLegacyPayload([
        'legacies' => [
            array_merge(draftLegacyPayload()['legacies'][0], ['ancestors' => ['A', 'B', 'C']]),
            draftLegacyPayload()['legacies'][1],
        ],
    ]))->assertSessionHasErrors('legacies.0.ancestors');

    $this->put(route('career.legacy.store'), [
        'affinity' => null,
        'legacies' => [
            draftLegacyPayload()['legacies'][0],
            draftLegacyPayload()['legacies'][1],
            draftLegacyPayload()['legacies'][1],
        ],
    ])->assertSessionHasErrors(['legacies' => 'A run names at most two Legacies, one per parent slot.']);

    expect(SetupDraft::legacySelection())->toBeNull();
});

it('renders the deck step with six slots, no ownership invented and the seven types the catalogue holds', function (): void {
    $fixture = wizardFixture();

    $this->get(route('career.deck'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/DeckSelect')
            ->has('slots', 6)
            ->where('slots.0.position', 1)
            ->where('slots.0.label', 'Slot 1')
            ->where('slots.0.selected', '')
            ->where('slots.0.card', null)
            // A slot nobody has written has no ownership. The two-button group renders that as `N/A` with
            // a `title`; it is not defaulted to Owned, which would be a claim.
            ->where('slots.0.ownership', null)
            ->where('slots.5.label', 'Slot 6 · Friends')
            ->where('slots.5.is_friend', true)
            ->where('slots.0.is_friend', false)
            ->has('types', 7)
            ->where('types.4.label', 'Wit')
            ->where('types.5.label', 'Pal')
            ->where('types.6.label', 'Group')
            ->has('picker.options', 2)
            ->where('picker.type', null)
            ->where('picker.fillingSlot', 1)
            ->where('scenarioPending', true)
            ->where('analysis.covered', 0));

    expect(DeckSlot::count())->toBe(0);
});

it('stores six slots with the flag per slot and reads the cards back on the next render', function (): void {
    $fixture = wizardFixture();
    SetupDraft::write(['scenario' => 'ura_finale']);

    $this->put(route('career.deck.store'), draftDeckPayload($fixture['cards'][0], $fixture['cards'][1]))
        ->assertRedirect(route('career.deck'));

    $draft = SetupDraft::deck();

    // Six rows in position order, the two blanks kept: a cleared slot is a statement the Trainer made, and
    // a row the run table has no column for (ADR-0014), which is why it lives here and not in `deck_slots`.
    expect($draft)->toHaveCount(6)
        ->and($draft[0])->toBe(['position' => 1, 'support_card_id' => $fixture['cards'][0], 'ownership' => 'OWNED'])
        ->and($draft[1])->toBe(['position' => 2, 'support_card_id' => $fixture['cards'][1], 'ownership' => 'RENTED'])
        ->and($draft[5])->toBe(['position' => 6, 'support_card_id' => null, 'ownership' => null]);

    expect(DeckSlot::count())->toBe(0);

    $this->get(route('career.deck'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('slots.0.selected', (string) $fixture['cards'][0])
            ->where('slots.0.ownership', 'OWNED')
            ->where('slots.0.card.name', 'Special Week [Tracen Academy]')
            ->where('slots.0.card.type', 'speed')
            ->where('slots.0.card.type_label', 'Speed')
            ->where('slots.1.ownership', 'RENTED')
            ->where('slots.2.card', null)
            // URA Finale's linked list is a cast list, and this card's character is not on it: derived on
            // read, never stored on the card (ADR-0014 correction 3).
            ->where('slots.0.card.scenario_link', 'not_linked')
            ->where('analysis.covered', 2));
});

it('refuses a card outside the catalogue, a flag outside the dictionary and a slot outside the six', function (): void {
    $fixture = wizardFixture();

    // A catalogue id that is not a row, in the shape the picker posts.
    $this->put(route('career.deck.store'), draftDeckPayload(999999, $fixture['cards'][1]))
        ->assertSessionHasErrors('deck.1.support_card_id');

    // A value outside the two the dictionary holds.
    $this->put(route('career.deck.store'), draftDeckPayload($fixture['cards'][0], $fixture['cards'][1], 'BORROWED'))
        ->assertSessionHasErrors(['deck.1.ownership' => 'A slot holds a card you own or one you rented, and nothing else.']);

    // A filled slot that says nothing about how it is held.
    $missing = draftDeckPayload($fixture['cards'][0], $fixture['cards'][1]);
    unset($missing['deck'][1]['ownership']);
    $this->put(route('career.deck.store'), $missing)->assertSessionHasErrors('deck.1.ownership');

    // A seventh position is not a deck.
    $this->put(route('career.deck.store'), draftDeckPayload($fixture['cards'][0], $fixture['cards'][1]) + [
        'deck' => array_merge(draftDeckPayload($fixture['cards'][0], $fixture['cards'][1])['deck'], [7 => ['support_card_id' => (string) $fixture['cards'][0], 'ownership' => 'OWNED']]),
    ])->assertSessionHasErrors('deck');

    // The same card twice is refused by the shared rule, reported against the deck rather than one row.
    $this->put(route('career.deck.store'), draftDeckPayload($fixture['cards'][0], $fixture['cards'][0]))
        ->assertSessionHasErrors(['deck' => 'The same card cannot be equipped twice. Break the duplicate instead.']);

    expect(SetupDraft::deck())->toBeNull();
});

it('keeps the ancestry and the deck through a visit to another step and back', function (): void {
    $fixture = wizardFixture();
    SetupDraft::write(['scenario' => 'ura_finale', 'umamusume_id' => $fixture['trainee']->id]);

    $this->put(route('career.legacy.store'), draftLegacyPayload([
        'legacies' => [
            array_merge(draftLegacyPayload()['legacies'][0], ['legacy_id' => $fixture['parents'][0]]),
            draftLegacyPayload()['legacies'][1],
        ],
    ]))->assertRedirect();

    $this->put(route('career.deck.store'), draftDeckPayload($fixture['cards'][0], $fixture['cards'][1]))
        ->assertRedirect();

    // Step 1 re-picked: `SetupDraft::write()` merges over the raw bag, so going back to the first step
    // cannot drop what steps 4 and 5 recorded.
    $this->put(route('career.scenario.store'), ['scenario' => 'trackblazer'])->assertRedirect();

    expect(SetupDraft::legacySelection())->not->toBeNull()
        ->and(SetupDraft::legacyParents())->toBe([$fixture['parents'][0], null])
        ->and(SetupDraft::deck()[0]['support_card_id'])->toBe($fixture['cards'][0]);

    // And the two steps still read their own values after the visit, not the client's memory.
    $this->get(route('career.legacy'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('graph.parents.0.ancestors.0.name', 'Grass Wonder')
            ->where('parents.0', $fixture['parents'][0]));

    $this->get(route('career.deck'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('slots.0.selected', (string) $fixture['cards'][0])
            ->where('slots.0.ownership', 'OWNED'));
});

it('leaves the run-scoped ancestry and deck writes exactly as they were', function (): void {
    // The shared rule sets are the point of the subclasses, and the sibling callers must not move: the
    // run's `legacy.update` still writes the column and the two foreign keys, and `runs.deck.sync` still
    // writes six positions with no ownership posted.
    $fixture = wizardFixture();
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $other = TrainingRun::factory()->create();

    $this->put(route('legacy.update', $run), draftLegacyPayload([
        'legacies' => [
            array_merge(draftLegacyPayload()['legacies'][0], ['legacy_id' => $fixture['parents'][0]]),
            array_merge(draftLegacyPayload()['legacies'][1], ['legacy_id' => $fixture['parents'][1]]),
        ],
    ]))->assertRedirect(route('legacy.compare', ['runs' => [$run->id]]));

    expect($run->fresh()->legacySelection()?->affinity)->toBe('◎')
        ->and($run->fresh()->inheritance_parent_a_id)->toBe($other->umamusume_id)
        ->and($run->fresh()->inheritance_parent_b_id)->not->toBeNull();

    // A refused write on the run screen still lands back on that run's builder, not on the wizard.
    $this->put(route('legacy.update', $run), ['affinity' => 'X'])->assertRedirect(route('legacy.builder', $run));

    $this->post(route('runs.deck.sync', $run), [
        'deck' => [
            1 => ['support_card_id' => (string) $fixture['cards'][0]],
            2 => ['support_card_id' => (string) $fixture['cards'][1]],
        ],
    ])->assertRedirect();

    expect(DeckSlot::query()->where('training_run_id', $run->id)->count())->toBe(2);
});

it('shapes the six nodes the same way for the draft and for a run', function (): void {
    // One graph builder, two screens: the wizard's step 4 and the Legacy Lab's builder print the same node
    // shape from the same payload, so a node means the same thing on both.
    $posted = draftLegacyPayload();

    // The stored payload holds no parent identity (`ADR-0010`: the run's two foreign keys do), so the
    // parity check feeds `fromArray()` the key set it accepts rather than the posted one.
    unset($posted['legacies'][0]['legacy_id'], $posted['legacies'][1]['legacy_id']);

    $payload = LegacySelectionPayload::fromArray($posted);
    $fromDraft = AncestryGraph::build($payload, 'Mejiro McQueen', 'Rice Shower', null);
    $fromRun = AncestryGraph::build($payload, 'Mejiro McQueen', 'Rice Shower', null);

    expect($fromDraft)->toBe($fromRun)
        ->and($fromDraft['parents'][0]['ancestors'])->toBe([
            ['slot' => 'Grandparent A1', 'name' => 'Grass Wonder'],
            ['slot' => 'Grandparent A2', 'name' => 'Mill Raptor'],
        ])
        ->and($fromDraft['parents'][1]['name'])->toBeNull()
        ->and($fromDraft['parents'][0]['probability']['value'])->toBeNull();
});

it('names the absence when the library holds no Veteran', function (): void {
    // The state a fresh install meets, asserted as itself: the pick list is empty and the step says so
    // rather than rendering two controls with nothing in them.
    $this->get(route('career.legacy'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('roster', [])
            ->where('rosterTotal', 0)
            ->where('trainee', null));

    expect(Veteran::count())->toBe(0);
});

it('keeps every scenario name and deck vocabulary out of the two new components', function (): void {
    $sources = [
        'resources/js/pages/Career/LegacySelect.vue',
        'resources/js/pages/Career/DeckSelect.vue',
    ];

    $values = array_merge(
        array_map(static fn (array $definition): string => (string) $definition['label'], array_values((array) config('scenarios.scenarios'))),
        array_keys((array) config('scenarios.scenarios')),
        // The seven type *words*, which is what a page must not type out. The storage keys are not in the
        // list: `friend` and `intelligence` are export keys that appear in ordinary prose here and in the
        // slot copy `ADR-0014` fixes ("the friend slot"), and a sweep that flagged them would be a sweep a
        // correct page cannot pass.
        array_map(static fn (string $type): string => SupportCard::typeWord($type), SupportCard::TYPES),
        LegacySelectionPayload::AFFINITY_GRADES,
    );

    foreach ($sources as $path) {
        $source = (string) file_get_contents(base_path($path));

        $violations = array_values(array_filter(
            $values,
            // Word-boundary matching, not a substring: one vocabulary value is a substring of ordinary JS,
            // so `str_contains` would flag any page that mentions it.
            static fn (string $value): bool => preg_match('/\b'.preg_quote($value, '/').'\b/', $source) === 1,
        ));

        expect($violations)->toBe([], "{$path} repeats: ".implode(', ', $violations));
    }
});
