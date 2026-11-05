<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Models\Skill;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The catalogue's scope filter (`R1`, `R2`; ADR-0011 §2; PRD FR-D-3).
 *
 * Three scopes, and the default is the narrow one. `global` keeps `availableOnGlobal()`, which is the
 * read-path commitment `ADR-0011` §2 states and the reason `SkillController`'s class docblock exists;
 * `all` and `japan_only` are reference overrides a caller asks for by name. A filter that changed the
 * default surface would reverse that decision without saying so, which is why the default is asserted
 * here rather than assumed.
 *
 * **Every seeded row sets both `release_status` and `name_is_client`.** `Skill::scopeAvailableOnGlobal()`
 * (`app/Models/Skill.php:108-112`) filters on both, and `SkillFactory` sets neither, so a row missing
 * either column is invisible to the default scope and every assertion below would read green against an
 * empty catalogue. A JapanOnly row sets `name_is_client` false because that is what the column means by
 * construction (`GametoraSkillsParser:194`: `$clientName !== null && $onGlobal`), not because this file
 * wants it false.
 *
 * `skillScreenImport()` is not used: its fixture carries no `distance_type` (asserted in
 * `SkillSearchScreenTest`), so it cannot reach the composition cases.
 */

/**
 * A Global row: released, and named by the client. Both columns, or it is invisible.
 *
 * Named `globalSkillRow`, not `globalSkill`: `tests/Feature/SupportCardPageTest.php:46` already
 * declares a `globalSkillRow()` and Pest loads every test file into one process, so a second function
 * of that name is a fatal redeclaration that stops the whole suite before the first assertion.
 * The suffix also states what this one returns (a factory row) rather than how it is scoped.
 */
function globalSkillRow(string $name, array $attributes = []): Skill
{
    return Skill::factory()->create([
        'name' => $name,
        'release_status' => ReleaseStatus::GlobalReleased->value,
        'name_is_client' => true,
        ...$attributes,
    ]);
}

/** A JapanOnly row: the client has not shipped it, so no client string exists for it. */
function japanOnlySkill(string $name, array $attributes = []): Skill
{
    return Skill::factory()->create([
        'name' => $name,
        'name_ja' => '掲げよ、己が魂の剣譜！',
        'release_status' => ReleaseStatus::JapanOnly->value,
        'name_is_client' => false,
        ...$attributes,
    ]);
}

it('answers with the Global subset when no scope is asked for', function (): void {
    globalSkillRow('Global Row');
    japanOnlySkill('Japan Row');

    $this->get(route('skills.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Skills/Index')
            // The prop states the default rather than leaving it implied, so a URL can be read back.
            ->where('scope', 'global')
            ->has('skills.data', 1)
            ->where('skills.data.0.name', 'Global Row')
            // The select is built from this map, so an option the controller cannot answer would show up
            // as a choice that returns nothing.
            ->where('scopes', [
                'global' => 'Global only',
                'all' => 'Every skill',
                'japan_only' => 'Japan only',
            ])
        );
});

