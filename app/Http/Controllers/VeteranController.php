<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ListVeterans;
use App\Actions\ShowVeteran;
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
 * **This is the read half of D16, and the split is a fact about the data, not a convenience.** The write
 * half, Save Veteran (`SCREEN-020`), is the thing that puts a row in this table, and `RecordVeteran` has
 * no route to it yet: no screen in the build files a career. So a library that looks empty is a library
 * nothing has been filed into, and both screens say that plainly rather than leaving an unexplained blank
 * list. The plan gates Save Veteran behind D15 (Career Result), which is unbuilt.
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
            'filingNotice' => 'A Veteran is a completed career filed into the library. Nothing in this build files one yet: the save screen arrives with the other half of slice D16, so an empty library means no career has been filed, not that none exists.',
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
            [
                'label' => 'Comparison and "Find parents for this build"',
                'reason' => 'Comparison of two records lives in the Legacy Lab. Choosing a parent by a computed score is the recommendation `ADR-0020` §3 keeps out of the record screens.',
            ],
        ];
    }
}
