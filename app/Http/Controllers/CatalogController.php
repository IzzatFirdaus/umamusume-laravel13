<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CardRarity;
use App\Enums\ReleaseStatus;
use App\Enums\TraineeGoalType;
use App\Http\Requests\CatalogSearchRequest;
use App\Models\CharacterCard;
use App\Models\DataSource;
use App\Models\Skill;
use App\Models\TraineeGoal;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use App\Models\UmamusumeAlias;
use App\Models\UmamusumeProfile;
use App\Services\DataPipeline\ArtworkMirror;
use App\Services\DataPipeline\NameNormalizer;
use App\Services\PageSize;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Server-rendered catalog browsing (PRD US-1, FR-A). Read path only; writes
 * come from the engine or manual edits, never here.
 */
class CatalogController extends Controller
{
    /**
     * Paginated roster tree, filterable by `status` (ReleaseStatus), `search` and
     * `show_unconfirmed`. Search compares a normalized term against the normalized match
     * key, the aliases and the card titles — the last two folded to the term's shape at
     * comparison time, see `normalizedColumn()` (matching rule, AGENTS.md §5 The Floor). A Trainer
     * searching a card must land on the trainee who owns it, so the card clause selects
     * the *parent* row. An unknown `status` is refused by CatalogSearchRequest and the
     * Trainer lands back on the canonical catalog with the field named; `status=all` is
     * the way back to the unfiltered list, and is an accepted token rather than an enum
     * value. Renders catalog.index.
     *
     * The default status is Released (Global), not "everything": a catalog whose first
     * screen is half JP-only rows is not the Global English tool PRD §1 describes.
     */
    public function index(CatalogSearchRequest $request, NameNormalizer $normalizer, ArtworkMirror $mirror): Response|RedirectResponse
    {
        return $this->renderPage('Catalog/Index', $this->indexProps($request, $normalizer, $mirror, route('catalog.index')));
    }

