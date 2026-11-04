<?php

declare(strict_types=1);

use App\Enums\CardRarity;
use App\Enums\ReleaseStatus;
use App\Enums\RunStatus;
use App\Models\DeckSlot;
use App\Models\Skill;
use App\Models\SupportCard;
use App\Models\SupportEffect;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The two support-card read surfaces, `/support-cards` and `/support-cards/{id}`.
 *
 * These assert the props the Inertia page receives, not the markup it renders: the page is a Vue
 * component now (ADR-0020 §1), so the server contract is the payload. The copy a Trainer reads, the
 * selected state a picker paints and the labels the form carries are asserted against the rendered
 * DOM in `tests/browser/support-cards.spec.ts`.
 *
 * The rows come from factories, never from the seeder, so a test names the card it asserts on.
 */

function cardWithEffects(array $attributes = []): SupportCard
{
    return SupportCard::factory()->create($attributes);
}

// Named for the row it creates, not the dictionary it feeds: `tests/Unit/SupportCardEffectsTest.php`
// already declares a global `effectDictionary()`, and Pest loads every test file into one process, so a
// second function of that name is a fatal redeclaration rather than a shadowed helper.
function effectRow(int $effectId, string $name, string $symbol = 'percent'): SupportEffect
{
    return SupportEffect::factory()->create([
        'effect_id' => $effectId,
        'name_en' => $name,
        'symbol' => $symbol,
    ]);
}

function globalSkill(int $exportId, string $name): Skill
{
    return Skill::factory()->create([
        'export_id' => $exportId,
        'name' => $name,
        'release_status' => ReleaseStatus::GlobalReleased,
        'name_is_client' => true,
    ]);
}

it('lists every card with the label a Trainer reads and a link to its own page', function (): void {
    $special = cardWithEffects(['char_name' => 'Special Week', 'title_en' => '[Tracen Academy]']);
    $tazuna = SupportCard::factory()->friend()->create(['title_en' => '[Pal]']);

    test()->get('/support-cards')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('SupportCards/Index')
            ->has('cards.data', 2)
            ->where('cards.data.0.name', 'Special Week [Tracen Academy]')
            ->where('cards.data.0.url', route('support-cards.show', $special))
            ->where('cards.data.1.name', 'Tazuna Hayakawa [Pal]')
            ->where('cards.data.1.url', route('support-cards.show', $tazuna))
            ->where('cards.total', 2)
            ->where('totalCount', 2));
});

it('counts the Global half of the catalogue apart from the whole table', function (): void {
    // The side tasks delta the parity pass filed as `SupportCardController.global_count`; the prop
    // ships as `globalCount`. It exists because the default view is one server's (the wireframe's
    // Global facet), so the row count on the table is not the size of the catalogue, and a Trainer
    // comparing the two needs both stated. Query source: `release_global is not null`, which is what
    // the generated `release_status` column calls 'Global'.
    SupportCard::factory()->create(['char_name' => 'Global One']);
    SupportCard::factory()->jpOnly()->create(['char_name' => 'Japan Only Two']);

    test()->get('/support-cards')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('totalCount', 2)
            ->where('globalCount', 1));
});

it('scopes the library to one server, defaulting to Global, and names the scope line', function (): void {
    // The wireframe's Server facet is two servers with Global selected and no all-servers option
    // (`support-cards.html:76`), and the table does not repeat availability on every row because the
    // list is already one server's. The per-row `release_status` stays in the payload for the Database
    // area and for these assertions.
    SupportCard::factory()->ssr()->wit()->create(['char_name' => 'Silence Suzuka']);
    SupportCard::factory()->jpOnly()->create(['char_name' => 'Gold Ship']);

    test()->get('/support-cards')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('server', 'Global')
            ->where('serverCount', 1)
            ->has('cards.data', 1)
            ->where('cards.data.0.name', 'Silence Suzuka [Tracen Academy]')
            ->where('cards.data.0.rarity_word', 'SSR')
            ->where('cards.data.0.type_label', 'Wit')
            ->where('cards.data.0.release_status', 'Global'));

    test()->get('/support-cards?server=JP')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('server', 'JP')
            ->where('serverCount', 1)
            ->has('cards.data', 1)
            ->where('cards.data.0.name', 'Gold Ship [Tracen Academy]')
            ->where('cards.data.0.rarity_word', 'R')
            ->where('cards.data.0.release_status', 'JP-only'));
});

it('summarizes each card\'s effects at cap on the row', function (): void {
    effectRow(1, 'Friendship Bonus');
    cardWithEffects(['char_name' => 'Special Week']);

    // The factory vector's last stated anchor is 15 at card level 35, and the dictionary row's `percent`
    // symbol is what makes it a percentage rather than a bare number.
    test()->get('/support-cards')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cards.data.0.effects.0.name', 'Friendship Bonus')
            ->where('cards.data.0.effects.0.display', '15%'));
});

it('names an effect the dictionary has no row for instead of inventing a label', function (): void {
    cardWithEffects(['char_name' => 'Special Week', 'effects' => [[77, 5, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1]]]);

    // `name` is null so the page can mark the gap rather than print a word the source does not state.
    test()->get('/support-cards')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cards.data.0.effects.0.effect_id', 77)
            ->where('cards.data.0.effects.0.name', null)
            ->where('cards.data.0.effects.0.display', '5'));
});

