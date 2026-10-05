<?php

declare(strict_types=1);

use App\Enums\CardRarity;
use App\Enums\ReleaseStatus;
use App\Models\Skill;
use App\Models\SupportCard;
use App\Models\SupportEffect;
use App\Models\Umamusume;
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

it('shows the rarity word, the type word and the availability the source states', function (): void {
    SupportCard::factory()->ssr()->wit()->create(['char_name' => 'Silence Suzuka']);
    SupportCard::factory()->jpOnly()->create(['char_name' => 'Gold Ship']);

    test()->get('/support-cards')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cards.data.0.name', 'Gold Ship [Tracen Academy]')
            ->where('cards.data.0.rarity_word', 'R')
            ->where('cards.data.0.release_status', 'JP-only')
            ->where('cards.data.1.name', 'Silence Suzuka [Tracen Academy]')
            ->where('cards.data.1.rarity_word', 'SSR')
            ->where('cards.data.1.type_label', 'Wit')
            ->where('cards.data.1.release_status', 'Global'));
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

it('paginates at twenty-five cards and carries the filter through the link', function (): void {
    foreach (range(1, 26) as $index) {
        SupportCard::factory()->create(['char_name' => sprintf('Trainee %02d', $index)]);
    }

    test()->get('/support-cards')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 25)
            ->where('cards.data.0.name', 'Trainee 01 [Tracen Academy]')
            ->where('cards.data.24.name', 'Trainee 25 [Tracen Academy]')
            ->where('cards.total', 26)
            ->where('totalCount', 26));

    test()->get('/support-cards?page=2&rarity=1')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 1)
            ->where('cards.data.0.name', 'Trainee 26 [Tracen Academy]'));
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
});

it('names the fetch that fills an empty catalog', function (): void {
    // The page renders the fetch-and-reparse copy; here the contract is the state that selects it.
    test()->get('/support-cards')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 0)
            ->where('totalCount', 0));
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
