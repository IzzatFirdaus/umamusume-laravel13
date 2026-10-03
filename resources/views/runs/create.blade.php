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
        <div class="block">
            {{-- A <label> may name exactly one labelable control, so it wraps the select and
                 points at it by id, and the combobox block sits outside it. A label holding two
                 labelable controls resolves to the first, which is the control this script
                 disables, so wrapping both made the caption a click that goes nowhere on the
                 path the script runs on. --}}
            {{-- "Trainee" rather than "Umamusume" because it is the caption both paths can honour:
                 the no-script select lists trainees only, and the combobox commits a trainee with
                 her card. It is also the string the field's own copy already uses ("No trainee or
                 card found", "Trainees and costume cards"), and the accessible name below begins
                 with it, which is what lets a speech-input user say the words on screen and land
                 here (WCAG 2.5.3).

                 The `*` is this repository's own required marker, already on "Trainee *" here and on
                 "Turn *" and "<Stat> *" on the run page. It marks the two mandatory fields because a
                 native `required` shows a sighted Trainer nothing until the submit fails, while
                 "(optional)" on the other two marks what a default already answers for. --}}
            <label for="umamusume-select" class="block">
                <span class="font-medium text-ink">Trainee *</span>

                <select id="umamusume-select" name="umamusume_id" required data-combobox-fallback
                        class="mt-1 w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink @error('umamusume_id') border-risk @enderror">
                    <option value="">Choose…</option>
                    @foreach ($umamusumes as $umamusume)
                        <option value="{{ $umamusume->id }}" @selected(old('umamusume_id') == $umamusume->id)>
                            {{ $umamusume->name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <div data-combobox class="mt-1 hidden">
                {{-- aria-label is explicit because this input is not inside the <label> above
                     at all: a label names one control, and the caption belongs to the select.
                     Without the attribute the combobox would have no accessible name. The `id` is
                     the other half: the script moves the caption's `for` onto this input when it
                     disables the select, so clicking the word "Trainee" focuses the control the
                     Trainer is actually using. aria-label still wins for the accessible name, so
                     the caption buys the pointer without renaming the field.

                     `aria-required` and not `required`: the value that submits is the hidden pair,
                     not this text. `required` here would gate on the label the Trainer sees typed,
                     which is a different string from the one posted, and a hidden-but-rendered
                     control still blocks the submit. The state a screen reader has to hear before
                     it wastes a submission is what this attribute carries. --}}
                <input type="text" id="trainee-combobox" role="combobox" aria-expanded="false" aria-autocomplete="list"
                       aria-controls="trainee-listbox" aria-activedescendant=""
                       aria-label="Trainee or costume card name" aria-required="true"
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
        </div>

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
            <span class="font-medium text-ink">Status *</span>
            <select name="status" required class="mt-1 w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                @foreach (\App\Enums\RunStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(old('status', 'Active') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </label>

        {{-- C-5: four fields the request validates and the model persists, surfaced here
             because the run page already reads them back (the inheritance row, the Grade
             Point period in the meter, the shop panel's countdown). A Trainer who wants to
             log them must currently create the run then hand-edit the row, which is what
             the audit named a workflow gap. They are all nullable in
             `StoreTrainingRunRequest`, so leaving any one empty keeps the same shape the
             form produced before this block existed. --}}

        <label class="block">
            <span class="font-medium text-ink">Inheritance parent A (optional)</span>
            <select name="inheritance_parent_a_id" class="mt-1 w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                <option value="">Not set</option>
                @foreach ($umamusumes as $umamusume)
                    <option value="{{ $umamusume->id }}" @selected(old('inheritance_parent_a_id') == $umamusume->id)>
                        {{ $umamusume->name }}
                    </option>
                @endforeach
            </select>
            @error('inheritance_parent_a_id')<p class="mt-1 text-risk">{{ $message }}</p>@enderror
        </label>

        <label class="block">
            <span class="font-medium text-ink">Inheritance parent B (optional)</span>
            <select name="inheritance_parent_b_id" class="mt-1 w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                <option value="">Not set</option>
                @foreach ($umamusumes as $umamusume)
                    <option value="{{ $umamusume->id }}" @selected(old('inheritance_parent_b_id') == $umamusume->id)>
                        {{ $umamusume->name }}
                    </option>
                @endforeach
            </select>
            @error('inheritance_parent_b_id')<p class="mt-1 text-risk">{{ $message }}</p>@enderror
        </label>

        {{-- The Grade Point period is entered, never derived (D-270). A scenario that does
             not compose the grade_objectives panel will be rejected by the request's own
             closure, so a Trainer who picks period 3 on URA Finals sees the message there
             rather than the value silently dropped. --}}
        <label class="block">
            <span class="font-medium text-ink">Current Grade Point period (optional)</span>
            <input type="number" name="current_objective_index" min="1" max="{{ \App\Models\RaceEntry::MAX_OBJECTIVE_INDEX }}"
                   value="{{ old('current_objective_index') }}"
                   class="mt-1 w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink @error('current_objective_index') border-risk @enderror">
            <span class="mt-1 block text-xs text-ink-muted">
                1 to {{ \App\Models\RaceEntry::MAX_OBJECTIVE_INDEX }}, tracked in the Grade Point meter on the run page. Only scenarios that compose the Grade Point panel accept a value here.
            </span>
            @error('current_objective_index')<p class="mt-1 text-risk">{{ $message }}</p>@enderror
        </label>

        <label class="block">
            <span class="font-medium text-ink">Shop resets in (optional)</span>
            <input type="number" name="shop_resets_in" min="0" value="{{ old('shop_resets_in') }}"
                   class="mt-1 w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink @error('shop_resets_in') border-risk @enderror">
            <span class="mt-1 block text-xs text-ink-muted">
                Turns until the shop rotation as you read it. The scenario's own upper bound applies at the model layer.
            </span>
            @error('shop_resets_in')<p class="mt-1 text-risk">{{ $message }}</p>@enderror
        </label>

        <label class="block">
            <span class="font-medium text-ink">Notes (optional)</span>
            <textarea name="notes" rows="3" class="mt-1 w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink">{{ old('notes') }}</textarea>
        </label>

        <button type="submit" class="enamel rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome">Create run</button>
    </form>
</x-layout>
