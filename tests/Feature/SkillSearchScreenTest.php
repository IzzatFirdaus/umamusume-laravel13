<?php

declare(strict_types=1);

use App\Models\MatchCandidate;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Services\DataPipeline\PipelineRunner;

/*
 * Screen D — the skill search surface (PRD FR-D-2, DESIGN.md §8.4, CONSTRAINTS.md D-62 to D-65).
 *
 * Rows arrive through the same producer path the import uses: `PipelineRunner` → `GametoraSkillsParser`
 * → `StoreSkills`. Nothing here hand-writes a `skills` row, because a test that builds the array a
 * screen reads cannot see the producer break — that is the failure KI-21 was filed against, and it is
 * the reason `Indomitable` is in the fixture (200471 and 300141, both verbatim from the live document)
 * rather than created here. Two rows, one client name, one of them badged Unique by the class-code rule
 * and named by no card: the collision the badge has to survive is data, not a fixture of this test.
 *
 * The field set is not this file's choice. `CONSTRAINTS.md` D-30 lists the permitted `Skill` surface as
 * name, name_ja, sp_cost, type, is_unique — which is exactly what the rows may print — and `ADR-0011` §3
 * forbids turning the stored class code into a rarity word, so a negative below tests for the words that
 * must never appear.
 */

/**
 * Import the fixture through the engine, so every row carries the flags the parser set.
 */
function skillScreenImport(): void
{
    app(PipelineRunner::class)->run(
        'gametora-skills',
        [...config('uma.sources.gametora-skills'), 'delay_ms' => 0],
        skillScreenFixtureBody(),
        null,
    );
}

function skillScreenFixtureBody(): string
{
    return (string) file_get_contents(base_path('tests/Fixtures/gametora-skills.sample.json'));
}

/**
 * The literal Japanese renderings (`enname`) that differ from the client string on rows Global has.
 *
 * Read off the fixture rather than re-typed here: one of them carries a word `[Global]` does not use for
 * a stat and another contains a surface word the lore gate rejects, so a test that spells a banned term to
 * prove its absence fails the gate it is checking. Same discipline as `83086b0`.
 *
 * @return list<string>
 */
function skillScreenDivergentRenderings(): array
{
    /** @var list<array<string, mixed>> $rows */
    $rows = json_decode(skillScreenFixtureBody(), true, 512, JSON_THROW_ON_ERROR);

    $renderings = [];

    foreach ($rows as $row) {
        $client = $row['name_en'] ?? null;
        $rendering = $row['enname'] ?? null;
        $unreleased = $row['unreleased'] ?? [];

        if (is_string($client) && is_string($rendering) && $rendering !== $client
            && ! (is_array($unreleased) && in_array('en', $unreleased, true))) {
            $renderings[] = $rendering;
        }
    }

    return $renderings;
}

/**
 * The visible text of every result row, so a facet test can count rows and name them in one read.
 *
 * @return list<string>
 */
function skillScreenRows(string $html): array
{
    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $rows = [];

    foreach ($xpath->query('//ul[@id="skill-results"]/li') as $li) {
        $rows[] = trim((string) preg_replace('/\s+/', ' ', $li->textContent));
    }

    return $rows;
}

it('lists the skills a Global trainer can meet and none the source has not released', function (): void {
    skillScreenImport();

    $rows = skillScreenRows($this->get(route('skills.index'))->assertOk()->content());

    // Nine of the twelve fixture rows: released on Global and named by the client. The three that are
    // not are excluded for two different reasons, and both have to fail here — `Check` has no client name,
    // and `Gluttonous Ruler` has a real English string for a skill Global has not shipped.
    expect($rows)->toHaveCount(9)
        ->and(implode(' | ', $rows))->toContain('Gourmand')
        ->and(implode(' | ', $rows))->toContain('Master of the Sands')
        ->and(implode(' | ', $rows))->not->toMatch('/Gluttonous Ruler|Raise My Soul|Check/')
        // The read path is a scope, not four where-clauses: the same filter the run screen uses.
        ->and(Skill::query()->availableOnGlobal()->count())->toBe(9)
        ->and(Skill::count())->toBe(12)
        ->and(MatchCandidate::count())->toBe(0);
});

