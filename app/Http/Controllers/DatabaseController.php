<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\RaceCatalogSlot;
use App\Models\Umamusume;
use App\Services\PageSize;
use App\Services\RaceFacts;
use App\Services\ScenarioCaps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The Database hub and its two new areas (SCREEN-023, plan §8 D17).
 *
 * Three of the five areas live here only as route names: Trainees, Support Cards and Skills are the
 * ported catalog surfaces, and their pages render the same shared list component the originals do.
 * The controllers that own their queries are the ones that render those three pages, because a query
 * should not be asked twice. `routes/web.php` binds `/database/trainees` to
 * `CatalogController::databaseIndex` and its two siblings the same way, so this file only holds the
 * hub and the two screens that did not exist before this slice.
 *
 * Read path only. Nothing here writes, and no area offers a form.
 */
class DatabaseController extends Controller
{
    private const RACE_SORT_COLUMNS = [
        'calendar' => null,
        'name' => 'title',
        'grade' => 'grade_code',
        'distance' => 'distance',
        'fans' => 'fans_needed',
    ];

    /**
     * Tables carrying their own per-row provenance stamp (N2.11).
     *
     * Each is a reference table whose rows record `source_url` + `fetched_at` (ADR-0003 Amendment
     * R3). The Data sources ledger only knows requests this tool made, so a dataset committed with
     * its own stamp has a real date the ledger never recorded. Listed once here rather than queried
     * per row, so the union arm costs one pass per table.
     *
     * @var list<string>
     */
    private const INLINE_STAMP_TABLES = [
        'race_catalog_slots',
        'skills',
        'character_cards',
        'umamusume_profiles',
        'support_cards',
        'support_effects',
    ];

