<?php

declare(strict_types=1);

use App\Models\SupportCard;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

/*
 * The support-card screens' Inertia payloads carry one thumbnail URL each, from
 * `ArtworkMirror::url('support_thumb', $card->support_id)` — the `ADR-0021` read half.
 *
 * **The two identifiers on these rows are not interchangeable, and that is what this file is
 * for.** `support_cards.support_id` is the publisher's number and the only key the mirror's storage
 * path answers to; `support_cards.id` is the local primary key and the one `support-cards.show`
 * binds on, because `SupportCardController` declares no `getRouteKeyName()`. A row that passes
 * `support_id` where a URL is wanted links to a page that does not exist; a row that passes the
 * local id where a path is wanted points the frame at a file the mirror never wrote. Neither
 * mistake shows up as a broken image — a wrong `support-id` simply finds no file, and the slot
 * renders nothing, so the bug reads as "the pictures never appeared".
 *
 * So each case asserts both halves at once, and `keys the frame and the link` seeds the two ids to
 * different values on purpose. Every URL is matched inside its attribute: `artwork.show` for id 1
 * is a literal prefix of the one for id 10001, so a bare `assertDontSee` on the crossed pair would
 * pass by accident in exactly the arrangement this file exists to reject.
 *
 * Geometry (`size-12` on the index, `size-16` on the detail), the decorative alt and the presence or
 * absence of an anchor live in the two `.vue` pages and `tests/browser/support-cards.spec.ts`.
 */

beforeEach(function (): void {
    Storage::fake('local');

    config(['uma.sources.gametora-artwork.delay_ms' => 0]);
});

it('passes the mirrored thumbnail keyed on the publisher support_id', function (): void {
    SupportCard::factory()->create(['support_id' => 10001]);

    Storage::disk('local')->put('artwork/supports/full/small/10001.png', 'PNG');

    $this->get('/support-cards')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('SupportCards/Index')
            ->where('cards.data.0.artworkURL', route('artwork.show', ['kind' => 'support_thumb', 'id' => 10001])));
});

it('passes null per row when the mirror holds no file', function (): void {
    SupportCard::factory()->create(['support_id' => 10001]);

    $this->get('/support-cards')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('SupportCards/Index')
            ->where('cards.data.0.artworkURL', null));
});

it('keys the frame on support_id and the link on the local id', function (): void {
    $card = SupportCard::factory()->create(['support_id' => 10001]);

    // The proof is void if the two keys are equal, so the test says so out loud rather than passing
    // on an autoincrement that happened to land on the published number.
    expect($card->id)->not->toBe(10001);

    Storage::disk('local')->put('artwork/supports/full/small/10001.png', 'PNG');

    $this->get('/support-cards')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('SupportCards/Index')
            ->where('cards.data.0.artworkURL', route('artwork.show', ['kind' => 'support_thumb', 'id' => 10001]))
            ->where('cards.data.0.url', route('support-cards.show', ['card' => $card->id]))
            // The crossed frame: the local id where the publisher's number belongs. Rendered, this
            // is not a 404 and not a broken image — it is a silently absent slot.
            ->where('cards.data.0.artworkURL', fn (mixed $value): bool => $value !== route('artwork.show', ['kind' => 'support_thumb', 'id' => $card->id])));
});

it('passes the mirrored thumbnail on the detail page, keyed on support_id', function (): void {
    $card = SupportCard::factory()->create(['support_id' => 10001]);

    Storage::disk('local')->put('artwork/supports/full/small/10001.png', 'PNG');

    $this->get('/support-cards/'.$card->id)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('SupportCards/Show')
            ->where('card.artworkURL', route('artwork.show', ['kind' => 'support_thumb', 'id' => 10001])));
});

it('passes null on the detail page when the mirror holds no file', function (): void {
    $card = SupportCard::factory()->create(['support_id' => 10001]);

    $this->get('/support-cards/'.$card->id)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('SupportCards/Show')
            ->where('card.artworkURL', null));
});
