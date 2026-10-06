<?php

declare(strict_types=1);

namespace App\Http\Requests\Career;

use App\Http\Requests\StoreBuildTargetRequest;

/**
 * The build target of the setup wizard's step 3 (`SCREEN-005`, FR-F-1, `ADR-0020` §1).
 *
 * A subclass, not a second rule set. `rules()`, `messages()`, `withValidator()` and `payload()` are
 * `StoreBuildTargetRequest`'s own, so the wizard and the run-scoped write (`runs.build-target.update`)
 * can never drift apart: there is one definition of the accepted vocabularies, one of the five named
 * stats, and one of the payload's key order. The parent's ceiling check resolves its clamp target as
 * the route's run when the route has one and as `SetupDraft::planningRun()` when it does not, which is
 * this request's case.
 *
 * **The server is the authority on the clamp** (`ScenarioCaps::forRun`, `ADR-0015`). The page's `max`
 * attribute mirrors the same ceiling as a convenience for the browser only; nothing here trusts it,
 * and a hand-made POST that ignores it is refused with the bound named (D-56).
 *
 * The payload is written under the draft's `build_target` key by `BuildTargetController::store()`,
 * never to a run row: the run is created once, at Preflight (`SetupDraft`'s class docblock carries the
 * reasoning).
 */
class StoreDraftBuildTargetRequest extends StoreBuildTargetRequest
{
    /**
     * @var string
     */
    protected $redirectRoute = 'career.target';
}
