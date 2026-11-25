<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PreseasonRoundFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One Unity Cup preseason round's recorded outcome (D-10).
 *
 * The client's Career Profile → Unity Cup panel lists Round 1..4, each with whether
 * it was held, its scheduled date and its result. This row is one of those rounds:
 * the round identity (`round` 1..4), the recorded `outcome` ('WIN' / 'LOSS'), or a
 * null outcome for a round the Trainer has not recorded — which is an explicit
 * unknown, never a fabricated loss (D-220).
 *
 * The scheduled date is NOT stored here: it is a scenario-level calendar fact and
 * lives on the `team_race` scenario slot (`ScenarioSlot`, D-8). Storing it per run
 * would let a run's round drift from the scenario's own calendar.
 *
 * The legacy `team_preseason_wins` tally on the run is independent of these rows and
 * is neither derived from them nor recomputed from them.
 *
 * @property int $id
 * @property int $training_run_id
 * @property int $round
 * @property string|null $outcome
 */
class PreseasonRound extends Model
{
    /** @use HasFactory<PreseasonRoundFactory> */
    use HasFactory;

    protected $table = 'preseason_rounds';

    protected $fillable = ['training_run_id', 'round', 'outcome'];

    protected $casts = ['round' => 'integer'];

    /** The four preseason rounds, in the order the client lists them. */
    public const ROUNDS = [1, 2, 3, 4];

    /** The two recorded outcomes. Null is "not recorded", not a third outcome. */
    public const OUTCOMES = ['WIN', 'LOSS'];

    protected static function booted(): void
    {
        static::saving(function (self $round): void {
            if (! in_array($round->round, self::ROUNDS, true)) {
                throw new \InvalidArgumentException(
                    "Preseason round [{$round->round}] is not one of 1..4.",
                );
            }

            if ($round->outcome !== null && ! in_array($round->outcome, self::OUTCOMES, true)) {
                throw new \InvalidArgumentException(
                    "Preseason outcome [{$round->outcome}] is not one of WIN/LOSS.",
                );
            }
        });
    }

    /** @return BelongsTo<TrainingRun, $this> */
    public function trainingRun(): BelongsTo
    {
        return $this->belongsTo(TrainingRun::class);
    }
}
