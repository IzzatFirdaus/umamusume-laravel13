@props([
    'turn' => null,
    'scenario' => null,
    'energy' => null,
    'fans' => null,
    'fanGate' => null,
])

@php
    /*
     * The persistent top strip: turn anchor, active scenario, Energy, and the fan shortfall.
     * `scenario` is the label only, never a ceiling source (KI-47). The Energy gauge sits here
     * and again beside the control that spends it (D-171, G-30). A value the run has not
     * recorded renders as N/A with a title, never as zero (D-220).
     */
    $shortfall = ($fans !== null && $fanGate !== null) ? max(0, (int) $fanGate - (int) $fans) : null;
@endphp

<div {{ $attributes->merge(['class' => 'sticky top-0 z-10 flex flex-wrap items-center gap-3 rounded-md border border-rule bg-panel px-3 py-2']) }}>
    <span class="rounded-md border-2 border-anchor bg-raised px-2 py-1 font-mono text-sm font-extrabold tabular-nums text-anchor">
        Turn {{ $turn === null ? 'N/A' : (int) $turn }}
    </span>

    @if ($scenario !== null)
        <span class="text-sm font-semibold text-ink-strong">{{ $scenario }}</span>
    @endif

    <x-energy-gauge :energy="$energy" class="min-w-40 flex-1" />

    <span class="font-mono text-xs tabular-nums text-ink-muted">
        @if ($shortfall === null)
            <span title="No fan count recorded for this run">Fans N/A</span>
        @else
            Fans {{ number_format((int) $fans) }} · short {{ number_format($shortfall) }}
        @endif
    </span>
</div>
