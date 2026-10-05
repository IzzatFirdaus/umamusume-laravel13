<?php

declare(strict_types=1);

use App\Models\SupportCard;
use Illuminate\Support\Facades\Storage;

/*
 * Task 8: the support-card index row wires the thumbnail into the leading cell.
 *
 * `SupportThumbComponentTest` owns the component's own states and `ArtworkMirrorTest` owns the
 * disk-path contract. This test owns the integration, which is where the one thing a component
 * test cannot see lives: this row carries two different identifiers, and they are not
 * interchangeable.
 *
 * `support_cards.id` is the local primary key and the one `support-cards.show` binds on, because
 * `SupportCardController` declares no `getRouteKeyName()`. `support_cards.support_id` is the
 * publisher's own number and the one `ArtworkMirror::sourceIds('support_thumb')` reads, so it is
 * the only key the storage path can be addressed by. A row that passes `support_id` to
 * `route-args` therefore links to `/support-cards/{support_id}`, which is 404 unless a local id
 * happens to equal a published number; a row that passes the local id to `support-id` points the
 * frame at a path the mirror never wrote. Neither failure is visible to a component test, because
 * a component test supplies both arguments by hand.
 *
 * The swap case is therefore seeded so the two ids differ (`support_id` 10001 against an
 * autoincrement `id` of 1) and asserts both URLs positively, then asserts the crossed pairs are
 * absent from the page.
 *
 * The slot is clickable here by `design-2.0` §45a ("navigates to support-card detail"), so it is
 * not `decorative`: §42's label-in-name clause wants the anchor's `aria-label` to carry the row's
 * printed name verbatim, and a name-bearing anchor is what keeps the duplicated destination
 * findable rather than anonymous.
 */

beforeEach(function (): void {
    Storage::fake('local');
});

it('renders the mirrored thumb linking to the card detail page', function (): void {
    $card = SupportCard::factory()->create(['support_id' => 10001]);

    Storage::disk('local')->put('artwork/supports/full/small/10001.png', 'PNG');

    $this->get('/support-cards')
        ->assertOk()
        ->assertSee('<img', false)
        ->assertSee(route('artwork.show', ['kind' => 'support_thumb', 'id' => 10001]), false)
        ->assertSee('alt="'.$card->displayName().'"', false)
        ->assertSee('aria-label="'.$card->displayName().'"', false)
        ->assertSee(route('support-cards.show', ['card' => $card->id]), false);
});

it('keys the frame on support_id and the link on the local id', function (): void {
    $card = SupportCard::factory()->create(['support_id' => 10001]);

    // The proof is void if the two keys are equal, so the test says so out loud rather than
    // passing on an autoincrement that happened to land on the published number.
    expect($card->id)->not->toBe(10001);

    Storage::disk('local')->put('artwork/supports/full/small/10001.png', 'PNG');

    $frame = route('artwork.show', ['kind' => 'support_thumb', 'id' => 10001]);
    $detail = route('support-cards.show', ['card' => $card->id]);
    $crossedFrame = route('artwork.show', ['kind' => 'support_thumb', 'id' => $card->id]);
    $crossedDetail = route('support-cards.show', ['card' => 10001]);

    // Every URL is matched inside its attribute, quotes included. `artwork.show` for id 1 is a
    // literal prefix of the one for id 10001, so a bare `assertDontSee` on the crossed pair passes
    // by accident in exactly the arrangement this case exists to reject.
    $this->get('/support-cards')
        ->assertOk()
        ->assertSee('src="'.$frame.'"', false)
        ->assertSee('href="'.$detail.'"', false)
        ->assertDontSee('src="'.$crossedFrame.'"', false)
        ->assertDontSee('href="'.$crossedDetail.'"', false);
});

it('omits the thumb when the mirror holds no file', function (): void {
    SupportCard::factory()->create(['support_id' => 10001]);

    // `design-2.0` §45a: a miss is the text-only row the screen ships today, so no frame, no grey
    // box and no placeholder glyph is left behind. `role="img"` on the rarity chip is not an
    // `<img>` tag, so this assertion is about the frame and nothing else.
    $this->get('/support-cards')
        ->assertOk()
        ->assertDontSee('<img', false);
});
