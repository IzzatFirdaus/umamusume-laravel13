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
 * @property int|null $race_catalog_slot_id
 * @property int|null $turn_entry_id the logged turn this race was run on, as the Trainer named it
 *                                   (KI-17); null when they have not tied the race to a turn
 * @property RaceEntryStatus $status
 * @property int|null $placement
 * @property int|null $fans_gain
 * @property int|null $objective_index the Grade Point period this finish counts toward,
 *                                     as the Trainer reported it (1..4, US-10); null
 *                                     when the run composes no grade objectives or the
 *                                     Trainer has not said yet
 * @property int|null $circles the client's circle estimate as the Trainer read it on a
 *                             Team Race (0..5 this slice, D-225); null on every other
 *                             slot, where the number does not exist
 * @property int|null $grade_points_earned what this finish paid in Grade Points (KI-10), priced by
 *                                         `gradePointsFor()` on a write and correctable by the
 *                                         Trainer; null means no source prices this finish, which is
 *                                         not the same claim as 0
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read TrainingRun $trainingRun
 * @property-read ScenarioRace|null $scenarioRace
 * @property-read ScenarioSlot|null $scenarioSlot
 * @property-read RaceCatalogSlot|null $raceCatalogSlot
 * @property-read TurnEntry|null $turnEntry
 */
#[Table('race_entries')]
#[Fillable(['training_run_id', 'scenario_race_id', 'scenario_slot_id', 'race_catalog_slot_id', 'turn_entry_id', 'status', 'placement', 'fans_gain', 'objective_index', 'circles', 'grade_points_earned'])]
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
     * The circle ceiling for this slice, an owner validation bound rather than a
     * published maximum: the corpus names 3 circles as a safety margin
     * (`team_race.circles_guidance`) and never says how many a full display holds.
     */
    public const MAX_CIRCLES = 5;

    /**
     * `objective_index` is entered, never inferred (D-270), and it is only ever
     * meaningful on a run whose scenario composes grade objectives. A column CHECK
     * could bound the number but cannot see the run's scenario, so the rule lives
     * here, the way `ScenarioSlot` carries its own kind and month checks.
     *
     * `circles` is entered too, and belongs to a Team Race only: URA and Trackblazer
     * have no team race, so a circle count on one of their slots would be a Unity Cup
     * fact pasted onto a scenario that cannot have it (D-221, D-220 for absence).
     */
    protected static function booted(): void
    {
        static::saving(function (self $entry): void {
            if ($entry->objective_index !== null) {
                TrainingRun::assertGradePeriod($entry->objective_index, $entry->trainingRun, 'objective_index');
            }

            $entry->priceGradePoints();

            $circles = $entry->circles;

            if ($circles === null) {
                return;
            }

            if ($circles < 0 || $circles > self::MAX_CIRCLES) {
                throw new \InvalidArgumentException(
                    "circles [{$circles}] is outside the 0..".self::MAX_CIRCLES.' range this tool accepts.',
                );
            }

            if ($entry->scenarioSlot?->kind !== 'team_race') {
                throw new \InvalidArgumentException(
                    'Circles can only be entered against a team race slot.',
                );
            }
        });
    }

    /**
     * The tier this entry's price is read against: the career calendar first, the scenario slot
     * second.
     *
     * The order is a ruling, not an accident of which relation happens to be loaded.
     * `race_catalog_slots` publishes a tier per race in a dated year, which is where a tier is a
     * per-race claim (R72); `scenario_slots` carries the seeded URA schedule and the free races a
     * Trainer typed in, which have no catalogue row to point at.
     */
    public function tierKey(): ?string
    {
        // Spelled out rather than chained, because both links are optional: an entry with no
        // catalogue row must fall through to the scenario slot instead of reading off null.
        $catalogTier = $this->raceCatalogSlot?->tier;

        if ($catalogTier !== null) {
            return $catalogTier;
        }

        return $this->scenarioSlot?->tier;
    }

    /**
     * Price this finish, unless the write already answered the question.
     *
     * Entered first, derived second. The guard skips when the caller set the figure itself, because
     * that is the Trainer correcting the tool with what the client actually paid, and it skips a
     * stored figure on an unrelated edit so changing the turn link does not re-price a race whose
     * tier label has since moved. A placement change is the one edit that does re-price: the
     * finish is what the price is a function of.
     */
    private function priceGradePoints(): void
    {
        if ($this->isDirty('grade_points_earned')) {
            return;
        }

        if ($this->grade_points_earned !== null && ! $this->isDirty('placement')) {
            return;
        }

        $this->grade_points_earned = self::gradePointsFor(
            $this->trainingRun,
            $this->tierKey(),
            $this->placement,
        );
    }

    /**
     * The Grade Points a finish is worth, or null when nothing prices it.
     *
     * `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Grade Points and Shop Coins - Exact Values"
     * prices a 1st place and says only that lower placements "scale down proportionally", with no
     * ratio named: that gap is KI-10's open half, so a 2nd place is unpriced rather than guessed at.
     * The same page's 100/60/30/0 placement table is Shop Coins, which it says explicitly "do not
     * depend on race grade at all" — borrowing those ratios would put a wrong number under a real
     * citation. A race with no tier is unpriced for the same reason, which since R75 is most races.
     *
     * @return int|null null means "this tool cannot price this finish", never "this finish scored 0"
     */
    public static function gradePointsFor(?TrainingRun $run, ?string $tier, ?int $placement): ?int
    {
        if ($run === null || $placement !== 1 || $tier === null) {
            return null;
        }

        return $run->gradePointsForTier($tier);
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
     * The slot in the shared career calendar this entry was run against.
     *
     * Null for a Trainer-typed free race, which has no catalogue row, and for a
     * team race round, which belongs to the scenario rather than to the calendar.
     *
     * @return BelongsTo<RaceCatalogSlot, $this>
     */
    public function raceCatalogSlot(): BelongsTo
    {
        return $this->belongsTo(RaceCatalogSlot::class, 'race_catalog_slot_id');
    }

    /**
     * The logged turn this race was run on, as the Trainer named it (KI-17).
     *
     * Null on a race the Trainer has not tied to a turn, which is a complete row: the gap KI-17
     * filed was the absence of the link, not the presence of unlinked entries. Nothing derives a
     * turn from a race date here or anywhere else (D-270).
     *
     * @return BelongsTo<TurnEntry, $this>
     */
    public function turnEntry(): BelongsTo
    {
        return $this->belongsTo(TurnEntry::class, 'turn_entry_id');
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
            'circles' => 'integer',
            'turn_entry_id' => 'integer',
        ];
    }

    /**
     * The finish as a Trainer reads it: 1st, 2nd, 3rd. R69.
     *
     * The teens carry the rule, not the last digit: 11/12/13 take `th` while the same final
     * digits take `st/nd/rd` two numbers later, which is why the modulo-10 branch is checked
     * against 11-13 first rather than folded into a suffix lookup.
     */
    public function placementOrdinal(): string
    {
        if ($this->placement === null) {
            return 'no placement';
        }

        if (in_array($this->placement % 100, [11, 12, 13], true)) {
            return $this->placement.'th';
        }

        return $this->placement.match ($this->placement % 10) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };
    }
}
