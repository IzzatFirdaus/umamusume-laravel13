<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Services\DataPipeline\Parsers\GametoraSkillsParser;

/**
 * Nine records cut verbatim out of the live `skills` document (manifest hash `609afe88`, pulled
 * 2026-09-29) and committed as `tests/Fixtures/gametora-skills.sample.json`.
 *
 * **The fixture is copied from the source rather than written to match this parser, and that is the
 * test's main feature.** `KI-23` is a parser reading `name_ja` from a document whose key is `name_jp`,
 * with a hand-written fixture using the same wrong key — so the suite agreed with the bug and could not
 * see it. Every key here is the document's, spelled the way the document spells it.
 *
 * Each row carries only the eight keys this parser reads (`id, name_en, enname, jpname, rarity, cost,
 * unreleased, condition_groups`). The localisation blobs, the descriptions and the relationship fields
 * were dropped: that took the file from 22 KB to 4.5 KB and four `lore-code` hits down to one. It is the
 * KI-23 discipline pointed the other way — a fixture holds what the code touches, not whatever happened
 * to be sitting beside it in the source. The one surviving `lore-code` hit is the `enname` of export id
 * 202001 — a value this parser reads (it is the name fallback), a value the test below proves this parser
 * never emits, and a value `SkillsFetchTest.php` proves never reaches the run screen. Removing it would be
 * editing the data to quiet a grep.
 *
 * Chosen to carry one case each that the source actually differs on, not one case per branch invented
 * here: a client name that diverges from the literal rendering (`G1 Averseness` / `G1 Dislike`), a row
 * with no client name at all (202711), a row with a client name that is not on `[Global]` (100101111),
 * a negative effect on an otherwise mapped code (200311), and two categories in one skill (202001).
 *
 * @return array<int, array<string, mixed>> keyed by the export's own skill id
 */
function skillsFixtureById(): array
{
    $body = (string) file_get_contents(base_path('tests/Fixtures/gametora-skills.sample.json'));
    $rows = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

    expect(is_array($rows))->toBeTrue();

    /** @var array<int, array<string, mixed>> $keyed */
    $keyed = array_column($rows, null, 'id');

    return $keyed;
}

/**
 * @return array<int, array<string, mixed>> parsed records keyed by export_id
 */
function parseSkillsFixture(): array
{
    $records = (new GametoraSkillsParser)->parse(
        (string) file_get_contents(base_path('tests/Fixtures/gametora-skills.sample.json'))
    );

    /** @var array<int, array<string, mixed>> $keyed */
    $keyed = array_column($records, null, 'export_id');

    return $keyed;
}

it('parses every record the document slice carries', function (): void {
    expect(parseSkillsFixture())->toHaveCount(9);
});

it('reads the Japanese name from the key this document uses', function (): void {
    // The direct guard against KI-23: the source spells it `jpname` here and `name_jp` in the card
    // document, and a fixture that agreed with a wrong guess is what let that live for a month.
    $fixture = skillsFixtureById();
    expect(array_filter($fixture, static fn (array $row): bool => array_key_exists('name_ja', $row)))->toBe([])
        ->and(array_filter($fixture, static fn (array $row): bool => isset($row['jpname'])))->toHaveCount(9);

    foreach (parseSkillsFixture() as $record) {
        expect($record['name_ja'])->not->toBeNull();
    }
});

it('takes the English name from name_en, not from the literal rendering of the Japanese', function (): void {
    $records = parseSkillsFixture();

    expect($records[200311]['name'])->toBe('G1 Averseness')
        ->and($records[110031]['name'])->toBe('Certain Victory')
        ->and($records[202001]['name'])->toBe('Master of the Sands')
        // The three renderings this file must never print as client copy. Named through the fixture
        // rather than re-typed: one of them carries a word `[Global]` does not use for a stat, and a
        // test that spells a banned term to prove its absence is a test that fails the gate it checks.
        ->and(array_column($records, 'name'))->not->toContain('G1 Dislike')
        ->and(array_column($records, 'name'))->not->toContain("It's Going to Be Me")
        ->and(array_column($records, 'name'))->not->toContain(skillsFixtureById()[202001]['enname']);
});

it('falls back to the rendering when no client name exists, and marks that it did', function (): void {
    $row = parseSkillsFixture()[202711];

    expect($row['name'])->toBe('Check')
        ->and($row['name_is_client'])->toBeFalse()
        ->and($row['release_status'])->toBe(ReleaseStatus::JapanOnly->value);
});

it('does not read a client-shaped name on an unreleased row as client copy', function (): void {
    $records = parseSkillsFixture();

    // ADR-0011: 362 rows carry name_en without being on [Global]. REFERENCE §2.7 (Air Messiah) is the
    // ruling — an English string on something the server has not shipped is a third-party label.
    expect($records[100101111]['name'])->toBe('Gluttonous Ruler')
        ->and($records[100101111]['name_is_client'])->toBeFalse()
        ->and($records[100471]['name'])->toBe("Raise My Soul's Blade!")
        ->and($records[100471]['name_is_client'])->toBeFalse();
});

