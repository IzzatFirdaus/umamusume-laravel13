<?php

declare(strict_types=1);

use App\Enums\AliasLanguage;
use App\Enums\CardRarity;
use App\Models\CharacterCard;
use App\Models\Umamusume;
use App\Models\UmamusumeAlias;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/*
 * The catalog as a trainee and card tree (PRD FR-A-6, US-1).
 *
 * One heading per trainee, one per card beneath her, ordered by the rule the controller
 * declares rather than the order rows were inserted in. Four things are pinned here that
 * the shape of the page depends on and nothing else checks: the nesting, the card-title
 * search, the two opt-out filters (`status=all`, `show_unconfirmed=1`), and the default
 * release status.
 *
 * A search fixture states its own `match_key` (see `CatalogTest.php:74`): the factory
 * derives one from a faker name inside `definition()`, so `create(['name' => ...])`
 * stores a key unrelated to the override, and a test that searches by name would be
 * searching nothing. Only `match_key` is a stored normalized column; the card title and
 * the alias are verbatim display strings that the query folds to the same shape at
 * comparison time, which is what the multi-word cases at the end of this file pin.
 *
 * Recorded deviation: the catalog row stopped displaying its alias count in this task.
 * The briefed row shape is name, Japanese name, max rarity and form count, so no row
 * asks for `aliases_count` and the index queries dropped the subselect with it.
 */

it('nests each card under its trainee', function (): void {
    $goldShip = Umamusume::factory()->create([
        'name' => 'Gold Ship', 'slug' => 'gold-ship', 'name_ja' => 'ゴールドシップ',
    ]);
    CharacterCard::factory()->create([
        'umamusume_id' => $goldShip->id, 'card_id' => 100701, 'title' => '[Red Strife]',
        'rarity' => CardRarity::TwoStar, 'global_release_date' => '2025-06-26', 'is_debut_form' => true,
    ]);
    CharacterCard::factory()->create([
        'umamusume_id' => $goldShip->id, 'card_id' => 100702, 'title' => '[RUN! RUIN! LAUNCHER!]',
        'rarity' => CardRarity::ThreeStar, 'global_release_date' => '2026-07-02',
    ]);

    $html = test()->get('/umamusume')->assertOk()->getContent();

    expect($html)->toContain('Gold Ship')
        ->and($html)->toContain('ゴールドシップ')
        ->and($html)->toContain('[Red Strife]')
        ->and($html)->toContain('[RUN! RUIN! LAUNCHER!]')
        // The trainee row carries her form count and the max rarity across her cards.
        ->and($html)->toContain('2 forms')
        ->and($html)->toContain('Three stars');
});

it('orders the debut first, then by Global release date ascending', function (): void {
    $teio = Umamusume::factory()->create([
        'name' => 'Tokai Teio', 'slug' => 'tokai-teio', 'match_key' => 'tokaiteio',
    ]);
    foreach ([
        ['[Beyond the Horizon]', '2025-07-16', false, 100302],
        ['[Peak Joy]', '2025-06-26', true, 100301],
        ['[A Later Form]', '2026-01-01', false, 100303],
    ] as [$title, $date, $debut, $cardId]) {
        CharacterCard::factory()->create([
            'umamusume_id' => $teio->id, 'card_id' => $cardId, 'title' => $title,
            'global_release_date' => $date, 'is_debut_form' => $debut,
        ]);
    }

    $html = test()->get('/umamusume?search=tokai teio')->assertOk()->getContent();

    // Asserted before the positions are read: `strpos` answers `false` for a title that
    // never rendered, and a `: int` closure turns that into a TypeError that hides the
    // real cause, which is a missing row.
    expect($html)->toContain('[Peak Joy]')
        ->and($html)->toContain('[Beyond the Horizon]')
        ->and($html)->toContain('[A Later Form]');

    $positions = array_map(
        static fn (string $title): int => strpos($html, $title),
        ['[Peak Joy]', '[Beyond the Horizon]', '[A Later Form]'],
    );

    // The debut leads even though [A Later Form] is last by date, and the two
    // non-debut forms follow their own date, not the order they were inserted in.
    expect($positions[0])->toBeGreaterThan(0)
        ->and($positions[0])->toBeLessThan($positions[1])
        ->and($positions[1])->toBeLessThan($positions[2]);
});

