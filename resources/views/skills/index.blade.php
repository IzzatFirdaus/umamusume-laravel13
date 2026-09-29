{{--
    Screen D, the skill search surface (PRD FR-D-2, DESIGN.md §8.4, CONSTRAINTS.md D-62 to D-65).

    Tokens only, no `dark:` fork and no zinc utility, for the reason recorded in catalog/index.blade.php
    (D-101, G-18, G-19). The form, the list and the pagination reuse that screen's classes so the tool's two
    server-driven filter surfaces are one idiom, which is D-64's argument applied to markup rather than to
    rows: a second presentation of the same object is ornament, not design.

    What is NOT rendered, and why: no icon and no description (D-30's permitted Skill surface is name,
    name_ja, sp_cost, type, is_unique; the source's `iconid` is unimported and both its English description
    fields fail the terminology table, G-SK-19 and G-SK-20). No rarity word and no class code dressed as a
    label (ADR-0011 §3). No hint level or discount badge, which is run state and not settled by any source
    here. And no em dash as the disclosure glyph (R-02, D-79, KI-7).
--}}
<x-layout title="Skill search">
    <h1 class="text-2xl font-semibold text-ink-strong">Skill search</h1>

    <form method="GET" action="{{ route('skills.index') }}" class="mt-4 flex flex-wrap items-end gap-3 text-sm">
        <label class="flex flex-col gap-1">
            <span class="text-ink">Search</span>
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                class="rounded-md border border-rule bg-raised px-2 py-1 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
                placeholder="Part of a skill name"
            >
        </label>

        <label class="flex flex-col gap-1">
            <span class="text-ink">Type</span>
            <select
                name="type"
                class="rounded-md border border-rule bg-raised px-2 py-1 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
            >
                <option value="">All</option>
                @foreach ($types as $option)
                    <option value="{{ $option }}" @selected($type === $option)>{{ $option }}</option>
                @endforeach
                <option value="{{ $unspecifiedType }}" @selected($type === $unspecifiedType)>
                    {{ $unspecifiedType }}
                </option>
            </select>
        </label>

        <label class="flex items-center gap-2 text-ink">
            <input
                type="checkbox"
                name="unique"
                value="1"
                class="size-4 rounded border-rule bg-raised focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
                @checked($unique)
            >
            <span>Unique only</span>
        </label>

        <button type="submit" class="enamel rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome">
            Filter
        </button>
    </form>

    {{-- D-65 draws the line here: the invitation belongs to the state where nobody has asked yet, and it
         stops speaking the moment a query is in flight. Repeating it above "nothing matches" would present
         two different states as one screen, which is the failure D-65 names. A facet chosen without a term
         still counts as no query, because the search field really is empty. --}}
    @if ($search === null)
        <p class="mt-2 text-sm text-ink-muted">
            Type part of a skill name to narrow the catalog. The search reads the same normalized key the
            import writes, so capital letters and spacing are not part of the question.
        </p>
    @endif

    @error('type')
        <p class="mt-2 text-sm text-risk">Type: {{ $message }}</p>
    @enderror

    @if ($skills->count() === 0)
        @if ($totalCount === 0)
            {{-- The state a Trainer meets before a fetch, stated as itself rather than as an empty result.
                 This is the one screen state that names the fix, and it names the command that does it. --}}
            <p class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted">
                The skill catalog holds no rows yet. Run `php artisan uma:fetch gametora-skills` to fill it.
            </p>
        @else
            {{-- D-65's second state, and deliberately no link to the review queue. Skills bypass
                 `match_candidates` by design (`StoreSkills`, and `SkillsFetchTest` asserts the queue stays
                 empty), so the queue can never hold a missing skill and a link to it would be a control that
                 cannot do what it says. G-SK-23 carries the amendment ask against D-65. --}}
            <p class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted">
                Nothing matches {{ $askedFor }}.
                @if ($searchKey !== null)
                    Searched as the key “{{ $searchKey }}”.
                @endif
                If a skill you expect is not in the skill catalog, that is a gap in the fetched data rather
                than a mistake in the query.
            </p>
        @endif
    @else
        <p class="mt-6 text-sm text-ink-muted">
            {{ $skills->total() }} of {{ $totalCount }} skills available on [Global]
        </p>

        <ul id="skill-results" class="mt-2 divide-y divide-rule rounded-md border border-rule bg-raised">
            @foreach ($skills as $skill)
                {{-- §6.11's row, reduced to what D-30 permits and what the data holds. The mark and the word
                     both carry Unique, so the state survives without the hue (D-12). --}}
                <li class="flex flex-wrap items-baseline gap-x-2 gap-y-1 px-4 py-3 text-sm">
                    <span class="font-semibold text-ink-strong">{{ $skill->name }}</span>

                    @if ($skill->name_ja)
                        <span class="text-ink-muted">{{ $skill->name_ja }}</span>
                    @endif

                    @if ($skill->is_unique)
                        <span
                            class="rounded-full bg-sunken px-2 py-0.5 text-xs text-ink-strong"
                            title="Marked from the source's skill class code, not from a card's own skill list."
                        >✦ Unique</span>
                    @endif

                    <span class="ml-auto text-xs text-ink-muted">
                        @if ($skill->type)
                            · {{ $skill->type }}
                        @endif

                        @if ($skill->sp_cost !== null)
                            · {{ $skill->sp_cost }} SP
                        @else
                            {{-- D-220 as the digest states it: an absent value renders as `N/A` with a `title`
                                 naming which kind of absence, never as a default. This absence is the
                                 source's, not a record nobody finished: it states a cost on learnable skills
                                 only (ADR-0011 §3). --}}
                            · <span title="The source states no SP cost for this class of skill.">N/A</span>
                        @endif
                    </span>
                </li>
            @endforeach
        </ul>

        <div class="mt-4 text-sm">
            {{ $skills->links() }}
        </div>
    @endif

    <p class="mt-6 max-w-3xl text-xs text-ink-muted">
        The type word is this tool's reading of the source's effect codes, not a client label. The reading
        refuses a negative value and refuses a skill carrying two kinds of effect, so some rows show no word
        at all; the Type filter's “{{ $unspecifiedType }}” choice finds those rows.
    </p>
</x-layout>
