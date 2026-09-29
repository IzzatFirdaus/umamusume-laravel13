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
