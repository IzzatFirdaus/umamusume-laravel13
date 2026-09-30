<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\TrainingRun;
use Illuminate\Support\Facades\DB;

/**
 * Writes one imported historical run and its turns (ADR-0017, PRD US-12's neighbouring concern).
 *
 * Deliberately small, and deliberately not called `Store*`: in this repo that prefix means "persist rows
 * the fetch pipeline ingested from a declared source" (`StoreSkills`, `StoreCharacterCards`,
 * `StoreSupportCards`), all of which write engine-owned reference data with provenance columns. An import
 * is a Trainer's own history arriving by file, which is why it follows `TrainingRunController::store()`'s
 * transaction shape instead.
 *
 * Every value is already validated by `ImportHistoricalRunRequest`, including each stat against the
 * scenario the run names, so this class assigns and persists rather than re-checking. It does not
 * re-normalise the scenario either: the request's inherited `prepareForValidation()` already turned an
 * empty string into null, which is the difference between an imported run rendering the baseline strip and
 * one 500ing the run page.
 */
final class ImportHistoricalRun
{
    /**
     * @param  array{umamusume_id: int, character_card_id?: int|null, scenario?: string|null, status: string, notes?: string|null, current_objective_index?: int|null}  $run
     * @param  list<array{turn: string|int, speed: string|int, stamina: string|int, power: string|int, guts: string|int, wit: string|int, sp: string|null, condition: string|null, energy: string|null, mood: string|null, fans: string|null}>  $turns
     */
    public function handle(array $run, array $turns, string $source): TrainingRun
    {
        return DB::transaction(function () use ($run, $turns, $source): TrainingRun {
            $model = TrainingRun::create([
                'umamusume_id' => $run['umamusume_id'],
                'character_card_id' => $run['character_card_id'] ?? null,
                'scenario' => $run['scenario'] ?? null,
                'status' => $run['status'],
                'notes' => $run['notes'] ?? null,
                'current_objective_index' => $run['current_objective_index'] ?? null,
                // Set at write time so the column states what actually happened: this row was created
                // from a file, and which file. A hand-typed run leaves both null.
                'imported_at' => now(),
                'import_source' => $source,
            ]);

            foreach ($turns as $turn) {
                $model->turnEntries()->create([
                    'turn' => (int) $turn['turn'],
                    'speed' => (int) $turn['speed'],
                    'stamina' => (int) $turn['stamina'],
                    'power' => (int) $turn['power'],
                    'guts' => (int) $turn['guts'],
                    'wit' => (int) $turn['wit'],
                    'sp' => $turn['sp'] === null ? null : (int) $turn['sp'],
                    'condition' => $turn['condition'],
                    'energy' => $turn['energy'] === null ? null : (int) $turn['energy'],
                    'mood' => $turn['mood'],
                    'fans' => $turn['fans'] === null ? null : (int) $turn['fans'],
                ]);
            }

            return $model;
        });
    }
}
