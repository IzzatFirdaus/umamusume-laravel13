<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\RaceCatalogSlot;
use App\Services\PageSize;
use Illuminate\Http\Request;
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
        return Inertia::render('Database/Index', [
            'areas' => [
                ['label' => 'Trainees', 'url' => route('database.trainees')],
                ['label' => 'Support Cards', 'url' => route('database.supports')],
                ['label' => 'Skills', 'url' => route('database.skills')],
                ['label' => 'Races', 'url' => route('database.races')],
                ['label' => 'Events', 'url' => route('database.events')],
                ['label' => 'Scenarios', 'url' => route('database.scenarios')],
                ['label' => 'Shop Items', 'url' => route('database.shop-items')],
                ['label' => 'Sparks', 'url' => route('database.sparks')],
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
                'surface' => $slot->surface,
                'fans_needed' => $slot->fans_needed,
                'is_mandatory' => $slot->is_mandatory,
                'is_maiden_gated' => $slot->is_maiden_gated,
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
                'state' => (string) $row['state'],
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
                'doc' => (string) config('reference.source_doc'),
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
                'state' => (string) $row['state'],
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
            'recheck' => [
                'text' => (string) ($shop['recheck']['text'] ?? ''),
                'url' => (string) ($shop['recheck']['url'] ?? ''),
            ],
            'source' => [
                'section' => (string) ($shop['source']['section'] ?? ''),
                'anchor' => (string) ($shop['source']['anchor'] ?? ''),
                'doc' => (string) config('reference.source_doc'),
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
                'state' => (string) $row['state'],
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
                'doc' => (string) config('reference.source_doc'),
            ],
            'totalCount' => count($categories),
        ]);
    }
}
