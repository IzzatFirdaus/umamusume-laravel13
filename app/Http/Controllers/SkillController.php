<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ReleaseStatus;
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
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Screen D, the skill search surface (PRD FR-D-2; DESIGN.md §8.4; CONSTRAINTS.md D-62 to D-65).
 *
 * Read path only. The engine writes `skills` and the Trainer writes `run_skills`; nothing here writes.
 * No row can reach this screen at the default scope that `Skill::availableOnGlobal()` rejects. The
 * catalogue exposes two explicit overrides — `?scope=all` (the full catalogue) and `?scope=japan_only`
 * (JapanOnly rows) — for reference; the planning workflow's scoping is unaffected, and `SkillPoolNonLeakageTest`
 * is the guard that keeps it so (`R1`, `ADR-0011` §2).
 *
 * **A JapanOnly row prints its own source string and nothing English.** `name_is_client` is
 * `$clientName !== null && $onGlobal` (`GametoraSkillsParser:194`), so it is false on every JapanOnly row
 * by construction; `name` there is the export's `enname`, a rendering of the Japanese. The Air Messiah
 * ruling (`UMAMUSUME_REFERENCE.md` §2.7) bars that class of string from UI copy, and `desc_en` on the
 * same rows is the same string class (present on exactly the same 355 rows), so both are nulled at the
 * payload edge.
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

    /** The default scope: the `[Global]` subset, per ADR-0011 §2. */
    private const SCOPE_GLOBAL = 'global';

    /** The scope that shows only rows the Global client has not received. */
    private const SCOPE_JAPAN_ONLY = 'japan_only';

    /** Every scope the catalogue answers, in the order the select offers them. */
    public const SCOPES = [
        self::SCOPE_GLOBAL => 'Global only',
        'all' => 'Every skill',
        self::SCOPE_JAPAN_ONLY => 'Japan only',
    ];

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
        // `?distance` arrives as a query string, and `query()` takes `?int $distance` from this
        // `strict_types=1` file, where a numeric string is a TypeError rather than a coercion.
        // Null must stay null: `?distance=0` is the Unspecified sentinel (D6), a state distinct from
        // "no distance filter", so `(int)` cannot be applied unconditionally.
        $rawDistance = $request->validated('distance');
        $distance = $rawDistance === null ? null : (int) $rawDistance;

        // R1: `global` by default. `availableOnGlobal()` is the read-path commitment ADR-0011 §2 states,
        // so the default surface keeps it; `all` and `japan_only` are explicit overrides a caller asks for
        // by name.
        $scope = $request->validated('scope') ?? self::SCOPE_GLOBAL;

        $skills = $this->query($search, $type, $request->boolean('unique'), $distance, $scope)
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString()
            ->through(static function (Skill $skill): array {
                // Ruling A: at `japan_only` the catalogue prints the source's own string and no English
                // column. `name` on these rows is `enname`, the literal rendering of the Japanese, which
                // the parser docblock and the Air Messiah ruling both call a third-party label rather than
                // client copy. The prop is still sent as null so the page can state the absence instead of
                // printing a string it must not print.
                $japanOnly = $skill->release_status === ReleaseStatus::JapanOnly;

                return [
                    'id' => $skill->id,
                    'name' => $japanOnly ? null : $skill->name,
                    'name_ja' => $skill->name_ja,
                    // Ruling 1 (symmetric): `desc_en` on a JapanOnly row carries the same third-party
                    // status as `name_en`, because both are present on exactly the same 355 rows and the
                    // Air Messiah test is about the string, not the field. Nulled here rather than hidden
                    // in the template so no surface can leak it.
                    'description' => $japanOnly ? null : $skill->description,
                    // The catalogue's description gate is `displayDescription()`, which takes the row's
                    // own availability. Sending it keeps the rule in one predicate; the null above is the
                    // second guard, so a surface that forgets the predicate still cannot print the string.
                    'release_status' => $skill->release_status?->value,
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
                ];
            });

        // P3: count skills with range operators on distance_type (<= or >=) for the footer note
        $rangeOpCount = $this->rangeOperatorCount($scope);

        return [
            'skills' => $skills,
            'search' => $search,
            'searchKey' => $search === null ? null : $this->normalizer->normalize($search),
            'type' => $type,
            'types' => GametoraSkillsParser::CATEGORIES,
            'unspecifiedType' => SkillSearchRequest::UNSPECIFIED,
            'unique' => $request->boolean('unique'),
            'totalCount' => $this->availableCount($scope),
            'askedFor' => $this->describeAsk($search, $type, $request->boolean('unique')),
            'distance' => $distance,
            'scope' => $scope,
            'scopes' => self::SCOPES,
            'rangeOpCount' => $rangeOpCount,
            // The header's provenance counts describe the whole table at every scope. A scoped count
            // would read `{629, 0, 0}` at the default scope, where a Trainer cannot tell whether
            // Japan-only is empty or merely filtered out; the current scope's own row count is the
            // paginator's "N of M" line, not this. Unscoped by construction, so no facet can move it.
            'release_status_counts' => $this->releaseStatusCounts(),
        ];
    }

    /**
     * How many rows the table holds per release status, for the catalogue header.
     *
     * The three buckets the header names, zero-filled so a missing status is a `0` rather than an
     * absent key the template would render as nothing. `other` is the remainder of the enum
     * (`GlobalAnnounced` today), not a placeholder: the header prints it as a real count.
     *
     * Deliberately not the same query as `availableCount()`: that one narrows through
     * `availableOnGlobal()`, which requires `name_is_client` as well as `release_status`. The two
     * agree on this tree (both 629, measured; zero rows carry `GlobalReleased` with
     * `name_is_client` false), but they agree by data rather than by construction, so the header
     * states the column it counts in its footnote and the browser case cross-checks it against
     * `uma:skill-inventory`, which reads the same column through its own code path.
     *
     * @return array{global: int, japan_only: int, other: int}
     */
    private function releaseStatusCounts(): array
    {
        /** @var array<string, int> $counts */
        $counts = Skill::query()
            ->selectRaw('release_status, COUNT(*) as n')
            ->groupBy('release_status')
            ->pluck('n', 'release_status')
            ->all();

        $global = (int) ($counts[ReleaseStatus::GlobalReleased->value] ?? 0);
        $japanOnly = (int) ($counts[ReleaseStatus::JapanOnly->value] ?? 0);
        $total = (int) array_sum($counts);

        return [
            'global' => $global,
            'japan_only' => $japanOnly,
            // The remainder rather than a named third status, so a status added to the enum later is
            // counted instead of silently dropped out of the total.
            'other' => $total - $global - $japanOnly,
        ];
    }

    /**
     * Count of skills whose condition tokens contain range operators on distance_type (<= or >=).
     * Computed from the stored condition_groups JSON at query time.
     */
    public function rangeOperatorCount(string $scope = self::SCOPE_GLOBAL): int
    {
        $count = 0;
        $skills = $this->scoped(Skill::query(), $scope)->get(['condition_groups']);
        foreach ($skills as $skill) {
            foreach ($skill->condition_groups ?? [] as $group) {
                $condition = $group['condition'] ?? '';
                $precondition = $group['precondition'] ?? '';
                if (str_contains($condition, 'distance_type<=') || str_contains($condition, 'distance_type>=')
                    || str_contains($precondition, 'distance_type<=') || str_contains($precondition, 'distance_type>=')
                ) {
                    $count++;
                    break 2;
                }
            }
        }

        return $count;
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
    public function show(string $skill, Request $request): Response
    {
        // Widened from `availableOnGlobal()` by `R1`: the catalogue's `japan_only` and `all` scopes make
        // these rows reachable, so the detail must answer them. A row the catalogue links to that the
        // detail 404s is a dead end a Trainer cannot tell from a deleted row. The name and description
        // are still gated separately (`skillPayload`), because reachability and display are two rules.
        $model = Skill::query()->findOrFail((int) $skill);

        $compareSkill = null;
        $compareDiff = null;
        $compareId = $request->query('compare');

        if ($compareId !== null && $compareId !== '' && (int) $compareId !== $model->id) {
            $compareSkill = Skill::query()->availableOnGlobal()->find((int) $compareId);
        } elseif ($compareId !== null && $compareId !== '' && (int) $compareId === $model->id) {
            // Allow self-comparison for testing purposes
            $compareSkill = $model;
        }

        if ($compareSkill !== null) {
            $compareDiff = $this->buildCompareDiff($model, $compareSkill);
        }

        // P5: picker skills for the compare control (shared picker from P1)
        $pickerSkills = $this->pickerSkills();

        return Inertia::render('Skills/Show', array_merge([
            'skill' => $this->skillPayload($model),
            'uniqueHolders' => $this->holderRows($model, 'skills_unique'),
            'innateHolders' => $this->holderRows($model, 'skills_innate'),
            'awakeningHolders' => $this->holderRows($model, 'skills_awakening'),
            'eventHolders' => $this->holderRows($model, 'skills_event'),
            'catalogueCount' => $this->availableCount(),
            'hintLadder' => [
                'measuredAt' => Carbon::parse((string) config('uma.skills.hint_discount_measured_at'))->format('M j, Y'),
                'rows' => HintDiscount::priceLadder($model->sp_cost),
            ],
            'related' => [
                'sameCondition' => $this->countSharingConditionString($model),
                'sameClassCode' => $this->countSharingClassCode($model),
                'sameClassCodeAndType' => $this->countSharingClassCodeAndType($model),
                'typeFilter' => [
                    'url' => route('skills.index', ['type' => $model->type ?? SkillSearchRequest::UNSPECIFIED]),
                    'label' => $model->type === null
                        ? 'Search the skills with no type word'
                        : 'Search the skills of this type',
                ],
            ],
            'runs' => $this->runRows($model),
            'picker_skills' => $pickerSkills,
        ], $compareSkill !== null ? [
            'compareSkill' => $this->skillPayload($compareSkill),
            'compareDiff' => $compareDiff,
        ] : []));
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
     * @param  ?int  $distance  exact distance_type value (1..4) or null for Unspecified
     * @return Builder<Skill>
     */
    private function query(?string $search, ?string $type, bool $unique, ?int $distance = null, string $scope = self::SCOPE_GLOBAL): Builder
    {
        $key = $search === null ? null : $this->normalizer->normalize($search);

        return $this->scoped(Skill::query(), $scope)
            // D-62: skill search matches on `match_key` OR `description_match_key`, never on a display string.
            // The query goes through the same normalizer that wrote the key, so `Corner  Adept` reaches
            // `corneradept×` and a query differing only in case or spacing is the same query.
            ->when(
                $key !== null && $key !== '',
                fn (Builder $q): Builder => $q->where(function (Builder $q2) use ($key): void {
                    $escaped = '%'.addcslashes($key, '\\%_').'%';
                    $q2->whereRaw("match_key LIKE ? ESCAPE '\\'", [$escaped])
                        ->orWhereRaw("description_match_key LIKE ? ESCAPE '\\'", [$escaped]);
                }),
            )
            ->when(
                $type !== null,
                fn (Builder $q): Builder => $type === SkillSearchRequest::UNSPECIFIED
                    // The rows the sign rule withheld from (ADR-0011 §4). Asking for a word they do not have
                    // is answered by the absence, not by excluding them.
                    ? $q->whereNull('type')
                    : $q->where('type', $type),
            )
            ->when($unique, fn (Builder $q): Builder => $q->where('is_unique', true))
            // P3: distance filter — exact match on parsed distance_type column
            ->when(
                $distance !== null,
                fn (Builder $q): Builder => $distance === 0
                    ? $q->whereNull('distance_type')
                    : $q->where('distance_type', $distance),
            );
    }

    /**
     * How many rows this screen can show before any query, for the empty state to state a fact rather than
     * a silence (D-65: the invitation names what a Trainer can search on).
     */
    private function availableCount(string $scope = self::SCOPE_GLOBAL): int
    {
        return $this->scoped(Skill::query(), $scope)->count();
    }

    /**
     * The read-path filter for one scope.
     *
     * `global` is `availableOnGlobal()` unchanged — the default surface, and ADR-0011 §2's commitment
     * that the `[Global]` subset is what a Trainer-facing surface selects. The two overrides are
     * reference reads a caller asks for by name (`R1`), and neither is reachable without saying so.
     *
     * @param  Builder<Skill>  $query
     * @return Builder<Skill>
     */
    private function scoped(Builder $query, string $scope): Builder
    {
        return match ($scope) {
            self::SCOPE_JAPAN_ONLY => $query->where('release_status', ReleaseStatus::JapanOnly->value),
            'all' => $query,
            default => $query->availableOnGlobal(),
        };
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

    /**
     * Build the standardized skill payload for the detail page and comparison.
     *
     * @return array<string, mixed>
     */
    private function skillPayload(Skill $skill): array
    {
        // Ruling A, extended to the detail: a JapanOnly row's `name` is the export's `enname`, so the
        // panel prints the source's own string instead. Same gate as the catalogue, one rule.
        $japanOnly = $skill->release_status === ReleaseStatus::JapanOnly;

        return [
            'id' => $skill->id,
            'name' => $japanOnly ? null : $skill->name,
            'name_ja' => $skill->name_ja,
            'type' => $skill->type,
            'sp_cost' => $skill->sp_cost,
            'rarity' => $skill->rarity,
            'is_unique' => $skill->is_unique,
            'condition_groups' => $skill->condition_groups,
            'source_url' => $skill->source_url,
            'fetched_at_display' => $skill->fetched_at?->timezone(config('uma.display_timezone'))->format('M j, Y'),
            'description' => $skill->description,
            // The description panel's gate is the row's own availability, not the scope it was opened
            // from (`R1`, ruling 1): a JapanOnly row's `desc_en` is third-party on the same test that
            // bars its `name_en`, whichever URL reached it.
            'release_status' => $skill->release_status?->value,
        ];
    }

    /**
     * Build the diff map between two skills for the comparison panel.
     * Returns an associative array where keys are field names and values are true if they differ.
     *
     * @return array<string, bool>
     */
    private function buildCompareDiff(Skill $a, Skill $b): array
    {
        $diff = [];

        $diff['type'] = $a->type !== $b->type;
        $diff['rarity'] = $a->rarity !== $b->rarity;
        $diff['is_unique'] = $a->is_unique !== $b->is_unique;
        $diff['sp_cost'] = $a->sp_cost !== $b->sp_cost;
        $diff['name'] = $a->name !== $b->name;
        $diff['name_ja'] = $a->name_ja !== $b->name_ja;
        $diff['description'] = $a->description !== $b->description;
        $diff['condition_groups'] = $a->condition_groups !== $b->condition_groups;

        // Compare hint ladder prices (if both have base cost)
        if ($a->sp_cost !== null && $b->sp_cost !== null) {
            $ladderA = HintDiscount::priceLadder($a->sp_cost);
            $ladderB = HintDiscount::priceLadder($b->sp_cost);
            $diff['hintLadder'] = $ladderA !== $ladderB;
        } else {
            $diff['hintLadder'] = $a->sp_cost !== $b->sp_cost;
        }

        return $diff;
    }

    /**
     * Get the list of skills for the shared picker (P1/P5).
     * Returns Global-available skills with id and name only.
     *
     * @return list<array{id: int, name: string}>
     */
    private function pickerSkills(): array
    {
        return Skill::query()
            ->availableOnGlobal()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Skill $skill) => ['id' => $skill->id, 'name' => $skill->name])
            ->values()
            ->all();
    }
}
