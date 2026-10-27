<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\IndependentTraining\Lifecycle;
use Illuminate\Console\Command;

/**
 * The scheduler tick that plays out Independent Training (§1.1.7): the mode runs with the app closed,
 * so completion is a scheduled close of every session whose 50-minute window has passed, not a
 * browser-side timer.
 */
class CompleteIndependentTraining extends Command
{
    protected $signature = 'independent-training:complete';

    protected $description = 'Complete Independent Training sessions whose 50-minute window has passed (§1.1.7)';

    public function handle(Lifecycle $lifecycle): int
    {
        $completed = $lifecycle->completeExpired();

        $this->info("Completed {$completed} independent training session(s).");

        return self::SUCCESS;
    }
}
