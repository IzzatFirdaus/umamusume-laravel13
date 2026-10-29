<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RunStatus;
use App\Http\Requests\LegacyCompareRequest;
use App\Http\Requests\StoreLegacySelectionRequest;
use App\Models\Legacy\LegacySelectionPayload;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use App\Models\Veteran;
use App\Services\Legacy\AncestryGraph;
use App\Services\Legacy\VeteranRow;
use App\Services\ScenarioCaps;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The Legacy Lab, `SCREEN-006` in the 2.0 design target (PRD FR-G, `ADR-0020` §3, under `ADR-0010`).
 *
 * Three surfaces, one rule: **this screen records and compares what the Trainer entered, and it
 * computes nothing** (`FR-G-4`). Every figure it prints is stored, and every figure it does not hold
 * renders `N/A` with a `title` naming the absence. Concretely, and each of these is a refusal rather
 * than an omission:
 *
 * - **No inherited stat, aptitude or skill value.** The corpus prices Blue Sparks at +5 / +12 / +21
 *   (`REFERENCE` §1.5.2) and grades Affinity at △ / ○ / ◎ (§1.5.4); neither is turned into a number
 *   here. A stored payload is a record of what the client showed, in the same category as
 *   `race_entries.circles` (`ADR-0010`).
 * - **No star-roll probability.** The odds the brief wants as `~10% ★★★` exist only as a wiki pair
 *   flagged ⚠️ STALE in §1.5.3, and nothing in this tree holds them as data. The plan's own rule for a
 *   silent corpus is "renders absence, never a number" (§3), so a Spark's chance is `N/A` with the
 *   reason in its `title`. This is the one place the slice's acceptance could have been read as
 *   "show Estimated" and the repository overrode it: there is nothing here to label.
 * - **No optimization.** `docs/research-scratch/PLANS-AND-BRIEFS.md` section `## screen-spec-2.0.md`'s own governance note on SCREEN-006 holds the
 *   "up to three recommended combinations" and "expected inheritance" clauses, and `ADR-0020` §3
 *   repeats that "Optimize Parents" and any auto-proposed combination are computation. The Compare
 *   screen aligns rows; it never ranks them and never proposes.
 *
 * **The six-node graph is a display of the payload, not a wider record.** `REFERENCE` §1.5.4: two
 * parents, each bringing two ancestors of her own, so the diagram holds six. That is
 * `LegacySelectionPayload::legacies[2].ancestors[2]` exactly. This slice does not add the trainee as a
 * seventh stored node, a grandparent as a stored row, or a node table: the payload is the record
 * (`ADR-0010` Decision), and the graph is how a person reads it. The Trainee row is the run's own
 * `umamusume`, which is what it would be in the client.
 *
 * **Filter vocabulary.** The veteran library's facet set (`trainee`, `scenario`, `tags`) lives on
 * `/veterans`; this surface lists saved pairs and carries no filter form (owner ruling 2026-10-28).
 * The rating facet stays refused by C3 on the record that no source records a rating.
 */
class LegacyController extends Controller
{
    /**
     * The banner copy every Legacy surface carries. Kept as one constant because the banner is the
     * screen's safety contract, and three hand-copied sentences about a ban are three chances to
     * reword one of them until it promises less than the ban requires.
     */
    public const RECORD_ONLY_NOTICE = 'Record only. This screen stores and compares what you enter. It does not compute inheritance.';

    /**
     * How many saved pairs the browse surface projects. A pair is a run with a Legacy Select, so this
     * is a window on the most recent ones rather than a paginated collection: the Veteran library below
     * it is the paginated list, and a second pager for a projection of the same rows would be two
     * controls over one set.
     */
    private const PAIR_LIMIT = 12;

    /**
     * `GET /legacy` — the saved legacies, one row per run that carries a Legacy Select.
     *
     * The wireframe's page (`legacy.html:54-113`): the pair table on the left, what a legacy is worth
     * and how many parents the library holds on the right. The veteran library's own list, its
     * filters and its pager live on `/veterans`; this surface no longer repeats them.
     */
    public function index(): Response
    {
        // Owner ruling 2026-10-28: this surface is the wireframe's saved-legacies page
        // (`legacy.html:54-113`). The veteran library moved to `/veterans` and the builder stays at
        // `/legacy/{run}`, so the page no longer carries a second, filtered copy of the library here.
        return Inertia::render('Legacy/Index', [
            // A Legacy Select is ONE object — two parents plus their four ancestor slots — stored as
            // one `training_runs.legacy_selection` column (`ADR-0010`); "a saved pair" is a run that
            // carries a selection, not two Veteran rows. Read-only: no table, no write, no derived score.
            'pairs' => $this->savedPairs(),
            'totalCount' => Veteran::query()->count(),
            'notice' => self::RECORD_ONLY_NOTICE,
        ]);
    }

