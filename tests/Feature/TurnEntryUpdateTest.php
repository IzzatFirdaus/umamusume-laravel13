<?php

declare(strict_types=1);

use App\Enums\EnergyState;
use App\Enums\RunStatus;
use App\Enums\TurnEventType;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * R2-16: a turn's Energy band is editable through the Cockpit's manual correction.
 *
 * The band was silently dropped on correction. `StoreTurnEntryRequest` accepted `energy_state` and
 * `energy_band` and `TrainingRunController::updateTurn()` persisted `validated()` unfiltered, but the
 * Cockpit's correction form neither rendered a control nor posted the two keys, and
 * `CockpitController::correctionSection()` did not send them, so there was nothing to prefill from.
 * These four cases pin the guided write, the prefill, the independence of the number from the band,
 * and the clearing rule the state carries.
 */

/**
 * A run with one logged turn, its Energy state and band set to what the case needs.
 *
 * Local rather than the suite's `cockpitRun()`: a top-level function declared in another test file is
 * not loaded when this file runs alone, so a shared name would pass in a full run and fail in a
 * focused one (which is how this case was first written and first failed).
 */
function energyCorrectionRun(?EnergyState $state = null, ?string $band = null): TrainingRun
{
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale', 'status' => RunStatus::Active]);

    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'speed' => 600,
        'stamina' => 600,
        'power' => 600,
        'guts' => 600,
        'wit' => 600,
        'sp' => 120,
        'energy' => 60,
        'fans' => 1000,
        'energy_state' => $state,
        'energy_band' => $band,
    ]);

    return $run;
}

/** The five stats both turn writes require, so a case states only what it is actually changing. */
function correctionPayload(array $overrides = []): array
{
    return array_merge([
        'turn' => 1,
        'speed' => 600,
        'stamina' => 600,
        'power' => 600,
        'guts' => 600,
        'wit' => 600,
    ], $overrides);
}

it('records a band through the correction and reads it back', function (): void {
    $run = energyCorrectionRun();
    $turn = $run->turnEntries()->firstOrFail();

    $this->from(route('runs.cockpit', $run))
        ->put(route('runs.turns.update', [$run, $turn]), correctionPayload([
            'energy_state' => 'band',
            'energy_band' => 'high',
        ]))
        ->assertSessionHasNoErrors();

    $fresh = $turn->fresh();

    expect($fresh->energy_state)->toBe(EnergyState::Band)
        ->and($fresh->energy_band)->toBe('high');
});

it('stores the band the guided form posts when the turn is first recorded', function (): void {
    // The guided screen (`Career/TrainingDetail.vue`) posts the same two keys the correction does, to the
    // same Form Request, but through `runs.turns.store`, whose write is filtered by `turnAttributes()`.
    // A key the allow-list omits is dropped at the door without a word, so the band a Trainer picked on
    // the guided screen has to be pinned here rather than assumed from the request's own rules:
    // `EnergyStateTest` builds its rows with the factory, which bypasses the write path entirely.
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale', 'status' => RunStatus::Active]);

    $this->post(route('runs.turns.store', $run), correctionPayload([
        'energy_state' => 'band',
        'energy_band' => 'low',
        'choice' => 'training-Speed',
        'outcome' => 'Success',
    ]))->assertRedirect(route('runs.cockpit', $run));

    $fresh = $run->turnEntries()->where('turn', 1)->firstOrFail();

    expect($fresh->energy_state)->toBe(EnergyState::Band)
        ->and($fresh->energy_band)->toBe('low');
});

it('sends the band for prefill and changes it through the Cockpit correction', function (): void {
    $run = energyCorrectionRun(EnergyState::Band, 'low');
    $turn = $run->turnEntries()->firstOrFail();

    // The form prefills from `correction.values`, so the stored band has to reach the page before a
    // Trainer can change it; without this key the control would open on the default every time.
    $this->get(route('runs.cockpit', $run))
        ->assertInertia(fn (Assert $page) => $page
            ->where('correction.values.energy_state', 'band')
            ->where('correction.values.energy_band', 'low'));

    $this->from(route('runs.cockpit', $run))
        ->put(route('runs.turns.update', [$run, $turn]), correctionPayload([
            'energy_state' => 'band',
            'energy_band' => 'mid',
        ]))
        ->assertSessionHasNoErrors();

    expect($turn->fresh()->energy_band)->toBe('mid');
});

