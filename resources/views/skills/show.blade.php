{{-- The detail page for one skill on Screen D (PRD FR-D-2 family).

     Tokens only, no `dark:` fork and no zinc utility, the same constraint
     catalog/index records (D-101, G-18, G-19). D-30's permitted Skill surface is name,
     name_ja, sp_cost, type, is_unique, so those are the fields the page can print; the
     export id never reaches the screen and `rarity` is never dressed as a client word
     (ADR-0011 §3). The mechanics the source states render verbatim as its own engine
     expressions and effect codes, and the absences it does not state stay in words (D-220)
     rather than as empty slots. The holder lists read the card-grain `skills_unique`,
     `skills_innate`, `skills_awakening` and `skills_event` arrays in reverse: the mirror of
     the grouping use the 2026-10-02 D-30 amendment admits, the owner brief's order, with the
     widening ask travelling in `docs/research-scratch/PLANS-AND-BRIEFS.md`. Unconfirmed forms
     sit behind the same disclosure the catalog list applies, so a form it hides holds nothing
     here. `skills_evo` is stored and **deliberately not rendered**: 421 of its 1,250 ids name a
     `[Global]`-released skill and the detail route refuses a row `availableOnGlobal()` rejects,
     so rendering that list would emit 404s on 829 of the links. --}}