    /**
     * The runs that carry a Legacy Select, projected as saved pairs (slice 7.1).
     *
     * A pair is ONE object — two parents and the sparks they pass on — and it is stored as one
     * `training_runs.legacy_selection` column (`ADR-0010`), so "a saved pair" is a run that carries a
     * selection, not two Veteran rows. The projection is additive and read-only: no table, no write and
     * no derived score. The three values the HFW's table asks for are read straight off the payload:
     * the blue grades are the stat sparks' star counts, the pink range is the width of the aptitude
     * sparks' stars, and the unique-skill hint family is the green plus white sparks, which are hint
     * levels rather than flat stat boosts (DM-3).
     *
     * `parent_a`/`parent_b` are the two names the wireframe's table prints as its own columns
     * (`legacy.html:80-101`); each is null when that slot holds no parent, and the page prints the
     * absence rather than a pair label hiding which half is missing.
     *
     * @return list<array{run_id: int, trainee: string, scenario_label: string, pair_label: string,
     *     parent_a: string|null, parent_b: string|null, blue_grades: string, pink_range: string,
     *     hint_family: string, builder_url: string, compare_url: string}>
     */
    private function savedPairs(): array
    {
        return TrainingRun::query()
            ->whereNotNull('legacy_selection')
            ->with(['umamusume:id,name', 'inheritanceParentA:id,name', 'inheritanceParentB:id,name'])
            ->latest('id')
            ->limit(self::PAIR_LIMIT)
            ->get()
            ->map(static function (TrainingRun $run): array {
                $sparks = [];
                $selection = $run->legacySelection();

                if ($selection !== null) {
                    foreach ($selection->legacies as $legacy) {
                        foreach ($legacy['sparks'] as $spark) {
                            $sparks[] = $spark;
                        }
                    }
                }

                $ofKind = static fn (string ...$kinds): array => array_values(array_filter(
                    $sparks,
                    static fn (array $spark): bool => in_array($spark['kind'], $kinds, true),
                ));

                $stars = static function (array $list): array {
                    $values = [];
                    foreach ($list as $spark) {
                        if (is_int($spark['stars'])) {
                            $values[] = $spark['stars'];
                        }
                    }

                    return $values;
                };

                $blue = $ofKind('blue');
                $pink = $ofKind('pink');
                $hints = $ofKind('green', 'white');

                $blueGrades = [];
                foreach ($blue as $spark) {
                    $target = is_string($spark['target']) ? $spark['target'] : null;
                    $star = is_int($spark['stars']) ? $spark['stars'] : null;
                    $blueGrades[] = trim(($target ?? 'Target not recorded').($star === null ? '' : ' ★'.$star));
                }

                $pinkStars = $stars($pink);
                $pinkRange = match (true) {
                    $pink === [] => 'No pink Spark recorded',
                    $pinkStars === [] => 'Star counts not recorded',
                    min($pinkStars) === max($pinkStars) => '★'.min($pinkStars),
                    default => '★'.min($pinkStars).' to ★'.max($pinkStars),
                };

                $hintCounts = [];
                foreach ($hints as $spark) {
                    $kind = $spark['kind'];
                    $hintCounts[$kind] = ($hintCounts[$kind] ?? 0) + 1;
                }

                $names = array_values(array_filter([
                    $run->inheritanceParentA?->name,
                    $run->inheritanceParentB?->name,
                ], static fn (?string $name): bool => $name !== null && $name !== ''));

                return [
                    'run_id' => $run->id,
                    'trainee' => $run->umamusume->name,
                    'scenario_label' => $run->hasScenario()
                        ? (string) (config('scenarios.scenarios.'.$run->scenarioKey().'.label') ?? $run->scenarioKey())
                        : 'No scenario set',
                    'pair_label' => $names === [] ? 'Parent names not recorded' : implode(' × ', $names),
                    'parent_a' => $run->inheritanceParentA?->name,
                    'parent_b' => $run->inheritanceParentB?->name,
                    'blue_grades' => $blueGrades === [] ? 'No blue Spark recorded' : implode(', ', $blueGrades),
                    'pink_range' => $pinkRange,
                    'hint_family' => $hintCounts === []
                        ? 'No hint Spark recorded'
                        : implode(', ', array_map(
                            static fn (string $kind, int $count): string => ucfirst($kind).' '.$count,
                            array_keys($hintCounts),
                            array_values($hintCounts),
                        )),
                    'builder_url' => route('legacy.builder', $run),
                    'compare_url' => route('legacy.compare', ['runs' => [$run->id]]),
                ];
            })
            ->all();
    }

