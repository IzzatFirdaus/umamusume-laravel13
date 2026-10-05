<?php

declare(strict_types=1);

use App\Enums\CardRarity;
use App\Models\CharacterCard;
use App\Models\Umamusume;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

/*
 * The catalog index's Inertia payload carries one portrait URL per trainee row (the debut form's
 * card, which is the row's identity) and one per nested card, sourced from
 * `ArtworkMirror::url('card_portrait', $id)` — the same loopback route the slot points at
 * (`ADR-0021` read half). A URL is present only when the mirror holds a file; a null is the mirror's
 * normal partial answer and Vue renders no `<img>` for it (`DESIGN.md` §4.7). This asserts the
 * props, not the rendered DOM: the two-level row and the `alt=""` handling live in
 * `resources/js/pages/Catalog/Index.vue` and `tests/browser`.
 */

beforeEach(function (): void {
    Storage::fake('local');

    // The declared delay is politeness toward a third-party host, and a test does not need it.
    config(['uma.sources.gametora-artwork.delay_ms' => 0]);
});

it('passes a portrait url per trainee row and per form when the mirror holds the file', function (): void {
    $umamusume = Umamusume::factory()->create(['name' => 'Special Week', 'slug' => 'special-week']);
    CharacterCard::factory()->create(['umamusume_id' => $umamusume->id, 'card_id' => 100101]);

    Storage::disk('local')->put('artwork/characters/portrait/trainee/256/100101.png', 'PNG');

    $url = route('artwork.show', ['kind' => 'card_portrait', 'id' => 100101]);

    $this->get('/umamusume')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Catalog/Index')
            ->where('umamusumes.data.0.artworkURL', $url)
            ->where('umamusumes.data.0.cards.0.artworkURL', $url));
});

it('passes null per trainee row and per form when the portrait is not mirrored', function (): void {
    $umamusume = Umamusume::factory()->create(['name' => 'Special Week', 'slug' => 'special-week']);
    CharacterCard::factory()->create(['umamusume_id' => $umamusume->id, 'card_id' => 100101]);

    $this->get('/umamusume')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Catalog/Index')
            ->where('umamusumes.data.0.artworkURL', null)
            ->where('umamusumes.data.0.cards.0.artworkURL', null));
});

/*
 * The index header is identity and the detail page header is the form in view, so the two screens
 * read different cards on purpose (DESIGN.md §4.7). Identity is the debut form, which the export
 * derives by rule, not the highest rarity: a trainee whose SSR costume was released later is still
 * the trainee she was at debut. The rarity badge keeps reading the top form on the same row, so
 * this asserts the portrait and the badge come from two different cards and neither is wrong.
 */
it('heads the row with the debut form while the badge still names the top rarity', function (): void {
    $umamusume = Umamusume::factory()->create(['name' => 'Special Week', 'slug' => 'special-week']);

    $debut = CharacterCard::factory()->debut()->create([
        'umamusume_id' => $umamusume->id,
        'card_id' => 100101,
        'rarity' => CardRarity::TwoStar,
    ]);
    CharacterCard::factory()->create([
        'umamusume_id' => $umamusume->id,
        'card_id' => 100102,
        'rarity' => CardRarity::ThreeStar,
    ]);

    Storage::disk('local')->put('artwork/characters/portrait/trainee/256/100101.png', 'PNG');
    Storage::disk('local')->put('artwork/characters/portrait/trainee/256/100102.png', 'PNG');

    $debutUrl = route('artwork.show', ['kind' => 'card_portrait', 'id' => $debut->card_id]);
    $topUrl = route('artwork.show', ['kind' => 'card_portrait', 'id' => 100102]);

    $this->get('/umamusume')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Catalog/Index')
            ->where('umamusumes.data.0.artworkURL', $debutUrl)
            ->where('umamusumes.data.0.max_rarity.stars', '★★★')
            // Both forms still carry their own frame inside the row; only the header rule moved.
            ->where('umamusumes.data.0.cards.0.artworkURL', $debutUrl)
            ->where('umamusumes.data.0.cards.1.artworkURL', $topUrl));
});
