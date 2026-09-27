<x-layout :title="'Run: '.$run->umamusume->name">
    <div class="flex items-baseline justify-between">
        <h1 class="text-2xl font-semibold">
            {{ $run->umamusume->name }}
            <span class="ml-2 text-sm font-normal text-zinc-500">{{ $run->status->label() }}@if($run->scenario) · {{ $run->scenario }}@endif</span>
        </h1>
        <div class="flex gap-3 text-sm">
            <a href="{{ route('runs.export', ['run' => $run, 'format' => 'csv']) }}" class="hover:underline">Export CSV</a>
            <a href="{{ route('runs.export', ['run' => $run, 'format' => 'json']) }}" class="hover:underline">Export JSON</a>
        </div>
    </div>

    @if ($run->notes)
        <p class="mt-2 text-sm text-zinc-600">{{ $run->notes }}</p>
    @endif

    <h2 class="mt-8 text-lg font-semibold">Turns</h2>
    @if ($run->turnEntries->isEmpty())
        <p class="mt-2 text-sm text-zinc-500">No turns logged yet. Add the first one below.</p>
    @else
        <table class="mt-3 w-full border-collapse text-sm">
            <thead>
                <tr class="border-b border-zinc-300 text-left text-zinc-600">
                    <th class="py-1 pr-3">Turn</th><th class="pr-3">Speed</th><th class="pr-3">Stamina</th>
                    <th class="pr-3">Power</th><th class="pr-3">Guts</th><th class="pr-3">Wit</th>
                    <th class="pr-3">SP</th><th class="pr-3">Condition</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($run->turnEntries as $entry)
                    <tr class="border-b border-zinc-100">
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

    <form method="POST" action="{{ route('runs.turns.store', $run) }}" class="mt-4 grid max-w-3xl grid-cols-2 gap-3 rounded border border-zinc-200 bg-white p-4 text-sm md:grid-cols-4">
        @csrf
        <label class="flex flex-col gap-1"><span>Turn *</span><input type="number" name="turn" min="1" required value="{{ old('turn', $run->turnEntries->count() + 1) }}" class="rounded border border-zinc-300 px-2 py-1"></label>
        @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
            <label class="flex flex-col gap-1"><span>{{ ucfirst($stat) }} *</span><input type="number" name="{{ $stat }}" min="0" max="1200" required value="{{ old($stat) }}" class="rounded border border-zinc-300 px-2 py-1"></label>
        @endforeach
        <label class="flex flex-col gap-1"><span>SP</span><input type="number" name="sp" min="0" max="1200" value="{{ old('sp') }}" class="rounded border border-zinc-300 px-2 py-1"></label>
        <label class="flex flex-col gap-1"><span>Condition</span><input type="text" name="condition" maxlength="255" value="{{ old('condition') }}" class="rounded border border-zinc-300 px-2 py-1"></label>
        <button type="submit" class="self-end rounded bg-zinc-900 px-3 py-1.5 text-white">Add turn</button>
    </form>
    @if ($errors->any())
        <ul class="mt-2 max-w-3xl list-disc pl-6 text-sm text-red-600">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <h2 class="mt-10 text-lg font-semibold">Skills</h2>
    @php
        $groups = ['Suggested', 'Acquired', 'Skipped'];
    @endphp
    @foreach ($groups as $group)
        @php $inGroup = $run->skills->filter(fn ($skill) => $skill->pivot->status === $group); @endphp
        <h3 class="mt-3 text-sm font-medium text-zinc-600">{{ $group }}</h3>
        @if ($inGroup->isEmpty())
            <p class="text-sm text-zinc-500">None.</p>
        @else
            <ul class="text-sm">
                @foreach ($inGroup as $skill)
                    <li>{{ $skill->name }}@if($skill->pivot->turn_acquired)<span class="text-zinc-500"> · turn {{ $skill->pivot->turn_acquired }}</span>@endif</li>
                @endforeach
            </ul>
        @endif
    @endforeach

    <form method="POST" action="{{ route('runs.skills.sync', $run) }}" class="mt-4 max-w-3xl space-y-3 rounded border border-zinc-200 bg-white p-4 text-sm">
        @csrf
        <label class="flex flex-wrap items-center gap-2">
            <span>Skill</span>
            <select name="skills[0][skill_id]" class="rounded border border-zinc-300 px-2 py-1">
                @foreach ($skills as $skill)
                    <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                @endforeach
            </select>
            <select name="skills[0][status]" class="rounded border border-zinc-300 px-2 py-1">
                @foreach (\App\Enums\SkillAcquisition::cases() as $acquisition)
                    <option value="{{ $acquisition->value }}">{{ $acquisition->label() }}</option>
                @endforeach
            </select>
            <input type="number" name="skills[0][turn_acquired]" min="1" placeholder="Turn" class="w-20 rounded border border-zinc-300 px-2 py-1">
        </label>
        <button type="submit" class="rounded bg-zinc-900 px-3 py-1.5 text-white">Save skill status</button>
    </form>

    <form method="POST" action="{{ route('runs.destroy', $run) }}" class="mt-10" onsubmit="return confirm('Delete this run and all its turns?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="rounded border border-red-300 px-3 py-1.5 text-sm text-red-700">Delete run</button>
    </form>
</x-layout>