    /**
     * `GET /legacy/{run}` — the six-node builder for one run.
     *
     * Run-scoped rather than global, and that is a routing decision with a reason: `legacy_selection`
     * is a column on `training_runs` (`ADR-0010`), so "the ancestry for this trainee" is a property of
     * one run and there is nowhere else to keep it. A trainer-scoped graph would need a new table, and
     * a new table is an ADR.
     *
     * The run must be `Active`. A `Completed` run is the record of a career that already happened, and
     * `ADR-0010` fixes the Legacy Select result for the life of the run; a `Retired` one is abandoned.
     * Refusing both here means the builder is only ever open on a run a Trainer is still planning, and
     * it is the same rule `RecordVeteran` applies to the other end of the lifecycle.
     */
    public function builder(TrainingRun $run): Response
    {
        abort_unless($run->status === RunStatus::Active, 404);

        $run->loadMissing(['umamusume', 'inheritanceParentA', 'inheritanceParentB']);

        $payload = $run->legacySelection();

        return Inertia::render('Legacy/Builder', [
            'run' => [
                'id' => $run->id,
                'status_label' => $run->status->label(),
                'scenario_label' => VeteranRow::scenarioLabel($run),
            ],
            'trainee' => [
                'id' => $run->umamusume->id,
                'name' => $run->umamusume->name,
                'name_ja' => $run->umamusume->name_ja,
            ],
            // The graph as the payload holds it, or as the empty six nodes when there is no read-back
            // yet. `hasSelection` is the third state `TrainingRun::legacySelection()` distinguishes
            // (D-220): "you never opened this screen" is a disclosure, while two empty parent records
            // is a claim about a client screen that cannot be completed that way.
            'hasSelection' => $payload !== null,
            'graph' => $this->graph($payload, $run),
            // The stored grade, so the select opens on what the run holds rather than on "Not recorded".
            // The edit form seeds from the payload for every field it carries; a re-confirm that wrote
            // blank would drop a grade the Trainer already read.
            'affinity' => $payload?->affinity,
            'roster' => $this->roster(),
            // The names of every `umamusume` the Trainer can type into an ancestor field. Grandparents
            // are frequently absent from the local catalogue, which is why they are names and not ids
            // (`ADR-0010` Consequences §2), so the list is a spelling aid and never a requirement.
            'knownNames' => Umamusume::query()->orderBy('name')->pluck('name')->all(),
            'sparkKinds' => AncestryGraph::SPARK_KIND_LABELS,
            'affinityGrades' => LegacySelectionPayload::AFFINITY_GRADES,
            'rankLetters' => LegacySelectionPayload::RANK_LETTERS,
            // The target options per category, the same assembly the wizard's ancestry step sends
            // (`LegacySelectController::show()`), because both screens post against the one request and
            // the category boundary check needs the same option lists to filter from.
            'sparkTargets' => [
                'Stat' => array_values((array) config('scenarios.stat_order')),
                'Aptitude' => LegacySelectionPayload::SPARK_APTITUDES,
                'Skill' => Skill::query()->orderBy('name')->pluck('name')->all(),
            ],
            'notice' => self::RECORD_ONLY_NOTICE,
        ]);
    }

