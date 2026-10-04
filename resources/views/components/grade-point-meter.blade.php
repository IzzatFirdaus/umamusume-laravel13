@props([
    // No default, for the same reason as x-resource-strip: a named default puts a
    // scenario in the view.
    'scenario',
    // ['name' => 'End of Classic Year', 'required' => 300] per objective, in order.
    'objectives' => [],
    // The 0-based position of the period the Trainer reports as live. Null means
    // they have not said, and null is a state this panel has to render, not a
    // missing argument: defaulting it to 0 would put every fresh Trackblazer run on
    // the debut objective and draw a bar toward a target nobody chose (D-220).
    'current' => null,
    // Grade Points logged against that period, or null when the run has entered
    // none. The default is null and has to be: @props resolves a prop as
    // `$value ?? $default`, so a default of 0 would silently turn "nothing
    // entered" into "she is on zero points" for every caller that passes null.
    'earned' => null,
    // How many completed races in that period cannot be converted to points (R18). Zero plus a null
    // $earned means nothing was logged; a positive count means the run did race and
    // the tool cannot price it. Those are different sentences.
    'unpricedCount' => 0,
    // `gradePeriods()` from the model: each period with its own earned sum. Optional,
    // because the ladder still renders for callers that only have the objectives.
    'periods' => [],
    // Completed races entered before any period was chosen for them. They belong to
    // no total, and staying silent about them would read as "nothing recorded".
    'unassignedCount' => 0,
])

@php
    $def = config('scenarios.scenarios.'.$scenario);

    if ($def === null) {
        throw new InvalidArgumentException("Unknown scenario [{$scenario}] for x-grade-point-meter.");
    }

    /*
     * Self-gating on config, not on a scenario name. This substitutes for the race
     * calendar where the scenario has objectives instead of mandatory races, and it
     * must be absent everywhere else: an URA run that rendered a Grade Point meter
     * would be describing a mechanic the run does not have (D-221, D-241, gate G-34).
     */
    if ($def['panels']['grade_objectives'] !== true) {
        return;
    }

    if ($objectives === []) {
        return;
    }

    /*
     * The denominator is the single most load-bearing decision on this panel.
     *
     * Grade Points are earned, not banked: D-232 says surplus never carries forward,
     * so there are four separate deadlines and each one is judged alone. A running
     * total would show 660 against a 900 ceiling, which reads as "360 banked toward
     * next year" — a strategy the game does not permit. So $required is always the
     * current objective alone, and the ladder below shows the others as their own
     * period with their own sum, never as credit toward anything.
     */
    $reported = $current !== null;
    $index = $reported ? max(0, min(count($objectives) - 1, (int) $current)) : null;
    $objective = $index === null ? null : $objectives[$index];
    $required = $objective === null ? 0 : max(0, (int) ($objective['required'] ?? 0));
    $logged = $objective !== null && $earned !== null;
    $earned = $logged ? max(0, (int) $earned) : null;
    $over = $logged ? max(0, $earned - $required) : 0;
    $remaining = max(0, $required - (int) $earned);
    $percent = ! $logged || $required === 0 ? 0 : min(100, (int) round(($earned / $required) * 100));

    // Ladder rows read their own sum by the objective's 1-based index when the model
    // passed the periods through, which is the only number D-232 permits beside a
    // deadline. Without `periods` the rows stay the plain target list they were.
    $sums = [];
    foreach ($periods as $row) {
        $sums[(int) ($row['index'] ?? 0)] = $row;
    }

    $rotation = config('scenarios.scenarios.'.$scenario.'.shop.rotation_turns');
@endphp

