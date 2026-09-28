<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Services\DataPipeline\Parsers\GametoraCharacterParser;

/**
 * @param  list<array<string, mixed>>  $cards
 */
function parseCards(array $cards): array
{
    return (new GametoraCharacterParser)->parse(json_encode($cards, JSON_THROW_ON_ERROR));
}

function card(int $charId, int $cardId, string $en, ?string $jp, ?string $jpDate, ?string $enDate): array
{
    return [
        'char_id' => $charId,
        'card_id' => $cardId,
        'name_en' => $en,
        'name_jp' => $jp,
        'release' => $jpDate,
        'release_en' => $enDate,
    ];
}

it('emits one record per Umamusume from a costume-card dataset, keeping the debut form', function (): void {
    $records = parseCards([
        card(1008, 100802, 'Vodka', 'ウオッカ', '2022-11-28', null),
        card(1008, 100801, 'Vodka', 'ウオッカ', '2021-02-24', '2025-06-26'),
        card(1141, 114101, 'Epiphaneia', 'エピファネイア', '2026-08-24', null),
    ]);

    expect($records)->toHaveCount(2)
        ->and($records[0]['jp_debut_date'])->toBe('2021-02-24')
        ->and($records[0]['global_debut_date'])->toBe('2025-06-26')
        ->and($records[0]['external_ref'])->toBe('gametora:char:1008')
        ->and($records[1]['name_ja'])->toBe('エピファネイア');
})->name('keeps the earliest costume card as the catalog record');

it('maps presence of a Global debut date onto the release status enum', function (): void {
    $records = parseCards([
        card(1001, 100101, 'Special Week', 'スペシャルウィーク', '2021-02-24', '2025-06-26'),
        card(1047, 104701, 'Zenno Rob Roy', 'ゼンノロブロイ', '2022-12-12', null),
    ]);

    expect($records[0]['release_status'])->toBe(ReleaseStatus::GlobalReleased->value)
        ->and($records[1]['release_status'])->toBe(ReleaseStatus::JapanOnly->value)
        ->and($records[1]['global_debut_date'])->toBeNull();
});

it('treats a unit as Japan-only when only a later costume card reached Global', function (): void {
    $records = parseCards([
        card(1008, 100801, 'Vodka', 'ウオッカ', '2021-02-24', null),
        card(1008, 100802, 'Vodka', 'ウオッカ', '2022-11-28', '2026-09-24'),
    ]);

    expect($records)->toHaveCount(1)
        ->and($records[0]['release_status'])->toBe(ReleaseStatus::JapanOnly->value)
        ->and($records[0]['global_debut_date'])->toBeNull();
});

it('reads the committed sample of the real dataset without raising', function (): void {
    $body = file_get_contents(base_path('tests/Fixtures/gametora-character-cards.sample.json'));
    $records = (new GametoraCharacterParser)->parse($body);

    expect($records)->toBeArray()
        ->and(count($records))->toBeGreaterThan(0)
        ->and(array_column($records, 'name'))->toContain('Special Week', 'Vodka')
        ->and($records[0]['name_ja'])->toBe('スペシャルウィーク')
        ->and(array_unique(array_column($records, 'external_ref')))->toHaveCount(count($records));
});

it('drops malformed input instead of throwing', function (string $body) {
    expect((new GametoraCharacterParser)->parse($body))->toBe([]);
})->with([
    'not json' => ['this is not json'],
    'json object rather than a list' => ['{"char_id": 1}'],
    'empty list' => ['[]'],
    'entry without a display name' => ['[{"char_id": 1001, "name_en": "  "}]'],
    'entry without a character id' => ['[{"name_en": "Special Week"}]'],
    'entry that is not an object' => ['["Special Week"]'],
]);

it('ignores date strings that are not plain calendar dates', function (): void {
    $records = parseCards([
        card(1001, 100101, 'Special Week', null, '2021/02/24', 'TBD'),
    ]);

    expect($records[0]['jp_debut_date'])->toBeNull()
        ->and($records[0]['global_debut_date'])->toBeNull()
        ->and($records[0]['release_status'])->toBe(ReleaseStatus::JapanOnly->value);
});

it('reads the Japanese name from the key the export actually publishes', function (): void {
    $body = json_encode([[
        'char_id' => 1007,
        'card_id' => 100701,
        'name_en' => 'Gold Ship',
        'name_jp' => 'ゴールドシップ',
        'release' => '2021-02-24',
        'release_en' => '2025-06-26',
    ]], JSON_THROW_ON_ERROR);

    $record = (new GametoraCharacterParser)->parse($body)[0];

    // The export spells this key name_jp and never name_ja. Reading the wrong one
    // stores a null, and the detail page loses the Japanese name US-1 promises.
    expect($record['name_ja'])->toBe('ゴールドシップ');
});
