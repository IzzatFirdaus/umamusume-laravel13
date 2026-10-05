<?php

declare(strict_types=1);

use App\Models\CharacterCard;
use App\Models\Umamusume;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

/*
 * The catalog index's Inertia payload carries one portrait URL per trainee row (the top-rarity
 * form's card) and one per nested card, sourced from `ArtworkMirror::url('card_portrait', $id)` —
 * the same loopback route the Blade slot points at (`ADR-0021` read half). A URL is present only
 * when the mirror holds a file; a null is the mirror's normal partial answer and Vue renders no
 * `<img>` for it (`DESIGN.md` §4.7). This asserts the props, not the rendered DOM: the two-level
 * row and the `alt=""` handling live in `resources/js/pages/Catalog/Index.vue` and `tests/browser`.
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