    /**
     * The hub: the eight areas the Database screen lists, all read-only.
     *
     * *Dated correction 2026-10-22 (UX audit F-14): the number above was already off by one when it was
     * written — the list held eight and omitted `/database/sources`, which `routes/web.php:301` serves
     * through `sources()` below. A ninth row is added, so "eight" is now nine. The count is not restated
     * here as the authority; `DatabaseHubSourcesLinkTest` pins that the payload carries the door.*
     *
     * No counts on the area links. Each area's own page states how many rows it holds, and the
     * catalog's first screen is filtered to `[Global]` while `umamusume` also holds JP-only rows, so a
     * hub number would be a figure no area corroborates on arrival.
     *
     * The three reference views (`SCR-SYS-008` to `010`) are the last three the brief lists. They were
     * once a "Not in this build" section; that label was wrong on both counts (the sections are in the
     * brief, and the source data exists), so it is deleted rather than softened.
     */
    public function index(): Response
    {
        // One grouped query for the four live areas, each with two figures: the rows the table holds and
        // the rows the area's own door leads with. The hub used to print the raw count, which disagreed
        // with the door a Trainer clicks through to (F-03): the Skills tile said 1,910 while the Skills
        // door said 623, because the door scopes to the rows released on Global and carrying a client
        // name, and the hub named no scope at all. The scoped figure is the tile's number, so the door
        // and its door always agree; the raw figure rides beside it, in the door's own "N of M" grammar.
        //
        // The three scoped arms repeat each door's own default predicate (`CatalogController::indexProps`
        // for trainees, `SupportCardController::databaseIndex` for supports, `Skill::scopeAvailableOnGlobal`
        // for skills). Races has no scope, so both its figures are the same number and the tile prints one.
        $live = collect(DB::select(
            "select 'trainees' as area, count(*) as total, sum(case when release_status = 'GlobalReleased' then 1 else 0 end) as scoped from umamusume "
            ."union all select 'supports', count(*), sum(case when release_status = 'Global' then 1 else 0 end) from support_cards "
            ."union all select 'skills', count(*), sum(case when release_status = 'GlobalReleased' and name_is_client = 1 then 1 else 0 end) from skills "
            ."union all select 'races', count(*), count(*) from race_catalog_slots"
        ))->keyBy('area');

        // Each area's `source` states, in one of the three arms the hub's own subtitle names, where
        // the data behind the door came from: the fetch engine's catalog tables, `config/scenarios.php`
        // (the only place scenario facts live, D-240), or the reference guide transcribed into
        // `config/reference.php`. The hub adds no new fact — it moves the provenance each view already
        // states onto the row that leads to it (frontend-fidelity Slice 3.2).
        //
        // `count` is the number the area's door leads with and `recorded` the rows the table holds; the
        // tile prints the second only where the two differ. It is null where the area's rows are
        // transcribed into config, and those tiles print the `tag` word the HFW prints instead. A count
        // that no table can corroborate would be a typed capture value.
        return Inertia::render('Database/Index', [
            'areas' => [
                ['label' => 'Trainees', 'url' => route('database.trainees'), 'source' => 'Fetched', 'count' => (int) ($live['trainees']->scoped ?? 0), 'recorded' => (int) ($live['trainees']->total ?? 0), 'tag' => null, 'description' => 'Aptitudes, costumes, base stats and growth rows.'],
                ['label' => 'Support Cards', 'url' => route('database.supports'), 'source' => 'Fetched', 'count' => (int) ($live['supports']->scoped ?? 0), 'recorded' => (int) ($live['supports']->total ?? 0), 'tag' => null, 'description' => 'Seven types, effect ladders, limit-break stages.'],
                ['label' => 'Skills', 'url' => route('database.skills'), 'source' => 'Fetched', 'count' => (int) ($live['skills']->scoped ?? 0), 'recorded' => (int) ($live['skills']->total ?? 0), 'tag' => null, 'description' => 'Engine conditions, base costs, hint ladders.'],
                ['label' => 'Races', 'url' => route('database.races'), 'source' => 'Fetched', 'count' => (int) ($live['races']->scoped ?? 0), 'recorded' => (int) ($live['races']->total ?? 0), 'tag' => null, 'description' => 'Grade, surface, distance band, fan thresholds.'],
                ['label' => 'Events', 'url' => route('database.events'), 'source' => 'Transcribed', 'count' => null, 'tag' => 'Reference', 'description' => 'Recurring live-event types and their cadence.'],
                ['label' => 'Scenarios', 'url' => route('database.scenarios'), 'source' => 'Config', 'count' => count((array) config('scenarios.scenarios', [])), 'tag' => null, 'description' => 'Caps, panels, live-on-Global dates, rulesets.'],
                ['label' => 'Shop Items', 'url' => route('database.shop-items'), 'source' => 'Transcribed', 'count' => null, 'tag' => 'Trackblazer', 'description' => 'Costs, effects and one publisher\'s ratings.'],
                ['label' => 'Sparks', 'url' => route('database.sparks'), 'source' => 'Transcribed', 'count' => null, 'tag' => 'Inheritance', 'description' => 'Blue, pink and white factor families and their grades.'],
                // The ninth area, and the one that had no door (UX audit F-14): `/database/sources`
                // is a registered screen serving the fetch and transcription record, and the hub is
                // the only index of Database screens, so leaving it out made the page URL-only.
                ['label' => 'Data sources', 'url' => route('database.sources'), 'source' => 'Config', 'count' => null, 'tag' => 'Ledger', 'description' => 'Every source, the date each was read, and what it fed.'],
            ],
            'matrix' => [
                'state' => config('scenarios.stale', false) ? 'Stale' : 'Current',
                'verified_at' => config('scenarios.verified_at'),
                'source' => config('scenarios.source'),
            ],
        ]);
    }

