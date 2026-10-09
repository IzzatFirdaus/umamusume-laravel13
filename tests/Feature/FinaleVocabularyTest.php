<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use App\Services\Advisor\TrainerAdvisor;
use Inertia\Testing\AssertableInertia as Assert;

 /*
 * The finale's payload vocabulary, sourced from `app/Services/Scenario/FinaleReader.php` (the one
 * reader behind `finale_state` on the Cockpit at `CockpitController.php:678` and `finale` on the
 * Career Result at `ResultController.php:72`), and the determinations recorded in the plan's
 * Slice 19 and Slice 20 bodies at `docs/Our-Grand-Concert-plan.md:786` and `:811`.
 *
 * Before this commit one word answered two questions on two screens. `scenario.finale` on the Cockpit
 * was what `config/scenarios.php` declares a scenario composes, and it is null for three of the four
 * `[Global]` scenarios; `finale` on the Career Result was this run's own position, read from the
 * catalogue by `FinaleReader`. Two readers of one payload family could take a null structure for "this
 * career has no finale", which is the false absence `CockpitController::finaleAbsence()` exists to
 * refuse, and the Result screen's `finale` was silently a different object from the Cockpit's.
 *
 * Now: the reading is `finale_state` everywhere, the declaration is `scenario.finale_structure` on the
 * surface that carries it, and the config's own spelling stays `finale` because that key is a layout
 * input, not a payload (`AGENTS.md` §7). The advisor's proximity line keeps the short word: it lives
 * under `advisor`, beside action, band, reasons, alternative and risk, and it is a shape of its own
 * (label, state, turns_away), so the collision it could not cause is the one it is not named for.
 *
 * These cases pin the naming and the agreement, which is what a rename is worth when it is worth
 * anything: not that the keys changed, but that one run now reads the same words on both screens.
 */

function finaleVocabularyRun(string $scenario = 'our_grand_concert'): TrainingRun
{
    return TrainingRun::factory()->create([
        'scenario' => $scenario,
        'status' => RunStatus::Completed,
        'umamusume_id' => Umamusume::factory()->state(['name' => 'Rice Shower', 'name_ja' => 'ライスシャワー']),
    ]);
}

function finaleVocabularySlot(string $scenarioKey): RaceCatalogSlot
{
    return RaceCatalogSlot::factory()->create([
        'scenario_key' => $scenarioKey,
        'year' => RaceCatalogSlot::YEAR_FINALE,
        'month' => null,
        'half' => null,
        'turn' => null,
        'title' => 'Catalogue finale row',
        'tier' => 'G1',
        'distance' => 2400,
        'distance_band' => 'Long',
        'surface' => 'Turf',
        'is_mandatory' => true,
        'sort_order' => 1,
    ]);
}

it('names the reading `finale_state` and drops the bare `finale` on the Career Result', function (): void {
    $run = finaleVocabularyRun();
    finaleVocabularySlot('our_grand_concert');

    $this->get(route('runs.result', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Result')
            ->has('finale_state')
            ->has('finale_state.reached')
            ->has('finale_state.outcome')
            ->has('finale_state.placement')
            ->has('finale_state.turn')
            ->has('finale_state.label')
            ->missing('finale')
            // The Result payload never carried the declaration, and the rename does not add one: the
            // structure belongs to the screen that composes the scenario's panels.
            ->missing('finale_structure')
            ->missing('scenario.finale')
            ->missing('scenario.finale_structure'));
});

it('names the declaration `finale_structure` and drops the bare `finale` on the Cockpit', function (): void {
    $run = finaleVocabularyRun('trackblazer');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Cockpit')
            // The one scenario whose config entry declares a structure, read verbatim.
            ->where('scenario.finale_structure.kind', 'points_league')
            ->where('scenario.finale_structure.races', 3)
            ->missing('scenario.finale')
            // And the reading is still there beside it, under the word the Result screen uses too.
            ->has('scenario.finale_state'));
});

it('keeps the config spelling while the payload renames', function (): void {
    // `config/scenarios.php` is the only place scenario names enter the layout path (AGENTS.md §7) and
    // its `finale` key is a config input, not a wire format. Proved from the config side as well as the
    // payload side, so the rename cannot be mistaken for an edit to the declared data.
    expect(config('scenarios.scenarios.trackblazer.finale'))->toBe(['kind' => 'points_league', 'races' => 3])
        ->and(config('scenarios.scenarios.trackblazer.finale_structure'))->toBeNull();
});

it('reads one run the same way on both screens', function (): void {
    $run = finaleVocabularyRun();
    $slot = finaleVocabularySlot('our_grand_concert');
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 71]);
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 2,
        'turn_entry_id' => $run->turnEntries()->where('turn', 71)->firstOrFail()->id,
    ]);

    // The shell's `data-page` payload is read rather than asserted key by key, because what the rename
    // owes is that the two screens hand back the same array, and that is a comparison rather than a
    // list of expectations (`CatalogRosterTreeTest` reads a payload the same way).
    $result = $this->get(route('runs.result', $run))->assertOk()->viewData('page')['props']['finale_state'];
    $cockpit = $this->get(route('runs.cockpit', $run))->assertOk()->viewData('page')['props']['scenario']['finale_state'];

    // Not "both non-null": the same array, key for key, which is what two call sites of one reader owe
    // the reader (`ADR-0015`: one owner per number, and here one owner per position).
    expect($result)->toBe($cockpit)
        ->and($result['outcome'])->toBe(RaceEntryStatus::Completed->label())
        ->and($result['placement'])->toBe('2nd')
        ->and($result['turn'])->toBe(71)
        ->and($result['reached'])->toBeTrue();
});

it('leaves the advisor proximity line under `advisor` and apart from the state', function (): void {
    // A third `finale` exists on the Cockpit payload and stays where it is: it is the advisor's own
    // section, its shape is a proximity (`label`, `state`, `turns_away`) rather than an outcome, and
    // renaming it would move a key that never collided with anything. Asserted so that the next reader
    // of this file finds the boundary stated rather than guessed.
    $run = finaleVocabularyRun();
    $slot = finaleVocabularySlot('our_grand_concert');

    for ($turn = 68; $turn <= 71; $turn++) {
        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => $turn]);
    }

    $advisor = $this->get(route('runs.cockpit', $run))->assertOk()->viewData('page')['props']['advisor'];

    expect(array_keys($advisor))->toBe(['action', 'band', 'reasons', 'alternative', 'risk', 'finale'])
        ->and($advisor['finale'])->not->toBeNull()
        ->and($advisor['finale'])->toHaveKeys(['label', 'state', 'turns_away'])
        ->and($advisor['finale'])->not->toHaveKey('outcome')
        ->and($advisor['finale'])->not->toHaveKey('reached')
        // The line is a proximity inside the window the engine publishes, not a coincidence of this
        // fixture: four logged turns of the last year puts the run inside FINALE_TURNS_AHEAD, and the
        // word for one turn out is `next` rather than a count.
        ->and($advisor['finale']['turns_away'])->toBeBetween(1, TrainerAdvisor::FINALE_TURNS_AHEAD)
        ->and($advisor['finale']['state'])
            ->toBe($advisor['finale']['turns_away'] === 1 ? 'next' : 'upcoming');
});
