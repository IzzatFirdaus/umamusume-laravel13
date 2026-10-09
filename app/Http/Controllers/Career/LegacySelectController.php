<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Actions\ListVeterans;
use App\Http\Controllers\Controller;
use App\Http\Controllers\LegacyController;
use App\Http\Requests\Career\StoreDraftLegacyRequest;
use App\Models\Legacy\LegacySelectionPayload;
use App\Models\Umamusume;
use App\Models\Veteran;
use App\Services\Career\SetupDraft;
use App\Services\Legacy\AncestryGraph;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Legacy Select, `SCR-CAR-008` (PRD FR-G-1, `ADR-0020` §1 and §3). Step 4 of the setup wizard.
 *
 * **The ancestry is enterable before a run exists**, which is the reason this step exists at all: the
 * Legacy Lab that does ship is run-scoped (`/legacy/{run}` writes `training_runs.legacy_selection`), and
 * with no run there was nowhere to put the six nodes. This step reads and writes the same session draft as
 * steps 1 to 3 (`SetupDraft`'s class docblock carries the reasoning) and the run is created once, at
 * Preflight (D7), which finds the payload and the two parent identities already entered.
 *
 * **Record only, exactly as the run-scoped screen is.** `ADR-0020` §3 and PRD FR-G-4: these surfaces store
 * and compare what the Trainer typed and compute nothing. No inheritance outcome, no expected stat, no
 * Affinity payout, no Spark roll probability, no score, no ranking, no recommended combination. The
 * `probability` every node carries is `AncestryGraph`'s permanent named absence, `N/A` with the reason in
 * its `title`, and Affinity is an entered grade letter and never a value. The same notice string the Legacy
 * Lab prints travels here as the prop `notice`, from its one owner.
 *
 * **One shape, two screens.** The graph is built by `AncestryGraph::build()`, the same call
 * `LegacyController` makes for the builder and the compare surface, so a node means the same thing on the
 * wizard step as it does on the run. The only difference is where the two parent names come from: the run
 * keeps them in `training_runs.inheritance_parent_a_id` and `_b_id` (`ADR-0010` Decision) and the draft has
 * no row, so it holds the library picks under `legacy_parents` and this controller resolves the name
 * through them.
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
     * The trainee node is the draft's chosen `umamusume`, which is the same fact the run will carry:
     * `ADR-0010`'s read-back holds no name for the trainee, and the client's own screen shows her.
     */
    public function show(ListVeterans $veterans): Response
    {
        $draft = SetupDraft::read();
        $trainee = $draft['umamusume_id'] === null
            ? null
            : Umamusume::query()->find($draft['umamusume_id']);

        $selection = SetupDraft::legacySelection();
        $parents = AncestryGraph::parentNames(SetupDraft::legacyParents());
        $library = $veterans->handle([], 100);

        // The costume is what tells two parents with the same trainee name apart, so it is loaded with
        // the page rather than per row: a pick list of two `Vodka` rows is the defect this answers.
        $library->getCollection()->loadMissing('trainingRun.characterCard');

        return Inertia::render('Career/LegacySelect', [
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
            'graph' => AncestryGraph::build(
                $selection === null ? null : LegacySelectionPayload::fromArray($selection),
                $trainee?->name,
                $parents[0],
                $parents[1],
            ),
            // The library ids the pick controls select, so a saved step reads back what was chosen rather
            // than what the client still holds. Position in the list is the slot: 0 is Parent A.
            'parents' => SetupDraft::legacyParents(),
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
            // The names a Trainer can type into an ancestor field. Grandparents are frequently absent from
            // the local catalogue, which is why they are names and not ids (`ADR-0010` Consequences §2), so
            // the list is a spelling aid and never a requirement.
            'knownNames' => Umamusume::query()->orderBy('name')->pluck('name')->all(),
            'sparkKinds' => AncestryGraph::SPARK_KIND_LABELS,
            'affinityGrades' => LegacySelectionPayload::AFFINITY_GRADES,
            'notice' => LegacyController::RECORD_ONLY_NOTICE,
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
