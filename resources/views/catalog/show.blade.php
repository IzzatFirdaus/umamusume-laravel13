{{-- Tokens only: no zinc utility and no `dark:` fork. See catalog/index for the
     reason and the gate this satisfies (D-101, G-18, G-19).

     Base stats and stat bonuses are deliberately absent. ADR-0012 Decision 1 authorizes the
     columns and they are not built, so there is nothing to read; the page does not print a
     placeholder for a field no source in this tree has stored. --}}
<x-layout :title="$umamusume->name">
    <div class="flex items-baseline justify-between">
        {{-- The Japanese name is not repeated here. It has its own full-width row in the profile
             block below, which is where the character page puts it, and printing it twice on one
             screen is a copy decision rather than a data one. --}}
        <h1 class="text-2xl font-semibold text-ink-strong">{{ $umamusume->name }}</h1>
        <a href="{{ route('catalog.index') }}" class="text-sm text-ink-muted hover:underline">Back to catalog</a>
    </div>

    {{--
        The profile block: Japanese name, voice actor, release date, birthday, height and three
        sizes — the "basic information" a character page shows. Every field is a source statement
        from the `characters` document, and the block is laid out the way that page lays it out:
        the Japanese name spans the row, then voice actor and release date side by side, then
        birthday, height and three sizes across three columns.

        **Release date is the one field this source does not carry**, and that is not a gap: the
        dates live on `umamusume.global_debut_date` and on each card's `global_release_date`
        already (ADR-0008), so printing the stored column keeps one answer rather than adding a
        second source for a fact this tree already holds.

        **Every value here is nullable because the source is genuinely partial.** Measured
        2026-09-30 over the 135 roster rows: `jp_name`, `va_ja`, `height` and all three birthday
        parts are present on 135, while `va_en` and `three_sizes` are each absent on 10. So the
        null path is the normal path for a Trainer, not an edge case, and it is rendered in words
        (D-220) rather than as a blank cell or a bare 0.

        No profile row at all is a third state and is the common one before the first
        `uma:fetch gametora-character-profiles`: the sentence below names the command.
    --}}
    <section class="mt-6" aria-labelledby="basic-information">
        <h2 id="basic-information" class="text-lg font-semibold text-ink-strong">Basic information</h2>

        @if ($umamusume->profile === null)
            {{-- The Japanese name still prints here. It is a field this page owes the Trainer
                 whether or not the profile fetch has run, so withholding it until a document
                 lands would leave the common pre-fetch state with no name on it at all. --}}
            @if ($umamusume->japaneseName() !== null)
                <dl class="mt-2 grid grid-cols-2 gap-x-6 gap-y-3 rounded-md border border-rule bg-raised p-4 text-sm md:grid-cols-3">
                    <div class="col-span-2 md:col-span-3">
                        <dt class="text-ink-muted">Japanese name</dt>
                        <dd class="text-ink">{{ $umamusume->japaneseName() }}</dd>
                    </div>
                </dl>
            @endif
            <p class="mt-2 rounded-md border border-dashed border-rule bg-raised p-4 text-sm text-ink-muted">
                No profile recorded for this trainee yet. It arrives with
                `php artisan uma:fetch gametora-character-profiles`.
            </p>
        @else
            <dl class="mt-2 grid grid-cols-2 gap-x-6 gap-y-3 rounded-md border border-rule bg-raised p-4 text-sm md:grid-cols-3">
                {{-- Full width, first row. Resolved by `Umamusume::japaneseName()`, which prefers
                     this row and falls back to the trainee's own column; every other field below
                     stays on one source, so a gap in this document is never filled by another. --}}
                <div class="col-span-2 md:col-span-3">
                    <dt class="text-ink-muted">Japanese name</dt>
                    <dd class="text-ink">{{ $umamusume->japaneseName() ?? 'Not published by the source' }}</dd>
                </div>

                <div>
                    <dt class="text-ink-muted">Voice actor</dt>
                    <dd class="text-ink">
                        {{-- Both casts, because the document carries both and they disagree
                             about who speaks for a trainee: `va_ja` is the romanised Japanese
                             cast the character page shows and is present on every roster row,
                             `va_en` is the English dub and is absent on 10. Printing the JP line
                             and silently dropping a missing EN line would read as one voice
                             actor when the source states two. --}}
                        {{ $umamusume->profile->va_ja ?? 'Not published by the source' }}
                        @if ($umamusume->profile->va_en)
                            <span class="text-ink-muted">(EN: {{ $umamusume->profile->va_en }})</span>
                        @else
                            <span class="block text-xs text-ink-muted"
                                  title="The source lists no English voice actor for this trainee.">No English dub listed</span>
                        @endif
                    </dd>
                </div>

                <div>
                    <dt class="text-ink-muted">Release date</dt>
                    <dd class="text-ink">
                        @if ($umamusume->global_debut_date)
                            <time datetime="{{ $umamusume->global_debut_date->toDateString() }}">
                                {{ $umamusume->global_debut_date->format('M j, Y') }} (Global)
                            </time>
                        @else
                            {{ $umamusume->jp_debut_date?->format('M j, Y') }} (JP only)
                        @endif
                    </dd>
                </div>

                <div>
                    <dt class="text-ink-muted">Birthday</dt>
                    <dd class="text-ink">
                        @php $profile = $umamusume->profile; @endphp
                        @if ($profile->hasFullBirthday())
                            <time datetime="{{ sprintf('%04d-%02d-%02d', $profile->birth_year, $profile->birth_month, $profile->birth_day) }}">
                                {{ \Illuminate\Support\Carbon::create($profile->birth_year, $profile->birth_month, $profile->birth_day)->format('M j, Y') }}
                            </time>
                        @elseif ($profile->birth_month !== null && $profile->birth_day !== null)
                            {{-- Measured shape, not a hypothetical one: `birth_year` is the only
                                 birthday part the document omits (17 of its 163 rows). Month and
                                 day are present on every roster row, so the date is stated and
                                 the missing part is named rather than filled with a January 1st
                                 (D-220). --}}
                            <span title="The source publishes no birth year for this trainee.">
                                {{ \Illuminate\Support\Carbon::create(2000, $profile->birth_month, $profile->birth_day)->format('M j') }}
                                · year not published
                            </span>
                        @else
                            Not published by the source
                        @endif
                    </dd>
                </div>

                <div>
                    <dt class="text-ink-muted">Height</dt>
                    <dd class="text-ink">
                        @if ($profile->height !== null)
                            {{ $profile->height }} cm
                        @else
                            Not published by the source
                        @endif
                    </dd>
                </div>

                <div>
                    <dt class="text-ink-muted">Three sizes</dt>
                    <dd class="text-ink">
                        @if ($profile->hasThreeSizes())
                            {{ $profile->three_sizes_b }} · {{ $profile->three_sizes_h }} · {{ $profile->three_sizes_w }} cm
                            <span class="block text-xs text-ink-muted">bust · height · waist</span>
                        @else
                            {{-- Absent on 10 of the 135 roster rows. A dash here would be an
                                 empty slot that reads as a formatting accident, so the absence
                                 is a sentence. --}}
                            Not published by the source
                        @endif
                    </dd>
                </div>
            </dl>
        @endif
    </section>

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

    {{-- Per-form content. The strip appears only above one form, because a single-choice control
         with nothing to choose between is chrome the Trainer has to read past, and the user's own
         complaint was a page of stacked duplicates rather than a page with a control on it. One
         form renders inline under the same heading, with the same body, and no tab chrome at all. --}}
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
        {{-- The deferrals that apply to every form, stated once and in words rather than drawn
             as empty sections (D-220's one permitted exception, because the absence is itself
             the answer to "what does this form give me").

             **Skills are the notable one, and the reason is a schema decision rather than a
             missing import.** The card document carries `skills_unique`, `skills_innate`,
             `skills_awakening` and `skills_event` as id arrays, and `ADR-0011` gives this tool
             the `skills` table that would resolve those ids to names. But `character_cards` does
             not store the arrays and `skills` stores no `char` column to join them through, so
             every id is unresolvable from the database alone; rendering them would need either a
             network call at view time (NFR-1) or columns ADR-0012 explicitly keeps off the card.
             Both are a new decision, so the line says so instead of drawing four empty groups.

             **Objectives and images are the two ADR-0012 decisions**, and both are recorded there
             with the reason. Objectives are per-character in a source with no scenario key; images
             are char-grain with no resolvable asset path. --}}
        <p class="mt-2 text-xs text-ink-muted">
            Skill lists are not shown: the card document's skill id arrays are not stored, and
            `ADR-0012` keeps them off the card row. Objectives and card images are not shown
            either; that ADR records why.
        </p>

        @if ($umamusume->cards->count() > 1)
            <x-form-tabs :cards="$umamusume->cards" :active="$activeCard"
                         :action="route('catalog.show', $umamusume->slug)"
                         :show-unconfirmed="$showUnconfirmed"
                         :trainee="$umamusume"
                         panel-view="catalog.partials.form-detail" />
        @else
            <div class="mt-2">
                @include('catalog.partials.form-detail', ['card' => $umamusume->cards->first(), 'trainee' => $umamusume])
            </div>
        @endif
    @endif

    {{-- The disclosure half of the roster tree (PRD FR-A-6, US-1), the citation the tree's
         test header and the `character_cards` migration both carry. A form behind the filter is
         not an absent form, and this line is the only place the hidden half is visible on the
         page at all. The count prints only when it is above zero, so it can never read as a
         bare 0, and the way to see them is a link a keyboard can reach rather than something to
         type into the address bar. --}}
    @if ($hiddenFormCount > 0)
        <p class="mt-2 text-xs text-ink-muted">
            {{ $hiddenFormCount }} {{ \Illuminate\Support\Str::plural('form', $hiddenFormCount) }} hidden as unconfirmed ·
            <a href="{{ route('catalog.show', ['slug' => $umamusume->slug, 'show_unconfirmed' => 1]) }}" class="text-ink-strong underline">Show unconfirmed forms</a>
        </p>
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
        {{-- D-33, and the sentence that keeps the provenances apart. This list is
             `data_sources`, which is scoped to the trainee; the profile block and the form
             panels each carry their own `source_url` / `fetched_at`. It claims nothing about
             verification: `unconfirmed` is a boolean defaulting to false with no verdict written
             onto any row yet, so "every card here was checked against the two Tier A sources"
             would read as true for rows nobody cross-checked. --}}
        <p class="mt-2 text-xs text-ink-muted">
            The rows above are this trainee's own fetch history. The profile block and each
            costume form name the source and the date their own row was read from.
        </p>
    @endif
</x-layout>
