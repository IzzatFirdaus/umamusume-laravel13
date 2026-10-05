<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;

/*
 * `<x-support-thumb>` is the read half of `ADR-0021` for support cards. `DESIGN.md` §4.7 fixes what
 * it owes a Trainer when the mirror holds nothing: the row reflows to text only, so the component
 * renders no element at all rather than an empty frame, a grey box, or a placeholder. Absence is a
 * normal answer here, not an error state, and the link wrapper belongs or disappears with the image.
 *
 * Two cases where the portrait has three: the decorative alt path stays implicit, because a support
 * card rarely prints its name beside the thumb, so `decorative` remains a prop a caller sets when
 * the placeholder calls for it rather than a mode carrying its own regression test.
 *
 * No route stub: `routes/web.php` registers `support-cards.show` and `artwork.show` (Task 2), a
 * Feature test boots the full app, and a stub here would collide with the name it restates.
 */

beforeEach(function (): void {
    Storage::fake('local');
});

it('renders an img when the file is mirrored', function (): void {
    Storage::disk('local')->put('artwork/supports/full/small/10001.png', 'PNG');

    $rendered = Blade::render(
        '<x-support-thumb :support-id="10001" size-class="size-12" name="Special Week" :route-args="[\'card\' => 10001]" />',
    );

    expect($rendered)
        ->toContain('<img')
        ->and($rendered)->toContain(route('artwork.show', ['kind' => 'support_thumb', 'id' => 10001]))
        ->and($rendered)->toContain('alt="Special Week"')
        ->and($rendered)->toContain('aria-label="Special Week"')
        ->and($rendered)->toContain(route('support-cards.show', ['card' => 10001]));
});

it('renders nothing when the file is not mirrored', function (): void {
    $rendered = Blade::render(
        '<x-support-thumb :support-id="10001" size-class="size-12" name="Special Week" />',
    );

    // Not an empty anchor and not an `<img>` with a dead src: the whole slot is absent, which is
    // also why the link is generated inside the guard rather than before it.
    expect(trim($rendered))->toBe('');
});
