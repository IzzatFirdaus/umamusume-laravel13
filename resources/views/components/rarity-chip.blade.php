@props(['rarity'])

{{--
    The glyph run is the readout, so this badge adds no colour role:
    DesignTokensTest pins the theme at exactly 60 tokens, and G-47 bans white on a
    light fill. Mood-pill carries an arrow for the same reason: hue alone cannot
    order three neighbours (G-6). role="img" with an aria-label turns a row of
    glyphs into a name instead of noise a screen reader has to interpret.

    No background fill: the row already sits on `bg-raised`, and a chip in its
    host's own fill reads as nothing.
--}}
<span {{ $attributes->merge(['class' => 'font-mono text-xs font-bold text-ink']) }}
    role="img" aria-label="{{ $rarity->label() }}"><span aria-hidden="true">{{ $rarity->stars() }}</span></span>
