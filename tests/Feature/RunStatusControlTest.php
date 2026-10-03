<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;

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

    $html = $this->get(route('runs.show', $run))->assertOk()->content();

    foreach (['Active', 'Completed', 'Retired'] as $label) {
        expect($html)->toContain('>'.$label.'<');
    }

    // The current value is the selected one, so pressing Save without touching the
    // select is a no-op rather than a silent reset to Active.
    expect(preg_match('/value="Completed"[^>]*selected/', $html))->toBe(1)
        ->and(preg_match('/value="Active"[^>]*selected/', $html))->toBe(0);
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

    $html = $this->get(route('runs.show', $run))->content();

    // Read by which form holds the select: the race panel ships its own `<select name="status">`
    // (components/race-panel.blade.php:144) and it comes earlier in the document, so a search for
    // the first one on the page finds the wrong control.
    preg_match_all(
        '#<form[^>]*action="'.preg_quote(route('runs.update', $run), '#').'"[^>]*>(.*?)</form>#s',
        $html,
        $forms,
    );

    $statusForm = null;

    foreach ($forms[1] as $body) {
        if (str_contains($body, '<select name="status"')) {
            $statusForm = $body;
        }
    }

    expect($statusForm)->not->toBeNull('the run page has no status select inside a run update form');

    // The shared request needs the fields this form is not editing, which is why the two
    // existing update forms carry them hidden. A status form that dropped them would blank
    // the run, so the carry-through is part of the control, not incidental markup.
    expect($statusForm)
        ->toContain('name="_method" value="PUT"')
        ->toContain('name="umamusume_id"')
        ->toContain('name="scenario"')
        ->not->toContain('name="current_objective_index"');
});
