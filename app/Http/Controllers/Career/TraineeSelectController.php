<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Http\Controllers\Controller;
use App\Http\Requests\Career\StoreTraineeRequest;
use App\Http\Requests\Career\TraineeSearchRequest;
use App\Models\CharacterCard;
use App\Models\Skill;
use App\Models\Umamusume;
use App\Services\Career\SetupDraft;
use App\Services\DataPipeline\ArtworkMirror;
use App\Services\PageSize;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Trainee Selection, `SCREEN-003` (PRD FR-A-1, `ADR-0020` §1). Step 2 of the setup wizard.
 *
 * **Reads the catalog, writes the session draft, creates nothing.** The choice goes through `SetupDraft`
 * exactly as the scenario choice does, so no run row appears before Preflight: an `Active` run made here
 * would surface as a phantom career on SCR-CAR-001 and as a phantom inheritance target in SCR-CAR-006,
 * which is the reasoning `SetupDraft`'s own docblock carries. `store()` redirects back to this step so the
 * pressed state on the next render is read from stored data, not from what the client still holds.
 *
 * **Every filter is a real column, and the two brief filters that are not are named below.**
 * `TraineeSearchRequest::FACETS` maps Surface, Distance and Running style onto the ten
 * `umamusume.aptitude_*` columns, and the letter domain comes from
 * `GametoraCharacterParser.php:143` (`preg_match('/^[SABCDEFG]$/')`), which is why `S` is offered although
 * the seeded roster peaks at `A`: the parser can write it. Omitted, with their citations:
 *
 * - **Growth rate.** No column on any table holds one. `docs/research-scratch/GOVERNANCE.md:600` defers
 *   `growth_rates` ("No column added now"), and `docs/research-scratch/DESIGN-CORPUS.md:727-728` forbids
 *   the figure on a rendered surface ("no effect magnitude, level, rarity or growth rate may appear …
 *   since the tool holds none of those"). The brief's card mock prints "Speed +20%"; there is no such datum.
 * - **Scenario suitability.** No table holds it. `config/scenarios.php`'s `scenario_links` is a
 *   per-scenario *cast list*, not a ranking: URA's one entry names a staff member who has no `umamusume`
 *   row, and Trackblazer's list is empty with a note saying so. Reading a cast list as suitability would
 *   turn a listing into a judgement this repository holds no source for.
 *
 * **Not cached** (`AGENTS.md` §8: trainer-data reads are never cached), and the catalog counter is not
 * this screen's to borrow: a wizard list has no promotion version to invalidate against. N+1 is handled by
 * eager loading the card tree once per page instead.
 */
class TraineeSelectController extends Controller
{
    public function show(TraineeSearchRequest $request, ArtworkMirror $mirror): Response
    {
        $draft = SetupDraft::read();

        $query = Umamusume::query()->with(['cards' => $this->cardScope()]);

        $this->applySearch($query, $request);
        $this->applyFacets($query, $request);
        $this->applySkillFilter($query, $request);
        $this->applySort($query, $request);

        $trainees = $query->paginate(PageSize::clamp($request->query('pageSize')), ['*'], 'page')
            ->withQueryString()
            ->through(fn (Umamusume $umamusume): array => $this->row($umamusume, $mirror));

        return Inertia::render('Career/TraineeSelect', [
            'trainees' => $trainees,
            'selected' => $draft['umamusume_id'],
            // The stored trainee's name, sent with the selection rather than looked up in the roster.
            // The list is paginated and filtered, so the chosen trainee is frequently not on the page
            // shown, and reading her name from that list answered `N/A` beside a flash that said she
            // was set (D5).
            'selectedName' => $draft['umamusume_id'] === null
                ? null
                : Umamusume::query()->whereKey($draft['umamusume_id'])->value('name'),
            // The wizard carries the scenario forward instead of asking again, so the step names it. The
            // label comes from `SetupDraft`, which reads `config/scenarios.php`, so no scenario name is
            // written into this page or this controller (D-240, gate G-33).
            'scenarioLabel' => SetupDraft::scenarioLabel(),
            'scenarioPending' => $draft['scenario'] === null,
            'filters' => [
                'search' => $request->validated('search'),
                'surface' => $request->validated('surface'),
                'distance' => $request->validated('distance'),
                'style' => $request->validated('style'),
                'skill' => $request->validated('skill') === null ? null : (int) $request->validated('skill'),
            ],
            'sortBy' => $request->sortKey(),
            'direction' => $request->input('direction') === 'desc' ? 'desc' : 'asc',
            // The facet vocabulary the filter form offers, sent rather than typed into the page so the
            // column groups and the letter domain have exactly one owner: `TraineeSearchRequest`.
            'facets' => [
                ['param' => 'surface', 'label' => 'Surface', 'letters' => TraineeSearchRequest::LETTERS],
                ['param' => 'distance', 'label' => 'Distance', 'letters' => TraineeSearchRequest::LETTERS],
                ['param' => 'style', 'label' => 'Running style', 'letters' => TraineeSearchRequest::LETTERS],
            ],
            'sortKeys' => [
                ['value' => 'name', 'label' => 'Name'],
                ['value' => 'turf', 'label' => 'Turf'],
                ['value' => 'dirt', 'label' => 'Dirt'],
                ['value' => 'sprint', 'label' => 'Sprint'],
                ['value' => 'mile', 'label' => 'Mile'],
                ['value' => 'medium', 'label' => 'Medium'],
                ['value' => 'long', 'label' => 'Long'],
                ['value' => 'front_runner', 'label' => 'Front runner'],
                ['value' => 'pace_chaser', 'label' => 'Pace chaser'],
                ['value' => 'late_surger', 'label' => 'Late surger'],
                ['value' => 'end_closer', 'label' => 'End closer'],
            ],
            'uniqueSkills' => $this->uniqueSkillOptions(),
        ]);
    }

    /**
     * Writes the trainee to the draft and returns to this step, so the selection the Trainer sees is the
     * stored one. Mirrors `ScenarioSelectController::store()`.
     */
    public function store(StoreTraineeRequest $request): RedirectResponse
    {
        SetupDraft::write(['umamusume_id' => (int) $request->validated('umamusume_id')]);

        // "Chosen", not "Selected", because the state word on the card is the one the wizard's own
        // vocabulary uses, and the message names the trainee rather than an id.
        $trainee = Umamusume::find((int) $request->validated('umamusume_id'));

        return redirect()
            ->route('career.trainee')
            ->with('status', $trainee === null ? 'Trainee set.' : "Trainee set: {$trainee->name}.");
    }

    /**
     * One row of the roster list. Every value is read off a column; nothing here is inferred.
     *
     * Rarity is printed **per costume form** and never as one trainee fact, because there is no
     * trainee-level rarity column to read: `character_cards.rarity` is the only rarity in the schema, and
     * an aggregate over it (a "max rarity" badge) would state a trainee property the data does not carry.
     * The aptitude letters are the trainee's own ten columns and appear once, not per form (ADR-0008).
     *
     * @return array<string, mixed>
     */
    private function row(Umamusume $umamusume, ArtworkMirror $mirror): array
    {
        /** @var Collection<int, CharacterCard> $cards */
        $cards = $umamusume->cards;

        // The frame is identity, and identity is the debut form (`DESIGN.md` §4.7, `ADR-0021`); the
        // `?? first()` branch is the same fallback the catalog list uses, so the two screens cannot
        // disagree about which form a trainee is pictured by.
        $headerCard = $cards->first(static fn (CharacterCard $card): bool => $card->is_debut_form)
            ?? $cards->first();

        return [
            'id' => $umamusume->id,
            'slug' => $umamusume->slug,
            'name' => $umamusume->name,
            'name_ja' => $umamusume->name_ja,
            'release_status_label' => $umamusume->release_status->label(),
            'form_count' => $cards->count(),
            'artworkURL' => $headerCard === null
                ? null
                : $mirror->url('card_portrait', (int) $headerCard->card_id),
            'aptitudes' => $this->aptitudes($umamusume),
            'forms' => $cards->map(static fn (CharacterCard $card): array => [
                'id' => $card->id,
                'title' => $card->title,
                'rarity_label' => $card->rarity->label(),
                'rarity_stars' => $card->rarity->stars(),
                'rarity_word' => $card->rarity->word(),
                'is_debut_form' => $card->is_debut_form,
            ])->values()->all(),
        ];
    }

    /**
     * The trainee's ten aptitude letters, or null when the source published none.
     *
     * `aptitude_turf` is the sentinel, the same one `CatalogController::aptitudes()` reads: the parser
     * writes all ten columns or none, so a null there means the whole set is absent and the page states
     * that in words rather than drawing an empty grid (D-220).
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
     * Name or slug substring. `match_key` is the catalog's normalized search column and would be the
     * tidier read, but the brief's Character filter is stated on the display name, and a Trainer typing
     * "teio" expects the name they can see.
     *
     * The `addcslashes()` is load-bearing, and copied from `CatalogController::index()`: `normalize()`
     * and LIKE both leave `%` and `_` alone, so without the escape a Trainer typing `%` gets a wildcard
     * and the whole roster back, which reads as a filter that matched everything.
     *
     * @param  Builder<Umamusume>  $query
     */
    private function applySearch(Builder $query, TraineeSearchRequest $request): void
    {
        $search = $request->validated('search');

        if (! is_string($search) || $search === '') {
            return;
        }

        $like = '%'.addcslashes($search, '%_\\').'%';

        $query->where(static fn (Builder $sub): Builder => $sub
            ->where('name', 'like', $like)
            ->orWhere('slug', 'like', $like));
    }

    /**
     * The three aptitude facets. A trainee matches a facet when **any** column in that facet's group
     * holds the letter, so `distance=A` asks "A on some distance" and leaves the reading of which one to
     * the row's own grid. The column list comes from `TraineeSearchRequest::FACETS`, so the request that
     * validates the letter and the query that filters on it cannot disagree about which columns a facet
     * owns.
     *
     * The OR is written per column rather than as `whereIn($columns, …)`, because a multi-column
     * `whereIn` compiles to a tuple `IN`, which SQLite does not support: passing the column group to it
     * asks for `(aptitude_turf, aptitude_dirt) IN (('A'))`, not for either column equalling the letter.
     *
     * @param  Builder<Umamusume>  $query
     */
    private function applyFacets(Builder $query, TraineeSearchRequest $request): void
    {
        foreach (TraineeSearchRequest::FACETS as $param => $columns) {
            $letter = $request->validated($param);

            if (! is_string($letter)) {
                continue;
            }

            $query->where(static function (Builder $sub) use ($columns, $letter): void {
                foreach ($columns as $column) {
                    $sub->orWhere($column, $letter);
                }
            });
        }
    }

    /**
     * The unique-skill filter: trainees owning a costume form whose `skills_unique` list carries the
     * requested export id. `skills_unique` is a JSON column, so this is a JSON containment test rather
     * than a join, and it runs over the trainee's cards because a unique skill belongs to a form.
     *
     * @param  Builder<Umamusume>  $query
     */
    private function applySkillFilter(Builder $query, TraineeSearchRequest $request): void
    {
        $skill = $request->validated('skill');

        if ($skill === null) {
            return;
        }

        $query->whereHas('cards', static fn (Builder $cards): Builder => $cards
            ->whereJsonContains('skills_unique', (int) $skill));
    }

    /**
     * The allowlisted sort. `TraineeSearchRequest::SORTS` is the only set of column names that can reach
     * `orderBy()`, which is what makes degrading an unknown key safe rather than merely quiet: the key
     * never becomes SQL.
     *
     * An aptitude sort is **not** an alphabetical sort. The letters run S (best) through G (worst), and
     * ASCII order would put A first and leave S at the end, so ordering `aptitude_long` directly would
     * present a G trainee as the best long-distance choice in the list. The rank expression below is the
     * fix, and it is one expression rather than a per-column case because the seven letters are one
     * domain. A trainee with no letter in the column ranks last, which is the honest position for an
     * unpublished aptitude.
     *
     * @param  Builder<Umamusume>  $query
     */
    private function applySort(Builder $query, TraineeSearchRequest $request): void
    {
        $column = $request->sortColumn() ?? TraineeSearchRequest::SORTS[TraineeSearchRequest::DEFAULT_SORT];
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        if ($column === 'name') {
            $query->orderBy('name', $direction);
        } else {
            $query->orderByRaw($this->rankExpression($column).' '.$direction);
            // The name is the tiebreak, and it is always needed: 67 trainees share seven letters, so a
            // sort on one column without a deterministic tie moves a trainee across a page boundary
            // between two loads of the same data (KI-41's shape).
            $query->orderBy('name');
        }

        $query->orderBy('id');
    }

    /**
     * Raw SQL ranking a letter column best-to-worst, so `ORDER BY` follows the client's scale instead of
     * the alphabet's.
     *
     * `$column` is interpolated because SQL takes identifiers where a placeholder cannot go. It is a
     * value out of `TraineeSearchRequest::SORTS`, never request input, and there is no binding to speak
     * of; the same shape and the same reasoning as `CatalogController::normalizedColumn()`.
     */
    private function rankExpression(string $column): string
    {
        $ranked = '';

        foreach (TraineeSearchRequest::LETTERS as $index => $letter) {
            $ranked .= " when '{$letter}' then {$index}";
        }

        // The fallthrough is the null case: no letter published ranks past every letter that is.
        return "case {$column}{$ranked} else 99 end";
    }

    /**
     * The unique-skill options the filter offers: every skill id the catalog's own costume forms list as
     * a unique, resolved through `Skill::scopeAvailableOnGlobal()` so a Global Trainer is never asked to
     * filter by a skill their client cannot show (`ADR-0011` §2).
     *
     * `ponytail:` the id set is built by reading `skills_unique` off all 106 card rows in PHP, because
     * the column is JSON and a SQL distinct over it is not portable. On this catalog that is one bounded
     * query per page load; the upgrade path is a `skill_card` pivot if the roster ever grows an order of
     * magnitude.
     *
     * @return list<array<string, mixed>>
     */
    private function uniqueSkillOptions(): array
    {
        $exportIds = CharacterCard::query()
            ->whereNotNull('skills_unique')
            ->pluck('skills_unique')
            ->flatten()
            ->filter()
            ->unique()
            ->values();

        if ($exportIds->isEmpty()) {
            return [];
        }

        return Skill::query()
            ->availableOnGlobal()
            ->whereIn('export_id', $exportIds)
            ->orderBy('name')
            ->get(['name', 'export_id'])
            ->map(static fn (Skill $skill): array => [
                'export_id' => $skill->export_id,
                'name' => $skill->name,
            ])
            ->values()
            ->all();
    }

    /**
     * Which costume forms a row shows, in the catalog's own order: debut first, then the Global release
     * date, then the source's card id as the tiebreak two same-dated forms need to keep a page
     * repeatable.
     *
     * `CatalogController::cardScope()` owns that ordering and is `private`, so this is the same rule
     * restated rather than the same code called; unlike its version there is no `show_unconfirmed` lever
     * here, because a wizard step that let a Trainer start a career on a form no second source has
     * confirmed would be a second opinion on disclosure the catalog already owns.
     *
     * @return Closure(HasMany<CharacterCard, Umamusume>): void
     */
    private function cardScope(): Closure
    {
        return static function (HasMany $cards): void {
            $cards->where('unconfirmed', false)
                ->orderBy('is_debut_form', 'desc')
                ->orderBy('global_release_date')
                ->orderBy('card_id');
        };
    }
}
