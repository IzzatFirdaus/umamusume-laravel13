<x-layout :title="'Run: '.$run->umamusume->name">
    {{--
        D-170 and D-41: turn, scenario, Energy and Fans are persistent, and the band never
        scrolls away or collapses. A Trainer reading turn 24 needs the totals at the same
        moment they read the entry, so they stay on screen while the log moves.

        D-40 draws these two regions left and right at desktop. This is the vertical form -
        a pinned state region above a scrolling log - because the stat band is a six-column
        grid sized to the page, and side by side it becomes six unreadable slivers at the
        width `main` allows. The rule the frame serves (persistence) holds either way; the
        axis is a deviation from D-40 as written, recorded with the measurement rather than
        smoothed over.

        `lg:sticky` only, so a narrow screen stacks and scrolls as one column, which is what
        D-40 asks for below 1024px. The negative top margin with matching padding is what
        keeps the `main` gutter from letting scrolled content appear above a pinned header:
        the sticky box owns that strip itself, with its own background.
    --}}
    <div class="flex items-baseline justify-between">
        <h1 class="text-2xl font-semibold text-ink-strong">
            {{ $run->umamusume->name }}
            {{-- The matrix label, not the raw slug, and "not set" rather than a
                 scenario name when the run has none: a run with no scenario does
                 not declare itself to be the baseline scenario. --}}
            <span class="ml-2 text-sm font-normal text-ink-muted">
                {{ $run->status->label() }} ·
                {{ $run->scenario === null ? 'No scenario set' : ($scenarios[$run->scenario] ?? $run->scenario) }}
            </span>
        </h1>
        <div class="flex gap-3 text-sm">
            <a href="{{ route('runs.export', ['run' => $run, 'format' => 'csv']) }}" class="hover:underline">Export CSV</a>
            <a href="{{ route('runs.export', ['run' => $run, 'format' => 'json']) }}" class="hover:underline">Export JSON</a>
        </div>
    </div>

    @if ($run->notes)
        <p class="mt-2 text-sm text-ink-muted">{{ $run->notes }}</p>
    @endif

    {{-- The pinned region holds exactly what D-170 names: the turn, the scenario, Energy
         and Fans in the strip, and the band under it. Measured before this narrowing,
         pinning the identity block with them made the pinned box 613px tall on a 900px
         viewport, which left a Trainer 287px of log to read - the frame meant to keep the
         totals in view had instead taken the screen from the thing they are read against.
         The h1 carries the same scenario word the strip already prints, so nothing named
         by the rule is what gave way. --}}
    <section aria-label="Run state" class="bg-page lg:sticky lg:top-0 lg:z-10 lg:py-3">
        {{-- Composed from the run's scenario (D-220). A run that names no scenario
             resolves to the baseline strip rather than to no strip at all, and a value
             the run has not recorded renders as unrecorded rather than as a default. --}}
        <h2 class="text-lg font-semibold text-ink-strong">Resources</h2>
        <x-resource-strip :scenario="$run->scenarioKey()" :run="$run->stripValues()" class="mt-3" />

        {{-- The stat band is the trainee's current numbers, so it belongs beside the run's
             current resources and above anything that talks about a single turn. It reads the
             latest logged turn: the run's state is what the last turn ended at, not an average
             of the log. No logged turn means no band at all, because five zeroes would be a
             claim about a trainee nobody entered (D-220). --}}
        @if ($band !== null)
            <h2 class="mt-6 text-lg font-semibold text-ink-strong">Stats</h2>
            <x-stat-band
                :scenario="$band['scenario']"
                :values="$band['values']"
                :skill-points="$band['skillPoints']"
                class="mt-3"
            />
        @endif
    </section>

    {{-- The log: everything that grows with the run, scrolling under the pinned state. --}}
    <section aria-label="Turn log" class="mt-2">

    {{--
        The goal panel, and there is at most one. A scenario with mandatory race
        goals gets the calendar; a scenario with Grade Point deadlines gets the
        meter; a scenario with neither gets nothing. Both components read that from
        config and return before their markup when the panel is off, so mounting
        both here is what keeps the set scenario-composed without a single
        `if ($scenario === ...)` in this template (D-221, D-240, D-241).

        Both are fed from the run: the calendar from `scenario_slots` and this run's
        race log, the meter from the matrix's own ladder. The earned figure is not
        passed at all when the run cannot state one, and the component's `earned`
        prop then renders the absence as absence rather than as zero.

        A run with no scenario chosen gets neither. `scenarioKey()` falls back to the
        baseline so the resource strip always has generic widgets, but these panels
        name specific races and specific deadlines, and borrowing the baseline's
        schedule would tell a Trainer their race calendar is Oka Sho when they have
        not picked a scenario at all (D-220, D-221).
    --}}
    @if ($run->hasScenario())
        @php $panelScenario = $run->scenarioKey(); @endphp
        <x-race-calendar :scenario="$panelScenario" :cells="$run->calendarCells()" class="mt-3" />
        <x-grade-point-meter
            :scenario="$panelScenario"
            :objectives="$run->gradeObjectives()"
            :earned="$run->gradeEarned()"
            :unpriced-count="$run->gradeUnpricedCount()"
            class="mt-3"
        />
    @endif

    <form method="POST" action="{{ route('runs.update', $run) }}" class="mt-4 flex max-w-3xl flex-wrap items-end gap-3 rounded-md border border-rule bg-raised p-4 text-sm">
        @csrf
        @method('PUT')
        {{-- The update shares StoreTrainingRunRequest with create, so the fields this
             form is not editing are carried through as-is rather than being blanked. --}}
        <input type="hidden" name="umamusume_id" value="{{ $run->umamusume_id }}">
        <input type="hidden" name="status" value="{{ $run->status->value }}">
        <label class="flex flex-col gap-1">
            <span class="font-medium text-ink">Scenario</span>
            {{-- A select, because the value is now validated against the matrix:
                 free text here would only produce a rejected submission. --}}
            <select name="scenario" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                <option value="">Not set (baseline strip)</option>
                @foreach ($scenarios as $key => $label)
                    <option value="{{ $key }}" @selected($run->scenario === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit" class="rounded-full border-2 border-rule px-4 py-2 font-bold text-ink-strong">Change scenario</button>
        @error('scenario')<p class="w-full text-risk">{{ $message }}</p>@enderror
    </form>

    <h2 class="mt-8 text-lg font-semibold text-ink-strong">Turns</h2>
    @php
        // A recorded failure is an event, not a column: `turn_entries` holds the absolute
        // values the client showed, and the failure that produced them belongs to
        // `turn_events` (ADR-0003). Keyed by turn so one row cannot borrow another's chip.
        $failures = $run->turnEvents
            ->filter(fn ($event): bool => $event->event_type === \App\Enums\TurnEventType::Failure)
            ->keyBy('turn');
    @endphp
    @if ($run->turnEntries->isEmpty())
        <p class="mt-2 text-sm text-ink-muted">No turns logged yet. Add the first one below.</p>
    @else
        <table class="mt-3 w-full border-collapse text-sm">
            <thead>
                <tr class="border-b border-rule text-left text-ink-muted">
                    <th class="py-1 pr-3">Turn</th><th class="pr-3">Speed</th><th class="pr-3">Stamina</th>
                    <th class="pr-3">Power</th><th class="pr-3">Guts</th><th class="pr-3">Wit</th>
                    <th class="pr-3">SP</th><th class="pr-3">Condition</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($run->turnEntries as $entry)
                    @php $failure = $failures[$entry->turn] ?? null; @endphp
                    <tr class="border-b border-rule">
                        <td class="py-1 pr-3">
                            {{ $entry->turn }}
                            @if ($failure !== null)
                                {{-- The word carries what the colour carries (D-12), and the
                                     kind is named because a penalty on Energy and a penalty
                                     on a stat are different problems for a Trainer. Never a
                                     bare zero, which would read as a stat nobody entered. --}}
                                <span class="ml-1.5 rounded border border-risk px-1 text-xs font-bold text-risk">Failed</span>
                                <span class="block text-xs text-ink-muted">
                                    Penalty kind: {{ $failure->deltas['penalty_kind'] ?? 'not recorded' }}
                                    · {{ $failure->source_name }}
                                </span>
                            @endif
                        </td>
                        <td class="pr-3">{{ $entry->speed }}</td>
                        <td class="pr-3">{{ $entry->stamina }}</td>
                        <td class="pr-3">{{ $entry->power }}</td>
                        <td class="pr-3">{{ $entry->guts }}</td>
                        <td class="pr-3">{{ $entry->wit }}</td>
                        <td class="pr-3">{{ $entry->sp ?? '' }}</td>
                        <td class="pr-3">{{ $entry->condition }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{--
        The guided rail is the door. `x-guided-step` already carried the choice cards, the
        preview panel and the gauge beside the control that spends it, and it had only ever
        been rendered on the review surface; mounting it here is the whole of the review's
        reframed finding. The numbers stay on this screen because they are what the tool
        records, and the client's own projected gains are not: the preview is this turn's
        entered values minus the stored ones, which a Trainer can check (D-256).

        The previous turn's values are placeholders, never pre-filled: an input that arrives
        with a number in it has already asserted that the stat did not change, and the
        Trainer may have meant to type a different one (D-220).
    --}}
    <x-guided-step
        :scenario="$guided['scenario']"
        :current="$guided['current']"
        :choices="$guided['choices']"
        :selected="$guided['values']['choice'] ?? null"
        :preview="$guided['preview']"
        :previewed="$guided['previewed']"
        :energy="$guided['energy']"
        :confirm-route="route('runs.turns.store', $run)"
        class="mt-6"
    >
        <div class="mt-3 grid grid-cols-2 gap-3 text-sm md:grid-cols-4">
            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">Turn</span>
                <input type="number" name="turn" min="1" required value="{{ $guided['turn'] }}"
                       class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
            </label>
            @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                <label class="flex flex-col gap-1">
                    <span class="font-medium text-ink">{{ ucfirst($stat) }}</span>
                    <input type="number" name="{{ $stat }}" min="0" max="1200" required
                           value="{{ $guided['values'][$stat] ?? '' }}"
                           placeholder="{{ $guided['previous']?->{$stat} ?? 'no logged turn' }}"
                           class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                </label>
            @endforeach
            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">Skill Points</span>
                <input type="number" name="sp" min="0" value="{{ $guided['values']['sp'] ?? '' }}"
                       placeholder="{{ $guided['previous']?->sp ?? 'no logged turn' }}"
                       class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
            </label>
            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">Energy</span>
                <input type="number" name="energy" min="0" max="100" value="{{ $guided['values']['energy'] ?? '' }}"
                       placeholder="{{ $guided['previous']?->energy ?? 'no logged turn' }}"
                       class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
            </label>
            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">Fans</span>
                <input type="number" name="fans" min="0" value="{{ $guided['values']['fans'] ?? '' }}"
                       placeholder="{{ $guided['previous']?->fans ?? 'no logged turn' }}"
                       class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
            </label>
            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">Mood</span>
                {{-- The five tier words are the client's own strings and the arrow is not
                     decoration: the three derived mood colours are not separable by hue,
                     so direction is the only ordinal signal the pill has (D-259,
                     DESIGN.md §6.17). `Practice Poor` is deliberately absent: it is a
                     failure condition from an event, not a mood tier (D-201). --}}
                @php $moodArrows = ['GREAT' => '↑', 'GOOD' => '↑', 'NORMAL' => '→', 'BAD' => '↓', 'AWFUL' => '↓']; @endphp
                <select name="mood" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                    <option value="">not yet recorded</option>
                    @foreach (\App\Enums\MoodTier::cases() as $tier)
                        <option value="{{ $tier->value }}"
                                @selected(($guided['values']['mood'] ?? null) === $tier->value
                                    || (($guided['values']['mood'] ?? null) === null && $guided['mood'] === $tier->value))>
                            {{ $tier->value }} {{ $moodArrows[$tier->value] }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">Outcome</span>
                <select name="outcome" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                    @foreach (['Success', 'Failure'] as $outcome)
                        <option value="{{ $outcome }}"
                                @selected(($guided['values']['outcome'] ?? null) === $outcome)>{{ $outcome }}</option>
                    @endforeach
                </select>
            </label>
            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">Penalty kind</span>
                {{-- Only required when the outcome is a failure, which the server checks.
                     It is not hidden behind a script, because nothing here runs one to hide
                     it, and a field that is always visible cannot be missed. --}}
                <select name="penalty_kind" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                    <option value="">none, or not a failure</option>
                    @foreach (['energy' => 'Energy', 'mood' => 'Mood', 'stat' => 'A stat'] as $value => $kind)
                        <option value="{{ $value }}"
                                @selected(($guided['values']['penalty_kind'] ?? null) === $value)>{{ $kind }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        @if ($guided['preview'] === [] && ! $guided['has_previous'])
            <p class="mt-3 text-xs text-ink-muted">
                This is the run's first turn, so there is no turn to compare against and the
                preview will have nothing to subtract. The numbers above are what gets stored.
            </p>
        @endif
    </x-guided-step>

    @error('previewed')<p class="mt-2 max-w-3xl text-sm text-risk">{{ $message }}</p>@enderror
    @if ($errors->any() && $errors->hasAny(['turn', 'speed', 'stamina', 'power', 'guts', 'wit', 'sp', 'energy', 'mood', 'fans', 'choice', 'outcome', 'penalty_kind', 'previewed']))
        <ul class="mt-2 max-w-3xl list-disc pl-6 text-sm text-risk">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    {{-- D-53: the raw entry field exists, is reachable, and is not the default. It is the
         door for correcting a mistyped turn or pasting a column from a spreadsheet, and the
         summary says so rather than dressing it up as an alternative interface. --}}
    <details class="mt-6 max-w-3xl">
        <summary class="cursor-pointer text-sm font-semibold text-ink-muted hover:text-ink">
            Correct a turn by hand
        </summary>
        <p class="mt-2 text-sm text-ink-muted">
            Every field at once, for fixing a mistyped turn or importing values. It writes on
            submit and shows no preview, so use the card above for a turn you are logging as
            it happens.
        </p>
        <form method="POST" action="{{ route('runs.turns.store', $run) }}" class="mt-3 grid grid-cols-2 gap-3 rounded-md border border-rule bg-raised p-4 text-sm md:grid-cols-4">
            @csrf
            <label class="flex flex-col gap-1"><span>Turn *</span><input type="number" name="turn" min="1" required value="{{ old('turn', $run->turnEntries->count() + 1) }}" class="rounded-md border border-rule bg-raised text-ink px-2 py-1"></label>
            @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                <label class="flex flex-col gap-1"><span>{{ ucfirst($stat) }} *</span><input type="number" name="{{ $stat }}" min="0" max="1200" required value="{{ old($stat) }}" class="rounded-md border border-rule bg-raised text-ink px-2 py-1"></label>
            @endforeach
            <label class="flex flex-col gap-1"><span>SP</span><input type="number" name="sp" min="0" max="1200" value="{{ old('sp') }}" class="rounded-md border border-rule bg-raised text-ink px-2 py-1"></label>
            <label class="flex flex-col gap-1"><span>Condition</span><input type="text" name="condition" maxlength="255" value="{{ old('condition') }}" class="rounded-md border border-rule bg-raised text-ink px-2 py-1"></label>
            <button type="submit" class="self-end enamel rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome">Add turn</button>
        </form>
    </details>

    <h2 class="mt-10 text-lg font-semibold text-ink-strong">Skills</h2>
    @php
        $groups = ['Suggested', 'Acquired', 'Skipped'];
    @endphp
    @foreach ($groups as $group)
        @php $inGroup = $run->skills->filter(fn ($skill) => $skill->pivot->status === $group); @endphp
        <h3 class="mt-3 text-sm font-semibold text-ink-muted">{{ $group }}</h3>
        @if ($inGroup->isEmpty())
            <p class="text-sm text-ink-muted">None.</p>
        @else
            <ul class="text-sm">
                @foreach ($inGroup as $skill)
                    <li>{{ $skill->name }}@if($skill->pivot->turn_acquired)<span class="text-ink-muted"> · turn {{ $skill->pivot->turn_acquired }}</span>@endif</li>
                @endforeach
            </ul>
        @endif
    @endforeach

    <form method="POST" action="{{ route('runs.skills.sync', $run) }}" class="mt-4 max-w-3xl space-y-3 rounded-md border border-rule bg-raised p-4 text-sm">
        @csrf
        <label class="flex flex-wrap items-center gap-2">
            <span>Skill</span>
            <select name="skills[0][skill_id]" class="rounded-md border border-rule bg-raised text-ink px-2 py-1">
                @foreach ($skills as $skill)
                    <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                @endforeach
            </select>
            <select name="skills[0][status]" class="rounded-md border border-rule bg-raised text-ink px-2 py-1">
                @foreach (\App\Enums\SkillAcquisition::cases() as $acquisition)
                    <option value="{{ $acquisition->value }}">{{ $acquisition->label() }}</option>
                @endforeach
            </select>
            <input type="number" name="skills[0][turn_acquired]" min="1" placeholder="Turn" class="w-20 rounded-md border border-rule bg-raised text-ink px-2 py-1">
        </label>
        <button type="submit" class="enamel rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome">Save skill status</button>
    </form>

    <form method="POST" action="{{ route('runs.destroy', $run) }}" class="mt-10" onsubmit="return confirm('Delete this run and all its turns?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="rounded-full border-2 border-risk px-3 py-1.5 text-sm font-semibold text-risk">Delete run</button>
    </form>
    </section>
</x-layout>
