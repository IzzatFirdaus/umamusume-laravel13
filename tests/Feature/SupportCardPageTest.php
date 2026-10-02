<?php

declare(strict_types=1);

use App\Enums\CardRarity;
use App\Enums\ReleaseStatus;
use App\Models\Skill;
use App\Models\SupportCard;
use App\Models\SupportEffect;
use App\Models\Umamusume;

/*
 * The two support-card read surfaces, `/support-cards` and `/support-cards/{id}`.
 *
 * Assertions are on rendered text rather than on markup, for the reason the skill-detail slice recorded:
 * Blade escapes what the source stored, so a title carrying brackets or an apostrophe does not survive a
 * raw-string comparison. Where a link has to be proved, the test navigates it instead of reading the href.
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

    $response = test()->get('/support-cards')->assertOk();

    $response->assertSee('Special Week [Tracen Academy]')
        ->assertSee('Tazuna Hayakawa [Pal]')
        ->assertSee('2 of 2 support cards')
        ->assertSee(route('support-cards.show', $special), false)
        ->assertSee(route('support-cards.show', $tazuna), false);
});

it('shows the rarity word, the type word and the availability the source states', function (): void {
    SupportCard::factory()->ssr()->wit()->create(['char_name' => 'Silence Suzuka']);
    SupportCard::factory()->jpOnly()->create(['char_name' => 'Gold Ship']);

    test()->get('/support-cards')
        ->assertOk()
        ->assertSee('SSR')
        ->assertSee('Wit')
        ->assertSee('JP-only')
        ->assertSee('Global');
});

it('summarizes each card\'s effects at cap on the row', function (): void {
    effectRow(1, 'Friendship Bonus');
    cardWithEffects(['char_name' => 'Special Week']);

    // The factory vector's last stated anchor is 15 at card level 35, and the dictionary row's `percent`
    // symbol is what makes it a percentage rather than a bare number.
    test()->get('/support-cards')->assertOk()->assertSee('Friendship Bonus 15%');
});

it('names an effect the dictionary has no row for instead of inventing a label', function (): void {
    cardWithEffects(['char_name' => 'Special Week', 'effects' => [[77, 5, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1]]]);

    test()->get('/support-cards')->assertOk()->assertSee('[Unverified] effect 77');
});

it('paginates at twenty-five cards and carries the filter through the link', function (): void {
    foreach (range(1, 26) as $index) {
        SupportCard::factory()->create(['char_name' => sprintf('Trainee %02d', $index)]);
    }

    test()->get('/support-cards')
        ->assertOk()
        ->assertSee('Trainee 01')
        ->assertSee('26 of 26 support cards')
        ->assertDontSee('Trainee 26');

    test()->get('/support-cards?page=2&rarity=1')
        ->assertOk()
        ->assertSee('Trainee 26')
        ->assertDontSee('Trainee 01');
});

it('narrows by rarity, by type and by availability', function (): void {
    SupportCard::factory()->ssr()->speed()->create(['char_name' => 'Only SSR Speed']);
    SupportCard::factory()->sr()->stamina()->create(['char_name' => 'Only SR Stamina']);
    SupportCard::factory()->jpOnly()->create(['char_name' => 'Only JP Card']);

    test()->get('/support-cards?rarity=3')->assertOk()
        ->assertSee('Only SSR Speed')->assertDontSee('Only SR Stamina');

    test()->get('/support-cards?type=stamina')->assertOk()
        ->assertSee('Only SR Stamina')->assertDontSee('Only SSR Speed');

    test()->get('/support-cards?status=JP-only')->assertOk()
        ->assertSee('Only JP Card')->assertDontSee('Only SSR Speed');
});

it('keeps the facets a Trainer chose selected in the pickers', function (): void {
    SupportCard::factory()->create(['char_name' => 'Special Week']);

    // The rarity picker is the one that can silently fail: PHP keys its map on ints while the query
    // string carries text, so a comparison without a cast leaves every picker reading "All" on a page
    // that is plainly filtered.
    $html = test()->get('/support-cards?rarity=3&type=guts&status=Global&sort=released')
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain('value="3" selected')
        ->toContain('value="guts" selected')
        ->toContain('value="Global" selected')
        ->toContain('value="released" selected');
});

it('refuses a facet value the columns cannot hold rather than answering with the whole catalog', function (): void {
    SupportCard::factory()->create(['char_name' => 'Special Week']);

    test()->get('/support-cards?type=turbo')
        ->assertStatus(302)
        ->assertRedirect(route('support-cards.index'));

    test()->followingRedirects()->get('/support-cards?type=turbo')->assertSee('Type:');
});

it('orders by rarity and by release date when asked, and by name when not', function (): void {
    SupportCard::factory()->create(['char_name' => 'Beryl', 'rarity' => CardRarity::OneStar, 'release_global' => '2025-01-01']);
    SupportCard::factory()->create(['char_name' => 'Amber', 'rarity' => CardRarity::ThreeStar, 'release_global' => '2026-01-01']);

    $byName = test()->get('/support-cards')->assertOk()->getContent();
    expect(strpos($byName, 'Amber'))->toBeLessThan(strpos($byName, 'Beryl'));

    $byRarity = test()->get('/support-cards?sort=rarity')->assertOk()->getContent();
    expect(strpos($byRarity, 'Beryl'))->toBeLessThan(strpos($byRarity, 'Amber'));

    $byRelease = test()->get('/support-cards?sort=released')->assertOk()->getContent();
    expect(strpos($byRelease, 'Amber'))->toBeLessThan(strpos($byRelease, 'Beryl'));
});

it('names the fetch that fills an empty catalog', function (): void {
    test()->get('/support-cards')
        ->assertOk()
        ->assertSee('The support-card catalog holds no rows yet')
        ->assertSee('uma:fetch gametora-support-cards');
});

it('names the ask when a filter matches nothing', function (): void {
    SupportCard::factory()->create(['char_name' => 'Special Week']);

    test()->get('/support-cards?rarity=3')
        ->assertOk()
        ->assertSee('Nothing matches')
        ->assertSee('rarity SSR')
        ->assertDontSee('Special Week');
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
        ->assertSee('Special Week [Tracen Academy]')
        ->assertSee('スペシャルウィーク')
        ->assertSee('[トレセン学園]')
        ->assertSee('SSR')
        ->assertSee('Guts')
        ->assertSee('Feb 24, 2021')
        ->assertSee('Jun 26, 2025')
        ->assertSee('Global');
});

it('resolves the anchor vector at cap and names the basis', function (): void {
    effectRow(1, 'Friendship Bonus');
    cardWithEffects(['char_name' => 'Special Week']);

    $card = SupportCard::query()->firstOrFail();

    test()->get("/support-cards/{$card->id}")
        ->assertOk()
        ->assertSee('Effects')
        ->assertSee('Friendship Bonus')
        ->assertSee('15%')
        ->assertSee('highest stated anchor');
});

it('links each hinted skill to its own page', function (): void {
    $skill = globalSkill(200162, 'Corner Adept');
    cardWithEffects(['char_name' => 'Special Week', 'hint_skills' => [200162]]);

    $card = SupportCard::query()->firstOrFail();

    test()->get("/support-cards/{$card->id}")
        ->assertOk()
        ->assertSee('Hinted skills')
        ->assertSee('Corner Adept');

    test()->get(route('skills.show', $skill))->assertOk()->assertSee('Corner Adept');
});

it('links each event skill to its own page', function (): void {
    $skill = globalSkill(200762, 'Straight Burst');
    cardWithEffects(['char_name' => 'Special Week', 'event_skills' => [200762]]);

    $card = SupportCard::query()->firstOrFail();

    test()->get("/support-cards/{$card->id}")
        ->assertOk()
        ->assertSee('Event skills')
        ->assertSee('Straight Burst');

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
        ->assertSee('Corner Adept')
        ->assertSee('2 hinted skill ids')
        ->assertDontSee('Japan Only Skill');
});

it('separates a list the source states as empty from a list nothing stored', function (): void {
    SupportCard::factory()->create(['char_name' => 'Empty Hints', 'hint_skills' => [], 'event_skills' => null]);

    $card = SupportCard::query()->firstOrFail();

    test()->get("/support-cards/{$card->id}")
        ->assertOk()
        ->assertSee('The source lists no hinted skills for this card')
        ->assertSee('No event skill list is stored for this card');
});

it('links the card to the trainee page its character id resolves to', function (): void {
    $trainee = Umamusume::factory()->create(['name' => 'Special Week', 'slug' => 'special-week']);
    $trainee->forceFill(['external_ref' => 'gametora:char:1001'])->save();

    cardWithEffects(['char_name' => 'Special Week', 'char_id' => 1001]);

    $card = SupportCard::query()->firstOrFail();

    test()->get("/support-cards/{$card->id}")
        ->assertOk()
        ->assertSee('Belongs to')
        ->assertSee('Special Week');

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
        ->assertSee('Tazuna Hayakawa')
        ->assertSee('has no trainee page in this catalog');
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
        ->assertSee('Provenance')
        ->assertSee('gametora.com/data/umamusume/support-cards.json')
        ->assertSee('Corrected by hand');
});

it('renders no tier label, which ADR-0014 holds pending a current Global source', function (): void {
    cardWithEffects(['char_name' => 'Special Week']);

    $card = SupportCard::query()->firstOrFail();

    test()->get('/support-cards')->assertOk()->assertDontSee('Tier');
    test()->get("/support-cards/{$card->id}")->assertOk()->assertDontSee('Tier');
});

it('refuses a card id that does not exist', function (): void {
    test()->get('/support-cards/999999')->assertNotFound();
});
