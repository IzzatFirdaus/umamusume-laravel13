@props([
    // No default. A named default would put a scenario name in the view, which is
    // the D-240 smell the whole component exists to avoid, and a strip with no
    // scenario has nothing to compose from.
    'scenario',
    // Whether the Trainer actually named a scenario. `scenarioKey()` falls back to the
    // baseline so this component always has widgets to compose, and that fallback is a
    // composition device, not a fact about the run: captioning the turn count with the
    // baseline's label stated a scenario the run does not have (audit F-3, D-220).
    'declared' => true,
    /**
     * The run's own numbers, keyed by widget: turn, energy, fans, team_rank,
     * bursts, grade_points, shop_coins. A key a scenario does not own is never
     * read, so a caller can pass one flat array for every scenario.
     *
     * @var array<string, int|string>
     */
    'run' => [],
])

@php
    $config = config('scenarios');
    $def = $config['scenarios'][$scenario] ?? null;

    if ($def === null) {
        throw new InvalidArgumentException("Unknown scenario [{$scenario}] for x-resource-strip.");
    }

    /*
     * The widget list comes from config. A scenario that owns no team rank renders
     * no team rank box: absence, not an empty slot. This is the whole of D-220, and
     * it is why there is no `if ($scenario === 'unity_cup')` anywhere in the tree.
     */
    $widgets = $def['widgets'];

    /*
     * A caption is a claim about the run, so it is composed the same way. Trackblazer
     * has no race calendar and therefore no fan-gated event, and printing "next event
     * gate 60,000" there states a mechanic the scenario does not have — D-220's failure
     * in caption form. Read the panel flag, not the scenario name (D-240).
     */
    $fansCaption = ($def['panels']['grade_objectives'] ?? false)
        ? 'farmed by racing here'
        : 'next event gate 60,000';
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap gap-2']) }}>
    @foreach ($widgets as $widget)
        @php
            // Each branch produces presentation only. Adding a scenario never touches this loop.
            //
            // A value the run does not have renders as `N/A` with a caption saying so.
            // Defaulting instead would assert a fact about the trainee that nobody
            // entered — a Team Rank of "G" on a run with no team data says the run is in
            // the lowest rank, and "0/100" says the trainee is exhausted (D-220). The
            // widget itself is still present: the scenario owns Energy, so the box
            // appears; only the value is withheld. `N/A` rather than a dash because an
            // em dash is banned in shipped copy (R-02, D-79) and a bare glyph says
            // nothing to a screen reader anyway.
            $raw = $run[$widget === 'spirit_bursts' ? 'bursts' : $widget] ?? null;
            $recorded = $raw !== null && $raw !== '';

            [$label, $value, $sub] = match ($widget) {
                'turn' => ['Turn', number_format((int) ($run['turn'] ?? 0)), $declared ? $def['label'] : 'no scenario set'],
                'energy' => ['Energy', $recorded ? ((int) $raw).'/100' : 'N/A', $recorded ? null : 'not yet recorded'],
                'fans' => ['Fans', $recorded ? number_format((int) $raw) : 'N/A', $recorded ? $fansCaption : 'not yet recorded'],
                'team_rank' => ['Team Rank', $recorded ? (string) $raw : 'N/A', $recorded ? 'drives facility level' : 'not yet recorded'],
                'spirit_bursts' => ['Spirit Bursts', $recorded ? number_format((int) $raw) : 'N/A', $recorded ? 'normal plus Extreme' : 'not yet recorded'],
                'grade_points' => ['Grade Points', $recorded ? ((int) $raw).'/300' : 'N/A', $recorded ? 'surplus does not carry over' : 'not yet recorded'],
                'shop_coins' => ['Shop Coins', $recorded ? number_format((int) $raw) : 'N/A', $recorded ? 'rotation in 2 turns' : 'not yet recorded'],
                default => throw new InvalidArgumentException("Unknown widget [{$widget}] in scenario config."),
            };

            // "N/A" alone is ambiguous between "not applicable" and "not available", so
            // the tooltip names which. The caption already says it in words; this is the
            // hover-time half of the same fact.
            $valueHint = $recorded ? null : 'No value recorded for this run';

            $segments = $widget === 'energy' && $recorded
                ? max(0, min(5, (int) round((int) $raw / 20)))
                : null;
        @endphp

        {{-- The turn widget is the torn-page calendar card of research §6.6: a tab strip
             with two punch holes over a bordered body. It is the run's timeline anchor and,
             in a run list, the row's identity, so it is drawn as an object rather than as
             another number in a box. --}}
        @if ($widget === 'turn')
            <div class="relative min-w-36 flex-1 overflow-hidden rounded-md border-2 border-anchor bg-raised px-3 pt-6 pb-2">
                <span class="absolute inset-x-0 top-0 flex h-5 items-center gap-1.5 bg-anchor px-2" aria-hidden="true">
                    <span class="size-2 rounded-full bg-raised"></span>
                    <span class="size-2 rounded-full bg-raised"></span>
                </span>
                <span class="block font-mono text-xl leading-none font-extrabold tabular-nums text-anchor">{{ $value }}</span>
                <span class="mt-1 block text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ $label }}</span>
                @if ($sub !== null)
                    <span class="block text-xs text-ink-muted">{{ $sub }}</span>
                @endif
            </div>
        @else
            <div class="min-w-36 flex-1 rounded-md border border-rule bg-raised px-3 py-2">
                <span class="block text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ $label }}</span>
                <span class="block font-mono text-xl font-extrabold tabular-nums text-ink-strong"
                      @if ($valueHint !== null) title="{{ $valueHint }}" @endif>{{ $value }}</span>

                @if ($segments !== null)
                    <span class="mt-1 flex gap-1" role="img" aria-label="Energy {{ $run['energy'] ?? 0 }} of 100">
                        @for ($i = 0; $i < 5; $i++)
                            <span class="h-1.5 flex-1 rounded-sm {{ $i < $segments ? 'bg-green' : 'bg-idle' }}"></span>
                        @endfor
                    </span>
                @elseif ($sub !== null)
                    <span class="block text-xs text-ink-muted">{{ $sub }}</span>
                @endif
            </div>
        @endif
    @endforeach
</div>
