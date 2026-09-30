<?php

declare(strict_types=1);

use App\Enums\MoodTier;
use App\Http\Requests\ImportHistoricalRunRequest;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Illuminate\Http\UploadedFile;

/*
 * ADR-0017: a run the app exported must be a run the app can read back.
 *
 * The import exists for a Trainer's finished careers arriving as a file, so its contract is the
 * export's own column list rather than a format invented beside it. The headline test below is
 * therefore a literal round trip: export a run, import the bytes that came back, export the run
 * that was created, and assert the two files are the same text. Anything the import silently
 * dropped or re-typed shows up as a diff there.
 *
 * The other tests are the failure modes an external file actually has: a header the app never
 * wrote, one row past the ceiling its scenario allows, two rows claiming one turn, and the Slice 2
 * regression this path is most exposed to - a `scenario` of `''`, which 500s the run page. The
 * request inherits the create form's normalisation, so the empty string becomes null before the row
 * is written, and the test asserts the rendered page rather than only the column.
 */

/**
 * One turn row in the shape `export()` emits, keyed by its own header so the same array serves as
 * both model input and CSV source.
 *
 * @return array<string, string|int|null>
 */
function importTurn(array $overrides = []): array
{
    return array_merge([
        'turn' => 1,
        'speed' => 600, 'stamina' => 400, 'power' => 300, 'guts' => 250, 'wit' => 200,
        'sp' => 40, 'condition' => null, 'energy' => 70, 'mood' => 'GOOD', 'fans' => 1200,
    ], $overrides);
}

/**
 * Text exactly as `export()` would write it, from the given rows.
 *
 * @param  list<array<string, string|int|null>>  $rows
 */
function importCsv(array $rows): string
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

function importRun(?string $scenario = null): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => $scenario]);
}

/**
 * The run an import in this test just created: every import makes a new row, never edits the one
 * whose CSV it read.
 */
