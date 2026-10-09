<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The finale as the Career Result reports it (Slice 20, `SCR-CAR-018`).
 *
 * Three answers, and the middle one is the one nothing could previously say: not yet reached,
 * reached with nothing recorded, and recorded. Both facts already exist — the finale is a
 * scenario-scoped mandatory row in the career calendar and the run's own `race_entries` row against
 * it is the outcome — so these cases assert a read, not a computation.
 *
 * The two absences are asserted as absences. A screen that printed an empty placement cell for a
 * finale nobody has run yet would state a finish that does not exist (D-220), so `placement` and
 * `turn` are null in the not-yet-reached case rather than zero or an empty string.
 *
 * The label is asserted as the notice 905 noun. The catalogue row's own title reads
 * "URA Finals Final (Grand Live)" and is filed as KI-82, which is why the payload does not carry it.
 */
function finaleReportingRun(string $scenario = 'our_grand_concert'): TrainingRun
{
    return TrainingRun::factory()->create([
        'scenario' => $scenario,
        'status' => RunStatus::Completed,
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]);
}

function finaleReportingSlot(string $scenarioKey): RaceCatalogSlot
{
    return RaceCatalogSlot::factory()->create([
        'scenario_key' => $scenarioKey,
        'year' => RaceCatalogSlot::YEAR_FINALE,
        'month' => null,
        'turn' => null,
        'slot_label' => 'Finale block',
        'title' => 'Catalogue finale row',
        'is_mandatory' => true,
    ]);
}

it('reports the finale as not yet reached for a career that has not got there', function (): void {
    $run = finaleReportingRun();
    finaleReportingSlot('our_grand_concert');

    $this->get(route('runs.result', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('finale_state.reached', false)
            ->where('finale_state.outcome', null)
            ->where('finale_state.placement', null)
            ->where('finale_state.turn', null)
             ->where('finale_state.label', 'Our Grand Concert')
        );
});

it('reports the finale as reached with no outcome recorded', function (): void {
    $run = finaleReportingRun();
    finaleReportingSlot('our_grand_concert');

    // Turn 71 is the last turn before the block: the next turn to play is Senior turn 24, and the
    // finale block opens at the instant after the grid. One row is enough because the position is
    // read from the highest logged turn, not from a count.
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 71]);

    $this->get(route('runs.result', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('finale_state.reached', true)
            ->where('finale_state.outcome', null)
            ->where('finale_state.placement', null)
        );
});

it('reports the recorded outcome, placement and turn', function (): void {
    $run = finaleReportingRun();
    $slot = finaleReportingSlot('our_grand_concert');

    $turn = TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 71]);

    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $slot->id,
        'turn_entry_id' => $turn->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
    ]);

    $this->get(route('runs.result', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('finale_state.reached', true)
            // The run's own stored word, not a finale-specific one: "Great Success" is the only
            // outcome the client was read stating and the full set is unknown (§7-22).
            ->where('finale_state.outcome', RaceEntryStatus::Completed->label())
            ->where('finale_state.placement', '1st')
            ->where('finale_state.turn', 71)
        );
});

it('emits no finale at all for a scenario whose calendar carries none', function (): void {
    $run = finaleReportingRun('ura_finale');

    $this->get(route('runs.result', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('finale_state', null)
            // The old key is gone rather than left standing as a null, which is how a reader of this
            // payload tells a scenario with no finale row from a payload that never carried the key.
            ->missing('finale')
        );
});

it('emits the same finale state on the cockpit payload', function (): void {
    $run = finaleReportingRun();
    $slot = finaleReportingSlot('our_grand_concert');

    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 71]);
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 2,
    ]);

    // `finale_state` on this payload too: Slice 23 gave the two screens one word for the reader's
    // output, and moved the config's own declaration to `scenario.finale_structure`, because a
    // structure and a reading are two questions and plain `finale` had been answering both.
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.finale_state.reached', true)
            ->where('scenario.finale_state.outcome', RaceEntryStatus::Completed->label())
            ->where('scenario.finale_state.placement', '2nd')
             ->where('scenario.finale_state.label', 'Our Grand Concert')
        );
});

it('emits no cockpit finale state for a scenario whose calendar carries none', function (): void {
    $run = finaleReportingRun('ura_finale');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.finale_state', null)
        );
});