    /**
     * The race database: every slot of the shared `[Global]` career calendar, read straight off
     * `race_catalog_slots`.
     *
     * The column set is what the source states for a slot, and no more: `tier` already carries the
     * client's own word (`G1`, `OP`, `Maiden`, `Debut`) and `grade_code` is its numeric twin, one to
     * one across all 410 rows, so printing both would state the same fact twice. `scenario_key`, the
     * track and race ids and the fetch provenance are held off the table for the same reason: a
     * reference table earns a column by deciding something a Trainer can see.
     *
     * No cache, for the reason `SkillController` records: `catalog:version` is bumped by promotion and
     * this table is written by a fetch, not by promotion, so a cached page would keep answering after
     * a re-fetch. The table is 410 rows, so the query is the whole cost.
     *
     * The wireframe draws a filter row over this table (`database-races.html:72-77`: Search, Grade,
     * Surface, Distance, Clear) and the page's own lede promises it, so the facets are answered here
     * rather than left to the five-page scroll. Every one of them reads a column this table already
     * stores: the search matches `title`, and Grade, Surface and Distance read `tier`, `surface` and
     * `distance_band`. Nothing is derived and nothing needs a new query.
     */
    public function races(Request $request): Response
    {
        $sort = $request->query('sort');
        $sort = is_string($sort) && array_key_exists($sort, self::RACE_SORT_COLUMNS) ? $sort : null;
        $column = $sort === null ? null : self::RACE_SORT_COLUMNS[$sort];
        $direction = match ($request->query('direction')) {
            'asc' => 'asc',
            'desc' => 'desc',
            default => in_array($sort, ['distance', 'fans'], true) ? 'desc' : 'asc',
        };

        $q = $request->query('q');
        $q = is_string($q) ? trim($q) : null;
        $grade = $request->query('grade');
        $grade = is_string($grade) ? trim($grade) : null;
        $surface = $request->query('surface');
        $surface = is_string($surface) ? trim($surface) : null;
        $distance = $request->query('distance');
        $distance = is_string($distance) ? trim($distance) : null;

        $query = RaceCatalogSlot::query()
            // SQLite has no default LIKE escape character, so the clause names one and the pattern
            // escapes `%`/`_` first: unescaped, a `%` would return the whole calendar (KI-26's trap).
            ->when($q !== null && $q !== '', static fn ($query) => $query->whereRaw(
                "title LIKE ? ESCAPE '\\'",
                ['%'.addcslashes($q, '\\%_').'%'],
            ))
            ->when($grade !== null && $grade !== '', static fn ($query) => $query->where('tier', $grade))
            ->when($surface !== null && $surface !== '', static fn ($query) => $query->where('surface', $surface))
            ->when($distance !== null && $distance !== '', static fn ($query) => $query->where('distance_band', $distance));

        if ($column === 'title') {
            $query->orderByRaw('title COLLATE NOCASE '.$direction);
        } elseif ($column !== null) {
            $query->orderByRaw($column.' IS NULL')->orderBy($column, $direction);
        }

        $slots = $query
            ->orderBy('year')
            ->orderBy('turn')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(PageSize::clamp($request->query('pageSize')))
            ->withQueryString()
            ->through(static fn (RaceCatalogSlot $slot): array => [
                'id' => $slot->id,
                'title' => $slot->title,
                'year_label' => $slot->yearLabel(),
                'turn' => $slot->turn,
                'tier' => $slot->tier,
                'distance' => $slot->distance,
                'distance_band' => $slot->distance_band,
                // N6.4: the 1,400 m band disagreement is a server flag, not a frontend hardcode. The
                // corpus itself disputes this line (Game8 Sprint vs export Mile), so the presence of
                // the conflict travels with the row rather than being re-derived from a metre count
                // inside the component.
                'distance_band_conflict' => $slot->distance === 1400,
                'surface' => $slot->surface,
                'fans_needed' => $slot->fans_needed,
                'is_mandatory' => $slot->is_mandatory,
                'is_special_race' => $slot->is_special_race,
            ]);

        return Inertia::render('Database/Races', [
            'slots' => $slots,
            'totalCount' => RaceCatalogSlot::query()->count(),
            // The payout card's provenance. Its figures are transcribed, not read from a table, so the
            // card carries the same reference metadata the other transcribed views carry (F-06).
            'source' => (array) config('reference.race_payouts.source', []),
            'recheck' => (array) config('reference.race_payouts.recheck', []),
            // Echoed so a picker paints the facet the Trainer chose, which is the same contract the
            // support-card catalog states: a select that reads "Any" after a filter has been applied
            // tells the Trainer their narrowing was lost.
            'filters' => [
                'q' => $q,
                'grade' => $grade,
                'surface' => $surface,
                'distance' => $distance,
                // Echoed on the same contract as the other facets: a restored URL that reordered the
                // calendar has to paint the ordering it applied, or the Trainer reads the rows as
                // arbitrary. `null` is the calendar order, which is also what an unknown token clamps to.
                'sort' => $sort,
                'direction' => $column === null ? null : $direction,
            ],
        ]);
    }