it('matches on the normalized key, so case and spacing in the query change nothing', function (): void {
    skillScreenImport();

    // D-62: skill search matches on `match_key`, never on display strings. `Corner Adept ×` stores the key
    // `corneradept×` — no spaces — so a query typed with the space the client shows has to be folded before
    // it is compared. Matching the raw string against `name` would also pass this test, which is why the
    // query below is deliberately not a prefix of any stored value's spelling.
    $spaced = skillScreenRows($this->get(route('skills.index', ['search' => 'Corner  Adept']))->content());
    $cased = skillScreenRows($this->get(route('skills.index', ['search' => 'GOURMAND']))->content());

    expect($spaced)->toHaveCount(1)
        ->and($spaced[0])->toContain('Corner Adept ×')
        ->and($cased)->toHaveCount(1)
        ->and($cased[0])->toContain('Gourmand');
});

it('treats a LIKE wildcard as the character the Trainer typed', function (): void {
    skillScreenImport();

    // `%` and `_` are pattern characters, and `NameNormalizer` leaves them in the query. Escaped before the
    // LIKE, a search for them asks for rows whose name really contains one — and this slice has exactly one,
    // `Givin' It 1000%`, so the assertion is about the character rather than about an empty result set.
    // Unescaped, the same query returns the whole catalog, which is KI-26's finding on `/umamusume`.
    $percents = skillScreenRows($this->get(route('skills.index', ['search' => '%']))->content());

    expect($percents)->toHaveCount(1)
        ->and($percents[0])->toContain("Givin' It 1000%")
        ->and(skillScreenRows($this->get(route('skills.index', ['search' => '_']))->content()))->toBe([])
        // ...and the narrowing still works, so the escape is not simply breaking the search.
        ->and(skillScreenRows($this->get(route('skills.index', ['search' => 'gourmand']))->content()))->toHaveCount(1);
});

it('narrows on the derived type, including the rows the sign rule leaves unlabelled', function (): void {
    skillScreenImport();

    $speed = skillScreenRows($this->get(route('skills.index', ['type' => 'Speed']))->content());
    $recovery = skillScreenRows($this->get(route('skills.index', ['type' => 'Recovery']))->content());

    // 5 of the 9 Global rows have no type: the derivation refuses a negative value and refuses a skill
    // carrying two categories (ADR-0011 §4, G-SK-18). `Unspecified` is offered because that null is a state
    // the data holds, not an error — D-30 renders only what exists, and a filter that could not reach these
    // five would imply they were missing something.
    expect($speed)->toHaveCount(2)
        ->and(implode(' | ', $speed))->toContain('Certain Victory')
        ->and(implode(' | ', $speed))->not->toContain('Gourmand')
        ->and($recovery)->toHaveCount(2)
        ->and(implode(' | ', $recovery))->toContain('Gourmand')
        ->and(skillScreenRows($this->get(route('skills.index', ['type' => 'Unspecified']))->content()))->toHaveCount(5)
        ->and(skillScreenRows($this->get(route('skills.index', ['type' => 'Passive']))->content()))->toBe([]);

    // An unlabelled row prints no type word at all. `G1 Averseness` carries effect code 1 at −400000, which
    // is an averseness, not a Passive skill.
    $averseness = array_values(array_filter(
        skillScreenRows($this->get(route('skills.index'))->content()),
        static fn (string $row): bool => str_contains($row, 'G1 Averseness'),
    ));
    expect($averseness)->toHaveCount(1)
        ->and($averseness[0])->not->toMatch('/Speed|Recovery|Passive/');
});