it('never prints a bare zero when a trainee has no forms', function (): void {
    Umamusume::factory()->create([
        'name' => 'Cardless One', 'slug' => 'cardless-one', 'match_key' => 'cardlessone',
    ]);

    $html = test()->get('/umamusume?search=cardless')->assertOk()->getContent();

    // G-13 and the disclosure pattern: an absent count prints words, never 0. This is
    // also the shape DesignTokensTest creates 30 of, so it has to render clean.
    expect($html)->toContain('Cardless One')
        ->and($html)->not->toMatch('/0 forms/')
        ->and($html)->toContain('no forms recorded');
});

it('hides a card only the Tier B source attests, unless asked', function (): void {
    $u = Umamusume::factory()->create(['name' => 'Fine Motion', 'slug' => 'fine-motion']);
    CharacterCard::factory()->confirmed()->create([
        'umamusume_id' => $u->id, 'card_id' => 112001, 'title' => '[Seen Twice]', 'is_debut_form' => true,
    ]);
    CharacterCard::factory()->unconfirmed()->create([
        'umamusume_id' => $u->id, 'card_id' => 112002, 'title' => '[GameTora Only]',
    ]);

    $default = test()->get('/umamusume')->assertOk()->getContent();
    expect($default)->toContain('[Seen Twice]')
        ->and($default)->not->toContain('[GameTora Only]');

    test()->get('/umamusume?show_unconfirmed=1')
        ->assertOk()
        ->assertSee('[GameTora Only]')
        ->assertSee('Not confirmed by two sources');
});

it('defaults the release status filter to released on Global', function (): void {
    Umamusume::factory()->create(['name' => 'Global One', 'slug' => 'global-one']);
    Umamusume::factory()->japanOnly()->create(['name' => 'Japan One', 'slug' => 'japan-one']);

    $html = test()->get('/umamusume')->assertOk()->getContent();
    expect($html)->toContain('Global One')->and($html)->not->toContain('Japan One');

    // And the explicit way back to everything, which is what keeps CatalogTest honest.
    $all = test()->get('/umamusume?status=all')->assertOk()->getContent();
    expect($all)->toContain('Global One')->and($all)->toContain('Japan One');
});

it('filters the tree by a card title as well as a trainee name', function (): void {
    $goldShip = Umamusume::factory()->create(['name' => 'Gold Ship', 'slug' => 'gold-ship']);
    CharacterCard::factory()->create([
        'umamusume_id' => $goldShip->id, 'card_id' => 100702, 'title' => '[RUN! RUIN! LAUNCHER!]',
    ]);
    Umamusume::factory()->create(['name' => 'Unrelated One', 'slug' => 'unrelated-one']);

    // The card matches, so its trainee is the row that appears. The empty state still
    // says "No Umamusume match" when nothing at all matches, so that stays true.
    test()->get('/umamusume?search=ruin')
        ->assertOk()
        ->assertSee('Gold Ship')
        ->assertDontSee('Unrelated One');
});

it('keeps the card tree intact through the database cache store', function (): void {
    config(['cache.default' => 'database']);
    Cache::flush();

    $u = Umamusume::factory()->create(['name' => 'Agnes Digital', 'slug' => 'agnes-digital']);
    CharacterCard::factory()->create([
        'umamusume_id' => $u->id, 'card_id' => 110102, 'title' => '[Full-Color Fangirling]', 'is_debut_form' => true,
    ]);

    // KI-2's failure mode was the cached id list handing back rows whose relations had
    // gone missing. The tree is exactly that shape, loaded twice so the cache is warm.
    test()->get('/umamusume')->assertOk()->assertSee('[Full-Color Fangirling]');
    test()->get('/umamusume')->assertOk()->assertSee('[Full-Color Fangirling]');
});

