<?php

declare(strict_types=1);

use App\Services\DataPipeline\Parsers\GametoraRaceCatalogParser;

/**
 * Fixture shape mirrors `race_instances` from the GameTora export: one row per
 * career race slot, with the race's own fields nested under `details`.
 *
 * @param  array<string, mixed>  $overrides
 * @param  array<string, mixed>  $details
 */
function raceInstanceRow(array $overrides = [], array $details = []): array
{
    $base = [
        'id' => '76',
        'instance' => 101601,
        'year' => 3,
        'month' => 10,
        'half' => 2,
        'tid' => '0',
        'fans_needed' => 20000,
        'fans_gain' => 45,
        'drops' => 101601,
    ];

    $baseDetails = [
        'id' => 101601,
        'name_en' => 'Tenno Sho (Autumn)',
        'grade' => 100,
        'distance' => 2000,
        'terrain' => 1,
        'track' => 10006,
        'url_name' => 'tenno-sho-autumn',
    ];

    return [
        ...array_merge($base, $overrides),
        'details' => array_merge($baseDetails, $details),
    ];
}

/**
 * @param  array<int, array<string, mixed>>  $rows
 */
function parseRaceRows(array $rows): array
{
    return (new GametoraRaceCatalogParser)->parse(json_encode($rows, JSON_THROW_ON_ERROR));
}

function parseRaceRow(array $overrides = [], array $details = []): array
{
    $rows = parseRaceRows([raceInstanceRow($overrides, $details)]);

    return $rows[0] ?? [];
}

it('decodes the grade code to the [Global] tier label recorded in 09', function (int $grade, string $tier): void {
    expect(parseRaceRow(details: ['grade' => $grade])['tier'])->toBe($tier);
})->with([
    [100, 'G1'],
    [200, 'G2'],
    [300, 'G3'],
    [400, 'OP'],
    [700, 'Pre-OP'],
    [800, 'Maiden'],
    [900, 'Debut'],
]);

it('keeps the raw grade code so a decoding can be audited against the export', function (): void {
    expect(parseRaceRow(details: ['grade' => 200]))->toMatchArray([
        'grade_code' => 200,
        'tier' => 'G2',
    ]);
});

it('drops a row the export flags as unreleased on the English server', function (): void {
    expect(parseRaceRow(details: ['unreleased_servers' => ['en']]))->toBe([])
        ->and(parseRaceRows([raceInstanceRow(details: ['unreleased_servers' => ['ko']])]))->toHaveCount(1);
});

it('drops the [JP-Only] Grand Masters final and keeps the four [Global] finals', function (string $slot, ?string $scenarioKey): void {
    $row = parseRaceRow(['id' => $slot, 'year' => 4, 'month' => 99999, 'half' => 3], [
        'id' => 999999,
        'name_en' => 'A final',
        'grade' => 100,
        'distance' => 99999,
        'terrain' => 99999,
        'track' => 99999,
    ]);

    if ($scenarioKey === null) {
        expect($row)->toBe([]);

        return;
    }

    expect($row['scenario_key'])->toBe($scenarioKey)
        ->and($row['turn'])->toBeNull()
        ->and($row['slot_label'])->toBe('after Senior December');
})->with([
    ['final', 'ura_finale'],
    ['final_aoharu', 'unity_cup'],
    ['final_mant', 'trackblazer'],
    ['final_live', 'grand_concert'],
    ['final_masters', null],
]);

it('leaves scenario_key null on the shared monthly slots that every scenario draws from', function (): void {
    expect(parseRaceRow()['scenario_key'])->toBeNull();
});

it('turns the export sentinels into null rather than storing a fake measurement', function (): void {
    $row = parseRaceRow(['id' => 'debut', 'year' => 1, 'month' => 6, 'half' => 2, 'fans_needed' => null], [
        'id' => 999999,
        'name_en' => 'Junior Make Debut',
        'grade' => 900,
        'distance' => 99999,
        'terrain' => 99999,
        'track' => 99999,
    ]);

    expect($row)->toMatchArray([
        'distance' => null,
        'distance_band' => null,
        'surface' => null,
        'track_id' => null,
        'fans_needed' => null,
        'turn' => 12,
        'slot_label' => 'Late June',
        'is_mandatory' => true,
        'is_special_race' => false,
    ]);
});

