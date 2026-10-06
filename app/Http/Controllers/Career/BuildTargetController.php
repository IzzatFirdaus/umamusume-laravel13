<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Enums\BuildPurpose;
use App\Http\Controllers\Controller;
use App\Http\Requests\Career\StoreDraftBuildTargetRequest;
use App\Models\Advisor\BuildTargetPayload;
use App\Services\Career\SetupDraft;
use App\Services\ScenarioCaps;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Build Target, `SCREEN-005` (PRD FR-F-1, `ADR-0020` §1). Step 3 of the setup wizard, headed
 * "Your target".
 *
 * **The target is enterable before a run exists.** This step reads and writes the same session draft
 * as steps 1 and 2 (`SetupDraft`'s class docblock carries the reasoning), and the stat ceilings come
 * from `ScenarioCaps::forRun(SetupDraft::planningRun())`: a saved=false `TrainingRun` carrying the
 * draft's scenario, which is all `forRun()` reads. When the draft has no scenario yet the ceilings
 * are the base cap with no bonus, which is `forRun(null)`'s own answer, and the page states that the
 * scenario is not chosen rather than lending a bonus nobody picked.
 *
 * **The server is the authority on the clamp** (`ScenarioCaps::forRun`, `ADR-0015`, KI-47). The page's
 * `max` attribute and its bar mirror the same ceiling as a convenience; the write is refused by
 * `StoreDraftBuildTargetRequest` (one rule set with the run-scoped write) when a number is above it,
 * with the bound named (D-56).
 *
 * **Everything the form offers comes from an owner, not from this file.** The purpose options are
 * `BuildPurpose`'s four cases labelled through `uma.build_purpose` (the map the enum docblock owes),
 * and the distance, surface and style vocabularies are `BuildTargetPayload`'s constants, so the form
 * and the payload's own reader cannot drift. The design brief also names a fifth purpose,
 * "Competitive Build", which has no `BuildPurpose` case: the page discloses that gap in one line
 * rather than offering an option the write would refuse or adding a case (a stored-vocabulary change
 * needing owner approval). Risk tolerance and per-skill marks (Required / High / Optional / Ignore)
 * are not recorded either, for the same reason: `BuildTargetPayload::KEYS` is exactly six keys and
 * `assertKeys()` refuses an unknown one.
 *
 * **The bar is not a readiness verdict.** Whether a target is reachable in the turns left is held
 * computation in this repository; the bar is the entered number against the config cap and nothing
 * more, and it never renders without the number printed beside it.
 */
class BuildTargetController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Career/BuildTarget', [
            // The stored payload as entered, or null before one exists: the page names the absence
            // and pre-fills no zeroes.
            'target' => SetupDraft::buildTarget(),
            'caps' => ScenarioCaps::forRun(SetupDraft::planningRun()),
            'statOrder' => array_values((array) config('scenarios.stat_order')),
            'purposeOptions' => $this->purposeOptions(),
            'distanceBands' => BuildTargetPayload::DISTANCE_BANDS,
            'surfaces' => BuildTargetPayload::SURFACES,
            'styles' => BuildTargetPayload::STYLES,
            'scenarioLabel' => SetupDraft::scenarioLabel(),
            'scenarioPending' => SetupDraft::read()['scenario'] === null,
        ]);
    }

    /**
     * Writes the target to the draft and returns to this step, so the form reads back stored data
     * rather than what the client still holds. The run-scoped write (`runs.build-target.update`)
     * is untouched and keeps writing `training_runs.build_target` through the same rule set.
     */
    public function store(StoreDraftBuildTargetRequest $request): RedirectResponse
    {
        SetupDraft::write(['build_target' => $request->payload()]);

        return redirect()->route('career.target')->with('status', 'Build target saved.');
    }

    /**
     * One option per stored case, labelled through the `uma.build_purpose` map. The value is the
     * enum's backing value, which is what the payload stores and what a form field submits.
     *
     * @return list<array{value: string, label: string}>
     */
    private function purposeOptions(): array
    {
        return array_map(
            static fn (BuildPurpose $case): array => ['value' => $case->value, 'label' => $case->label()],
            BuildPurpose::cases(),
        );
    }
}
