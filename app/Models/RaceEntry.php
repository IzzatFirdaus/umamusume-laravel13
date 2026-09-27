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
 * @property RaceEntryStatus $status
 * @property int|null $placement
 * @property int|null $fans_gain
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read TrainingRun $trainingRun
 * @property-read ScenarioRace|null $scenarioRace
 */
#[Table('race_entries')]
#[Fillable(['training_run_id', 'scenario_race_id', 'status', 'placement', 'fans_gain'])]
class RaceEntry extends Model
{
    /** @use HasFactory<RaceEntryFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<TrainingRun, $this>
     */
    public function trainingRun(): BelongsTo
    {
        return $this->belongsTo(TrainingRun::class);
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
        ];
    }
}
