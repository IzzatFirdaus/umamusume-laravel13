@props(['grade'])

@php
    /*
     * Keyed literals, not interpolated: Tailwind scans sources for complete class names.
     * A `+` is a step within the base letter's colour, not a tenth colour, so it is stripped
     * before the lookup (KI-8).
     */
    $fills = [
        'G' => 'bg-grade-g', 'F' => 'bg-grade-f', 'E' => 'bg-grade-e', 'D' => 'bg-grade-d',
        'C' => 'bg-grade-c', 'B' => 'bg-grade-b', 'A' => 'bg-grade-a', 'S' => 'bg-grade-s',
        'SS' => 'bg-grade-ss',
    ];

    $fill = $fills[rtrim((string) $grade, '+-')] ?? 'bg-grade-g';
@endphp

{{-- 22px (size-5.5) square carrying the ink-strong letter: white on these fills measures
     1.20-1.44 in the light theme and is banned (D-258, G-47). --}}
<span {{ $attributes->merge(['class' => 'inline-grid size-5.5 place-items-center rounded-md border border-rule text-xs font-bold text-ink-strong '.$fill]) }}>{{ $grade }}</span>