it('narrows to the class-code unique rows and never prints a rarity word', function (): void {
    skillScreenImport();

    $unique = skillScreenRows($this->get(route('skills.index', ['unique' => '1']))->content());

    // Red Ace (code 3), Certain Victory (5) and 300141 (5). The count is what the class-code rule reaches,
    // not what the card document names — the disclosure on the badge says so in its own words.
    expect($unique)->toHaveCount(3)
        ->and(implode(' | ', $unique))->toContain('✦ Unique')
        ->and(implode(' | ', $unique))->toContain('Red Ace')
        ->and(skillScreenRows($this->get(route('skills.index'))->content()))->toHaveCount(9);

    // D-30's `rarity` is absent from D-30's permitted Skill surface, and ADR-0011 §3 keeps it that way: the
    // six class codes are learnable / evolvable / unique variants / evolved, not the client's three
    // rarities. No word for any of them may appear, and neither may the bare code dressed as a label.
    $html = $this->get(route('skills.index'))->content();

    expect($html)->not->toMatch('/\bRare\b|\bNormal\b|class code \d|rarity/i');
});

it('distinguishes a screen nobody has searched from a search that missed', function (): void {
    skillScreenImport();

    $blank = (string) preg_replace('/\s+/', ' ', $this->get(route('skills.index'))->content());
    $missed = (string) preg_replace('/\s+/', ' ', $this->get(route('skills.index', ['search' => 'runing']))->content());

    // D-65: empty search and no-results are different states. The first invites a query; the second says the
    // skill is not in the catalog. The review-queue offer D-65 also names is deliberately absent — skills
    // bypass `match_candidates` by design, so that queue can never hold one (G-SK-23 carries the amendment
    // ask), and pointing a Trainer at it would be a control that cannot do what it says.
    expect($blank)->toContain('Type part of a skill name')
        ->and($blank)->not->toContain('not in the skill catalog')
        ->and($missed)->toContain('not in the skill catalog')
        ->and($missed)->not->toContain('Type part of a skill name')
        ->and($missed)->not->toMatch('/review queue/i')
        // The normalized read is shown, because a query that folds `runing` into a key is the thing a
        // Trainer needs to see to know how it was searched.
        ->and($missed)->toContain('runing');
});

it('names the fetch when the catalog holds nothing, rather than blaming the query', function (): void {
    // No import at all, which is the state `database/database.sqlite` is in until somebody migrates and
    // fetches. A Trainer reading "not in the skill catalog" there would go looking for a typo; the honest
    // sentence names the command instead (D-65: a missing skill is a data-fetch gap, not a user error).
    $html = (string) preg_replace('/\s+/', ' ', $this->get(route('skills.index'))->assertOk()->content());

    expect($html)->toContain('php artisan uma:fetch gametora-skills')
        ->and($html)->not->toContain('not in the skill catalog')
        ->and(Skill::count())->toBe(0);
});

it('states the cost where the source does and names the kind of absence where it does not', function (): void {
    skillScreenImport();

    $html = $this->get(route('skills.index'))->content();
    $rows = skillScreenRows($html);

    // D-220 as the digest states it (`ARCHITECTURE-ESSENTIALS.md` §D-220): an absent value renders as `N/A`
    // with a `title` naming which kind of absence, never as a default and never as a bare zero. The em dash
    // is not the disclosure glyph — R-02/D-79 and KI-7 bar it in shipped copy.
    //
    // The kind matters here. A null `sp_cost` is not a fact nobody recorded: the source states a cost on
    // class codes 1 and 2 only, so a unique skill has no SP price to state. Wording it as "not yet recorded"
    // would claim a pending import that is not pending.
    expect(implode(' | ', $rows))->toContain('· 180 SP')
        ->and($html)->toContain('N/A')
        ->and($html)->not->toContain('—')
        ->and($html)->toMatch('/title="[^"]*(no SP cost|class code)[^"]*"/i');

    // Every row shows either a number or the disclosure. A row showing neither would read as a zero.
    foreach ($rows as $row) {
        expect($row)->toMatch('{\d+ SP|N/A}', "row: {$row}");
    }
});

