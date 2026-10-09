<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEntry;

/*
 * D-51 across the two stages, as a sequence rather than as two separate facts.
 *
 * The existing guided tests assert each step on its own - a preview writes nothing, a
 * confirm stores a row, a confirm without a preview is refused. What none of them states
 * is the invariant the two stages jointly hold: at every point in the sequence the row
 * count is 0 or 1, it only ever becomes 1 on the confirm stage, and a stage that fails
 * leaves it where it was.
 *
 * That invariant is what the D-1 fix put at risk, because the fix moved a boolean that
 * decides both the visible stage and whether the confirm control is rendered. A test that
 * only ever reads the rail's stage would not notice that fix also made stage 1 writable;
 * one that counts rows does.
 *
 * Every count here is asserted on TurnEntry directly rather than on the response body.
 */

function stagedRun(): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => 'ura_finale']);
}

function stagedPayload(array $overrides = []): array
{
    return array_merge([
        'turn' => 1,
        'speed' => 120, 'stamina' => 110, 'power' => 130, 'guts' => 100, 'wit' => 95,
        'sp' => 20, 'energy' => 70, 'mood' => 'GREAT', 'fans' => 1200,
        'choice' => 'training-Speed', 'outcome' => 'Success', 'stage' => 'preview',
    ], $overrides);
}

it('holds the row count at zero after stage one and at one only after stage two', function (): void {
    $run = stagedRun();

    expect(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe(0);

    // The preview is a redirect (F-7) and now targets the Cockpit with the input flashed;
    // the Cockpit does not render a preview (the two-stage preview flow is retired, R-2),
    // so this test reads no body, only the row count below. No followRedirect() here:
    // Illuminate\Testing\TestResponse has no followRedirect() on Laravel 13.32.
    test()->post("/training-runs/{$run->id}/turns", stagedPayload())
        ->assertRedirect(route('runs.cockpit', $run));

    expect(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe(0);

    test()->post("/training-runs/{$run->id}/turns", stagedPayload([
        'stage' => 'confirm',
        'previewed' => '1',
    ]))->assertRedirect();

    expect(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe(1);
});

it('leaves the count where it was when the confirm stage is rejected', function (): void {
    $run = stagedRun();

    // Rejected for a missing penalty kind, which is a stage-two failure, not a stage-one
    // one: the row must not appear even though everything else about the turn is valid.
    test()->post("/training-runs/{$run->id}/turns", stagedPayload([
        'stage' => 'confirm',
        'previewed' => '1',
        'outcome' => 'Failure',
        'penalty_kind' => null,
    ]))->assertSessionHasErrors('penalty_kind');

    expect(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe(0);
});

it('does not let a repeated preview accumulate rows', function (): void {
    $run = stagedRun();

    foreach (range(1, 3) as $ignored) {
        test()->post("/training-runs/{$run->id}/turns", stagedPayload())
            ->assertRedirect(route('runs.cockpit', $run));
    }

    expect(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe(0);
});

it('grows the count by exactly one per confirmed turn', function (): void {
    $run = stagedRun();

    foreach ([1, 2, 3] as $turn) {
        test()->post("/training-runs/{$run->id}/turns", stagedPayload([
            'turn' => $turn,
            'speed' => 100 + $turn * 10,
            'stage' => 'confirm',
            'previewed' => '1',
        ]))->assertRedirect();

        expect(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe($turn);
    }
});
