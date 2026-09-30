<?php

declare(strict_types=1);

use App\Services\DataPipeline\Parsers\GametoraCharacterCardParser;
use App\Services\DataPipeline\Parsers\GametoraCharacterParser;

/**
 * @return list<array<string, mixed>>
 */
function globalCardRecords(): array
{
    return (new GametoraCharacterCardParser)->parse(globalCardBody());
}

function globalCardBody(): string
{
    return file_get_contents(base_path('tests/Fixtures/gametora-character-cards.global.sample.json')) ?: '';
}

it('emits one record per Global card and drops every JP-only one', function (): void {
    $cardIds = array_column(globalCardRecords(), 'card_id');

    // Eleven rows in, seven with a Global date. The brief said eight; its own
    // parenthetical names four [JP-Only] forms — Gold Ship's La Mode 564, Tokai
    // Teio's Dream Butterfly and both Fenomeno forms — and 11 - 4 is 7. That matches
    // the errata this plan was written from: Special Week has three Global forms and
    // Gold Ship two (docs/requests/2026-09-29-catalog-roster-and-trainee-selector.md
    // E-2, E-3), so the fixture carries 3 + 2 + 2.
    expect($cardIds)->toHaveCount(7)
        ->and($cardIds)->not->toContain(100703)
        ->and($cardIds)->not->toContain(100303)
        ->and($cardIds)->not->toContain(112701)
        ->and($cardIds)->not->toContain(112702);
});

it('keeps the [Global] client title verbatim, brackets and all', function (): void {
    $titles = array_column(globalCardRecords(), 'title');

    // The Global string is not the JP string with brackets: the export's own `title`
    // for card 100702 is "Run! Fun! Watergun!". CONSTRAINTS.md:38 bars editing a
    // verbatim client name, so the guard belongs on the display path, never here.
    expect($titles)->toContain('[RUN! RUIN! LAUNCHER!]')
        ->and($titles)->toContain('[Hopp\'n♪Happy Heart]')
        ->and($titles)->not->toContain('Run! Fun! Watergun!')
        ->and($titles)->not->toContain('Supreme Commander of the Rising Sun');
});

it('flags exactly one debut form per trainee, the earliest JP release', function (): void {
    $byCard = [];
    foreach (globalCardRecords() as $record) {
        $byCard[$record['card_id']] = $record;
    }

    expect($byCard[100101]['is_debut_form'])->toBeTrue()
        ->and($byCard[100102]['is_debut_form'])->toBeFalse()
        ->and($byCard[100103]['is_debut_form'])->toBeFalse()
        ->and($byCard[100701]['is_debut_form'])->toBeTrue()
        ->and($byCard[100702]['is_debut_form'])->toBeFalse()
        // Gold Ship's debut is her JP-earliest card, and it is 2-star: the debut flag
        // is not a rarity claim, which is why the trainee row shows both separately.
        ->and($byCard[100701]['rarity'])->toBe(2);
});

it('links each card to its trainee by the ref the character parser writes', function (): void {
    $records = globalCardRecords();

    expect($records[0]['char_external_ref'])->toBe('gametora:char:1001')
        ->and(array_unique(array_column($records, 'char_external_ref')))->toHaveCount(3);
});

it('derives the debut through the same code path as the character catalog', function (): void {
    $cards = json_decode(globalCardBody(), true, 512, JSON_THROW_ON_ERROR);
    $characters = (new GametoraCharacterParser)->parse(globalCardBody());
    $debutForms = GametoraCharacterParser::debutForms($cards);

    // Two parsers, one rule: every card row's debut flag is the character catalog's
    // own pick for that trainee, read out of the single derivation both grains call.
    // If the surfaces ever split, this loop names the card they disagree about.
    foreach (globalCardRecords() as $record) {
        $charId = (int) str_replace('gametora:char:', '', $record['char_external_ref']);

        expect($record['is_debut_form'])->toBe((int) $debutForms[$charId]['card_id'] === $record['card_id']);
    }

    // The brief expected three names here and no `Fenomeno`. That is the card grain's
    // rule, not the character grain's: GametoraCharacterParser records a trainee with
    // no Global form as JapanOnly rather than dropping her, which is what
    // GametoraCharacterParserTest's Zenno Rob Roy case pins. So Fenomeno is a catalog
    // row and owns no card row, and the order is char_id order — ksort, unchanged by
    // the extraction.
    expect(array_column($characters, 'name'))->toBe(['Special Week', 'Tokai Teio', 'Gold Ship', 'Fenomeno'])
        ->and(array_column($characters, 'external_ref'))->toBe([
            'gametora:char:1001',
            'gametora:char:1003',
            'gametora:char:1007',
            'gametora:char:1127',
        ])
        ->and(array_column(globalCardRecords(), 'char_external_ref'))->not->toContain('gametora:char:1127');
});

