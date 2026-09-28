@props(['run', 'slots'])

@php
    // Only the scenarios that compose a race calendar, or a grade ladder that needs
    // finishes attributed to a period, get a race writer. Everywhere else this returns
    // without rendering, because a form for a mechanic the run lacks is a lie (D-221).
    if (! $run->composesPanel('race_calendar') && ! $run->composesGradeObjectives()) {
        return;
    }

    $teamRace = $run->composesPanel('team_race');
    $graded = $run->composesGradeObjectives();
    $entries = $run->raceEntries->sortBy(fn (App\Models\RaceEntry $e): int => $e->scenarioSlot?->sort_order ?? PHP_INT_MAX);
@endphp

<div {{ $attributes->merge(['class' => 'rounded-md border border-rule bg-panel p-3']) }}>
    <div class="lattice-bleed mb-3 flex h-11 items-center rounded-full bg-chrome pl-16 pr-4 text-sm font-bold text-on-chrome">
        <span>Races</span>
    </div>

    <form method="POST" action="{{ route('runs.races.store', $run) }}" class="flex max-w-3xl flex-wrap items-end gap-3 rounded-md border border-rule bg-raised p-3 text-sm">
        @csrf
        <label class="flex flex-col gap-1">
            <span class="font-medium text-ink">Calendar slot</span>
            @if ($slots->isEmpty())
                {{-- The honest empty state: the writer is here, the calendar is not.
                     `scenario_slots` is empty until the fetch engine lands (KI-11), so a
                     select with no options would read as a broken control rather than as
                     an absent dataset. --}}
                <input type="text" class="rounded-md border border-rule bg-sunken px-2 py-1 text-ink-muted"
                       value="no calendar rows to enter against" disabled>
                <span class="text-xs text-ink-muted">
                    Races are fetched data, and this build has not fetched them.
                </span>
            @else
                <select name="scenario_slot_id" required
                        class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                    @foreach ($slots as $slot)
                        <option value="{{ $slot->id }}">{{ $slot->title }} · {{ $slot->kind }} · {{ $slot->tier ?? 'no grade' }}</option>
                    @endforeach
                </select>
            @endif
        </label>

        <label class="flex flex-col gap-1">
            <span class="font-medium text-ink">Outcome</span>
            <select name="status" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                @foreach (\App\Enums\RaceEntryStatus::cases() as $status)
                    <option value="{{ $status->value }}">{{ $status->value }}</option>
                @endforeach
            </select>
        </label>

        <label class="flex flex-col gap-1">
            <span class="font-medium text-ink">Placement</span>
            <input type="number" name="placement" min="1" class="w-20 rounded-md border border-rule bg-raised px-2 py-1 text-ink">
        </label>

        @if ($teamRace)
            {{-- Circles only where the client draws them, and never as a default 0: the
                 Trainer read a number or they did not (D-225, D-220). --}}
            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">Circles read</span>
                <select name="circles" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                    <option value="">not read</option>
                    @foreach (range(0, \App\Models\RaceEntry::MAX_CIRCLES) as $circles)
                        <option value="{{ $circles }}">{{ $circles }}</option>
                    @endforeach
                </select>
            </label>
        @endif

        @if ($graded)
            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">Counts toward</span>
                <select name="objective_index" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                    <option value="">no period</option>
                    @foreach ($run->gradeObjectives() as $objective)
                        <option value="{{ $objective['index'] }}">{{ $objective['index'] }}. {{ $objective['name'] }}</option>
                    @endforeach
                </select>
            </label>
        @endif

        <button type="submit" class="rounded-full border-2 border-rule px-4 py-2 font-bold text-ink-strong">Record race</button>

        @if ($errors->any())
            <p class="w-full text-sm text-risk" role="alert">
                {{ $errors->first('scenario_slot_id', 'That race is not on this run\'s calendar.') }}
                {{ $errors->first('circles', 'Circles are read as 0 to 5, on a team race only.') }}
                {{ $errors->first('objective_index', 'A period index is one of the four objectives.') }}
                {{ $errors->first('placement', 'Placement is a finish number, 1 or above.') }}
            </p>
        @endif
    </form>

    @if ($entries->isEmpty())
        <p class="mt-3 rounded-md border border-rule bg-raised px-3 py-2 text-sm text-ink-muted" role="status">
            No races recorded for this run.
        </p>
    @else
        <ul class="mt-3 flex flex-col gap-1.5" aria-label="Recorded races">
            @foreach ($entries as $entry)
                <li class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 rounded-md border border-rule bg-raised px-3 py-2 text-sm">
                    <span class="min-w-0 flex-1">
                        <span class="font-semibold text-ink-strong">{{ $entry->scenarioSlot?->title ?? 'a race with no calendar row' }}</span>
                        <span class="ml-1.5 text-xs text-ink-muted">{{ $entry->status->value }}</span>
                    </span>
                    <span class="flex flex-wrap gap-x-3 font-mono text-xs tabular-nums text-ink-muted">
                        <span>{{ $entry->scenarioSlot?->tier ?? 'no grade' }}</span>
                        <span>{{ $entry->placement === null ? 'no placement' : $entry->placement.'th' }}</span>
                        @if ($teamRace)
                            <span>{{ $entry->circles === null ? 'circles not read' : $entry->circles.' circles' }}</span>
                        @endif
                        @if ($graded)
                            <span>{{ $entry->objective_index === null ? 'no period' : 'period '.$entry->objective_index }}</span>
                        @endif
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