it('paginates at twenty-five cards by default, supports pageSize selector, and carries the filter through the link', function (): void {
    foreach (range(1, 26) as $index) {
        SupportCard::factory()->create(['char_name' => sprintf('Trainee %02d', $index)]);
    }

    // Twenty-five rows by default (D1/C21).
    test()->get('/support-cards')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 25)
            ->where('cards.data.0.name', 'Trainee 01 [Tracen Academy]')
            ->where('cards.data.24.name', 'Trainee 25 [Tracen Academy]')
            ->where('cards.total', 26)
            ->where('totalCount', 26));

    // The clamp still honours an explicit size, so the old 5-row view survives as a URL.
    test()->get('/support-cards?pageSize=5')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 5)
            ->where('cards.data.0.name', 'Trainee 01 [Tracen Academy]'));

    // Selector values pass through/clamp as before.
    test()->get('/support-cards?pageSize=50')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 26));

    test()->get('/support-cards?pageSize=1000')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 26));

    test()->get('/support-cards?pageSize=abc')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 25));

    // Option-set narrowing (C22, v9): in-range non-options resolve to nearest option,
    // tie-break toward smaller option. Garbage/parsed-to-≤0 → 25.
    // With 26 fixtures: effective 25 → 25 on page 1; effective 5 → 5 on page 1; effective 50/100 → 26 (all).
    test()->get('/support-cards?pageSize=0')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 25));
    test()->get('/support-cards?pageSize=-5')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 25));
    test()->get('/support-cards?pageSize=1')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 5));
    test()->get('/support-cards?pageSize=4')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 5));
    test()->get('/support-cards?pageSize=5')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 5));
    test()->get('/support-cards?pageSize=15')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 25));
    test()->get('/support-cards?pageSize=25')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 25));
    test()->get('/support-cards?pageSize=30')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 25));
    test()->get('/support-cards?pageSize=50')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 26));
    test()->get('/support-cards?pageSize=99')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 26));
    test()->get('/support-cards?pageSize=100')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 26));
    test()->get('/support-cards?pageSize=1000')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 26));

    test()->get('/support-cards?page=2&rarity=1')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 1)
            ->where('cards.data.0.name', 'Trainee 26 [Tracen Academy]'));
});

it('searches the three stored name fields, escapes LIKE metacharacters, and names the ask', function (): void {
    SupportCard::factory()->create([
        'char_name' => 'Special Week',
        'title_en' => '[Tracen Academy]',
        'name_ja' => 'スペシャルウィーク',
    ]);
    SupportCard::factory()->create([
        'char_name' => 'Silence Suzuka',
        'title_en' => '[Beyond This Shining Moment]',
        'name_ja' => 'サイレンススズカ',
    ]);
    SupportCard::factory()->create([
        'char_name' => '100% Real',
        'title_en' => '[Percent]',
        'name_ja' => 'ヒャクパーセント',
    ]);

    test()->get('/support-cards?q=Silence')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 1)
            ->where('cards.data.0.name', 'Silence Suzuka [Beyond This Shining Moment]'));

    // The epithet and the Japanese name are the other two stored fields, and the row prints both.
    test()->get('/support-cards?q=Shining')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 1)
            ->where('cards.data.0.name', 'Silence Suzuka [Beyond This Shining Moment]'));

    test()->get('/support-cards?q=サイレンス')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 1)
            ->where('cards.data.0.name', 'Silence Suzuka [Beyond This Shining Moment]'));

    // `%` is a LIKE wildcard. Unescaped, this would return all three cards instead of the one whose
    // name states it (the KI-26 trap).
    test()->get('/support-cards?q=%25')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 1)
            ->where('cards.data.0.name', '100% Real [Percent]'));

    test()->get('/support-cards?q=Nothing')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 0)
            ->where('askedFor', 'the name "Nothing"'));
});

it('narrows by rarity, by type and by availability', function (): void {
    SupportCard::factory()->ssr()->speed()->create(['char_name' => 'Only SSR Speed']);
    SupportCard::factory()->sr()->stamina()->create(['char_name' => 'Only SR Stamina']);
    SupportCard::factory()->jpOnly()->create(['char_name' => 'Only JP Card']);

    test()->get('/support-cards?rarity=3')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 1)
            ->where('cards.data.0.name', 'Only SSR Speed [Tracen Academy]'));

    test()->get('/support-cards?type=stamina')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 1)
            ->where('cards.data.0.name', 'Only SR Stamina [Tracen Academy]'));

    test()->get('/support-cards?status=JP-only')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 1)
            ->where('cards.data.0.name', 'Only JP Card [Tracen Academy]'));
});

it('hands the pickers the facets a Trainer chose', function (): void {
    SupportCard::factory()->create(['char_name' => 'Special Week']);

    // The rarity facet is the one that can silently fail: PHP keys its map on ints while the query
    // string carries text, so the page casts the chosen value back to text to select the picker. The
    // props are what the picker binds to; the `selected` attribute it paints is asserted in the
    // browser spec.
    test()->get('/support-cards?rarity=3&type=guts&status=Global&sort=released')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('rarity', '3')
            ->where('type', 'guts')
            ->where('status', 'Global')
            ->where('sort', 'released'));
});

it('refuses a facet value the columns cannot hold rather than answering with the whole catalog', function (): void {
    SupportCard::factory()->create(['char_name' => 'Special Week']);

    // Silently dropping an unknown `type` would answer a question nobody asked with the full catalog,
    // which is how a facet lies. The form request rejects it and lands on the canonical route.
    test()->get('/support-cards?type=turbo')
        ->assertStatus(302)
        ->assertRedirect(route('support-cards.index'))
        ->assertSessionHasErrors('type');
});

