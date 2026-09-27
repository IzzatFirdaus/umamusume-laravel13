<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RunStatus;
use App\Enums\SkillAcquisition;
use Database\Factories\TrainingRunFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * One training run for one Umamusume; owns its turns and skill states, both
 * cascade-deleted with it (PRD FR-C-1, C-4).
 *
 * @property int $id
 * @property int $umamusume_id
 * @property string|null $scenario
 * @property RunStatus $status
 * @property int|null $inheritance_parent_a_id
 * @property int|null $inheritance_parent_b_id
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, TurnEntry> $turnEntries
 * @property-read Collection<int, TurnEvent> $turnEvents
 * @property-read Collection<int, RaceEntry> $raceEntries
 * @property-read Collection<int, Skill> $skills
 * @property-read Umamusume $umamusume
 */
#[Fillable(['umamusume_id', 'scenario', 'status', 'inheritance_parent_a_id', 'inheritance_parent_b_id', 'notes'])]
class TrainingRun extends Model
{
    /** @use HasFactory<TrainingRunFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Umamusume, $this>
     */
    public function umamusume(): BelongsTo
    {
        return $this->belongsTo(Umamusume::class);
    }

    /**
     * @return BelongsTo<Umamusume, $this>
     */
    public function inheritanceParentA(): BelongsTo
    {
        return $this->belongsTo(Umamusume::class, 'inheritance_parent_a_id');
    }

    /**
     * @return BelongsTo<Umamusume, $this>
     */
    public function inheritanceParentB(): BelongsTo
    {
        return $this->belongsTo(Umamusume::class, 'inheritance_parent_b_id');
    }

    /**
     * @return HasMany<TurnEntry, $this>
     */
    public function turnEntries(): HasMany
    {
        return $this->hasMany(TurnEntry::class)->orderBy('turn');
    }

    /**
     * @return HasMany<TurnEvent, $this>
     */
    public function turnEvents(): HasMany
    {
        return $this->hasMany(TurnEvent::class)->orderBy('turn');
    }

    /**
     * @return HasMany<RaceEntry, $this>
     */
    public function raceEntries(): HasMany
    {
        return $this->hasMany(RaceEntry::class);
    }

    /**
     * @return BelongsToMany<Skill, $this, RunSkill, 'pivot'>
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'run_skills')
            ->withPivot(['status', 'turn_acquired'])
            ->using(RunSkill::class);
    }

    public function setSkillStatus(Skill $skill, SkillAcquisition $status, ?int $turnAcquired = null): void
    {
        $this->skills()->syncWithoutDetaching([
            $skill->id => [
                'status' => $status->value,
                'turn_acquired' => $turnAcquired,
            ],
        ]);
    }

    protected function casts(): array
    {
        return [
            'status' => RunStatus::class,
        ];
    }
}
