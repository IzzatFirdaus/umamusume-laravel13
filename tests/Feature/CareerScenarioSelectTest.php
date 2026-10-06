<?php

declare(strict_types=1);

use App\Services\Career\SetupDraft;
use App\Services\ScenarioCaps;
use Inertia\Testing\AssertableInertia as Assert;

// SCREEN-002 (`ADR-0020` §1). The props are asserted here; rendered copy, the keyboard path, the
// 44px sweep and 320px reflow live in `tests/browser/career-scenario-select.spec.ts`
// (plan §2 convention 2). The wizard writes to the session draft, never to a run, so no case here
// needs a `TrainingRun` row at all.

it('renders one card per scenario the matrix composes, each derived from config', function (): void {
    /** @var array<string, array<string, mixed>> $scenarios */
    $scenarios = config('scenarios.scenarios');

    $this->get('/career/setup/scenario')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/ScenarioSelect')
            ->has('scenarios', count($scenarios))
            ->where('scenarios.0.key', array_key_first($scenarios))
            ->where('scenarios.0.label', config('scenarios.scenarios.ura_finale.label'))
            ->where('selected', null)
            // A ruleset version is not sourced, so the card carries the absence and its reason.
            ->where('ruleset.value', null)
            ->has('ruleset.title')
            // The two brief fields the corpus holds nothing for, named rather than filled.
            ->where('scenarios.0.description', null)
            ->where('scenarios.0.recommended_use', null));
});

it('reads the optimization focus off cap_bonus and calls a tie a tie', function (): void {
    // URA Finale is +200 across all five stats. Naming one "primary" among five equal bonuses would
    // be inventing a preference the matrix does not express, so the derivation says "uniform".
    $this->get('/career/setup/scenario')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenarios.0.optimizes.kind', 'uniform')
            ->where('scenarios.0.optimizes.bonus', 200)
            ->where('scenarios.0.optimizes.stats', ['Speed', 'Stamina', 'Power', 'Guts', 'Wit'])
            // Unity Cup pays +600 Wit against +100 elsewhere, so Wit is the one peak.
            ->where('scenarios.1.optimizes.kind', 'peaked')
            ->where('scenarios.1.optimizes.stats', ['Wit'])
            ->where('scenarios.1.optimizes.bonus', 600)
            // Trackblazer pays Stamina highest, Our Grand Concert Speed highest.
            ->where('scenarios.2.optimizes.stats', ['Stamina'])
            ->where('scenarios.3.optimizes.stats', ['Speed']));
});

it('names each scenario systems and resources from the matrix rather than from a scenario name', function (): void {
    $this->get('/career/setup/scenario')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenarios.0.systems', ['Race calendar'])
            // URA composes exactly the baseline widget set, so it claims no scenario resource.
            ->where('scenarios.0.resources', [])
            ->where('scenarios.1.systems', ['Race calendar', 'Team races', 'Team Rank ladder'])
            ->where('scenarios.1.resources', ['Team Rank', 'Spirit Bursts'])
            ->where('scenarios.2.systems', ['Grade Point objectives', 'Pro Shop', 'Epithet routes'])
            ->where('scenarios.2.resources', ['Grade Points', 'Shop Coins'])
            ->where('scenarios.2.loop', ['training', 'shop', 'outcome', 'skill'])
            ->where('scenarios.2.live_on_global', '2026-03-12'));
});

it('derives the documentation badge from the matrix flag, not from a scenario name', function (): void {
    // The card's baseline state for Our Grand Concert is a derivation, not a branch: its entry composes
    // no panel beyond the baseline and no widget beyond the baseline three, so the system and resource
    // lists come back empty on their own (D-241, gate G-41).
    //
    // `documented` is read as it is stored. It is `true` here because commit `ab53861` recovered the
    // 2026-10-05 primary read for the scenario, which is what flipped the flag; the slice brief was
    // written while it read `false`. The badge mechanism is therefore proven against a falsified copy
    // of the matrix below rather than against the contested current value, so the component contract
    // holds whichever way the owner rules on the flag.
    $this->get('/career/setup/scenario')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenarios.3.key', 'our_grand_concert')
            ->where('scenarios.3.documented', true)
            ->where('scenarios.3.systems', [])
            ->where('scenarios.3.resources', []));

    /** @var array<string, array<string, mixed>> $matrix */
    $matrix = config('scenarios.scenarios');
    config(['scenarios.scenarios.our_grand_concert.documented' => false]);

    $this->get('/career/setup/scenario')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('scenarios.3.documented', false));

    config(['scenarios.scenarios' => $matrix]);
});

