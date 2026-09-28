@props([
    'tier' => null,
    'unrecorded' => 'Mood not recorded',
])

@php
    /*
     * Tailwind scans sources for complete class names, so the five fills are written out
     * as literals rather than built from the tier value; a concatenated `bg-mood-{...}`
     * compiles to nothing and the pill renders unstyled. Same reason the stat band lists
     * its tints by hand.
     */
    $fills = [
        'GREAT' => 'bg-mood-great',
        'GOOD' => 'bg-mood-good',
        'NORMAL' => 'bg-mood-normal',
        'BAD' => 'bg-mood-bad',
        'AWFUL' => 'bg-mood-awful',
    ];
@endphp

@if ($tier instanceof \App\Enums\MoodTier)
    {{--
        Word, then the arrow D-259 makes mandatory. The glyph is the only ordinal signal
        in the component: three of these five fills were derived to the same luminance as
        their neighbours, so hue cannot order them and colour alone would be a readout
        that reads as decoration. The ink is `--color-on-mood`, stepped from the theme's
        own dark ink because #482720 on this fill is 4.29:1 (DESIGN.md §6.17, slice-6 §2).
    --}}
    <span
        {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-1.5 py-0.5 font-mono text-xs font-bold uppercase tracking-wide text-on-mood '.$fills[$tier->value]]) }}
    >{{ $tier->value }}<span aria-hidden="true">{{ $tier->arrow() }}</span></span>
@else
    <span {{ $attributes->merge(['class' => 'text-xs text-ink-muted']) }}>{{ $unrecorded }}</span>
@endif
