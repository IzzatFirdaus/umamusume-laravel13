@props([
    // No default. A named default put a scenario name in a view, which is the D-240 smell
    // x-resource-strip already refuses: a band with no scenario has no cap set to read, and
    // silently resolving to the baseline would rate a trainee against the wrong ceiling.
    'scenario',
    'values' => [],
    'skillPoints' => null,
])

@php
    $config = config('scenarios');
    $def = $config['scenarios'][$scenario] ?? null;

    if ($def === null) {
        throw new InvalidArgumentException("Unknown scenario [{$scenario}] for x-stat-band.");
    }

    $base = $config['base_cap'];
    $order = $config['stat_order'];

    /*
     * Full literal class strings, keyed rather than interpolated. Tailwind v4
     * scans sources for complete class names, so "bg-tint-{$stat}" would compile
     * to nothing at all and the band would render unstyled without an error.
     */
    $tintClass = [
        'Speed' => 'bg-tint-speed border-line-speed',
        'Stamina' => 'bg-tint-stamina border-line-stamina',
        'Power' => 'bg-tint-power border-line-power',
        'Guts' => 'bg-tint-guts border-line-guts',
        'Wit' => 'bg-tint-wit border-line-wit',
    ];

    $glyphPath = [
        'Speed' => 'M2 13h11l1 2H1v-2zm1-3 3-4 3 3 3-2 1 3H3z',
        'Stamina' => 'M8 15S2 11 2 6.6A3.6 3.6 0 0 1 8 4a3.6 3.6 0 0 1 6 2.6C14 11 8 15 8 15z',
        'Power' => 'M1 5h2v6H1zm3 1h2v4H4zm8-1h2v6h-2zM10 6h2v4h-2zM6 7h4v2H6z',
        'Guts' => 'M8 1c2 3 5 4 5 8a5 5 0 0 1-10 0c0-2 1-3 2-4 0 2 1 3 2 3 1-2-1-4-1-7z',
        'Wit' => 'M8 2 15 6l-7 4-7-4 7-4zm-5 7v3c0 1.7 10 1.7 10 0V9L8 11 3 9z',
    ];

    $gradeClass = [
        'G' => 'bg-grade-g', 'F' => 'bg-grade-f', 'E' => 'bg-grade-e', 'D' => 'bg-grade-d',
        'C' => 'bg-grade-c', 'B' => 'bg-grade-b', 'A' => 'bg-grade-a', 'S' => 'bg-grade-s',
        'SS' => 'bg-grade-ss',
    ];

    /*
     * Grade is derived from the entered value, never stored. The banding is ours:
     * no source in this repository defines a client stat grade, so the boundaries
     * are printed below the band and labelled provisional (D-256, G-46).
     *
     * Provenance of the letter set and the tint families: visual observation of
     * client frames, NOT client text. RAW-FINDINGS §5 records that the badge hexes
     * came from block averages over ~28px targets and are weaker evidence than the
     * point probes used elsewhere, and no exported string names a grade threshold.
     * So both the boundaries and the colours are read off pixels.
     *
     * How to resolve it: high-resolution screenshots of grade badges spanning all
     * nine letters, sampled at the letter fill rather than the white interior, plus
     * one capture where a stat is nudged across a boundary. That turns this comment
     * into a sourced table and retires the [Provisional] marker.
     */
    $band = $config['grade_banding'];
    $gradeOf = function (int $value) use ($band): string {
        $index = min(count($band['labels']) - 1, intdiv(max(0, $value), $band['step']));

        return $band['labels'][$index];
    };
@endphp