    /**
     * `GET /legacy/compare` — up to four recorded configurations side by side.
     *
     * Rows are properties, columns are runs, and both are stored. The alignment is `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` §46's
     * whole ask: "never force the user to compare two separate cards mentally". There is no total
     * row, no "best" marker and no ordering, because each of those would rank configurations on a
     * value the tool does not hold (`ADR-0020` §3).
     */
    public function compare(LegacyCompareRequest $request): Response
    {
        $columns = [];

        foreach ($request->runsInOrder($request->runIds()) as $run) {
            $payload = $run->legacySelection();
            // The library row is optional: a run can hold a Legacy selection and never have been
            // saved to the Veteran library, and its tags and notes read as `N/A` rather than as an
            // empty list that looks like "this Veteran has no tags" (`LegacyCompareRequest`'s
            // docblock says why the query is on the run).
            $veteran = $run->veteran;

            $columns[] = [
                'veteran_id' => $veteran?->id,
                'run_id' => $run->id,
                'trainee' => $run->umamusume->name,
                // `tags` is `array<array-key, mixed>|null` on the model, so a Trainer who saved a
                // Veteran with no tags really does produce null, and the column prints an empty list
                // as "no tags" rather than as a missing cell. The null arm is written explicitly
                // because `?->tags ?? []` reads as if `??` were doing work it does not: `tags` is
                // already typed nullable, so only the absent *Veteran* needs coalescing.
                'tags' => $veteran === null ? [] : ($veteran->tags ?? []),
                'notes' => $veteran?->notes,
                'hasSelection' => $payload !== null,
                'graph' => $this->graph($payload, $run),
                // The wireframe's two starting-cap cards (`legacy-compare.html:90-115`).
                'caps' => $this->startingCaps($run),
            ];
        }

        return Inertia::render('Legacy/Compare', [
            'columns' => $columns,
            'maxRuns' => LegacyCompareRequest::MAX_RUNS,
            'comparable' => $this->comparableRuns(),
            'sparkKinds' => AncestryGraph::SPARK_KIND_LABELS,
            'notice' => self::RECORD_ONLY_NOTICE,
        ]);
    }

    /**
     * The five starting ceilings the compare page prints per column.
     *
     * `ScenarioCaps::forRun()` is the one owner of the arithmetic (`ADR-0015`/`ADR-0025`): the run's
     * entered ceiling where it states one, the scenario's published cap otherwise. Wit is the one stat
     * with no entered column (`ADR-0025` deliberately stores no `cap_wit`), so it prints the scenario's
     * cap and says so; a run with no scenario gets N/A with the reason rather than the base-cap number
     * wearing a scenario's name.
     *
     * @return list<array{stat: string, label: string, value: int|null, note: string|null}>
     */
    private function startingCaps(TrainingRun $run): array
    {
        // `ScenarioCaps` keys its matrix with the config's capitalized stat names, not slugs.
        $caps = ScenarioCaps::forRun($run);
        $labels = ['Speed' => 'Speed', 'Stamina' => 'Stamina', 'Power' => 'Power', 'Guts' => 'Guts', 'Wit' => 'Wit'];

        $rows = [];

        foreach ($labels as $stat => $label) {
            if ($stat === 'Wit') {
                $value = $run->hasScenario() ? ScenarioCaps::stat($run->scenarioKey(), 'Wit') : null;

                $rows[] = [
                    'stat' => strtolower($stat),
                    'label' => $label,
                    'value' => $value,
                    'note' => $value === null
                        ? 'No scenario is set on this run, and this build stores no entered Wit ceiling.'
                        : "The scenario's published cap; this build stores no entered Wit ceiling.",
                ];

                continue;
            }

            $rows[] = ['stat' => strtolower($stat), 'label' => $label, 'value' => $caps[$stat], 'note' => null];
        }

        return $rows;
    }

