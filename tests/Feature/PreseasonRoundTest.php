<?php

declare(strict_types=1);

use App\Models\PreseasonRound;
use App\Models\TrainingRun;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
 * D-10 — individual preseason-round outcome persistence (owner-authorised 2026-11-25).
 *
 * The client's Unity Cup panel prints Round 1..4 with a result each. These tests pin
 * the persistence contract: a round is recorded with WIN/LOSS, corrected in place,
 * cleared back to an explicit unknown, and deleted with its run. The legacy
 * `team_preseason_wins` tally is independent and is never recomputed from the rows.
 */

it('persists a recorded round outcome through the run update', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);

    $response = $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => 'unity_cup',
        'status' => 'Active',
        'preseason_rounds' => [
            1 => 'WIN',
            2 => 'LOSS',
        ],
    ]);

    $response->assertRedirect();

    expect(PreseasonRound::where('training_run_id', $run->id)->count())->toBe(2)
        ->and(PreseasonRound::where('training_run_id', $run->id)->where('round', 1)->first()->outcome)->toBe('WIN')
        ->and(PreseasonRound::where('training_run_id', $run->id)->where('round', 2)->first()->outcome)->toBe('LOSS');
});

it('corrects a round outcome in place without adding a second row', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);

    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => 'unity_cup',
        'status' => 'Active',
        'preseason_rounds' => [1 => 'WIN'],
    ]);

    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => 'unity_cup',
        'status' => 'Active',
        'preseason_rounds' => [1 => 'LOSS'],
    ]);

    $rows = PreseasonRound::where('training_run_id', $run->id)->where('round', 1)->get();
    expect($rows)->toHaveCount(1)
        ->and($rows->first()->outcome)->toBe('LOSS');
});

it('clears a round back to an explicit unknown when the value is null', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    PreseasonRound::factory()->for($run)->round(1)->win()->create();

    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => 'unity_cup',
        'status' => 'Active',
        'preseason_rounds' => [1 => null],
    ]);

    $row = PreseasonRound::where('training_run_id', $run->id)->where('round', 1)->first();
    expect($row)->not->toBeNull()
        ->and($row->outcome)->toBeNull();
});

it('rejects an outcome outside WIN/LOSS at the boundary', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);

    $response = $this->from(route('runs.team', $run))->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => 'unity_cup',
        'status' => 'Active',
        'preseason_rounds' => [1 => 'DRAW'],
    ]);

    $response->assertSessionHasErrors('preseason_rounds.1');
    expect(PreseasonRound::where('training_run_id', $run->id)->count())->toBe(0);
});

it('rejects a round outside 1..4 at the model layer', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);

    $run->preseasonRounds()->create(['round' => 5, 'outcome' => 'WIN']);
})->throws(InvalidArgumentException::class);

it('leaves the legacy team_preseason_wins tally untouched', function (): void {
    $run = TrainingRun::factory()->create([
        'scenario' => 'unity_cup',
        'status' => 'Active',
        'team_preseason_wins' => 3,
    ]);

    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => 'unity_cup',
        'status' => 'Active',
        'preseason_rounds' => [1 => 'WIN', 2 => 'WIN'],
    ]);

    expect($run->fresh()->team_preseason_wins)->toBe(3);
});

it('deletes a run\'s preseason rounds with the run', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    PreseasonRound::factory()->for($run)->round(1)->win()->create();
    PreseasonRound::factory()->for($run)->round(2)->loss()->create();

    $response = $this->delete("/training-runs/{$run->id}", ['confirmed' => '1']);
    $response->assertRedirect(route('runs.index'));

    $this->assertDatabaseMissing('preseason_rounds', ['training_run_id' => $run->id]);
});

it('keeps rounds with no row absent rather than a fabricated loss', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);

    // Only round 1 recorded; rounds 2-4 have no row at all.
    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => 'unity_cup',
        'status' => 'Active',
        'preseason_rounds' => [1 => 'WIN'],
    ]);

    $rounds = PreseasonRound::where('training_run_id', $run->id)->pluck('round')->all();
    expect($rounds)->toBe([1]);
});

it('leaves stored rounds untouched when the map is omitted entirely', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    PreseasonRound::factory()->for($run)->round(1)->win()->create();

    // No `preseason_rounds` key at all: a partial update of the run row.
    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => 'unity_cup',
        'status' => 'Active',
        'team_motto' => 'Dreaming Big',
    ]);

    expect(PreseasonRound::where('training_run_id', $run->id)->count())->toBe(1)
        ->and(PreseasonRound::where('training_run_id', $run->id)->where('round', 1)->first()->outcome)->toBe('WIN');
});

it('moves only the rounds named in a partial map', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    PreseasonRound::factory()->for($run)->round(1)->win()->create();
    PreseasonRound::factory()->for($run)->round(2)->win()->create();

    // Only round 2 is named; round 1 keeps its stored WIN.
    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => 'unity_cup',
        'status' => 'Active',
        'preseason_rounds' => [2 => 'LOSS'],
    ]);

    expect(PreseasonRound::where('training_run_id', $run->id)->where('round', 1)->first()->outcome)->toBe('WIN')
        ->and(PreseasonRound::where('training_run_id', $run->id)->where('round', 2)->first()->outcome)->toBe('LOSS');
});

it('rejects a round key outside 1..4 at the boundary', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);

    $response = $this->from(route('runs.team', $run))->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => 'unity_cup',
        'status' => 'Active',
        'preseason_rounds' => [5 => 'WIN'],
    ]);

    $response->assertSessionHasErrors('preseason_rounds');
    expect(PreseasonRound::where('training_run_id', $run->id)->count())->toBe(0);
});

it('refuses preseason rounds on a scenario with no team layer', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $response = $this->from(route('runs.team', $run))->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => 'ura_finale',
        'status' => 'Active',
        'preseason_rounds' => [1 => 'WIN'],
    ]);

    $response->assertSessionHasErrors('preseason_rounds');
});

it('reconciles individual wins against the preserved legacy tally without overwriting it', function (): void {
    $run = TrainingRun::factory()->create([
        'scenario' => 'unity_cup',
        'team_preseason_wins' => 4,
    ]);

    // Three individual round wins recorded; the legacy tally stays 4 (an imported
    // aggregate observation, not recomputed from the rows).
    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => 'unity_cup',
        'status' => 'Active',
        'preseason_rounds' => [1 => 'WIN', 2 => 'WIN', 3 => 'WIN'],
    ]);

    $run->refresh();
    $individualWins = PreseasonRound::where('training_run_id', $run->id)->where('outcome', 'WIN')->count();

    expect($individualWins)->toBe(3)
        ->and($run->team_preseason_wins)->toBe(4);
});
