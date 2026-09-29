<?php

declare(strict_types=1);

use App\Services\DataPipeline\Parsers\GametoraCharacterProfileParser;

/**
 * Fixture shape mirrors `characters` from the GameTora manifest, re-read 2026-09-30.
 *
 * Every row is a real one from the body, copied whole rather than trimmed to the fields the
 * parser reads, so the test exercises the document as it arrives — including `sex` and `race`,
 * which the parser must ignore. G-16 forbids invented fixture copy, and these are four real
 * trainees whose gaps are the three the roster actually has.
 */
beforeEach(function (): void {
    $this->parser = new GametoraCharacterProfileParser;
    $this->body = (string) file_get_contents(__DIR__.'/../Fixtures/gametora-characters.sample.json');
});

/**
 * @return array<string, mixed>
 */
function profileRow(GametoraCharacterProfileParser $parser, string $body, int $charId): array
{
    foreach ($parser->parse($body) as $row) {
        if ($row['char_external_ref'] === "gametora:char:{$charId}") {
            return $row;
        }
    }

    test()->fail("no parsed row for char_id {$charId}");
}

it('resolves every row through the same external_ref shape the character parser writes', function (): void {
    $rows = $this->parser->parse($this->body);

    expect($rows)->toHaveCount(4)
        ->and(array_column($rows, 'char_external_ref'))
        ->toBe([
            'gametora:char:1001',
            'gametora:char:1095',
            'gametora:char:2001',
            'gametora:char:9040',
        ]);
});

it('reads the six profile fields off a complete row', function (): void {
    $row = profileRow($this->parser, $this->body, 1001);

    expect($row['name_ja'])->toBe('スペシャルウィーク')
        ->and($row['va_ja'])->toBe('和氣あず未')
        ->and($row['va_en'])->toBe('Azumi Waki')
        ->and($row['birth_year'])->toBe(1995)
        ->and($row['birth_month'])->toBe(5)
        ->and($row['birth_day'])->toBe(2)
        ->and($row['height'])->toBe(158)
        ->and($row['three_sizes_b'])->toBe(81)
        ->and($row['three_sizes_h'])->toBe(81)
        ->and($row['three_sizes_w'])->toBe(56);
});

it('reads three_sizes as the object the body carries, not a rendering of it', function (): void {
    // The earlier probe reported three_sizes as a space-separated string. The body is an object,
    // and a parser reading a string here would find nothing and silently store nothing.
    $row = profileRow($this->parser, $this->body, 1095);

    expect($row['three_sizes_b'])->toBeNull()
        ->and(profileRow($this->parser, $this->body, 1001)['three_sizes_b'])->toBeInt();
});

it('keeps the Japanese voice actor and drops the absent English one', function (): void {
    // Believe is one of the 26 of 163 source rows with no va_en. The field the page shows is
    // va_ja, and that one is present on every roster row — so the two casts cannot share a rule.
    $row = profileRow($this->parser, $this->body, 1095);

    expect($row['va_ja'])->toBe('秋山実咲')
        ->and($row['va_en'])->toBeNull();
});

