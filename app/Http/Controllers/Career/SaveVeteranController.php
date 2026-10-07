<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Actions\RecordVeteran;
use App\Enums\RunStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\LegacyController;
use App\Http\Requests\StoreVeteranRequest;
use App\Models\TrainingRun;
use App\Services\Legacy\AncestryGraph;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Save Veteran, `SCREEN-020` (plan §8's D16, PRD FR-G-1). The screen that files a finished career into the
 * Veteran library, and the first caller of `RecordVeteran`.
 *
 * **This is the write half of D16.** The read half landed first as `VeteranController`, and its copy said
 * plainly that nothing in the build could file a career — which was true, and is what this screen changes.
 * `ADR-0020` §3 authorises the library as *recording only*, so this screen stores the Trainer's own tags and
 * note beside a career that already exists. It adds no column: `veterans` holds `training_run_id`, `tags` and
 * `notes`, and everything else on the screen is read back through the run.
 *
 * **Three figures the brief asks for are not here.** `screen-spec-2.0` §24's correction row upgrades this
 * screen to a Factor Analysis workflow that prints a legacy value and a best-use recommendation, in the form
 * "Excellent Medium parent. Best used for: Medium, Pace Chaser, Speed-oriented builds". All three are
 * `ADR-0020` §3's held computation, and none has a column to read even if it were permitted, so each arrives
 * as `null` with the ruling as its `title` rather than as a score. `SCR-CAR-018` records the same ruling for
 * the Result screen one step earlier, and the two must not disagree.
 *
 * **A Veteran has no name of its own.** The brief lists "veteran name"; the table stores no such column, and
 * a Veteran *is* the run whose trainee she was (`ADR-0010` keeps the foreign keys as the identity), so the
 * screen prints her name read through the run and says the library row adds tags and a note, not a name.
 *
 * Only a Completed run reaches the write. A non-`Completed` run arrives on the GET and sees the blocked
 * state and no form: the `blocked` prop replaces the whole form in `SaveVeteran.vue`, so the browser path
 * prints no validation error. `StoreVeteranRequest` owns the Completed rule as a field error for a client
 * that posts without the screen, and `RecordVeteran` guards it in the domain for a caller that bypasses the
 * request; both refusals are tested at their own layer, not in the browser.
 */
class SaveVeteranController extends Controller
{
    public function show(TrainingRun $run): Response
    {
        $run->load([
            'umamusume',
            'turnEntries',
            'skills',
            'raceEntries',
            'inheritanceParentA',
            'inheritanceParentB',
            'veteran',
        ]);

        $recordable = $run->status === RunStatus::Completed;
        $latest = $run->turnEntries->sortByDesc('turn')->first();
        $strip = $run->stripValues();
        $veteran = $run->veteran;

        return Inertia::render('Career/SaveVeteran', [
            'run' => [
                'id' => $run->id,
                // Read through the run, because no column names a Veteran apart from her (`ADR-0010`).
                'trainee' => $run->umamusume->name,
                'trainee_ja' => $run->umamusume->name_ja,
                'scenario_label' => $run->hasScenario()
                    ? (string) config('scenarios.scenarios.'.$run->scenarioKey().'.label', $run->scenarioKey())
                    : 'No scenario set',
                'status' => $run->status->value,
                'status_label' => $run->status->label(),
                'recordable' => $recordable,
            ],
            'career' => [
                'turns' => $strip['turn'],
                'energy' => $strip['energy'],
                'fans' => $strip['fans'],
            ],
            // The last logged turn's totals, never a sum of deltas (ADR-0003). A run with no logged turn has
            // no stat at all, and null is what the page prints as `N/A` beside its reason.
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
            // Reviewed before tagging: what the career actually recorded, read-only.
            'graph' => AncestryGraph::build(
                $run->legacySelection(),
                $run->umamusume->name,
                $run->inheritanceParentA?->name,
                $run->inheritanceParentB?->name,
            ),
            'spark_kinds' => AncestryGraph::SPARK_KIND_LABELS,
            'suggested_tags' => config('uma.veteran.suggested_tags'),
            // The form's ceilings come from the request that enforces them, so the page's `maxlength` and
            // the boundary's `max` cannot drift apart into a field that truncates at one number and refuses
            // at another.
            'limits' => StoreVeteranRequest::limits(),
            'saved' => $veteran === null ? null : [
                'tags' => $veteran->tags ?? [],
                'notes' => $veteran->notes,
            ],
            'held' => [
                'factor_analysis' => [
                    'value' => null,
                    'title' => 'Factor analysis is a held computation: `ADR-0020` §3 keeps the library to storing and searching what the Trainer entered, and no table in this repository prices a Spark.',
                ],
                'legacy_value' => [
                    'value' => null,
                    'title' => 'A legacy value would be a score over the recorded Sparks, which `ADR-0020` §3 forbids deriving and no column holds.',
                ],
                'best_use' => [
                    'value' => null,
                    'title' => 'A best use is a recommendation, and `ADR-0020` §3 keeps auto-proposed parents and recommended combinations out of the record screens.',
                ],
            ],
            'notice' => LegacyController::RECORD_ONLY_NOTICE,
            'absences' => self::absences(),
            'blocked' => $recordable ? null : 'This career is '.$run->status->label().', not Completed, so there is nothing to file yet. The run record is where the status changes, and the Career Result is what a finished career adds up to.',
            'save_url' => route('runs.veteran.store', $run),
            'result_url' => route('runs.result', $run),
            'run_url' => route('runs.show', $run),
            'library_url' => route('veterans.index'),
        ]);
    }

    /**
     * Files the career, or rewrites the row that already holds it.
     *
     * One run is one Veteran (`RecordVeteran` keys the write on `training_run_id`), so saving twice is an
     * edit rather than a duplicate career — the reason the screen prefills from an existing row.
     *
     * The run is a typed parameter rather than read off the request, the same shape
     * `InheritanceEventController::store()` uses: route-model binding resolves `{run}` from the controller's
     * own signature, so a method that never names it leaves the parameter a bare id string and every
     * `instanceof` check downstream refuses it.
     */
    public function store(StoreVeteranRequest $request, TrainingRun $run, RecordVeteran $record): RedirectResponse
    {
        $veteran = $record->handle($run, $request->tagList(), $request->noteText());

        return redirect()
            ->route('veterans.show', $veteran)
            ->with('status', "Veteran saved: {$run->umamusume->name}.");
    }

    /**
     * What `SCREEN-020` and `SCREEN-021` ask for that this build cannot give, each with its reason on the
     * screen.
     *
     * Favorite, archive and delete are absent because `veterans` holds no column for them; adding one is a
     * stored-shape decision for the schema owner, not a UI slice, so the brief's destructive flow is named
     * rather than improvised. The three held figures are named again here even though `held` already carries
     * them, because the list is what the page prints as a section and a Trainer reading only it still gets
     * the reason.
     *
     * @return list<array{label: string, reason: string}>
     */
    private static function absences(): array
    {
        return [
            [
                'label' => 'Factor analysis, a legacy value, a best use',
                'reason' => 'Held computation under `ADR-0020` §3, and no table in this repository prices a Spark or scores a career.',
            ],
            [
                'label' => 'A name for the Veteran apart from her trainee',
                'reason' => 'The library row is a pointer to the career (`ADR-0010`), so it adds tags and a note and never a second identity.',
            ],
            [
                'label' => 'Favorite, archive, delete',
                'reason' => 'No column holds any of the three on a library row, and adding one is a stored-shape change the schema owner decides.',
            ],
        ];
    }
}
