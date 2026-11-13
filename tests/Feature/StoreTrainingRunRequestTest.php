<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Umamusume;

/*
 * Phase 1.1 step 1 — the scenario-switch boundary of `StoreTrainingRunRequest`, at `runs.update`.
 *
 * `scenario` and `current_objective_index` are two columns of one form (`Career/RunSettings.vue`), and the
 * validator refuses a period on a scenario that composes no Result Point objectives. These cases pin the
 * two branches that exist **today**, so the KI-162 repair can be measured against them rather than
 * against a description of them.
 *
 * **What these cases deliberately do not decide.** Whether a switch SHOULD clear the period or SHOULD be
 * refused is the owner's ruling (`Phase 1.1` gate). Both behaviours are recorded here as measured; no
 * case asserts a preference, and no production file is changed by this file.
 *
 * Measured on `274eadfc`+working tree: (a) passes, (b) is refused.
 */

/**
 * A Trackblazer run whose Trainer has reported a Result Point period.
 */
function switchingRun(): TrainingRun
{
    return TrainingRun::factory()->create([
        'scenario' => 'trackblazer',
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create()->id,
        'current_objective_index' => 2,
    ]);
}

it('switches scenario when the run carries no recorded period', function (): void {
    // The branch a corrected form would reach: a scenario change with no period stored anywhere. This is
    // the only shape in which a switch to a non-composing scenario lands today.
    $run = TrainingRun::factory()->create([
        'scenario' => 'trackblazer',
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create()->id,
        'current_objective_index' => null,
    ]);

    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'status' => $run->status->value,
        'scenario' => 'unity_cup',
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($run->refresh()->scenario)->toBe('unity_cup');
});

it('cannot switch scenario even when the payload omits the period, because the model guard reads the stored value', function (): void {
    // **Second layer, measured directly.** The validator passes here — no `current_objective_index` key is
    // sent, so `StoreTrainingRunRequest` has nothing to judge — and the write still fails, because
    // `TrainingRun::booted()` calls `assertGradePeriod()` against the model's own still-set period and the
    // scenario being written. The exception surfaces as a 500, not a validation redirect.
    //
    // This is why a fix cannot live in the form alone: the period must be cleared in the SAME update that
    // changes the scenario, or the guard refuses the intermediate state.
    $run = switchingRun();

    $this->withoutExceptionHandling();

    expect(fn () => $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'status' => $run->status->value,
        'scenario' => 'unity_cup',
    ]))->toThrow(InvalidArgumentException::class, 'composes no Result Point objectives');

    expect($run->refresh()->scenario)->toBe('trackblazer');
});

it('refuses a switch that carries a period the target scenario cannot hold, and writes nothing', function (): void {
    // The KI-162 shape, pinned. `RunSettings.vue:70-75` spreads one `base` object into all three forms,
    // so the scenario form resends the period it is not editing and the switch is refused.
    $run = switchingRun();

    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'status' => $run->status->value,
        'scenario' => 'unity_cup',
        'current_objective_index' => '2',
    ])->assertSessionHasErrors('current_objective_index');

    $run->refresh();

    expect($run->scenario)->toBe('trackblazer')
        ->and($run->current_objective_index)->toBe(2);
});

it('accepts a switch that stays inside a scenario which composes periods', function (): void {
    // The guard is the TARGET scenario's matrix flag, not the source's: a Trackblazer-to-Trackblazer
    // write carrying a period is legitimate and must not be caught by the same rule.
    $run = switchingRun();

    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'status' => $run->status->value,
        'scenario' => 'trackblazer',
        'current_objective_index' => '3',
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($run->refresh()->current_objective_index)->toBe(3);
});
