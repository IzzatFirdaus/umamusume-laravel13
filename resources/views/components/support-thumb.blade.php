@props([
    'supportId' => 0,
    'sizeClass' => 'size-12',
    'name' => '',
    'decorative' => false,
    'routeArgs' => [],
])

{{--
    The read half of `ADR-0021`, behaving per `DESIGN.md` §4.7. A frame appears only when the mirror
    holds a file for this support id; when it does not, this component renders no element at all, so
    the host row reflows to text. No grey box, no loader, no placeholder glyph: the mirror is partial
    by nature and a broken visual would claim a defect the tool does not have. The `src` is the
    loopback `artwork.show` route from `ArtworkMirror::url()`, never the asset host (§7: no CDN,
    offline first).

    The alt is the client display name and nothing else. C-4 bounds its vocabulary and the tool cannot
    see inside the file it would describe, so there is no invented descriptor. Where the host already
    prints that name beside the slot the caller passes `decorative`, and then the alt and the anchor
    label both go empty so a screen reader reads the name once, from the text that is already there.

    Deliberate omissions, both of them ceilings with a named upgrade path: no `$attributes` spread,
    because every caller so far sizes the slot through `size-class` alone and a stray class on the
    anchor would be a second style owner; and a fixed `width`/`height` pair that carries the square's
    intrinsic ratio while `size-class` sets the painted box. §4.7 reserves the right to record one
    geometry in `DESIGN.md`, and Task 10 is where a pixel value would be written down.
--}}

@php
    $url = app(\App\Services\DataPipeline\ArtworkMirror::class)->url('support_thumb', (int) $supportId);
@endphp

@if ($url !== null)
    <a href="{{ route('support-cards.show', $routeArgs) }}"
       aria-label="{{ $decorative ? '' : $name }}"
       class="block {{ $sizeClass }}">
        <img src="{{ $url }}"
             alt="{{ $decorative ? '' : $name }}"
             class="{{ $sizeClass }} object-cover rounded-md"
             width="64" height="64">
    </a>
@endif
