<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * D-1: the first turn of a run, through the guided rail.
 *
 * The deadlock, in three layers that all read the same thing:
 *
 *   StoreTurnEntryRequest:78   a confirm stage requires `previewed`
 *     ^ the rail rendered its confirm control only when the delta list was non-empty
 *       ^ previewDeltas() returns [] when there is no previous row
 *         ^ a run's first turn has no previous row
 *
 * So the first turn of every run could not be committed through the rail. It was not a
 * data-loss bug: the raw escape hatch beside the rail still records a turn, and that is
 * exactly why it survived - GuidedTurnOnRunViewTest's confirm test runs against a run that
 * already has turn 1, so no existing test ever previewed turn 1.
 *
 * The fix is in the controller, not the view: an empty delta list meant both "this is the
 * plain GET" and "this is a preview of a first turn", and the rail read the first. Those
 * are now two named states (`rail.previewed` beside `rail.preview`), and the confirm gate
 * follows the state rather than the list. The screen is Inertia now (ADR-0020), so these
 * assert that state; the rendered copy is in tests/browser/run-detail.spec.ts.
 *
 * D-1 was fixed rather than characterized: the old behaviour contradicted D-53, which
 * requires the escape hatch to be reachable rather than default, and nothing here pins it.
 */

function firstTurnRun(): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => 'ura_finale']);
}

function firstTurnPayload(array $overrides = []): array
{
    return array_merge([
        'turn' => 1,
        'speed' => 120,
        'stamina' => 110,
        'power' => 130,
        'guts' => 100,
        'wit' => 95,
        'sp' => 20,
        'energy' => 70,
        'mood' => 'GREAT',
        'fans' => 1200,
        'choice' => 'training-Speed',
        'outcome' => 'Success',
        'stage' => 'preview',
    ], $overrides);
}

it('offers the confirm stage on a preview of the very first turn', function (): void {
    $run = firstTurnRun();
    $payload = firstTurnPayload();

    test()->post("/training-runs/{$run->id}/turns", $payload)
        ->assertRedirect(route('runs.show', $run));

    // PRG: the previewed screen is the redirect target, not the POST body. The controller
    // flashes the submitted input plus the `previewed` flag it was told to set, so the
    // carry below is what the browser actually arrives with.
    test()
        ->withSession(['_old_input' => $payload + ['previewed' => '1']])
        ->get(route('runs.show', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            // A first turn previews to an empty delta list by arithmetic, so the only thing
            // that can carry "you have previewed this" is the flag itself.
            ->where('rail.preview', [])
            ->where('rail.previewed', true)
            ->where('rail.current', 'outcome'));

    expect(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe(0);
});

it('says why the first turn has no deltas, rather than showing an empty preview', function (): void {
    $run = firstTurnRun();
    $payload = firstTurnPayload();

    test()->post("/training-runs/{$run->id}/turns", $payload)
        ->assertRedirect(route('runs.show', $run));

    // D-220: an absent figure is a labelled absence. Five zeroed deltas, or a blank panel
    // with no explanation, would both be structure claiming to be data. The empty list and
    // `has_previous` false are what render the first-turn note, in
    // tests/browser/run-detail.spec.ts.
    test()
        ->withSession(['_old_input' => $payload + ['previewed' => '1']])
        ->get(route('runs.show', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('rail.preview', [])
            ->where('rail.has_previous', false));
});

it('commits the first turn through the rail without touching the raw form', function (): void {
    $run = firstTurnRun();

    $previewed = firstTurnPayload();
    $previewed['previewed'] = '1';
    $previewed['stage'] = 'confirm';

    test()->post("/training-runs/{$run->id}/turns", $previewed)->assertRedirect();

    // The point of the fix, asserted on the row rather than on the markup: the whole
    // sequence above went through the rail, and the run has its first turn.
    expect(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe(1);

    $entry = TurnEntry::sole();

    expect($entry->turn)->toBe(1)
        ->and($entry->speed)->toBe(120)
        ->and($entry->energy)->toBe(70);
});

it('leaves a second turn previewing its deltas as before', function (): void {
    $run = firstTurnRun();
    TurnEntry::create([
        'training_run_id' => $run->id, 'turn' => 1, 'speed' => 100, 'stamina' => 100,
        'power' => 100, 'guts' => 100, 'wit' => 100, 'sp' => 0, 'energy' => 88,
    ]);

    $payload = firstTurnPayload([
        'turn' => 2, 'speed' => 150, 'stamina' => 100, 'power' => 100, 'guts' => 100,
        'wit' => 100, 'energy' => 74,
    ]);

    test()->post("/training-runs/{$run->id}/turns", $payload)
        ->assertRedirect(route('runs.show', $run));

    // `has_previous` true is what keeps the first-turn note off this preview; the note's
    // own copy and the delta colours are in tests/browser/run-detail.spec.ts.
    test()
        ->withSession(['_old_input' => $payload + ['previewed' => '1']])
        ->get(route('runs.show', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('rail.preview', [
                ['direction' => 'up', 'text' => '+50 Speed'],
                ['direction' => 'up', 'text' => '+20 Skill Points'],
                ['direction' => 'down', 'text' => '-14 Energy'],
                ['direction' => 'up', 'text' => '+1200 Fans'],
            ])
            ->where('rail.has_previous', true));
});

it('still refuses a confirm that was never previewed, on a first turn too', function (): void {
    $run = firstTurnRun();

    $unpreviewed = firstTurnPayload(['stage' => 'confirm']);
    unset($unpreviewed['previewed']);

    test()->post("/training-runs/{$run->id}/turns", $unpreviewed)
        ->assertSessionHasErrors('previewed');

    // D-51 is unchanged by the fix: the guard is the flag, and the flag is only ever
    // rendered into a response that has already previewed.
    expect(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe(0);
});

it('does not offer the confirm stage on a plain visit to a fresh run', function (): void {
    $run = firstTurnRun();

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('rail.previewed', false));
});

it('still renders no turn rows for a run that has logged none', function (): void {
    $umamusume = Umamusume::factory()->create();
    $run = TrainingRun::factory()->create(['umamusume_id' => $umamusume->id]);

    // The D-1 regression bar, unchanged and unmodifiable: a fresh run invents no turn
    // row and mounts no stat band, whatever the rail is offering to record.
    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('band', null));

    expect(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe(0);
});
