<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\DeckSlot;
use App\Models\SupportCard;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use App\Models\Veteran;
use App\Services\Career\SetupDraft;
use Inertia\Testing\AssertableInertia as Assert;

// SCR-CAR-010 (SCREEN-008, PRD FR-A-5, `ADR-0020` §1 and §3; plan §8 D7): the wizard's Preflight step.
// It composes the five entered steps into one contract, states the warnings derivable from entered
// data, and creates the run exactly once on `Start Career`. The rendered copy, the Edit round trip and
// the error focus path live in `tests/browser/career-preflight.spec.ts`.

/**
 * The stat matrix's five stats with an entered target each, so the payload's `targets` map is exactly
 * the matrix's own key set (a partial map is refused by `BuildTargetPayload`).
 *
 * @return array<string, int>
 */
function preflightTargets(): array
{
    return array_fill_keys(config('scenarios.stat_order'), 800);
}

/**
 * A complete draft: every one of the five wizard keys as its own step left it.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function preflightDraft(Umamusume $trainee, array $cardIds, array $parentIds, array $overrides = []): array
{
    $slots = [];

    foreach (DeckSlot::POSITIONS as $index => $position) {
        $slots[] = [
            'position' => $position,
            'support_card_id' => $cardIds[$index] ?? null,
            'ownership' => ($cardIds[$index] ?? null) === null ? null : 'OWNED',
        ];
    }

    return array_merge([
        'scenario' => 'ura_finale',
        'umamusume_id' => $trainee->id,
        'build_target' => [
            'purpose' => 'StoryClear',
            'distance' => 'Medium',
            'surface' => 'Turf',
            'style' => 'Pace Chaser',
            'targets' => preflightTargets(),
            'skill_priorities' => [],
        ],
        'legacy_selection' => [
            'affinity' => '◎',
            'legacies' => [
                ['rank' => 3, 'is_guest' => false, 'ancestors' => ['Grass Wonder', 'Mill Raptor'], 'sparks' => [['kind' => 'blue', 'target' => 'Speed', 'stars' => 2]]],
                ['rank' => null, 'is_guest' => true, 'ancestors' => ['Mayano Top Gun', ''], 'sparks' => []],
            ],
        ],
        'legacy_parents' => $parentIds,
        'deck' => $slots,
    ], $overrides);
}

/**
 * @return array{cards: list<int>, parents: list<int>, trainee: Umamusume}
 */
function preflightFixture(string $distanceAptitude = 'A'): array
{
    $cards = [];

    for ($i = 0; $i < 6; $i++) {
        $cards[] = SupportCard::factory()->create(['char_name' => 'Card '.$i, 'title_en' => null, 'type' => 'speed'])->id;
    }

    return [
        'cards' => $cards,
        'parents' => [Veteran::factory()->create()->id, Veteran::factory()->create()->id],
        'trainee' => Umamusume::factory()->create([
            'aptitude_medium' => $distanceAptitude,
            'aptitude_turf' => 'A',
            'aptitude_pace_chaser' => 'A',
        ]),
    ];
}

