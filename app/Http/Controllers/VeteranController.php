<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ListVeterans;
use App\Actions\ShowVeteran;
use App\Http\Requests\VeteranCompareRequest;
use App\Http\Requests\VeteranSearchRequest;
use App\Models\Umamusume;
use App\Models\Veteran;
use App\Services\Legacy\VeteranRow;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The Veteran library: the Trainer's own filed careers, browsed and opened (`SCREEN-021`, PRD FR-G-2,
 * `frontend-development-plan.md` §8's D16).
 *
 * **The read half of D16, with its write half beside it.** Save Veteran (`SCREEN-020`) is
 * `Career\SaveVeteranController`, and it is the first caller of `RecordVeteran`, so a library that looks
 * empty is a library no career has been filed into yet. `filingNotice` says that in those words and names the
 * door. What this block used to record — that no route could file a career and the plan gated the screen
 * behind an unbuilt D15 — was true when it was written and is the kind of claim that goes false silently, so
 * it is corrected here rather than left standing: `tests/browser/veterans.spec.ts` asserts the copy, and the
 * copy and the spec travel together.
 *
 * **It computes nothing**, the same rule `LegacyController` states at length (FR-G-4, `ADR-0020` §3). The
 * row shape is `VeteranRow`, which the Legacy Lab's browse list reads through the identical call, so the
 * two surfaces cannot print different Veterans. `SCREEN-021`'s favorite, archive and delete are absent
 * because no column holds them; its Spark-quality, aptitude, skill-coverage, race-history and
 * overall-usefulness sorts are absent because no sourced table prices a Spark or scores a build. Both
 * screen's absences are rendered as named absences with their reason, not as empty controls a Trainer
 * would have to guess about.
 *
 * The detail screen reads through `ShowVeteran`, which is the one place this record's relations are
 * declared eager, so opening a Veteran is two queries and not one per skill and race row.
 */
final class VeteranController extends Controller
{
    /**
     * `GET /veterans`, the library list.
     */
    public function index(VeteranSearchRequest $request, ListVeterans $veterans): Response
    {
        $filters = $request->filters();
        $order = $request->order();

        $rows = $veterans
            ->handle($filters, $request->query('pageSize'), $order)
            ->withQueryString()
            ->through(fn (Veteran $veteran): array => VeteranRow::from($veteran));

        return Inertia::render('Veterans/Index', [
            'veterans' => $rows,
            'order' => $order,
            'filters' => [
                'trainee' => $filters['trainee'],
                'scenario' => $filters['scenario'],
                'tag' => $filters['tags'][0] ?? null,
            ],
            // The filter form's pick lists. The trainee roster is a Trainer's own scale (tens, not
            // thousands) and this is a personal library, so it is unpaginated and uncached, the same
            // reading `LegacyController::index()` takes for the browse list's facets.
            'trainees' => Umamusume::query()
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all(),
            'scenarios' => array_map(
                static fn (array $definition): string => $definition['label'],
                config('scenarios.scenarios'),
            ),
            'totalCount' => Veteran::query()->count(),
            'notice' => LegacyController::RECORD_ONLY_NOTICE,
            // True as of D16's write half: Save Veteran is a screen now, so the empty library is a library
            // nothing has been filed into yet, and the door to filing one is named rather than the absence.
            'filingNotice' => 'A Veteran is a completed career filed into the library. Nothing is filed here: a career is saved from its own Result screen, so open one that finished and choose Save Veteran.',
            'file_veteran_url' => route('runs.index'),
            // `SCREEN-021`'s "Find Parents for This Build". A search, not an optimizer: it carries the
            // library's own tag filter onto the Legacy Lab's browse list, because the tags are the distance,
            // surface and style facets a parent would need to match, and it applies no score to the result.
            // The trainee and scenario filters are deliberately not carried: one parent search does not
            // exclude a trainee by name, and the Lab's own scenario facet is where that belongs.
            'find_parents_url' => route('legacy.index', $filters['tags'] === [] ? [] : ['tags' => $filters['tags']]),
            'compare_url' => route('veterans.compare'),
            'absences' => self::absences(),
            'searchAction' => route('veterans.index'),
        ]);
    }

    /**
     * `GET /veterans/{veteran}`, one filed career and the ancestry it was built from.
     *
     * The turn-by-turn record stays on the run and this screen links to it rather than restating it: a
     * Veteran is a pointer plus the Trainer's own metadata (`Veteran`'s docblock), and a second reading of
     * the same turns would be a second place for the two to disagree.
     */
    public function show(Veteran $veteran, ShowVeteran $show): Response
    {
        $veteran = $show->handle($veteran);
        $run = $veteran->trainingRun;
        $strip = $run->stripValues();

        return Inertia::render('Veterans/Show', [
            'veteran' => VeteranRow::from($veteran),
            'career' => [
                'turns' => $strip['turn'],
                'energy' => $strip['energy'],
                'fans' => $strip['fans'],
            ],
            'counts' => [
                'skills' => $run->skills->count(),
                'races' => $run->raceEntries->count(),
            ],
            // The two parents, as the run records them. The six-node graph is not re-rendered here:
            // `components/legacy/AncestryNode.vue` is an editable node (pick control, options, field
            // errors) and mapping it read-only would be a second copy of `Legacy/Builder.vue`'s shape.
            // `ponytail:` the graph lives at `legacy.builder`, which this row links to when a read-back
            // exists; promote it to a shared read-only renderer when a second screen needs the nodes.
            'parents' => [
                'a' => $run->inheritanceParentA?->name,
                'b' => $run->inheritanceParentB?->name,
            ],
            'runUrl' => route('runs.show', $run),
            'traineeUrl' => route('catalog.show', $run->umamusume->slug),
            'notice' => LegacyController::RECORD_ONLY_NOTICE,
            'absences' => self::absences(),
        ]);
    }