    /**
     * The same roster tree as the Database hub's Trainees area (SCREEN-023, plan §8 D17).
     *
     * One query, one row mapper, one props array: the two URLs render one screen, and the only
     * difference is the page name and the path the pager and the filter form write back to. A redirect
     * was the smaller diff and it is refused by the slice brief, because no owner ruling authorises
     * one, so the body is shared instead of the URL being borrowed. The `path` argument keeps the
     * pager links and the form's own address on the page a Trainer is standing on, rather than on the
     * URL this query was first written for.
     */
    public function databaseIndex(CatalogSearchRequest $request, NameNormalizer $normalizer, ArtworkMirror $mirror): Response|RedirectResponse
    {
        return $this->renderPage('Database/Trainees', $this->indexProps($request, $normalizer, $mirror, route('database.trainees')));
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private function renderPage(string $page, array $props): Response
    {
        return Inertia::render($page, $props);
    }

    /**
     * The list page's data, shared by `index()` and `databaseIndex()`. See `index()` for the
     * behaviour it states; the `$path` argument is the only argument that differs between them.
     *
     * @return array<string, mixed>
     */
    private function indexProps(CatalogSearchRequest $request, NameNormalizer $normalizer, ArtworkMirror $mirror, string $path): array
    {
        $status = $request->validated('status');
        $search = $request->query('search');
        $page = max(1, (int) $request->query('page', '1'));
        $pageSize = PageSize::clamp($request->query('pageSize'));

        /** @var ReleaseStatus|null $statusEnum */
        $statusEnum = $status !== null && $status !== CatalogSearchRequest::ALL ? ReleaseStatus::tryFrom((string) $status) : null;
        $showAllStatus = $status === CatalogSearchRequest::ALL;
        $showUnconfirmed = $request->boolean('show_unconfirmed');
        $searchKey = $search !== null && $search !== '' ? $normalizer->normalize((string) $search) : null;

        // The three aptitude facets (`catalog.html:75-77`). Each value names a COLUMN — the aptitudes
        // are ten columns on `umamusume`, one letter grade each — so the filter is "that axis is
        // recorded at all", not "that letter". A trainee with a null on the axis genuinely has no
        // aptitude there and is correctly excluded; filtering by grade would be a different feature.
        // Validated by `CatalogSearchRequest` above, so an unknown value never reaches this line.
        $surface = $request->validated('surface');
        $distance = $request->validated('distance');
        $style = $request->validated('style');

        $query = Umamusume::query()
            ->with(['cards' => $this->cardScope($showUnconfirmed)])
            ->when(! $showAllStatus && $statusEnum === null, fn (Builder $q): Builder => $q->where('release_status', ReleaseStatus::GlobalReleased->value))
            ->when($statusEnum !== null, fn (Builder $q): Builder => $q->where('release_status', $statusEnum->value))
            ->when($surface !== null, fn (Builder $q): Builder => $q->whereNotNull('aptitude_'.$surface))
            ->when($distance !== null, fn (Builder $q): Builder => $q->whereNotNull('aptitude_'.$distance))
            ->when($style !== null, fn (Builder $q): Builder => $q->whereNotNull('aptitude_'.$style))
            ->when($searchKey !== null, function (Builder $q) use ($searchKey): void {
                // normalize() strips separators but not LIKE's own metacharacters, and
                // the term is bound as a parameter, so without this escape a Trainer
                // typing "%" or "_" gets a wildcard and the whole catalog back.
                $like = '%'.addcslashes($searchKey, '%_\\').'%';

                $q->where(function (Builder $sub) use ($like): void {
                    // Both sides of each comparison have to arrive at the same string:
                    // `match_key` is already normalized, so the title and the alias are
                    // folded at comparison time by `normalizedColumn()`. A card match
                    // still surfaces its trainee, because the trainee is the level this
                    // page is organised at.
                    $sub->where('match_key', 'like', $like)
                        ->orWhereHas('aliases', fn ($a) => $a->whereRaw($this->normalizedColumn('alias').' like ?', [$like]))
                        ->orWhereHas('cards', fn ($c) => $c->whereRaw($this->normalizedColumn('title').' like ?', [$like]));
                });
            });

        [$items, $total] = $this->cached($query, $status, $searchKey, $page, $pageSize, $showUnconfirmed, $surface, $distance, $style);

        // `withQueryString()` because the paginator is built by hand here: without it the
        // pager links carry a bare `?page=N` and paging silently reverts status, search
        // and the unconfirmed opt-in.
        $umamusumes = (new LengthAwarePaginator($items, $total, $pageSize, $page, ['path' => $path]))
            ->withQueryString()
            ->through(function (Umamusume $umamusume) use ($mirror): array {
                // The badge and the form count both read the collection the card scope loaded,
                // so a hidden card moves the max rarity with it.
                $maxRarity = $umamusume->cards->isEmpty()
                    ? null
                    : CardRarity::from((int) $umamusume->cards->max(static fn (CharacterCard $card): int => $card->rarity->value));

                // The header frame is identity, and identity is the debut form: the export derives
                // `is_debut_form` by rule, and a trainee who later gained an SSR costume is still
                // the trainee she was at debut. The badge on the same row keeps naming the top
                // rarity, so the two read two different cards deliberately and neither is wrong
                // (`DESIGN.md` §4.7). The `?? first()` branch is not defensive padding: rows with no
                // debut flag still get a frame rather than nothing, which is the fallback the detail
                // page already uses, so the two screens cannot disagree by accident. The rule
                // itself lives on the model (`Umamusume::defaultForm()`), so the two screens read
                // it from one place rather than each restating it. `url()` resolves to null when
                // the mirror holds no file and Vue renders no frame. A `card_portrait` is a trainee
                // portrait keyed on `card_id`, the same kind the detail page points at.
                $headerCard = $umamusume->defaultForm();

                return [
                    'id' => $umamusume->id,
                    'slug' => $umamusume->slug,
                    'name' => $umamusume->name,
                    'name_ja' => $umamusume->name_ja,
                    'release_status' => $umamusume->release_status->value,
                    'release_status_label' => $umamusume->release_status->label(),
                    'max_rarity' => $maxRarity === null ? null : ['label' => $maxRarity->label(), 'stars' => $maxRarity->stars()],
                    'artworkURL' => $headerCard === null ? null : $mirror->url('card_portrait', (int) $headerCard->card_id),
                    // The form the card names beside her portrait, by the same rule the portrait uses
                    // (`Umamusume::defaultForm()`): the debut form when the export flags one, otherwise
                    // the first form in `cardScope()` order. The card and the detail page therefore
                    // cannot name two different forms for one trainee, and `is_debut_form` lets the
                    // page say "debut form" only where that is the fact (`catalog.html` annotation 3:
                    // the form and its own rarity are two figures, not a trainee rarity).
                    'default_form' => $headerCard === null ? null : [
                        'title' => $headerCard->title,
                        'rarity_label' => $headerCard->rarity->label(),
                        'rarity_stars' => $headerCard->rarity->stars(),
                        'rarity_word' => $headerCard->rarity->word(),
                        'is_debut_form' => $headerCard->is_debut_form,
                    ],
                    // Three letters, not nine (`catalog.html` annotation 2): the strongest value per
                    // axis, read off the model's own ten columns. The detail page owns the full set.
                    'aptitude_summary' => $this->aptitudeSummary($umamusume),
                    'form_count' => $umamusume->cards->count(),
                    'cards' => $umamusume->cards->map(static fn (CharacterCard $card): array => [
                        'id' => $card->id,
                        'title' => $card->title,
                        'rarity_label' => $card->rarity->label(),
                        'rarity_stars' => $card->rarity->stars(),
                        'is_debut_form' => $card->is_debut_form,
                        'unconfirmed' => $card->unconfirmed,
                        'global_release_date' => $card->global_release_date->toDateString(),
                        'global_release_date_display' => $card->global_release_date->format('M j, Y'),
                        'artworkURL' => $mirror->url('card_portrait', (int) $card->card_id),
                    ])->all(),
                ];
            });

        return [
            'umamusumes' => $umamusumes,
            // The screen's own scope rather than the filtered answer. With no search term the list's total
            // already is that scope, so the ordinary load costs no extra read; only a searched page pays
            // one query for the number the count line prints, and `CatalogRosterTreeTest` holds that the
            // tree never grows a query per row.
            'totalCount' => $searchKey === null ? $total : Umamusume::query()
                ->when(! $showAllStatus && $statusEnum === null, fn (Builder $q): Builder => $q->where('release_status', ReleaseStatus::GlobalReleased->value))
                ->when($statusEnum !== null, fn (Builder $q): Builder => $q->where('release_status', $statusEnum->value))
                ->count(),
            'askedFor' => is_string($search) && $search !== '' ? '"'.$search.'"' : 'these filters',
            'statuses' => collect(ReleaseStatus::cases())
                ->map(static fn (ReleaseStatus $status): array => ['value' => $status->value, 'label' => $status->label()])
                ->all(),
            'currentStatus' => $statusEnum?->value,
            // The three aptitude facets, echoed so a reload keeps the selection the URL carries. Read
            // from `validated()` like `status`, so only a value the rule accepted is ever echoed back
            // into a control.
            'currentSurface' => $surface,
            'currentDistance' => $distance,
            'currentStyle' => $style,
            'search' => $search,
            'showAllStatus' => $showAllStatus,
            'showUnconfirmed' => $showUnconfirmed,
            'allStatusesLabel' => 'All statuses',
        ];
    }

    /**
     * Raw SQL that folds a stored string into the shape `NameNormalizer::normalize()`
     * leaves a search term in, so a `like` between the two is a comparison and not a
     * coincidence. Needed because `character_cards.title` and `umamusume_aliases.alias`
     * carry verbatim source data with no normalized column behind them, and normalize()
     * deletes the spaces that make most card epithets multi-word: `red strife` can only
     * reach `[Red Strife]` if the column loses its space too.
     *
     * `$column` is interpolated rather than bound because SQL takes identifiers and
     * literals where a placeholder cannot go. It is a string literal from this file at
     * both call sites, never request input; the *term* is what varies, and it stays a
     * bound parameter in the caller.
     *
     * Known ceiling, stated rather than assumed away: this folds spaces, hyphens and
     * middle dots, which every engine here can do. Two classes of difference it cannot
     * fold portably. First, **diacritics**: SQLite's `lower()` is ASCII-only, so it
     * neither maps `É` to `E` nor drops combining marks the way normalize() does, and
     * `[Nuit Étoilée de Scarlet]` still will not answer `nuit etoilee`. Second, **width
     * variants**, and in the awkward direction: NFKD rewrites full-width katakana to its
     * halfwidth form on the term side while SQL leaves the stored column untouched, so a
     * katakana alias or title is folded on one side and not the other and cannot match
     * either. The upgrade path for both is the same: a normalized key stored beside
     * `title` and `alias`, written by the pipeline the way `match_key` already is —
     * durable, indexable, and honest on both sides. That is a schema change, so it is
     * not this method's to make.
     */
    private function normalizedColumn(string $column): string
    {
        $expression = "lower({$column})";

        foreach (NameNormalizer::FOLDED_CHARACTERS as $character) {
            $expression = "replace({$expression}, '{$character}', '')";
        }

        return $expression;
    }

    /**
     * Detail page for one Umamusume by slug: her aliases, her profile block, her last 10
     * provenance rows, and her costume forms. Renders the Catalog/Show Inertia page.
     *
     * The two provenances are two answers. `dataSources` is this trainee's own fetch history
     * (FR-A-4); each card carries its own `source_url` / `fetched_at` from Amendment A1, so a
     * form names the document its row was actually read from. The page prints both, and never
     * one in place of the other.
     *
     * Forms are scoped by the same `cardScope()` the list uses, so a solo-sourced form is
     * hidden here exactly as it is there, and `?show_unconfirmed=1` is the lever on both.
     * Hiding is not the same as having none, so the page also gets the count the scope left
     * out: one bounded count on the same indexed key, taken after the 404 so an unknown slug
     * still costs one lookup, and skipped entirely when nothing is being hidden.
     *
     * `?form={card_id}` picks the active costume form, so a link into one form survives a
     * reload and can be shared. It is the **local** `character_cards.id` and not the source's
     * `card_id`, because the local key is the one a row on this page can name. An absent,
     * unparseable or out-of-scope value falls back to the first form in `cardScope()` order
     * rather than erroring: a stale shared link should show the trainee, not a 404, and the
     * strip is a convenience rather than a route with its own error states.
     *
     * Not cached. This read is one indexed lookup, four bounded relation loads and at most
     * one bounded count on a local SQLite file, and the only thing a cache could hold for it
     * is the model graph itself — which `serializable_classes => false` will not hand back.
     * Leaving it out is the fix; see `cached()` for the same reason stated with the evidence
     * (KI-2).
     *
     * The trainee ladder (`traineeGoalLadder()`) is composed on that same uncached read, which is also
     * why a promotion cannot serve a stale one here: `catalog:version` is bumped by the store action
     * (`StoreTraineeGoals` through `PipelineRunner`) and this page never holds a copy of either answer.
     * Anyone adding a `Cache::remember` to `show()` later must key it on that version or the ladder goes
     * stale by construction. `CatalogDetailPageTest` proves the freshness by promoting a row and reading
     * the page twice.
     *
     * @throws NotFoundHttpException when the slug is unknown
     */
    public function show(Request $request, string $slug): Response
    {
        $showUnconfirmed = $request->boolean('show_unconfirmed');

        $umamusume = Umamusume::where('slug', $slug)
            ->with([
                'aliases',
                'profile',
                'dataSources' => fn ($q) => $q->latest('fetched_at')->limit(10),
                'cards' => $this->cardScope($showUnconfirmed),
            ])
            ->first();

        abort_if($umamusume === null, 404);

        $activeCard = $this->activeCard($request, $umamusume->cards);

        // WS-2 Task 2.4: the data access for the detail page lives here, not in the page. `scenario`
        // is a column on `training_runs`, not a relation — the row renders its label from
        // `config/scenarios.php`, which is the only place a scenario name enters the layout path
        // (D-240). `withCount` rather than `with('turnEntries')` keeps the section at two queries
        // instead of 1 + 10N, since the row only prints the count.
        $runs = TrainingRun::query()
            ->where('umamusume_id', $umamusume->id)
            ->withCount('turnEntries')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // WS-2 Task 2.2, widened to the four lists the card document publishes. The arrays decide
        // which band a skill sits in and nothing else, which is the use D-30's 2026-10-02 amendment
        // admits, and the skill detail page reads the same four columns in reverse, so the two
        // surfaces cannot drift about what a band means. Resolved here rather than in the page for
        // the same reason as the runs, and keyed so each group renders in the source's own order.
        //
        // The null branch is real, not defensive: a trainee with no Global card has no form to read
        // skill lists from, and the page's Costume forms section says exactly that. The arrays are
        // themselves nullable, which is why the inner `??` is there too. `is_unique` is selected
        // because `SkillRow.vue` draws the Unique pill from it, and the column was never fetched
        // here, so the pill could not render on a trainee page for any card.
        //
        // With no explicit `card` parameter the form in view is her default form, which Decision 9
        // (A-11) fixes as the card flagged `is_debut_form`; the portrait below reads the same
        // `$card`, so the picture and the skills under it can never name different forms.
        $card = $activeCard ?? $umamusume->defaultForm();

        $skillLists = [
            ['label' => 'Her unique skills', 'absent' => 'No unique skill recorded for this form.', 'ids' => $card === null ? [] : ($card->skills_unique ?? [])],
            ['label' => 'Her innate skills', 'absent' => 'No innate skills recorded for this form.', 'ids' => $card === null ? [] : ($card->skills_innate ?? [])],
            ['label' => 'Her awakening skills', 'absent' => 'No awakening skills recorded for this form.', 'ids' => $card === null ? [] : ($card->skills_awakening ?? [])],
            ['label' => 'Her event skills', 'absent' => 'No event skills recorded for this form.', 'ids' => $card === null ? [] : ($card->skills_event ?? [])],
        ];

        $skillIds = collect($skillLists)->flatMap(static fn (array $list): array => $list['ids'])->all();
        $skillsByExportId = $skillIds === []
            ? collect()
            : Skill::query()->whereIn('export_id', $skillIds)->get(['id', 'export_id', 'name', 'sp_cost', 'is_unique', 'release_status', 'name_is_client'])->keyBy('export_id');

        $scenarioLabels = array_map(static fn (array $def): string => $def['label'], config('scenarios.scenarios'));

        return Inertia::render('Catalog/Show', [
            // The "New run" action's destination: the carry endpoint writes this trainee into the
            // setup draft and opens the wizard at its first step (plan Slice 4.3).
            'carry_url' => route('career.trainee.carry', $umamusume),
            'trainee' => [
                'id' => $umamusume->id,
                'slug' => $umamusume->slug,
                'name' => $umamusume->name,
                'name_ja' => $umamusume->name_ja,
                'release_status_label' => $umamusume->release_status->label(),
                'is_japan_only' => $umamusume->release_status === ReleaseStatus::JapanOnly,
                // Resolved through the model so the page owes the Trainer a name whether or not the
                // profile fetch has run: it prefers the profile row and falls back to the trainee's
                // own column (D-220).
                'japanese_name' => $umamusume->japaneseName(),
                'jp_debut_date' => $umamusume->jp_debut_date?->toDateString(),
                'global_debut_date' => $umamusume->global_debut_date?->toDateString(),
                'release_date' => $this->releaseDate($umamusume),
                'is_manual' => $umamusume->is_manual,
                'aptitudes' => $this->aptitudes($umamusume),
                'profile' => $this->profileShape($umamusume->profile),
                // The Identity slot (`ADR-0021` read half, `design-2.0` §45a "Catalog detail,
                // Identity"). Keyed on the same `$card` the four skill lists above read, so the
                // portrait and the skills it sits above can never disagree about which form is
                // being shown. `card_id` is the publisher's number and the only key the mirror's
                // storage path answers to; a null is the mirror's normal partial answer and the
                // page renders no frame for it (`DESIGN.md` §4.7).
                'artworkURL' => $card === null
                    ? null
                    : app(ArtworkMirror::class)->url('card_portrait', (int) $card->card_id),
                'aliases' => $umamusume->aliases->map(static fn (UmamusumeAlias $alias): array => [
                    'alias' => $alias->alias,
                    'language_label' => $alias->language->label(),
                ])->values()->all(),
            ],
            'cards' => $umamusume->cards->map(static fn (CharacterCard $card): array => [
                'id' => $card->id,
                'title' => $card->title,
                'rarity_label' => $card->rarity->label(),
                'rarity_stars' => $card->rarity->stars(),
                'is_debut_form' => $card->is_debut_form,
                'unconfirmed' => $card->unconfirmed,
                'global_release_date' => $card->global_release_date->toDateString(),
                'global_release_date_display' => $card->global_release_date->format('M j, Y'),
                'snapshot_path' => $card->snapshot_path,
                'source_url' => $card->source_url,
                'fetched_at_display' => $card->fetched_at?->timezone(config('uma.display_timezone'))->format('M j, Y'),
            ])->values()->all(),
            'activeCardId' => $activeCard?->id,
            'hiddenFormCount' => $showUnconfirmed ? 0 : $umamusume->cards()->where('unconfirmed', true)->count(),
            'runs' => $runs->map(static fn (TrainingRun $run): array => [
                'id' => $run->id,
                'status_label' => $run->status->label(),
                'scenario_label' => $run->scenario ? ($scenarioLabels[$run->scenario] ?? $run->scenario) : 'No scenario set',
                'turn_count' => $run->turn_entries_count,
            ])->values()->all(),
            'skillLists' => collect($skillLists)->map(static fn (array $list): array => [
                'label' => $list['label'],
                'absent' => $list['absent'],
                'has_ids' => $list['ids'] !== [],
                'skills' => collect($list['ids'])
                    ->map(static fn ($id) => $skillsByExportId->get($id))
                    ->filter()
                    // The detail route serves only rows `Skill::availableOnGlobal()` accepts, so a
                    // JapanOnly or third-party-named skill prints its name with no link that would
                    // 404 on it (ADR-0011 §2).
                    ->map(static fn (Skill $skill): array => [
                        'id' => $skill->id,
                        'name' => $skill->name,
                        'sp_cost' => $skill->sp_cost,
                        'is_unique' => $skill->is_unique,
                        'url' => $skill->release_status === ReleaseStatus::GlobalReleased && $skill->name_is_client
                            ? route('skills.show', $skill)
                            : null,
                    ])
                    ->values()
                    ->all(),
            ])->values()->all(),
            // ADR-0026's widest-reach ruling (owner decision 3): the trainee's own career ladder, read
            // before any run exists for it.
            'traineeGoals' => $this->traineeGoalLadder($umamusume),
            'provenance' => $umamusume->dataSources->map(static fn (DataSource $source): array => [
                'url' => $source->url,
                'source_key' => $source->source_key,
                'fetched_at_display' => $source->fetched_at->timezone(config('uma.display_timezone'))->format('Y-m-d H:i T'),
            ])->values()->all(),
            // Passed rather than read off `$request` in the page: the form links have to carry the
            // opt-in into their address, and a page reaching for the request would be the only place
            // in this controller that did.
            'showUnconfirmed' => $showUnconfirmed,
        ]);
    }

    /**
     * The trainee's own ladder as the source states it, with no run to read progress from.
     *
     * `ADR-0026`'s widest-reach ruling (owner decision 3): the ladder a career asks of a trainee is
     * readable before one is started. The row keys are `CockpitController::traineeGoalRows()`'s keys and
     * every string comes from the same readers (`TraineeGoal::obligationLine()`, `yearLabel()`,
     * `turnWithinYear()`, `absenceReason()`), so the two surfaces cannot drift into two vocabularies for
     * one objective, which is the same reason the calendar turn arithmetic has one owner (`ADR-0015`).
     *
     * Three run-scoped fields are null here and each null is a statement about this page rather than a
     * missing value: `state`, `race_url` and the group's `summary` count are read from a run's records
     * (which entries finished, which fans were logged, which turn is next to play), and this screen has
     * no run. Printing a due-now mark or a cleared count without one would be an invented reading, and
     * PRD §6.11 keeps the block an integer pair or nothing at all.
     *
     * Two queries whatever the ladder's length: one for the rows, one batched for the calendar titles
     * `raceName()` prefers. The `FinalRound` filter is the plan §2 ruling, not a display choice: those
     * rows are the scenario's obligation and the Cockpit's Mandatory races group renders them.
     *
     * @return array{rows: list<array<string, mixed>>, note: string, summary: null, empty_note: string|null}
     */
    private function traineeGoalLadder(Umamusume $umamusume): array
    {
        /** @var Collection<int, TraineeGoal> $goals */
        $goals = TraineeGoal::query()
            ->where('umamusume_id', $umamusume->id)
            ->where('goal_type', '!=', TraineeGoalType::FinalRound->value)
            ->with('raceCatalogSlot')
            ->orderBy('career_year')
            ->orderBy('turn')
            ->orderBy('source_order')
            ->orderBy('source_race_choice')
            ->get();

        $rows = array_values($goals->map(static fn (TraineeGoal $goal): array => [
            'label' => $goal->obligationLine(),
            'state' => null,
            'year_label' => $goal->yearLabel(),
            'turn' => $goal->turnWithinYear(),
            'race_url' => null,
            'absence' => $goal->absenceReason(),
        ])->all());

        return [
            'rows' => $rows,
            'note' => 'Read from the objectives document this tool fetches for this character.'
                .' Whether a goal is met, which one is due now, and how many are cleared are read from'
                .' a run, so they print in the Cockpit while a career is in progress.',
            // The key the Cockpit's group carries, held null because this page has no run to count with.
            // A shape difference here would be a second contract for one ladder.
            'summary' => null,
            // One reader for the absence, shared with the Cockpit's group. It answers null for exactly the
            // rows that are not empty, so this page still carries no second boolean for the same fact.
            'empty_note' => TraineeGoal::ladderAbsenceReason($goals),
        ];
    }

    /**
     * The aptitude grades from strongest to weakest, as the client orders them.
     *
     * Only used to pick the best of the two surface letters or the four in a distance or style family;
     * the grade itself is printed verbatim, never mapped to a word.
     */
    private const APTITUDE_RANK = ['S' => 0, 'A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6, 'G' => 7];

    /**
     * The strongest letter on each of the three aptitude axes, for the list card.
     *
     * The card shows three letters instead of nine (`catalog.html` annotation 2), so each axis is read
     * down to its best grade: the surface family (Turf, Dirt), the distance family and the style
     * family. The axes and their labels come from `Umamusume::APTITUDE_AXES` through
     * `aptitudeAxes()`, the model's own owner of the ten-column list, so this screen cannot print a
     * second spelling of "Pace Chaser".
     *
     * Null is the same absence the detail page prints: `aptitude_turf` is the sentinel the parser
     * writes all ten or none, so no letter is reported rather than three empty pills.
     *
     * @return list<array{axis: string, label: string, letter: string}>|null
     */
    private function aptitudeSummary(Umamusume $umamusume): ?array
    {
        $axes = collect($umamusume->aptitudeAxes())->keyBy('key');

        $families = [
            'surface' => ['turf', 'dirt'],
            'distance' => ['sprint', 'mile', 'medium', 'long'],
            'style' => ['front_runner', 'pace_chaser', 'late_surger', 'end_closer'],
        ];

        $summary = [];

        foreach ($families as $axis => $keys) {
            $best = null;

            foreach ($keys as $key) {
                $letter = $axes->get($key)['letter'] ?? null;

                if ($letter === null || ! isset(self::APTITUDE_RANK[$letter])) {
                    continue;
                }

                if ($best === null || self::APTITUDE_RANK[$letter] < self::APTITUDE_RANK[$best['letter']]) {
                    $best = [
                        'axis' => $axis,
                        'label' => (string) ($axes->get($key)['label'] ?? $key),
                        'letter' => $letter,
                    ];
                }
            }

            if ($best !== null) {
                $summary[] = $best;
            }
        }

        return $summary === [] ? null : $summary;
    }

    /**
     * The release date the profile block prints, as the one stored answer (ADR-0008).
     *
     * Global wins when both are known, because that is the date a Global Trainer can act on; the
     * JP date is the fallback for a trainee Global has not shipped. A trainee with neither date
     * gets null rather than a dangling "(JP only)" suffix, and the page then prints nothing for
     * the field instead of an empty label.
     *
     * @return array{iso: string, display: string, is_global: bool}|null
     */
    private function releaseDate(Umamusume $umamusume): ?array
    {
        $date = $umamusume->global_debut_date ?? $umamusume->jp_debut_date;

        if ($date === null) {
            return null;
        }

        return [
            'iso' => $date->toDateString(),
            'display' => $date->format('M j, Y'),
            'is_global' => $umamusume->global_debut_date !== null,
        ];
    }

    /**
     * The ten aptitude letters, or null when the source published none.
     *
     * `aptitude_turf` is the sentinel: the parser writes all ten or none, so one null means the
     * whole set is absent and the page states that in words rather than drawing an empty grid
     * (D-220, `AptitudeGrid.vue`).
     *
     * @return array<string, string>|null
     */
    private function aptitudes(Umamusume $umamusume): ?array
    {
        if ($umamusume->aptitude_turf === null) {
            return null;
        }

        return [
            'turf' => $umamusume->aptitude_turf,
            'dirt' => $umamusume->aptitude_dirt,
            'sprint' => $umamusume->aptitude_sprint,
            'mile' => $umamusume->aptitude_mile,
            'medium' => $umamusume->aptitude_medium,
            'long' => $umamusume->aptitude_long,
            'front_runner' => $umamusume->aptitude_front_runner,
            'pace_chaser' => $umamusume->aptitude_pace_chaser,
            'late_surger' => $umamusume->aptitude_late_surger,
            'end_closer' => $umamusume->aptitude_end_closer,
        ];
    }

    /**
     * The profile block as an explicit array, or null when no profile fetch has run.
     *
     * Null is a third state, not an empty block: the common state before the first
     * `uma:fetch gametora-character-profiles`, which the page names in words. Every field inside
     * stays on one source, so a gap in this document is never filled by another (D-220).
     *
     * @return array{va_ja: string|null, va_en: string|null, birthday: array{iso: string|null, display: string}|null, height: int|null, three_sizes: array{b: int|null, h: int|null, w: int|null}|null}|null
     */
    private function profileShape(?UmamusumeProfile $profile): ?array
    {
        if ($profile === null) {
            return null;
        }

        return [
            'va_ja' => $profile->va_ja,
            'va_en' => $profile->va_en,
            'birthday' => $this->birthday($profile),
            'height' => $profile->height,
            'three_sizes' => $profile->hasThreeSizes()
                ? ['b' => $profile->three_sizes_b, 'h' => $profile->three_sizes_h, 'w' => $profile->three_sizes_w]
                : null,
        ];
    }

    /**
     * The birthday, split so a missing year reads as a missing year and never as a January 1st.
     *
     * `birth_year` is the only part this document omits. When month and day are present but the
     * year is not, the date is stated and the gap is named rather than filled; the `iso` is null
     * there so the page prints a `<span>` with the explanation instead of a `<time>` that would
     * assert a year the source never published (D-220).
     *
     * @return array{iso: string|null, display: string}|null
     */
    private function birthday(UmamusumeProfile $profile): ?array
    {
        if ($profile->hasFullBirthday()) {
            return [
                'iso' => sprintf('%04d-%02d-%02d', $profile->birth_year, $profile->birth_month, $profile->birth_day),
                'display' => Carbon::create($profile->birth_year, $profile->birth_month, $profile->birth_day)->format('M j, Y'),
            ];
        }

        if ($profile->birth_month !== null && $profile->birth_day !== null) {
            return [
                'iso' => null,
                'display' => Carbon::create(2000, $profile->birth_month, $profile->birth_day)->format('M j').' · year not published',
            ];
        }

        return null;
    }

    /**
     * The form the page opens on.
     *
     * Membership is checked against the *scoped* collection rather than re-queried, so a
     * `?form=` naming a form the current `show_unconfirmed` setting hides falls back instead of
     * revealing it: the query param is a convenience, and it must not become a way past the
     * disclosure the list enforces too.
     *
     * @param  Collection<int, CharacterCard>  $cards  already ordered by cardScope()
     */
    private function activeCard(Request $request, Collection $cards): ?CharacterCard
    {
        if ($cards->isEmpty()) {
            return null;
        }

        $requested = $request->query('form');

        if ($requested === null || $requested === '' || ! is_numeric($requested)) {
            return $cards->first();
        }

        $id = (int) $requested;

        return $cards->firstWhere('id', $id) ?? $cards->first();
    }

    /**
     * Cache the list page + its count under a version-embedded key. Promotion
     * bumps catalog:version, so a whole page group invalidates at once instead
     * of per-row churn (versioned-keys strategy, ARCHITECTURE §6).
     *
     * What is cached is the page's list of ids and its total, never the models.
     * `config/cache.php` ships `serializable_classes => false`, which is the
     * framework refusing to unserialize objects out of a persistent store: a
     * cached Eloquent collection comes back as `__PHP_Incomplete_Class` and the
     * view dies reading `->slug`. CACHE_STORE=array in phpunit.xml kept the suite
     * green because the array store hands back the very objects it was given, so
     * only the real app showed it (KI-2).
     *
     * @param  Builder<Umamusume>  $query
     * @return array{0: Collection<int, Umamusume>, 1: int}
     */
    private function cached($query, ?string $status, ?string $searchKey, int $page, int $pageSize, bool $showUnconfirmed, ?string $surface = null, ?string $distance = null, ?string $style = null): array
    {
        // Read, never written, and with no TTL of its own (F-10 / N-4): the counter is the promotion's, and
        // a `remember` here installed an expiring zero that a later increment inherited, so the page group
        // could go back to key namespace 1 an hour after it left it. Absent means nothing has promoted yet.
        $version = (int) Cache::get('catalog:version');
        $ttl = (int) config('uma.cache.ttl', 900);
        // Defensive key separation, not a live dependency: the flag changes which cards a
        // row shows, not which parent rows exist or how many there are, because
        // `cardScope()` constrains only the eager load. It stays in the key so a future
        // scope that filters parents by card state cannot reuse a page cached without it.
        // The three aptitude facets join the key because they CHANGE WHICH ROWS EXIST, not merely
        // what a row shows: the filter is applied to the list query itself. Without them here, a
        // Turf-only page and an unfiltered page at the same status/search/page would share one cached
        // id list, and the second Trainer to ask would be handed the first one's rows. They are
        // appended rather than folded into the status slot so an absent facet keeps producing the
        // byte-identical key it produced before this feature, and no existing entry is invalidated.
        $base = "catalog:list:v{$version}:".md5("{$status}|{$searchKey}|".($showUnconfirmed ? '1' : '0')."|{$surface}|{$distance}|{$style}");

        /** @var list<int> $ids */
        $ids = Cache::remember(
            "{$base}:p{$page}:{$pageSize}",
            $ttl,
            // The `orderBy('id')` is what makes `forPage()` repeatable: a name tie left unresolved can move
            // a trainee across a page boundary between two loads of the same data (KI-41).
            fn (): array => $query->orderBy('name')->orderBy('id')->forPage($page, $pageSize)->pluck('id')->all(),
        );

        $total = Cache::remember("{$base}:count", $ttl, fn () => $query->count());

        // Re-read the page by id rather than caching the models. The card tree is loaded
        // through the same `cardScope()` the list query used, so a cache hit cannot hand
        // back trainees whose forms went missing (KI-2's failure shape).
        $items = Umamusume::query()
            ->with(['cards' => $this->cardScope($showUnconfirmed)])
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        return [$items, $total];
    }

    /**
     * Which cards a page shows, and in what order.
     *
     * One method called by the list query, the cache re-read, and the detail page rather than
     * a closure threaded between them: `Relation::__call` forwards `when()` and
     * `orderBy()` to the underlying Builder, so a closure declared to take and
     * return a `HasMany` would be a type the runtime does not honour. Mutating the
     * relation in place and returning nothing keeps the declared shape true, and
     * the three call sites cannot drift because there is one code path.
     *
     * Debut first, then the Global release date, then the source's own card id as
     * the tiebreak two same-dated forms need to keep the page repeatable.
     *
     * @return Closure(Relation<*, *, *>): mixed
     */
    private function cardScope(bool $showUnconfirmed): Closure
    {
        return static function (Relation $cards) use ($showUnconfirmed): void {
            if (! $showUnconfirmed) {
                $cards->where('unconfirmed', false);
            }

            $cards->orderBy('is_debut_form', 'desc')
                ->orderBy('global_release_date')
                ->orderBy('card_id');
        };
    }
}
