{{--
    One support card's own page (PRD FR-B; ADR-0014).

    Tokens only, no `dark:` fork and no zinc utility, the same constraint catalog/index records (D-101,
    G-18, G-19). The field grid, the section rhythm and the provenance block are skills/show's, so the
    two reference surfaces read as one tool.

    Absences stay in words rather than as empty slots (D-220), and the two skill lists keep the source's
    distinction between a list it states as empty and a list nothing stored. An id that resolves to no
    `[Global]` skill page is counted, not linked and not dropped: the detail route refuses a row
    `availableOnGlobal()` rejects, so a link to it would be a link that 404s (ADR-0011 §2).

    No tier label, which ADR-0014 holds pending a current Global source. D-30 carries no SupportCard
    entry at all, so the widening ask travels in `docs/research-scratch/PLANS-AND-BRIEFS.md`.
--}}
<x-layout :title="$card->displayName()">
    <div class="flex items-baseline justify-between">
        {{-- The `ADR-0021` thumbnail in the title block, at the `size-16` geometry `design-2.0` §45a
             fixes for this screen. `support-id` is the publisher's number and the only key the mirror's
             storage path is addressed by; `route-args` carries the local `id`, which is what
             `SupportCardController::show()` binds on. The wrapper link is the component's, not this
             screen's: §45a gives the detail slot the click action "no action", and pointing it at the
             page it is already on changes no page and moves no scroll.

             Not `decorative`, and the reason is worth recording. The title beside the slot prints the
             same name, so §42 would normally blank the `alt`; but `decorative` blanks the anchor's
             `aria-label` with it, and a focusable anchor with no accessible name fails WCAG 2.2 AA
             4.1.2 on a screen whose binding constraint is WCAG 2.2 AA. The component's default keeps the
             name reachable and leaves the `alt` carrying the client's display name, which is the smaller
             of the two deviations. Making the flag blank only the `alt`, or making the slot
             non-navigational here, is a change to the component and travels with Task 10. --}}
        <x-support-thumb
            :support-id="$card->support_id"
            size-class="size-16"
            :name="$card->displayName()"
            :route-args="['card' => $card->id]"
        />
        <h1 class="text-2xl font-semibold text-ink-strong">{{ $card->displayName() }}</h1>
        <a href="{{ route('support-cards.index') }}" class="text-sm text-ink-muted hover:underline">Back to support cards</a>
    </div>

    {{-- The source publishes the Japanese name and epithet as their own fields, so both print when both
         exist. A Pal card carries no Japanese name at all, and an empty line would read as a rendering
         accident rather than as an absence. --}}
    @if ($card->name_ja !== null || $card->title_ja !== null)
        <p class="mt-1 text-sm text-ink-muted">{{ trim(($card->name_ja ?? '').' '.($card->title_ja ?? '')) }}</p>
    @endif

    <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-3 rounded-md border border-rule bg-raised p-4 text-sm md:grid-cols-3">
        <div>
            <dt class="text-ink-muted">Rarity</dt>
            <dd class="flex items-baseline gap-2 text-ink">
                <x-rarity-chip :rarity="$card->rarity" />
                {{ $card->rarityWord() }}
            </dd>
        </div>

        <div>
            <dt class="text-ink-muted">Type</dt>
            <dd class="text-ink">{{ $card->typeLabel() }}</dd>
        </div>

        <div>
            <dt class="text-ink-muted">Availability</dt>
            <dd class="text-ink">{{ $card->release_status }}</dd>
        </div>

        <div>
            <dt class="text-ink-muted">Released in Japan</dt>
            <dd class="text-ink">
                @if ($card->release_jp !== null)
                    {{-- A calendar date stays a calendar date: converting it through the display timezone
                         would move a card released on the 24th to the 24th at 09:00 and, on the wrong side
                         of midnight, to a day the source never stated. --}}
                    {{ $card->release_jp->format('M j, Y') }}
                @else
                    <span title="The source states no Japan release date for this card.">N/A</span>
                @endif
            </dd>
        </div>

        <div>
            <dt class="text-ink-muted">Released on [Global]</dt>
            <dd class="text-ink">
                @if ($card->release_global !== null)
                    {{ $card->release_global->format('M j, Y') }}
                @else
                    <span title="The source states no Global release date for this card.">N/A</span>
                @endif
            </dd>
        </div>

        <div>
            <dt class="text-ink-muted">Belongs to</dt>
            <dd class="text-ink">
                @if ($trainee !== null)
                    <a href="{{ route('catalog.show', $trainee->slug) }}" class="text-ink-strong underline">{{ $trainee->name }}</a>
                @else
                    {{-- `char_id` addresses the source's whole character space, not this catalog's, so the
                         join misses two ways: the 9000-block staff have no trainee row at all (ADR-0014
                         correction 1), and a card can name a trainee this catalog does not track. No count
                         is stated here because the second number moves with the roster. --}}
                    <span title="The character id the source gives this card has no row in the trainable catalog.">
                        {{ $card->char_name ?? 'This card' }} has no trainee page in this catalog.
                    </span>
                @endif
            </dd>
        </div>
    </dl>

    <section class="mt-8" aria-labelledby="effects">
        <h2 id="effects" class="text-lg font-semibold text-ink-strong">Effects</h2>

        @if ($effects === [])
            <p class="mt-2 text-sm text-ink-muted">
                The source states no effect anchor for this card.
            </p>
        @else
            <ul class="mt-2 flex flex-wrap gap-2 text-xs">
                @foreach ($effects as $effect)
                    <li class="rounded-md bg-sunken px-2 py-1 text-ink">
                        {{ $effect['name'] ?? '[Unverified] effect '.$effect['effect_id'] }} {{ $effect['display'] }}
                    </li>
                @endforeach
            </ul>

            <p class="mt-2 text-xs text-ink-muted">
                Each figure is that effect's highest stated anchor, not an interpolation, and the anchor a
                card stops at is not always level 50. An effect whose id the dictionary has no row for is
                printed as its id rather than given a word this tool cannot source.
            </p>
        @endif
    </section>

    {{-- Both lists are the source's own ids, kept in the order it publishes them, because a card's hint
         list is the order the game prints it in. --}}
    @foreach ([['Hinted', $hinted, 'hinted'], ['Event', $events, 'event']] as [$heading, $list, $noun])
        <section class="mt-8" aria-labelledby="{{ $noun }}-skills">
            <h2 id="{{ $noun }}-skills" class="text-lg font-semibold text-ink-strong">{{ $heading }} skills</h2>

            @if (! $list['stored'])
                <p class="mt-2 text-sm text-ink-muted">No {{ $noun }} skill list is stored for this card.</p>
            @elseif ($list['linked']->isEmpty())
                <p class="mt-2 text-sm text-ink-muted">The source lists no {{ $noun }} skills for this card.</p>
            @else
                <ul class="mt-2">
                    @foreach ($list['linked'] as $skill)
                        <x-skill-row :skill="$skill" />
                    @endforeach
                </ul>
            @endif

            @if ($list['unlinked'] > 0)
                <p class="mt-2 text-xs text-ink-muted">
                    {{ $list['unlinked'] }} {{ $noun }} skill ids in the source's list resolve to no skill
                    page this catalog can open, so they are counted here rather than linked or dropped.
                </p>
            @endif
        </section>
    @endforeach

    <h2 class="mt-8 text-lg font-semibold text-ink-strong">Provenance</h2>
    @if ($card->source_url)
        <p class="mt-2 text-xs text-ink-muted">
            from
            <a href="{{ $card->source_url }}" class="text-ink-strong underline">{{ $card->source_url }}</a>
            @if ($card->fetched_at !== null)
                · read {{ $card->fetched_at->timezone(config('uma.display_timezone'))->format('M j, Y') }}
            @endif
            @if ($card->is_manual)
                {{-- FR-B-4's stop sign, stated where a Trainer can see it: a row carrying it is theirs, and
                     the engine will not overwrite it on the next fetch. --}}
                · <span class="text-ink-strong">Corrected by hand</span>, so the fetch engine leaves it alone
            @endif
        </p>
    @else
        <p class="mt-2 text-sm text-ink-muted">No fetched source. This record was seeded or entered by hand.</p>
    @endif
</x-layout>