    /**
     * `GET /veterans/compare` — up to four filed careers, one property per row (`SCREEN-022`,
     * `design-2.0` §46).
     *
     * **Why this exists beside the Legacy Lab's compare.** That surface refuses a run with no Legacy
     * read-back, on the reasoning that a column of absences looks compared and is not. The comparison the
     * library offers is between *careers*, and the career a Trainer most wants beside another is usually the
     * one they just filed and have built no Legacy on, so refusing it would delete the subject of the screen.
     * Different eligibility, so a different selector (`VeteranCompareRequest`), while the row itself is still
     * `VeteranRow` — one shape, so the list, the detail screen, both compare surfaces and the Legacy Lab
     * picker cannot print four different Veterans.
     *
     * **Every cell is a stored column, and the three rows the brief asks for that are not stored are named,
     * not scored.** `design-2.0` §46 and `SCREEN-022`'s correction row want inheritance usefulness, a
     * compatibility calculation and a factor comparison. `ADR-0020` §3 keeps those out of the record screens
     * and no table prices them, so they arrive in `absences` with their reason. `FR-G-4` is why nothing here
     * orders the columns by anything it derived.
     *
     * The eager load is the request's own (`veteransInOrder()`), so four careers cost three queries rather
     * than one per row.
     */
    public function compare(VeteranCompareRequest $request): Response
    {
        $veterans = $request->veteransInOrder();

        $columns = array_map(static function (Veteran $veteran): array {
            $run = $veteran->trainingRun;
            $latest = $run->turnEntries->sortByDesc('turn')->first();
            $strip = $run->stripValues();

            return array_merge(VeteranRow::from($veteran), [
                'career' => [
                    'turns' => $strip['turn'],
                    'energy' => $strip['energy'],
                    'fans' => $strip['fans'],
                ],
                // The last logged turn's totals, never a sum of deltas (ADR-0003).
                'stats' => [
                    'Speed' => $latest?->speed,
                    'Stamina' => $latest?->stamina,
                    'Power' => $latest?->power,
                    'Guts' => $latest?->guts,
                    'Wit' => $latest?->wit,
                ],
                'counts' => [
                    'skills' => $run->skills->count(),
                    'races' => $run->raceEntries->count(),
                ],
                'aptitudes' => $run->umamusume->aptitudeAxes(),
            ]);
        }, $veterans);

        // A Trainer picks from the library, not from every run: an unfiled career has no row here to name.
        $comparable = Veteran::query()
            ->with('trainingRun.umamusume')
            ->get()
            ->map(static fn (Veteran $veteran): array => [
                'id' => $veteran->id,
                'label' => $veteran->trainingRun->umamusume->name,
            ])
            ->sortBy('label')
            ->values()
            ->all();

        return Inertia::render('Veterans/Compare', [
            'columns' => $columns,
            'selected' => $request->veteranIds(),
            'max' => VeteranCompareRequest::MAX_VETERANS,
            'comparable' => $comparable,
            // The axis list travels once, beside the cells that read it. Deriving the rows from the first
            // column would make a career with no published letters decide the shape for every career, and
            // the ten axes are `Umamusume`'s own columns, not a property of whichever row came first.
            'aptitude_axes' => Umamusume::APTITUDE_AXES,
            'empty' => $columns === [] ? [
                'message' => 'Nothing is selected, so there is nothing to line up. Choose up to '
                    .VeteranCompareRequest::MAX_VETERANS.' careers below, or open the library and use a row\'s Compare link.',
                'library_url' => route('veterans.index'),
            ] : null,
            'notice' => LegacyController::RECORD_ONLY_NOTICE,
            'absences' => self::compareAbsences(),
        ]);
    }

    /**
     * What `SCREEN-022` asks for that this build cannot give, each with the reason on the screen.
     *
     * @return list<array{label: string, reason: string}>
     */
    private static function compareAbsences(): array
    {
        return [
            [
                'label' => 'Inheritance usefulness and a compatibility calculation',
                'reason' => 'Held computation under `ADR-0020` §3: the library stores and compares what was entered and derives no outcome, and no table in this repository prices a parent combination.',
            ],
            [
                'label' => 'A factor comparison',
                'reason' => 'The factor inventory is the recorded Spark set. Two careers can be shown side by side and read, and no sourced table grades one against the other.',
            ],
            [
                'label' => 'Scenario fit',
                'reason' => 'A career records the scenario it ran in, which is a stored fact and is printed. Whether it suits another scenario is a judgement no column holds.',
            ],
        ];
    }

    /**
     * What `SCREEN-021` asks for that this build cannot give, each with the reason on the screen.
     *
     * @return list<array{label: string, reason: string}>
     */
    public static function absences(): array
    {
        return [
            [
                'label' => 'Favorite, archive, delete',
                'reason' => 'No column holds any of the three on a library row, and adding one is a stored-shape change the schema owner decides.',
            ],
            [
                'label' => 'Sort by Spark quality, aptitude, skill coverage, race history or overall usefulness',
                'reason' => 'No sourced table in this repository prices a Spark or scores a build, so the library cannot order by a figure it does not hold.',
            ],
            [
                'label' => 'The Factors view',
                'reason' => 'The factor inventory is the Spark set, which is the same unsourced table the sort above names.',
            ],
            // The fourth absence this list carried — comparison and "find parents for this build" — is built
            // as of this slice's write half: `veterans.compare` lines careers up and `find_parents_url` runs
            // the Legacy Lab's search with the library's own tags. Neither is a score.
        ];
    }
}