    /**
     * The scenario database: the composition matrix in `config/scenarios.php`, printed as it is.
     *
     * The config is the single source of truth for what the UI renders per scenario (D-240, gate
     * G-33), so this screen reads it and branches on nothing but the resolved values: the `panels.*`
     * flags, the `widgets[]` keys and the `partially_documented` marker. A fifth scenario is one config
     * entry and no edit to this method or to the page.
     *
     * Every entry is labelled, on or off, rather than omitted. The Scenario Selection cards hide an off
     * panel because a scenario must not claim a system it does not compose; a reference matrix earns
     * its place by stating the matrix, so an off panel is a fact here rather than a silence.
     */
    public function scenarios(): Response
    {
        $baseCap = (int) config('scenarios.base_cap');
        $verifiedAt = (string) config('scenarios.verified_at');
        $widgetLabels = config('scenarios.widget_labels');
        $panelLabels = config('scenarios.panel_labels');

        $scenarios = [];

        foreach (config('scenarios.scenarios') as $key => $definition) {
            $caps = [];

            // `ScenarioCaps` is the single owner of per-stat ceiling arithmetic (ADR-0015,
            // `AGENTS.md` §8). This loop used to compute `base + bonus` itself, with no clamp against
            // `config('scenarios.hard_cap')`, so a bonus large enough to pass the engine ceiling would
            // have printed here as a legal cap the validator would then refuse: the matrix and the
            // form quoting two different numbers for one stat.
            $published = ScenarioCaps::caps((string) $key);

            foreach ($definition['cap_bonus'] as $stat => $bonus) {
                $bonus = (int) $bonus;

                $caps[] = [
                    'stat' => (string) $stat,
                    'bonus' => $bonus,
                    // The published cap, base plus bonus as separate labelled terms (DESIGN.md §6.22).
                    // `published` is the same figure the validators clamp against.
                    'cap' => $published[(string) $stat] ?? $baseCap + $bonus,
                ];
            }

            $widgets = [];

            foreach ($definition['widgets'] as $widget) {
                $widgets[] = ['label' => (string) ($widgetLabels[$widget] ?? $widget)];
            }

            $panels = [];

            foreach ($definition['panels'] as $panel => $enabled) {
                $panels[] = [
                    'label' => (string) ($panelLabels[$panel] ?? $panel),
                    'enabled' => (bool) $enabled,
                ];
            }

            $scenarios[(string) $key] = [
                'label' => (string) $definition['label'],
                'live_on_global' => (string) $definition['live_on_global'],
                'caps' => $caps,
                'widgets' => $widgets,
                'panels' => $panels,
                // The dated instant a source pins this scenario's numbering to (the rework or the
                // launch), never a ruleset version: no source defines a Global version, so that line
                // stays N/A and the two facts are not merged (`database-scenarios.html:112-124`).
                'ruleset' => [
                    'date' => (string) ($definition['ruleset_version']['date'] ?? ''),
                    'event' => (string) ($definition['ruleset_version']['event'] ?? ''),
                ],
                'partially_documented' => (bool) ($definition['partially_documented'] ?? false),
                'notes' => (string) $definition['notes'],
            ];
        }

        return Inertia::render('Database/Scenarios', [
            'baseCap' => $baseCap,
            'verifiedAt' => $verifiedAt,
            'scenarios' => $scenarios,
        ]);
    }

    /**
     * The Events reference view (`SCR-SYS-008`): the recurring event types from
     * `docs/UMAMUSUME_REFERENCE.md` §4.4, printed as the reference guide states them.
     *
     * **Recurring types, not live instances.** §4.1 to §4.3 are a dated snapshot of what was
     * running on one day and will rot; §4.4 is a cadence table read from the local event
     * exports, which is what a reference view can honestly print. Each row carries the doc's
     * own provenance state and the `title` the badge shows.
     */
    public function events(): Response
    {
        /** @var array<string, mixed> $events */
        $events = (array) config('reference.events', []);
        $rows = [];

        foreach ((array) ($events['rows'] ?? []) as $row) {
            $rows[] = [
                'name' => (string) $row['name'],
                'servers' => array_values((array) $row['servers']),
                'cadence' => (string) $row['cadence'],
                // S2.5: the short cadence the reference doc states beside the long observation. Nullable
                // on purpose: one row (the special event) carries a single-window observation with no
                // pattern to summarise, and inventing a word there would upgrade one observation into a
                // schedule. The frontend prints the long text when this is null.
                'cadence_summary' => isset($row['cadence_summary']) && is_string($row['cadence_summary'])
                    ? $row['cadence_summary']
                    : null,
                'mechanics' => (string) $row['mechanics'],
                'state' => self::referenceState($row['state'] ?? null),
                'source_title' => (string) $row['source_title'],
            ];
        }

        return Inertia::render('Database/Events', [
            'rows' => $rows,
            'recheck' => [
                'text' => (string) ($events['recheck']['text'] ?? ''),
                'url' => (string) ($events['recheck']['url'] ?? ''),
            ],
            'source' => [
                'section' => (string) ($events['source']['section'] ?? ''),
                'anchor' => (string) ($events['source']['anchor'] ?? ''),
            ],
            'totalCount' => count($rows),
        ]);
    }

