<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Actions\ListVeterans;
use App\Http\Controllers\Controller;
use App\Http\Controllers\LegacyController;
use App\Http\Requests\Career\StoreDraftLegacyRequest;
use App\Models\Legacy\LegacySelectionPayload;
use App\Models\Skill;
use App\Models\Umamusume;
use App\Models\Veteran;
use App\Services\Career\SetupDraft;
use App\Services\Legacy\AncestryGraph;
use App\Services\ScenarioCaps;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Legacy Select, `SCR-CAR-008` (PRD FR-G-1, `ADR-0020` §1 and §3). Step 4 of the setup wizard.
 *
 * **The ancestry is enterable before a run exists**, which is the reason this step exists at all: the
 * Legacy Lab that does ship is run-scoped (`/legacy/{run}` writes `training_runs.legacy_selection`), and
 * with no run there was nowhere to put the two parents. This step reads and writes the same session draft
 * as steps 1 to 3 (`SetupDraft`'s class docblock carries the reasoning) and the run is created once, at
 * Preflight (D7), which finds the payload and the two parent identities already entered.
 *
 * **Two slots, not a graph (Phase D).** The step draws the wireframe's two parent cards
 * (`career-legacy.html:87-120`), not the six-node graph: the graph's run-scoped home is
 * `InheritanceEvent.vue`, which already renders the ancestry (`InheritanceEvent.vue:442-450`). The
 * controller no longer builds the graph here; it resolves the two parent cards from the draft's library
 * picks and the recorded payload, and supplies the scenario ceilings the projected-start table reads out.
 *
 * **Record only, exactly as the run-scoped screen is.** `ADR-0020` §3 and PRD FR-G-4: these surfaces store
 * and compare what the Trainer typed and compute nothing. No inheritance outcome, no expected stat, no
 * Affinity payout, no Spark roll probability, no score, no ranking, no recommended combination. The same
 * notice string the Legacy Lab prints travels here as the prop `notice`, from its one owner.
 *
 * **The pick list is `ListVeterans`, and it is a page.** The action clamps its page size at
 * `PageSize::MAX`, so the roster this step offers is the first 100 rows of the Trainer's own library and
 * `rosterTotal` says how large that library is. A library longer than the page is rare and is disclosed in
 * one line rather than handled by a paginated picker a parent slot does not need; the full browse surface is
 * `/legacy`'s.
 */
class LegacySelectController extends Controller
{
    /**
     * `GET /career/setup/legacy`.
     *
     * The two parent cards are resolved here: the draft's `legacy_parents` ids name the veterans, and the
     * recorded `legacy_selection` payload carries the sparks and rank each card prints. A slot with no pick
     * is null, which the card reads as `Not chosen`.
     */
    public function show(ListVeterans $veterans): Response
    {
        SetupDraft::markVisited(4);
        $draft = SetupDraft::read();
        $trainee = $draft['umamusume_id'] === null
            ? null
            : Umamusume::query()->find($draft['umamusume_id']);

        $selection = SetupDraft::legacySelection();
        $parentIds = SetupDraft::legacyParents();
        $library = $veterans->handle([], 100);

        // The costume is what tells two parents with the same trainee name apart, so it is loaded with
        // the page rather than per row: a pick list of two `Vodka` rows is the defect this answers.
        $library->getCollection()->loadMissing('trainingRun.characterCard');

        $pickedIds = array_values(array_filter($parentIds, static fn (?int $id): bool => $id !== null));
        $picked = $pickedIds === []
            ? collect()
            : Veteran::query()
                ->whereIn('id', $pickedIds)
                ->with(['trainingRun.umamusume', 'trainingRun.characterCard'])
                ->get()
                ->keyBy('id');

        $slotPayloads = (array) ($selection['legacies'] ?? []);
        $parents = [];

        foreach ([0, 1] as $slot) {
            $veteranId = $parentIds[$slot] ?? null;
            $veteran = $veteranId === null ? null : $picked->get($veteranId);

            if ($veteran === null) {
                $parents[] = null;

                continue;
            }

            /** @var array<string, mixed> $slotPayload */
            $slotPayload = (array) ($slotPayloads[$slot] ?? []);
            $run = $veteran->trainingRun;
            $scenario = is_string($run?->scenario) && $run->scenario !== ''
                ? (string) config('scenarios.scenarios.'.$run->scenario.'.label', $run->scenario)
                : null;

            /** @var array<int, array{family: string, stat: string|null, stars: int|null}> $sparks */
            $labels = LegacySelectionPayload::sparkKindLabels();
            $sparks = array_map(static function (mixed $spark) use ($labels): array {
                $spark = (array) $spark;
                $kind = (string) ($spark['kind'] ?? '');

                return [
                    // The [Global] display label for the recorded kind, so the card prints `Blue spark`
                    // and the projected-start table's per-stat match reads the same word.
                    'family' => $labels[$kind] ?? ucfirst($kind),
                    'stat' => isset($spark['target']) && is_string($spark['target']) ? $spark['target'] : null,
                    'stars' => isset($spark['stars']) && is_numeric($spark['stars']) ? (int) $spark['stars'] : null,
                ];
            }, (array) ($slotPayload['sparks'] ?? []));

            $parents[] = [
                'veteranId' => $veteran->id,
                'runId' => $veteran->training_run_id,
                'name' => (string) $run->umamusume->name,
                'japanese' => $run->umamusume->name_ja,
                'scenario' => $scenario,
                'stars' => isset($slotPayload['rank']) && is_numeric($slotPayload['rank']) ? (int) $slotPayload['rank'] : null,
                'savedAt' => $veteran->created_at?->toDateString(),
                'sparks' => $sparks,
            ];
        }

        $planningRun = SetupDraft::planningRun();

        return Inertia::render('Career/LegacySelect', [
            'visited' => SetupDraft::visited(),
            'saved' => SetupDraft::saved(),
            'trainee' => $trainee === null
                ? null
                : ['id' => $trainee->id, 'name' => $trainee->name, 'name_ja' => $trainee->name_ja],
            'scenarioLabel' => SetupDraft::scenarioLabel(),
            // The third state `TrainingRun::legacySelection()` distinguishes on the run screen (D-220):
            // "this step was never opened" is a disclosure, while two empty parent records is a claim
            // about a client screen nobody has read yet.
            'hasSelection' => $selection !== null,
            // The grade for the pair, as entered. It is a letter the Trainer read off the client and never
            // a value this tool works out (`ADR-0020` §3), and the step's own field has to read it back or
            // a saved step would show an empty control over a stored grade.
            'affinity' => $selection['affinity'] ?? null,
            // The raw payload, so the form's round-trip keeps the record whole: a re-saved pick rewrites
            // the same ancestors and sparks rather than erasing them.
            'payload' => $selection,
            'parentA' => $parents[0] ?? null,
            'parentB' => $parents[1] ?? null,
            // The scenario's published ceilings per stat, for the projected-start table's read-out. A
            // draft with no scenario gets the base cap with no bonus, matching `ScenarioCaps::scenarioCaps`.
            'scenarioCaps' => ScenarioCaps::scenarioCaps($planningRun),
            'statOrder' => array_values((array) config('scenarios.stat_order')),
            // The library ids the pick controls select, so a saved step reads back what was chosen rather
            // than what the client still holds. Position in the list is the slot: 0 is Parent A.
            'parents' => $parentIds,
            'roster' => $library
                ->through(static function (Veteran $veteran): array {
                    $name = $veteran->trainingRun->umamusume->name;
                    $costume = $veteran->trainingRun->characterCard?->title;

                    return [
                        'id' => $veteran->id,
                        'name' => $name,
                        'costume' => $costume,
                        // The option's own label: two rows the client spells the same are told apart by
                        // the costume and always by the library id.
                        'label' => $costume === null
                            ? "{$name} · {$veteran->id}"
                            : "{$name} {$costume} · {$veteran->id}",
                    ];
                })
                ->items(),
            'rosterTotal' => $library->total(),
            'affinityGrades' => LegacySelectionPayload::AFFINITY_GRADES,
            // The four vocabularies the grandparent editors need, assembled from the same sources the
            // Legacy Lab uses (LegacyController::show()), because both screens post against the one
            // request: a Spark's category boundary check filters these same lists
            // (StoreLegacySelectionRequest::assertTargetMatchesCategory), and a separate copy would let
            // the two surfaces offer different options for one validated field.
            'knownNames' => Umamusume::query()->orderBy('name')->pluck('name')->all(),
            'sparkKinds' => AncestryGraph::SPARK_KIND_LABELS,
            'rankLetters' => LegacySelectionPayload::RANK_LETTERS,
            'sparkTargets' => [
                'Stat' => array_values((array) config('scenarios.stat_order')),
                'Aptitude' => LegacySelectionPayload::SPARK_APTITUDES,
                'Skill' => Skill::query()->orderBy('name')->pluck('name')->all(),
            ],
            'notice' => LegacyController::RECORD_ONLY_NOTICE,
            // The step's write URL, supplied server-side the way CareerLayout.vue:11 and
            // UnityCupEditor.vue:20 already receive URLs. No Ziggy dependency is introduced.
            'action' => route('career.legacy.store'),
        ]);
    }

    /**
     * `PUT /career/setup/legacy`.
     *
     * The payload and the two library picks are written together, because one without the other is a graph
     * that cannot name its own parents: `LegacyController::update()` writes the json and the two foreign
     * keys in one statement for the same reason. A refused write stores neither — the Form Request answers
     * before the controller runs, and the draft keeps what it had.
     */
    public function store(StoreDraftLegacyRequest $request): RedirectResponse
    {
        SetupDraft::write([
            'legacy_selection' => $request->payload(),
            'legacy_parents' => $request->parentIds(),
        ]);

        return redirect()
            ->route('career.legacy')
            ->with('status', 'Legacy recorded.');
    }
}
