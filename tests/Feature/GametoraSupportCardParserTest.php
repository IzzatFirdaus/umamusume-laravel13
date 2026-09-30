<?php

declare(strict_types=1);

use App\Models\SupportCard;
use App\Services\DataPipeline\Parsers\GametoraSupportCardParser;

/*
 * The support-card reader, run against the real published document.
 *
 * `database/seeders/data/support-cards.88dea522.json` is the committed body: 559 records from GameTora's
 * `support-cards` document, whose first eight sha256 hex digits are the `88dea522` in its name and in the
 * manifest's answer for that key. Every number asserted here was measured on that body before the parser
 * was written, and each is asserted against the parse of the body itself rather than against a fixture
 * cut to match the code. That is the KI-23 discipline: a hand-written fixture agrees with whatever key
 * its author misread, and here one field is spelled `name_jp` in this document, `jpname` in the skill
 * document and `jp_name` in the profile document. Three spellings, one publisher, one mistake each.
 *
 * The counts are the shape of the data, so they are pinned as counts: seven types in a known split,
 * three rarities in a known split, 251 of 559 records carrying a `release_en`, 23 carrying a `char_id`
 * in the 9000 block, 5,114 anchor rows all exactly twelve wide. A parser that shelved a type wrong or
 * truncated a vector moves one of those numbers, and the test says which.
 */

function supportCardsFixtureBody(): string
{
    return (string) file_get_contents(base_path('database/seeders/data/support-cards.88dea522.json'));
}

/**
 * The published rows keyed by the document's own `support_id`.
 *
 * @return array<int, array<string, mixed>>
 */
function supportCardsDocument(): array
{
    $rows = json_decode(supportCardsFixtureBody(), true, 512, JSON_THROW_ON_ERROR);
    expect(is_array($rows))->toBeTrue();

    /** @var array<int, array<string, mixed>> $keyed */
    $keyed = array_column($rows, null, 'support_id');

    return $keyed;
}

/**
 * @return list<array<string, mixed>>
 */
function parsedSupportCards(): array
{
    return (new GametoraSupportCardParser)->parse(supportCardsFixtureBody());
}

it('reads every record in the published document, keyed on the export id', function (): void {
    $records = parsedSupportCards();

    expect($records)->toHaveCount(559)
        ->and(array_column($records, 'support_id'))->toHaveCount(559)
        ->and(array_column($records, 'support_id'))->toBe(array_keys(supportCardsDocument()));
});

it('keeps the seven types and three rarities in the counts the document publishes', function (): void {
    $records = parsedSupportCards();

    $types = array_count_values(array_column($records, 'type'));
    $rarities = array_count_values(array_map(
        fn (array $record): string => (string) $record['rarity'],
        $records
    ));

    // Measured on the body. `friend` and `group` are the two the export adds to the five stat colours,
    // and they are the two the deck panel has to render without a stat to put under them.
    expect($types)->toEqual([
        'speed' => 125,
        'guts' => 111,
        'power' => 99,
        'stamina' => 97,
        'intelligence' => 99,
        'friend' => 23,
        'group' => 5,
    ])->and($rarities)->toEqual([1 => 146, 2 => 101, 3 => 312]);

    // The list the column CHECK and the picker read is the list this parser may emit.
    $seen = array_keys($types);
    sort($seen);
    $declared = SupportCard::TYPES;
    sort($declared);

    expect($seen)->toBe($declared);
});

it('emits only the columns the table holds, so a published key cannot reach a write', function (): void {
    $columns = [
        'support_id', 'char_id', 'char_name', 'name_ja', 'title_en', 'title_ja',
        'rarity', 'type', 'release_jp', 'release_global', 'effects',
    ];

    foreach (parsedSupportCards() as $record) {
        expect(array_keys($record))->toBe($columns);
    }

    // Read by the document and deliberately not stored, because no column holds them: `hints` and
    // `event_skills` describe the hint and skill economy ADR-0014 leaves out of Phase 1, `obtained` is
    // the gacha path, and the `_ko` / `_tw` fields are the same strings on two other servers.
    $row = supportCardsDocument()[10001];

    expect($row)->toHaveKey('hints')
        ->and($row)->toHaveKey('obtained')
        ->and($row)->toHaveKey('name_ko')
        ->and($row)->toHaveKey('url_name');
});

it('takes the Japanese name from name_jp, which is what this document calls it', function (): void {
    $document = supportCardsDocument();

    foreach (parsedSupportCards() as $record) {
        expect($record['name_ja'])->toBe($document[$record['support_id']]['name_jp']);
    }

    // The trap KI-23 describes: reading `name_ja` or `jpname` from this document yields null on all 559
    // rows, and an all-null column reads as an empty source rather than as a wrong key.
    expect(array_filter(
        parsedSupportCards(),
        fn (array $record): bool => $record['name_ja'] === null
    ))->toBe([]);
});

