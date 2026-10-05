<?php

declare(strict_types=1);

use App\Models\CharacterCard;
use Illuminate\Support\Facades\Storage;

/*
 * Task 6: the catalog detail Identity slot wires the portrait into the page header.
 *
 * `character-portrait.blade.php` is the unit gate for the component's own states, and
 * `ArtworkMirrorTest` locks the disk-path contract (config-driven, `characters/portrait/
 * trainee/256/{id}.png`). This test locks the integration: that `show.blade.php` invokes the
 * component with the active card's id, and that the absent-file branch leaves the page free
 * of any `<img>` rather than drawing a frame around nothing.
 *
 * `decorative` is what Identity passes, because the page already prints the trainee's name in
 * the `<h1>` the slot sits beside. `DESIGN.md` §4.7 says a decorative image takes `alt=""` so
 * a screen reader reads the name once, from the text. So the present-state case asserts the
 * blank alt is what actually reaches the rendered HTML from the host view, not from the unit
 * test's own fixture name.
 */

beforeEach(function (): void {
    Storage::fake('local');
});

it('shows the mirrored portrait when present', function (): void {
    $card = CharacterCard::factory()->create(['card_id' => 100101]);

    Storage::disk('local')->put('artwork/characters/portrait/trainee/256/100101.png', 'PNG');

    $this->get('/umamusume/'.$card->umamusume->slug)
        ->assertOk()
        ->assertSee('<img', false)
        ->assertSee('alt=""', false);
});

it('omits the slot when the portrait is not mirrored', function (): void {
    $card = CharacterCard::factory()->create(['card_id' => 100101]);

    $this->get('/umamusume/'.$card->umamusume->slug)
        ->assertOk()
        ->assertDontSee('<img', false);
});