function importedRun(int $sourceId): TrainingRun
{
    return TrainingRun::query()->whereKeyNot($sourceId)->latest('id')->firstOrFail();
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function importPayload(TrainingRun $source, array $overrides = []): array
{
    return array_merge([
        'umamusume_id' => $source->umamusume_id,
        'scenario' => $source->scenario,
        'status' => $source->status->value,
    ], $overrides);
}

it('round trips an exported run straight back in through the import', function (): void {
    $source = importRun();
    $source->turnEntries()->createMany([
        importTurn(),
        importTurn(['turn' => 2, 'speed' => 700, 'mood' => 'GREAT', 'sp' => null, 'fans' => 2400]),
        importTurn(['turn' => 3, 'stamina' => 900, 'energy' => null, 'condition' => 'Good']),
    ]);

    $csv = test()->get("/training-runs/{$source->id}/export/csv")->assertOk()->getContent();

    test()->post(route('runs.import.preview'), importPayload($source, ['csv' => $csv]))->assertOk();
    test()->post(route('runs.import.store'), importPayload($source, ['csv' => $csv]))
        ->assertRedirect()
        ->assertSessionHas('status', 'Imported 3 turns.');

    $imported = importedRun($source->id);

    expect($imported->umamusume_id)->toBe($source->umamusume_id);
    expect($imported->scenario)->toBe($source->scenario);
    expect($imported->turnEntries)->toHaveCount(3);

    foreach ($source->turnEntries as $original) {
        $copy = $imported->turnEntries()->where('turn', $original->turn)->firstOrFail();

        foreach (['speed', 'stamina', 'power', 'guts', 'wit', 'sp', 'condition', 'energy', 'mood', 'fans'] as $field) {
            expect($copy->{$field})->toBe($original->{$field}, "turn {$original->turn} {$field}");
        }
    }

    // The strictest form of the claim: the file the import produced is byte-for-byte the file it was
    // handed. `turnEntries()` orders by turn, so this compares content rather than insertion order.
    expect(test()->get("/training-runs/{$imported->id}/export/csv")->assertOk()->getContent())->toBe($csv);
});

it('marks the run it imported and leaves a typed run unmarked', function (): void {
    $source = importRun();
    $source->turnEntries()->create(importTurn());
    $typed = importRun();

    expect($typed->imported_at)->toBeNull();
    expect($typed->import_source)->toBeNull();

    $csv = test()->get("/training-runs/{$source->id}/export/csv")->getContent();

    test()->post(route('runs.import.store'), importPayload($source, ['csv' => $csv]))->assertRedirect();

    $imported = importedRun($source->id);

    expect($imported->imported_at)->not->toBeNull();
    expect($imported->import_source)->toBe('pasted CSV ('.substr(md5($csv), 0, 8).')');
    expect($source->fresh()->imported_at)->toBeNull();
});

it('normalises an empty scenario so an imported run renders instead of 500ing', function (): void {
    $source = importRun();

    // The Slice 2 defect: a `scenario` column holding `''` 500s the run page, and an importer taking
    // an external string is exactly how such a row would be created.
    test()->post(route('runs.import.preview'), importPayload($source, ['scenario' => '', 'csv' => importCsv([importTurn()])]))->assertOk();
    test()->post(route('runs.import.store'), importPayload($source, ['scenario' => '', 'csv' => importCsv([importTurn()])]))
        ->assertRedirect();

    $imported = importedRun($source->id);

    expect($imported->scenario)->toBeNull();
    expect($imported->hasScenario())->toBeFalse();
    test()->get(route('runs.show', $imported))->assertOk();
});

it('measures every imported stat against the ceiling its own scenario sets', function (): void {
    $source = importRun('unity_cup');
    $before = TrainingRun::count();

    // unity_cup caps Speed at 1300 and Wit at 1800, so the same 1400 is legal in one column and
    // refused in the other. That asymmetry is the assertion: the import reads ScenarioCaps per stat
    // rather than one flat ceiling, and a run naming no scenario would get 1200 for both.
    test()->post(route('runs.import.store'), importPayload($source, [
        'csv' => importCsv([importTurn(['wit' => 1400])]),
    ]))->assertRedirect();

    expect(TrainingRun::count())->toBe($before + 1);

    test()->post(route('runs.import.store'), importPayload($source, [
        'csv' => importCsv([importTurn(['speed' => 1400])]),
    ]))->assertSessionHasErrors(['turns.0.speed']);

    expect(TrainingRun::count())->toBe($before + 1);

    test()->post(route('runs.import.store'), [
        'umamusume_id' => $source->umamusume_id,
        'status' => 'Completed',
        'csv' => importCsv([importTurn(['speed' => 1201])]),
    ])->assertSessionHasErrors(['turns.0.speed']);
});

it('rejects a file whose header the app never wrote', function (): void {
    $source = importRun();
    $before = TrainingRun::count();

    $csv = "turn,speed,stamina,power,guts,wit,sp\n1,1,1,1,1,1,1";

    test()->post(route('runs.import.preview'), importPayload($source, ['csv' => $csv]))
        ->assertSessionHasErrors(['turns']);
    test()->post(route('runs.import.store'), importPayload($source, ['csv' => $csv]))
        ->assertSessionHasErrors(['turns']);

    expect(TrainingRun::count())->toBe($before);
});

it('rejects two rows claiming the same turn number', function (): void {
    $source = importRun();
    $before = TrainingRun::count();

    test()->post(route('runs.import.store'), importPayload($source, [
        'csv' => importCsv([importTurn(['turn' => 4]), importTurn(['turn' => 4, 'speed' => 601])]),
    ]))->assertSessionHasErrors(['turns.1.turn']);

    expect(TrainingRun::count())->toBe($before);
});

it('reads an uploaded file and keeps its name as the provenance', function (): void {
    $source = importRun();
    $source->turnEntries()->createMany([importTurn(), importTurn(['turn' => 2])]);
    $csv = test()->get("/training-runs/{$source->id}/export/csv")->getContent();

    test()->post(route('runs.import.store'), [
        'umamusume_id' => $source->umamusume_id,
        'scenario' => $source->scenario,
        'status' => $source->status->value,
        'file' => UploadedFile::fake()->createWithContent('my-career-2024.csv', $csv),
    ])->assertRedirect();

    $imported = importedRun($source->id);

    expect($imported->import_source)->toBe('my-career-2024.csv');
    expect($imported->turnEntries)->toHaveCount(2);
});

it('writes nothing on the preview step', function (): void {
    $source = importRun();
    $runsBefore = TrainingRun::count();
    $turnsBefore = TurnEntry::count();

    test()->post(route('runs.import.preview'), importPayload($source, [
        'csv' => importCsv([importTurn(), importTurn(['turn' => 2])]),
    ]))
        ->assertOk()
        ->assertSee('Confirm the import')
        ->assertSee('2 turns');

    expect(TrainingRun::count())->toBe($runsBefore);
    expect(TurnEntry::count())->toBe($turnsBefore);
});

it('rejects a condition carrying a comma rather than shifting every later column', function (): void {
    $source = importRun();
    $before = TrainingRun::count();

    // `export()` joins with bare commas and does not quote, so a comma inside `condition` arrives as a
    // twelfth cell. Reading it by position would land `fans` in `energy`; the row-length check makes
    // the file fail loudly instead. Recorded as the format's known limit in ADR-0017.
    $csv = importCsv([importTurn(['condition' => 'Good, slightly lame'])]);

    test()->post(route('runs.import.store'), importPayload($source, ['csv' => $csv]))
        ->assertSessionHasErrors(['turns']);

    expect(TrainingRun::count())->toBe($before);
});

it('accepts a mood a paper sheet wrote in lower case', function (): void {
    $source = importRun();

    test()->post(route('runs.import.store'), importPayload($source, [
        'csv' => importCsv([importTurn(['mood' => 'good'])]),
    ]))->assertRedirect();

    $imported = importedRun($source->id);

    expect($imported->turnEntries()->firstWhere('turn', 1)->mood)->toBe(MoodTier::Good);
});

it('refuses an unknown trainee and a missing turn body', function (): void {
    $source = importRun();

    test()->post(route('runs.import.store'), [
        'umamusume_id' => Umamusume::query()->max('id') + 1,
        'status' => 'Completed',
        'csv' => importCsv([importTurn()]),
    ])->assertSessionHasErrors(['umamusume_id']);

    test()->post(route('runs.import.store'), importPayload($source))
        ->assertSessionHasErrors(['csv']);
});
