<?php

declare(strict_types=1);

use App\Models\CharacterCard;
use App\Models\Umamusume;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

/*
 * The catalog detail page's Inertia payload carries one Identity portrait URL, taken from the same
 * card the page reads its skill lists from (`$activeCard ?? $umamusume->cards->first()`,
 * `CatalogController::show()`). That identity matters and is what this test pins: the portrait is
 * the *active form's* art, so switching forms with `?card=` is expected to change the frame, and a
 * regression that quietly pinned the frame to `cards->first()` would leave the picture disagreeing
 * with the skills printed underneath it.
 *
 * The URL comes from `ArtworkMirror::url('card_portrait', $card->card_id)` — the publisher's own
 * `card_id`, never the local primary key, because that is the only key the mirror's storage path is
 * addressed by. A null is the mirror's normal partial answer and `ArtworkSlot` renders no element
 * for it (`DESIGN.md` §4.7: absence is a normal state, not an error state).
 *
 * This asserts the props. Geometry (`size-16`), the decorative `alt=""` and the absent anchor all
 * live in `resources/js/pages/Catalog/Show.vue` and `tests/browser/catalog-detail.spec.ts`.
 */

beforeEach(function (): void {
    Storage::fake('local');

    config(['uma.sources.gametora-artwork.delay_ms' => 0]);
});

it('passes the active form portrait when the mirror holds the file', function (): void {
    $umamusume = Umamusume::factory()->create(['name' => 'Special Week', 'slug' => 'special-week']);
    $card = CharacterCard::factory()->create(['umamusume_id' => $umamusume->id, 'card_id' => 100101]);

    Storage::disk('local')->put('artwork/characters/portrait/trainee/256/100101.png', 'PNG');

    $this->get('/umamusume/special-week?card='.$card->id)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Catalog/Show')
            ->where('trainee.artworkURL', route('artwork.show', ['kind' => 'card_portrait', 'id' => 100101])));
});

it('passes null when the active form portrait is not mirrored', function (): void {
    $umamusume = Umamusume::factory()->create(['name' => 'Special Week', 'slug' => 'special-week']);
    CharacterCard::factory()->create(['umamusume_id' => $umamusume->id, 'card_id' => 100101]);

    $this->get('/umamusume/special-week')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Catalog/Show')
            ->where('trainee.artworkURL', null));
});

it('passes null for a trainee with no card at all', function (): void {
    Umamusume::factory()->create(['name' => 'Special Week', 'slug' => 'special-week']);

    // The null branch is a real state, not defensive: a trainee the character fetch has not given a
    // costume form yet has no card to key a path on, and `show()` already handles that by printing
    // its "no forms" copy rather than erroring. The slot must not be what turns it into a 500.
    $this->get('/umamusume/special-week')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Catalog/Show')
            ->where('trainee.artworkURL', null));
});
