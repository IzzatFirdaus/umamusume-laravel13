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
    @php
    /*
     * The ceiling each stat input will accept, from the same owner that answers the
     * POST. These forms used to hardcode max="1200", which meant a Trackblazer Trainer
     * could not type the 1,900 Stamina the band showed as this scenario's own ceiling -
     * the browser refused it before the request was ever sent (ADR-0015). SP keeps no
     * max at all: no source states an SP ceiling, and the field used to carry a
     * recycled 1200 that contradicted the rule the server applies.
     */
    $statCaps = \App\Services\ScenarioCaps::forRun($run);
@endphp

<div class="flex items-baseline justify-between">
        <h1 class="text-2xl font-semibold text-ink-strong">
            {{ $run->umamusume->name }}
            {{-- The matrix label, not the raw slug, and "not set" rather than a
                 scenario name when the run has none: a run with no scenario does
                 not declare itself to be the baseline scenario. The absence is read
                 from `hasScenario()` rather than re-tested here, because a third
                 spelling of "is a scenario set" is how a blank column slipped past
                 two of them and rendered an empty caption. --}}
            <span class="ml-2 text-sm font-normal text-ink-muted">
                {{ $run->status->label() }} ·
                {{ $run->hasScenario() ? ($scenarios[$run->scenario] ?? $run->scenario) : 'No scenario set' }}
            </span>
        </h1>
        <div class="flex gap-3 text-sm">
            <a href="{{ route('runs.export', ['run' => $run, 'format' => 'csv']) }}" class="inline-flex min-h-11 items-center hover:underline">Export CSV</a>
            <a href="{{ route('runs.export', ['run' => $run, 'format' => 'json']) }}" class="inline-flex min-h-11 items-center hover:underline">Export JSON</a>
        </div>
        {{-- Provenance stated rather than implied (ADR-0017): a run that arrived by file is not the
             Trainer's own typing, so the gaps in it belong to the sheet rather than to their memory.
             A typed run says nothing, which is the honest default. --}}
        @if ($run->imported_at)
            <p class="mt-1 text-xs text-ink-muted">
                Imported {{ $run->imported_at->timezone(config('uma.display_timezone'))->format('M j, Y') }} from {{ $run->import_source ?: 'a CSV' }}
            </p>
        @endif
    </div>

    @if ($run->notes)
        <p class="mt-2 text-sm text-ink-muted">{{ $run->notes }}</p>
    @endif

    {{-- The pinned region is the Resources strip alone (O-2). Pinning the whole state block
         took two thirds of the viewport at 1024x720 and 1280x800, leaving the forms below the
         remainder; Stats scrolls with the log, and Mood sits with the strip values because it
         is 25px and belongs with them (O-4). The strip is the part D-170 names that benefits
         from staying in view: turn, Energy, fans and the Unity Cup counters.

         `lg:contents` at the breakpoint removes this wrapper's box so the sticky strip's
         containing block is the page rather than the short state block, which is what lets it
         stay pinned over the log. Below `lg` the wrapper is an ordinary block and nothing is
         sticky. The section stays in the DOM, so the frame pins' containment still holds. --}}
    <section aria-label="Run state" class="bg-page lg:contents">
        <section aria-label="Resources" class="bg-page lg:sticky lg:top-0 lg:z-10 lg:py-2">
            {{-- Composed from the run's scenario (D-220). A run that names no scenario
                 resolves to the baseline strip rather than to no strip at all, and a value
                 the run has not recorded renders as unrecorded rather than as a default. --}}
            <h2 class="text-lg font-semibold text-ink-strong">Resources</h2>
            <x-resource-strip :scenario="$run->scenarioKey()" :declared="$run->hasScenario()" :run="$run->stripValues()" class="mt-3" />

            {{-- Mood is the trainee's state, not a turn's detail, and at 25px it belongs with
                 the strip values rather than in a section of its own (O-4). It reads the latest
                 logged turn for the same reason the band does. A turn that stored no tier says
                 "not recorded" rather than defaulting to NORMAL, which would be a claim about a
                 trainee nobody asked about (D-220). --}}
            @if ($band !== null)
                <div class="mt-2 flex flex-wrap items-baseline gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Mood</span>
                    <x-mood-pill :tier="$currentMood" unrecorded="not recorded" />
                </div>
            @endif
        </section>

        {{-- The stat band is the trainee's current numbers, so it belongs beside the run's
             current resources and above anything that talks about a single turn. It reads the
             latest logged turn: the run's state is what the last turn ended at, not an average
             of the log. No logged turn means no band at all, because five zeroes would be a
             claim about a trainee nobody entered (D-220). It scrolls with the log rather than
             pinning (O-2). --}}
        @if ($band !== null)
            <section aria-label="Stats">
                <h2 class="mt-6 text-lg font-semibold text-ink-strong">Stats</h2>
                <x-stat-band
                    :scenario="$band['scenario']"
                    :caps="$band['caps']"
                    :values="$band['values']"
                    :skill-points="$band['skillPoints']"
                    class="mt-3"
                />
            </section>
        @endif
    </section>

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

        It is its own section so a reader asking for the region of the calendar's own
        heading gets the calendar rather than the turn-log wrapper (O-4).
    --}}
    @if ($run->hasScenario())
        <section aria-label="Race calendar" class="mt-2">
            @php
                $panelScenario = $run->scenarioKey();
                // The year is address state, not view state: a Trainer should be able
                // to link "her Classic spring", and back should not drop the tab.
                // Clamped because the query string is user input, in one place the race
                // panel reads the same way.
                $calendarYear = $run->careerYearForTab(request('year'));
                // The turn being decided carries its own year, so the outline belongs to
                // whichever tab holds it rather than to the year of the last logged turn.
                $nextTurn = $run->nextTurnToPlay();
            @endphp
            <x-race-calendar
                :scenario="$panelScenario"
                :cells="$run->calendarCells($calendarYear)"
                :year="$calendarYear"
                :next-turn="$nextTurn !== null && $calendarYear === $nextTurn['year'] ? $nextTurn['turn'] : null"
                class="mt-3" />
            <x-grade-point-meter
                :scenario="$panelScenario"
                :objectives="$run->gradeObjectives()"
                :current="$run->currentPeriodPosition()"
                :earned="$run->gradeEarned()"
                :unpriced-count="$run->gradeUnpricedCount()"
                :periods="$run->gradePeriods()"
                :unassigned-count="$run->gradeUnassignedCount()"
                class="mt-3"
            />
            {{-- Self-gating on `panels.shop`, which only Trackblazer opens. It sits with the
                 other scenario panels rather than with the turn log because it describes the
                 run's resources, not one turn. --}}
            <x-shop-panel :run="$run" class="mt-3" />
            {{-- Every panel below self-gates on the composition matrix, so a scenario that
                 does not open a mechanic renders nothing rather than an empty frame: these
                 are Trackblazer and Unity Cup surfaces, and an URA run must not show either
                 (D-221, D-241, gate G-34). --}}
            <x-race-panel :run="$run" :slots="$raceSlots" :entry-mode="$entryMode" class="mt-3" />
            <x-team-rank-gauge :run="$run" class="mt-3" />
            <x-spirit-burst-roster :run="$run" class="mt-3" />
            <x-team-race-panel :run="$run" class="mt-3" />
            <x-epithet-checklist :run="$run" class="mt-3" />
            <x-race-fatigue-chip :run="$run" class="mt-3" />
        </section>
    @endif

    {{-- The log: everything that grows with the run, scrolling under the pinned state. --}}
    <section aria-label="Turn log" class="mt-2">

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

    @if ($run->composesGradeObjectives())
        {{-- Its own form, because its own verb: this one says which deadline the Trainer
             is working to, and a button reading "Change scenario" would promise something
             else. The fields it is not editing are carried through so the shared request
             does not blank them. The period is entered, never inferred (D-270), and
             "not reported" is a real option rather than a placeholder: the meter renders
             it as no period, not as zero (D-220). --}}
        <form method="POST" action="{{ route('runs.update', $run) }}" class="mt-3 flex max-w-3xl flex-wrap items-end gap-3 rounded-md border border-rule bg-raised p-4 text-sm">
            @csrf
            @method('PUT')
            <input type="hidden" name="umamusume_id" value="{{ $run->umamusume_id }}">
            <input type="hidden" name="status" value="{{ $run->status->value }}">
            <input type="hidden" name="scenario" value="{{ $run->scenario }}">
            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">Grade Point period</span>
                <select name="current_objective_index" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                    <option value="">Not reported</option>
                    @foreach ($run->gradeObjectives() as $objective)
                        <option value="{{ $objective['index'] }}"
                            @selected($run->current_objective_index === $objective['index'])>
                            {{ $objective['index'] }}. {{ $objective['name'] }}
                        </option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="rounded-full border-2 border-rule px-4 py-2 font-bold text-ink-strong">Report period</button>
            @error('current_objective_index')<p class="w-full text-risk">{{ $message }}</p>@enderror
        </form>
    @endif

    {{-- The deck sits with the run's own record rather than inside the `hasScenario()` cluster: every
         scenario has six support card slots, so a run that names no scenario still had a deck, and
         gating it would hide the one piece of setup that explains the turns below. --}}
    <h2 class="mt-8 text-lg font-semibold text-ink-strong">Support deck</h2>
    <x-deck-panel :run="$run" :cards="$deckCards" class="mt-3" />

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
        {{-- Nine columns do not fit a phone. The table scrolls rather than reflows, and the
             wrapper is focusable because a scroll container nobody can tab into has no focus
             for the arrow keys to scroll — the clipped columns would be trackpad-only (KI-25).
             Same shape as the race calendar's region, one convention for both. --}}
        <div class="mt-3 overflow-x-auto" role="region" tabindex="0" aria-label="Turn log">
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="border-b border-rule text-left text-ink-muted">
                        <th class="py-1 pr-3">Turn</th><th class="pr-3">Speed</th><th class="pr-3">Stamina</th>
                        <th class="pr-3">Power</th><th class="pr-3">Guts</th><th class="pr-3">Wit</th>
                        <th class="pr-3">SP</th><th class="pr-3">Condition</th><th class="pr-3">Mood</th>
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
                            <td class="pr-3"><x-mood-pill :tier="$entry->mood" unrecorded="not recorded" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
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
        :declared="$run->hasScenario()"
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
                    <input type="number" name="{{ $stat }}" min="0" max="{{ $statCaps[ucfirst($stat)] }}" required
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
                <select name="mood" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                    <option value="">not yet recorded</option>
                    @foreach (\App\Enums\MoodTier::cases() as $tier)
                        <option value="{{ $tier->value }}"
                                @selected(($guided['values']['mood'] ?? null) === $tier->value
                                    || (($guided['values']['mood'] ?? null) === null && $guided['mood'] === $tier->value))>
                            {{ $tier->value }} {{ $tier->arrow() }}
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

    {{-- One envelope for both kinds of error: the `previewed` request-stage marker renders as a
         list item like any per-field error does, because one treatment is easier to read than two
         (static review F-04). `$errors->all()` already carries the previewed message alongside the
         field messages. --}}
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
        <summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-muted hover:text-ink">
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
                <label class="flex flex-col gap-1"><span>{{ ucfirst($stat) }} *</span><input type="number" name="{{ $stat }}" min="0" max="{{ $statCaps[ucfirst($stat)] }}" required value="{{ old($stat) }}" class="rounded-md border border-rule bg-raised text-ink px-2 py-1"></label>
            @endforeach
            <label class="flex flex-col gap-1"><span>SP</span><input type="number" name="sp" min="0" value="{{ old('sp') }}" class="rounded-md border border-rule bg-raised text-ink px-2 py-1"></label>
            <label class="flex flex-col gap-1"><span>Condition</span><input type="text" name="condition" maxlength="255" value="{{ old('condition') }}" class="rounded-md border border-rule bg-raised text-ink px-2 py-1"></label>
            <button type="submit" class="self-end enamel rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome">Add turn</button>
        </form>
    </details>

    <section aria-label="Skills">
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
                    <li>
                        {{ $skill->name }}
                        @if ($skill->is_unique)
                            {{-- The sparkle is the client's own mark for a unique skill and means nothing
                                 else in this system (DESIGN.md §6.0, §6.11), so it appears here and never
                                 as ornament. The word rides beside it because D-12 bars a state that lives
                                 only in a glyph or a colour. --}}
                            <span class="text-ink-muted">✦ Unique</span>
                        @endif
                        @if ($skill->sp_cost !== null)
                            <span class="text-ink-muted">· {{ $skill->sp_cost }} SP</span>
                        @endif
                        @if ($skill->pivot->turn_acquired)
                            <span class="text-ink-muted">· turn {{ $skill->pivot->turn_acquired }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    @endforeach

    {{-- Two absences, said out loud rather than left to be inferred. SP is the cost the source states;
         the class code the source carries is not rendered because six codes do not answer to the
         client's three rarities (ADR-0011 §3), and no hint-level discount appears anywhere because
         conflict row 16 bars encoding either candidate curve without an in-client check. --}}
    <p class="mt-2 max-w-3xl text-xs text-ink-muted">
        SP is the cost the source states for that skill. Skill-point discounts from hint levels are not
        shown: no source in this repository settles the per-level reduction.
        {{-- G-SK-13: this select lists every Global row, which is the reason Screen D exists. Linking out
             is the honest statement that the list is too long to scan, and it costs no new mechanism. --}}
            <a href="{{ route('skills.index') }}" class="inline-flex min-h-11 items-center underline">Search the skill catalog</a>
            to narrow that list before choosing here.
    </p>

    {{-- One row per skill the run already carries, plus a spare row to add another (KI-33's
         repeater). The form used to hard-index `skills[0]`, which was fine while the table held ten
         names and a run held one or two skills; a build pre-populated with four or five rows from a
         card cannot be *edited* in a one-row form, and editing is the job the pre-populate exists
         to serve. `syncSkills` upserts and never detaches, so the rows below describe what is on
         the run today and the spare is where the next one goes. --}}
    @php
        $skillRows = $run->skills->sortBy('name')->values();
        $rowTotal = max(1, $skillRows->count() + 1);
    @endphp

    <form method="POST" action="{{ route('runs.skills.sync', $run) }}" class="mt-4 max-w-3xl space-y-3 rounded-md border border-rule bg-raised p-4 text-sm">
        @csrf

        {{-- KI-37, measured in a browser: `px-2 py-1` with no height put the two selects at 31, the turn
             input at 30 and the submit at 32 against `docs/design-research/DESIGN.md` §6.14's 44. The four
             now carry `h-11`, this repository's idiom for the value (the scenario panels' capsule headers),
             and the three field controls take Screen D's focus ring (`skills/index.blade.php`:31). The turn
             input also gains `step="1"`: §6.14's last bullet asks number inputs to carry steppers, and the
             browser review confirmed the attribute was absent (`step` was `null`). --}}

        @if ($skills->isEmpty())
            {{-- KI-51's UI consequence: on a fresh clone no tracked writer produces an offerable skill
                 (the tracked seeder sets neither `release_status` nor `name_is_client`), so this is the
                 ordinary first state rather than an error. The two commands are Screen D's own
                 (`skills/index.blade.php`:89-92), named here rather than re-worded. --}}
            <p class="text-xs text-ink-muted">
                No skills are available to choose yet. Run `php artisan uma:fetch gametora-skills` to fill
                the catalogue, or `php artisan uma:reparse gametora-skills` if a fetch says the document is
                unchanged.
            </p>
        @endif

        @for ($row = 0; $row < $rowTotal; $row++)
            @php
                $entry = $skillRows->get($row);
                // Rehydrated from `old()` so a refused submit does not discard the edit the Trainer
                // just made. The deck form has done this since D-3, on the same reasoning: the rail
                // and this form disagreed about what to do with input the server had refused.
                //
                // The accessors are dotted, and have to be. `old()` resolves through `Arr::get`,
                // which splits on dots and treats `skills[0][skill_id]` as one literal key that the
                // nested `_old_input` array does not contain, so the bracketed spelling returns the
                // default and the form renders the stored value with no visible cause. The `name`
                // attributes stay bracketed; only the lookup is dotted. The cast to string is D-3's
                // too, because `old()` hands back a string and the pivot hands back an int, and a
                // row that keeps its stored value has to keep rendering too.
                $selectedSkillId = old("skills.{$row}.skill_id", $entry?->id);
                $selectedStatus = old("skills.{$row}.status", $entry?->pivot->status);
                $selectedTurn = old("skills.{$row}.turn_acquired", $entry?->pivot->turn_acquired);
                // `@error` compiles its argument to a single-quoted PHP string, so a key written as
                // "skills.{$row}.turn_acquired" reaches the error bag as that literal and matches
                // nothing. The keys are built here, where the file is still plain PHP, and the
                // directive is handed the finished string.
                $skillIdError = "skills.{$row}.skill_id";
                $statusError = "skills.{$row}.status";
                $turnError = "skills.{$row}.turn_acquired";
            @endphp
            {{-- KI-36. Each control gets its own `<label for>`: the wrapper label used to name the
                 group, which left the status select and the turn input with no accessible name at
                 all, and a placeholder is not a name — it disappears exactly when the field has a
                 value, which is every row on a pre-populated run. --}}
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex flex-col gap-1">
                    <label for="skill-{{ $row }}-id" class="text-ink-muted">Skill</label>
                    <select id="skill-{{ $row }}-id" name="skills[{{ $row }}][skill_id]" class="h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green">
                        <option value="">Choose a skill</option>
                        @foreach ($skills as $skill)
                            {{-- The cost is in the label because the choice being made is a spending
                                 choice: a Trainer planning a build picks partly on what the skill
                                 costs, and the catalogue has been stating that figure since the
                                 import (FR-D-1). --}}
                            <option value="{{ $skill->id }}" @selected((string) $selectedSkillId === (string) $skill->id)>{{ $skill->name }}@if ($skill->sp_cost !== null) · {{ $skill->sp_cost }} SP @endif</option>
                        @endforeach
                    </select>
                    @error($skillIdError)
                        <p class="text-risk">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-1">
                    <label for="skill-{{ $row }}-status" class="text-ink-muted">Acquisition status</label>
                    <select id="skill-{{ $row }}-status" name="skills[{{ $row }}][status]" class="h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green">
                        @foreach (\App\Enums\SkillAcquisition::cases() as $acquisition)
                            <option value="{{ $acquisition->value }}" @selected((string) $selectedStatus === (string) $acquisition->value)>{{ $acquisition->label() }}</option>
                        @endforeach
                    </select>
                    @error($statusError)
                        <p class="text-risk">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-1">
                    <label for="skill-{{ $row }}-turn" class="text-ink-muted">Turn acquired</label>
                    <input type="number" id="skill-{{ $row }}-turn" name="skills[{{ $row }}][turn_acquired]" min="1" step="1"
                           value="{{ $selectedTurn }}" placeholder="Turn"
                           class="h-11 w-20 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green">
                    @error($turnError)
                        <p class="text-risk">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        @endfor

        <button type="submit" class="enamel h-11 rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome">Save skill status</button>
    </form>
    </section>

    </section>

    {{-- Confirmation is a disclosure, not a script: ADR-0007 records the no-script posture, and
         native confirm() was the route's last inline handler. The summary is the prompt; the
         form inside submits the DELETE only after the Trainer opens it. It sits outside the
         turn-log section so the scrolling region keeps its one disclosure, the escape hatch
         (RunViewFrameTest pins the count). --}}
    <details class="mt-10">
        <summary class="flex min-h-11 cursor-pointer items-center rounded-full border-2 border-risk px-3 text-sm font-semibold text-risk">
            Delete run
        </summary>
        <p class="mt-2 text-sm text-ink-muted">
            This removes the run and every turn logged for it. The action is permanent.
        </p>
        <form method="POST" action="{{ route('runs.destroy', $run) }}" class="mt-2">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-full border-2 border-risk px-3 py-1.5 text-sm font-semibold text-risk">Delete this run</button>
        </form>
    </details>
</x-layout>
