<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ScenarioSlotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ScenarioSlot — generalised timeline slot for any scenario.
 *
 * Replaces the URA-specific `scenario_races` table. The `kind` discriminator
 * carries the slot type so the timeline composes from data (D-221, D-240).
 *
 * Kinds:
 *   goal_race       Mandatory or optional races with fan/maiden gates (URA, Unity Cup)
 *   team_race       Unity Cup Team Races (every 6 months, opponent selection)
 *   grade_deadline  Trackblazer Grade Point deadlines (end of each year)
 *   scripted_event  Scenario-specific events (URA April Unique Skill check)
 */
class ScenarioSlot extends Model
{
    /** @use HasFactory<ScenarioSlotFactory> */
    use HasFactory;

    protected $table = 'scenario_slots';

    protected $fillable = [
        'scenario_key',
        'kind',
        'slot_label',
        'title',
        'description',
        'month',
        'half',
        'tier',
        'fans_needed',
        'is_mandatory',
        'is_maiden_gated',
        'sort_order',
        'source_url',
        'snapshot_path',
        'fetched_at',
        'source_timezone',
        'is_manual',
    ];

    protected $casts = [
        'month' => 'integer',
        'half' => 'string',
        'fans_needed' => 'integer',
        'is_mandatory' => 'boolean',
        'is_maiden_gated' => 'boolean',
        'sort_order' => 'integer',
        'is_manual' => 'boolean',
        'fetched_at' => 'datetime',
    ];

    // Valid kinds — enforced at model layer (DB has no CHECK on SQLite)
    public const array VALID_KINDS = [
        'goal_race',
        'team_race',
        'grade_deadline',
        'scripted_event',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $slot): void {
            if (! in_array($slot->kind, self::VALID_KINDS, true)) {
                throw new \InvalidArgumentException("Invalid kind [{$slot->kind}] for ScenarioSlot. Valid: ".implode(', ', self::VALID_KINDS));
            }

            if ($slot->month < 1 || $slot->month > 12) {
                throw new \InvalidArgumentException('month must be 1-12');
            }

            if (! in_array($slot->half, ['Early', 'Late'], true)) {
                throw new \InvalidArgumentException("Invalid half [{$slot->half}]. Must be 'Early' or 'Late'");
            }
        });
    }

    /**
     * @return HasMany<RaceEntry, $this>
     */
    public function raceEntries(): HasMany
    {
        return $this->hasMany(RaceEntry::class, 'scenario_slot_id');
    }

    // Type predicates — used by components instead of string comparison
    public function isGoalRace(): bool
    {
        return $this->kind === 'goal_race';
    }

    public function isTeamRace(): bool
    {
        return $this->kind === 'team_race';
    }

    public function isGradeDeadline(): bool
    {
        return $this->kind === 'grade_deadline';
    }

    public function isScriptedEvent(): bool
    {
        return $this->kind === 'scripted_event';
    }

    // Convenience: does this slot have a fan gate?
    public function hasFanGate(): bool
    {
        return $this->isGoalRace() && $this->fans_needed !== null && $this->fans_needed > 0;
    }

    // Convenience: does this slot have a maiden gate?
    public function hasMaidenGate(): bool
    {
        return $this->isGoalRace() && $this->is_maiden_gated;
    }

    // Convenience: is this a mandatory goal race?
    public function isMandatoryGoal(): bool
    {
        return $this->isGoalRace() && $this->is_mandatory;
    }
}
