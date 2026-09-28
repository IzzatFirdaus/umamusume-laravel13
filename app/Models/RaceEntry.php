<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RaceEntryStatus;
use Database\Factories\RaceEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * What the Trainer did with one calendar slot (ADR-0003 race_entries). A
 * declined race is a row with status Skipped, not an absent row.
 *
 * @property int $id
 * @property int $training_run_id
 * @property int|null $scenario_race_id
 * @property int|null $scenario_slot_id
 * @property RaceEntryStatus $status
 * @property int|null $placement
 * @property int|null $fans_gain
 * @property int|null $objective_index the Grade Point period this finish counts toward,
 *                                     as the Trainer reported it (1..4, US-10); null
 *                                     when the run composes no grade objectives or the
 *                                     Trainer has not said yet
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read TrainingRun $trainingRun
 * @property-read ScenarioRace|null $scenarioRace
 * @property-read ScenarioSlot|null $scenarioSlot
 */
#[Table('race_entries')]
#[Fillable(['training_run_id', 'scenario_race_id', 'scenario_slot_id', 'status', 'placement', 'fans_gain', 'objective_index'])]
class RaceEntry extends Model
{
    /** @use HasFactory<RaceEntryFactory> */
    use HasFactory;

    /**
     * The four Grade Point periods, in the order the matrix lists them: the debut
     * race, then the end of Junior, Classic and Senior year (ADR-0003, US-10).
     */
    public const MAX_OBJECTIVE_INDEX = 4;

    /**
     * `objective_index` is entered, never inferred (D-270), and it is only ever
     * meaningful on a run whose scenario composes grade objectives. A column CHECK
     * could bound the number but cannot see the run's scenario, so the rule lives
     * here, the way `ScenarioSlot` carries its own kind and month checks.
     */
    protected static function booted(): void
    {
        static::saving(function (self $entry): void {
            if ($entry->objective_index !== null) {
                TrainingRun::assertGradePeriod($entry->objective_index, $entry->trainingRun, 'objective_index');
            }
        });
    }

    /**
     * @return BelongsTo<TrainingRun, $this>
     */
    public function trainingRun(): BelongsTo
    {
        return $this->belongsTo(TrainingRun::class);
    }

    /**
     * @return BelongsTo<ScenarioSlot, $this>
     */
    public function scenarioSlot(): BelongsTo
    {
        return $this->belongsTo(ScenarioSlot::class, 'scenario_slot_id');
    }

    /**
     * @return BelongsTo<ScenarioRace, $this>
     */
    public function scenarioRace(): BelongsTo
    {
        return $this->belongsTo(ScenarioRace::class);
    }

    protected function casts(): array
    {
        return [
            'status' => RaceEntryStatus::class,
            'placement' => 'integer',
            'fans_gain' => 'integer',
            'objective_index' => 'integer',
        ];
    }
}
