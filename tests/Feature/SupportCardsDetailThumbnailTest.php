<?php

declare(strict_types=1);

use App\Models\SupportCard;
use Illuminate\Support\Facades\Storage;

/*
 * Task 8: the support-card detail header wires the thumbnail into the title block.
 *
 * Same integration boundary as `SupportCardsIndexThumbnailTest`, the other half of the plan's
 * Task 8: this screen addresses the row by its own row, so the two-identifier question does not
 * arise here, but the geometry does. `design-2.0` §45a fixes this slot at `size-16` while the
 * index row carries `size-12`, so the case asserts the recorded box rather than leaving the
 * component's own default (`size-12`) to decide it silently.
 *
 * On `decorative`: §45a gives this slot the click action "no action", but
 * `support-thumb.blade.php` always wraps the frame in a link, so the host cannot opt out without
 * editing a file this slice does not own. `decorative` was rejected here because it blanks
 * `aria-label` as well as `alt`, and a blank `aria-label` on a focusable anchor is an unnamed
 * control, which fails WCAG 2.2 AA 4.1.2 on a screen whose binding constraint is WCAG 2.2 AA.
 * The component's default is therefore used: the self-link keeps an accessible name and the `alt`
 * carries the client's display name. The residual deviation is one alt attribute, recorded in the
 * SDD ledger for Task 10 alongside the Task 4 finding it belongs to.
 */

beforeEach(function (): void {
    Storage::fake('local');
});

it('renders the mirrored thumb in the header at the recorded geometry', function (): void {
    $card = SupportCard::factory()->create(['support_id' => 10001]);

    Storage::disk('local')->put('artwork/supports/full/small/10001.png', 'PNG');

    $this->get('/support-cards/'.$card->id)
        ->assertOk()
        ->assertSee('<img', false)
        ->assertSee(route('artwork.show', ['kind' => 'support_thumb', 'id' => 10001]), false)
        ->assertSee(route('support-cards.show', ['card' => $card->id]), false)
        ->assertSee('size-16', false);
});

it('omits the thumb when the mirror holds no file', function (): void {
    $card = SupportCard::factory()->create(['support_id' => 10001]);

    $this->get('/support-cards/'.$card->id)
        ->assertOk()
        ->assertDontSee('<img', false);
});
