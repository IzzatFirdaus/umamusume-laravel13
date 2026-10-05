<?php

declare(strict_types=1);

use App\Http\Requests\ImportHistoricalRunRequest;
use App\Models\TrainingRun;
use Illuminate\Support\MessageBag;

/**
 * KI-46: the import's per-stat error told the Trainer `The turns.0.speed field must be between 0 and
 * 1400.` The ceiling and the column were right; `turns.0.speed` is the Form Request's internal nested key,
 * and it was the only part of the sentence naming which row broke. Laravel's `attributes()` maps a wildcard
 * key to one string, so renaming it collapses every row onto the same word and the row identity goes with
 * it, and the finding's own prescription is a message that states the row.
 *
 * Asserted on the MessageBag the request returns. The form prints its own prefix in front of these strings,
 * and the composite sentence is measured in `tests/browser/run-import.spec.ts` against the landed view:
 * that pass closes the gap this header used to name, when the prefix lived in a Blade file carrying a
 * peer's uncommitted change and `Runs/Import.vue` did not exist yet.
 */
function csvWith(array $rows): string
{
    $lines = [implode(',', ImportHistoricalRunRequest::HEADERS)];

    foreach ($rows as $row) {
        $cells = [];

        foreach (ImportHistoricalRunRequest::HEADERS as $header) {
            $cells[] = (string) ($row[$header] ?? '');
        }

        $lines[] = implode(',', $cells);
    }

    return implode("\n", $lines);
}

function turnRow(array $overrides = []): array
{
    return array_merge([
        'turn' => 1,
        'speed' => 600, 'stamina' => 400, 'power' => 300, 'guts' => 250, 'wit' => 200,
        'sp' => 40, 'condition' => null, 'energy' => 70, 'mood' => 'GOOD', 'fans' => 1200,
    ], $overrides);
}

function importErrors(array $rows): MessageBag
{
    $source = TrainingRun::factory()->create(['scenario' => 'unity_cup']);

    $response = test()->post(route('runs.import.store'), [
        'umamusume_id' => $source->umamusume_id,
        'scenario' => $source->scenario,
        'status' => $source->status->value,
        'csv' => csvWith($rows),
    ]);

    $response->assertSessionHasErrors();

    return app('session.store')->get('errors')->getBag('default');
}

it('names the turn a ceiling break belongs to instead of the request key', function (): void {
    expect(importErrors([turnRow(['turn' => 7, 'speed' => 1400])])->first('turns.0.speed'))
        ->toBe('turn 7 must be between 0 and 1300.');
});

it('keeps one row identity per broken row instead of collapsing them', function (): void {
    $errors = importErrors([
        turnRow(['turn' => 1, 'speed' => 1400]),
        turnRow(['turn' => 2, 'speed' => 1500]),
    ]);

    // Both rows are in the bag. The form's `@error("turns.*.speed")` resolves through `MessageBag::first()`,
    // so one line per column renders however many rows broke; naming the row is what makes that line
    // actionable, and the envelope itself is a separate call on a peer's file.
    expect($errors->get('turns.0.speed'))->toBe(['turn 1 must be between 0 and 1300.'])
        ->and($errors->get('turns.1.speed'))->toBe(['turn 2 must be between 0 and 1300.']);
});

it('falls back to the row position when the turn number is the broken cell', function (): void {
    $errors = importErrors([turnRow(['turn' => 'x', 'speed' => 1400])]);

    expect($errors->first('turns.0.turn'))->toBe('row 1 must be a turn number.')
        ->and($errors->first('turns.0.speed'))->toBe('row 1 must be between 0 and 1300.');
});

it('refuses the internal key on every one of those messages', function (): void {
    $errors = importErrors([turnRow(['turn' => 7, 'speed' => 1400, 'stamina' => 'abc'])]);

    foreach ($errors->all() as $message) {
        expect($message)->not->toMatch('/turns\.\d+\./');
    }
});

it('says nothing about turns when the file is accepted, so the four above are not every run erroring', function (): void {
    $source = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    $before = TrainingRun::count();

    test()->post(route('runs.import.store'), [
        'umamusume_id' => $source->umamusume_id,
        'scenario' => $source->scenario,
        'status' => $source->status->value,
        'csv' => csvWith([turnRow(['turn' => 7, 'speed' => 1300])]),
    ])->assertRedirect();

    expect(TrainingRun::count())->toBe($before + 1);
});
