@props(['run', 'slots', 'entryMode' => 'calendar'])

@php
    // Only the scenarios that compose a race calendar, or a grade ladder that needs
    // finishes attributed to a period, get a race writer. Everywhere else this returns
    // without rendering, because a form for a mechanic the run lacks is a lie (D-221).
    if (! $run->composesPanel('race_calendar') && ! $run->composesGradeObjectives()) {
        return;
    }

    $teamRace = $run->composesPanel('team_race');
    $graded = $run->composesGradeObjectives();
    $entries = $run->raceEntries->sortBy(fn (App\Models\RaceEntry $e): int => $e->scenarioSlot?->sort_order ?? $e->raceCatalogSlot?->sort_order ?? PHP_INT_MAX);

    // The calendar list is the career catalogue for the year this screen shows, which is
    // the same year the grid above is drawn from. `scenario_slots` carries no year, so a
    // list built from it offered a Senior G1 to a Trainer sitting in Junior.
    $calendarYear = $run->careerYearForTab(request('year'));
    $catalogSlots = $run->calendarRaceSlots($calendarYear);
    $manualSlots = $slots->where('kind', 'free_race');

    /*
     * R67: server-driven disclosure, the shape guided-step already uses. The mode is a
     * submitted value on a GET form, so switching branches is a navigation and not a
     * mutation; `old()` wins because a failed write flashes its input, and the Trainer who
     * mistyped a month comes back to the month field rather than to the other branch.
     *
     * The reason this is not a client-side branch toggle: that library is not a dependency
     * (KI-21). A declarative branch is emitted by Blade and made inert by the browser, so the
     * form existed in the HTML and nowhere a Trainer could reach it.
     */
    $mode = old('entry_mode', $entryMode) === 'manual' ? 'manual' : 'calendar';
@endphp

