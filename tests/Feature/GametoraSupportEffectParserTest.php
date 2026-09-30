<?php

declare(strict_types=1);

use App\Models\SupportEffect;
use App\Services\DataPipeline\Parsers\GametoraSupportEffectParser;

/*
 * The support-effect dictionary reader, run against the real published document.
 *
 * `database/seeders/data/support_effects.ca447e53.json` is the committed body: 35 records whose sha256
 * prefix is the `ca447e53` the manifest names for `support_effects`, and whose ids run 1 to 33 plus 41
 * and 9991. This is the table that names the ids sitting at position 0 of every anchor vector on a
 * support card, so a mis-shelved field here mislabels a deck panel rather than a dictionary.
 *
 * The load-bearing one is `calc`. Four records declare it (`mult` on ids 1, 27 and 28, `add` on id 19)
 * and 31 declare nothing, and UMAMUSUME_REFERENCE.md §1.4.8 reads that absence itself as the finding:
 * exactly the effects declaring `calc` combine multiplicatively. So the 31 nulls are asserted as nulls
 * and `flat`, this repository's prose word for them, is asserted absent. A parser that filled the gap in
 * would still produce a table that looked complete.
 */

function supportEffectsFixtureBody(): string
{
    return (string) file_get_contents(base_path('database/seeders/data/support_effects.ca447e53.json'));
}

/**
 * The published rows keyed by the document's own `id`.
 *
 * @return array<int, array<string, mixed>>
 */
function supportEffectsDocument(): array
{
    $rows = json_decode(supportEffectsFixtureBody(), true, 512, JSON_THROW_ON_ERROR);
    expect(is_array($rows))->toBeTrue();

    /** @var array<int, array<string, mixed>> $keyed */
    $keyed = array_column($rows, null, 'id');

    return $keyed;
}

/**
 * @return list<array<string, mixed>>
 */
function parsedSupportEffects(): array
{
    return (new GametoraSupportEffectParser)->parse(supportEffectsFixtureBody());
}

it('reads all 35 dictionary rows and keys them on the export id', function (): void {
    $records = parsedSupportEffects();

    expect($records)->toHaveCount(35)
        ->and(array_column($records, 'effect_id'))->toBe(array_keys(supportEffectsDocument()))
        ->and(max(array_column($records, 'effect_id')))->toBe(9991);
});

it('carries calc verbatim: four modes, thirty-one absences', function (): void {
    $document = supportEffectsDocument();
    $records = parsedSupportEffects();

    $declaring = array_values(array_filter($records, fn (array $record): bool => $record['calc'] !== null));

    expect($declaring)->toHaveCount(4)
        ->and(array_column($declaring, 'effect_id'))->toBe([1, 19, 27, 28])
        ->and(array_column($declaring, 'calc'))->toBe(['mult', 'add', 'mult', 'mult']);

    foreach ($records as $record) {
        // Null where the record declares nothing, and the same word where it does. No fallback, no
        // default, and no `flat` invented for the 31.
        expect($record['calc'])->toBe($document[$record['effect_id']]['calc'] ?? null);
    }

    expect(array_filter($records, fn (array $record): bool => $record['calc'] === 'flat'))->toBe([])
        ->and(array_filter(
            $records,
            fn (array $record): bool => $record['calc'] !== null
                && ! in_array($record['calc'], SupportEffect::CALC_MODES, true)
        ))->toBe([]);
});

it('names a row whose document has no name_en with that document\'s name_en_eon', function (): void {
    $document = supportEffectsDocument();

    // Two records (id 32 and 41) publish no `name_en`, and `support_effects.name_en` is NOT NULL. Both
    // carry the publisher's longer `name_en_eon` spelling, which is the same fallback
    // `GametoraScenarioParser` uses between `name_en` and `name_en_old`.
    $missing = array_values(array_filter(
        $document,
        fn (array $row): bool => ! isset($row['name_en'])
    ));

    expect($missing)->toHaveCount(2)
        ->and(array_column($missing, 'id'))->toBe([32, 41]);

    foreach (parsedSupportEffects() as $record) {
        $row = $document[$record['effect_id']];

        expect($record['name_en'])->toBe($row['name_en'] ?? $row['name_en_eon'])
            ->and($record['name_en'])->not->toBe('')
            // Never the Japanese name, which every record has and which is not an English label.
            ->and($record['name_ja'])->toBe($row['name_ja']);
    }
});