it('composes every entered step into one contract', function (): void {
    $fixture = preflightFixture();
    session([SetupDraft::SESSION_KEY => preflightDraft($fixture['trainee'], $fixture['cards'], $fixture['parents'])]);

    test()->get(route('career.preflight'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Preflight')
            ->where('step', 6)
            ->where('contract.complete', true)
            // Build: trainee, scenario, target, and the six legacy members.
            ->where('contract.build.trainee.name', $fixture['trainee']->name)
            ->where('contract.build.scenario.label', 'URA Finale')
            ->where('contract.build.target.purpose', 'StoryClear')
            ->where('contract.build.target.purpose_label', 'Story Clear')
            ->where('contract.build.target.distance', 'Medium')
            ->where('contract.build.target.targets.Speed', 800)
            ->where('contract.build.legacy.affinity', '◎')
            ->has('contract.build.legacy.members', 2)
            // The parent's own name is read through the library row the draft picked (`ADR-0010` keeps the
            // identity in the run's two foreign keys, not in the payload), so the expectation resolves the
            // same way rather than restating the payload's ancestor text.
            ->where('contract.build.legacy.members.0.name', Veteran::find($fixture['parents'][0])->trainingRun->umamusume->name)
            ->where('contract.build.legacy.members.1.is_guest', true)
            ->where('contract.build.legacy.members.0.ancestors.1.name', 'Mill Raptor')
            ->where('contract.build.legacy.members.0.sparks.0.kind_label', 'Blue')
            // The deck: six positions with the six cards, and the D6 analysis beside them.
            ->has('contract.deck.slots', 6)
            ->where('contract.deck.slots.5.is_friend', true)
            ->where('contract.deck.slots.0.card.name', 'Card 0')
            ->where('contract.deck.analysis.covered', 6)
            ->has('contract.deck.analysis.categories')
            // The target section reads the same payload, and the skill list is a named absence.
            ->has('contract.target.stats', 5)
            ->where('contract.target.stats.0.label', 'Speed')
            ->where('contract.target.stats.0.value', 800)
            ->where('contract.target.races.surface', 'Turf')
            ->where('contract.target.skills.names', [])
            // The ruleset snapshot is a named absence while no source defines a version.
            ->where('contract.ruleset.label', 'N/A')
            ->where('contract.warnings', [])
            ->where('startAction', route('career.preflight.store'))
        );
});

it('names each missing section as its own warning and never claims the contract is complete', function (): void {
    session([SetupDraft::SESSION_KEY => []]);

    test()->get(route('career.preflight'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('contract.complete', false)
            ->where('contract.build.trainee', null)
            ->where('contract.build.legacy', null)
            ->where('contract.deck', null)
            ->where('contract.warnings.0.key', 'missing_section.scenario')
            ->where('contract.warnings.1.key', 'missing_section.trainee')
            ->where('contract.warnings.2.key', 'missing_section.target')
            ->where('contract.warnings.3.key', 'missing_section.legacy')
            ->where('contract.warnings.4.key', 'missing_section.deck')
        );
});

it('warns when the target distance sits on a weak aptitude, and stays quiet at C or better', function (): void {
    $fixture = preflightFixture('D');
    session([SetupDraft::SESSION_KEY => preflightDraft($fixture['trainee'], $fixture['cards'], $fixture['parents'])]);

    test()->get(route('career.preflight'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('contract.warnings.0.key', 'aptitude_below_target.distance')
            ->where('contract.warnings.0.detail', 'Your target is Medium. This trainee\'s distance aptitude is D, which design-2.0 §17 bands Weak or lower.')
        );

    $better = preflightFixture('C');
    session([SetupDraft::SESSION_KEY => preflightDraft($better['trainee'], $better['cards'], $better['parents'])]);

    test()->get(route('career.preflight'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('contract.warnings', []));
});

it('warns about a half-filled deck, an empty parent slot and a skill priority no source names', function (): void {
    $fixture = preflightFixture();
    $draft = preflightDraft($fixture['trainee'], $fixture['cards'], [null, null], [
        'deck' => [
            ['position' => 1, 'support_card_id' => $fixture['cards'][0], 'ownership' => 'OWNED'],
            ['position' => 2, 'support_card_id' => $fixture['cards'][1], 'ownership' => 'OWNED'],
            ['position' => 3, 'support_card_id' => null, 'ownership' => null],
            ['position' => 4, 'support_card_id' => null, 'ownership' => null],
            ['position' => 5, 'support_card_id' => null, 'ownership' => null],
            ['position' => 6, 'support_card_id' => null, 'ownership' => null],
        ],
    ]);
    $draft['build_target']['skill_priorities'] = ['Nihon Ichi no Uma'];
    session([SetupDraft::SESSION_KEY => $draft]);

    test()->get(route('career.preflight'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('contract.complete', true)
            ->where('contract.warnings.0.key', 'deck_incomplete')
            ->where('contract.warnings.0.detail', '2 of six positions carry a card; 4 are still empty. A career can start either way.')
            ->where('contract.warnings.1.key', 'legacy_slot_empty.parent_a')
            ->where('contract.warnings.2.key', 'legacy_slot_empty.parent_b')
            ->where('contract.warnings.3.key', 'skill_priority_unsourced.Nihon Ichi no Uma')
            ->where('contract.warnings.3.href', route('career.target'))
        );
});

it('carries the deck step\'s owned-or-rented flag onto the run it creates', function (): void {
    // The audit's case: the friend card in slot six is borrowed, and the flag was lost the moment the
    // run existed because nothing wrote it down (D3). The draft already holds it; this is the write
    // that puts it on the run.
    $fixture = preflightFixture();
    session([SetupDraft::SESSION_KEY => preflightDraft($fixture['trainee'], $fixture['cards'], $fixture['parents'], [
        'deck' => [
            ['position' => 1, 'support_card_id' => $fixture['cards'][0], 'ownership' => 'OWNED'],
            ['position' => 2, 'support_card_id' => null, 'ownership' => null],
            ['position' => 3, 'support_card_id' => null, 'ownership' => null],
            ['position' => 4, 'support_card_id' => null, 'ownership' => null],
            ['position' => 5, 'support_card_id' => null, 'ownership' => null],
            ['position' => 6, 'support_card_id' => $fixture['cards'][1], 'ownership' => 'RENTED'],
        ],
    ])]);

    test()->put(route('career.preflight.store'))->assertRedirect();

    $slots = TrainingRun::query()->where('umamusume_id', $fixture['trainee']->id)->sole()->deckSlots->keyBy('slot_position');

    expect($slots)->toHaveCount(2)
        ->and($slots[1]->ownership)->toBe('OWNED')
        ->and($slots[6]->ownership)->toBe('RENTED');
});

it('creates the run from an Inertia JSON body, where the session draft is the only source', function (): void {
    // The browser's own shape: Inertia PUTs `{}` as JSON, so the request's input source is the json bag
    // rather than the request bag. The draft is composed in `prepareForValidation()` either way, and this
    // is the case that proves it.
    $fixture = preflightFixture();
    session([SetupDraft::SESSION_KEY => preflightDraft($fixture['trainee'], $fixture['cards'], $fixture['parents'])]);

    $before = TrainingRun::count();

    test()->putJson(route('career.preflight.store'), [], ['X-Inertia' => 'true'])
        ->assertRedirect();

    expect(TrainingRun::count())->toBe($before + 1);
    expect(TrainingRun::query()->where('umamusume_id', $fixture['trainee']->id)->exists())->toBeTrue();
});

it('starts a career from a scenario and a trainee alone, storing the empty sections as null', function (): void {
    // The state a fresh install meets: the catalogue is seeded and the Veteran library is empty, so the
    // ancestry step and the deck step have nothing to save. Warnings never block, so this must create.
    $fixture = preflightFixture();
    session([SetupDraft::SESSION_KEY => [
        'scenario' => 'ura_finale',
        'umamusume_id' => $fixture['trainee']->id,
    ]]);

    $before = TrainingRun::count();

    test()->put(route('career.preflight.store'))->assertRedirect();

    $run = TrainingRun::query()->where('umamusume_id', $fixture['trainee']->id)->sole();

    expect(TrainingRun::count())->toBe($before + 1)
        ->and($run->legacy_selection)->toBeNull()
        ->and($run->build_target)->toBeNull()
        ->and($run->deckSlots)->toHaveCount(0)
        ->and($run->legacySelection())->toBeNull();
});

it('creates the run from the draft once, with the deck, the ancestry and the target on it', function (): void {
    $fixture = preflightFixture();
    session([SetupDraft::SESSION_KEY => preflightDraft($fixture['trainee'], $fixture['cards'], $fixture['parents'])]);

    // The fixture's two Veteran rows each carry a Completed run of their own
    // (`VeteranFactory::definition()`), so the invariant is the count, not zero.
    $before = TrainingRun::count();

    test()->put(route('career.preflight.store'))
        ->assertRedirect();

    $run = TrainingRun::query()->where('umamusume_id', $fixture['trainee']->id)->sole();

    expect(TrainingRun::count())->toBe($before + 1)
        ->and($run->scenario)->toBe('ura_finale')
        ->and($run->status)->toBe(RunStatus::Active)
        ->and($run->deckSlots)->toHaveCount(6)
        ->and($run->deckSlots->first()->support_card_id)->toBe($fixture['cards'][0])
        ->and($run->legacySelection()?->affinity)->toBe('◎')
        ->and($run->inheritance_parent_a_id)->not->toBeNull()
        ->and($run->inheritance_parent_b_id)->not->toBeNull()
        ->and($run->build_target['purpose'])->toBe('StoryClear')
        // The draft is spent: a second Start Career must not create a second run from the same setup.
        ->and(session(SetupDraft::SESSION_KEY))->toBeNull();
});

it('refuses a draft whose support card no longer exists, creating nothing', function (): void {
    $fixture = preflightFixture();
    $draft = preflightDraft($fixture['trainee'], $fixture['cards'], $fixture['parents']);
    $draft['deck'][0]['support_card_id'] = 999999;
    session([SetupDraft::SESSION_KEY => $draft]);

    $before = TrainingRun::count();

    test()->put(route('career.preflight.store'))
        ->assertSessionHasErrors('deck');

    expect(TrainingRun::count())->toBe($before);
});

it('refuses a draft whose build target carries an unknown key, creating nothing', function (): void {
    $fixture = preflightFixture();
    $draft = preflightDraft($fixture['trainee'], $fixture['cards'], $fixture['parents']);
    $draft['build_target']['risk_tolerance'] = 'high';
    session([SetupDraft::SESSION_KEY => $draft]);

    $before = TrainingRun::count();

    test()->put(route('career.preflight.store'))
        ->assertSessionHasErrors('build_target');

    expect(TrainingRun::count())->toBe($before);
});

it('refuses a draft with no trainee, creating nothing', function (): void {
    $fixture = preflightFixture();
    $draft = preflightDraft($fixture['trainee'], $fixture['cards'], $fixture['parents'], ['umamusume_id' => null]);
    session([SetupDraft::SESSION_KEY => $draft]);

    $before = TrainingRun::count();

    test()->put(route('career.preflight.store'))
        ->assertSessionHasErrors('umamusume_id');

    expect(TrainingRun::count())->toBe($before);
});

it('survives all three child reruns with every merged key intact on an Inertia JSON PUT', function (): void {
    // The exact attack surface that once produced `"" is not a valid backing value for RunStatus`:
    // an Inertia JSON PUT whose child FormRequests hydrated via createFrom() shared the parent's
    // json ParameterBag by reference, and the child's ->replace() wiped status, umamusume_id,
    // scenario, legacy_parents, build_target, legacy_selection and deck between validation passing
    // and runAttributes() reading them. This test posts in the browser's own exact shape
    // (`putJson` + X-Inertia) and asserts EVERY merged-out key survived the three reruns:
    // deck (always runs from preflightDraft's 6 filled slots), legacy (has legacies),
    // build_target (has a target). Regression, not a new contract.
    $fixture = preflightFixture();
    $draft = preflightDraft($fixture['trainee'], $fixture['cards'], $fixture['parents']);
    session([SetupDraft::SESSION_KEY => $draft]);

    $beforeRuns = TrainingRun::count();
    $beforeSlots = DeckSlot::count();

    test()->putJson(route('career.preflight.store'), [], ['X-Inertia' => 'true'])
        ->assertRedirect();

    $run = TrainingRun::query()
        ->where('umamusume_id', $fixture['trainee']->id)
        ->latest('id')
        ->firstOrFail();

    // Every merged key is still readable through the created row:
    expect(TrainingRun::count())->toBe($beforeRuns + 1)
        // status is the one Preflight's prepareForValidation adds rather than the draft carrying it:
        ->and($run->status)->toBe(RunStatus::Active)
        ->and($run->scenario)->toBe('ura_finale')
        ->and($run->umamusume_id)->toBe($fixture['trainee']->id)
        // build_target was not wiped to null by a child replace()
        ->and($run->build_target)->not->toBeNull()
        ->and($run->build_target['purpose'])->toBe('StoryClear')
        ->and($run->build_target['distance'])->toBe('Medium')
        ->and($run->build_target['targets']['Speed'])->toBe(800)
        // legacy_selection was not wiped to null
        ->and($run->legacy_selection)->not->toBeNull()
        ->and($run->legacy_selection['affinity'])->toBe('◎')
        ->and(count($run->legacy_selection['legacies']))->toBe(2)
        // legacy_parents were resolved to the two veterans' trainee rows
        ->and($run->inheritance_parent_a_id)->toBe(Veteran::traineeId($fixture['parents'][0]))
        ->and($run->inheritance_parent_b_id)->toBe(Veteran::traineeId($fixture['parents'][1]));

    // Deck rows were created: preflightDraft fills all six slots, so six rows
    expect(DeckSlot::count())->toBe($beforeSlots + 6);
});
