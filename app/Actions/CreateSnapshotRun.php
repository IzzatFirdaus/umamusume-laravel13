<?php

declare(strict_types=1);

namespace App\Actions;

use App\Domain\Career\CareerPosition;
use App\Enums\CareerPhase;
use App\Enums\CareerYear;
use App\Enums\EnergyState;
use App\Enums\RunMode;
use App\Enums\RunStatus;
use App\Enums\SnapshotFieldState;
use App\Http\Requests\StoreSnapshotRequest;
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
 * The snapshot's current state rides the run's own turn log: when the Trainer read all five core stats,
 * the action writes one turn entry at the position's turn carrying what was read. That is not a
 * workaround - the entry records what the client showed at that turn, which is exactly what a turn
 * entry is - and it is why the run then needs no new read path at all: every surface that reads the
 * run's latest turn reads the imported values, and the run's next turn is the one after the position.
 * A reading that marks any core stat Unknown or Not provided cannot form a complete turn row, so no
 * entry is written and the per-field states on the stored position are the record.
 */
final class CreateSnapshotRun
{
    /**
     * The five stat columns a turn entry cannot be written without: the schema holds them non-null, so
     * a reading that does not carry all five cannot form a complete turn row and no entry is written.
     */
    private const CORE_STATE_FIELDS = ['speed', 'stamina', 'power', 'guts', 'wit'];

    /**
     * The state fields whose columns are nullable, so a complete entry may carry them or omit them.
     */
    private const OPTIONAL_STATE_FIELDS = ['energy', 'fans', 'skill_points'];

    /**
     * @param  array<string, mixed>  $data  the request's validated payload
     */
    public function handle(array $data): TrainingRun
    {
        return DB::transaction(function () use ($data): TrainingRun {
            $states = $this->fieldStates($data);
            $values = $this->knownValues($data, $states);

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
                'field_states' => $states,
                'field_values' => $values === [] ? null : $values,
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

            $state = $this->completeEntryState($values);

            if ($state !== null) {
                TurnEntry::create($state + [
                    'training_run_id' => $run->id,
                    'turn' => $turnIndex,
                ]);
            }

            return $run;
        });
    }

    /**
     * A complete turn entry for the snapshot, or null when one cannot be formed honestly.
     *
     * The five core stats are non-null in the schema, so a reading that marks any of them Unknown or
     * Not provided cannot become a turn row: filling the gap with a zero would print a number the
     * Trainer never read, which is the guess the snapshot flow exists to refuse. In that case the
     * per-field states and values on the stored position are the record, and no turn entry is written.
     *
     * @param  array<string, int>  $values
     * @return array<string, int|string>|null
     */
    private function completeEntryState(array $values): ?array
    {
        $state = [];

        foreach (self::CORE_STATE_FIELDS as $field) {
            if (! isset($values[$field])) {
                return null;
            }

            $state[$field] = $values[$field];
        }

        foreach (self::OPTIONAL_STATE_FIELDS as $field) {
            if (isset($values[$field])) {
                $state[$field === 'skill_points' ? 'sp' : $field] = $values[$field];
            }
        }

        // Energy carries its own state, because a snapshot that did not read it is `unknown`, not a
        // blank: the audit's "N/A everywhere" is exactly the absence this distinguishes from a number.
        $state['energy_state'] = isset($values['energy'])
            ? EnergyState::Exact->value
            : EnergyState::Unknown->value;

        return $state;
    }

    /**
     * The numbers the Trainer read, keyed by state field name.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $states
     * @return array<string, int>
     */
    private function knownValues(array $data, array $states): array
    {
        $values = [];

        foreach (StoreSnapshotRequest::STATE_FIELDS as $field) {
            $value = $this->knownValue($data, $states, $field);

            if ($value !== null) {
                $values[$field] = $value;
            }
        }

        return $values;
    }

    /**
     * The integer a field carries when the Trainer marked it Known, or null otherwise.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $states
     */
    private function knownValue(array $data, array $states, string $field): ?int
    {
        if (($states[$field] ?? null) !== SnapshotFieldState::Known->value) {
            return null;
        }

        $value = $data[$field] ?? null;

        return ($value === null || $value === '') ? null : (int) $value;
    }

    /**
     * Each state field's confidence, defaulted from whether a value came with it.
     *
     * A form that posts no companion map is the flat shape the action has always taken, and there a
     * present value is Known and an absent one is Not provided. An explicit map wins: it is how the
     * review screen's "I don't know this value" survives into the store as a state of its own.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function fieldStates(array $data): array
    {
        $provided = is_array($data['field_states'] ?? null) ? $data['field_states'] : [];

        $states = [];

        foreach (StoreSnapshotRequest::STATE_FIELDS as $field) {
            $explicit = is_string($provided[$field] ?? null)
                ? SnapshotFieldState::tryFrom($provided[$field])
                : null;

            $states[$field] = ($explicit ?? (($data[$field] ?? null) === null || ($data[$field] ?? null) === ''
                ? SnapshotFieldState::NotProvided
                : SnapshotFieldState::Known))->value;
        }

        return $states;
    }
}