{#
    One band, six columns, identical geometry in every scenario and every theme.
    Two markers, two meanings: the dashed tick is the 1200 halved-gains line, the
    bar end is the scenario ceiling. Where a ceiling equals 1200 the two coincide,
    and the bar end itself takes the dashes so the coincidence stays visible instead
    of being clipped away by the track's overflow (ADR-0002, D-211, D-212).
#}
<div {{ $attributes->merge(['class' => 'rounded-md border border-rule bg-raised']) }}>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6">
        @foreach ($order as $stat)
            @php
                $value = (int) ($values[$stat] ?? 0);
                $cap = $base + (int) $def['cap_bonus'][$stat];
                $pct = min(100, $cap > 0 ? $value / $cap * 100 : 0);
                $softPct = min(100, $cap > 0 ? $base / $cap * 100 : 100);
                $atCeiling = $cap <= $base;
                $grade = $gradeOf($value);
                // R13: the fill is keyed on the base letter with the modifier
                // stripped, the badge prints the full letter. The banding emits
                // seventeen labels including half-steps like `B+`, and the nine tint
                // families are the nine letters — a `+` is a step within B's colour,
                // not a tenth colour, and inventing one would be reading a client
                // badge that no capture shows (KI-8).
                $gradeFill = $gradeClass[rtrim($grade, '+-')];
            @endphp
            <div class="border-b border-r border-rule p-3 last:border-r-0">
                <div class="-mx-3 -mt-3 mb-2 flex items-center gap-1.5 border-b px-3 py-1.5
                            text-xs font-semibold uppercase tracking-wide text-ink
                            {{ $tintClass[$stat] }}">
                    <svg class="size-3.5 shrink-0 fill-current" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="{{ $glyphPath[$stat] }}" />
                    </svg>
                    <span>{{ $stat }}</span>
                </div>

                <div class="flex items-end gap-2">
                    <span class="inline-grid size-5 place-items-center rounded border border-rule
                                 text-xs font-bold text-ink-strong {{ $gradeFill }}"
                          title="Derived from the entered value, not read from the client">
                        {{ $grade }}
                    </span>
                    <span class="font-mono text-2xl leading-none font-extrabold tabular-nums text-ink-strong">
                        {{ number_format($value) }}
                    </span>
                </div>

                <div class="mt-0.5 font-mono text-xs tabular-nums text-ink-muted">
                    / {{ number_format($cap) }}
                </div>

                <div class="relative mt-2 h-1.5 overflow-hidden rounded bg-sunken
                            {{ $atCeiling ? 'border-r-2 border-dashed border-r-ink-faint rounded-l' : '' }}">
                    <div class="absolute inset-y-0 left-0 bg-green-deep" style="width: {{ round($pct, 2) }}%"></div>
                    @if ($value > $base)
                        <div class="absolute inset-y-0 bg-up opacity-50"
                             style="left: {{ round($softPct, 2) }}%; width: {{ round(min(100 - $softPct, ($value - $base) / $cap * 100), 2) }}%"></div>
                    @endif
                    @unless ($atCeiling)
                        <div class="absolute -inset-y-1 w-0 border-l-2 border-dashed border-ink-faint"
                             style="left: {{ round($softPct, 2) }}%"></div>
                    @endunless
                </div>
            </div>
        @endforeach

        <div class="border-b border-rule p-3">
            <div class="-mx-3 -mt-3 mb-2 flex items-center gap-1.5 border-b border-line-sp px-3 py-1.5
                        text-xs font-semibold uppercase tracking-wide text-ink bg-tint-sp">
                <svg class="size-3.5 shrink-0 fill-current" viewBox="0 0 16 16" aria-hidden="true">
                    <path d="M3 2h9a1 1 0 0 1 1 1v10H4a1 1 0 0 1-1-1V2zm2 1v8h7V3H5z" />
                </svg>
                <span>Skill Points</span>
            </div>
            <div class="font-mono text-2xl leading-none font-extrabold tabular-nums text-ink-strong">
                {{ $skillPoints === null ? '0' : number_format((int) $skillPoints) }}
            </div>
            <div class="mt-0.5 text-xs text-ink-muted">no cap, no grade</div>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 px-3 py-2 text-xs text-ink-muted">
        <span class="inline-block h-3 w-0 border-l-2 border-dashed border-ink-faint" aria-hidden="true"></span>
        <span>
            <span class="font-semibold text-ink-strong">1,200</span> is where training gains halve.
            The bar end is the scenario ceiling. Where the two coincide the bar end is dashed.
        </span>
    </div>

    <div class="px-3 pb-3 font-mono text-xs tabular-nums text-ink-muted">
        {{ $base }} base
        + @foreach ($order as $stat){{ $stat }} +{{ $def['cap_bonus'][$stat] }}@if (! $loop->last), @endif @endforeach
        breakthrough not tracked + deck untracked.
        Hard cap {{ number_format($config['hard_cap']) }}.
    </div>

    <div class="border-t border-rule px-3 py-2">
        <p class="text-xs text-ink-muted">
            <span class="rounded border border-dashed border-down px-1 font-semibold text-down">Provisional</span>
            Our grade scale is provisional; validated below 450 only.
            No source in this repository defines a client stat grade, so these letters are ours.
        </p>
    </div>
</div>