it('returns nothing for a body that is not a JSON array', function (): void {
    expect((new GametoraCharacterCardParser)->parse('not json'))->toBe([])
        ->and((new GametoraCharacterCardParser)->parse('{"object":true}'))->toBe([]);
});

it('skips a row with no usable card id, no client title, or no real Global date', function (): void {
    $body = json_encode([
        ['card_id' => null, 'char_id' => 1001, 'title_en_gl' => '[No Id]', 'rarity' => 3, 'release_en' => '2025-06-26'],
        ['card_id' => 100102, 'char_id' => 1001, 'title_en_gl' => null, 'rarity' => 3, 'release_en' => '2025-10-14'],
        ['card_id' => 100103, 'char_id' => 1001, 'title_en_gl' => '[Sentinel]', 'rarity' => 3, 'release_en' => '9999-12-31'],
        ['card_id' => 100104, 'char_id' => 1001, 'title_en_gl' => '[Not A Date]', 'rarity' => 3, 'release_en' => 'soon'],
        ['card_id' => 100105, 'char_id' => 1001, 'title_en_gl' => '[Bad Rarity]', 'rarity' => 7, 'release_en' => '2025-06-26'],
    ], JSON_THROW_ON_ERROR);

    // 9999-12-31 is the export's own placeholder (GametoraCharacterParser::UNKNOWN_DATE),
    // and a rarity outside 1..3 is a claim this app has no word for. Neither becomes a
    // row: dateOrNull() accepts the placeholder's shape, so the card read rejects it.
    expect((new GametoraCharacterCardParser)->parse($body))->toBe([]);
});

it('refuses a Global card with no client title rather than fall back to the Japanese-side string', function (): void {
    $body = json_encode([[
        'card_id' => 100702,
        'char_id' => 1007,
        'name_en' => 'Gold Ship',
        'rarity' => 3,
        'release' => '2022-07-29',
        // Every guard before the title one passes: this is a real Global date and a real
        // card id. Only `title_en_gl` is absent, and `title` is present — the exact row
        // shape that makes `$card['title_en_gl'] ?? $card['title']` look harmless. It is
        // not harmless: `title` here is "Run! Fun! Watergun!", the Japanese-side string,
        // and the card ships on [Global] as "[RUN! RUIN! LAUNCHER!]". CONSTRAINTS.md:38
        // forbids editing a verbatim client name, so a row carrying the wrong one of the
        // two is a silent data error no later stage can detect.
        'title' => 'Run! Fun! Watergun!',
        'release_en' => '2026-07-02',
    ]], JSON_THROW_ON_ERROR);

    // This test exists to fail if someone reintroduces the chain, the sibling of
    // GametoraCharacterParserTest's "never accepts the wrong source key as a silent
    // fallback", which covers the name_jp / name_ja version of the same defect.
    expect((new GametoraCharacterCardParser)->parse($body))->toBe([]);
});

it('emits cards for a trainee with no usable name_en and flags none of them as her debut form', function (): void {
    // KNOWN GAP, pinned as behaviour rather than fixed. `debutForms()` skips any row whose
    // `name_en` is blank or missing (GametoraCharacterParser `:111`), so no debut id is ever
    // recorded for that char_id, while the card grain's own guards do not read `name_en` at
    // all and emit her cards. The result is a trainee with card rows and no
    // `is_debut_form: true` anywhere — against ADR-0008's "`is_debut_form` is derived,
    // never copied" paragraph, which says the flag "is the earliest JP `release` among
    // that trainee's cards", and against `PRD.md` FR-A-6, whose
    // roster nests cards under a trainee. The consequence is display-shaped: a catalog tree
    // with no heading form for her, and Task 7's store failing to resolve the ref she does
    // own. Whether a nameless trainee is a data defect upstream or a row to tolerate is
    // Task 8's cross-check question, so the parser is deliberately unchanged here.
    $body = json_encode([
        ['card_id' => 109901, 'char_id' => 1099, 'name_en' => '  ', 'title_en_gl' => '[First Form]', 'rarity' => 3, 'release' => '2024-01-09', 'release_en' => '2025-06-26'],
        ['card_id' => 109902, 'char_id' => 1099, 'title_en_gl' => '[Second Form]', 'rarity' => 2, 'release' => '2025-03-01', 'release_en' => '2026-02-12'],
    ], JSON_THROW_ON_ERROR);

    $records = (new GametoraCharacterCardParser)->parse($body);

    expect($records)->toHaveCount(2)
        ->and(array_column($records, 'card_id'))->toBe([109901, 109902])
        ->and(array_column($records, 'is_debut_form'))->toBe([false, false])
        // The character grain, which is where the name is required, records nothing for her
        // at all: two grains, two answers to "does this trainee exist".
        ->and((new GametoraCharacterParser)->parse($body))->toBe([]);
});

