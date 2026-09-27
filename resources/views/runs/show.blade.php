<x-layout :title="'Run: '.$run->umamusume->name">
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

    {{-- Composed from the run's scenario (D-220). A run that names no scenario
         resolves to the baseline strip rather than to no strip at all, and a value
         the run has not recorded renders as unrecorded rather than as a default. --}}
    <h2 class="mt-8 text-lg font-semibold text-ink-strong">Resources</h2>
    <x-resource-strip :scenario="$run->scenarioKey()" :run="$run->stripValues()" class="mt-3" />

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
    --}}
    @php $panelScenario = $run->scenarioKey(); @endphp
    <x-race-calendar :scenario="$panelScenario" :cells="$run->calendarCells()" class="mt-3" />
    <x-grade-point-meter
        :scenario="$panelScenario"
        :objectives="$run->gradeObjectives()"
        :earned="$run->gradeEarned()"
        class="mt-3"
    />

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
                <option value="">Not set — baseline strip</option>
                @foreach ($scenarios as $key => $label)
                    <option value="{{ $key }}" @selected($run->scenario === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit" class="rounded-full border-2 border-rule px-4 py-2 font-bold text-ink-strong">Change scenario</button>
        @error('scenario')<p class="w-full text-risk">{{ $message }}</p>@enderror
    </form>

    <h2 class="mt-8 text-lg font-semibold text-ink-strong">Turns</h2>
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
                    <tr class="border-b border-rule">
                        <td class="py-1 pr-3">{{ $entry->turn }}</td>
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

    <form method="POST" action="{{ route('runs.turns.store', $run) }}" class="mt-4 grid max-w-3xl grid-cols-2 gap-3 rounded-md border border-rule bg-raised p-4 text-sm md:grid-cols-4">
        @csrf
        <label class="flex flex-col gap-1"><span>Turn *</span><input type="number" name="turn" min="1" required value="{{ old('turn', $run->turnEntries->count() + 1) }}" class="rounded-md border border-rule bg-raised text-ink px-2 py-1"></label>
        @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
            <label class="flex flex-col gap-1"><span>{{ ucfirst($stat) }} *</span><input type="number" name="{{ $stat }}" min="0" max="1200" required value="{{ old($stat) }}" class="rounded-md border border-rule bg-raised text-ink px-2 py-1"></label>
        @endforeach
        <label class="flex flex-col gap-1"><span>SP</span><input type="number" name="sp" min="0" max="1200" value="{{ old('sp') }}" class="rounded-md border border-rule bg-raised text-ink px-2 py-1"></label>
        <label class="flex flex-col gap-1"><span>Condition</span><input type="text" name="condition" maxlength="255" value="{{ old('condition') }}" class="rounded-md border border-rule bg-raised text-ink px-2 py-1"></label>
        <button type="submit" class="self-end enamel rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome">Add turn</button>
    </form>
    @if ($errors->any())
        <ul class="mt-2 max-w-3xl list-disc pl-6 text-sm text-risk">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

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
</x-layout>
