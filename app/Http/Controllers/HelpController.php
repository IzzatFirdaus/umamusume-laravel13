<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

/**
 * The Help screen (HFW help.html; plan §5 Slice 11). Reachable from the topbar's Help button on
 * every screen and deliberately absent from the rail (HFW annotation: help is a utility, not a
 * destination). The page answers the four questions the wireframe names plus one app-local card,
 * About this build, which reads the version from the shared `app` props; the shell prints none
 * (the topbar ruling in `AppLayout.vue`). No page props of its own. No PRD citation for the
 * screen exists yet; recorded as a documentation follow-up in the release notes rather than a
 * blocker.
 */
class HelpController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Help');
    }
}
