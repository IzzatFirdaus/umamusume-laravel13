<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SkillLevelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * The level a skill reached in one run (ADR-0029 D4).
 *
 * Run × skill → level integer, the level and nothing else. Acquisition status and the turn acquired
 * stay on `run_skills`; this row is the level alone, so a skill marked Acquired and one re-marked
 * Suggested never disagree about which table owns what.
 *
 * @property int $id
 * @property int $training_run_id
 * @property int $skill_id
 * @property int $current_level
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read TrainingRun $trainingRun
 * @property-read Skill $skill
 */
#[Fillable(['training_run_id', 'skill_id', 'current_level'])]
class SkillLevel extends Model
{
    /** @use HasFactory<SkillLevelFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['current_level' => 'integer'];
    }

    /**
     * @return BelongsTo<TrainingRun, $this>
     */
    public function trainingRun(): BelongsTo
    {
        return $this->belongsTo(TrainingRun::class);
    }

    /**
     * @return BelongsTo<Skill, $this>
     */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
