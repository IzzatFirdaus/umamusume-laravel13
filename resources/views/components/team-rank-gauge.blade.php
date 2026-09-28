@props(['run'])

@php
    if (! $run->composesPanel('team_rank_ladder')) {
        return;
    }

    $current = $run->latestTeamRank();
    $level = $current === null ? null : $run->facilityLevel($current->rank);

    // The ladder the client shows, taken from the config mapping rather than typed into
    // the view, so a scenario that re-tiers its league is a config change and not a
    // silent disagreement with the panel (D-240).
    $ladder = [];
    foreach ((array) config('scenarios.scenarios.'.$run->scenarioKey().'.team_rank_ladder') as $rung) {
        foreach ((array) ($rung['ranks'] ?? []) as $letter) {
            $ladder[] = ['rank' => $letter, 'level' => (int) $rung['level']];
        }
    }
@endphp

<div {{ $attributes->merge(['class' => 'rounded-md border border-rule bg-panel p-3']) }}>
    <div class="lattice-bleed mb-3 flex h-11 items-center rounded-full bg-chrome pl-16 pr-4 text-sm font-bold text-on-chrome">
        <span>Team Rank</span>
    </div>

    @if ($current === null)
        {{-- A gauge with no reading. Zero would say the team sits at the bottom of the
             league, which is a claim about a rank nobody entered (D-220). --}}
        <p class="text-sm text-ink">
            <span class="font-bold text-ink">Rank not recorded</span>: the league letter is
            read off the client, and this run has none entered yet.
        </p>
    @else
        <p class="flex flex-wrap items-baseline gap-x-3 text-sm">
            {{-- Facility level is named as derived, and the cause is named with it: the
                 level is a team property, so a chip that omits the rank it came from reads
                 as an independent stat (D-222, D-256). --}}
            <span class="text-base font-bold text-ink-strong">{{ $current->rank }}</span>
            <span class="text-ink">
                @if ($level === null)
                    above the top rung: grants a second hint, no higher facility
                @else
                    facility level {{ $level }} <span class="text-xs text-ink-muted">derived from the rank letter</span>
                @endif
            </span>
        </p>
    @endif

    <ol class="mt-3 flex flex-wrap gap-1.5" aria-label="League ladder">
        @foreach ($ladder as $rung)
            @php $isCurrent = $current !== null && $rung['rank'] === $current->rank; @endphp
            <li class="rounded px-2 py-1 font-mono text-xs font-bold tabular-nums
                       {{ $isCurrent ? 'bg-pick text-on-pick' : 'bg-sunken text-ink-muted' }}"
                @if ($isCurrent) aria-current="step" @endif>
                {{ $rung['rank'] }}
                <span class="font-sans font-normal">{{ $isCurrent ? 'current' : 'lv '.$rung['level'] }}</span>
            </li>
        @endforeach
    </ol>

    <p class="mt-2 text-xs text-ink-muted">
        The ladder carries S+ above S: it grants a second hint rather than a higher facility,
        so it has no level to show.
    </p>
</div>