it('badges both rows the client calls Indomitable and discloses what the badge is not', function (): void {
    skillScreenImport();

    $indomitable = array_values(array_filter(
        skillScreenRows($this->get(route('skills.index', ['search' => 'indomitable']))->content()),
        static fn (string $row): bool => str_contains($row, 'Indomitable'),
    ));

    // Two rows, one name, and the badge falls on the one no card names. This is G-SK-21, and the screen
    // renders it rather than hiding it: the learnable row states 170 SP and a Recovery effect, the class-code
    // row states no cost and a Speed effect, so the rows are distinguishable without a rarity word.
    expect($indomitable)->toHaveCount(2)
        ->and(count(array_filter($indomitable, static fn (string $r): bool => str_contains($r, '✦ Unique'))))->toBe(1)
        ->and(implode(' | ', $indomitable))->toContain('170 SP')
        // The disclosure travels with the mark, in the badge's own `title`, so a Trainer reading the badge
        // reads its basis too (D-256: a derived value prints its rule and says whose derivation it is).
        ->and($this->get(route('skills.index', ['search' => 'indomitable']))->content())
        ->toMatch('/title="[^"]*class code[^"]*"/i');
});

it('never prints a source translation, over the rendered page', function (): void {
    skillScreenImport();

    $html = $this->get(route('skills.index'))->content();

    // Screen D is where a client-string violation would actually reach a Trainer: it is the surface the 623
    // rows were imported for. Decoded first, because an apostrophe and a `×` are entity-escaped in the
    // markup and a raw not-contains could pass on the encoding rather than on the absence (KI-21's failure,
    // pointed the other way). The row below carries `'`-escaped and `×`-bearing values in the same set.
    $visible = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

    expect(skillScreenDivergentRenderings())->toHaveCount(8)
        // Positive control through the same transformation: without it the eight absences would pass on an
        // extraction that saw nothing.
        ->and($visible)->toContain('Master of the Sands')
        ->and($visible)->not->toContain(...skillScreenDivergentRenderings());
});

it('pages through the catalog with the token-overridden pagination chrome', function (): void {
    skillScreenImport();

    $first = $this->get(route('skills.index', ['pageSize' => '3', 'page' => '1']))->content();
    $second = $this->get(route('skills.index', ['pageSize' => '3', 'page' => '2']))->content();

    $firstRows = skillScreenRows($first);
    $secondRows = skillScreenRows($second);

    expect($firstRows)->toHaveCount(3)
        ->and($secondRows)->toHaveCount(3)
        ->and(array_intersect($firstRows, $secondRows))->toBe([])
        // The published vendor view renders this; nothing on this screen restates it.
        ->and($first)->toContain('Pagination Navigation')
        // Rows come back in the same order the run screen's picker uses, so the two surfaces cannot offer
        // the same catalog in two different orders.
        ->and($firstRows[0])->toContain('Certain Victory');

    // A page past the end is an empty page, not an error and not the last page repeated.
    expect(skillScreenRows($this->get(route('skills.index', ['pageSize' => '3', 'page' => '9']))->content()))->toBe([]);
});

it('refuses a facet value the data has no such column for rather than ignoring it', function (): void {
    skillScreenImport();

    // Silently dropping an unknown `type` would answer a question nobody asked with the full catalog, which
    // is how a facet lies: the Trainer reads eight results as "these are the Speed skills". The form request
    // rejects it and the page comes back naming the field.
    $this->get(route('skills.index', ['type' => 'Debuff']))
        ->assertRedirect()
        ->assertSessionHasErrors('type');

    // The whole derivable set, so an option added to the form without a value behind it is visible here.
    $derived = Skill::query()->pluck('type')->filter()->unique()->values()->sort()->values()->all();

    expect($derived)->toBe(['Recovery', 'Speed']);
});

it('is reachable from the navigation and from the run screen it exists to relieve', function (): void {
    skillScreenImport();
    $run = TrainingRun::factory()->create();

    $runScreen = $this->get(route('runs.show', $run))->content();

    // G-SK-13: the run screen's picker lists every Global row in one select, and Screen D is the designed
    // answer. A route nobody can reach is a defect, so both the shell and the picker point at it.
    expect($runScreen)->toContain(route('skills.index'))
        ->and($this->get(route('skills.index'))->content())->toContain('Skill search');
});