it('keeps the 23 cards whose char_id is a staff id the trainable catalogue does not have', function (): void {
    $staff = array_values(array_filter(
        parsedSupportCards(),
        fn (array $record): bool => $record['char_id'] >= 9000
    ));

    expect($staff)->toHaveCount(23)
        ->and(array_unique(array_column($staff, 'char_id')))->toHaveCount(12);

    // The evidence a wrongly-declared foreign key would have destroyed. `umamusume` rows come from the
    // character-card document, whose character ids stop at 1149, so a card with char_id 9001 or 9040
    // names nobody in the trainable catalogue. Tazuna Hayakawa's Pal cards and the two group cards are
    // also the rows the Scenario Link badge is derived from.
    $roster = json_decode(
        (string) file_get_contents(base_path('database/seeders/data/gametora-characters.e9e9ee6d.json')),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    expect(max(array_column($roster, 'char_id')))->toBe(1149);

    $document = supportCardsDocument();

    foreach ($staff as $record) {
        expect($record['char_id'])->toBe($document[$record['support_id']]['char_id']);
    }
});

it('stores each effect row as twelve ints with -1 kept as the absence it means', function (): void {
    $vectors = [];

    foreach (parsedSupportCards() as $record) {
        foreach ($record['effects'] as $row) {
            $vectors[] = $row;
        }
    }

    expect($vectors)->toHaveCount(5114);

    foreach ($vectors as $row) {
        expect($row)->toHaveCount(12)
            ->and(array_filter($row, fn (mixed $value): bool => is_int($value)))->toHaveCount(12);
    }

    // `-1` is "the client holds no entry at that level", not zero (UMAMUSUME_REFERENCE.md §1.4.7), and
    // the interpolation rule reads it position by position. Assert the marker survives rather than
    // trusting that it was not rewritten.
    $first = parsedSupportCards()[0];

    expect($first['support_id'])->toBe(10001)
        ->and($first['effects'])->toBe(supportCardsDocument()[10001]['effects'])
        ->and(in_array(-1, $first['effects'][0], true))->toBeTrue();
});

it('populates release_global only where the document states a release_en', function (): void {
    $records = parsedSupportCards();
    $document = supportCardsDocument();

    expect(array_filter($records, fn (array $r): bool => $r['release_global'] !== null))->toHaveCount(251)
        ->and(array_filter($records, fn (array $r): bool => $r['release_global'] === null))->toHaveCount(308);

    foreach ($records as $record) {
        $row = $document[$record['support_id']];

        // The key is absent rather than null on the 308, so `?? null` is the whole reading, and falling
        // back to `release_ko` or `release_zh_tw` would put another server's date in this column.
        expect($record['release_global'])->toBe($row['release_en'] ?? null)
            ->and($record['release_jp'])->toBe($row['release']);
    }
});

it('drops a record whose type or rarity the columns cannot hold, and keeps its siblings', function (): void {
    // A body the publisher does not ship, built here rather than stored so the fixture above stays the
    // real document. The case is an eighth type or a fourth rarity arriving in a future revision.
    $valid = [
        'support_id' => 90001, 'char_id' => 1001, 'char_name' => 'Special Week', 'name_jp' => '名',
        'rarity' => 1, 'type' => 'speed', 'release' => '2021-02-24', 'effects' => [],
    ];

    $body = (string) json_encode([
        $valid,
        [...$valid, 'support_id' => 90002, 'type' => 'turbo'],
        [...$valid, 'support_id' => 90003, 'rarity' => 5],
        [...$valid, 'support_id' => 90004, 'rarity' => '2'],
        [...$valid, 'support_id' => 90005, 'char_id' => null],
        [...$valid, 'support_id' => 90006, 'release' => '24/02/2021', 'release_en' => 'not a date'],
    ]);

    $records = (new GametoraSupportCardParser)->parse($body);

    expect($records)->toHaveCount(2)
        ->and(array_column($records, 'support_id'))->toBe([90001, 90006])
        // A date that fails the pattern is a null, not a coerced value and not a dropped card: the row
        // is still a card, and `release_status` reads Unreleased from two nulls.
        ->and($records[1]['release_jp'])->toBeNull()
        ->and($records[1]['release_global'])->toBeNull();
});

it('drops an anchor row that is not twelve wide and keeps the card that carries it', function (): void {
    $twelve = [1, 5, -1, -1, 10, 10, -1, -1, 15, -1, -1, -1];

    $body = (string) json_encode([[
        'support_id' => 90010, 'char_id' => 1001, 'char_name' => 'Special Week', 'name_jp' => '名',
        'rarity' => 1, 'type' => 'speed', 'release' => '2021-02-24',
        'effects' => [$twelve, [1, 5, 10], [2, 'x', 3, 4, 5, 6, 7, 8, 9, 10, 11, 12], 'not a row'],
    ]]);

    $records = (new GametoraSupportCardParser)->parse($body);

    // The vector is positional: a three-wide row read as levels 1, 5 and 10 prints a confident wrong
    // number, so it goes while the card and its good row stay.
    expect($records)->toHaveCount(1)
        ->and($records[0]['effects'])->toBe([$twelve]);
});

it('parses nothing out of a body that is not a list of records', function (): void {
    $parser = new GametoraSupportCardParser;

    expect($parser->parse(''))->toBe([])
        ->and($parser->parse('not json at all'))->toBe([])
        ->and($parser->parse('null'))->toBe([])
        ->and($parser->parse('{"support_id": 10001}'))->toBe([])
        ->and($parser->parse('[1, "two", null]'))->toBe([]);
});
