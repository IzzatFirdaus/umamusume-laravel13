@props([
    'skill',
])

{{--
    One row in the trainee detail page's Skills section (WS-2 Task 2.2).

    The row prints only what the source states: the name, whether the skill is this character's
    unique one, and its SP cost, and the name links to the skill's own detail page. **No `turn`
    column**, because a field that is `N/A` on every row
    until the Phase B2 storage decision lands is noise on every row; it arrives with that decision.

    Nothing here ranks or recommends a skill. The `✦ Unique` pill is a classification the source
    publishes (the skill belongs to this character), not a judgement of quality — the Slice 5
    Phase A verdict closed the `best_for` question, and a row that implied "good" would reopen it.
--}}
<li class="flex flex-wrap items-baseline gap-2 border-b border-rule py-1.5 last:border-b-0">
    <a href="{{ route('skills.show', $skill) }}" class="text-ink hover:underline">{{ $skill->name }}</a>

    @if ($skill->is_unique)
        <span
            class="rounded-full bg-sunken px-2 py-0.5 text-xs text-ink-strong"
            title="Marked from the source's skill class code, not from a card's own skill list."
        >✦ Unique</span>
    @endif

    <span class="ml-auto font-mono text-xs tabular-nums text-ink-muted">
        @if ($skill->sp_cost === null)
            {{-- Absence is stated, never a bare dash: the source does not publish a cost for
                 every skill, and an empty cell would read as a rendering accident. --}}
            <span title="The source publishes no SP cost for this skill.">N/A SP</span>
        @else
            {{ $skill->sp_cost }} SP
        @endif
    </span>
</li>
