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
            {{-- Absence vocabulary (WS-2, binding): a missing value is N/A carrying a title, never
                 the word "Unknown". The date is absent when the source has not published it, which
                 is a different statement from "this trainee has no debut". --}}
            @if ($umamusume->jp_debut_date)
                <dd class="text-ink">{{ $umamusume->jp_debut_date->toDateString() }}</dd>
            @else
                <dd class="text-ink"><span title="The source publishes no JP debut date for this trainee.">N/A</span></dd>
            @endif
        </div>
        <div>
            <dt class="text-ink-muted">Global debut</dt>
            @if ($umamusume->global_debut_date)
                <dd class="text-ink">{{ $umamusume->global_debut_date->toDateString() }}</dd>
            @else
                <dd class="text-ink"><span title="The source publishes no Global debut date for this trainee.">N/A</span></dd>
            @endif
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

    {{-- Aptitudes sit at the second position in the page's binding order (WS-2), directly under
         Identity. They used to render as an `h4` inside each costume form's panel, which drew the
         same ten-letter grid once per form on a trainee with several forms — the stacked-duplicate
         complaint this page was restructured to answer. They are a property of the trainee, not of
         a form, so they render once here. --}}
    <section class="mt-8" aria-labelledby="aptitudes">
        <h2 id="aptitudes" class="text-lg font-semibold text-ink-strong">Aptitude</h2>
        @if ($umamusume->aptitude_turf === null)
            <p class="mt-2 text-sm text-ink-muted">Aptitude not published for this trainee.</p>
        @else
            <div class="mt-2">
                <x-aptitude-grid :umamusume="$umamusume" />
            </div>
        @endif
    </section>

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

             **Skills have moved out of this note.** The card document's `skills_unique`,
             `skills_innate`, `skills_awakening` and `skills_event` id arrays *are* stored on
             `character_cards` (casts at `CharacterCard.php`), and the Skills section below
             resolves all four to names through `skills.export_id`. `skills_evo` is stored too and
             deliberately not listed there, for the reason that section states under its own
             heading. What is not kept is `skills_awakening_en`, which the document carries on 5 of
             its 268 records. This paragraph used to say the opposite about the lists, which was
             true before that storage landed and has been stale since.

             **Objectives and images are the two ADR-0012 decisions**, and both are recorded there
             with the reason. Objectives are per-character in a source with no scenario key; images
             are char-grain with no resolvable asset path. --}}
        <p class="mt-2 text-xs text-ink-muted">
            Objectives and card images are not recorded for this form; `ADR-0012` records why.
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

    {{-- Skills, between Costume forms and Goal races per the binding order (WS-2). The lists are
         the form's own four `skills_*` arrays, resolved by the controller through `skills.export_id`
         and rendered by `x-skill-row`, so this section and the skill detail page's holder groups read
         the same columns in opposite directions. No `turn` column: it is `N/A` on every row until the
         Phase B2 storage decision, and a column of absences is noise. Nothing here ranks a skill: the
         ✦ pill classifies, it does not recommend. --}}
    <section class="mt-8" aria-labelledby="skills">
        <h2 id="skills" class="text-lg font-semibold text-ink-strong">Skills</h2>

        @if (collect($skillLists)->every(static fn (array $list): bool => $list['ids'] === []))
            <p class="mt-2 text-sm text-ink-muted">No skill lists are recorded for this form.</p>
        @else
            @foreach ($skillLists as $list)
                <h3 class="mt-4 text-xs font-bold uppercase tracking-widest text-ink-muted">{{ $list['label'] }}</h3>
                <ul class="mt-1">
                    @if ($list['skills']->isNotEmpty())
                        @foreach ($list['skills'] as $skill)
                            {{-- The detail route serves only rows `Skill::availableOnGlobal()`
                                 accepts, so a JapanOnly or third-party-named skill prints its name
                                 without a link that would 404 on it. --}}
                            <x-skill-row
                                :skill="$skill"
                                :linked="$skill->release_status === \App\Enums\ReleaseStatus::GlobalReleased && $skill->name_is_client"
                            />
                        @endforeach
                    @elseif ($list['ids'] === [])
                        {{-- A list the form does not carry at all. Each group says for itself rather
                             than leaving a heading over a blank, which is D-220's rule for a value the
                             data does not hold. --}}
                        <li class="py-1.5 text-sm text-ink-muted">{{ $list['absent'] }}</li>
                    @else
                        {{-- The card lists ids and the catalogue names none of them, which is a
                             different claim from an empty list: measured on the committed bodies, 1 of
                             the 1,273 event ids resolves to no skill row. The export id is not printed
                             (D-30: no export id reaches a screen). --}}
                        <li class="py-1.5 text-sm text-ink-muted">Recorded on this form but not in the skill catalog yet.</li>
                    @endif
                </ul>
            @endforeach
        @endif

        <p class="mt-2 text-xs text-ink-muted">
            From this form's stored <code>skills_unique</code>, <code>skills_innate</code>,
            <code>skills_awakening</code> and <code>skills_event</code> lists.
            <code>skills_evo</code> is recorded on the card and not listed here: most of its ids name
            a skill [Global] has not shipped, and the skill pages refuse those rows rather than link
            to a missing page.
        </p>
    </section>

    {{-- Goal races: a heading with the canonical absence body (WS-2 Task 2.3). No `trainee_goals`
         table exists — KI-34 is the reservation for it — and the pennant has no source wired, so
         the section states the absence rather than drawing four empty client panels. --}}
    <section class="mt-8" aria-labelledby="goal-races">
        <h2 id="goal-races" class="text-lg font-semibold text-ink-strong">Goal races</h2>
        <p class="mt-2 text-sm text-ink-muted">
            Goal races are not recorded. The source publishes per-trainee goal races; this tool
            does not record them.
        </p>
    </section>

    {{-- Her runs, with the page's one primary action (DESIGN.md §2.3). The query lives in the
         controller, not here (WS-2 Task 2.4). The helper line is what keeps the action honest: the
         link does not pre-select this trainee, because `TrainingRunController::create()` never
         reads a `umamusume_id` param, so a link that appeared to pass one would silently not. --}}
    <section class="mt-8" aria-labelledby="her-runs">
        <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2">
            <h2 id="her-runs" class="text-lg font-semibold text-ink-strong">Her runs</h2>
            <a href="{{ route('runs.create') }}"
               class="enamel rounded-full bg-chrome px-4 py-1.5 text-sm font-bold text-on-chrome">
                New run
            </a>
        </div>
        <p class="mt-1 text-xs text-ink-muted">Opens run setup; the trainee is chosen there.</p>

        @if ($runs->isEmpty())
            <p class="mt-2 text-sm text-ink-muted">No runs recorded for her yet.</p>
        @else
            <ul class="mt-2 divide-y divide-rule rounded-md border border-rule">
                @foreach ($runs as $run)
                    <li class="flex flex-wrap items-baseline gap-x-3 px-3 py-2 text-sm">
                        <span class="font-semibold text-ink-strong">{{ $run->status->label() }}</span>
                        <span class="text-ink">
                            {{ $run->scenario ? ($scenarioLabels[$run->scenario] ?? $run->scenario) : 'No scenario set' }}
                        </span>
                        <span class="ml-auto font-mono text-xs tabular-nums text-ink-muted">
                            {{ $run->turn_entries_count }} {{ \Illuminate\Support\Str::plural('turn', $run->turn_entries_count) }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- Aliases sit seventh in the binding order, after Her runs. --}}
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