it('stores the chosen scenario in the draft and reads it back on the next render', function (): void {
    $this->put('/career/setup/scenario', ['scenario' => 'trackblazer'])
        ->assertRedirect(route('career.scenario'));

    expect(SetupDraft::read()['scenario'])->toBe('trackblazer')
        ->and(SetupDraft::scenarioLabel())->toBe('Trackblazer');

    // The page re-reads the draft, so the radio reflects stored state rather than client memory.
    $this->get('/career/setup/scenario')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('selected', 'trackblazer'));
});

it('carries the chosen scenario across navigation to another screen and back', function (): void {
    // WCAG 3.3.7 redundancy and the slice acceptance in one move: leaving the wizard for the
    // Dashboard and returning must not ask the Trainer to choose again.
    $this->put('/career/setup/scenario', ['scenario' => 'unity_cup'])->assertRedirect();
    $this->get('/')->assertOk();

    $this->get('/career/setup/scenario')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('selected', 'unity_cup'));

    // And the draft is still intact after the trip, which is the property a client-only selection
    // would not have.
    expect(SetupDraft::read()['scenario'])->toBe('unity_cup');
});

it('refuses a scenario the matrix does not compose and leaves the draft untouched', function (): void {
    $this->put('/career/setup/scenario', ['scenario' => 'grand_masters'])
        ->assertSessionHasErrors('scenario');

    expect(SetupDraft::read()['scenario'])->toBeNull();

    $this->put('/career/setup/scenario', [])->assertSessionHasErrors('scenario');
});

it('reads a stale draft key as no scenario rather than as a blank card', function (): void {
    // A config change can leave a stored key that composes nothing. Rendering it would print a card
    // with no label, so `SetupDraft::read()` validates the key against the matrix on the way out.
    session([SetupDraft::SESSION_KEY => ['scenario' => 'withdrawn_scenario', 'umamusume_id' => null]]);

    $this->get('/career/setup/scenario')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('selected', null));

    expect(SetupDraft::scenarioLabel())->toBeNull();
});

it('adds a fifth scenario with one config entry and no component edit', function (): void {
    /** @var array<string, array<string, mixed>> $matrix */
    $matrix = config('scenarios.scenarios');

    // The gate G-33 claim made testable: a scenario nobody wrote a branch for still renders, with a
    // peak, a system list and a resource list read from its own entry.
    config(['scenarios.scenarios.fifth_scenario' => [
        'label' => 'Fifth Scenario',
        'live_on_global' => '2027-01-01',
        'cap_bonus' => ['Speed' => 100, 'Stamina' => 100, 'Power' => 500, 'Guts' => 100, 'Wit' => 100],
        'widgets' => ['turn', 'energy', 'fans', 'shop_coins'],
        'steps' => ['training', 'outcome', 'skill'],
        'panels' => [
            'race_calendar' => false,
            'team_race' => false,
            'grade_objectives' => false,
            'shop' => true,
            'epithet_routes' => false,
            'team_rank_ladder' => false,
        ],
        'scenario_links' => [],
        'facility_level_source' => 'repetition',
        'notes' => 'A test fixture, not a shipped scenario.',
    ]]);

    $this->get('/career/setup/scenario')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('scenarios', count($matrix) + 1)
            ->where('scenarios.4.key', 'fifth_scenario')
            ->where('scenarios.4.label', 'Fifth Scenario')
            ->where('scenarios.4.optimizes.stats', ['Power'])
            ->where('scenarios.4.optimizes.bonus', 500)
            ->where('scenarios.4.systems', ['Pro Shop'])
            ->where('scenarios.4.resources', ['Shop Coins'])
            ->where('scenarios.4.documented', null));

    config(['scenarios.scenarios' => $matrix]);
});

it('gives a planning run to the ceiling owner only once a scenario is chosen', function (): void {
    // The Build Target step clamps against the chosen scenario before any run exists.
    // `ScenarioCaps::forRun()` reads only `hasScenario()` and `scenarioKey()`, so an unsaved model
    // answers it. Null when no scenario is chosen, which refuses to lend any scenario's cap bonus.
    expect(SetupDraft::planningRun())->toBeNull();

    SetupDraft::write(['scenario' => 'trackblazer']);

    $run = SetupDraft::planningRun();

    expect($run)->not->toBeNull()
        ->and($run?->exists)->toBeFalse()
        ->and($run?->scenarioKey())->toBe('trackblazer')
        ->and(ScenarioCaps::forRun($run)['Stamina'])->toBe(1900);
});