it('treats a card id as a string or an int without losing its identity', function (): void {
    $body = json_encode([
        [
            'card_id' => '100701',
            'char_id' => '1007',
            // The shared derivation only considers a card whose trainee has a name, so
            // a body without `name_en` has no debut to flag. That is the character
            // parser's own rule: its blank-name case drops such a row outright
            // (GametoraCharacterParserTest, "entry without a display name").
            'name_en' => 'Gold Ship',
            'title_en_gl' => '[Red Strife]',
            'rarity' => '2',
            'release_en' => '2025-06-26',
            'release' => '2021-02-24',
        ],
    ], JSON_THROW_ON_ERROR);

    $records = (new GametoraCharacterCardParser)->parse($body);

    // The column is typed and the card_id is the join key, so a numeric string from a
    // hand-made body must land as an int rather than as a loose match somewhere later.
    expect($records)->toHaveCount(1)
        ->and($records[0]['card_id'])->toBe(100701)
        ->and($records[0]['rarity'])->toBe(2)
        ->and($records[0]['is_debut_form'])->toBeTrue();
});

/*
 * KI-33: the source publishes a trainee's own skill lists and this parser used to drop them, which is
 * why `run_skills` could not be pre-populated and D-44's `Suggested` state had nothing to seed from
 * from. The two fixture rows below carry the ids `KNOWN-ISSUES.md` KI-33 cites from the live
 * `character-cards` document at hash `e9e9ee6d` — cited, not re-fetched — and Gold Ship is the row
 * that decides the column's shape: she carries **two** uniques, so a scalar column would silently
 * drop one of them and no later stage could tell.
 */
it('keeps the innate and unique lists the source publishes, as lists', function (): void {
    $byCard = [];

    foreach (globalCardRecords() as $record) {
        $byCard[$record['card_id']] = $record;
    }

    expect($byCard[100101]['skills_innate'])->toBe([200512, 201352, 200732])
        ->and($byCard[100101]['skills_unique'])->toBe([100011])
        ->and($byCard[100701]['skills_innate'])->toBe([201591, 201212, 201472])
        // Two values, both kept, in the source's own order.
        ->and($byCard[100701]['skills_unique'])->toBe([10071, 100071]);
});

it('emits an empty list rather than a null for a card the document gives no lists', function (): void {
    $byCard = [];

    foreach (globalCardRecords() as $record) {
        $byCard[$record['card_id']] = $record;
    }

    // 100102 is a real Global card the fixture carries without either key. `[]` and `null` are
    // different claims downstream — one is "she has none", the other is "nobody looked" — and the
    // pre-populate reads this column, so the shape has to be pinned here.
    expect($byCard[100102]['skills_innate'])->toBe([])
        ->and($byCard[100102]['skills_unique'])->toBe([]);
});

it('refuses to turn a malformed skill list into a crash or a half-row', function (mixed $value, array $expected, string $why): void {
    $body = json_encode([[
        'card_id' => 100101,
        'char_id' => 1001,
        'name_en' => 'Special Week',
        'title_en_gl' => '[Special Dreamer]',
        'rarity' => 3,
        'release_en' => '2025-06-26',
        'skills_unique' => $value,
    ]], JSON_THROW_ON_ERROR);

    $records = (new GametoraCharacterCardParser)->parse($body);

    expect($records)->toHaveCount(1)
        ->and($records[0]['skills_unique'])->toBe($expected, $why)
        // The card itself still lands: a bad list is not a reason to lose the row.
        ->and($records[0]['card_id'])->toBe(100101);
})->with([
    'a string' => ['not an array', [], 'a scalar where a list belongs is not one skill, it is no skill'],
    'a nested array' => [[[10071], [100071]], [], 'ints only; a nested array has no id at its top level'],
    'a mixed list' => [[10071, '100071', 0, -5, null, 'x'], [10071, 100071], 'numeric strings are ids, zero and negatives are not'],
    'an associative array' => [['a' => 10071], [10071], 'keys are not part of the contract, values are'],
    'null' => [null, [], 'absent is empty, not a null column'],
]);