it('orders by rarity and by release date when asked, and by name when not', function (): void {
    SupportCard::factory()->create(['char_name' => 'Beryl', 'rarity' => CardRarity::OneStar, 'release_global' => '2025-01-01']);
    SupportCard::factory()->create(['char_name' => 'Amber', 'rarity' => CardRarity::ThreeStar, 'release_global' => '2026-01-01']);

    test()->get('/support-cards')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cards.data.0.name', 'Amber [Tracen Academy]')
            ->where('cards.data.1.name', 'Beryl [Tracen Academy]'));

    test()->get('/support-cards?sort=rarity')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cards.data.0.name', 'Beryl [Tracen Academy]')
            ->where('cards.data.1.name', 'Amber [Tracen Academy]'));

    test()->get('/support-cards?sort=released')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cards.data.0.name', 'Amber [Tracen Academy]')
            ->where('cards.data.1.name', 'Beryl [Tracen Academy]'));

    // Explicit name sort (same as default but explicit)
    test()->get('/support-cards?sort=name')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cards.data.0.name', 'Amber [Tracen Academy]')
            ->where('cards.data.1.name', 'Beryl [Tracen Academy]'));
});

it('orders by type when asked', function (): void {
    SupportCard::factory()->wit()->create(['char_name' => 'C Wit']);
    SupportCard::factory()->speed()->create(['char_name' => 'A Speed']);
    SupportCard::factory()->guts()->create(['char_name' => 'B Guts']);

    // Legend order for type sort (C25): Speed, Stamina, Power, Guts, Wit, Pal, Group
    test()->get('/support-cards?sort=type')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cards.data.0.name', 'A Speed [Tracen Academy]')
            ->where('cards.data.1.name', 'B Guts [Tracen Academy]')
            ->where('cards.data.2.name', 'C Wit [Tracen Academy]'));
});

it('orders by friendship bonus at cap with N/A last', function (): void {
    // Card with friendship bonus
    effectRow(1, 'Friendship Bonus');
    SupportCard::factory()->create(['char_name' => 'Has Bonus', 'effects' => [[1, 5, 10, 15, 20, 25, 30, 35, -1, -1, -1, -1]]]);

    // Card without friendship bonus (explicitly no effects)
    SupportCard::factory()->create(['char_name' => 'No Bonus', 'effects' => []]);

    // Card with friendship bonus at higher value
    SupportCard::factory()->create(['char_name' => 'High Bonus', 'effects' => [[1, 10, 15, 20, 25, 30, 35, 40, -1, -1, -1, -1]]]);

    // Card with no effects at all (another N/A)
    SupportCard::factory()->create(['char_name' => 'No Effects', 'effects' => []]);

    // Default: highest friendship bonus first, N/A last
    test()->get('/support-cards?sort=friendship_bonus')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cards.data.0.name', 'High Bonus [Tracen Academy]')
            ->where('cards.data.1.name', 'Has Bonus [Tracen Academy]')
            // N/A cards (no friendship bonus) should be last
            ->where('cards.data.2.friendship', null)
            ->where('cards.data.3.friendship', null));
});

it('orders by initial friendship gauge at cap with N/A last', function (): void {
    // Card with initial friendship gauge (effect 14)
    effectRow(14, 'Initial Friendship Gauge');
    SupportCard::factory()->create(['char_name' => 'Has Gauge', 'effects' => [[14, 5, 10, 15, -1, -1, -1, -1, -1, -1, -1, -1]]]);

    // Card without initial friendship gauge
    SupportCard::factory()->create(['char_name' => 'No Gauge', 'effects' => [[1, 5, 10, 15, -1, -1, -1, -1, -1, -1, -1, -1]]]);

    // Card with higher initial friendship gauge
    SupportCard::factory()->create(['char_name' => 'High Gauge', 'effects' => [[14, 10, 15, 20, -1, -1, -1, -1, -1, -1, -1, -1]]]);

    // Default: highest gauge first, N/A last
    test()->get('/support-cards?sort=initial_friendship_gauge')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cards.data.0.name', 'High Gauge [Tracen Academy]')
            ->where('cards.data.1.name', 'Has Gauge [Tracen Academy]')
            // N/A card should be last
            ->where('cards.data.2.effects.0.effect_id', 1));
});

it('preserves derived sort across pages (friendship_bonus)', function (): void {
    effectRow(1, 'Friendship Bonus');
    // Create 11 cards: 5 with varying friendship bonus, 6 without (N/A)
    // With pageSize=5 (narrowed from 2), this spans 3 pages: 5, 5, 1
    SupportCard::factory()->create(['char_name' => 'Low Bonus', 'effects' => [[1, 5, 10, 15, -1, -1, -1, -1, -1, -1, -1, -1]]]);
    SupportCard::factory()->create(['char_name' => 'Mid Bonus', 'effects' => [[1, 10, 15, 20, -1, -1, -1, -1, -1, -1, -1, -1]]]);
    SupportCard::factory()->create(['char_name' => 'High Bonus', 'effects' => [[1, 15, 20, 25, -1, -1, -1, -1, -1, -1, -1, -1]]]);
    SupportCard::factory()->create(['char_name' => 'Mid2 Bonus', 'effects' => [[1, 12, 18, 22, -1, -1, -1, -1, -1, -1, -1, -1]]]);
    SupportCard::factory()->create(['char_name' => 'Mid3 Bonus', 'effects' => [[1, 8, 12, 18, -1, -1, -1, -1, -1, -1, -1, -1]]]);
    SupportCard::factory()->create(['char_name' => 'No Bonus A', 'effects' => []]);
    SupportCard::factory()->create(['char_name' => 'No Bonus B', 'effects' => []]);
    SupportCard::factory()->create(['char_name' => 'No Bonus C', 'effects' => []]);
    SupportCard::factory()->create(['char_name' => 'No Bonus D', 'effects' => []]);
    SupportCard::factory()->create(['char_name' => 'No Bonus E', 'effects' => []]);
    SupportCard::factory()->create(['char_name' => 'No Bonus F', 'effects' => []]);

    // Page 1: High Bonus, Mid2 Bonus, Mid Bonus, Mid3 Bonus, Low Bonus
    test()->get('/support-cards?sort=friendship_bonus&pageSize=5')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cards.data.0.name', 'High Bonus [Tracen Academy]')
            ->where('cards.data.1.name', 'Mid2 Bonus [Tracen Academy]')
            ->where('cards.data.2.name', 'Mid Bonus [Tracen Academy]')
            ->where('cards.data.3.name', 'Mid3 Bonus [Tracen Academy]')
            ->where('cards.data.4.name', 'Low Bonus [Tracen Academy]')
            ->where('cards.current_page', 1)
            ->where('cards.last_page', 3));

    // Page 2: No Bonus A, No Bonus B, No Bonus C, No Bonus D, No Bonus E (all N/A, tie-break by name)
    test()->get('/support-cards?sort=friendship_bonus&pageSize=5&page=2')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cards.data.0.name', 'No Bonus A [Tracen Academy]')
            ->where('cards.data.1.name', 'No Bonus B [Tracen Academy]')
            ->where('cards.data.2.name', 'No Bonus C [Tracen Academy]')
            ->where('cards.data.3.name', 'No Bonus D [Tracen Academy]')
            ->where('cards.data.4.name', 'No Bonus E [Tracen Academy]')
            ->where('cards.current_page', 2));

    // Page 3: No Bonus F (last N/A)
    test()->get('/support-cards?sort=friendship_bonus&pageSize=5&page=3')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cards.data.0.name', 'No Bonus F [Tracen Academy]')
            ->where('cards.current_page', 3));
});