it('includes both release statuses at scope=all', function (): void {
    globalSkillRow('Global Row');
    japanOnlySkill('Japan Row');

    $this->get(route('skills.index', ['scope' => 'all']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scope', 'all')
            ->has('skills.data', 2)
        );
});

it('answers only JapanOnly rows at scope=japan_only, named by the source string', function (): void {
    globalSkillRow('Global Row');
    japanOnlySkill('Japan Row');

    $this->get(route('skills.index', ['scope' => 'japan_only']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scope', 'japan_only')
            ->has('skills.data', 1)
            // `R1`/ruling A: the row's `name` column holds the export's `enname`, a rendering of the
            // Japanese, which the Air Messiah ruling bars from UI copy. It is nulled at the payload edge
            // so no surface can print it, and the source's own string is what the row shows.
            ->where('skills.data.0.name', null)
            ->where('skills.data.0.name_ja', '掲げよ、己が魂の剣譜！')
            // Ruling 1 (symmetric): `desc_en` on a JapanOnly row is third-party on the same test.
            ->where('skills.data.0.description', null)
        );
});

it('refuses an unknown scope rather than answering with a different one', function (): void {
    globalSkillRow('Global Row');

    // `R2`: an unknown facet value is refused, per this request class's own docblock. A typo dropped
    // silently answers a question nobody asked, and at `all` that is 3x the rows.
    //
    // The refusal is a redirect carrying a field error, not a 422: `SkillSearchRequest` is a FormRequest
    // on a browser GET, and Laravel answers a failed FormRequest with `redirect()->back()` plus the
    // error bag. The existing precedent (`SkillSearchScreenTest`, "refuses a facet value the data has no
    // such column for") asserts exactly this shape, and its target was pinned to the canonical route by
    // ADR-0018 so the refusal cannot land on the referer.
    $this->get(route('skills.index', ['scope' => 'glboal']))
        ->assertRedirect(route('skills.index'))
        ->assertSessionHasErrors('scope');
});

it('composes the scope with the distance facet, sentinel intact', function (): void {
    globalSkillRow('Global Mile', ['distance_type' => 2]);
    globalSkillRow('Global Sprint', ['distance_type' => 1]);
    japanOnlySkill('Japan Mile', ['distance_type' => 2]);
    japanOnlySkill('Japan Unrecorded', ['distance_type' => null]);

    // Both facets narrow. A scope that widened the set, or a distance that was dropped once a scope was
    // present, would show more than one row here.
    $this->get(route('skills.index', ['scope' => 'all', 'distance' => '2']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scope', 'all')
            ->where('distance', 2)
            ->has('skills.data', 2)
        );

    // `0` is the Unspecified sentinel, not "no filter" (D6). Composed with a scope it must still select
    // the rows that store no distance, which is one Global row and one JapanOnly row here.
    $this->get(route('skills.index', ['scope' => 'all', 'distance' => '0']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('distance', 0)
            ->has('skills.data', 1)
            ->where('skills.data.0.name', null)
        );
});

it('composes the scope with the search query', function (): void {
    globalSkillRow('Corner Adept');
    globalSkillRow('Straightaway');
    japanOnlySkill('Corner Something');

    // Phase 7 changed this number from 2 to 1, and the change is the rule rather than a repair. The JP
    // row's `match_key` is built from `name`, which on a JapanOnly row is the export's `enname` — a
    // string no cell in the catalogue prints. At `all` the row IS visible, but under its `name_ja`, so
    // matching it on an invisible English string is the "answered a question nobody asked" shape the
    // scope rule refuses. `match_key` is now gated to GlobalReleased rows, so `corner` reaches the one
    // Global row whose printed name contains it.
    $this->get(route('skills.index', ['scope' => 'all', 'search' => 'corner']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scope', 'all')
            ->has('skills.data', 1)
            ->where('skills.data.0.name', 'Corner Adept')
        );

    // The JP row is still reachable at `all` — through the string it prints. That is the gap Phase 7
    // closed, and it is asserted here so the number above cannot be mistaken for the row being lost.
    $this->get(route('skills.index', ['scope' => 'all', 'search' => '掲げよ']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scope', 'all')
            ->has('skills.data', 1)
            ->where('skills.data.0.name_ja', '掲げよ、己が魂の剣譜！')
        );

    // The same query at the default scope answers one row, so the scope is doing work rather than
    // riding along.
    $this->get(route('skills.index', ['search' => 'corner']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('skills.data', 1));
});

it('keeps the scope across pages', function (): void {
    // Two pages at ten per page, so the pager has somewhere to go.
    foreach (range(1, 12) as $i) {
        globalSkillRow("Global Row {$i}");
    }
    japanOnlySkill('Japan Row');

    $pages = $this->get(route('skills.index', ['scope' => 'all', 'pageSize' => '10', 'page' => '2']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scope', 'all')
            ->has('skills.data', 3)
        );

    // The pager's own links carry the query, so page 2 is not page 2 of a different set.
    expect($pages->inertiaProps('skills')['last_page'])->toBe(2);
});

/*
 * The header's provenance counts (G2, B1/B4).
 *
 * The rule these four cases pin is that the figures describe the WHOLE table at every scope. The
 * rejected alternative was a scoped count, which reads `{629, 0, 0}` at the default scope where a
 * Trainer cannot tell whether Japan-only is empty or merely filtered out. The current scope's own row
 * count is the paginator's "N of M" line, so nothing is lost by counting the table here.
 *
 * Three Global and two JapanOnly, so the expected triple differs from the row counts at every scope
 * below: at `scope=global` the page shows three rows against a header saying three and two, and at
 * `scope=japan_only` it shows two against the same header. A scoped implementation would answer
 * `{3, 0, 0}` and `{0, 2, 0}` and fail all four.
 */
it('counts the full table by release status at the default scope', function (): void {
    foreach (range(1, 3) as $i) {
        globalSkillRow("Global Row {$i}");
    }
    foreach (range(1, 2) as $i) {
        japanOnlySkill("Japan Row {$i}");
    }

    $this->get(route('skills.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Skills/Index')
            ->where('release_status_counts', [
                'global' => 3,
                'japan_only' => 2,
                'other' => 0,
            ])
            // The page itself is still narrowed, so the two figures are answering different questions.
            ->has('skills.data', 3)
        );
});

it('counts the full table unchanged by the scope filter', function (): void {
    foreach (range(1, 3) as $i) {
        globalSkillRow("Global Row {$i}");
    }
    foreach (range(1, 2) as $i) {
        japanOnlySkill("Japan Row {$i}");
    }

    // The narrow scope shows only the two JapanOnly rows; the header still names all five.
    $this->get(route('skills.index', ['scope' => 'japan_only']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scope', 'japan_only')
            ->has('skills.data', 2)
            ->where('release_status_counts', [
                'global' => 3,
                'japan_only' => 2,
                'other' => 0,
            ])
        );
});

it('counts the full table unchanged by the search query', function (): void {
    foreach (range(1, 3) as $i) {
        globalSkillRow("Global Row {$i}");
    }
    foreach (range(1, 2) as $i) {
        japanOnlySkill("Japan Row {$i}");
    }

    // A query that matches one row must not narrow the header to one row.
    $this->get(route('skills.index', ['scope' => 'all', 'search' => 'Global Row 1']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('skills.data', 1)
            ->where('release_status_counts', [
                'global' => 3,
                'japan_only' => 2,
                'other' => 0,
            ])
        );
});

it('counts the full table unchanged by the distance filter', function (): void {
    globalSkillRow('Global Mile', ['distance_type' => 2]);
    globalSkillRow('Global Sprint', ['distance_type' => 1]);
    globalSkillRow('Global Unrecorded', ['distance_type' => null]);
    japanOnlySkill('Japan Mile', ['distance_type' => 2]);
    japanOnlySkill('Japan Long', ['distance_type' => 4]);

    // Two rows reach `distance=2`; the header still names five across the table.
    $this->get(route('skills.index', ['scope' => 'all', 'distance' => '2']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('distance', 2)
            ->has('skills.data', 2)
            ->where('release_status_counts', [
                'global' => 3,
                'japan_only' => 2,
                'other' => 0,
            ])
        );
});

/*
 * The `other` bucket is a remainder, not a placeholder (B1). `GlobalAnnounced` is the third enum case
 * and the only one that lands there today, so a status this app grows later is counted rather than
 * silently dropped out of the total. A row with a null `release_status` counts here too, which is the
 * honest answer: the column says nothing, so it is not a Global row and not a JapanOnly one.
 */
it('counts every status outside the two named buckets as other', function (): void {
    globalSkillRow('Global Row');
    japanOnlySkill('Japan Row');
    Skill::factory()->create([
        'name' => 'Announced Row',
        'release_status' => ReleaseStatus::GlobalAnnounced->value,
        'name_is_client' => false,
    ]);

    $this->get(route('skills.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('release_status_counts', [
                'global' => 1,
                'japan_only' => 1,
                'other' => 1,
            ])
        );
});
