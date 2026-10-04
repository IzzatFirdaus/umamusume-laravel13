@props([
    // No default, for the same reason as x-guided-step: a named default puts a
    // scenario in the view, and a panel for "no scenario chosen" is not a panel.
    'scenario',
    'cells' => [],
    // Which of the three career years these 24 cells belong to. Null keeps the
    // panel as it was before the grid had a year to show: no tabs, no highlight.
    'year' => null,
    // The turn the Trainer is deciding about, as its position within `year`, 1-24.
    // Null when nothing has been logged or when the career has no turn left to take;
    // the grid then shows no outline rather than falling back to the last turn played.
    'nextTurn' => null,
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
     *
     * Two of these seven cannot be reached from data today, and both are kept on
     * purpose rather than deleted on the way past:
     *
     *   goal          79ffad5 withdrew `is_mandatory` as a rendering input, because the
     *                 flag marks a career obligation while the client's banner marks a
     *                 per-character objective. The pennant stays wired to the state so
     *                 that `trainee_goals` has a treatment to emit into.
     *   maiden_locked GametoraRaceCatalogParser writes `is_maiden_gated` false on every
     *                 row, so no fetched race can raise this lock.
     *
     * Three client marks are absent by rule rather than by omission: the race artwork
     * thumbnail (PRD §6.13 keeps images out of this tool), the padlock glyph on a fan
     * gate (there is no icon set, and the figure is what the Trainer acts on), and the
     * 55% dim §6.20 lists for both locks, which would take the fan figure below the
     * contrast floor that is the reason for printing it.
     */
    $stateClass = [
        // The client's empty half-month is a grey box with a muted plus and no words.
        // The fill is the measured disabled cell #D0D1D0 (DESIGN.md §6.20); the words
        // moved into the accessible name, where a screen reader still hears "No race".
        'empty' => 'border border-rule bg-disabled text-ink-muted py-1.5',
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
        // Pale yellow with a warm outline, which is this tool's own pick pair: the same
        // fill and ink the selected year tab and a held spirit burst already use.
        'current' => 'border-2 border-pick-line bg-pick text-on-pick py-1.5',
    ];

    $stateWord = [
        'empty' => 'No race',
        'open' => 'Entry open',
        'goal' => 'Mandatory goal',
        'fan_locked' => 'Fan gate',
        'maiden_locked' => 'Maiden rule',
        // The client's word for a slot a race has been put on is `Scheduled`, and the
        // pink pill under the name says exactly that, so the accessible name says it
        // too. The model state stays `past`: that is the fact about the entry, not the
        // label the Trainer reads.
        'past' => 'Scheduled',
        'current' => 'Next',
    ];

    // The caption counts in digits because a Trainer counts the turns against it.
    $slotCount = count($monthLabels) * 2;
@endphp

<div {{ $attributes->merge(['class' => 'rounded-md border border-rule bg-panel p-3']) }}>
    {{-- Capsule header with argyle lattice bleed: the client's most repeated element,
         measured across frames 234521, 230755 and 232345. It marks this as a section
         header rather than as data. x-capsule-header is the one owner of that anatomy
         (chrome fill rather than the client's bright lime, because a capsule always
         carries a word and white on bright lime measures 1.99:1: DESIGN.md §2.3
         amendment, research §6.3, D-3). Eight panels used to hand-copy this div. --}}
    <x-capsule-header title="Race calendar" class="mb-3" />

    @if ($year !== null)
        {{-- The client's own three tabs, server-rendered as links. There is no
             runtime JavaScript dependency in this project, and a career year is
             part of the screen's address rather than a transient UI state: a
             Trainer should be able to hand someone "look at her Classic spring"
             as a URL, and back should not lose the tab. --}}
        <div class="mb-3 flex gap-1" role="tablist" aria-label="Career year">
            @foreach (\App\Models\RaceCatalogSlot::YEARS as $value => $label)
                @continue($value === \App\Models\RaceCatalogSlot::YEAR_FINALE)
                <a role="tab"
                   aria-selected="{{ $value === $year ? 'true' : 'false' }}"
                   href="{{ request()->fullUrlWithQuery(['year' => $value]) }}"
                   class="rounded-full px-3 py-1 text-xs font-semibold {{ $value === $year
                        ? 'bg-pick text-on-pick'
                        : 'border border-rule bg-raised text-ink hover:bg-sunken' }}">
                    {{ $label }} Year
                </a>
            @endforeach
        </div>
    @endif

    @if ($cells === [])
        {{-- A bare "no data" line tells the Trainer nothing. The scenario owns this
             calendar either way, so the structure stays; what is missing is their own
             entries, and the panel has to say which and what to do about it. --}}
        <p class="mb-3 rounded-md border border-dashed border-rule bg-raised px-3 py-2 text-sm text-ink">
            No races entered for this run yet. The grid is the {{ $slotCount }} turn slots this
            scenario runs on, so it renders even when empty; add a race on a turn to fill it.
        </p>
    @endif

    {{-- Four cells across, six rows, one row per month pair: the client's own shape, measured off
         docs/game-screenshots/Screenshot 2026-07-17 230755.png. The build was the same 24 slots
         transposed into twelve columns and two rows, which needed a 56rem band and a horizontal
         scroll to hold them. This tool is desktop-only (`docs/UX Behavior Specification - Umamusume
         Trainer Companion.md` line 18) and four columns do not overflow at the 768px floor, so the
         band went — and with it the `tabindex="0"` that existed to make a clipped region reachable
         by keyboard (KI-25). A focus stop that scrolls nothing spends a keypress on nothing. --}}
    <div class="grid grid-cols-4 items-start gap-x-3 gap-y-4" role="region" aria-label="Race calendar, {{ $year !== null ? \App\Models\RaceCatalogSlot::YEARS[$year].' year, ' : '' }}{{ $slotCount }} turn slots">
        {{-- Reading down a column walks the year: a row is the Early and Late halves of two
             consecutive months, so the DOM order is still the turn order. --}}
        @foreach (array_chunk(range(0, 23), 4) as $row)
            @foreach ($row as $slotIndex)
                @php
                    $monthIndex = intdiv($slotIndex, 2);
                    $half = $slotIndex % 2 === 0 ? 'Early' : 'Late';
                    $month = $monthLabels[$monthIndex];
                    $cell = $cells[$monthIndex]['halves'][$half] ?? [];
                    $slotItems = $cell['slots'] ?? [];
                    // Derive a single state for the cell's border treatment from
                    // its slots: goal wins, then fan_locked, then maiden_locked,
                    // then open, then past, then empty. Multiple slots share one
                    // border; their labels stack inside.
                    $state = 'empty';
                    $priority = ['goal' => 6, 'current' => 5, 'fan_locked' => 4, 'maiden_locked' => 3, 'open' => 2, 'past' => 1];
                    // The row order above is the turn order, so the slot index is the
                    // turn with one added: Junior Early January is turn 1.
                    $cellTurn = $slotIndex + 1;
                    $isNext = $nextTurn !== null && $nextTurn === $cellTurn;
                    // The peer's priority map already ranked `current` between
                    // goal and fan_locked; nothing ever fed it, so the state was
                    // defined and unreachable. The turn being decided is a property
                    // of the cell, not of any race in it, so it enters as one more
                    // candidate for the same loop rather than as a slot. The state
                    // key stays `current` -- it names the cell the Trainer stands on
                    // -- while the prop and the spoken words name the turn to play.
                    $candidates = array_map(fn (array $s): string => $s['state'] ?? '', $slotItems);

                    if ($isNext) {
                        $candidates[] = 'current';
                    }

                    foreach ($candidates as $candidate) {
                        if (($priority[$candidate] ?? 0) > ($priority[$state] ?? 0)) {
                            $state = $candidate;
                        }
                    }
                    $state = array_key_exists($state, $stateClass) ? $state : 'empty';
                    $firstLabel = $slotItems[0]['label'] ?? null;
                    $fans = $state === 'fan_locked' ? ($slotItems[0]['fans_needed'] ?? null) : null;
                    // Whether any race in this half-month has been put here by this run. It is
                    // not the same question as the cell's border state: a cell holding one
                    // entered race and one open one draws the open treatment, because that is
                    // the stronger claim on the border, while the entered race is still
                    // entered. The Trainer-entered marker below has the same shape.
                    $entered = collect($slotItems)->contains(fn (array $s): bool => ($s['state'] ?? '') === 'past');
                    $ariaSlots = count($slotItems);
                    $ariaText = $ariaSlots > 1
                        ? "{$ariaSlots} races: " . implode(', ', array_map(fn ($s) => $s['label'] ?? '', $slotItems))
                        : ($firstLabel ?? $stateWord[$state]);

                    if ($entered && $state !== 'past') {
                        $ariaText .= '; one entered';
                    }

                    if ($isNext) {
                        $ariaText .= '; next turn to play';
                    }
                @endphp
                {{-- The caption sits under the box, as in the client capture, because the box's
                     tint is the state and the label is not part of it. The accessible name leads
                     with the same two words in the same order, so what a screen reader says and
                     what the Trainer reads are one phrase. `role="img"` is what makes it a name at
                     all: a label on a bare div is a property most technologies do not expose. --}}
                <div class="flex flex-col items-center gap-1">
                    <div class="w-full rounded-md border px-1 text-center text-xs leading-tight
                                {{ $stateClass[$state] }} relative"
                         role="img"
                         aria-label="{{ $half }} {{ $month }}: {{ $stateWord[$state] }}, {{ $ariaText }}">
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
                        @if ($slotItems === [])
                            {{-- A half-month with nothing in it carries the client's muted plus
                                 and no word: twenty-four boxes each printing "No race" is the
                                 grid describing itself rather than the calendar. The state is
                                 still spoken, in the accessible name on this box. --}}
                            <span class="block text-base leading-none text-ink-faint" aria-hidden="true">+</span>
                        @elseif ($ariaSlots > 1)
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
                        @if ($entered)
                            {{-- The client's pink `Scheduled` pill marks a race this run has put
                                 on this half-month, and UX §2.11 says it is never dimmed. It sits
                                 under the name because there is no thumbnail for it to sit over
                                 (PRD §6.13), and it is keyed to the slot rather than to the cell's
                                 border, because a shared half-month keeps the open border. Fill and
                                 ink are the mood pill's existing pair. --}}
                            <span class="mt-0.5 inline-block rounded-full bg-mood-great px-1.5 py-0.5 font-mono text-[10px] font-bold text-on-mood">Scheduled</span>
                        @endif
                        @if ($fans !== null)
                            <span class="block font-mono text-xs tabular-nums text-ink-muted">
                                {{ number_format((int) $fans) }} fans
                            </span>
                        @endif
                        @if (collect($slotItems)->contains('manual', true))
                            <span class="block text-[10px] text-ink-muted">Trainer-entered</span>
                        @endif
                    </div>
                    <span class="text-xs font-semibold text-ink-muted">{{ $half }} {{ $month }}</span>
                </div>
            @endforeach
        @endforeach
    </div>

    <p class="mt-2 text-xs text-ink-muted">
        {{ $slotCount }} turn slots, Early and Late for each month. A fan gate shows the number it
        needs; a maiden rule is a different lock and a dashed outline, because one is a quantity to
        work toward and the other is an event to reach.
    </p>
</div>
