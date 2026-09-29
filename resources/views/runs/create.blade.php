{{-- Tokens only: no zinc utility and no `dark:` fork. See catalog/index for the
     reason and the gate this satisfies (D-101, G-18, G-19). --}}
<x-layout title="New training run">
    <h1 class="text-2xl font-semibold text-ink-strong">New training run</h1>

    <form method="POST" action="{{ route('runs.store') }}" class="mt-6 max-w-lg space-y-4 rounded-md border border-rule bg-raised p-4 text-sm">
        @csrf

        {{-- One field, two paths. The native select is the no-script route and the
             combobox is the one a Trainer actually uses. Both carry name="umamusume_id",
             and a display:none control still submits, so the combobox's hidden inputs
             ship DISABLED in the markup and the script flips the pair in one block:
             select off, hidden fields on. Reversed, a scriptless POST would send
             umamusume_id=<pick> then umamusume_id="" and PHP keeps the last one, which
             fails `required` on exactly the path the enhancement exists for.

             What the no-script path cannot carry: `old('umamusume_id')` comes back on that
             path through the select's `@selected()`, which is what keeps the trainee
             sticky without JS. The disabled hidden input does not resubmit
             character_card_id, so a scriptless Trainer who failed on `scenario` returns to
             a run naming the trainee but not the form. That is the honest ceiling for a
             path with no filtering script, not an oversight: character_card_id is nullable
             and Task 5's rule accepts a submission without it. --}}
        <label class="block">
            <span class="font-medium text-ink">Umamusume</span>

            <select name="umamusume_id" required data-combobox-fallback
                    class="mt-1 w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink @error('umamusume_id') border-risk @enderror">
                <option value="">Choose…</option>
                @foreach ($umamusumes as $umamusume)
                    <option value="{{ $umamusume->id }}" @selected(old('umamusume_id') == $umamusume->id)>
                        {{ $umamusume->name }}
                    </option>
                @endforeach
            </select>

            <div data-combobox class="mt-1 hidden">
                {{-- aria-label is explicit because this input is not the first labelable
                     descendant of the <label>, so the implicit association goes to the
                     select and the combobox would otherwise have no accessible name. --}}
                <input type="text" role="combobox" aria-expanded="false" aria-autocomplete="list"
                       aria-controls="trainee-listbox" aria-activedescendant=""
                       aria-label="Trainee or costume card name"
                       autocomplete="off" data-combobox-input
                       class="w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink @error('umamusume_id') border-risk @enderror"
                       value="{{ $selectedLabel }}" placeholder="Trainee or card name">

                <ul id="trainee-listbox" role="listbox" aria-label="Trainees and costume cards"
                    data-combobox-listbox
                    class="mt-1 max-h-72 overflow-y-auto rounded-md border border-rule bg-raised" hidden></ul>

                <p data-combobox-status aria-live="polite" class="mt-1 text-xs text-ink-muted"></p>

                <input type="hidden" name="umamusume_id" data-combobox-umamusume-id disabled
                       value="{{ old('umamusume_id') }}">
                <input type="hidden" name="character_card_id" data-combobox-card-id disabled
                       value="{{ old('character_card_id') }}">
            </div>

            @error('umamusume_id')<p class="mt-1 text-risk">{{ $message }}</p>@enderror
            @error('character_card_id')<p class="mt-1 text-risk">{{ $message }}</p>@enderror
        </label>

        <script type="application/json" id="trainee-roster">@json($rosterJson)</script>

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