it('states availability from the source flag and nowhere else', function (): void {
    $records = parseSkillsFixture();
    $global = array_keys(array_filter($records, static fn (array $r): bool => $r['release_status'] === ReleaseStatus::GlobalReleased->value));

    sort($global);

    // Every row absent `unreleased` is Global; the three with it are not. Sorted, so the expectation
    // reads in the order a reader can check it against the fixture.
    expect($global)->toBe([10091, 110031, 200311, 200333, 201351, 202001])
        ->and(count($global))->toBe(6);
});

it('name_is_client is exactly the intersection of a client name and availability on Global', function (): void {
    foreach (parseSkillsFixture() as $id => $record) {
        $expectClient = $record['release_status'] === ReleaseStatus::GlobalReleased->value
            && isset(skillsFixtureById()[$id]['name_en']);

        expect($record['name_is_client'])->toBe($expectClient, "export id {$id}");
    }
});

it('keeps the export class code and emits no rarity word', function (): void {
    $records = parseSkillsFixture();

    expect($records[200311]['rarity'])->toBe(1)
        ->and($records[201351]['rarity'])->toBe(2)
        ->and($records[10091]['rarity'])->toBe(3)
        ->and($records[110031]['rarity'])->toBe(5)
        ->and($records[100101111]['rarity'])->toBe(6)
        // Six codes where the client has three rarities: nothing here is a label.
        ->and(array_column($records, 'rarity'))->toContain(6)
        ->and(array_values(array_filter(array_column($records, 'rarity'), static fn (?int $v): bool => $v > 3)))->toHaveCount(3);

    // Six codes where the client has three rarities: nothing here is a label. Only three values appear
    // across the fixture, and one of them is null — the refusal is part of the result, not a gap in it.
    $types = array_values(array_unique(array_column($records, 'type')));
    sort($types);

    expect($types)->toBe([null, 'Recovery', 'Speed'])
        ->and(array_diff($types, [null, 'Speed', 'Recovery', 'Passive']))->toBe([]);
});

it('calls a skill unique by the class codes the card document names', function (): void {
    $records = parseSkillsFixture();

    // 3, 4 and 5 are what a card's `skills_unique` reaches; 1, 2 and 6 are not (ADR-0011 §5).
    expect($records[110031]['is_unique'])->toBeTrue()   // rarity 5
        ->and($records[10091]['is_unique'])->toBeTrue() // rarity 3
        ->and($records[201351]['is_unique'])->toBeFalse() // rarity 2
        ->and($records[100101111]['is_unique'])->toBeFalse(); // rarity 6, evolved
});

it('stores the cost the source states and leaves it null where the source states none', function (): void {
    $records = parseSkillsFixture();

    expect($records[200311]['sp_cost'])->toBe(50)
        ->and($records[201351]['sp_cost'])->toBe(180)
        ->and($records[200333]['sp_cost'])->toBe(100)
        // Uniques and evolved forms are not bought with SP: cost is absent on all of rarity 3..6.
        ->and($records[110031]['sp_cost'])->toBeNull()
        ->and($records[10091]['sp_cost'])->toBeNull()
        ->and($records[100101111]['sp_cost'])->toBeNull();
});

it('derives the category only where the code and the sign both agree', function (): void {
    $records = parseSkillsFixture();

    expect($records[201351]['type'])->toBe('Recovery')   // code 9, +550: a Stamina recovery mid-race
        ->and($records[110031]['type'])->toBe('Speed')   // code 27, +4500: "increase velocity"
        ->and($records[100471]['type'])->toBe('Speed');  // code 27 twice, still one category

    // The three refusals, each for a different reason.
    expect($records[202001]['type'])->toBeNull()   // 9 and 27: recovery *and* velocity is not one type
        ->and($records[200311]['type'])->toBeNull() // code 1, but -400000: an averseness, not a Passive
        ->and($records[200333]['type'])->toBeNull() // code 21 is unmapped, and negative besides
        ->and($records[202711]['type'])->toBeNull(); // code 31 is unmapped
});

it('drops a body that is not a list of records instead of throwing', function (string $body, string $why) {
    expect((new GametoraSkillsParser)->parse($body))->toBe([]);
})->with([
    'not json' => ['{nope', 'unparseable body'],
    'a map, not a list' => ['{"skills": []}', 'decoded to a map, so the loop sees scalars'],
    'empty list' => ['[]', 'nothing to import'],
    'rows without an id' => ['[{"name_en": "No Id"}]', 'a row this table cannot key'],
    'rows with no name at all' => ['[{"id": 1}]', 'a row whose display name would be a number'],
]);