it('stores va_en as the romanisation of va_ja, never a separate English dub cast', function (): void {
    // Dated erratum 2026-09-30 corrects an earlier docblock reading that called va_en "the English
    // dub cast." The body says otherwise: on Special Week va_ja '和氣あず未', va_en 'Azumi Waki' and
    // va_ko '와키 아즈미' are one performer in three scripts. This pins the reading the parser must
    // keep — both casts stored verbatim, no field preferred over the other — so a future "show the
    // English one only" branch fails here rather than rendering Japanese script to a Global reader.
    $week = profileRow($this->parser, $this->body, 1001);

    expect($week['va_ja'])->toBe('和氣あず未')
        ->and($week['va_en'])->toBe('Azumi Waki')
        // The two are the same person, and the parser carries both without collapsing or preferring.
        ->and($week['va_ja'])->not->toBe($week['va_en'])
        // va_ko is refused: two casts are stored, not four scripts of one name.
        ->and(array_keys($week))->not->toContain('va_ko');

    // Where the stage name is already romanised, all three source fields hold the identical string
    // — the proof that they are scripts of one name, not three casts. Built in-memory because the
    // fixture has no such row, and race 'uma' so the row still parses under the coming filter.
    $teio = (new GametoraCharacterProfileParser)->parse((string) json_encode([
        [
            'char_id' => 1002,
            'race' => 'uma',
            'jp_name' => 'トウカイテイオー',
            'va_ja' => 'Machico',
            'va_en' => 'Machico',
            'va_ko' => 'Machico',
        ],
    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE))[0];

    expect($teio['va_ja'])->toBe('Machico')
        ->and($teio['va_en'])->toBe('Machico');
});

it('keeps a row whose every optional field is absent', function (): void {
    // Darley Arabian: no va_en, no three_sizes, no birth_year. A parser that dropped this row
    // would be inventing absence, and the page would then say "not fetched" rather than the
    // truer "this document says little about her".
    $row = profileRow($this->parser, $this->body, 9040);

    expect($row['va_ja'])->toBe('進藤尚美')
        ->and($row['va_en'])->toBeNull()
        ->and($row['birth_year'])->toBeNull()
        ->and($row['birth_month'])->toBe(2)
        ->and($row['birth_day'])->toBe(24)
        ->and($row['height'])->toBe(165)
        ->and($row['three_sizes_b'])->toBeNull()
        ->and($row['three_sizes_h'])->toBeNull()
        ->and($row['three_sizes_w'])->toBeNull();
});

it('reports a missing year as a missing year rather than assembling a date', function (): void {
    // Happy Meek is one of the 17 of 163 rows with no birth_year. Month and day are present on
    // all 163, so the parser must not substitute a year to make a complete date.
    $row = profileRow($this->parser, $this->body, 2001);

    expect($row['birth_year'])->toBeNull()
        ->and($row['birth_month'])->toBe(3)
        ->and($row['birth_day'])->toBe(20);
});

it('emits three nulls for a two-thirds three_sizes rather than a partial measurement', function (): void {
    $body = json_encode([
        ['char_id' => 7, 'jp_name' => 'テスト', 'three_sizes' => ['b' => 80, 'h' => 80]],
    ], JSON_UNESCAPED_UNICODE);

    $row = $this->parser->parse((string) $body)[0];

    expect($row['three_sizes_b'])->toBeNull()
        ->and($row['three_sizes_h'])->toBeNull()
        ->and($row['three_sizes_w'])->toBeNull();
});

it('ignores sex and race rather than storing them', function (): void {
    // The body carries both on every row (`"race": "uma"`, `"sex": 1`). Neither is one of the
    // six fields the block shows, and CONSTRAINTS.md C-4 governs how this tool names these
    // characters, so the parser's output shape must not have a place to put them.
    $row = profileRow($this->parser, $this->body, 1001);

    expect(array_keys($row))->toBe([
        'char_external_ref',
        'name_ja',
        'va_ja',
        'va_en',
        'birth_year',
        'birth_month',
        'birth_day',
        'height',
        'three_sizes_b',
        'three_sizes_h',
        'three_sizes_w',
    ]);
});

it('skips a row with no char_id and a row that is not an array', function (): void {
    $body = json_encode([
        ['jp_name' => 'no id here'],
        'not an array',
        ['char_id' => 0, 'jp_name' => 'zero id'],
        ['char_id' => '4242', 'jp_name' => 'kept'],
    ], JSON_UNESCAPED_UNICODE);

    $rows = $this->parser->parse((string) $body);

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['char_external_ref'])->toBe('gametora:char:4242');
});

it('returns nothing for a body that is not JSON', function (): void {
    expect($this->parser->parse('{ not json'))->toBe([])
        ->and($this->parser->parse('"a string"'))->toBe([]);
});