it('keeps symbol as the export words it, including the two records that state none', function (): void {
    $symbols = array_count_values(array_map(
        fn (array $record): string => $record['symbol'] ?? '__absent__',
        parsedSupportEffects()
    ));

    expect($symbols)->toEqual([
        'percent' => 11,
        'none' => 21,
        'level' => 1,
        '__absent__' => 2,
    ]);

    $document = supportEffectsDocument();

    foreach (parsedSupportEffects() as $record) {
        expect($record['symbol'])->toBe($document[$record['effect_id']]['symbol'] ?? null);
    }
});

it('describes a row from desc_en, falling back only to the same language', function (): void {
    $document = supportEffectsDocument();
    $records = parsedSupportEffects();

    // 32 records carry `desc_en`, both of the two that do not carry `desc_en_eon`, and id 33 carries
    // neither, so 34 descriptions land and one row is honestly empty.
    expect(array_filter($records, fn (array $record): bool => $record['description_en'] !== null))
        ->toHaveCount(34)
        ->and(array_values(array_filter(
            $records,
            fn (array $record): bool => $record['description_en'] === null
        )))->toHaveCount(1);

    foreach ($records as $record) {
        $row = $document[$record['effect_id']];

        expect($record['description_en'])->toBe($row['desc_en'] ?? $row['desc_en_eon'] ?? null);
    }
});

it('emits only the six columns the dictionary has', function (): void {
    foreach (parsedSupportEffects() as $record) {
        expect(array_keys($record))->toBe([
            'effect_id', 'name_en', 'name_ja', 'calc', 'symbol', 'description_en',
        ]);
    }

    // Published and not stored, because no column holds them. `_note` in particular is GameTora's own
    // working comment about a record it made up, which is commentary about the data rather than a fact
    // about the game, and `inactive` / `no_value` are the publisher's shelving of effects the client no
    // longer surfaces.
    $document = supportEffectsDocument();

    expect($document[33])->toHaveKey('_note')
        ->and($document[20])->toHaveKey('inactive')
        ->and($document[9991])->toHaveKey('no_value')
        ->and($document[1])->toHaveKey('name_ko');
});

it('drops a row it cannot key or cannot name', function (): void {
    $body = (string) json_encode([
        ['id' => 501, 'name_en' => 'Speed Bonus', 'name_ja' => 'スピードボーナス'],
        ['name_en' => 'No id at all'],
        ['id' => 502, 'name_ja' => '名前なし'],
        ['id' => 503, 'name_en' => '   ', 'name_ja' => '空白'],
        'not an object',
    ]);

    $records = (new GametoraSupportEffectParser)->parse($body);

    expect($records)->toHaveCount(1)
        ->and($records[0]['effect_id'])->toBe(501)
        ->and($records[0]['name_ja'])->toBe('スピードボーナス')
        ->and($records[0]['calc'])->toBeNull();
});

it('passes a calc mode the column does not know through to the CHECK rather than emptying it', function (): void {
    // The asymmetry is deliberate and this is the line that pins it. `calc` absence carries meaning
    // (§1.4.8), so a parser that filtered an unknown mode to null would rewrite "multiplies" as
    // "does not multiply" without saying so. It reaches the column instead, and `SupportCardTest` plus
    // `StoreSupportCardsTest` show the CHECK refusing it.
    $body = (string) json_encode([['id' => 510, 'name_en' => 'New Mode', 'calc' => 'div']]);

    expect((new GametoraSupportEffectParser)->parse($body)[0]['calc'])->toBe('div');
});

it('parses nothing out of a body that is not a list of records', function (): void {
    $parser = new GametoraSupportEffectParser;

    expect($parser->parse(''))->toBe([])
        ->and($parser->parse('{"id": 1}'))->toBe([])
        ->and($parser->parse('false'))->toBe([])
        ->and($parser->parse('[1, 2, 3]'))->toBe([]);
});