    /**
     * `PUT /legacy/{run}` — "Confirm Inheritance".
     *
     * The write `SCREEN_SPEC.md` §7-3 recorded as missing. It is a whole-column replace rather than a
     * merge, for the reason `updateBuildTarget` gives: the payload is entered and read as one object,
     * and a merge would leave a Spark the Trainer just deleted sitting in the column while nothing on
     * screen still claimed it.
     *
     * **The two parent foreign keys are written in the same statement as the json.** The owner's
     * `ADR-0010` ruling kept those columns as the parent's identity and put the read-back details in
     * the payload, so a builder that stored only the json would leave the graph's two parent names
     * reading `N/A` while the Trainer had just entered them. `legacy_id` is resolved to an
     * `umamusume` id here, and a slot left unchosen is written as null rather than left holding the
     * previous run's parent, which is the same reason the json is replaced whole.
     *
     * `D-260`'s fixity ("its result is fixed for the life of the run") is enforced here, not in the
     * payload, which is where `ADR-0010` Consequences §3 says the guard belongs: the rule only becomes
     * reachable once a writer exists, and it is reachable from exactly one.
     */
    public function update(StoreLegacySelectionRequest $request, TrainingRun $run): RedirectResponse
    {
        abort_unless($run->status === RunStatus::Active, 404);

        $parentIds = $this->parentUmamusumeIds($request);

        $run->update([
            'legacy_selection' => $request->payload(),
            'inheritance_parent_a_id' => $parentIds[0],
            'inheritance_parent_b_id' => $parentIds[1],
        ]);

        return redirect()
            ->route('legacy.compare', ['runs' => [$run->id]])
            ->with('status', 'Inheritance recorded.');
    }

    /**
     * The `umamusume` id behind each picked library row, or null for a slot left unchosen.
     *
     * A `legacy_id` names a `veterans` row, and a Veteran is a run whose trainee is the Umamusume the
     * client will show in the parent slot. Reading the identity through the run is what stops a
     * library id being written into a column that means a trainee.
     *
     * @return array{0: int|null, 1: int|null}
     */
    private function parentUmamusumeIds(StoreLegacySelectionRequest $request): array
    {
        /** @var list<array<string, mixed>> $legacies */
        $legacies = (array) $request->validated('legacies', []);

        $ids = [];

        foreach ($legacies as $legacy) {
            $id = $legacy['legacy_id'] ?? null;

            $ids[] = Veteran::traineeId(is_int($id) ? $id : null);
        }

        return [$ids[0] ?? null, $ids[1] ?? null];
    }

    /**
     * The runs the compare surface can open: the ones carrying a read-back.
     *
     * A run with no payload would print a column of `N/A`, which is the "looks compared and is not"
     * failure `LegacyCompareRequest`'s docblock names. The list is bounded and not cached, being
     * Trainer data (`ARCHITECTURE.md` §6).
     *
     * @return list<array{id: int, label: string}>
     */
    private function comparableRuns(): array
    {
        return array_values(TrainingRun::query()
            ->whereNotNull('legacy_selection')
            ->with('umamusume')
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(static fn (TrainingRun $run): array => [
                'id' => $run->id,
                'label' => $run->umamusume->name.' · run #'.$run->id,
            ])
            ->all());
    }

    /**
     * The picker's rows. One shape for the browse list's roster and the builder's roster, so the two
     * cannot disagree about who is in the library.
     *
     * @return list<array{id: int, name: string}>
     */
    private function roster(): array
    {
        return array_values(Veteran::query()
            ->with('trainingRun.umamusume')
            ->latest('id')
            ->get()
            ->map(static fn (Veteran $veteran): array => [
                'id' => $veteran->id,
                'name' => $veteran->trainingRun->umamusume->name,
            ])
            ->values()
            ->all());
    }

    /**
     * The six-node graph, as the run and its payload hold it.
     *
     * The shape itself lives in `AncestryGraph`, which the setup wizard's ancestry step reads through the
     * same call, so the run screen, the compare surface and the draft screen cannot disagree about what a
     * node is. This method supplies the three names the payload does not hold.
     *
     * @return array{trainee: array{name: string|null}, parents: list<array<string, mixed>>}
     */
    private function graph(?LegacySelectionPayload $payload, ?TrainingRun $run = null): array
    {
        $parentNames = $this->parentNames($run);

        return AncestryGraph::build(
            $payload,
            $run?->umamusume?->name,
            $parentNames[0],
            $parentNames[1],
        );
    }

    /**
     * The two parent names, from the run's own foreign keys rather than from the payload.
     *
     * `loadMissing` rather than `load`, so the builder's own eager load is not re-issued.
     *
     * @return array{0: string|null, 1: string|null}
     */
    private function parentNames(?TrainingRun $run): array
    {
        if ($run === null) {
            return [null, null];
        }

        $run->loadMissing(['inheritanceParentA', 'inheritanceParentB']);

        return [
            $run->inheritanceParentA?->name,
            $run->inheritanceParentB?->name,
        ];
    }
}
