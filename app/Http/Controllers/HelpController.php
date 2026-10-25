<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

/**
 * The Help screen (HFW help.html; plan §5 Slice 11). Reachable from the topbar's Help button on
 * every screen and deliberately absent from the rail (HFW annotation: help is a utility, not a
 * destination). A minimal honest page for now: the product loop and the named absence of undo.
 * No PRD citation for the screen exists yet; recorded as a documentation follow-up in the release
 * notes rather than a blocker.
 */
class HelpController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Help');
    }
}
