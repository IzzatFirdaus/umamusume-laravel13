<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;

/*
 * D-1: the first turn of a run, through the guided rail.
 *
 * The deadlock, in three layers that all read the same thing:
 *
 *   StoreTurnEntryRequest:78   a confirm stage requires `previewed`
 *     ^ guided-step renders `previewed` only when `@if ($preview !== [])`
 *       ^ previewDeltas() returns [] when there is no previous row
 *         ^ a run's first turn has no previous row
 *
 * So the first turn of every run could not be committed through the rail. It was not a
 * data-loss bug: the raw escape hatch at runs/show.blade.php:304 still records a turn, and
 * that is exactly why it survived - GuidedTurnOnRunViewTest's confirm test runs against a
 * run that already has turn 1, so no existing test ever previewed turn 1.
 *
 * The fix is in the controller, not the view: an empty delta list meant both "this is the
 * plain GET" and "this is a preview of a first turn", and the rail read the first. Those
 * are now two named states, and the confirm gate follows the state rather than the list.
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

    $html = test()->post("/training-runs/{$run->id}/turns", firstTurnPayload())
        ->assertOk()
        ->getContent();

    // A first turn previews to an empty delta list by arithmetic, so the only thing that
    // can carry "you have previewed this" is the flag itself.
    expect($html)->toContain('name="previewed"')
        ->and($html)->toContain('Step 2 of')
        ->and(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe(0);
});

it('says why the first turn has no deltas, rather than showing an empty preview', function (): void {
    $run = firstTurnRun();

    $html = test()->post("/training-runs/{$run->id}/turns", firstTurnPayload())->getContent();

    // D-220: an absent figure is a labelled absence. Five zeroed deltas, or a blank panel
    // with no explanation, would both be structure claiming to be data.
    expect($html)->toContain('This is the run\'s first turn')
        ->and($html)->not->toContain('+0 Speed');
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

    $html = test()->post("/training-runs/{$run->id}/turns", firstTurnPayload([
        'turn' => 2, 'speed' => 150, 'stamina' => 100, 'power' => 100, 'guts' => 100,
        'wit' => 100, 'energy' => 74,
    ]))->getContent();

    expect($html)->toContain('+50 Speed')
        ->toContain('-14 Energy')
        ->not->toContain('This is the run\'s first turn');
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

    $html = test()->get("/training-runs/{$run->id}")->assertOk()->getContent();

    expect($html)->not->toContain('name="previewed"');
});

it('still renders no turn rows for a run that has logged none', function (): void {
    $umamusume = Umamusume::factory()->create();
    $run = TrainingRun::factory()->create(['umamusume_id' => $umamusume->id]);

    $html = test()->get("/training-runs/{$run->id}")->assertOk()->getContent();

    // The D-1 regression bar, unchanged and unmodifiable: a fresh run invents no turn
    // row and mounts no stat band, whatever the rail is offering to record.
    expect($html)->not->toContain('bg-grade-')
        ->and(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe(0);
});
