<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Http\Controllers\Controller;
use App\Models\TrainingRun;
use App\Services\Advisor\AdvisorAnalysis;
use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * The Advisor AI read route (`ADR-0038`, accepted 2026-11-25).
 *
 * **Its own route, not a cockpit key.** The cockpit's `advisor` prop is pinned to exactly
 * `decision` and `finale` by `CareerCockpitTest`, and that prop is a recorded contract. Widening it
 * to carry an optional AI layer would make every page render pay for a feature that is off by
 * default, so the layer reads here instead.
 *
 * **Off is a 404, not an empty payload.** `advisor_ai.enabled` defaults to false. A closed switch
 * and a career with no recommendation are different statements, and answering 200 with a
 * half-shaped body would collapse them.
 *
 * **Read-only.** This action renders; it never writes. `AdvisorAnalysis` has no save, no update and
 * no dispatch, so a failed analysis leaves the career byte-identical — asserted by
 * `AdvisorAnalysisRouteTest` rather than assumed.
 *
 * **A failure names the category, never the failure.** The 503 body is fixed copy: an exception
 * message can carry a provider token, and the client is owed a stable code it can branch on.
 */
final class AdvisorAnalysisController extends Controller
{
    public function show(TrainingRun $run): JsonResponse
    {
        abort_unless((bool) config('advisor_ai.enabled'), 404);

        // Resolved rather than injected. `AdvisorAnalysis` is `final`, so a test that forces the
        // failure path can only bind a partial mock of the real instance into the container; a
        // parameter type-hint would reject that mock before the action ran and the 503 branch would
        // be unreachable. Resolution keeps the substitution the test (and any future fault
        // injection) depends on.
        $analysis = app(AdvisorAnalysis::class);

        // The same "latest turn" rule the cockpit reads, resolved here so the service stays a pure
        // function of (run, turn) and a second implementation of the rule is not introduced.
        $latest = $run->liveTurnEntries->sortByDesc('turn')->first();

        try {
            $payload = $analysis->analyze($run, $latest);
        } catch (Throwable) {
            // The exception's own words stay server-side: the response carries a code, not a trace.
            return response()->json([
                'error' => [
                    'code' => 'advisor_analysis_unavailable',
                    'message' => 'The analysis could not be produced. The recommendation on the cockpit is unaffected.',
                ],
            ], 503);
        }

        return response()->json($payload);
    }
}
