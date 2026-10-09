<?php

declare(strict_types=1);

namespace App\Actions;

use App\Domain\Career\CareerPosition;
use App\Enums\CareerPhase;
use App\Enums\CareerYear;
use App\Enums\RunMode;
use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Services\CareerCalendar;
use Illuminate\Support\Facades\DB;

/**
 * The one writer a snapshot has, and the reason the snapshot flow needs its own action.
 *
 * A snapshot run is created at the position the Trainer read off their client, not at turn 1, so the
 * position's turn index is derived through the calendar that owns the arithmetic and stored on the
 * run - a stored position wins over a derived one, which is the whole point of the column.
 *
 * The snapshot's current state rides the run's own turn log: when the Trainer entered any stat, the
 * action writes one turn entry at the position's turn carrying what was read. That is not a workaround
 * - the entry records what the client showed at that turn, which is exactly what a turn entry is -
 * and it is why the run then needs no new read path at all: every surface that reads the run's latest
 * turn reads the imported values, and the run's next turn is the one after the position.
 */
final class CreateSnapshotRun
{
    /**
     * @param  array<string, mixed>  $data  the request's validated payload
     */
    public function handle(array $data): TrainingRun
    {
        return DB::transaction(function () use ($data): TrainingRun {
            $turnIndex = CareerCalendar::turnIndexFor(
                CareerYear::from((int) $data['career_year']),
                (int) $data['career_month'],
                CareerPhase::from((string) $data['career_phase']),
            );

            $position = CareerPosition::fromArray([
                'year' => (int) $data['career_year'],
                'month' => (int) $data['career_month'],
                'phase' => (string) $data['career_phase'],
                'turn_index' => $turnIndex,
            ]);

            $run = TrainingRun::create([
                'umamusume_id' => (int) $data['umamusume_id'],
                'scenario' => (string) $data['scenario'],
                'status' => RunStatus::Active,
                'mode' => RunMode::Snapshot,
                'notes' => $data['notes'] ?? null,
                'career_position' => $position,
                'career_position_source' => 'imported',
            ]);

            $state = array_filter([
                'speed' => $data['speed'] ?? null,
                'stamina' => $data['stamina'] ?? null,
                'power' => $data['power'] ?? null,
                'guts' => $data['guts'] ?? null,
                'wit' => $data['wit'] ?? null,
                'energy' => $data['energy'] ?? null,
                'fans' => $data['fans'] ?? null,
                'sp' => $data['skill_points'] ?? null,
            ], static fn (mixed $value): bool => $value !== null);

            if ($state !== []) {
                TurnEntry::create($state + [
                    'training_run_id' => $run->id,
                    'turn' => $turnIndex,
                ]);
            }

            return $run;
        });
    }
}
