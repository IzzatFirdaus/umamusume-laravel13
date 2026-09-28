@props([
    // No default, for the same reason as x-guided-step: a named default puts a
    // scenario in the view, and a panel for "no scenario chosen" is not a panel.
    'scenario',
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
     *
     * The three that must never be confused are goal, fan lock and maiden lock,
     * and D-173 makes their distinctness a review failure rather than a taste call:
     *
     *   goal          2px warm outline, raised fill, red `Goal` pennant, taller than
     *                 its neighbours, and never dimmer than them
     *   fan_locked    2px SOLID, sunken fill, and the number it needs
     *   maiden_locked 2px DASHED, raised fill, and its own sentence
     *
     * The maiden gate is the reason the two locks are not one treatment. Its
     * remedy is an event, not a quantity, so there is no number to show and a
     * solid border beside the fan lock's solid border would be telling the Trainer
     * to grind toward 0 fans.
     */
    $stateClass = [
        'empty' => 'border border-rule bg-sunken text-ink-muted py-1.5',
        'open' => 'border border-dashed border-green-line bg-raised text-ink py-1.5',
        // D-181: a `Goal` pennant, a heavier warm outline and greater height. The
        // padding is the height, and it only reads as height because the grid
        // aligns cells to the top instead of stretching the row to its tallest.
        // Not green: this cell does not mean "affordable", and not risk red either,
        // which would call an obligation an error.
        'goal' => 'border-2 border-goal-line bg-raised text-ink-strong py-3',
        'fan_locked' => 'border-2 border-solid border-rule bg-sunken text-ink-muted py-1.5',
        'maiden_locked' => 'border-2 border-dashed border-ink-muted bg-raised text-ink-muted py-1.5',
        'past' => 'border border-rule bg-transparent text-ink-muted py-1.5',
        'current' => 'border-2 border-pick-line bg-raised text-ink-strong py-1.5',
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

    // The caption counts in digits because a Trainer counts the turns against it.
    $slotCount = count($monthLabels) * 2;
@endphp

<div {{ $attributes->merge(['class' => 'rounded-md border border-rule bg-panel p-3']) }}>
    {{-- Capsule header with argyle lattice bleed: the client's most repeated element,
         measured across frames 234521, 230755 and 232345. It marks this as a section
         header rather than as data. The fill is --color-chrome, not the client's bright
         lime, because a capsule always carries a word and white on bright lime measures
         1.99:1 (DESIGN.md §2.3 amendment, research §6.3, D-3). --}}
    <div class="lattice-bleed mb-3 flex h-11 items-center rounded-full bg-chrome pl-16 pr-4 text-sm font-bold text-on-chrome">
        <span>Race calendar</span>
    </div>

    @if ($cells === [])
        {{-- A bare "no data" line tells the Trainer nothing. The scenario owns this
             calendar either way, so the structure stays; what is missing is their own
             entries, and the panel has to say which and what to do about it. --}}
        <p class="mb-3 rounded-md border border-dashed border-rule bg-raised px-3 py-2 text-sm text-ink">
            No races entered for this run yet. The grid is the {{ $slotCount }} turn slots this
            scenario runs on, so it renders even when empty; add a race on a turn to fill it.
        </p>
    @endif

    {{-- 24 cells across 12 columns does not fit a phone, and shrinking each cell
         until it fits would make the lock treatments unreadable, which is the one
         thing this grid exists to communicate. So the grid keeps its width and the
         region scrolls, focusable for keyboard users. --}}
    <div class="overflow-x-auto" role="region" aria-label="Race calendar, {{ $slotCount }} turn slots" tabindex="0">
        {{-- 56rem on the spacing scale (224 × 0.25rem), not an arbitrary value: G-4 keeps
             geometry on the scale so it moves with the token system. --}}
        <div class="grid min-w-224 grid-cols-12 items-start gap-1">
            @foreach ($monthLabels as $index => $month)
                <div class="col-span-1 text-center text-xs font-semibold text-ink-muted">{{ $month }}</div>
            @endforeach

            @foreach (['Early', 'Late'] as $half)
                @foreach ($monthLabels as $monthIndex => $month)
                    @php
                        $cell = $cells[$monthIndex]['halves'][$half] ?? [];
                        $slotItems = $cell['slots'] ?? [];
                        // Derive a single state for the cell's border treatment from
                        // its slots: goal wins, then fan_locked, then maiden_locked,
                        // then open, then past, then empty. Multiple slots share one
                        // border; their labels stack inside.
                        $state = 'empty';
                        $priority = ['goal' => 6, 'current' => 5, 'fan_locked' => 4, 'maiden_locked' => 3, 'open' => 2, 'past' => 1];
                        foreach ($slotItems as $s) {
                            $p = $priority[$s['state'] ?? ''] ?? 0;
                            if ($p > ($priority[$state] ?? 0)) {
                                $state = $s['state'];
                            }
                        }
                        $state = array_key_exists($state, $stateClass) ? $state : 'empty';
                        $firstLabel = $slotItems[0]['label'] ?? null;
                        $fans = $state === 'fan_locked' ? ($slotItems[0]['fans_needed'] ?? null) : null;
                        $ariaSlots = count($slotItems);
                        $ariaText = $ariaSlots > 1
                            ? "{$ariaSlots} races: " . implode(', ', array_map(fn ($s) => $s['label'] ?? '', $slotItems))
                            : ($firstLabel ?? $stateWord[$state]);
                    @endphp
                    {{-- The accessible name carries the month, the half and the state,
                         because a cell's state lives in its outline and its pennant and
                         the race name alone would not say whether it is open. D-181 keeps
                         the *visual* signal, and this is the non-visual half of the same
                         fact rather than a sentence on the screen. `role="img"` is what
                         makes it a name at all: a label on a bare div is a property most
                         technologies do not expose. --}}
                    <div class="col-span-1 rounded-md border px-1 text-center text-xs leading-tight
                                {{ $stateClass[$state] }} relative"
                         role="img"
                         aria-label="{{ $month }} {{ $half }}: {{ $stateWord[$state] }}, {{ $ariaText }}">
                        @if ($state === 'goal')
                            {{-- D-181: "A goal race announces itself with a Goal pennant, a
                                 heavier warm outline and greater height." The client's own
                                 red Goal flag, in the top-right corner. Only the filled
                                 edge carries colour: `border-<color>` on its own sets all
                                 four sides, and then whether the dead edges stay
                                 transparent is Tailwind's sheet order rather than a
                                 decision here. No caption and no tooltip: the treatment is
                                 the message. --}}
                            <span class="absolute top-0 right-0 h-0 w-0 border-t-3 border-b-3 border-l-5 border-t-transparent border-b-transparent border-l-goal"
                                  aria-hidden="true"></span>
                        @endif
                        <span class="block font-mono text-xs tabular-nums text-ink-muted">{{ $half }}</span>
                        @if ($ariaSlots > 1)
                            <ul class="space-y-0.5">
                                @foreach ($slotItems as $slotItem)
                                    <li class="truncate font-semibold" title="{{ $slotItem['label'] ?? '' }}">
                                        {{ $slotItem['label'] ?? $stateWord[$state] }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <span class="block truncate font-semibold" title="{{ $firstLabel ?? $stateWord[$state] }}">
                                {{ $firstLabel ?? $stateWord[$state] }}
                            </span>
                        @endif
                        @if ($fans !== null)
                            <span class="block font-mono text-xs tabular-nums text-ink-muted">
                                {{ number_format((int) $fans) }} fans
                            </span>
                        @endif
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>

    <p class="mt-2 text-xs text-ink-muted">
        {{ $slotCount }} turn slots, Early and Late for each month. A fan gate shows the number it
        needs; a maiden rule is a different lock and a dashed outline, because one is a quantity to
        work toward and the other is an event to reach.
    </p>
</div>
