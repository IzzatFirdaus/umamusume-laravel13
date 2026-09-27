@props([
    // No default, for the same reason as x-resource-strip: a named default puts a
    // scenario in the view.
    'scenario',
    // ['name' => 'End of Classic Year', 'required' => 300] per objective, in order.
    'objectives' => [],
    // Index into $objectives of the objective being worked on right now.
    'current' => 0,
    // Grade Points logged against that objective, or null when the run has entered
    // none. The default is null and has to be: @props resolves a prop as
    // `$value ?? $default`, so a default of 0 would silently turn "nothing
    // entered" into "she is on zero points" for every caller that passes null.
    'earned' => null,
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
     * current objective alone, and the ladder below shows the others as dates, not
     * as credit.
     */
    $index = max(0, min(count($objectives) - 1, (int) $current));
    $objective = $objectives[$index];
    $required = max(0, (int) ($objective['required'] ?? 0));
    $logged = $earned !== null;
    $earned = $logged ? max(0, (int) $earned) : null;
    $over = $logged ? max(0, $earned - $required) : 0;
    $remaining = max(0, $required - $earned);
    $percent = ! $logged || $required === 0 ? 0 : min(100, (int) round(($earned / $required) * 100));

    $rotation = config('scenarios.scenarios.'.$scenario.'.shop.rotation_turns');
@endphp

<div {{ $attributes->merge(['class' => 'rounded-md border border-rule bg-panel p-3']) }}>
    {{-- Same capsule header as every other panel, so a section reads as a section
         across scenarios (DESIGN.md §2.3; the lattice bleed is the client's most
         repeated element, measured across frames 234521, 230755 and 232345). --}}
    <div class="lattice-bleed mb-3 flex h-11 items-center rounded-full bg-chrome pl-16 pr-4 text-sm font-bold text-on-chrome">
        <span>Grade Point</span>
    </div>

    <div class="rounded-md border border-rule bg-raised p-3">
        <p class="text-xs font-bold uppercase tracking-widest text-ink-muted">Working toward</p>
        <p class="mt-0.5 text-base font-bold text-ink-strong">{{ $objective['name'] ?? '' }}</p>

        <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-sunken" role="img"
             aria-label="{{ $logged ? number_format($earned).' of '.number_format($required) : 'Grade Points not yet recorded' }} toward {{ $objective['name'] ?? 'the current objective' }}">
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
        @else
            {{-- Nothing is entered, so nothing is claimed. The bar stays empty and
                 the figure is named as missing: "0 / 300" would assert that this
                 trainee stands on zero points, which is a fact, not an absence. --}}
            <p class="mt-1.5 text-sm text-ink-muted">
                <span class="font-bold text-ink">not yet recorded</span> — no Grade Points are
                entered for this run, so there is no progress to show yet.
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
                       {{ $isCurrent ? 'border-2 border-pick bg-raised' : 'border-rule bg-panel' }}"
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
</div>
