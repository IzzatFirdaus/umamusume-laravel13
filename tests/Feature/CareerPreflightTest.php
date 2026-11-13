<?php

declare(strict_types=1);

use App\Enums\CardRarity;
use App\Enums\ReleaseStatus;
use App\Enums\RunStatus;
use App\Enums\SkillAcquisition;
use App\Models\CharacterCard;
use App\Models\DeckSlot;
use App\Models\Skill;
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
        // Three-star (SSR, ceiling 50) so a level of 35 or 50 is legal for the card that holds it. The
        // factory's default is a one-star, whose 40 ceiling would refuse 50 — a different case.
        $cards[] = SupportCard::factory()->ssr()->create(['char_name' => 'Card '.$i, 'title_en' => null, 'type' => 'speed'])->id;
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

it('offers one edit door per earlier step, and no two doors share a destination', function (): void {
    // The old row carried a "Back" and an "Edit Deck" that both bound the deck route - two of four
    // links to one place - and no door at all for the two choices a Trainer most often wants to
    // change (wizard audit 2026-10-12 §2.3, §3.8). The five routes are distinct by construction;
    // the final assertion reads the prop the five links bind, so a regression that repoints two
    // doors to one route fails here rather than on a rendered page.
    $this->get('/career/setup/preflight')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('edit.scenario', route('career.scenario'))
            ->where('edit.trainee', route('career.trainee'))
            ->where('edit.target', route('career.target'))
            ->where('edit.legacy', route('career.legacy'))
            ->where('edit.deck', route('career.deck'))
            ->where('edit', fn ($edit): bool => $edit->unique()->count() === $edit->count()));
});

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
            // The asks rows (`career-preflight.html:152-155`), read from `config/scenarios.php` rather
            // than computed. Pinned here because the payload extension is what lets the screen print
            // them at all: the facility rule is one word, the fan gates carry turf and dirt separately
            // (the wireframe prints "Valentine's 60,000 turf"), and the stamina floor is per distance.
            ->where('contract.build.scenario.asks.facility_level_source', 'repetition')
            ->has('contract.build.scenario.asks.fan_gates')
            ->where('contract.build.scenario.asks.fan_gates.0.label', "Valentine's Day")
            ->where('contract.build.scenario.asks.fan_gates.0.turf', 60000)
            ->where('contract.build.scenario.asks.fan_gates.0.dirt', 40000)
            ->where('contract.build.scenario.asks.stamina_by_distance.Medium.min', 600)
            ->where('contract.build.scenario.asks.stamina_by_distance.Medium.max', 700)
            // The speed half travels in the source's own hedge, so the finale row can print it even
            // before the target step names a distance band.
            ->where('contract.build.scenario.asks.speed_phrasing', 'around 800 Speed, or an A rank')
            // The duel row (Slice 1.3). The screen used to hardcode "Win pays 30 SP and +1 cap tier"
            // while the config said "one tier", so the two drifted and neither matched the reference
            // guide's 2026-11-12 correction that the cap raise is +4 points. This pins the payload to
            // the config so a future edit to either cannot silently disagree again.
            ->where('contract.build.scenario.asks.duel.win_sp', 30)
            ->where('contract.build.scenario.asks.duel.loss_sp', 15)
            ->where('contract.build.scenario.asks.duel.cap_points', 4)
            ->where('contract.build.scenario.asks.duel.escalation_wins', 6)
            ->where(
                'contract.build.scenario.asks.duel.gains',
                fn (string $gains): bool => str_contains($gains, '+4')
                    && ! str_contains($gains, 'tier'),
            )
            // URA's own record carries no focus sentence, and the reason it says so travels with it.
            ->where('contract.build.scenario.focus', null)
            ->where('contract.build.scenario.focus_reason', 'No repository guide names a planning focus for this scenario. The scenario guides and the mechanics reference were searched. The 2026-10-16 publisher pass found none either: Game8\'s URA Finale guide states a scenario comparison and a Summer camp tip, and the plan\'s advisor hierarchy is this tool\'s own decision order, not a published planning focus.')
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