it('preserves derived sort across pages (initial_friendship_gauge)', function (): void {
    effectRow(14, 'Initial Friendship Gauge');
    // Create 11 cards: 5 with varying gauge, 6 without (N/A)
    SupportCard::factory()->create(['char_name' => 'Low Gauge', 'effects' => [[14, 5, 10, 15, -1, -1, -1, -1, -1, -1, -1, -1]]]);
    SupportCard::factory()->create(['char_name' => 'Mid Gauge', 'effects' => [[14, 10, 15, 20, -1, -1, -1, -1, -1, -1, -1, -1]]]);
    SupportCard::factory()->create(['char_name' => 'High Gauge', 'effects' => [[14, 15, 20, 25, -1, -1, -1, -1, -1, -1, -1, -1]]]);
    SupportCard::factory()->create(['char_name' => 'Mid2 Gauge', 'effects' => [[14, 12, 18, 22, -1, -1, -1, -1, -1, -1, -1, -1]]]);
    SupportCard::factory()->create(['char_name' => 'Mid3 Gauge', 'effects' => [[14, 8, 12, 18, -1, -1, -1, -1, -1, -1, -1, -1]]]);
    SupportCard::factory()->create(['char_name' => 'No Gauge A', 'effects' => []]);
    SupportCard::factory()->create(['char_name' => 'No Gauge B', 'effects' => []]);
    SupportCard::factory()->create(['char_name' => 'No Gauge C', 'effects' => []]);
    SupportCard::factory()->create(['char_name' => 'No Gauge D', 'effects' => []]);
    SupportCard::factory()->create(['char_name' => 'No Gauge E', 'effects' => []]);
    SupportCard::factory()->create(['char_name' => 'No Gauge F', 'effects' => []]);

    // Page 1: High Gauge, Mid2 Gauge, Mid Gauge, Mid3 Gauge, Low Gauge
    test()->get('/support-cards?sort=initial_friendship_gauge&pageSize=5')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cards.data.0.name', 'High Gauge [Tracen Academy]')
            ->where('cards.data.1.name', 'Mid2 Gauge [Tracen Academy]')
            ->where('cards.data.2.name', 'Mid Gauge [Tracen Academy]')
            ->where('cards.data.3.name', 'Mid3 Gauge [Tracen Academy]')
            ->where('cards.data.4.name', 'Low Gauge [Tracen Academy]')
            ->where('cards.current_page', 1)
            ->where('cards.last_page', 3));

    // Page 2: No Gauge A through No Gauge E (all N/A, tie-break by name)
    test()->get('/support-cards?sort=initial_friendship_gauge&pageSize=5&page=2')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cards.data.0.name', 'No Gauge A [Tracen Academy]')
            ->where('cards.data.1.name', 'No Gauge B [Tracen Academy]')
            ->where('cards.data.2.name', 'No Gauge C [Tracen Academy]')
            ->where('cards.data.3.name', 'No Gauge D [Tracen Academy]')
            ->where('cards.data.4.name', 'No Gauge E [Tracen Academy]')
            ->where('cards.current_page', 2));

    // Page 3: No Gauge F
    test()->get('/support-cards?sort=initial_friendship_gauge&pageSize=5&page=3')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cards.data.0.name', 'No Gauge F [Tracen Academy]')
            ->where('cards.current_page', 3));
});

it('names the ask when a filter matches nothing', function (): void {
    SupportCard::factory()->create(['char_name' => 'Special Week']);

    test()->get('/support-cards?rarity=3')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 0)
            ->where('totalCount', 1)
            ->where('askedFor', 'rarity SSR'));
});

it('filters by trainee', function (): void {
    // Create a trainee
    $trainee = Umamusume::factory()->create(['name' => 'Special Week', 'external_ref' => 'gametora:char:123']);
    SupportCard::factory()->create(['char_name' => 'Special Week', 'char_id' => 123, 'title_en' => '[Tracen Academy]']);
    SupportCard::factory()->create(['char_name' => 'Other', 'char_id' => 456, 'title_en' => '[Tracen Academy]']);

    // Filter by the trainee
    test()->get('/support-cards?trainee=gametora:char:123')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 1)
            ->where('cards.data.0.char_name', 'Special Week')
            ->where('trainee', 'gametora:char:123'));

    // Trainee options should include the trainee
    test()->get('/support-cards')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('traineeOptions.gametora:char:123', 'Special Week'));
});

