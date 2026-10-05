{{--
    The support-card catalog (PRD FR-B; ADR-0014).

    Tokens only, no `dark:` fork and no zinc utility, for the reason catalog/index records (D-101, G-18,
    G-19). The form, the list and the pagination reuse skills/index's classes so the tool's two
    server-driven filter surfaces stay one idiom.

    **Availability is a facet, not a filter applied to the Trainer.** All 559 published cards are here,
    including the 308 `[Global]` has not received, because `release_status` is a stored generated column
    whose whole purpose is to state availability per row. Pre-filtering to Global would put a second,
    silent copy of that decision on the read side.

    What is NOT rendered, and why: no icon (the source publishes none). No tier label,
    which ADR-0014 holds pending a current Global source. No effect value at any level but cap, because
    the panel names its basis rather than printing bracketing anchors (D-256). And no em dash as the
    disclosure glyph (R-02, D-79, KI-7). D-30 currently carries no SupportCard entry at all, so the
    widening ask travels in `docs/research-scratch/PLANS-AND-BRIEFS.md`.

    Card art is the one exception this list used to make: a row now carries the `ADR-0021` thumbnail
    when the local mirror holds the card's file, and stays the text-only row when it does not. The
    source publishes no art of its own, which is why the art arrives through the mirror rather than
    through this import.
--}}
<x-layout title="Support cards">
    <h1 class="text-2xl font-semibold text-ink-strong">Support cards</h1>

    {{-- Control height is `h-11`, the value DESIGN.md §6.14 fixes for a form input and the one
         skills/index already carries, so the two filter surfaces measure the same in a browser. --}}
    <form method="GET" action="{{ route('support-cards.index') }}" class="mt-4 flex flex-wrap items-end gap-3 text-sm">
        <label class="flex flex-col gap-1">
            <span class="text-ink">Rarity</span>
            <select
                name="rarity"
                class="h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
            >
                <option value="">All</option>
                {{-- The cast is load-bearing: PHP keyed this map on ints, and `$rarity` is the query
                     string's text, so `'3' === 3` would leave every picker showing "All" on a filtered
                     page. --}}
                @foreach ($rarityWords as $value => $word)
                    <option value="{{ $value }}" @selected((string) $value === $rarity)>{{ $word }}</option>
                @endforeach
            </select>
        </label>

        <label class="flex flex-col gap-1">
            <span class="text-ink">Type</span>
            <select
                name="type"
                class="h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
            >
                <option value="">All</option>
                @foreach ($typeWords as $value => $word)
                    <option value="{{ $value }}" @selected($type === $value)>{{ $word }}</option>
                @endforeach
            </select>
        </label>

        <label class="flex flex-col gap-1">
            <span class="text-ink">Availability</span>
            <select
                name="status"
                class="h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
            >
                <option value="">All</option>
                @foreach ($availabilities as $option)
                    <option value="{{ $option }}" @selected($status === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </label>

        <label class="flex flex-col gap-1">
            <span class="text-ink">Sort by</span>
            <select
                name="sort"
                class="h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
            >
                <option value="" @selected($sort === null)>Name</option>
                @foreach ($sorts as $value => $label)
                    <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <button type="submit" class="enamel rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome">
            Filter
        </button>
    </form>

    @error('rarity')
        <p class="mt-2 text-sm text-risk">Rarity: {{ $message }}</p>
    @enderror
    @error('type')
        <p class="mt-2 text-sm text-risk">Type: {{ $message }}</p>
    @enderror
    @error('status')
        <p class="mt-2 text-sm text-risk">Availability: {{ $message }}</p>
    @enderror
    @error('sort')
        <p class="mt-2 text-sm text-risk">Sort by: {{ $message }}</p>
    @enderror

    @if ($cards->count() === 0)
        @if ($totalCount === 0)
            {{-- The state a Trainer meets before a fetch, stated as itself rather than as an empty result.
                 Both commands are named because one of them is a trap: the snapshot short-circuit keys on
                 the document's hash under today's date in a disk shared by every database in the working
                 tree, so a second database asked the same day is told "unchanged" and stays empty. KI-27. --}}
            <p class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted">
                The support-card catalog holds no rows yet. Run `php artisan uma:fetch gametora-support-cards`
                to fill it, or `php artisan uma:reparse gametora-support-cards` if a fetch says the document
                is unchanged.
            </p>
        @else
            {{-- No link to the review queue: support cards bypass `match_candidates` by design
                 (`StoreSupportCards`), so the queue can never hold a missing card and a link to it would
                 be a control that cannot do what it says. --}}
            <p class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted">
                Nothing matches {{ $askedFor }}. If a card you expect is not in the support-card catalog,
                that is a gap in the fetched data rather than a mistake in the query.
            </p>
        @endif
    @else
        <p class="mt-6 text-sm text-ink-muted">
            {{ $cards->total() }} of {{ $totalCount }} support cards
        </p>

        <ul id="support-card-results" class="mt-2 divide-y divide-rule rounded-md border border-rule bg-raised">
            @foreach ($cards as $card)
                @php $effects = \App\Services\SupportCardEffects::atCap($card, $effectNames); @endphp
                <li class="flex flex-wrap items-baseline gap-x-2 gap-y-1 px-4 py-3 text-sm">
                    {{-- The `ADR-0021` thumbnail, the leading cell `design-2.0` §45a fixes for this row.
                         Two identifiers meet here and they are not interchangeable: `support-id` is the
                         publisher's number and the only one the mirror's storage path is keyed on, while
                         `route-args` carries the local `id` because `SupportCardController` binds
                         `support-cards.show` on the primary key and declares no `getRouteKeyName()`. Swapping
                         them links to a page that does not exist and points the frame at a file the mirror
                         never wrote, and neither mistake shows up in the component's own test, which
                         supplies both arguments by hand.

                         The slot is clickable here (`§45a`: "navigates to support-card detail") and is not
                         `decorative`: §42's label-in-name clause wants the anchor labelled with the name this
                         row already prints, which is also what makes the second link to the same destination
                         findable rather than anonymous. When the mirror holds nothing the component renders
                         no element at all, so the row is the text-only list it has always been. --}}
                    <x-support-thumb
                        :support-id="$card->support_id"
                        size-class="size-12"
                        :name="$card->displayName()"
                        :route-args="['card' => $card->id]"
                    />

                    <a href="{{ route('support-cards.show', $card) }}" class="font-semibold text-ink-strong hover:underline">{{ $card->displayName() }}</a>

                    <x-rarity-chip :rarity="$card->rarity" />

                    <span class="rounded-full bg-sunken px-2 py-0.5 text-xs text-ink-strong">{{ $card->rarityWord() }}</span>
                    <span class="rounded-full bg-sunken px-2 py-0.5 text-xs text-ink-strong">{{ $card->typeLabel() }}</span>

                    {{-- The word carries the state on its own, so no colour role and no tooltip: the
                         column already states it in the client's own terms, and a hue-only readout of
                         three availabilities fails G-6 the way mood-pill's would. --}}
                    <span class="ml-auto text-xs text-ink-muted">{{ $card->release_status }}</span>

                    @if ($effects !== [])
                        <span class="flex w-full flex-wrap gap-x-3 gap-y-0.5 font-mono text-xs text-ink-muted">
                            @foreach ($effects as $effect)
                                {{-- A dictionary row that is absent is marked, not labelled: the id is the
                                     source's own number, so naming it states a fact rather than inventing a
                                     word (D-20, UMAMUSUME_REFERENCE.md §1.4.8). --}}
                                <span>{{ $effect['name'] ?? '[Unverified] effect '.$effect['effect_id'] }} {{ $effect['display'] }}</span>
                            @endforeach
                        </span>
                    @endif
                </li>
            @endforeach
        </ul>

        <div class="mt-4 text-sm">
            {{ $cards->links() }}
        </div>
    @endif

    <p class="mt-6 max-w-3xl text-xs text-ink-muted">
        Effect figures are each effect's highest stated anchor, not an interpolation, and the anchor a card
        stops at is not always level 50. The type word is the client's: the source's key for Wit is
        `intelligence` and for Pal is `friend`.
    </p>
</x-layout>