it('derives the turn as (month - 1) * 2 + half, the mapping the client grid labels', function (int $month, int $half, int $turn, string $label): void {
    $row = parseRaceRow(['month' => $month, 'half' => $half]);

    expect($row['turn'])->toBe($turn)->and($row['slot_label'])->toBe($label);
})->with([
    [1, 1, 1, 'Early January'],
    [6, 2, 12, 'Late June'],
    [7, 1, 13, 'Early July'],
    [12, 1, 23, 'Early December'],
    [12, 2, 24, 'Late December'],
]);

it('bands distance on the published cut-offs', function (int $distance, string $band): void {
    expect(parseRaceRow(details: ['distance' => $distance])['distance_band'])->toBe($band);
})->with([
    [1200, 'Sprint'],
    [1400, 'Sprint'],
    [1401, 'Mile'],
    [1600, 'Mile'],
    [1800, 'Mile'],
    [1801, 'Medium'],
    [2400, 'Medium'],
    [2401, 'Long'],
]);

it('names the debut and the scenario finals mandatory without touching a per-character Goal', function (): void {
    $titles = array_map(
        fn (array $r): string => $r['title'],
        parseRaceRows([
            raceInstanceRow(['id' => 'debut', 'year' => 1, 'month' => 6, 'half' => 2], ['name_en' => 'Junior Make Debut', 'grade' => 900]),
            raceInstanceRow(['id' => 'maiden', 'year' => 1, 'month' => 7, 'half' => 1], ['name_en' => 'Junior Maiden Race', 'grade' => 800]),
            raceInstanceRow(['id' => '76', 'year' => 3, 'month' => 10, 'half' => 2], ['name_en' => 'Tenno Sho (Autumn)']),
        ])
    );

    expect($titles)->toBe(['Junior Make Debut', 'Junior Maiden Race', 'Tenno Sho (Autumn)']);

    $mandatory = array_map(
        fn (array $r): bool => $r['is_mandatory'],
        parseRaceRows([
            raceInstanceRow(['id' => 'debut'], ['name_en' => 'Junior Make Debut', 'grade' => 900]),
            raceInstanceRow(['id' => 'maiden'], ['name_en' => 'Junior Maiden Race', 'grade' => 800]),
            raceInstanceRow(['id' => '76'], ['name_en' => 'Tenno Sho (Autumn)']),
        ])
    );

    // The maiden race is conditional on a lost debut, and a graded race is never
    // scenario-mandatory, so only the debut carries the flag here.
    expect($mandatory)->toBe([true, false, false]);
});

it('carries the timeline marker without reading it as a server statement', function (): void {
    expect(parseRaceRow(details: ['did_not_exist' => 'pre_nar'])['did_not_exist'])->toBe('pre_nar')
        ->and(parseRaceRow()['did_not_exist'])->toBeNull();
});

it('keeps a zero fan gate as zero, because "no fans required" is a real answer', function (): void {
    expect(parseRaceRow(['fans_needed' => 0])['fans_needed'])->toBe(0);
});

it('returns nothing on a body it cannot read rather than throwing mid-fetch', function (string $body): void {
    expect((new GametoraRaceCatalogParser)->parse($body))->toBe([]);
})->with([
    'truncated json' => ['[{"id":'],
    'json object not a list' => ['{"a":1}'],
    'empty array' => ['[]'],
]);

it('skips a malformed row without discarding the valid ones beside it', function (): void {
    $rows = parseRaceRows([
        ['id' => 'x', 'year' => 3],
        raceInstanceRow(),
    ]);

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['title'])->toBe('Tenno Sho (Autumn)');
});

it('does not emit export_slot_id because the column does not exist in the schema', function (): void {
    $row = parseRaceRow();

    expect($row)->not->toHaveKey('export_slot_id');
});