<div {{ $attributes->merge(['class' => 'rounded-md border border-rule bg-panel p-3']) }}>
    <div class="lattice-bleed mb-3 flex h-11 items-center rounded-full bg-chrome pl-16 pr-4 text-sm font-bold text-on-chrome">
        <span>Races</span>
    </div>

    {{-- The branch choice sits outside the record form: a form may not nest, and the choice
         is not part of what gets recorded. Each control submits its own value to the screen
         that knows how to render it. --}}
    <div class="mb-3 flex flex-wrap gap-2" aria-label="Race entry mode">
        @foreach (['calendar' => 'Calendar race', 'manual' => 'Race not on the calendar'] as $key => $label)
            <form method="GET" action="{{ route('runs.show', $run) }}">
                <input type="hidden" name="entry_mode" value="{{ $key }}">
                <button type="submit" aria-pressed="{{ $mode === $key ? 'true' : 'false' }}"
                        class="rounded-md border px-3 py-1.5 text-sm font-medium
                               {{ $mode === $key ? 'border-pick-line bg-pick/10 text-ink-strong' : 'border-rule text-ink-muted' }}">
                    {{ $label }}
                </button>
            </form>
        @endforeach
    </div>

    <form method="POST" action="{{ route('runs.races.store', $run) }}" class="flex max-w-3xl flex-wrap items-end gap-3 rounded-md border border-rule bg-raised p-3 text-sm">
        @csrf
        <input type="hidden" name="entry_mode" value="{{ $mode }}">

        @if ($mode === 'calendar')
            {{-- Calendar path. Each option names its half-month and grade, so a race cannot
                 be chosen on its name alone, and the heading says which year is in scope. --}}
            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">Calendar race · {{ \App\Models\RaceCatalogSlot::YEARS[$calendarYear] }} year</span>
                @if ($catalogSlots->isEmpty())
                    <input type="text" class="rounded-md border border-rule bg-sunken px-2 py-1 text-ink-muted"
                           value="no calendar races in this year" disabled>
                    <span class="text-xs text-ink-muted">
                        Races are fetched data, and this career year has none of them.
                    </span>
                @else
                    <select name="race_catalog_slot_id"
                            class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                        @foreach ($catalogSlots as $slot)
                            <option value="{{ $slot->id }}" data-tier="{{ $slot->tier ?? '' }}" @selected(old('race_catalog_slot_id') == $slot->id)>
                                {{ $slot->title }} · {{ $slot->slot_label }} · {{ $slot->tier ?? 'no grade' }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </label>

            {{-- A race the Trainer typed earlier is not a calendar race and lives in the other
                 table, so it gets its own control: one select cannot carry two field names, and
                 the validator refuses an entry that sets both. --}}
            @if ($manualSlots->isNotEmpty())
                <label class="flex flex-col gap-1">
                    <span class="font-medium text-ink">Trainer-entered race</span>
                    <select name="scenario_slot_id"
                            class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                        <option value="">not a hand-entered race</option>
                        @foreach ($manualSlots as $slot)
                            <option value="{{ $slot->id }}" @selected(old('scenario_slot_id') == $slot->id)>{{ $slot->title }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
        @else
            {{-- Manual path --}}
            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">Race title</span>
                <input type="text" name="title" value="{{ old('title') }}" required maxlength="255"
                       class="min-w-48 rounded-md border border-rule bg-raised px-2 py-1 text-ink"
                       placeholder="e.g. Practice Race">
            </label>
            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">Month</span>
                <select name="month" required class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                    <option value="">select</option>
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}" {{ old('month') == $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </label>
            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">Half</span>
                <select name="half" required class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                    <option value="">select</option>
                    <option value="Early" {{ old('half') === 'Early' ? 'selected' : '' }}>Early</option>
                    <option value="Late" {{ old('half') === 'Late' ? 'selected' : '' }}>Late</option>
                </select>
            </label>
            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">Tier</span>
                <input type="text" name="tier" value="{{ old('tier') }}" maxlength="10"
                       class="w-20 rounded-md border border-rule bg-raised px-2 py-1 text-ink"
                       placeholder="optional">
                <span class="text-xs text-ink-muted">No prefill for manual races</span>
            </label>
        @endif

        <label class="flex flex-col gap-1">
            <span class="font-medium text-ink">Outcome</span>
            <select name="status" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                @foreach (\App\Enums\RaceEntryStatus::cases() as $status)
                    <option value="{{ $status->value }}" {{ old('status') === $status->value ? 'selected' : '' }}>{{ $status->value }}</option>
                @endforeach
            </select>
        </label>

        <label class="flex flex-col gap-1">
            <span class="font-medium text-ink">Placement</span>
            <input type="number" name="placement" min="1" value="{{ old('placement') }}" class="w-20 rounded-md border border-rule bg-raised px-2 py-1 text-ink">
        </label>

        {{-- KI-17: the turn this race was run on. A race entry used to point only at a month and a
             half, so no logged turn could be identified as a race turn, and D-230's premise that the
             consecutive-race count is "already recoverable from turn_entries" was false. The Trainer
             names the turn; nothing guesses it from a race date (D-270). --}}
        <label class="flex flex-col gap-1">
            <span class="font-medium text-ink">Logged turn</span>
            @if ($run->turnEntries->isEmpty())
                <input type="text" class="rounded-md border border-rule bg-sunken px-2 py-1 text-ink-muted"
                       value="no turns logged to name" disabled>
                <span class="text-xs text-ink-muted">Log the turn first, then name it on its race.</span>
            @else
                <select name="turn_entry_id" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                    <option value="">not named</option>
                    @foreach ($run->turnEntries->sortBy('turn') as $turn)
                        <option value="{{ $turn->id }}" {{ old('turn_entry_id') == $turn->id ? 'selected' : '' }}>Turn {{ $turn->turn }}</option>
                    @endforeach
                </select>
            @endif
        </label>

        @if ($teamRace)
            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">Circles read</span>
                <select name="circles" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                    <option value="">not read</option>
                    @foreach (range(0, \App\Models\RaceEntry::MAX_CIRCLES) as $circles)
                        <option value="{{ $circles }}" {{ old('circles') === (string) $circles ? 'selected' : '' }}>{{ $circles }}</option>
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
                        <option value="{{ $objective['index'] }}" {{ old('objective_index') == $objective['index'] ? 'selected' : '' }}>{{ $objective['index'] }}. {{ $objective['name'] }}</option>
                    @endforeach
                </select>
            </label>
        @endif

        <button type="submit" class="rounded-full border-2 border-rule px-4 py-2 font-bold text-ink-strong">Record race</button>

        @if ($errors->any())
            <p class="w-full text-sm text-risk" role="alert">
                {{ $errors->first('race_catalog_slot_id') }}
                {{ $errors->first('scenario_slot_id') }}
                {{ $errors->first('title') }}
                {{ $errors->first('month') }}
                {{ $errors->first('half') }}
                {{ $errors->first('circles', 'Circles are read as 0 to 5, on a team race only.') }}
                {{ $errors->first('objective_index', 'A period index is one of the four objectives.') }}
                {{ $errors->first('placement', 'Placement is a finish number, 1 or above.') }}
                {{ $errors->first('turn_entry_id', 'That turn was not logged on this run.') }}
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
                        <span class="font-semibold text-ink-strong">{{ $entry->raceCatalogSlot?->title ?? $entry->scenarioSlot?->title ?? 'a race with no calendar row' }}</span>
                        <span class="ml-1.5 text-xs text-ink-muted">{{ $entry->status->value }}</span>
                        @if ($entry->scenarioSlot?->isFreeRace())
                            <span class="ml-1 text-[10px] text-ink-muted">Trainer-entered</span>
                        @endif
                    </span>
                    <span class="flex flex-wrap gap-x-3 font-mono text-xs tabular-nums text-ink-muted">
                        <span>{{ $entry->tierKey() ?? 'no grade' }}</span>
                        <span>{{ $entry->placementOrdinal() }}</span>
                        <span>{{ $entry->turnEntry === null ? 'turn not named' : 'turn '.$entry->turnEntry->turn }}</span>
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
