<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\RaceCatalogSlotFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * RaceCatalogSlot — one race at one turn of the career calendar.
 *
 * The catalogue is the shared [Global] career schedule: 410 rows, one per race per
 * turn, corroborated against client captures in
 * docs/scenarios/09-global-race-calendar.md. It is deliberately not
 * `scenario_slots`: that table holds scenario-authored objects (a Unity Cup team
 * race round, a Trackblazer deadline), whose grain is one-per-turn and which carry
 * no distance, surface or fan gate.
 *
 * `scenario_key` null means every scenario. Only the four scenario finals set it.
 */
class RaceCatalogSlot extends Model
{
    /** @use HasFactory<RaceCatalogSlotFactory> */
    use HasFactory;

    public const YEAR_JUNIOR = 1;

    public const YEAR_CLASSIC = 2;

    public const YEAR_SENIOR = 3;

    /** The post-December final block, which sits outside the 24-turn year grid. */
    public const YEAR_FINALE = 4;

    public const array YEARS = [
        self::YEAR_JUNIOR => 'Junior',
        self::YEAR_CLASSIC => 'Classic',
        self::YEAR_SENIOR => 'Senior',
        self::YEAR_FINALE => 'Finale',
    ];

    protected $table = 'race_catalog_slots';

    protected $fillable = [
        'scenario_key',
        'year',
        'month',
        'half',
        'turn',
        'slot_label',
        'title',
        'tier',
        'grade_code',
        'distance',
        'distance_band',
        'surface',
        'track_id',
        'race_id',
        'fans_needed',
        'fans_gain_curve',
        'fans_first',
        'fans_second',
        'fans_third',
        'is_mandatory',
        'is_maiden_gated',
        'is_special_race',
        'did_not_exist',
        'external_ref',
        'sort_order',
        'source_url',
        'snapshot_path',
        'fetched_at',
        'source_timezone',
        'is_manual',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'turn' => 'integer',
        'grade_code' => 'integer',
        'distance' => 'integer',
        'track_id' => 'integer',
        'race_id' => 'integer',
        'fans_needed' => 'integer',
        'fans_gain_curve' => 'integer',
        'fans_first' => 'integer',
        'fans_second' => 'integer',
        'fans_third' => 'integer',
        'sort_order' => 'integer',
        'is_mandatory' => 'boolean',
        'is_maiden_gated' => 'boolean',
        'is_special_race' => 'boolean',
        'is_manual' => 'boolean',
        'fetched_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $slot): void {
            if (! array_key_exists($slot->year, self::YEARS)) {
                throw new \InvalidArgumentException("Unknown career year [{$slot->year}]");
            }

            // The finale block has no month by design; a dated row must be dated
            // properly rather than parked on month zero.
            if ($slot->year !== self::YEAR_FINALE && ($slot->month === null || $slot->month < 1 || $slot->month > 12)) {
                throw new \InvalidArgumentException('month must be 1-12 outside the finale block');
            }

            if ($slot->title === '') {
                throw new \InvalidArgumentException('a catalogue row needs the client race name');
            }
        });
    }

    /**
     * @return HasMany<RaceEntry, $this>
     */
    public function raceEntries(): HasMany
    {
        return $this->hasMany(RaceEntry::class, 'race_catalog_slot_id');
    }

    /**
     * Everything the given scenario can actually run: its own shared rows plus the
     * rows that belong to no scenario at all.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForScenario(Builder $query, string $scenarioKey): Builder
    {
        return $query->where(function (Builder $q) use ($scenarioKey): void {
            $q->whereNull('scenario_key')->orWhere('scenario_key', $scenarioKey);
        });
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeInYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOnTurn(Builder $query, int $year, int $turn): Builder
    {
        return $query->where('year', $year)->where('turn', $turn);
    }

    /**
     * The turn inside a career year, or null for the finale block.
     */
    public function turnNumber(): ?int
    {
        return $this->turn;
    }

    /**
     * Whether the career position in `$next` has reached this row.
     *
     * One integer orders the two, because a career year is a fixed number of turns: year times the
     * year length plus the turn is monotonic across the year boundary, which turn 24 of Junior rolling
     * into turn 1 of Classic is the reason for. The finale block has no turn, so it reads as the first
     * instant of the fourth year rather than as no position at all.
     *
     * `null` means the caller has no position to compare from, which is not the same claim as "the
     * calendar has reached this row"; it answers false and leaves the screen to say why.
     *
     * @param  array{year: int, turn: int}|null  $next
     */
    public function isAtOrBeforeTurn(?array $next): bool
    {
        if ($next === null) {
            return false;
        }

        $at = (int) $this->year * TrainingRun::TURNS_PER_YEAR + (int) ($this->turn ?? 0);
        $now = (int) $next['year'] * TrainingRun::TURNS_PER_YEAR + (int) $next['turn'];

        return $at <= $now;
    }

    public function yearLabel(): string
    {
        return self::YEARS[$this->year] ?? (string) $this->year;
    }

    public function hasFanGate(): bool
    {
        return $this->fans_needed !== null && $this->fans_needed > 0;
    }

    /**
     * The maiden lock: a race this trainee cannot enter until she has won
     * something. No fan figure travels with it, because it clears on an event
     * rather than on a quantity.
     */
    public function hasMaidenGate(): bool
    {
        return $this->is_maiden_gated;
    }

    /**
     * Highest first-place payout curve `[Global]` publishes a row for.
     *
     * `en/race-fans` holds curves 1-50; the `[JP]` table runs to 61. Nine career
     * slots ask for curves 51-56, so their payout is unknown on `[Global]` even
     * though the curve id itself is present in the export — see
     * docs/scenarios/09-global-race-calendar.md, "[Global] versus [JP]" finding 3.
     */
    public const MAX_RESOLVED_GLOBAL_PAYOUT_CURVE = 50;

    /**
     * Whether this row's first-place fan payout can be stated.
     *
     * Callers must render null — "not known" — and never 0, which would read as
     * "this race earns nothing". The distinction is why this is a method and not
     * a `fans_gain_curve !== null` check at each call site.
     */
    public function hasKnownFanGain(): bool
    {
        return $this->fans_gain_curve !== null
            && $this->fans_gain_curve <= self::MAX_RESOLVED_GLOBAL_PAYOUT_CURVE;
    }

    /**
     * The per-placement fan payout, formatted, or null when the row does not carry all three.
     *
     * One owner because two surfaces print it: the Race Decision card's field list and the Race
     * Planner's Fan gain row (`RaceFacts` and `RacePlannerController`). A partial set is an absence
     * rather than a half-answer, and a null must never render as 0.
     */
    public function fanPayout(): ?string
    {
        if ($this->fans_first === null || $this->fans_second === null || $this->fans_third === null) {
            return null;
        }

        return sprintf(
            '1st: %s / 2nd: %s / 3rd: %s',
            number_format($this->fans_first),
            number_format($this->fans_second),
            number_format($this->fans_third),
        );
    }

    public function distanceLabel(): string
    {
        return $this->distance === null ? 'Decided by your most-run types' : number_format($this->distance).' m';
    }
}