it('filters by carries effect(s) with OR semantics', function (): void {
    effectRow(1, 'Friendship Bonus');
    effectRow(14, 'Initial Friendship Gauge');

    // Card with Friendship Bonus (effect 1)
    SupportCard::factory()->create(['char_name' => 'Has Friendship', 'effects' => [[1, 5, 10, 15, -1, -1, -1, -1, -1, -1, -1, -1]]]);

    // Card with Initial Friendship Gauge (effect 14)
    SupportCard::factory()->create(['char_name' => 'Has Gauge', 'effects' => [[14, 5, 10, 15, -1, -1, -1, -1, -1, -1, -1, -1]]]);

    // Card with neither
    SupportCard::factory()->create(['char_name' => 'No Effects', 'effects' => []]);

    // Filter by Friendship Bonus only
    test()->get('/support-cards?effects=1')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 1)
            ->where('cards.data.0.name', 'Has Friendship [Tracen Academy]'));

    // Filter by Initial Friendship Gauge only
    test()->get('/support-cards?effects=14')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 1)
            ->where('cards.data.0.name', 'Has Gauge [Tracen Academy]'));

    // Filter by both (OR semantics)
    test()->get('/support-cards?effects=1,14')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 2));

    // Effect options should include the effects
    test()->get('/support-cards')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('effectOptions.1', 'Friendship Bonus')
            ->where('effectOptions.14', 'Initial Friendship Gauge'));
});

it('filters by used in a deck', function (): void {
    $cardInDeck = SupportCard::factory()->create(['char_name' => 'In Deck', 'title_en' => '[Tracen Academy]']);
    $cardNotInDeck = SupportCard::factory()->create(['char_name' => 'Not In Deck', 'title_en' => '[Tracen Academy]']);

    // Create a saved run with the first card in a deck slot
    $run = TrainingRun::factory()->create();
    DeckSlot::factory()->create([
        'training_run_id' => $run->id,
        'support_card_id' => $cardInDeck->id,
        'slot_position' => 1,
    ]);

    // Filter by used in deck
    test()->get('/support-cards?used_in_deck=1')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 1)
            ->where('cards.data.0.name', 'In Deck [Tracen Academy]'));

    // Filter by not used in deck
    test()->get('/support-cards?used_in_deck=0')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 1)
            ->where('cards.data.0.name', 'Not In Deck [Tracen Academy]'));
});

it('filters by recorded as owned', function (): void {
    $ownedCard = SupportCard::factory()->create(['char_name' => 'Owned', 'title_en' => '[Tracen Academy]']);
    $rentedCard = SupportCard::factory()->create(['char_name' => 'Rented', 'title_en' => '[Tracen Academy]']);

    // Create a saved run with owned card
    $run = TrainingRun::factory()->create();
    DeckSlot::factory()->create([
        'training_run_id' => $run->id,
        'support_card_id' => $ownedCard->id,
        'slot_position' => 1,
        'ownership' => 'OWNED',
    ]);

    // Create a saved run with rented card
    $run2 = TrainingRun::factory()->create();
    DeckSlot::factory()->create([
        'training_run_id' => $run2->id,
        'support_card_id' => $rentedCard->id,
        'slot_position' => 1,
        'ownership' => 'RENTED',
    ]);

    // Debug: check the query directly
    $ownedCards = SupportCard::query()
        ->whereHas('deckSlots', function (Builder $q): void {
            $q->where('ownership', 'OWNED')
                ->whereHas('trainingRun', function (Builder $rq): void {
                    $rq->whereNotNull('id');
                });
        })
        ->get();
    Log::info('Direct query owned cards: '.$ownedCards->count());
    foreach ($ownedCards as $c) {
        Log::info("  Card: {$c->char_name} {$c->title_en}");
    }

    // Filter by owned
    test()->get('/support-cards?owned=1')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 1)
            ->where('cards.data.0.name', 'Owned [Tracen Academy]'));

    // Filter by not owned (RENTED doesn't count)
    test()->get('/support-cards?owned=0')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 1)
            ->where('cards.data.0.name', 'Rented [Tracen Academy]'));
});

it('renders one card\'s own page with the fields the source states', function (): void {
    SupportCard::factory()->ssr()->create([
        'char_name' => 'Special Week',
        'name_ja' => 'スペシャルウィーク',
        'title_en' => '[Tracen Academy]',
        'title_ja' => '[トレセン学園]',
        'release_jp' => '2021-02-24',
        'release_global' => '2025-06-26',
    ]);

    $card = SupportCard::query()->firstOrFail();

    test()->get("/support-cards/{$card->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('SupportCards/Show')
            ->where('card.name', 'Special Week [Tracen Academy]')
            ->where('card.name_ja', 'スペシャルウィーク')
            ->where('card.title_ja', '[トレセン学園]')
            ->where('card.rarity_word', 'SSR')
            ->where('card.type_label', 'Guts')
            // A calendar date stays a calendar date, so it is formatted server-side and never routed
            // through the display timezone.
            ->where('card.release_jp_display', 'Feb 24, 2021')
            ->where('card.release_global_display', 'Jun 26, 2025')
            ->where('card.release_status', 'Global'));
});

it('resolves the anchor vector at cap and names the basis', function (): void {
    effectRow(1, 'Friendship Bonus');
    cardWithEffects(['char_name' => 'Special Week']);

    $card = SupportCard::query()->firstOrFail();

    test()->get("/support-cards/{$card->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('effects.0.name', 'Friendship Bonus')
            ->where('effects.0.display', '15%'));
});