    /**
     * The Shop Items reference view (`SCR-SYS-009`): the consumables and currencies from
     * `docs/UMAMUSUME_REFERENCE.md` §1.6.10, plus the two currency items that section describes
     * in prose.
     *
     * **The Trackblazer Pro Shop is reused, not duplicated.** Its item class already lives in
     * `config/scenarios.php` `trackblazer.shop_items` (verified 2026-09-27), so this method reads
     * that block and passes it as its own section rather than restating its nineteen rows here. The
     * reference config carries only the wider catalogue the scenario block does not.
     */
    public function shopItems(): Response
    {
        /** @var array<string, mixed> $shop */
        $shop = (array) config('reference.shop_items', []);
        $rows = [];

        foreach ((array) ($shop['rows'] ?? []) as $row) {
            $rows[] = [
                'name' => (string) $row['name'],
                'effect' => (string) $row['effect'],
                'spend_site' => (string) $row['spend_site'],
                'kind' => (string) $row['kind'],
                'state' => self::referenceState($row['state'] ?? null),
                'source_title' => (string) $row['source_title'],
                'detail' => (string) $row['detail'],
            ];
        }

        // Reused verbatim from the scenario matrix, never retyped. `rotation_turns` is the only
        // extra fact the view prints beside the block, and it comes from the same entry.
        $scenarioShopItems = [];

        foreach ((array) config('scenarios.scenarios.trackblazer.shop_items', []) as $item) {
            $scenarioShopItems[] = [
                'name' => (string) $item['name'],
                'cost' => (int) $item['cost'],
                'effect' => (string) $item['effect'],
            ];
        }

        return Inertia::render('Database/ShopItems', [
            'rows' => $rows,
            'scenarioShop' => [
                'label' => (string) config('scenarios.scenarios.trackblazer.label'),
                'rotation_turns' => (int) config('scenarios.scenarios.trackblazer.shop.rotation_turns'),
                'items' => $scenarioShopItems,
            ],
            // The publisher's own "Worth" ratings, keyed by item name, and the sentence that says whose
            // opinion they are. Both come from the scenario config (`shop_worth`, `shop_worth_source`);
            // the page prints them under a header that names the source, never as this tool's advice.
            'ratings' => (array) config('scenarios.scenarios.trackblazer.shop_worth', []),
            'ratingsSource' => (string) config('scenarios.scenarios.trackblazer.shop_worth_source', ''),
            'recheck' => [
                'text' => (string) ($shop['recheck']['text'] ?? ''),
                'url' => (string) ($shop['recheck']['url'] ?? ''),
            ],
            'source' => [
                'section' => (string) ($shop['source']['section'] ?? ''),
                'anchor' => (string) ($shop['source']['anchor'] ?? ''),
            ],
            'totalCount' => count($rows),
        ]);
    }

    /**
     * The Sparks reference view (`SCR-SYS-010`): the Spark categories from
     * `docs/UMAMUSUME_REFERENCE.md` §1.5.2 and the star-roll odds from §1.5.3.
     *
     * The `records` counts are cited, not re-derived: the Spark export exists on a developed machine
     * under `research-scratch/data/json/` but it is gitignored and no runtime path reads it. Five of
     * the six are cells in it, and the sixth is this guide's own reading of part of its 336-record
     * bucket, which the view names in copy rather than leaving to the arithmetic. The odds table is
     * small and answers a question a Trainer asks directly, so it renders on the page rather than
     * only inside a drawer.
     */
    public function sparks(): Response
    {
        /** @var array<string, mixed> $sparks */
        $sparks = (array) config('reference.sparks', []);
        $categories = [];

        foreach ((array) ($sparks['categories'] ?? []) as $row) {
            $categories[] = [
                'category' => (string) $row['category'],
                'jp_name' => (string) $row['jp_name'],
                'global_name' => (string) $row['global_name'],
                'effect' => (string) $row['effect'],
                'records' => (int) $row['records'],
                'state' => self::referenceState($row['state'] ?? null),
                'source_title' => (string) $row['source_title'],
            ];
        }

        $rollOdds = [];

        foreach ((array) ($sparks['roll_odds'] ?? []) as $row) {
            $rollOdds[] = [
                'band' => (string) $row['band'],
                'one_star' => (string) $row['one_star'],
                'two_stars' => (string) $row['two_stars'],
                'three_stars' => (string) $row['three_stars'],
            ];
        }

        return Inertia::render('Database/Sparks', [
            'categories' => $categories,
            'rollOdds' => $rollOdds,
            'rollOddsNote' => (string) ($sparks['roll_odds_note'] ?? ''),
            'recheck' => [
                'text' => (string) ($sparks['recheck']['text'] ?? ''),
                'url' => (string) ($sparks['recheck']['url'] ?? ''),
            ],
            'source' => [
                'section' => (string) ($sparks['source']['section'] ?? ''),
                'anchor' => (string) ($sparks['source']['anchor'] ?? ''),
            ],
            'totalCount' => count($categories),
        ]);
    }

