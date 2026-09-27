{{-- Tokens only: no zinc utility and no `dark:` fork. See catalog/index for the
     reason and the gate this satisfies (D-101, G-18, G-19). --}}
<x-layout title="New training run">
    <h1 class="text-2xl font-semibold text-ink-strong">New training run</h1>

    <form method="POST" action="{{ route('runs.store') }}" class="mt-6 max-w-lg space-y-4 rounded-md border border-rule bg-raised p-4 text-sm">
        @csrf

        <label class="block">
            <span class="font-medium text-ink">Umamusume</span>
            <select name="umamusume_id" required class="mt-1 w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink @error('umamusume_id') border-risk @enderror">
                <option value="">Choose…</option>
                @foreach ($umamusumes as $umamusume)
                    <option value="{{ $umamusume->id }}" @selected(old('umamusume_id') == $umamusume->id)>{{ $umamusume->name }}</option>
                @endforeach
            </select>
            @error('umamusume_id')<p class="mt-1 text-risk">{{ $message }}</p>@enderror
        </label>

        {{-- A select over the composition matrix, because StoreTrainingRunRequest
             validates `scenario` against its keys: free text here could only ever
             produce a rejected submission. Leaving it empty is a real option and
             renders the baseline strip. --}}
        <label class="block">
            <span class="font-medium text-ink">Scenario (optional)</span>
            <select name="scenario" class="mt-1 w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                <option value="">Not set (baseline strip)</option>
                @foreach ($scenarios as $key => $label)
                    <option value="{{ $key }}" @selected(old('scenario') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error('scenario')<p class="mt-1 text-risk">{{ $message }}</p>@enderror
        </label>

        <label class="block">
            <span class="font-medium text-ink">Status</span>
            <select name="status" required class="mt-1 w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                @foreach (\App\Enums\RunStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(old('status', 'Active') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </label>

        <label class="block">
            <span class="font-medium text-ink">Notes (optional)</span>
            <textarea name="notes" rows="3" class="mt-1 w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink">{{ old('notes') }}</textarea>
        </label>

        <button type="submit" class="enamel rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome">Create run</button>
    </form>
</x-layout>