it('links each hinted skill to its own page', function (): void {
    $skill = globalSkill(200162, 'Corner Adept');
    cardWithEffects(['char_name' => 'Special Week', 'hint_skills' => [200162]]);

    $card = SupportCard::query()->firstOrFail();

    test()->get("/support-cards/{$card->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('hinted.stored', true)
            ->has('hinted.skills', 1)
            ->where('hinted.skills.0.name', 'Corner Adept')
            ->where('hinted.skills.0.url', route('skills.show', $skill))
            ->where('hinted.unlinked', 0));

    test()->get(route('skills.show', $skill))->assertOk();
});

it('links each event skill to its own page', function (): void {
    $skill = globalSkill(200762, 'Straight Burst');
    cardWithEffects(['char_name' => 'Special Week', 'event_skills' => [200762]]);

    $card = SupportCard::query()->firstOrFail();

    test()->get("/support-cards/{$card->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('events.skills.0.name', 'Straight Burst')
            ->where('events.skills.0.url', route('skills.show', $skill)));

    test()->get(route('skills.show', $skill))->assertOk();
});

it('counts a hint id it cannot link rather than dropping it silently', function (): void {
    globalSkill(200162, 'Corner Adept');

    // 200999 is in the card's list and in no skill row, and 200163 names a skill the [Global] scope
    // refuses. Neither may become a link that 404s, and neither may vanish without a word.
    Skill::factory()->create([
        'export_id' => 200163,
        'name' => 'Japan Only Skill',
        'release_status' => ReleaseStatus::JapanOnly,
        'name_is_client' => false,
    ]);

    cardWithEffects(['char_name' => 'Special Week', 'hint_skills' => [200162, 200163, 200999]]);

    $card = SupportCard::query()->firstOrFail();

    test()->get("/support-cards/{$card->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('hinted.skills', 1)
            ->where('hinted.skills.0.name', 'Corner Adept')
            ->where('hinted.unlinked', 2));
});

it('separates a list the source states as empty from a list nothing stored', function (): void {
    SupportCard::factory()->create(['char_name' => 'Empty Hints', 'hint_skills' => [], 'event_skills' => null]);

    $card = SupportCard::query()->firstOrFail();

    test()->get("/support-cards/{$card->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('hinted.stored', true)
            ->has('hinted.skills', 0)
            ->where('events.stored', false)
            ->has('events.skills', 0));
});

it('links the card to the trainee page its character id resolves to', function (): void {
    $trainee = Umamusume::factory()->create(['name' => 'Special Week', 'slug' => 'special-week']);
    $trainee->forceFill(['external_ref' => 'gametora:char:1001'])->save();

    cardWithEffects(['char_name' => 'Special Week', 'char_id' => 1001]);

    $card = SupportCard::query()->firstOrFail();

    test()->get("/support-cards/{$card->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('trainee.name', 'Special Week')
            ->where('trainee.url', route('catalog.show', $trainee->slug)));

    test()->get(route('catalog.show', $trainee->slug))->assertOk();
});

it('names the absence when the character id resolves to no trainee', function (): void {
    // The 9000 block is the source's staff space and the trainable catalog holds no row from it, so a
    // staff card has nobody to link to (ADR-0014 correction 1). A card naming a trainee this catalog does
    // not track misses the same way. The count of both moves with the roster, so no count is asserted.
    SupportCard::factory()->friend()->create();

    $card = SupportCard::query()->firstOrFail();

    test()->get("/support-cards/{$card->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('card.char_name', 'Tazuna Hayakawa')
            ->where('trainee', null));
});

it('prints the row\'s own provenance and whether the Trainer corrected it', function (): void {
    SupportCard::factory()->create([
        'char_name' => 'Special Week',
        'source_url' => 'https://gametora.com/data/umamusume/support-cards.json',
        'is_manual' => true,
    ]);

    $card = SupportCard::query()->firstOrFail();

    test()->get("/support-cards/{$card->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('card.source_url', 'https://gametora.com/data/umamusume/support-cards.json')
            ->where('card.is_manual', true));
});

it('renders no tier label, which ADR-0014 holds pending a current Global source', function (): void {
    cardWithEffects(['char_name' => 'Special Week']);

    $card = SupportCard::query()->firstOrFail();

    test()->get('/support-cards')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->missing('cards.data.0.tier'));

    test()->get("/support-cards/{$card->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->missing('card.tier'));
});

it('refuses a card id that does not exist', function (): void {
    test()->get('/support-cards/999999')->assertNotFound();
});

/*
 * The HFW parity additions (plan Slice 8): the stage table's anchors, the rarity cap, the runs that
 * use the card, and the index's own catalogue-wide facts.
 */

it('states each card level the source gives an effect a value for, and its last anchor as the cap', function (): void {
    effectRow(1, 'Friendship Bonus');
    cardWithEffects(['char_name' => 'Special Week']);

    $card = SupportCard::query()->firstOrFail();

    // The factory vector states 5 at level 1, 10 at 20, 10 at 25 and 15 at 35, so the row carries four
    // pairs: a level between two anchors carries no entry and must not print as a zero.
    test()->get("/support-cards/{$card->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('effectStages', 1)
            ->where('effectStages.0.name', 'Friendship Bonus')
            ->has('effectStages.0.anchors', 4)
            ->where('effectStages.0.anchors.0.level', 1)
            ->where('effectStages.0.anchors.0.display', '5%')
            ->where('effectStages.0.anchors.3.level', 35)
            ->where('effectStages.0.anchors.3.display', '15%')
            ->where('effectStages.0.cap.level', 35)
            ->where('effectStages.0.cap.display', '15%'));
});

