<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Models\MatchCandidate;
use App\Models\Skill;
use App\Services\DataPipeline\PipelineRunner;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Screen D — the skill search surface (PRD FR-D-2, DESIGN.md §8.4, CONSTRAINTS.md D-62 to D-65).
 *
 * Ported to Inertia + Vue (ADR-0020 §1): behavior is asserted on the page props here, and the
 * rendered copy and the accessibility path move to `tests/browser/skills.spec.ts` (the port recipe
 * in `docs/proposals/frontend-development-plan.md` §5.1).
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

it('lists the skills a Global trainer can meet and none the source has not released', function (): void {
    skillScreenImport();

    $this->get(route('skills.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Skills/Index')
            ->has('skills.data', 9)
            ->where('totalCount', 9)
            ->where('search', null)
            ->where('skills.data', function (Collection $rows): bool {
                $names = collect($rows)->pluck('name');

                return $names->contains('Gourmand')
                    && $names->contains('Master of the Sands')
                    && ! $names->contains('Gluttonous Ruler')
                    && ! $names->contains('Raise My Soul')
                    && ! $names->contains('Check');
            }));

    // The read path is a scope, not four where-clauses: the same filter the run screen uses.
    expect(Skill::query()->availableOnGlobal()->count())->toBe(9)
        ->and(Skill::count())->toBe(12)
        ->and(MatchCandidate::count())->toBe(0);
});

