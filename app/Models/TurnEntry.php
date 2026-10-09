<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EnergyState;
use App\Enums\MoodTier;
use Database\Factories\TurnEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One Trainer-entered turn of stats; the sole input to all run math (PRD
 * FR-C-2, CLAUDE.md planner rule: deterministic over entered turns).
 *
 * Energy, mood and fans are end-of-turn totals, not deltas, matching the five
 * stat columns (ADR-0003). All three are nullable because runs logged before
 * they existed must not be backfilled with guesses.
 *
 * @property int $id
 * @property int $training_run_id
 * @property int $turn
 * @property int $speed
 * @property int $stamina
 * @property int $power
 * @property int $guts
 * @property int $wit
 * @property int|null $sp
 * @property string|null $condition
 * @property int|null $energy
 * @property EnergyState|null $energy_state
 * @property string|null $energy_band
 * @property MoodTier|null $mood
 * @property int|null $fans
 * @property-read TrainingRun $trainingRun
 */
#[Fillable(['training_run_id', 'turn', 'speed', 'stamina', 'power', 'guts', 'wit', 'sp', 'condition', 'energy', 'energy_state', 'energy_band', 'mood', 'fans', 'facility_speed', 'facility_stamina', 'facility_power', 'facility_guts', 'facility_wit'])]
class TurnEntry extends Model
{
    /** @use HasFactory<TurnEntryFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<TrainingRun, $this>
     */
    public function trainingRun(): BelongsTo
    {
        return $this->belongsTo(TrainingRun::class);
    }

    protected function casts(): array
    {
        return [
            'turn' => 'integer',
            'speed' => 'integer',
            'stamina' => 'integer',
            'power' => 'integer',
            'guts' => 'integer',
            'wit' => 'integer',
            'sp' => 'integer',
            'energy' => 'integer',
            'energy_state' => EnergyState::class,
            'mood' => MoodTier::class,
            'fans' => 'integer',
        ];
    }
}
