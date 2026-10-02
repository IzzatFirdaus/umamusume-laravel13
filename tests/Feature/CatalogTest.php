<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Models\DataSource;
use App\Models\Umamusume;

/*
 * Catalog list, filter, search, detail and empty state.
 *
 * The Phase 3A mapping named this file's concern `CatalogFilterEdgeTest` - a file that
 * does not exist, and creating it would have split one surface's coverage across two
 * files that could then drift. It resolves here: the release-status filter, the search
 * normalisation, and the empty state are the three cases below, and they are already the
 * edges - a filter that drops the japan-only rows, a search that has to normalise before
 * it can match, and a search that matches nothing at all.
 */

it('lists only global released umamusume when filtered by release status', function (): void {
    $released = Umamusume::factory()->create(['name' => 'Released One', 'slug' => 'released-one']);
    Umamusume::factory()->japanOnly()->create(['name' => 'Japan Only One', 'slug' => 'japan-only-one']);

    $response = test()->get('/umamusume?status=GlobalReleased');

    $response->assertOk()
        ->assertSee('Released One')
        ->assertDontSee('Japan Only One');

    expect($released->release_status)->toBe(ReleaseStatus::GlobalReleased);
});

it('shows a detail page with Japanese name and provenance', function (): void {
    $umamusume = Umamusume::factory()->create([
        'name' => 'Special Week',
        'slug' => 'special-week',
        'name_ja' => 'スペシャルウィーク',
    ]);

    DataSource::factory()->create([
        'umamusume_id' => $umamusume->id,
        'url' => 'https://example.test/special-week',
        'fetched_at' => now(),
    ]);

    test()->get('/umamusume/special-week')
        ->assertOk()
        ->assertSee('スペシャルウィーク')
        ->assertSee('https://example.test/special-week');
});

it('labels japan-only entries as not yet released on global', function (): void {
    Umamusume::factory()->japanOnly()->create([
        'name' => 'Unseen One',
        'slug' => 'unseen-one',
        'release_status' => ReleaseStatus::JapanOnly,
    ]);

    test()->get('/umamusume/unseen-one')
        ->assertOk()
        ->assertSee('Not yet released on Global');
});

it('renders the empty state when nothing matches the search', function (): void {
    test()->get('/umamusume?search=nothinghere')
        ->assertOk()
        ->assertSee('No Umamusume match');
});

it('finds an umamusume by normalized search text', function (): void {
    Umamusume::factory()->create([
        'name' => 'Special Week',
        'slug' => 'special-week',
        'match_key' => 'specialweek',
    ]);

    test()->get('/umamusume?search=SPECIAL WEEK')
        ->assertOk()
        ->assertSee('Special Week');
});

it('sizes every catalog control to the design contract\'s 44px', function (): void {
    // KI-29. The index shipped `px-2 py-1` with no height, so a browser measured the search
    // field, the status filter and the submit at 30/31/32 against the 44 the design contract
    // fixes (`docs/research-scratch/DESIGN-CORPUS.md` "DESIGN.md" section 6.14). The run screen
    // took the same fix at c17e63b; this is the second surface. The class assertion proves the
    // token was applied, not the rendered height: h-11 is 2.75rem, which is 44px only while the
    // root font size is 16px, so the browser read in the task record is what proves the contract.
    // The selector below covers untyped, text and search inputs, every select, and submit
    // buttons, so a control with no type attribute or a type="search" cannot slip through; the
    // checkbox is deliberately outside it, because a checkbox is a 24px AA-floor control, not a
    // form field.
    $html = $this->get('/umamusume')->assertOk()->getContent();

    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $controls = $xpath->query(
        '//input[not(@type) or @type="text" or @type="search"]'
        .' | //select | //button[not(@type) or @type="submit"]'
    );

    expect($controls->length)->toBeGreaterThan(0);

    foreach ($controls as $node) {
        /** @var DOMElement $node */
        expect(str_contains($node->getAttribute('class'), 'h-11'))
            ->toBeTrue("{$node->nodeName} [{$node->getAttribute('name')}] is not sized to h-11");
    }
});
