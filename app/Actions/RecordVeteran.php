<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Veteran;
use InvalidArgumentException;

/**
 * Records a completed run as a Veteran (PRD FR-G-1, ADR-0020 §3).
 *
 * Only a Completed run is recordable. The library is "a record built from a completed run" (FR-G-1), and a
 * run still Active or Retired is not finished, so accepting one would file a career the Trainer has not
 * completed into a library that reads as finished. The refusal is an InvalidArgumentException, the same
 * shape the model guards use, because the caller here is a future Form Request or the Save Veteran screen
 * and both own the message shown to the Trainer.
 *
 * Saving is keyed on the run rather than appended: one run is one Veteran, so recording it a second time
 * rewrites its tags and notes instead of growing a duplicate library row for the same career.
 */
final class RecordVeteran
{
    /**
     * @param  list<string>  $tags
     */
    public function handle(TrainingRun $run, array $tags = [], ?string $notes = null): Veteran
    {
        if ($run->status !== RunStatus::Completed) {
            throw new InvalidArgumentException(
                "Run [{$run->id}] is {$run->status->value}; only a Completed run is recordable as a Veteran.",
            );
        }

        return Veteran::updateOrCreate(
            ['training_run_id' => $run->id],
            ['tags' => $tags, 'notes' => $notes],
        );
    }
}
