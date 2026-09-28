@props(['run'])

@php
    if (! $run->composesPanel('team_race')) {
        return;
    }

    $slots = $slots ?? $run->raceEntries
        ->map(fn (App\Models\RaceEntry $e): ?App\Models\ScenarioSlot => $e->scenarioSlot?->kind === 'team_race' ? $e->scenarioSlot : null)
        ->filter()
        ->unique('id')
        ->values();

    $guidance = (int) config('scenarios.scenarios.'.$run->scenarioKey().'.team_race.circles_guidance', 0);
    $entries = $run->raceEntries->filter(fn (App\Models\RaceEntry $e): bool => $e->scenarioSlot?->kind === 'team_race');
@endphp

<div {{ $attributes->merge(['class' => 'rounded-md border border-rule bg-panel p-3']) }}>
    <div class="lattice-bleed mb-3 flex h-11 items-center rounded-full bg-chrome pl-16 pr-4 text-sm font-bold text-on-chrome">
        <span>Team Race</span>
    </div>

    {{-- 06:109, transcribed into config as `circles_guidance`: three circles is a safety
         margin, not a win condition, and the sentence matters because losing moves the
         league rank down. A panel that printed it as a target would tell a Trainer a
         mid-number guarantees the race, which the source does not claim. --}}
    <p class="text-sm text-ink">
        <span class="font-bold text-ink-strong">Aim for at least {{ $guidance }} circles</span>
        <span class="text-ink-muted">as a margin, not a win condition. A loss lowers the league
            rank, and after the 2026-07-01 rework a loss can be retried with an Alarm Clock.</span>
    </p>

    @if ($entries->isEmpty())
        <p class="mt-3 rounded-md border border-rule bg-raised px-3 py-2 text-sm text-ink-muted" role="status">
            No team races recorded. Circles are entered on the race form as the number the
            client showed before you committed.
        </p>
    @else
        <ul class="mt-3 flex flex-col gap-1.5" aria-label="Team races">
            @foreach ($entries as $entry)
                @php $circles = $entry->circles; @endphp
                <li class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 rounded-md border border-rule bg-raised px-3 py-2 text-sm">
                    <span class="font-semibold text-ink-strong">{{ $entry->scenarioSlot?->title ?? 'a team race with no calendar row' }}</span>
                    <span class="flex flex-wrap gap-x-3 font-mono text-xs tabular-nums text-ink-muted">
                        <span>{{ $entry->scenarioSlot?->tier ?? 'opponent not recorded' }}</span>
                        <span>{{ $circles === null ? 'circles not read' : $circles.' circles' }}</span>
                        @if ($circles !== null)
                            {{-- The margin is named beside the number so the reading is not
                                 a bare count the Trainer has to interpret twice. --}}
                            <span>{{ $circles >= $guidance ? 'at or above the margin' : 'below the margin' }}</span>
                        @endif
                        <span>{{ $entry->placement === null ? 'no placement' : 'placed '.$entry->placement }}</span>
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