it('loads the tree in a fixed number of queries, not one per trainee', function (): void {
    // C-6's 200 ms budget is a manual benchmark (CONSTRAINTS.md says so, and no test
    // measures a duration here), so this pins the *shape* it depends on instead of
    // inventing a millisecond figure: forms arrive eager-loaded with the page, so the
    // query count cannot grow with the number of trainees or forms on screen.
    foreach (range(1, 4) as $index) {
        $trainee = Umamusume::factory()->create([
            'name' => "Form Counted {$index}", 'slug' => "form-counted-{$index}",
        ]);
        CharacterCard::factory()->count(3)->create(['umamusume_id' => $trainee->id]);
    }

    DB::flushQueryLog();
    DB::enableQueryLog();
    test()->get('/umamusume')->assertOk()->assertSee('Form Counted 4');
    $log = DB::getQueryLog();

    // Counted separately because the shell owns it: the layout reads the stored theme
    // once per render, and a page that grew a trainee must not grow a query for it.
    $tree = array_values(array_filter(
        $log,
        static fn (array $q): bool => ! str_contains($q['query'], 'from "preferences"'),
    ));

    // Four, whatever the page size: the ids and the total, the page re-read, and one
    // eager load carrying every form on screen. The fixture is 4 trainees with 3 forms
    // each; loading a trainee's forms per row would be 4 more, and 68 trainees on a page
    // would be 68.
    expect($tree)->toHaveCount(4)
        ->and(count($log))->toBe(5, 'the shell added a query the tree does not own');
})->group('perf');

it('reads a trainee badge off the cards the filter let through', function (): void {
    $nature = Umamusume::factory()->create([
        'name' => 'Nice Nature', 'slug' => 'nice-nature', 'match_key' => 'nicenature',
    ]);
    CharacterCard::factory()->confirmed()->create([
        'umamusume_id' => $nature->id, 'card_id' => 118001, 'title' => '[Two Star, Attested]',
        'rarity' => CardRarity::TwoStar, 'is_debut_form' => true,
    ]);
    CharacterCard::factory()->unconfirmed()->create([
        'umamusume_id' => $nature->id, 'card_id' => 118002, 'title' => '[Three Star, Alone]',
        'rarity' => CardRarity::ThreeStar,
    ]);

    // The badge and the form count beside it answer the same question, "what is on this
    // screen", so both read the visible set: hiding the 3 star hides the badge with it,
    // and `show_unconfirmed=1` moves both together. The glyph run is asserted alongside
    // the words because a rarity `<select>` or a card's `aria-label` could put the word
    // "Three stars" on the page for the wrong reason; `★★★` can only be the badge.
    $default = test()->get('/umamusume?search=nice nature')->assertOk()->getContent();
    expect($default)->toContain('[Two Star, Attested]')
        ->and($default)->not->toContain('[Three Star, Alone]')
        ->and($default)->toContain('Two stars')
        ->and($default)->not->toContain('Three stars')
        ->and($default)->not->toContain('★★★');

    $shown = test()->get('/umamusume?search=nice nature&show_unconfirmed=1')->assertOk()->getContent();
    expect($shown)->toContain('Three stars')
        ->and($shown)->toContain('★★★')
        ->and($shown)->toContain('2 forms');
});

it('never serves a page cached for one term to another term sharing its key', function (): void {
    Umamusume::factory()->create([
        'name' => 'Tokai Teio', 'slug' => 'tokai-teio', 'match_key' => 'tokaiteio',
    ]);
    // A display name the one-word term reads verbatim and the double-spaced one does
    // not, with a match key that says it is somebody else. Search reads the normalized
    // match key, never the raw name, so it is out of both pages; the moment the query
    // also matched on the name string, this row's presence would depend on which of the
    // two warmed the shared key. (Titles and aliases do read display strings, folded -
    // this row has neither, so the cache-key argument is untouched.)
    Umamusume::factory()->create([
        'name' => 'Tokaiteio Lookalike', 'slug' => 'tokaiteio-lookalike', 'match_key' => 'lookalike',
    ]);

    test()->get('/umamusume?search=tokaiteio')->assertOk();
    $html = test()->get('/umamusume?search=TOKAI%20%20TEIO')->assertOk()->getContent();

    expect($html)->toContain('Tokai Teio')
        ->and($html)->not->toContain('Tokaiteio Lookalike');
});