    /**
     * Where every dataset this tool prints comes from, in one place.
     *
     * Every reference view used to carry its own "Transcribed from `docs/...` §x.y" line, which named
     * a file and a section number the Trainer does not hold. One page answers the same question for
     * all of them: a fetched dataset names its URL, a transcribed one names the guide and the date
     * the guide was anchored, and every row says what re-checking it costs.
     *
     * Two arms, because the two kinds of dataset age differently. A fetch's freshness is a recorded
     * timestamp (`data_sources.fetched_at`, the newest row this tool holds). A transcription's
     * freshness is the guide's own anchor date, which `config/reference.php` already carries per
     * dataset: the date the transcribing pass read the document, not a claim that the document has
     * not moved since.
     *
     * No count and no status chip. A dataset that has never been fetched says so in words rather
     * than showing a zero or an empty date, because "no fetch recorded" and "fetched long ago" are
     * different facts and only one of them is a problem.
     */
    /**
     * The per-source Supplies phrase (N6.2).
     *
     * The column used to repeat one generic pipeline sentence on every row, which told a Trainer
     * nothing about which document a dataset comes from. Each phrase below is lifted from the named
     * parser's own docblock, so the column states what the ingest actually reads rather than what a
     * screen wishes it read. A source with no entry here falls back to the generic sentence rather
     * than claiming a capability this build cannot cite.
     *
     * @return array<string, string>
     */
    private static function suppliesPhrases(): array
    {
        return [
            'gametora-characters' => 'Character catalogue',
            'gametora-character-cards' => 'Character-card',
            'gametora-character-profiles' => 'Trainee profile',
            'gametora-race-catalog' => 'race catalogue',
            'gametora-racetracks' => 'racetrack',
            'gametora-races' => 'race',
            'gametora-race-fans' => 'race-fan',
            'gametora-race-rewards' => 'race-reward',
            'gametora-skills' => 'skill catalogue',
            'gametora-support-cards' => 'Support-card',
            'gametora-support-effects' => 'Support-effect',
            'gametora-artwork' => 'artwork',
            'gametora-ura-objectives' => 'URA objective',
        ];
    }

