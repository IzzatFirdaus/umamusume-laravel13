<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\RaceCatalogSlot;
use App\Models\Umamusume;
use App\Services\PageSize;
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
        // One grouped query for the four live counts (slice 9.1). Four separate `count()` calls are
        // four round trips for four integers, and the hub is the screen every other area is reached
        // from, so it is the one page where that shape is worth fixing. `UNION ALL` keeps the areas as
        // rows of one result set; the alternative — one query per area — is what this replaces.
        $live = collect(DB::select(
            "select 'trainees' as area, count(*) as total from umamusume where external_ref is not null "
            ."union all select 'supports', count(*) from support_cards "
            ."union all select 'skills', count(*) from skills "
            ."union all select 'races', count(*) from race_catalog_slots"
        ))->pluck('total', 'area');

        // Each area's `source` states, in one of the three arms the hub's own subtitle names, where
        // the data behind the door came from: the fetch engine's catalog tables, `config/scenarios.php`
        // (the only place scenario facts live, D-240), or the reference guide transcribed into
        // `config/reference.php`. The hub adds no new fact — it moves the provenance each view already
        // states onto the row that leads to it (frontend-fidelity Slice 3.2).
        //
        // `count` is the live row count for the four fetched areas and the scenario matrix; it is null
        // where the area's rows are transcribed into config, and those tiles print the `tag` word the
        // HFW prints instead. A count that no table can corroborate would be a typed capture value.
        return Inertia::render('Database/Index', [
            'areas' => [
                ['label' => 'Trainees', 'url' => route('database.trainees'), 'source' => 'Fetched', 'count' => (int) ($live['trainees'] ?? 0), 'tag' => null, 'description' => 'Aptitudes, costumes, base stats and growth rows.'],
                ['label' => 'Support Cards', 'url' => route('database.supports'), 'source' => 'Fetched', 'count' => (int) ($live['supports'] ?? 0), 'tag' => null, 'description' => 'Seven types, effect ladders, limit-break stages.'],
                ['label' => 'Skills', 'url' => route('database.skills'), 'source' => 'Fetched', 'count' => (int) ($live['skills'] ?? 0), 'tag' => null, 'description' => 'Engine conditions, base costs, hint ladders.'],
                ['label' => 'Races', 'url' => route('database.races'), 'source' => 'Fetched', 'count' => (int) ($live['races'] ?? 0), 'tag' => null, 'description' => 'Grade, surface, distance band, fan thresholds.'],
                ['label' => 'Events', 'url' => route('database.events'), 'source' => 'Transcribed', 'count' => null, 'tag' => 'Reference', 'description' => 'Support-card and trainee event chains.'],
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
     * No facet either. The table is five pages and the columns already carry the year, so a filter
     * here would be a second surface over the same rows without a third use for it.
     */
    public function races(Request $request): Response
    {
        $slots = RaceCatalogSlot::query()
            ->orderBy('year')
            ->orderBy('turn')
            ->orderBy('sort_order')
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
                'surface' => $slot->surface,
                'fans_needed' => $slot->fans_needed,
                'is_mandatory' => $slot->is_mandatory,
                'is_special_race' => $slot->is_special_race,
            ]);

        return Inertia::render('Database/Races', [
            'slots' => $slots,
            'totalCount' => RaceCatalogSlot::query()->count(),
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

            foreach ($definition['cap_bonus'] as $stat => $bonus) {
                $bonus = (int) $bonus;

                $caps[] = [
                    'stat' => (string) $stat,
                    'bonus' => $bonus,
                    // The published cap, base plus bonus as separate labelled terms (DESIGN.md §6.22).
                    'cap' => $baseCap + $bonus,
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
    public function sources(): Response
    {
        $lastFetched = DB::table('data_sources')->max('fetched_at');

        $fetched = [];

        foreach ((array) config('uma.sources', []) as $key => $source) {
            if (! is_array($source) || ! isset($source['url'])) {
                continue;
            }

            $fetched[] = [
                'name' => (string) $key,
                'url' => (string) $source['url'],
                'fetched_at' => $lastFetched === null ? null : (string) $lastFetched,
                'note' => 'Fetched by the ingest pipeline from the publisher\'s own document. The newest '
                    .'recorded fetch across every dataset is the date beside this row.',
            ];
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

            $transcribed[] = [
                'name' => $name === '' ? (string) $key : $name,
                'url' => (string) ($dataset['recheck']['url'] ?? ''),
                'anchored_at' => (string) $dataset['source']['anchor'],
                'note' => (string) ($dataset['recheck']['text'] ?? ''),
            ];
        }

        return Inertia::render('Database/Sources', [
            'docName' => 'the reference guide',
            'fetched' => $fetched,
            'transcribed' => $transcribed,
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
