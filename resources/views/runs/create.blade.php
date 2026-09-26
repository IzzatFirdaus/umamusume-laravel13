<x-layout title="New training run">
    <h1 class="text-2xl font-semibold">New training run</h1>

    <form method="POST" action="{{ route('runs.store') }}" class="mt-6 max-w-lg space-y-4 rounded border border-zinc-200 bg-white p-4 text-sm">
        @csrf

        <label class="block">
            <span class="font-medium">Umamusume</span>
            <select name="umamusume_id" required class="mt-1 w-full rounded border border-zinc-300 px-2 py-1 @error('umamusume_id') border-red-500 @enderror">
                <option value="">Choose…</option>
                @foreach ($umamusumes as $umamusume)
                    <option value="{{ $umamusume->id }}" @selected(old('umamusume_id') == $umamusume->id)>{{ $umamusume->name }}</option>
                @endforeach
            </select>
            @error('umamusume_id')<p class="mt-1 text-red-600">{{ $message }}</p>@enderror
        </label>

        <label class="block">
            <span class="font-medium">Scenario (optional)</span>
            <input type="text" name="scenario" value="{{ old('scenario') }}" maxlength="255" class="mt-1 w-full rounded border border-zinc-300 px-2 py-1">
        </label>

        <label class="block">
            <span class="font-medium">Status</span>
            <select name="status" required class="mt-1 w-full rounded border border-zinc-300 px-2 py-1">
                @foreach (\App\Enums\RunStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(old('status', 'Active') === $status->value)>{{ $status->value }}</option>
                @endforeach
            </select>
        </label>

        <label class="block">
            <span class="font-medium">Notes (optional)</span>
            <textarea name="notes" rows="3" class="mt-1 w-full rounded border border-zinc-300 px-2 py-1">{{ old('notes') }}</textarea>
        </label>

        <button type="submit" class="rounded bg-zinc-900 px-3 py-1.5 text-white">Create run</button>
    </form>
</x-layout>
