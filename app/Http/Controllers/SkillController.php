<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SkillSearchRequest;
use App\Models\CharacterCard;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use App\Services\DataPipeline\NameNormalizer;
use App\Services\DataPipeline\Parsers\GametoraSkillsParser;
use App\Services\HintDiscount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Screen D, the skill search surface (PRD FR-D-2; DESIGN.md §8.4; CONSTRAINTS.md D-62 to D-65).
 *
 * Read path only. The engine writes `skills` and the Trainer writes `run_skills`; nothing here writes, and
 * no row can reach this screen that `Skill::availableOnGlobal()` rejects — a JP-only or third-party-named
 * skill on a Trainer-facing surface is a defect under `ADR-0011` §2 and PRD FR-D-3.
 *
 * **The rows print exactly the field set D-30 names for `Skill`** — name, name_ja, sp_cost, type, is_unique.
 * Two of §6.11's elements are not here because the data cannot honour them: the icon (`iconid` is on all
 * 1,910 source rows and nothing stores it, G-SK-19) and the description (both English description fields
 * fail the terminology table, G-SK-16/G-SK-20). The cost stepper and the discount badge are §6.11's run
 * state, not a catalog row's; no table stores a per-run hint level, so the detail prices the ladder for the
 * catalogue base only.
 *
 * **No cache.** `CatalogController` caches its page ids and total behind a version key; that machinery buys
 * nothing here and its invalidation is a lie for this table. Measured on the imported document, the whole
 * Global read is 0.8 ms and a narrowed LIKE is 0.3 ms against NFR-3's 200 ms budget, and `catalog:version`
 * is bumped by promotion — which skills bypass (`StoreSkills` writes straight through, `SkillsFetchTest`'s
 * `MatchCandidate::count() === 0`). A cached skills page would therefore keep answering after a re-fetch.
 */
class SkillController extends Controller
{
    /** The detail card's run list is capped; the total is stated beside it. */
    private const RUN_CARD_LIMIT = 20;

    public function __construct(private readonly NameNormalizer $normalizer) {}

    /**
     * @return Response|RedirectResponse a bad facet value redirects back with the field named; see the request.
     */
    public function index(SkillSearchRequest $request): Response|RedirectResponse
    {
        return $this->renderPage('Skills/Index', $this->indexProps($request));
    }