it('matches on the normalized key, so case and spacing in the query change nothing', function (): void {
    skillScreenImport();

    // D-62: skill search matches on `match_key`, never on display strings. `Corner Adept ×` stores the key
    // `corneradept×` — no spaces — so a query typed with the space the client shows has to be folded before
    // it is compared. Matching the raw string against `name` would also pass this test, which is why the
    // query below is deliberately not a prefix of any stored value's spelling.
    $this->get(route('skills.index', ['search' => 'Corner  Adept']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('skills.data', 1)
            ->where('skills.data.0.name', 'Corner Adept ×'));

    $this->get(route('skills.index', ['search' => 'GOURMAND']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('skills.data', 1)
            ->where('skills.data.0.name', 'Gourmand'));
});

it('treats a LIKE wildcard as the character the Trainer typed', function (): void {
    skillScreenImport();

    // `%` and `_` are pattern characters, and `NameNormalizer` leaves them in the query. Escaped before the
    // LIKE, a search for them asks for rows whose name really contains one — and this slice has exactly one,
    // `Givin' It 1000%`, so the assertion is about the character rather than about an empty result set.
    // Unescaped, the same query returns the whole catalog, which is KI-26's finding on `/umamusume`.
    $this->get(route('skills.index', ['search' => '%']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('skills.data', 1)
            ->where('skills.data.0.name', "Givin' It 1000%"));

    $this->get(route('skills.index', ['search' => '_']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('skills.data', 0));

    // ...and the narrowing still works, so the escape is not simply breaking the search.
    $this->get(route('skills.index', ['search' => 'gourmand']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('skills.data', 1));
});

it('narrows on the derived type, including the rows the sign rule leaves unlabelled', function (): void {
    skillScreenImport();

    // 5 of the 9 Global rows have no type: the derivation refuses a negative value and refuses a skill
    // carrying two categories (ADR-0011 §4, G-SK-18). `Unspecified` is offered because that null is a state
    // the data holds, not an error — D-30 renders only what exists, and a filter that could not reach these
    // five would imply they were missing something.
    $this->get(route('skills.index', ['type' => 'Speed']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('skills.data', 2)
            ->where('skills.data', fn (Collection $rows): bool => collect($rows)->pluck('name')->contains('Certain Victory')
                && ! collect($rows)->pluck('name')->contains('Gourmand')));

    $this->get(route('skills.index', ['type' => 'Recovery']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('skills.data', 2)
            ->where('skills.data', fn (Collection $rows): bool => collect($rows)->pluck('name')->contains('Gourmand')));

    $this->get(route('skills.index', ['type' => 'Unspecified']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('skills.data', 5));

    $this->get(route('skills.index', ['type' => 'Passive']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('skills.data', 0));

    // An unlabelled row prints no type word at all. `G1 Averseness` carries effect code 1 at −400000, which
    // is an averseness, not a Passive skill.
    $this->get(route('skills.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('skills.data', function (Collection $rows): bool {
                $row = collect($rows)->firstWhere('name', 'G1 Averseness');

                return $row !== null && $row['type'] === null;
            }));
});

it('narrows to the class-code unique rows and never prints a rarity word', function (): void {
    skillScreenImport();

    // Red Ace (code 3), Certain Victory (5) and 300141 (5). The count is what the class-code rule reaches,
    // not what the card document names — the disclosure on the badge says so in its own words.
    $this->get(route('skills.index', ['unique' => '1']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('skills.data', 3)
            ->where('skills.data', fn (Collection $rows): bool => collect($rows)->pluck('name')->contains('Red Ace')));

    $this->get(route('skills.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('skills.data', 9));

    // D-30's `rarity` is absent from D-30's permitted Skill surface, and ADR-0011 §3 keeps it that way: the
    // six class codes are learnable / evolvable / unique variants / evolved, not the client's three
    // rarities. The payload is what the page renders, so no word for any of them may appear in it, and
    // neither may the bare code dressed as a label.
    expect($this->get(route('skills.index'))->content())->not->toMatch('/\bRare\b|\bNormal\b|class code \d|rarity/i');
});

it('distinguishes a screen nobody has searched from a search that missed', function (): void {
    skillScreenImport();

    // D-65: empty search and no-results are different states. The invitation state is `search === null`;
    // a miss carries the term and the normalized key it folded to. The review-queue offer D-65 also names
    // is deliberately absent — skills bypass `match_candidates` by design, so that queue can never hold one
    // (G-SK-23 carries the amendment ask), and pointing a Trainer at it would be a control that cannot do
    // what it says.
    $this->get(route('skills.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('search', null)
            ->where('searchKey', null));

    $this->get(route('skills.index', ['search' => 'runing']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('search', 'runing')
            ->where('searchKey', 'runing')
            ->has('skills.data', 0)
            ->where('totalCount', 9));
});

it('names the fetch when the catalog holds nothing, rather than blaming the query', function (): void {
    // No import at all, which is the state `database/database.sqlite` is in until somebody migrates and
    // fetches. The empty-catalog state is `totalCount === 0`; the copy that names the command lives in the
    // page and is guarded by `EmptyStateCommandNamesTest`. (D-65: a missing skill is a data-fetch gap, not
    // a user error.)
    $this->get(route('skills.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Skills/Index')
            ->has('skills.data', 0)
            ->where('totalCount', 0)
            ->where('search', null));

    expect(Skill::count())->toBe(0);
});

it('states the cost where the source does and names the kind of absence where it does not', function (): void {
    skillScreenImport();

    // D-220 as the digest states it (`ARCHITECTURE-ESSENTIALS.md` §D-220): an absent value renders as `N/A`
    // with a `title` naming which kind of absence, never as a default and never as a bare zero. The em dash
    // is not the disclosure glyph — R-02/D-79 and KI-7 bar it in shipped copy.
    //
    // The kind matters here. A null `sp_cost` is not a fact nobody recorded: the source states a cost on
    // class codes 1 and 2 only, so a unique skill has no SP price to state. Wording it as "not yet recorded"
    // would claim a pending import that is not pending.
    $this->get(route('skills.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('skills.data', function (Collection $rows): bool {
                $costed = collect($rows)->firstWhere('name', 'Gourmand');

                return $costed !== null && $costed['sp_cost'] === 180;
            })
            ->where('skills.data', function (Collection $rows): bool {
                $unique = collect($rows)->firstWhere('is_unique', true);

                return $unique !== null && $unique['sp_cost'] === null;
            }));
});

it('badges both rows the client calls Indomitable and discloses what the badge is not', function (): void {
    skillScreenImport();

    // Two rows, one name, and the badge falls on the one no card names. This is G-SK-21, and the screen
    // renders it rather than hiding it: the learnable row states 170 SP and a Recovery effect, the class-code
    // row states no cost and a Speed effect, so the rows are distinguishable without a rarity word. The
    // badge's own `title` disclosure is asserted in `tests/browser/skills.spec.ts` (D-256: a derived value
    // prints its rule and says whose derivation it is).
    $this->get(route('skills.index', ['search' => 'indomitable']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('skills.data', 2)
            ->where('skills.data', fn (Collection $rows): bool => collect($rows)->where('is_unique', true)->count() === 1)
            ->where('skills.data', fn (Collection $rows): bool => collect($rows)->pluck('sp_cost')->contains(170)));
});

it('prints the Japanese name only when it says something the client name does not', function (): void {
    skillScreenImport();

    // Found by rendering the filled database rather than the fixture: 18 of the 623 `[Global]` rows store
    // the same string in both name columns, because the source's `jpname` for those skills is Latin script
    // (`#LookatCurren`, `U=ma2`, `∴win Q.E.D.`), and the screen's first row printed it twice. The row here
    // is created rather than imported because the rule is about two columns agreeing; the producer's own
    // writing of `jpname` into `name_ja` is pinned in `GametoraSkillsParserTest`.
    Skill::create([
        'name' => '#LookatCurren',
        'name_ja' => '#LookatCurren',
        'match_key' => '#lookatcurren',
        'release_status' => ReleaseStatus::GlobalReleased->value,
        'name_is_client' => true,
    ]);

    // The pair travels as two props, and the page prints the second only when it differs from the first
    // (the rendering rule is pinned in the browser spec).
    $this->get(route('skills.index', ['search' => 'lookatcurren']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('skills.data', 1)
            ->where('skills.data.0.name', '#LookatCurren')
            ->where('skills.data.0.name_ja', '#LookatCurren'));

    // And a row whose two columns differ still carries both, so this is a display rule, not a suppression.
    $this->get(route('skills.index', ['search' => 'averseness']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('skills.data', 1)
            ->where('skills.data.0.name', 'G1 Averseness'));
});

it('never prints a source translation, over the rendered payload', function (): void {
    skillScreenImport();

    // Screen D is where a client-string violation would actually reach a Trainer: it is the surface the 623
    // rows were imported for. After the port the page renders from the payload, so the payload is what must
    // carry no rendering — the props expose `name` (the client string) and never `enname`.
    $content = $this->get(route('skills.index'))->assertOk()->content();

    expect(skillScreenDivergentRenderings())->toHaveCount(8)
        // Positive control through the same read: without it the eight absences would pass on an
        // extraction that saw nothing.
        ->and($content)->toContain('Master of the Sands')
        ->and($content)->not->toContain(...skillScreenDivergentRenderings());
});

it('pages through the catalog with the token-overridden pagination chrome', function (): void {
    skillScreenImport();

    $first = $this->get(route('skills.index', ['pageSize' => '3', 'page' => '1']))->assertOk();
    $second = $this->get(route('skills.index', ['pageSize' => '3', 'page' => '2']))->assertOk();

    $first->assertInertia(fn (Assert $page) => $page
        ->has('skills.data', 3)
        ->where('skills.per_page', 3)
        // Rows come back in the same order the run screen's picker uses, so the two surfaces cannot offer
        // the same catalog in two different orders.
        ->where('skills.data.0.name', 'Certain Victory')
        // Nine rows at three per page: prev, three numbered pages, next. The page renders its own nav from
        // this list (the aria-label and the focus path are asserted in the browser spec).
        ->has('skills.links', 5));

    $second->assertInertia(fn (Assert $page) => $page->has('skills.data', 3));

    // A page past the end is an empty page, not an error and not the last page repeated.
    $this->get(route('skills.index', ['pageSize' => '3', 'page' => '9']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('skills.data', 0));
});

it('refuses a facet value the data has no such column for rather than ignoring it', function (): void {
    skillScreenImport();

    // Silently dropping an unknown `type` would answer a question nobody asked with the full catalog, which
    // is how a facet lies: the Trainer reads eight results as "these are the Speed skills". The form request
    // rejects it and the page comes back naming the field.
    //
    // Tightened to the canonical URL by ADR-0018: the bare `assertRedirect()` passed while the target was
    // `previous()`, which resolves to the referer in a browser and to `/` in a test, so the refusal could
    // land the Trainer on the run list with an error about a skill type.
    $this->get(route('skills.index', ['type' => 'Debuff']))
        ->assertRedirect(route('skills.index'))
        ->assertSessionHasErrors('type');

    // The whole derivable set, so an option added to the form without a value behind it is visible here.
    $derived = Skill::query()->pluck('type')->filter()->unique()->values()->sort()->values()->all();

    expect($derived)->toBe(['Recovery', 'Speed']);
});

it('is reachable from the shell nav rather than a run-screen door', function (): void {
    skillScreenImport();

    // G-SK-13: Screen D exists to relieve the catalogue the 0.1.0 run record listed in one select.
    // Owner ruling R-2 (`docs/proposals/frontend-development-plan.md` §9.6 close-out, 2026-10-09)
    // retired that page with `Runs/Show.vue`, and the Cockpit holds no `/skills` door of its own, so
    // the run-scoped half of this assertion is gone. What remains is the shell's own nav item - the one
    // door every screen carries - read from the layout that renders it (the page is client-rendered,
    // ADR-0020 §1) and resolved against the route table, so a renamed route fails here rather than
    // shipping a dead `/skills`. The rendered link is asserted in `tests/browser/skills.spec.ts`.
    $layout = (string) file_get_contents(base_path('resources/js/layouts/AppLayout.vue'));
    $skillsPath = parse_url(route('skills.index'), PHP_URL_PATH);

    expect($layout)->toContain("to: '".$skillsPath."'");
});
