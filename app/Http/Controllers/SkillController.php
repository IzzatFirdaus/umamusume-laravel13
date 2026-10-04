<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SkillSearchRequest;
use App\Models\CharacterCard;
use App\Models\Skill;
use App\Models\Umamusume;
use App\Services\DataPipeline\NameNormalizer;
use App\Services\DataPipeline\Parsers\GametoraSkillsParser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

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
 * state, not a catalog row's, and hint level is out of this slice's scope entirely.
 *
 * **No cache.** `CatalogController` caches its page ids and total behind a version key; that machinery buys
 * nothing here and its invalidation is a lie for this table. Measured on the imported document, the whole
 * Global read is 0.8 ms and a narrowed LIKE is 0.3 ms against NFR-3's 200 ms budget, and `catalog:version`
 * is bumped by promotion — which skills bypass (`StoreSkills` writes straight through, `SkillsFetchTest`'s
 * `MatchCandidate::count() === 0`). A cached skills page would therefore keep answering after a re-fetch.
 */
class SkillController extends Controller
{
    public function __construct(private readonly NameNormalizer $normalizer) {}

    /**
     * @return View|RedirectResponse a bad facet value redirects back with the field named; see the request.
     */
    public function index(SkillSearchRequest $request): View|RedirectResponse
    {
        $perPage = min(100, max(1, (int) ($request->query('pageSize') ?? 25)));

        $search = $request->validated('search');
        $type = $request->validated('type');

        $skills = $this->query($search, $type, $request->boolean('unique'))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('skills.index', [
            'skills' => $skills,
            'search' => $search,
            'searchKey' => $search === null ? null : $this->normalizer->normalize($search),
            'type' => $type,
            'types' => GametoraSkillsParser::CATEGORIES,
            'unspecifiedType' => SkillSearchRequest::UNSPECIFIED,
            'unique' => $request->boolean('unique'),
            'totalCount' => $this->availableCount(),
            'askedFor' => $this->describeAsk($search, $type, $request->boolean('unique')),
        ]);
    }

    /**
     * The detail page for one Global skill: the fields D-30 permits a Skill surface, the
     * trainees whose forms carry the skill, and the absences the data holds.
     *
     * The read starts from `availableOnGlobal()` for the reason index() states: no row
     * reaches a Trainer-facing surface that the scope rejects, and an unknown id is the
     * same refusal. The parameter is the local `skills.id`, the key a row on this tree
     * can name, not the source's export id, which no screen prints.
     */
    public function show(string $skill): View
    {
        $model = Skill::query()->availableOnGlobal()->findOrFail((int) $skill);

        return view('skills.show', [
            'skill' => $model,
            'uniqueHolders' => $this->holders($model, 'skills_unique'),
            'innateHolders' => $this->holders($model, 'skills_innate'),
            'awakeningHolders' => $this->holders($model, 'skills_awakening'),
            'eventHolders' => $this->holders($model, 'skills_event'),
        ]);
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