    public function sources(): Response
    {
        // Each source's own newest recorded fetch, keyed by the config key the provenance rows are written
        // under. This used to take `max(fetched_at)` over the whole table and stamp every row with that one
        // figure, so on a database where only one source has ever been fetched every other row claimed that
        // source's date (F-04). A source with no row says so in words, which the view already renders: "no
        // fetch recorded" and "fetched long ago" are different facts.
        $fetchedAt = DB::table('data_sources')
            ->selectRaw('source_key, max(fetched_at) as fetched_at')
            ->groupBy('source_key')
            ->pluck('fetched_at', 'source_key');

        // N2.11: the second arm. Most reference tables carry their own per-row stamp
        // (`source_url` + `fetched_at`, ADR-0003 Amendment R3), and the ledger above only knows about
        // requests this tool made. A dataset whose rows were committed with their own stamp therefore
        // has a real date the ledger never recorded. One enriched row may name several documents as a
        // comma-separated list, so each URL is split out and takes the row's stamp — keeping the list
        // whole would produce a key no source could ever match.
        $inlineStamp = [];

        foreach (self::INLINE_STAMP_TABLES as $table) {
            foreach (DB::table($table)->select('source_url', 'fetched_at')->get() as $row) {
                if ($row->source_url === null || $row->fetched_at === null) {
                    continue;
                }

                foreach (explode(',', (string) $row->source_url) as $url) {
                    $url = trim($url);

                    if ($url === '') {
                        continue;
                    }

                    $at = (string) $row->fetched_at;

                    if (! isset($inlineStamp[$url]) || $at > $inlineStamp[$url]) {
                        $inlineStamp[$url] = $at;
                    }
                }
            }
        }

        // TD-08: the two directions of the source↔conflict association, derived once from the one
        // config list so the row stamps and the payload cannot disagree. Only rows the ledger actually
        // prints are kept — a filter option matching nothing is worse than no option — and the
        // `evidence_available: false` rows are what make an unnamed source `unknown` rather than `none`.
        $conflictRows = (array) config('reference.conflicts.rows', []);
        $evidenceUnavailable = false;
        $openConflicts = [];
        $resolvedConflicts = [];
        $openBySource = [];
        $resolvedBySource = [];

        foreach ($conflictRows as $conflict) {
            if (! is_array($conflict) || ! isset($conflict['id'])) {
                continue;
            }

            if (($conflict['evidence_available'] ?? true) === false) {
                $evidenceUnavailable = true;
            }

            $id = (string) $conflict['id'];
            $named = (array) ($conflict['affected_sources'] ?? []);
            $state = (string) ($conflict['state'] ?? 'open');

            if ($state === 'resolved') {
                $resolvedConflicts[] = $conflict;
            } else {
                $openConflicts[] = $conflict;
            }

            foreach ($named as $name) {
                if ($state === 'resolved') {
                    $resolvedBySource[(string) $name][] = $id;
                } else {
                    $openBySource[(string) $name][] = $id;
                }
            }
        }

        $supplies = self::suppliesPhrases();
        $generic = 'Fetched by the ingest pipeline from the publisher\'s own document. The date '
            .'beside this row is this source\'s own newest recorded fetch, not the newest fetch '
            .'across every dataset.';

        $fetched = [];

        foreach ((array) config('uma.sources', []) as $key => $source) {
            if (! is_array($source) || ! isset($source['url'])) {
                continue;
            }

            $url = (string) $source['url'];
            $ledgerAt = $fetchedAt[$key] ?? null;
            $inlineAt = $inlineStamp[$url] ?? null;

            // The newer arm wins and names itself, so a Trainer can tell a request this tool made
            // from a stamp the committed rows carried. Neither arm is an absence borrowed from the other.
            if ($ledgerAt !== null && ($inlineAt === null || (string) $ledgerAt >= $inlineAt)) {
                $at = (string) $ledgerAt;
                $channel = 'data_sources';
            } elseif ($inlineAt !== null) {
                $at = $inlineAt;
                $channel = 'inline';
            } else {
                $at = null;
                $channel = null;
            }

            $fetched[] = self::withInvolvement([
                'name' => (string) $key,
                'url' => $url,
                'fetched_at' => $at,
                'channel' => $channel,
                // N6.5 (ND-14(b), owner-approved 2026-11-22): the corpus's own weight class for the
                // source, read from the config entry rather than typed here. A dataset without a
                // recorded tier would be a fabricated classification, so the key is trusted to
                // config and nothing else.
                'tier' => isset($source['tier']) && is_string($source['tier']) ? $source['tier'] : null,
                'note' => isset($supplies[(string) $key])
                    ? $supplies[(string) $key].' — '.$generic
                    : $generic,
            ], $openBySource, $resolvedBySource, $evidenceUnavailable);
        }

        $transcribed = [];

        foreach ((array) config('reference', []) as $key => $dataset) {
            if (! is_array($dataset) || ! isset($dataset['source']['anchor'])) {
                continue;
            }

            // The config's `section` reads "§4.4 Recurring event types": the words after the number
            // are the dataset's own name, and the number is the part a Trainer cannot use.
            $section = (string) ($dataset['source']['section'] ?? '');
            $name = preg_replace('/^§[\d.]+\s*/u', '', $section) ?? $section;

            $transcribed[] = self::withInvolvement([
                'name' => $name === '' ? (string) $key : $name,
                'url' => (string) ($dataset['recheck']['url'] ?? ''),
                'anchored_at' => (string) $dataset['source']['anchor'],
                // N6.5 (ND-14(b), owner-approved 2026-11-22): the dataset's own tier, recorded in
                // the config beside its section and anchor from the corpus's source list. The
                // conflict log carries none on purpose: its rows aggregate claims from sources of
                // different weights, so one tier on the record would misattribute them.
                'tier' => isset($dataset['source']['tier']) && is_string($dataset['source']['tier'])
                    ? $dataset['source']['tier']
                    : null,
                'note' => (string) ($dataset['recheck']['text'] ?? ''),
            ], $openBySource, $resolvedBySource, $evidenceUnavailable);
        }

        // A name no arm printed is dropped from both directions, so no conflict can name a row the
        // screen does not hold. The row set is the union of both arms, keyed by the name each prints.
        $held = array_fill_keys(
            array_merge(array_column($fetched, 'name'), array_column($transcribed, 'name')),
            true,
        );

        $openConflicts = self::narrowSources($openConflicts, $held);
        $resolvedConflicts = self::narrowSources($resolvedConflicts, $held);

        return Inertia::render('Database/Sources', [
            'docName' => 'the reference guide',
            'fetched' => collect($fetched),
            'transcribed' => collect($transcribed),
            // N6.3: conflicts as server payload records, not a Vue const. The count tile derives
            // from this list; all four stay open by ruling (no resolution is recorded here).
            'conflicts' => $openConflicts,
            // TD-08: the settled rows, shipped as their own history. The open panel's semantics are
            // unchanged — it still carries the open record only — but "we settled this" must not be
            // indistinguishable from "we never looked".
            'conflict_history' => $resolvedConflicts,
            // The gap is disclosed on its own, independently of any known involvement: a source can be
            // named by an open conflict as fact while two other records could not establish their
            // sources at all. Never silently treated as exhaustive.
            'conflict_evidence_unavailable' => $evidenceUnavailable,
            // The wireframe states the matrix's verification date beside this ledger
            // (`database-sources.html:74`), and it is the same recorded value the Scenarios page
            // prints, read from the one place it is written so two screens cannot disagree.
            'matrixVerifiedAt' => (string) config('scenarios.verified_at'),
        ]);
    }

