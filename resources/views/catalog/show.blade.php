{{-- Tokens only: no zinc utility and no `dark:` fork. See catalog/index for the
     reason and the gate this satisfies (D-101, G-18, G-19). --}}
<x-layout :title="$umamusume->name">
    <div class="flex items-baseline justify-between">
        <h1 class="text-2xl font-semibold text-ink-strong">
            {{ $umamusume->name }}
            @if ($umamusume->name_ja)
                <span class="ml-2 text-base text-ink-muted">{{ $umamusume->name_ja }}</span>
            @endif
        </h1>
        <a href="{{ route('catalog.index') }}" class="text-sm text-ink-muted hover:underline">Back to catalog</a>
    </div>

    <dl class="mt-6 grid grid-cols-2 gap-x-6 gap-y-3 rounded-md border border-rule bg-raised p-4 text-sm md:grid-cols-4">
        <div>
            <dt class="text-ink-muted">Release status</dt>
            <dd class="text-ink">{{ $umamusume->release_status->label() }}</dd>
        </div>
        <div>
            <dt class="text-ink-muted">JP debut</dt>
            <dd class="text-ink">{{ $umamusume->jp_debut_date?->toDateString() ?? 'Unknown' }}</dd>
        </div>
        <div>
            <dt class="text-ink-muted">Global debut</dt>
            <dd class="text-ink">{{ $umamusume->global_debut_date?->toDateString() ?? 'Unknown' }}</dd>
        </div>
        <div>
            <dt class="text-ink-muted">Edited by Trainer</dt>
            <dd class="text-ink">{{ $umamusume->is_manual ? 'Yes (engine will not overwrite)' : 'No' }}</dd>
        </div>
    </dl>

    {{-- Forms before fetch history: this `<h2>` answers "which costumes does she have, and
         where did each of those rows come from", and Provenance below it answers the
         trainee-level half of the same question. Rows are `<h3>` under an `<h2>` section, the
         one-level-below correction catalog/index made and for the same reason. The debut note
         and the rarity chip sit in the row's meta group, so a form reads identically on this
         page and in the tree. --}}
    <h2 class="mt-8 text-lg font-semibold text-ink-strong">Costume forms</h2>

    @if ($umamusume->cards->isEmpty())
        <p class="mt-2 rounded-md border border-dashed border-rule bg-raised p-4 text-sm text-ink-muted">
            @if ($hiddenFormCount > 0)
                Every costume form recorded for this trainee is hidden as unconfirmed.
            @else
                No Global costume cards recorded for this trainee yet. Forms arrive with
                `php artisan uma:fetch gametora-character-cards`.
            @endif
        </p>
    @else
        <ul class="mt-2 divide-y divide-rule rounded-md border border-rule bg-raised">
            @foreach ($umamusume->cards as $card)
                <li class="flex flex-wrap items-baseline justify-between gap-x-4 px-4 py-2 text-sm">
                    <h3 class="font-medium text-ink">{{ $card->title }}</h3>
                    <span class="flex items-baseline gap-3 text-xs text-ink-muted">
                        <x-rarity-chip :rarity="$card->rarity" />
                        @if ($card->is_debut_form)
                            <span>debut form</span>
                        @endif
                        {{-- `text-risk` on `bg-raised` is the measured pair DESIGN.md §3.4
                             records at 10.89 light / 6.22 dark. No second badge and no new
                             token: the flag is words, and the chip names the stars. --}}
                        @if ($card->unconfirmed)
                            <span class="font-semibold text-risk">Not confirmed by two sources</span>
                        @endif
                        {{-- A date-only column stays date-only: no zone conversion here, the
                             same convention the tree row and the debut rows follow. --}}
                        <time datetime="{{ $card->global_release_date->toDateString() }}">
                            Released (Global) {{ $card->global_release_date->format('M j, Y') }}
                        </time>
                        {{-- This is the card's own fetch, not the trainee's: Amendment A1 put
                             `source_url` / `snapshot_path` / `fetched_at` on this row, so the
                             row names the document it was actually read from. The URL rides a
                             `title` attribute rather than a link, because a local-only tool
                             should not ship an outbound click, and `{{ }}` escapes it because
                             a URL that arrived from fetched content is untrusted. `fetched_at`
                             is nullable, so an un-fetched row prints nothing instead of an
                             empty date; a stored instant renders through the display zone. --}}
                        @if ($card->fetched_at)
                            <span title="{{ $card->source_url }}">
                                read {{ $card->fetched_at->timezone(config('uma.display_timezone'))->format('M j, Y') }}
                            </span>
                        @endif
                    </span>
                </li>
            @endforeach
        </ul>
    @endif

    {{-- A form behind the filter is not an absent form, and this line is the only place the
         hidden half is visible on the page at all. The count prints only when it is above
         zero, so it can never read as a bare 0, and the way to see them is a link a keyboard
         can reach rather than something to type into the address bar. --}}
    @if ($hiddenFormCount > 0)
        <p class="mt-2 text-xs text-ink-muted">
            {{ $hiddenFormCount }} {{ \Illuminate\Support\Str::plural('form', $hiddenFormCount) }} hidden as unconfirmed ·
            <a href="{{ route('catalog.show', ['slug' => $umamusume->slug, 'show_unconfirmed' => 1]) }}" class="text-ink-strong underline">Show unconfirmed forms</a>
        </p>
    @endif

    {{-- The token set has no caution chrome: `up` means increase, `risk` means
         failure, and `pick` measures 1.60:1 on the raised surface so it cannot
         carry a boundary. The notice is therefore carried by its copy over a
         declared border token (`ink-faint`, 3.26:1 light / 4.21:1 dark) rather
         than by an invented colour. Adding a caution token is a DESIGN.md
         amendment, not a view-local choice. --}}
    @if ($umamusume->release_status === \App\Enums\ReleaseStatus::JapanOnly)
        <p class="mt-4 rounded-md border border-ink-faint bg-raised px-3 py-2 text-sm text-ink">
            Not yet released on Global. JP-sourced data below.
        </p>
    @endif

    <h2 class="mt-8 text-lg font-semibold text-ink-strong">Aliases</h2>
    @if ($umamusume->aliases->isEmpty())
        <p class="mt-2 text-sm text-ink-muted">No aliases yet.</p>
    @else
        <ul class="mt-2 flex flex-wrap gap-2 text-sm">
            @foreach ($umamusume->aliases as $alias)
                <li class="rounded-md bg-sunken px-2 py-1 text-ink">{{ $alias->alias }} <span class="text-ink-muted">({{ $alias->language->label() }})</span></li>
            @endforeach
        </ul>
    @endif

    <h2 class="mt-8 text-lg font-semibold text-ink-strong">Provenance</h2>
    @if ($umamusume->dataSources->isEmpty())
        <p class="mt-2 text-sm text-ink-muted">No fetched sources. This record was seeded or entered by hand.</p>
    @else
        <ul class="mt-2 space-y-1 text-sm">
            @foreach ($umamusume->dataSources as $source)
                <li class="text-ink">
                    <a href="{{ $source->url }}" class="text-ink-strong underline">{{ $source->url }}</a>
                    <span class="text-ink-muted">
                        · {{ $source->source_key }} · fetched {{ $source->fetched_at->timezone(config('uma.display_timezone'))->format('Y-m-d H:i T') }}
                    </span>
                </li>
            @endforeach
        </ul>
        {{-- D-33, and the sentence that keeps the two provenances apart. This list is
             `data_sources`, which is scoped to the trainee; the form rows above carry their
             own `source_url` / `fetched_at` from Amendment A1. "checked" rather than
             "confirmed" on purpose: with `?show_unconfirmed=1` a row on this page says it was
             not confirmed by two sources, and the sentence must not contradict it. --}}
        <p class="mt-2 text-xs text-ink-muted">
            The rows above are this trainee's own fetch history. Each costume form names the
            source and the date its own row was read from, and every card on this page was
            checked against the two Tier A sources listed in
            docs/data/2026-09-29-global-roster-crosscheck.md.
        </p>
    @endif
</x-layout>
