@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
    'shortcut' => null,
])

@php
    /*
     * Full literal class strings, keyed rather than interpolated: Tailwind v4 scans sources
     * for complete class names, so "bg-{$variant}" compiles to nothing and the control renders
     * unstyled without an error. Every variant carries `min-h-11` (2.75rem = 44px), the control
     * size contract KI-29 / KI-37 measured against.
     */
    $variants = [
        'primary' => 'enamel min-h-11 rounded-full bg-chrome px-5 font-bold text-on-chrome',
        'secondary' => 'min-h-11 rounded-full border-2 border-rule px-4 font-bold text-ink-strong',
        'danger' => 'min-h-11 rounded-full bg-risk px-4 font-bold text-on-chrome',
        // Banner: rectangular with a 40px right wedge cap, so `pr-12` clears the cap.
        'banner' => 'enamel relative min-h-11 rounded-md bg-chrome pr-12 pl-4 font-bold text-on-chrome',
    ];

    $classes = $variants[$variant] ?? $variants['primary'];
    $wedge = $variant === 'banner';
@endphp

@if ($variant === 'discipline')
    {{-- Round Discipline button: 64px (size-16), a 4px green ring over a deeper 2px outer ring,
         and the 1..5 shortcut hint on the cap. --}}
    <button type="{{ $type }}"
        {{ $attributes->merge(['class' => 'relative grid size-16 place-items-center rounded-full border-4 border-green bg-raised font-bold text-ink-strong ring-2 ring-green-deep']) }}>
        {{ $slot }}
        @if ($shortcut !== null)
            <span class="absolute -right-1 -bottom-1 grid size-5 place-items-center rounded-full bg-chrome font-mono text-xs font-bold text-on-chrome"
                  aria-hidden="true">{{ $shortcut }}</span>
        @endif
    </button>
@elseif ($href !== null)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
        @if ($wedge)
            <span class="absolute inset-y-0 right-0 grid w-10 place-items-center rounded-r-md bg-green-deep text-on-chrome" aria-hidden="true">›</span>
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
        @if ($wedge)
            <span class="absolute inset-y-0 right-0 grid w-10 place-items-center rounded-r-md bg-green-deep text-on-chrome" aria-hidden="true">›</span>
        @endif
    </button>
@endif