it('drops a level the source states nothing at instead of printing a zero', function (): void {
    effectRow(2, 'Mood Effect');
    cardWithEffects([
        'char_name' => 'Special Week',
        // ANCHOR_LEVELS is [1, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50]: position 8 is level 35, so
        // the level-40 slot is position 9.
        'effects' => [[2, -1, -1, -1, -1, -1, -1, -1, -1, 60, -1, -1]],
    ]);

    $card = SupportCard::query()->firstOrFail();

    // One anchor at level 40 and nothing else: the row is short, not padded with five zeros.
    test()->get("/support-cards/{$card->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('effectStages.0.anchors', 1)
            ->where('effectStages.0.anchors.0.level', 40)
            ->where('effectStages.0.anchors.0.display', '60%')
            ->where('effectStages.0.cap.level', 40));
});

it('states the client level cap for each rarity', function (): void {
    $ssr = SupportCard::factory()->ssr()->create(['char_name' => 'SSR Card']);
    $sr = SupportCard::factory()->sr()->create(['char_name' => 'SR Card']);
    $r = SupportCard::factory()->create(['char_name' => 'R Card']);

    // Four breaks at +5 from the unbroken base give SSR 50 / SR 45 / R 40, which is where the export's
    // effect ladder stops for each rarity (`UMAMUSUME_REFERENCE.md` §1.4.2).
    test()->get("/support-cards/{$ssr->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('levelCap', 50));

    test()->get("/support-cards/{$sr->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('levelCap', 45));

    test()->get("/support-cards/{$r->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('levelCap', 40));
});

it('answers where the card is used from the runs whose deck holds it', function (): void {
    $card = SupportCard::factory()->create(['char_name' => 'Special Week']);
    $run = TrainingRun::factory()->create([
        'umamusume_id' => Umamusume::factory()->create(['name' => 'Rice Shower'])->id,
        'scenario' => 'unity_cup',
        'status' => RunStatus::Active,
    ]);
    DeckSlot::factory()->create([
        'training_run_id' => $run->id,
        'support_card_id' => $card->id,
        'card_level' => 45,
    ]);

    test()->get("/support-cards/{$card->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('usedBy', 1)
            ->where('usedBy.0.run_id', $run->id)
            ->where('usedBy.0.trainee_name', 'Rice Shower')
            ->where('usedBy.0.status_label', 'Active')
            ->where('usedBy.0.card_level', 45)
            // The run's own door is its cockpit. `runs.show` is the compatibility doorway that
            // redirects there, so asserting it would pin a hop the Trainer never sees.
            ->where('usedBy.0.url', route('runs.cockpit', $run)));
});

it('splits the row into the character name and the card\'s own epithet', function (): void {
    // The dictionary row supplies the effect's symbol; without it the formatted value has no unit.
    effectRow(1, 'Friendship Bonus');
    cardWithEffects(['char_name' => 'Special Week', 'title_en' => '[Tracen Academy]']);

    test()->get('/support-cards')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cards.data.0.name', 'Special Week [Tracen Academy]')
            ->where('cards.data.0.char_name', 'Special Week')
            ->where('cards.data.0.title', '[Tracen Academy]')
            ->where('cards.data.0.type', 'guts')
            ->where('cards.data.0.friendship.display', '15%'));
});

it('prints no friendship bonus for a Pal card, and none invented for a card that states none', function (): void {
    // A Pal card carries no friendship bonus at all: the type is the absence's cause, not a data gap.
    SupportCard::factory()->friend()->create(['char_name' => 'Aaa Pal', 'effects' => []]);
    cardWithEffects(['char_name' => 'Zzz No Bonus', 'effects' => [[2, 10, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1]]]);

    test()->get('/support-cards')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cards.data.0.type', 'friend')
            ->where('cards.data.0.friendship', null)
            ->where('cards.data.1.type', 'guts')
            ->where('cards.data.1.friendship', null));
});

it('counts the seven types by rarity, names the effects that decide a deck slot, and the ones no card carries', function (): void {
    effectRow(1, 'Friendship Bonus');
    effectRow(2, 'Mood Effect');
    cardWithEffects(['char_name' => 'Special Week']);
    SupportCard::factory()->speed()->create(['char_name' => 'Silence Suzuka']);

    // The first card carries effect 1 and the second carries the factory vector's effect 1 too, so
    // Mood Effect (id 2) is the vocabulary row no card here states a value for. Only Friendship Bonus
    // is in the deciding set, so the other four ids are dropped rather than printed unnamed.
    test()->get('/support-cards')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('typeCounts', 7)
            ->where('typeCounts.0.type', 'speed')
            ->where('typeCounts.0.r', 1)
            ->where('typeCounts.0.sr', 0)
            ->where('typeCounts.0.ssr', 0)
            ->where('typeCounts.0.global_ssr', 0)
            ->where('typeCounts.3.type', 'guts')
            ->where('typeCounts.3.r', 1)
            ->where('typeCounts.3.ssr', 0)
            ->has('decidingEffects', 1)
            ->where('decidingEffects.0.effect_id', 1)
            ->where('decidingEffects.0.name', 'Friendship Bonus')
            ->where('decidingEffects.0.description', "Multiplies the tile's payout. Two cards multiply: 1.25 × 1.30 = 1.625.")
            ->where('uncoveredEffects.0.effect_id', 2)
            ->where('uncoveredEffects.0.name', 'Mood Effect'));
});

it('reconciles the type counts with the catalog total and the Global SSR count', function (): void {
    SupportCard::factory()->ssr()->speed()->create();
    SupportCard::factory()->sr()->stamina()->create();
    SupportCard::factory()->create(['rarity' => CardRarity::OneStar, 'type' => 'guts']);
    SupportCard::factory()->ssr()->speed()->jpOnly()->create();

    test()->get('/support-cards')->assertOk()->assertInertia(function (Assert $page): void {
        /** @var list<array{r: int, sr: int, ssr: int, global_ssr: int}> $rows */
        $rows = $page->toArray()['props']['typeCounts'];

        $total = array_sum(array_map(static fn (array $row): int => $row['r'] + $row['sr'] + $row['ssr'], $rows));
        $globalSsr = array_sum(array_column($rows, 'global_ssr'));

        expect($total)->toBe(4)
            ->and($globalSsr)->toBe(1);
    });
});

