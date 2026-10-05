<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Veteran;

/**
 * Reads one Veteran for its screen (PRD FR-G-1, FR-G-3).
 *
 * The recorded facts live on the run, so the read eager-loads the ones FR-G-1 names — the trainee, the
 * logged turns (final stats), the skill states and the race record — and nothing else. It assembles no
 * payload and derives no figure: the show screen is a later slice (`frontend-development-plan.md` §7's
 * D16), and building its payload here would put a screen's shape into the domain layer. What this buys is
 * the one read the screen needs without an N+1 per relation.
 */
final class ShowVeteran
{
    public function handle(Veteran $veteran): Veteran
    {
        return $veteran->loadMissing([
            'trainingRun.umamusume',
            'trainingRun.turnEntries',
            'trainingRun.skills',
            'trainingRun.raceEntries',
        ]);
    }
}