it('keeps the active filters on every pagination link', function (): void {
    Umamusume::factory()->create([
        'name' => 'Tokai Teio', 'slug' => 'tokai-teio', 'match_key' => 'tokaiteio',
    ]);
    Umamusume::factory()->create([
        'name' => 'Teio Alternate', 'slug' => 'teio-alternate', 'match_key' => 'teioalternate',
    ]);

    // Two rows at one per page, so page 2 renders and its pager links with it.
    $html = test()->get('/umamusume?status=all&search=teio&show_unconfirmed=1&pageSize=1&page=2')
        ->assertOk()
        ->getContent();

    preg_match_all('/href="([^"]*page=\d+)"/', $html, $matches);

    // A bare `/umamusume?page=1` silently reverts status, search and the unconfirmed
    // opt-in the moment a Trainer pages, so no pager link may drop one of the three.
    $dropped = array_values(array_filter(
        $matches[1],
        static fn (string $href): bool => ! str_contains($href, 'status=all')
            || ! str_contains($href, 'search=teio')
            || ! str_contains($href, 'show_unconfirmed=1'),
    ));

    expect($matches[1])->not->toBeEmpty()
        ->and($dropped)->toBe([], 'a pagination link dropped an active filter');
});

it('finds a trainee through a two-word card epithet', function (): void {
    $goldShip = Umamusume::factory()->create([
        'name' => 'Gold Ship', 'slug' => 'gold-ship', 'match_key' => 'goldship',
    ]);
    CharacterCard::factory()->create([
        'umamusume_id' => $goldShip->id, 'card_id' => 100701, 'title' => '[Red Strife]',
    ]);

    // The one-word card search above survives because a single word has no space in it.
    // This one does not: the term folds to `redstrife` and the title is verbatim source
    // data with its space intact, so only a clause that folds the column too can reach
    // it. `match_key` is `goldship`, so the name path cannot be what passes here, and
    // the title itself is asserted because a row could otherwise render any other form.
    test()->get('/umamusume?search=red%20strife')
        ->assertOk()
        ->assertSee('Gold Ship')
        ->assertSee('[Red Strife]');
});

it('finds a trainee through a hyphenated card epithet', function (): void {
    $digital = Umamusume::factory()->create([
        'name' => 'Agnes Digital', 'slug' => 'agnes-digital', 'match_key' => 'agnesdigital',
    ]);
    CharacterCard::factory()->create([
        'umamusume_id' => $digital->id, 'card_id' => 110102, 'title' => '[Full-Color Fangirling]',
    ]);

    // The hyphen is folded on both sides, so the space the Trainer typed and the hyphen
    // the source stored reach the same string. Same shape as the epithet case, and the
    // title is a verbatim one rather than a fixture invented to have punctuation in it.
    test()->get('/umamusume?search=full%20color')
        ->assertOk()
        ->assertSee('Agnes Digital')
        ->assertSee('[Full-Color Fangirling]');
});

it('finds a trainee through a two-word alias', function (): void {
    $nature = Umamusume::factory()->create([
        'name' => 'Nice Nature', 'slug' => 'nice-nature', 'match_key' => 'nicenature',
    ]);
    // A fixture surface form, not attested data: aliases in this table are the alternate
    // names the matcher reads (PRD FR-A-2), and two words is the case being pinned.
    UmamusumeAlias::factory()->create([
        'umamusume_id' => $nature->id,
        'alias' => 'Nature Boy',
        'language' => AliasLanguage::English,
    ]);

    // `natureboy` is nowhere in `nicenature`, so the alias clause is the only path that
    // can return this row.
    test()->get('/umamusume?search=nature%20boy')
        ->assertOk()
        ->assertSee('Nice Nature');
});