it('prunes effect options per server filter (C15)', function (): void {
    // Create a Global card with effect 1 (Friendship Bonus)
    effectRow(1, 'Friendship Bonus');
    effectRow(2, 'Mood Effect');

    SupportCard::factory()->create([
        'char_name' => 'Global Card',
        'effects' => [[1, 5, 10, 15, -1, -1, -1, -1, -1, -1, -1, -1]],
    ]);

    // Create a JP-only card with effect 2 (Mood Effect) - no Global cards have this
    SupportCard::factory()->jpOnly()->create([
        'char_name' => 'JP Card',
        'effects' => [[2, 5, 10, 15, -1, -1, -1, -1, -1, -1, -1, -1]],
    ]);

    // Global server filter: only Friendship Bonus should appear in effectOptions
    test()->get('/support-cards?server=Global')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('effectOptions.1', 'Friendship Bonus')
            ->missing('effectOptions.2')); // Mood Effect not in Global

    // JP server filter: only Mood Effect (from JP-only card) should appear
    test()->get('/support-cards?server=JP')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->missing('effectOptions.1') // Friendship Bonus not in JP
            ->where('effectOptions.2', 'Mood Effect'));

    // Default (Global): only Friendship Bonus
    test()->get('/support-cards')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('effectOptions.1', 'Friendship Bonus')
            ->missing('effectOptions.2'));
});

it('unresolved char_id never matches any trainee option', function (): void {
    // Create a trainee
    $trainee = Umamusume::factory()->create(['name' => 'Special Week', 'external_ref' => 'gametora:char:123']);
    SupportCard::factory()->create(['char_name' => 'Special Week', 'char_id' => 123, 'title_en' => '[Tracen Academy]']);

    // Create a card with char_id that doesn't resolve to any Umamusume
    SupportCard::factory()->create(['char_name' => 'Unresolved', 'char_id' => 9999, 'title_en' => '[Tracen Academy]']);

    // Trainee options should only include the resolved trainee
    test()->get('/support-cards')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('traineeOptions.gametora:char:123', 'Special Week')
            ->missing('traineeOptions.gametora:char:9999'));

    // Filter by the resolved trainee - only the resolved card matches
    test()->get('/support-cards?trainee=gametora:char:123')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 1)
            ->where('cards.data.0.char_name', 'Special Week'));

    // Filter by unresolved trainee (should match nothing, not even the unresolved card)
    test()->get('/support-cards?trainee=gametora:char:9999')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 0));
});

it('owned=0 and owned=1 are complementary; two-filter combination narrows correctly', function (): void {
    $ownedCard = SupportCard::factory()->create(['char_name' => 'Owned Card', 'title_en' => '[Tracen Academy]']);
    $rentedCard = SupportCard::factory()->create(['char_name' => 'Rented Card', 'title_en' => '[Tracen Academy]']);
    $unusedCard = SupportCard::factory()->create(['char_name' => 'Unused Card', 'title_en' => '[Tracen Academy]']);

    $trainee = Umamusume::factory()->create(['name' => 'Special Week', 'external_ref' => 'gametora:char:123']);
    SupportCard::factory()->create(['char_name' => 'Special Week', 'char_id' => 123, 'title_en' => '[Tracen Academy]']);

    // Create saved run with owned card
    $run = TrainingRun::factory()->create();
    DeckSlot::factory()->create([
        'training_run_id' => $run->id,
        'support_card_id' => $ownedCard->id,
        'slot_position' => 1,
        'ownership' => 'OWNED',
    ]);

    // Create saved run with rented card
    $run2 = TrainingRun::factory()->create();
    DeckSlot::factory()->create([
        'training_run_id' => $run2->id,
        'support_card_id' => $rentedCard->id,
        'slot_position' => 1,
        'ownership' => 'RENTED',
    ]);

    // owned=1 returns only owned cards
    test()->get('/support-cards?owned=1')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 1)
            ->where('cards.data.0.name', 'Owned Card [Tracen Academy]')
        );

    // owned=0 returns rented + unused + trainee card (complementary to owned=1 which returns 1 of 4 cards)
    test()->get('/support-cards?owned=0')
        ->assertOk()
        ->assertInertia(function (Assert $page) {
            $props = $page->toArray();
            Log::info('DEBUG owned=0 assertInertia cards: '.json_encode(array_map(fn ($c) => $c['name'], $props['props']['cards']['data'] ?? [])));
            $page->has('cards.data', 3);
        });

    // Two-filter combination: trainee + used_in_deck
    // First, make the owned card belong to the trainee
    $ownedCard->update(['char_id' => 123, 'char_name' => 'Special Week']);

    $comboResponse = test()->get('/support-cards?trainee=gametora:char:123&used_in_deck=1');
    $comboResponse->assertOk();
    $comboContent = $comboResponse->getContent();
    $comboData = json_decode($comboContent, true);
    Log::info('DEBUG combo raw start: '.substr($comboContent, 0, 500));
    Log::info('DEBUG combo cards: '.json_encode(array_map(fn ($c) => $c['name'], $comboData['page']['props']['cards']['data'] ?? [])));
    $comboResponse->assertInertia(fn (Assert $page) => $page
        ->has('cards.data', 1)
        ->where('cards.data.0.name', 'Special Week [Tracen Academy]')
    );

    // Two-filter combination: owned + used_in_deck (both true should still return owned in deck)
    test()->get('/support-cards?owned=1&used_in_deck=1')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 1)
            ->where('cards.data.0.name', 'Special Week [Tracen Academy]')
        );
});