    /**
     * The same skill search as the Database hub's Skills area (SCREEN-023, plan §8 D17). One query
     * and one props array, shared rather than re-asked of a redirect: the slice brief refuses a
     * redirect here because no owner ruling authorises one. The pager keeps the request's own path,
     * so the Database URL pages within itself.
     */
    public function databaseIndex(SkillSearchRequest $request): Response|RedirectResponse
    {
        return $this->renderPage('Database/Skills', $this->indexProps($request));
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private function renderPage(string $page, array $props): Response
    {
        return Inertia::render($page, $props);
    }

    /**
     * The search page's data, shared by `index()` and `databaseIndex()`.
     *
     * @return array<string, mixed>
     */
    private function indexProps(SkillSearchRequest $request): array
    {
        $perPage = min(100, max(1, (int) ($request->query('pageSize') ?? 25)));

        $search = $request->validated('search');
        $type = $request->validated('type');

        $skills = $this->query($search, $type, $request->boolean('unique'))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString()
            ->through(static fn (Skill $skill): array => [
                'id' => $skill->id,
                'name' => $skill->name,
                'name_ja' => $skill->name_ja,
                'is_unique' => $skill->is_unique,
                'type' => $skill->type,
                'sp_cost' => $skill->sp_cost,
                // The activation predicate the source states, one entry per condition group, printed
                // verbatim in monospace beside the readable name (`skills.html` annotation 1). Null is
                // a group with no predicate, so the list carries only the strings the source wrote and
                // the page states the absence once rather than inventing "none".
                'conditions' => array_map(
                    static fn (array $group): ?string => $group['condition'] ?? null,
                    $skill->condition_groups ?? [],
                ),
            ]);

        return [
            'skills' => $skills,
            'search' => $search,
            'searchKey' => $search === null ? null : $this->normalizer->normalize($search),
            'type' => $type,
            'types' => GametoraSkillsParser::CATEGORIES,
            'unspecifiedType' => SkillSearchRequest::UNSPECIFIED,
            'unique' => $request->boolean('unique'),
            'totalCount' => $this->availableCount(),
            'askedFor' => $this->describeAsk($search, $type, $request->boolean('unique')),
        ];
    }

    /**
     * The detail page for one Global skill: the fields D-30 permits a Skill surface, the
     * trainees whose forms carry the skill, the runs that record it, and the absences the
     * data holds.
     *
     * The read starts from `availableOnGlobal()` for the reason index() states: no row
     * reaches a Trainer-facing surface that the scope rejects, and an unknown id is the
     * same refusal. The parameter is the local `skills.id`, the key a row on this tree
     * can name, not the source's export id, which no screen prints.
     */
    public function show(string $skill): Response
    {
        $model = Skill::query()->availableOnGlobal()->findOrFail((int) $skill);

        return Inertia::render('Skills/Show', [
            'skill' => [
                'id' => $model->id,
                'name' => $model->name,
                'name_ja' => $model->name_ja,
                'type' => $model->type,
                'sp_cost' => $model->sp_cost,
                // The export's class code, the integer ADR-0011 §3 keeps auditable. It renders as the
                // integer and never as a rarity word; the dated erratum records the rendering.
                'rarity' => $model->rarity,
                'is_unique' => $model->is_unique,
                'condition_groups' => $model->condition_groups,
                'source_url' => $model->source_url,
                // A stored instant renders through the display timezone (US-7); the view names the date only.
                'fetched_at_display' => $model->fetched_at?->timezone(config('uma.display_timezone'))->format('M j, Y'),
            ],
            'uniqueHolders' => $this->holderRows($model, 'skills_unique'),
            'innateHolders' => $this->holderRows($model, 'skills_innate'),
            'awakeningHolders' => $this->holderRows($model, 'skills_awakening'),
            'eventHolders' => $this->holderRows($model, 'skills_event'),
            // The provenance card states the size of the corpus this row belongs to, so a Trainer can
            // read one row against the whole (`skill-detail.html`, "Entries"). It is the same count the
            // index states, read through the same scope, so the two screens cannot disagree.
            'catalogueCount' => $this->availableCount(),
            // The ladder is a catalogue reference priced from this row's own base; the service owns the
            // arithmetic, so the detail and the run planner cannot disagree. No row marks a "current"
            // level: no table stores a per-run hint level.
            'hintLadder' => [
                'measuredAt' => Carbon::parse((string) config('uma.skills.hint_discount_measured_at'))->format('M j, Y'),
                'rows' => HintDiscount::priceLadder($model->sp_cost),
            ],
            'related' => [
                // The wireframe's three counts. Each reads a stored field; a missing input is an absence.
                'sameCondition' => $this->countSharingConditionString($model),
                'sameClassCode' => $this->countSharingClassCode($model),
                'sameClassCodeAndType' => $this->countSharingClassCodeAndType($model),
                // Built here, not in the page: the label states the only facet the index can answer.
                'typeFilter' => [
                    'url' => route('skills.index', ['type' => $model->type ?? SkillSearchRequest::UNSPECIFIED]),
                    'label' => $model->type === null
                        ? 'Search the skills with no type word'
                        : 'Search the skills of this type',
                ],
            ],
            'runs' => $this->runRows($model),
        ]);
    }

    /**
     * How many other Global skills share at least one stored condition string with this row.
     *
     * The comparison is the stored `condition` string, the unit the wireframe's Related card names. A
     * skill with two groups is still one skill, so the subquery tests the group array and the outer query
     * counts rows: a peer sharing either string counts once.
     *
     * ponytail: `json_each` over the JSON column, fine at 623 rows; a generated column is the upgrade if
     * this ever runs per row in a list.
     */
    private function countSharingConditionString(Skill $model): ?int
    {
        $conditions = array_values(array_filter(array_map(
            static fn (array $group): ?string => is_string($group['condition'] ?? null) ? $group['condition'] : null,
            $model->condition_groups ?? [],
        ), static fn (?string $condition): bool => $condition !== null && $condition !== ''));

        if ($conditions === []) {
            return null;
        }

        $placeholders = implode(', ', array_fill(0, count($conditions), '?'));

        return Skill::query()
            ->availableOnGlobal()
            ->whereKeyNot($model->id)
            ->whereRaw(
                "EXISTS (SELECT 1 FROM json_each(skills.condition_groups) WHERE json_extract(json_each.value, '$.condition') IN ({$placeholders}))",
                $conditions,
            )
            ->count();
    }

    /**
     * How many other Global skills carry this row's stored class code.
     *
     * `skills.rarity` is the export's class code (1-6), not a rarity word; it is the same field the engine
     * card prints, so the card's two class-code rows count one thing.
     */
    private function countSharingClassCode(Skill $model): ?int
    {
        if ($model->rarity === null) {
            return null;
        }

        return Skill::query()
            ->availableOnGlobal()
            ->whereKeyNot($model->id)
            ->where('rarity', $model->rarity)
            ->count();
    }

    /**
     * How many other Global skills carry this row's class code and its derived type.
     *
     * A missing half is an absence rather than a match: two untyped skills do not share a type.
     */
    private function countSharingClassCodeAndType(Skill $model): ?int
    {
        if ($model->rarity === null || $model->type === null) {
            return null;
        }

        return Skill::query()
            ->availableOnGlobal()
            ->whereKeyNot($model->id)
            ->where('rarity', $model->rarity)
            ->where('type', $model->type)
            ->count();
    }

    /**
     * The runs that record this skill, newest first, capped for the card.
     *
     * Status is the pivot's stored string (`RunSkill`'s contract); the level is the unique-skill level
     * (`skill_levels`, ADR-0029 D4), never a hint level, which no table stores. The label keeps the run id
     * because two careers of one trainee otherwise read alike, and the scenario label is config's so the
     * slug never reaches the screen.
     *
     * @return array{rows: list<array{id: int, label: string, status: string, level: int|null, url: string}>, total: int}
     */
    private function runRows(Skill $model): array
    {
        $matching = TrainingRun::query()
            ->whereHas('skills', static fn (Builder $builder): Builder => $builder->where('skills.id', $model->id));

        $total = (clone $matching)->count();

        $runs = $matching
            ->with([
                'umamusume:id,name',
                'skills' => static fn ($builder) => $builder->where('skills.id', $model->id),
                'skillLevels' => static fn ($builder) => $builder->where('skill_id', $model->id),
            ])
            ->latest('created_at')
            ->latest('id')
            ->limit(self::RUN_CARD_LIMIT)
            ->get();

        return [
            'total' => $total,
            'rows' => $runs->map(static function (TrainingRun $run): array {
                $status = $run->skills->first()?->pivot?->status;

                return [
                    'id' => $run->id,
                    'label' => implode(' · ', array_filter([
                        $run->umamusume->name,
                        $run->hasScenario()
                            ? (config('scenarios.scenarios.'.$run->scenario.'.label') ?? $run->scenario)
                            : null,
                        "run {$run->id}",
                    ])),
                    'status' => is_string($status) ? $status : '',
                    'level' => $run->skillLevels->first()?->current_level,
                    'url' => route('runs.cockpit', $run),
                ];
            })->values()->all(),
        ];
    }

    /**
     * What the Trainer narrowed the catalog by, for the no-results state to name the ask instead of
     * repeating a query back at them. An empty set of conditions cannot reach that state: with rows in the
     * table and no filter, the first page has rows.
     */
    private function describeAsk(?string $search, ?string $type, bool $unique): string
    {
        $parts = array_filter([
            $search === null ? null : sprintf('“%s”', $search),
            $type === null ? null : "type {$type}",
            $unique ? 'unique skills' : null,
        ]);

        return $parts === [] ? 'those conditions' : implode(' + ', $parts);
    }

    /**
     * The filter, built once so the count and the page cannot disagree about what "these rows" means.
     *
     * @return Builder<Skill>
     */
    private function query(?string $search, ?string $type, bool $unique): Builder
    {
        $key = $search === null ? null : $this->normalizer->normalize($search);

        return Skill::query()
            ->availableOnGlobal()
            // D-62: skill search matches on `match_key`, never on a display string. The query goes through
            // the same normalizer that wrote the key, so `Corner  Adept` reaches `corneradept×` and a query
            // differing only in case or spacing is the same query.
            ->when(
                $key !== null && $key !== '',
                fn (Builder $q): Builder => $q->whereRaw(
                    // SQLite has no default LIKE escape character, so the clause names one, and `%` and `_`
                    // in the query are escaped before it: `NameNormalizer` leaves both alone, so unescaped a
                    // Trainer typing `%` would get the whole catalog. That is KI-26 on `/umamusume`, where it
                    // was reproduced and not fixed. The value stays a bound parameter, so this is a
                    // pattern-escaping question, not an SQL one.
                    "match_key LIKE ? ESCAPE '\\'",
                    ['%'.addcslashes($key, '\\%_').'%'],
                ),
            )
            ->when(
                $type !== null,
                fn (Builder $q): Builder => $type === SkillSearchRequest::UNSPECIFIED
                    // The rows the sign rule withheld from (ADR-0011 §4). Asking for a word they do not have
                    // is answered by the absence, not by excluding them.
                    ? $q->whereNull('type')
                    : $q->where('type', $type),
            )
            ->when($unique, fn (Builder $q): Builder => $q->where('is_unique', true));
    }

    /**
     * How many rows this screen can show before any query, for the empty state to state a fact rather than
     * a silence (D-65: the invitation names what a Trainer can search on).
     */
    private function availableCount(): int
    {
        return Skill::query()->availableOnGlobal()->count();
    }

    /**
     * The holder collection as the page props shape: name, slug, and the trainee page URL.
     *
     * The URL is built here rather than in the page for the reason the catalog index maps its
     * rows: a route name stays server-side, so a page never assembles a link out of a string.
     *
     * @return list<array{name: string, slug: string, url: string}>
     */
    private function holderRows(Skill $skill, string $column): array
    {
        return array_values($this->holders($skill, $column)
            ->map(static fn (Umamusume $holder): array => [
                'name' => $holder->name,
                'slug' => $holder->slug,
                'url' => route('catalog.show', $holder->slug),
            ])
            ->values()
            ->all());
    }

    /**
     * The trainees whose confirmed forms list this skill's export id on one card column,
     * one entry per trainee, ordered by name.
     *
     * The lookup reads the card-grain lists in reverse: `skills_unique`, `skills_innate`,
     * `skills_awakening` and `skills_event`, the four lists the 2026-10-02 D-30 amendment
     * admits for grouping a card's own skills, read the other way. Unconfirmed forms sit
     * behind the same disclosure the catalog list applies, so a form that list hides cannot
     * surface here either.
     *
     * @return Collection<int, Umamusume>
     */
    private function holders(Skill $skill, string $column): Collection
    {
        if ($skill->export_id === null) {
            return collect();
        }

        try {
            return CharacterCard::query()
                ->where('unconfirmed', false)
                ->where(function (Builder $query) use ($column, $skill) {
                    $query->whereJsonContains($column, $skill->export_id)
                        ->whereRaw('json_valid('.$column.')');
                })
                ->with('umamusume:id,slug,name')
                ->get()
                ->sortBy(fn (CharacterCard $card): string => $card->umamusume->name)
                ->unique('umamusume_id')
                ->map(fn (CharacterCard $card): Umamusume => $card->umamusume)
                ->values();
        } catch (QueryException $e) {
            // Column doesn't exist yet (migration not run) or JSON is malformed
            if ($e->getCode() === 'HY000' && str_contains($e->getMessage(), 'no such column')) {
                return collect();
            }

            throw $e;
        }
    }
}
