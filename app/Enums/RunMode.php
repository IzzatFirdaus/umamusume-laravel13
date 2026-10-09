<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * How a run came to exist, and therefore what its turn numbers mean.
 *
 * A `NewCareer` run's turn 1 is Junior Year Early January and its position is always derivable from
 * the turns it has logged. A `Snapshot` run was entered mid-career, so its position is what the
 * Trainer named (`training_runs.career_position`) and its turn numbers continue from there; deriving
 * one for it would answer the Trainer's own record with a computation. The distinction is what makes
 * `hasImportedPosition()` meaningful rather than merely true.
 */
enum RunMode: string
{
    case NewCareer = 'new_career';
    case Snapshot = 'snapshot';
}