    /**
     * Drop the config-named sources no arm actually holds.
     *
     * @param  array<int, array<string, mixed>>  $conflicts
     * @param  array<string, true>  $held
     * @return array<int, array<string, mixed>>
     */
    private static function narrowSources(array $conflicts, array $held): array
    {
        foreach ($conflicts as $i => $conflict) {
            $conflicts[$i]['affected_sources'] = array_values(array_filter(
                (array) ($conflict['affected_sources'] ?? []),
                fn (mixed $name): bool => isset($held[(string) $name]),
            ));
        }

        return $conflicts;
    }

    /**
     * Stamp one ledger row with its open and historical conflict involvement (TD-08).
     *
     * The five states exist because a two-state boolean cannot make the distinction that matters: a
     * source named only by settled rows is NOT in a live dispute but is also not untouched, and a
     * source the records could not place is not a clean one. `unknown` therefore outranks `none`.
     *
     * @param  array<string, mixed>  $row
     * @param  array<string, list<string>>  $openBySource
     * @param  array<string, list<string>>  $resolvedBySource
     * @return array<string, mixed>
     */
    private static function withInvolvement(
        array $row,
        array $openBySource,
        array $resolvedBySource,
        bool $evidenceUnavailable,
    ): array {
        $name = (string) $row['name'];
        $open = $openBySource[$name] ?? [];
        $resolved = $resolvedBySource[$name] ?? [];

        $state = match (true) {
            $open !== [] && $resolved !== [] => 'mixed',
            $open !== [] => 'open',
            $resolved !== [] => 'resolved',
            $evidenceUnavailable => 'unknown',
            default => 'none',
        };

        $note = match ($state) {
            'mixed' => 'Named by an open conflict ('.count($open).') and by '.count($resolved)
                .' resolved conflict(s) as history.',
            'open' => 'Named by an open conflict.',
            'resolved' => 'Named only by resolved conflicts, as history.',
            'unknown' => 'No conflict record establishes this source\'s involvement: missing evidence, '
                .'not a clean bill of health.',
            default => 'No conflict record names this source.',
        };

        return [
            ...$row,
            'conflicts' => $open,
            'open_conflict_count' => count($open),
            'in_open_conflict' => $open !== [],
            'history_involvement' => $state,
            'resolved_conflicts' => $resolved,
            'history_involvement_note' => $note,
        ];
    }

    /**
     * One race slot's own page: the full catalogue row plus its decoded facts.
     */
    public function raceDetail(int $id): Response
    {
        $slot = RaceCatalogSlot::findOrFail($id);

        return Inertia::render('Database/RaceDetail', [
            'slot' => $slot,
            'facts' => RaceFacts::forSlot($slot),
        ]);
    }

    /**
     * The row's source-strength state, or the unknown fallback.
     *
     * The badge indexes its map with the state and has no fallback, so an out-of-vocabulary config
     * string was a render-time failure on the whole reference view; the four states the config's own
     * comment defines are the only values this passes, and `calculated` stays reserved there because
     * no reference row is derived by this app (DESIGN.md §5's ruling on the two axes).
     *
     * @param  mixed  $state  the config row's own `state` string
     */
    private static function referenceState(mixed $state): string
    {
        return in_array($state, ['confirmed', 'calculated', 'estimated', 'unknown'], true)
            ? (string) $state
            : 'unknown';
    }
}