it('reads the focus sentence from the scenario metadata, and states the duel only for URA Finale', function (): void {
    $fixture = preflightFixture();
    $draft = preflightDraft($fixture['trainee'], $fixture['cards'], $fixture['parents'], ['scenario' => 'unity_cup']);
    session([SetupDraft::SESSION_KEY => $draft]);

    test()->get(route('career.preflight'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Preflight')
            ->where('contract.build.scenario.key', 'unity_cup')
            // Unity Cup's guide names a planning focus, and it now travels to the page.
            ->where('contract.build.scenario.focus', 'Wit carries the scenario\'s own cap bonus; team rank and Spirit Burst counts decide the exclusive-skill payout.')
            ->where('contract.build.scenario.focus_reason', null)
            // The duel is URA's own system: a non-URA scenario states no fan gates and no finale
            // targets, so those rows print their absence rather than URA's figures.
            ->where('contract.build.scenario.asks.fan_gates', [])
            ->where('contract.build.scenario.asks.stamina_by_distance', [])
            ->where('contract.build.scenario.asks.speed_phrasing', null));
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
            ->where('contract.warnings.0.detail', 'Your target is Medium. This trainee\'s distance aptitude is D, which the aptitude band table places in the Weak band or lower.')
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

it('carries the deck step\'s card levels through creation, by position (R6-11)', function (): void {
    // The wizard's deck step stores each card's level (`StoreDraftDeckRequest::payload()`), and Preflight
    // wrote three keys only: `support_card_id`, `slot_position`, `ownership`. `grep -c card_level` on
    // `PreflightController` returned 0, so the level was lost at the step that creates the run and the
    // run's deck ladder printed "Not recorded" over a level the draft had held for two steps.
    //
    // Asserted by position key, never by array order: the create loop iterates a map, and an order-based
    // assertion would pass with the levels on the wrong slots.
    $fixture = preflightFixture();

    session([SetupDraft::SESSION_KEY => preflightDraft($fixture['trainee'], $fixture['cards'], $fixture['parents'], [
        'deck' => [
            ['position' => 1, 'support_card_id' => $fixture['cards'][0], 'ownership' => 'OWNED', 'card_level' => 35],
            ['position' => 2, 'support_card_id' => $fixture['cards'][1], 'ownership' => 'OWNED', 'card_level' => null],
            ['position' => 3, 'support_card_id' => null, 'ownership' => null, 'card_level' => null],
            ['position' => 4, 'support_card_id' => null, 'ownership' => null, 'card_level' => null],
            ['position' => 5, 'support_card_id' => null, 'ownership' => null, 'card_level' => null],
            ['position' => 6, 'support_card_id' => $fixture['cards'][2], 'ownership' => 'RENTED', 'card_level' => 50],
        ],
    ])]);

    test()->put(route('career.preflight.store'))->assertRedirect();

    $slots = TrainingRun::query()->where('umamusume_id', $fixture['trainee']->id)->sole()->deckSlots->keyBy('slot_position');

    expect($slots)->toHaveCount(3)
        ->and($slots[1]->card_level)->toBe(35)
        ->and($slots[2]->card_level)->toBeNull()
        ->and($slots[6]->card_level)->toBe(50)
        // The level and the flag travel together: a rented card keeps both.
        ->and($slots[6]->ownership)->toBe('RENTED');
});

it('refuses a level that passed at draft time but exceeds the card return ceiling at commit (R6-11)', function (): void {
    // A level valid for the card's rarity when it was typed, and invalid by the time the run starts. The
    // draft step's own rarity check cannot see this: the draft was saved before the card changed. The
    // child re-run at Preflight has to apply the same ceiling, or the run is created holding a level the
    // client cannot represent.
    $fixture = preflightFixture();
    $card = SupportCard::query()->findOrFail($fixture['cards'][0]);

    session([SetupDraft::SESSION_KEY => preflightDraft($fixture['trainee'], $fixture['cards'], $fixture['parents'], [
        'deck' => [
            ['position' => 1, 'support_card_id' => $card->id, 'ownership' => 'OWNED', 'card_level' => 45],
            ['position' => 2, 'support_card_id' => null, 'ownership' => null, 'card_level' => null],
            ['position' => 3, 'support_card_id' => null, 'ownership' => null, 'card_level' => null],
            ['position' => 4, 'support_card_id' => null, 'ownership' => null, 'card_level' => null],
            ['position' => 5, 'support_card_id' => null, 'ownership' => null, 'card_level' => null],
            ['position' => 6, 'support_card_id' => null, 'ownership' => null, 'card_level' => null],
        ],
    ])]);

    // The card's rarity drops under the recorded level. Lvl 45 is legal for a three-star card (ceiling
    // 50) and illegal for a one-star (ceiling 40), so the same draft is now stale rather than malformed.
    $card->forceFill(['rarity' => CardRarity::OneStar])->save();

    $runsBefore = TrainingRun::count();

    test()->put(route('career.preflight.store'))->assertSessionHasErrors('deck');

    expect(TrainingRun::count())->toBe($runsBefore)
        ->and(DeckSlot::query()->where('support_card_id', $card->id)->exists())->toBeFalse()
        // The draft is intact: a refused write leaves state as it was (SHR-5).
        ->and(SetupDraft::deck()[0]['card_level'])->toBe(45);
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

it('names the trainee in the flash that answers Start career', function (): void {
    // The one line a Trainer reads after the press. `Run created.` said that a row was written and never
    // whose career it is, which is the whole of what the write decided.
    $fixture = preflightFixture();
    session([SetupDraft::SESSION_KEY => preflightDraft($fixture['trainee'], $fixture['cards'], $fixture['parents'])]);

    test()->put(route('career.preflight.store'))
        ->assertRedirect()
        ->assertSessionHas('status', fn (string $status): bool => str_starts_with($status, 'Career started for '.$fixture['trainee']->name));
});

it('names the run id in the creation flash', function (): void {
    // R4-01: the one line that answers Start career said whose career exists but not which one, so a
    // Trainer holding two careers of the same trainee could not tell the new row from the old.
    $fixture = preflightFixture();
    session([SetupDraft::SESSION_KEY => preflightDraft($fixture['trainee'], $fixture['cards'], $fixture['parents'])]);

    test()->put(route('career.preflight.store'))
        ->assertRedirect()
        ->assertSessionHas('status', fn (string $status): bool => str_starts_with($status, 'Career started for '.$fixture['trainee']->name.', run #'));

    $run = TrainingRun::query()->where('umamusume_id', $fixture['trainee']->id)->sole();

    expect(session('status'))->toBe('Career started for '.$fixture['trainee']->name.', run #'.$run->id.'.');
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

it('resolves the chosen form onto the trainee it belongs to (R6-09, R7-03, R7-13)', function (): void {
    // R6-09: the Form tile printed `as chosen above` because no prop could name the form. The label now
    // travels inside `trainee` -- not beside it -- because `contract.build` is pinned to four keys by the
    // case below, and a fifth sibling would break that structural invariant.
    //
    // R7-03/R7-13: this case used to seed `character_card_id` with a SUPPORT card, because
    // `preflightFixture()`'s `cards` array is the six-card deck. A form id is a `character_cards` id —
    // `TrainingRun.character_card_id` names a costume form, and the picker's `Form` select writes one —
    // so the old fixture proved the label resolver against a table it never queries at runtime.
    // `PreflightController::formLabel()` looked the id up in `SupportCard`, which is why the walk read
    // `FORM · N/A` while the picker's select showed a form.
    $fixture = preflightFixture();
    $trainee = $fixture['trainee'];

    $form = CharacterCard::factory()->for($trainee)->create([
        'title' => '[Rosy Dreams]',
        'rarity' => CardRarity::ThreeStar,
        'is_debut_form' => false,
    ]);

    session([SetupDraft::SESSION_KEY => preflightDraft($trainee, $fixture['cards'], $fixture['parents'], [
        'character_card_id' => $form->id,
    ])]);

    $this->get(route('career.preflight'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Preflight')
            ->has('contract.build.trainee.form_label')
            ->where('contract.build.trainee.form_label', '[Rosy Dreams]'));
});

it('marks the Legacy row by how many parents were actually named (R6-10)', function (): void {
    // R6-10: `checkState()` returned `Pass` for every entered row that was not `deck`, and `entered` for
    // legacy is only `legacy !== null` -- true whenever a `legacy_selection` exists, names or not. The
    // state word and the summary count must agree in both directions; this pins the named direction,
    // the browser case pins the unnamed one.
    $fixture = preflightFixture();

    session([SetupDraft::SESSION_KEY => preflightDraft($fixture['trainee'], $fixture['cards'], $fixture['parents'])]);

    // The section's two members come from the stored `legacy_selection` payload, not from the veteran ids
    // in `legacy_parents`, so the names asserted are the payload's own (see `preflightDraft()`).
    $this->get(route('career.preflight'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('contract.build.legacy.members', 2)
            ->where('contract.build.legacy.members.0.rank', 3)
            ->where('contract.build.legacy.members.1.is_guest', true));
});

it('renders a heading whose count matches the rendered step rows', function (): void {
    $fixture = preflightFixture();
    session([SetupDraft::SESSION_KEY => preflightDraft($fixture['trainee'], $fixture['cards'], $fixture['parents'])]);

    test()->get(route('career.preflight'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Preflight')
            // The checklist renders one row per build section (scenario, trainee, target, legacy)
            // plus the deck section, for a total of five.
            ->has('contract.build', 4)
            ->has('contract.deck.slots', 6));
});

it('does not name a purpose the select cannot offer', function (): void {
    test()->get(route('career.target'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/BuildTarget')
            ->has('purposeOptions', 4)
            ->where('purposeOptions.0.label', 'Story Clear')
            ->where('purposeOptions.1.label', 'Champions Meeting')
            ->where('purposeOptions.2.label', 'Parent Farming')
            ->where('purposeOptions.3.label', 'Skill Farming'));
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
it('counts a manual parent pair as two recorded parents', function (): void {
    // A rented-parent pair (UMAMUSUME_REFERENCE.md §1.5.4) has no library rows, so the count that
    // once read "0 of two parents recorded" must read the payload names as the record: the pin
    // is the count itself, not merely the absence of an exception.
    $fixture = preflightFixture();

    $draft = preflightDraft($fixture['trainee'], $fixture['cards'], [null, null], [
        'legacy_selection' => [
            'affinity' => '◎',
            'legacies' => [
                ['rank' => 3, 'is_guest' => false, 'name' => 'Silence Suzuka', 'ancestors' => ['', ''], 'sparks' => []],
                ['rank' => null, 'is_guest' => true, 'name' => 'Tokai Teio', 'ancestors' => ['', ''], 'sparks' => []],
            ],
        ],
    ]);

    session([SetupDraft::SESSION_KEY => $draft]);

    $this->get(route('career.preflight'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('contract.build.legacy.members.0.name', 'Silence Suzuka')
            ->where('contract.build.legacy.members.1.name', 'Tokai Teio')
        );

    // R6-05: the name now survives the commit too. Before the fix the payload reaching this draft had
    // no `name` at all, so the contract's members printed the slot's absence and the created run stored
    // a nameless parent. The run's column is asserted, not the draft's, because the run is what the
    // Inheritance page reads afterwards.
    $this->put(route('career.preflight.store'))->assertRedirect();

    $run = TrainingRun::query()->where('umamusume_id', $fixture['trainee']->id)->sole();
    $selection = $run->legacySelection();

    expect($selection->legacies[0]['name'])->toBe('Silence Suzuka')
        ->and($selection->legacies[1]['name'])->toBe('Tokai Teio');
});

/*
 * S-A-P01.2 — the wizard door seeds the card's starting skills, the way the legacy door already does.
 *
 * Two create doors existed and only one seeded: `TrainingRunController::store()` calls
 * `prePopulateSkills`, `PreflightController::store()` seeded nothing and its docblock said so
 * (`:51`). A Trainer starting from the wizard got an empty Skills section while the same trainee
 * started from the dashboard got her card's list — proposal P-01 §4.1, `SHR-1`.
 */
it('seeds the card starting skills on a career started from the wizard', function (): void {
    $umamusume = Umamusume::factory()->create();

    $unique = Skill::factory()->create([
        'name' => 'Seeded Unique',
        'export_id' => 900001,
        'release_status' => ReleaseStatus::GlobalReleased,
        'name_is_client' => true,
        'is_unique' => true,
    ]);

    $innate = Skill::factory()->create([
        'name' => 'Seeded Innate',
        'export_id' => 900002,
        'release_status' => ReleaseStatus::GlobalReleased,
        'name_is_client' => true,
    ]);

    $card = CharacterCard::factory()->create([
        'umamusume_id' => $umamusume->id,
        'is_debut_form' => true,
        'skills_unique' => [$unique->export_id],
        'skills_innate' => [$innate->export_id],
    ]);

    SetupDraft::write([
        'scenario' => 'ura_finale',
        'umamusume_id' => $umamusume->id,
        'character_card_id' => $card->id,
    ]);

    $this->put(route('career.preflight.store'))->assertRedirect();

    $run = TrainingRun::query()->where('umamusume_id', $umamusume->id)->sole();

    expect($run->skills()->pluck('skills.id')->all())
        ->toHaveCount(2)
        ->toContain($unique->id, $innate->id);

    // `Suggested`, never `Acquired`: a seeded row is the card's implication, not a skill the Trainer
    // earned. The status is what keeps the two distinguishable on the planner (P-01 §7 decision 2).
    expect($run->skills()->get()->pluck('pivot.status')->unique()->all())
        ->toBe([SkillAcquisition::Suggested->value]);
});

it('seeds nothing when the draft names no costume form', function (): void {
    $umamusume = Umamusume::factory()->create();

    SetupDraft::write([
        'scenario' => 'ura_finale',
        'umamusume_id' => $umamusume->id,
        'character_card_id' => null,
    ]);

    $this->put(route('career.preflight.store'))->assertRedirect();

    $run = TrainingRun::query()->where('umamusume_id', $umamusume->id)->sole();

    expect($run->skills()->count())->toBe(0);
});

/*
 * Plan v2 (post-audit) slice 2.4, reshaped: the walk-8 F-06 defect is the sentence, not the
 * payload. `StoreBuildTargetRequest` keeps `build_target` null for a ceilings-only save — that is
 * `ADR-0025`'s implemented contract ("a run may hold ceilings and no target", the request's own
 * docblock), and `BuildTargetPayload::assertKeys()` refuses a seventh key, so a `ceilings_only`
 * payload key is not available to distinguish the states. The distinction therefore travels as a
 * reason the contract states, and Preflight prints that reason where it used to print one sentence
 * for every cause (PA-03's acceptance: "a draft carrying ceilings but no purpose renders a sentence
 * naming what is missing").
 */
it('names ceilings-only and unreadable target drafts instead of calling them unentered', function (): void {
    $fixture = preflightFixture();
    $draft = preflightDraft($fixture['trainee'], $fixture['cards'], $fixture['parents']);
    unset($draft['build_target']);
    $draft['caps'] = ['cap_speed' => 1325];
    session([SetupDraft::SESSION_KEY => $draft]);

    test()->get(route('career.preflight'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Preflight')
            ->where('contract.build.target', null)
            ->where('contract.target.reason', 'Not entered · ceilings only.'));
});

it('names an unreadable stored target rather than a step the Trainer never opened', function (): void {
    $fixture = preflightFixture();
    $draft = preflightDraft($fixture['trainee'], $fixture['cards'], $fixture['parents']);
    $draft['build_target'] = ['purpose' => 'A value the reader refuses'];
    session([SetupDraft::SESSION_KEY => $draft]);

    test()->get(route('career.preflight'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('contract.build.target', null)
            ->where('contract.target.reason', 'A build target was entered, but it cannot be read. Re-enter step 3.'));
});

it('leaves the reason null when no ceilings and no target metadata exist', function (): void {
    $fixture = preflightFixture();
    $draft = preflightDraft($fixture['trainee'], $fixture['cards'], $fixture['parents']);
    unset($draft['build_target']);
    session([SetupDraft::SESSION_KEY => $draft]);

    test()->get(route('career.preflight'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('contract.target.reason', null));
});

it('stores ceilings-only drafts as cap columns with a null build target, per ADR-0025', function (): void {
    $umamusume = Umamusume::factory()->create();

    SetupDraft::write([
        'scenario' => 'ura_finale',
        'umamusume_id' => $umamusume->id,
        'caps' => ['cap_speed' => 1325],
    ]);

    $this->put(route('career.preflight.store'))->assertRedirect();

    $run = TrainingRun::query()->where('umamusume_id', $umamusume->id)->sole();

    expect($run->cap_speed)->toBe(1325)
        ->and($run->build_target)->toBeNull();
});