it('leaves the band in place when only the numeric energy is corrected', function (): void {
    $run = energyCorrectionRun(EnergyState::Band, 'high');
    $turn = $run->turnEntries()->firstOrFail();

    // A correction that touches the number and re-posts the state it read must not lose the band: the
    // form carries both keys on every save, so the stored word survives a change it did not make.
    $this->from(route('runs.cockpit', $run))
        ->put(route('runs.turns.update', [$run, $turn]), correctionPayload([
            'energy' => 72,
            'energy_state' => 'band',
            'energy_band' => 'high',
        ]))
        ->assertSessionHasNoErrors();

    $fresh = $turn->fresh();

    expect($fresh->energy)->toBe(72)
        ->and($fresh->energy_state)->toBe(EnergyState::Band)
        ->and($fresh->energy_band)->toBe('high');
});

it('clears the previous band when the state becomes not recorded', function (): void {
    $run = energyCorrectionRun(EnergyState::Band, 'high');
    $turn = $run->turnEntries()->firstOrFail();

    // The client posts the band as empty whenever the state does not name it, so `validated()` carries
    // `energy_band => null` and the stale word is cleared. Posting `energy_state` alone would leave the
    // old band on the row, which is why the form always sends both keys.
    $this->from(route('runs.cockpit', $run))
        ->put(route('runs.turns.update', [$run, $turn]), correctionPayload([
            'energy_state' => 'unknown',
            'energy_band' => '',
        ]))
        ->assertSessionHasNoErrors();

    $fresh = $turn->fresh();

    expect($fresh->energy_state)->toBe(EnergyState::Unknown)
        ->and($fresh->energy_band)->toBeNull();
});

/*
 * S1.14: the two keys a correction accepts but cannot store as columns.
 *
 * `turn` is the row's identity, not one of its attributes. `turn_events` and `race_entries` address a
 * turn by NUMber with no `turn_entry_id`, so moving a row's number is a renumber, and a renumber that
 * only rewrites `turn_entries` strands both event tables at the old number. The store door documented
 * that rule; the correction door simply wrote `validated()` wholesale. `StoreTurnEntryRequest` now
 * refuses a changed number and prohibits the three keys the correction path has no column and no event
 * for, so the value is refused rather than dropped in silence. Both halves are pinned here because a
 * refusal that is not asserted is a refusal the next edit can delete freely.
 */
it('refuses a correction that moves a turn to another number', function (): void {
    $run = energyCorrectionRun();
    $turn = $run->turnEntries()->firstOrFail();

    // A failure event at the number the row currently holds: if the renumber were allowed, this event
    // would be left behind addressing a turn that no longer exists.
    $run->turnEvents()->create([
        'turn' => 1,
        'event_type' => TurnEventType::Failure,
        'source_name' => 'Train Speed',
        'choice_label' => 'Train Speed',
        'deltas' => ['penalty_kind' => 'mood', 'recorded' => []],
        'origin_note' => 'fixture',
    ]);

    $this->from(route('runs.cockpit', $run))
        ->put(route('runs.turns.update', [$run, $turn]), correctionPayload(['turn' => 2]))
        ->assertSessionHasErrors('turn');

    // Nothing moved: the row is still turn 1 and so is its event.
    expect($turn->fresh()->turn)->toBe(1)
        ->and($run->turnEvents()->where('turn', 1)->count())->toBe(1)
        ->and($run->turnEvents()->where('turn', 2)->count())->toBe(0);
});

it('refuses a correction that carries a key the correction path cannot store', function (): void {
    $run = energyCorrectionRun();
    $turn = $run->turnEntries()->firstOrFail();

    // `performance` is a `Scenario` event written by `storeTurn`, not a column. Posting it to the
    // correction door used to pass validation and vanish (preventSilentlyDiscardingAttributes is off),
    // so the Trainer saw a success for a change nothing recorded.
    foreach ([['performance' => ['type' => 'mood', 'delta' => 1]], ['stage' => 'review'], ['previewed' => '1']] as $payload) {
        $this->from(route('runs.cockpit', $run))
            ->put(route('runs.turns.update', [$run, $turn]), correctionPayload($payload))
            ->assertSessionHasErrors(array_key_first($payload));
    }

    // No `Scenario` event was invented, and the row itself is untouched.
    expect($run->turnEvents()->where('event_type', TurnEventType::Scenario)->count())->toBe(0)
        ->and($turn->fresh()->speed)->toBe(600);
});
