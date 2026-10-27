<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\IndependentTrainingPolicy;
use Database\Factories\IndependentTrainingRunFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One Independent Training session — the idle mode §1.1.7 documents.
 *
 * A row is the 50-minute window the Trainer commits and walks away from, plus the three inputs fixed
 * at start (policy, rotation, priority skills) and the two ways it ends. The underlying `TrainingRun`
 * carries the trainee, deck and scenario; this row carries only what is mode-specific, so a normal
 * career and an idle session share one run shape and this table is the idle overlay.
 *
 * @property int $id
 * @property int $training_run_id
 * @property IndependentTrainingPolicy $policy
 * @property list<int>|null $priority_skills skill ids, capped at 10
 * @property mixed|null $rotation null means the scenario's target races only
 * @property Carbon $started_at
 * @property Carbon $completes_at started_at + 50 minutes
 * @property Carbon|null $completed_at set when the scheduler finishes the run
 * @property Carbon|null $abandoned_at set when the Trainer gives up
 * @property array<string, mixed>|null $result the simulator's completion payload, written on complete
 * @property-read TrainingRun $trainingRun
 */
class IndependentTrainingRun extends Model
{
    /** @use HasFactory<IndependentTrainingRunFactory> */
    use HasFactory;

    protected $table = 'independent_training_runs';

    protected $fillable = [
        'training_run_id',
        'policy',
        'priority_skills',
        'rotation',
        'started_at',
        'completes_at',
        'completed_at',
        'abandoned_at',
        'result',
    ];

    protected function casts(): array
    {
        return [
            'policy' => IndependentTrainingPolicy::class,
            'priority_skills' => 'array',
            'rotation' => 'array',
            'result' => 'array',
            'started_at' => 'datetime',
            'completes_at' => 'datetime',
            'completed_at' => 'datetime',
            'abandoned_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<TrainingRun, $this>
     */
    public function trainingRun(): BelongsTo
    {
        return $this->belongsTo(TrainingRun::class);
    }

    /**
     * The 50-minute ceiling §1.1.7 fixes: the run plays out over a fixed 50 minutes.
     */
    public const DURATION_MINUTES = 50;

    /**
     * §1.1.7: up to ten priority skill hints steer which event skills the run takes.
     */
    public const MAX_PRIORITY_SKILLS = 10;

    /**
     * In progress while the window is open and neither ending has been written.
     */
    public function isInProgress(): bool
    {
        return $this->completed_at === null
            && $this->abandoned_at === null
            && $this->completes_at->isFuture();
    }
}