<div {{ $attributes->merge(['class' => 'rounded-md border border-rule bg-panel p-3']) }}>
    {{-- Same capsule header as every other panel, so a section reads as a section
         across scenarios (DESIGN.md §2.3; the lattice bleed is the client's most
         repeated element, measured across frames 234521, 230755, 232345). Drawn by
         x-capsule-header, which is the one owner of that anatomy: five panels used
         to hand-copy this div and drift from it (pl-16 against pl-20, text-sm
         against text-base) while still claiming to be the same header. --}}
    <x-capsule-header title="Grade Point" class="mb-3" />

    <div class="rounded-md border border-rule bg-raised p-3">
        @if ($objective === null)
            {{-- The state T1c asks for and the one a fresh Trackblazer run is actually
                 in. No target, no bar, no zero: `0 / 300` would assert both that a
                 period is chosen and that the trainee stands on nothing, and neither
                 is known (D-220). The races that exist without a period are named
                 rather than silently dropped from every total. --}}
            <p class="text-xs font-bold uppercase tracking-widest text-ink-muted">Grade Point period</p>
            <p class="mt-0.5 text-sm text-ink">
                <span class="font-bold text-ink">no period reported</span>: which deadline this run
                is working toward is something the Trainer says, not something this tool infers
                from a date, so there is no target to measure against yet.
            </p>
            @if ($unassignedCount > 0)
                <p class="mt-1.5 text-xs text-ink-muted">
                    {{ number_format($unassignedCount) }} logged
                    {{ $unassignedCount === 1 ? 'result has' : 'results have' }} no period
                    entered against it, so none of them counts toward any total below.
                </p>
            @endif
        @else
        <p class="text-xs font-bold uppercase tracking-widest text-ink-muted">Working toward</p>
        <p class="mt-0.5 text-base font-bold text-ink-strong">{{ $objective['name'] ?? '' }}</p>

        <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-sunken" role="img"
             aria-label="{{ $logged ? number_format($earned).' of '.number_format($required) : ($unpricedCount > 0 ? 'Grade Points not totalled' : 'Grade Points not yet recorded') }} toward {{ $objective['name'] ?? 'the current objective' }}">
            <div class="h-full rounded-full bg-green-deep" style="width: {{ $percent }}%"></div>
        </div>

        @if ($logged)
            <p class="mt-1.5 font-mono text-sm tabular-nums text-ink-strong">
                {{ number_format($earned) }} / {{ number_format($required) }}
                <span class="font-sans text-xs text-ink-muted">
                    @if ($over > 0)
                        · {{ number_format($over) }} over the objective
                    @elseif ($required > 0)
                        · {{ number_format($remaining) }} to go
                    @else
                        · no points required
                    @endif
                </span>
            </p>
        @elseif ($unpricedCount > 0)
            {{-- R18's middle state, and the reason Slice 2's two states were not
                 enough. A run that logged two races and cannot be priced is not a run
                 that logged nothing, and the old sentence read as a instruction to go
                 enter races. The total stays withheld (KI-10): grade points are
                 published for a 1st place only, and a race with no calendar slot
                 records no grade at all. --}}
            <p class="mt-1.5 text-sm text-ink-muted">
                <span class="font-bold text-ink">not yet totalled</span>:
                {{ $unpricedCount }} logged {{ $unpricedCount === 1 ? 'result has' : 'results have' }}
                no published Grade Point value, so any total here would count less than
                this run earned.
            </p>
        @else
            {{-- Nothing is entered, so nothing is claimed. The bar stays empty and
                 the figure is named as missing: "0 / 300" would assert that this
                 trainee stands on zero points, which is a fact, not an absence. --}}
            <p class="mt-1.5 text-sm text-ink-muted">
                <span class="font-bold text-ink">not yet recorded</span>: no races are
                logged for this run, so there is no progress to show yet.
            </p>
        @endif

        {{-- The bar is capped at the objective, so the overflow cannot read as
             progress toward anything. Stating it in words is what makes an
             over-achievement legible instead of a bar that quietly tops out. --}}
        @if ($over > 0)
            <p class="mt-2 rounded-md border border-rule bg-sunken px-3 py-2 text-xs text-ink">
                {{ number_format($over) }} past the objective, and not banked. The next objective
                starts from zero whatever this one finishes at.
            </p>
        @endif
        @endif
    </div>

    <p class="mt-3 rounded-md border border-rule bg-raised px-3 py-2 text-sm text-ink">
        <span class="font-bold text-ink-strong">Surplus does not carry over.</span>
        @if (! $logged)
            Each of these is judged on its own, so whatever is left over at one deadline is
            not available at the next.
        @elseif ($required > 0)
            {{ number_format($earned) }} of {{ number_format($required) }} is met against this
            objective; anything beyond it is not banked toward the next one.
        @else
            This objective asks for no points, so there is nothing to bank.
        @endif
    </p>

    <ol class="mt-3 flex flex-col gap-1.5" aria-label="Grade Point objectives">
        @foreach ($objectives as $i => $item)
            @php
                $done = $i < $index;
                $isCurrent = $i === $index;
            @endphp
            <li class="flex items-baseline justify-between gap-3 rounded-md border px-3 py-2 text-sm
                       {{ $isCurrent ? 'border-2 border-pick-line bg-raised' : 'border-rule bg-panel' }}"
                @if ($isCurrent) aria-current="step" @endif>
                <span class="min-w-0 flex-1">
                    <span class="font-semibold {{ $isCurrent ? 'text-ink-strong' : 'text-ink-muted' }}">
                        {{ $item['name'] ?? '' }}
                    </span>
                    @if ($done)
                        <span class="ml-1.5 text-xs font-bold text-green-deep">Complete</span>
                    @endif
                </span>
                <span class="shrink-0 font-mono text-xs tabular-nums text-ink-muted">
                    {{ number_format((int) ($item['required'] ?? 0)) }} pts
                    @php $row = $sums[(int) ($item['index'] ?? ($i + 1))] ?? null; @endphp
                    {{-- Each deadline carries its own sum and nothing else. A total of the
                         four would be the banking arithmetic D-232 forbids, and an absent
                         period says "not recorded" rather than 0 (D-220). --}}
                    @if ($row !== null)
                        ·
                        @if ($row['earned'] !== null)
                            {{ number_format((int) $row['earned']) }} earned
                        @elseif ($row['unpriced'] > 0)
                            not totalled
                        @else
                            not recorded
                        @endif
                    @endif
                </span>
            </li>
        @endforeach
    </ol>

    {{-- D-232 makes this pair the shop's real economy: the rotation is what
         decides when an offer is worth saving for, and the balance is not, because
         unspent coins die with the run. Both facts are sourced; the balance itself
         is run state this build does not hold yet, so it is named as missing rather
         than shown as zero. --}}
    <div class="mt-3 flex flex-wrap gap-x-6 gap-y-1 border-t border-rule pt-3 text-xs text-ink-muted">
        @if ($rotation !== null)
            <span>Rotation resets in {{ (int) $rotation }} turns</span>
        @endif
        <span>Shop Coins: not yet recorded</span>
    </div>

    {{-- R40, one line: the ladder printed above is the standard track, the rule that would
         place a trainee on the dirt-leaning or limited-turf-range track is not sourced,
         and the disagreement between the two guides is carried by KI-15. Naming which
         track the numbers came from is the difference between a conservative target and
         an unexplained one (D-256). --}}
    <p class="mt-3 text-xs text-ink-muted">
        Targets shown are the <span class="font-bold text-ink">standard</span> track. Which
        trainee belongs on the dirt-leaning or limited-turf-range track is not sourced, and
        KI-15 carries the disagreement.
    </p>
</div>