<x-layout :title="$skill->name">
    <div class="flex items-baseline justify-between">
        <h1 class="text-2xl font-semibold text-ink-strong">{{ $skill->name }}</h1>
        <a href="{{ route('skills.index') }}" class="text-sm text-ink-muted hover:underline">Back to skill search</a>
    </div>

    {{-- The index's dedupe rule carried over: 18 of the 623 [Global] rows store the same
         string in both name columns, and printing the pair repeats the name. --}}
    @if ($skill->name_ja !== null && $skill->name_ja !== $skill->name)
        <p class="mt-1 text-sm text-ink-muted">{{ $skill->name_ja }}</p>
    @endif

    <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-3 rounded-md border border-rule bg-raised p-4 text-sm md:grid-cols-3">
        <div>
            <dt class="text-ink-muted">Type</dt>
            <dd class="text-ink">
                @if ($skill->type)
                    {{ $skill->type }}
                @else
                    <span title="The source's effect codes give this skill no type word.">N/A</span>
                @endif
            </dd>
        </div>

        <div>
            <dt class="text-ink-muted">SP cost</dt>
            <dd class="text-ink">
                @if ($skill->sp_cost !== null)
                    {{ $skill->sp_cost }} SP
                @else
                    <span title="The source states no SP cost for this class of skill.">N/A</span>
                @endif
            </dd>
        </div>

        <div>
            <dt class="text-ink-muted">Unique</dt>
            <dd class="text-ink">
                @if ($skill->is_unique)
                    <span
                        class="rounded-full bg-sunken px-2 py-0.5 text-xs text-ink-strong"
                        title="Marked from the source's skill class code, not from a card's own skill list."
                    >✦ Unique</span>
                @else
                    No
                @endif
            </dd>
        </div>
    </dl>

    {{-- The mechanics the source states, then the absences it does not. The activation predicate
         and effect vector are rendered verbatim as the engine's own expressions and codes: they are
         not client copy, and no effect word is derived beyond the type word above (D-20, D-256).
         Rendering them is beyond D-30's current Skill entry; the widening ask is filed in
         `docs/research-scratch/PLANS-AND-BRIEFS.md`. The description stays absent: `ADR-0011`
         declined it and no client-string source exists (G-SK-20). --}}
    <section class="mt-8" aria-labelledby="mechanics">
        <h2 id="mechanics" class="text-lg font-semibold text-ink-strong">Mechanics</h2>

        @if ($skill->condition_groups !== null && $skill->condition_groups !== [])
            @foreach ($skill->condition_groups as $group)
                <div class="mt-3 rounded-md border border-rule bg-raised p-3 text-sm">
                    @if (count($skill->condition_groups) > 1)
                        <h3 class="text-xs font-bold uppercase tracking-widest text-ink-muted">Activation {{ $loop->iteration }}</h3>
                    @endif

                    <dl class="grid gap-x-6 gap-y-2 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <dt class="text-ink-muted">Condition</dt>
                            <dd class="font-mono text-xs text-ink">{{ $group['condition'] ?? 'N/A' }}</dd>
                        </div>

                        @if (($group['precondition'] ?? null) !== null)
                            <div class="md:col-span-2">
                                <dt class="text-ink-muted">Precondition</dt>
                                <dd class="font-mono text-xs text-ink">{{ $group['precondition'] }}</dd>
                            </div>
                        @endif

                        @if (($group['base_time'] ?? null) !== null)
                            <div>
                                <dt class="text-ink-muted">Base time</dt>
                                <dd class="text-ink">{{ $group['base_time'] }} ms</dd>
                            </div>
                        @endif
                    </dl>

                    @if (($group['effects'] ?? []) !== [])
                        <ul class="mt-2 flex flex-wrap gap-2 text-xs">
                            @foreach ($group['effects'] as $effect)
                                <li class="rounded-md bg-sunken px-2 py-1 font-mono text-ink">
                                    effect {{ $effect['type'] }}: {{ $effect['value'] > 0 ? '+' : '' }}{{ $effect['value'] }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach

            <p class="mt-2 text-xs text-ink-muted">
                The expressions and effect codes above are the source's own engine data, not client
                labels. The description is not recorded: no client-string source for it exists
                (`ADR-0011`, G-SK-20), and no awakening threshold is kept either.
            </p>
        @else
            <p class="mt-2 text-sm text-ink-muted">
                Activation conditions, effect values and distance or style qualifiers are not
                recorded. This tool's skills source stores no description it may render, which
                `ADR-0011` records, and no awakening thresholds are kept.
            </p>
        @endif
    </section>

    {{-- The holder lists keep the card's own grouping, because the lists answer different
         questions: the unique list names the skill that belongs to her, the innate list names
         what her kit starts with, and the awakening and event lists name what those routes
         grant. The trainee is the link, once per group however many of her forms carry the
         skill. --}}
    <section class="mt-8" aria-labelledby="held-by">
        <h2 id="held-by" class="text-lg font-semibold text-ink-strong">Held by trainees</h2>

        @php
            $holderGroups = [
                'As her unique skill' => $uniqueHolders,
                'Among her innate skills' => $innateHolders,
                'Among her awakening skills' => $awakeningHolders,
                'Among her event skills' => $eventHolders,
            ];
            $hasAnyHolder = collect($holderGroups)->contains(fn ($holders): bool => $holders->isNotEmpty());
        @endphp

        @if (! $hasAnyHolder)
            <p class="mt-2 text-sm text-ink-muted">No trainees recorded with this skill.</p>
        @else
            @foreach ($holderGroups as $heading => $holders)
                @if ($holders->isNotEmpty())
                    <h3 class="mt-4 text-xs font-bold uppercase tracking-widest text-ink-muted">{{ $heading }}</h3>
                    <ul class="mt-1">
                        @foreach ($holders as $holder)
                            <li class="py-1 text-sm">
                                <a href="{{ route('catalog.show', $holder->slug) }}" class="text-ink-strong underline">{{ $holder->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @endforeach
        @endif

        <p class="mt-2 text-xs text-ink-muted">
            From the stored `skills_unique`, `skills_innate`, `skills_awakening` and
            `skills_event` lists on each trainee's costume forms; a form hidden as unconfirmed
            holds nothing here.
        </p>
    </section>

    {{-- D-33 at meta weight: the row's own provenance, named the way a costume form names
         its fetch. A row no fetch wrote states that instead of printing an empty line. --}}
    <h2 class="mt-8 text-lg font-semibold text-ink-strong">Provenance</h2>
    @if ($skill->source_url)
        <p class="mt-2 text-xs text-ink-muted">
            from
            <a href="{{ $skill->source_url }}" class="text-ink-strong underline">{{ $skill->source_url }}</a>
            @if ($skill->fetched_at)
                · read {{ $skill->fetched_at->timezone(config('uma.display_timezone'))->format('M j, Y') }}
            @endif
        </p>
    @else
        <p class="mt-2 text-sm text-ink-muted">No fetched source. This record was seeded or entered by hand.</p>
    @endif
</x-layout>
