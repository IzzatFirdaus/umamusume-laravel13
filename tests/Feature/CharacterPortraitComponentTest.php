<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;

/*
 * `<x-character-portrait>` is the read half of `ADR-0021`. `DESIGN.md` §4.7 fixes what it owes a
 * Trainer when the mirror holds nothing: the row reflows to text only, so the component renders no
 * element at all rather than an empty frame, a grey box, or a placeholder. Absence is a normal
 * answer here, not an error state, and the link wrapper belongs or disappears with the image.
 *
 * The alt text is the client display name and nothing else (C-4 governs its vocabulary), except
 * where the same name is already printed beside the picture: there the image is decorative, takes
 * `alt=""`, and the anchor loses its label because the surrounding text is the label.
 *
 * No route stub: `routes/web.php` registers `catalog.show` and `artwork.show` (Task 2), a Feature
 * test boots the full app, and a stub here would collide with the name it restates.
 */

beforeEach(function (): void {
    Storage::fake('local');
});

it('renders an img when the file is mirrored', function (): void {
    Storage::disk('local')->put('artwork/characters/portrait/trainee/256/100101.png', 'PNG');

    $rendered = Blade::render(
        '<x-character-portrait :card-id="100101" size-class="size-12" name="Air Groove" :route-args="[\'slug\' => \'air-groove\']" />',
    );

    expect($rendered)
        ->toContain('<img')
        ->and($rendered)->toContain(route('artwork.show', ['kind' => 'card_portrait', 'id' => 100101]))
        ->and($rendered)->toContain('alt="Air Groove"')
        ->and($rendered)->toContain('aria-label="Air Groove"')
        ->and($rendered)->toContain(route('catalog.show', ['slug' => 'air-groove']));
});

it('renders nothing when the file is not mirrored', function (): void {
    $rendered = Blade::render(
        '<x-character-portrait :card-id="100101" size-class="size-12" name="Air Groove" :route-args="[\'slug\' => \'air-groove\']" />',
    );

    // Not an empty anchor and not an `<img>` with a dead src: the whole slot is absent, which is
    // also why the link is generated inside the guard rather than before it.
    expect(trim($rendered))->toBe('');
});

it('uses alt="" when decorative is set', function (): void {
    Storage::disk('local')->put('artwork/characters/portrait/trainee/256/100101.png', 'PNG');

    $rendered = Blade::render(
        '<x-character-portrait :card-id="100101" size-class="size-12" name="Air Groove" decorative :route-args="[\'slug\' => \'air-groove\']" />',
    );

    expect($rendered)
        ->toContain('alt=""')
        ->and($rendered)->toContain('aria-label=""')
        ->and($rendered)->not->toContain('alt="Air Groove"');
});
