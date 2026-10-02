@props(['energy' => null])

@php
    /*
     * Ten segments across a hue sweep: cyan -> green -> lime -> amber -> risk.
     *
     * CONFLICT, flagged not resolved. The sweep makes green read as a "good" level and
     * `--color-risk` read as a "low" level, which reverses two settled rules: green is the
     * action/affordance colour and never "good" (DESIGN.md §2.1), and `--color-risk` is
     * reserved for training failure and rejection. The shipped energy readout (x-resource-strip,
     * x-guided-step) is five green/idle cells for exactly that reason. This component is the
     * brief's shape; the semantic rule it breaks is recorded, not repaired.
     */
    $sweep = [
        'bg-sp', 'bg-sp', 'bg-green', 'bg-green', 'bg-lattice',
        'bg-lattice', 'bg-pick', 'bg-pick', 'bg-risk', 'bg-risk',
    ];

    $recorded = $energy !== null;
    $filled = $recorded ? max(0, min(10, (int) ceil((int) $energy / 10))) : 0;

    $state = match (true) {
        ! $recorded => null,
        (int) $energy > 50 => 'Safe',
        (int) $energy >= 30 => 'Caution',
        default => 'Danger',
    };

    $stateClass = match ($state) {
        'Danger' => 'bg-risk text-on-chrome',
        'Caution' => 'bg-pick text-on-pick',
        default => 'bg-green-tint text-ink',
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col gap-1']) }}>
    @if (! $recorded)
        {{-- Absent, not zero: an empty gauge would say the trainee is exhausted on a run that
             has not logged a turn (D-220). --}}
        <span class="font-mono text-sm text-ink-muted" title="No Energy value recorded for this run">Energy N/A</span>
    @else
        <div class="relative flex h-2 gap-0.5" role="img" aria-label="Energy {{ (int) $energy }} of 100">
            @foreach ($sweep as $i => $fill)
                <span class="flex-1 rounded-sm {{ $i < $filled ? $fill : 'bg-idle' }}"></span>
            @endforeach
            {{-- 50 is the only sourced Energy threshold (D-204). --}}
            <span class="absolute -inset-y-1 border-l-2 border-risk" style="left: 50%" aria-hidden="true"></span>
        </div>
        <span class="flex items-center gap-2 text-xs">
            <span class="font-mono tabular-nums text-ink-muted">{{ (int) $energy }}/100</span>
            <span class="rounded border border-transparent px-2 py-0.5 font-bold {{ $stateClass }}">{{ $state }}</span>
        </span>
    @endif
</div>
