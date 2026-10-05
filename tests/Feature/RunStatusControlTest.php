<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * SCREEN_SPEC.md §7-4: a run could enter as Active, Completed or Retired and never leave that
 * value, because both update forms on the run page carried `status` as a hidden input whose only
 * job was to satisfy the shared request's `required` rule (show.blade.php:264, :291). Retiring a
 * finished run meant editing the database by hand, while FR-C-4 claims runs are editable through
 * the web UI.
 *
 * The server side already refused a value outside RunStatus (`StoreTrainingRunRequest:52`), so
 * the gap was the control, not the rule. The tests below therefore pin the transitions a Trainer
 * can now make, and that a hand-built bad value is still refused rather than stored.
 */

function statusRun(RunStatus $status = RunStatus::Active): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => 'ura_finale', 'status' => $status]);
}

it('offers the three statuses and marks the run\'s current one', function (): void {
    $run = statusRun(RunStatus::Completed);

    test()->get(route('runs.show', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            // The options the status select renders, value => label, built from `RunStatus::cases()`.
            ->where('statuses', ['Active' => 'Active', 'Completed' => 'Completed', 'Retired' => 'Retired'])
            // The current value is what the select binds to, so pressing Save without touching the
            // select is a no-op rather than a silent reset to Active.
            ->where('run.status', 'Completed'));
});

it('moves a run through each status transition the select offers', function (RunStatus $from, RunStatus $to): void {
    $run = statusRun($from);

    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => $run->scenario,
        'status' => $to->value,
    ])->assertRedirect(route('runs.show', $run));

    expect($run->refresh()->status)->toBe($to);
})->with([
    'Active to Completed' => [RunStatus::Active, RunStatus::Completed],
    'Completed to Retired' => [RunStatus::Completed, RunStatus::Retired],
    'Retired back to Active' => [RunStatus::Retired, RunStatus::Active],
]);

it('refuses a status outside the enum and leaves the run where it was', function (): void {
    $run = statusRun(RunStatus::Active);

    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => $run->scenario,
        'status' => 'OnHold',
    ])->assertSessionHasErrors('status');

    expect($run->refresh()->status)->toBe(RunStatus::Active);
});

it('changes the status without blanking the fields the select is not editing', function (): void {
    $run = statusRun();
    $run->update(['notes' => 'Kept across a status change']);

    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => $run->scenario,
        'status' => RunStatus::Retired->value,
    ])->assertRedirect(route('runs.show', $run));

    $fresh = $run->refresh();

    expect($fresh->status)->toBe(RunStatus::Retired)
        ->and($fresh->notes)->toBe('Kept across a status change')
        ->and($fresh->scenario)->toBe('ura_finale')
        ->and($fresh->umamusume_id)->toBe($run->umamusume_id);
});

it('submits the status form with the run\'s own carried fields', function (): void {
    $run = statusRun();

    test()->get(route('runs.show', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            // The shared request needs the fields this form is not editing, which is why the two
            // existing update forms carry them. A status form that dropped them would blank the run,
            // so the payload ships the run's own values and the action the form posts to.
            ->where('run.update_url', route('runs.update', $run))
            ->where('run.umamusume_id', $run->umamusume_id)
            ->where('run.scenario', 'ura_finale'));
});
