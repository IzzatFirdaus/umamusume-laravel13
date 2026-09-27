@props([
    'scenario' => 'ura_finale',
    'cells' => [],
    'monthLabels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
])

@php
    $def = config('scenarios.scenarios.'.$scenario);

    if ($def === null) {
        throw new InvalidArgumentException("Unknown scenario [{$scenario}] for x-race-calendar.");
    }

    /*
     * Self-gating on config, not on a scenario name. Trackblazer and Our Grand
     * Concert have no mandatory race goals, so this renders nothing at all rather
     * than an empty grid: an empty calendar would still claim the scenario has one
     * (D-221, gate G-34).
     */
    if ($def['panels']['race_calendar'] !== true) {
        return;
    }

    /*
     * Cell states. Full literal class strings so Tailwind's scanner sees them.
     * The fan lock and the maiden lock are deliberately different treatments:
     * one is a number you can work toward, the other is a rule about the trainee
     * (D-152, gate G-28).
     */
    $stateClass = [
        'empty' => 'border-rule bg-sunken text-ink-faint',
        'open' => 'border-dashed border-green-line bg-raised text-ink',
        'goal' => 'border-green bg-green-tint text-ink-strong',
        'fan_locked' => 'border-rule bg-sunken text-ink-muted',
        'maiden_locked' => 'border-rule bg-panel text-ink-muted',
        'past' => 'border-rule bg-transparent text-ink-faint',
        'current' => 'border-pick bg-raised text-ink-strong',
    ];

    $stateWord = [
        'empty' => 'No race',
        'open' => 'Entry open',
        'goal' => 'Mandatory goal',
        'fan_locked' => 'Fan gate',
        'maiden_locked' => 'Maiden rule',
        'past' => 'Run',
        'current' => 'Next',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-md border border-rule bg-panel p-3']) }}>
    <h3 class="mb-2 text-xs font-bold uppercase tracking-widest text-ink-muted">Race calendar</h3>

    <div class="grid grid-cols-12 gap-1">
        @foreach ($monthLabels as $index => $month)
            <div class="col-span-1 text-center text-xs font-semibold text-ink-muted">{{ $month }}</div>
        @endforeach

        @foreach (['Early', 'Late'] as $half)
            @foreach ($monthLabels as $monthIndex => $month)
                @php
                    $cell = $cells[$monthIndex]['halves'][$half] ?? [];
                    $state = $cell['state'] ?? 'empty';
                    $label = $cell['label'] ?? null;
                    $fans = $cell['fans_needed'] ?? null;
                    $state = array_key_exists($state, $stateClass) ? $state : 'empty';
                @endphp
                <div class="col-span-1 rounded-md border px-1 py-1.5 text-center text-xs leading-tight
                            {{ $stateClass[$state] }}">
                    <span class="block font-mono text-xs tabular-nums text-ink-faint">{{ $half }}</span>
                    <span class="block truncate font-semibold" title="{{ $label ?? $stateWord[$state] }}">
                        {{ $label ?? $stateWord[$state] }}
                    </span>
                    @if ($fans !== null)
                        <span class="block font-mono text-xs tabular-nums text-ink-muted">
                            {{ number_format((int) $fans) }} fans
                        </span>
                    @endif
                </div>
            @endforeach
        @endforeach
    </div>

    <p class="mt-2 text-xs text-ink-muted">
        Twenty-four turn slots, Early and Late for each month. A fan gate shows the number it needs;
        a maiden rule is a different lock, because one is progress and the other is eligibility.
    </p>
</div>
