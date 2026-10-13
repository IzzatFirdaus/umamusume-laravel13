<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TraineeGoalType;
use Database\Factories\TraineeGoalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One career objective a trainee's own ladder states (PRD US-10, ADR-0026).
 *
 * The publisher's ladder, read at trainee grain: a race and the placing it asks for, or a fan
 * count and the turn it is due by. Nothing here says whether a run met it. That is derived from
 * the `race_entries` the Trainer already records, so a goal the client clears cannot drift out of
 * step with the race result, and no second owner for the same fact exists.
 *
 * @property int $id
 * @property int $umamusume_id
 * @property int|null $race_catalog_slot_id null for a fan goal, an unread goal, or a race Global has not shipped
 * @property TraineeGoalType $goal_type
 * @property int|null $required_placement the source's own figure, raw: 1, 3 and 5 are client-confirmed
 * @property int|null $fan_threshold
 * @property int $career_year 1 Junior, 2 Classic, 3 Senior, 4 the post-December final block
 * @property int $turn the deadline turn, 1..78
 * @property int $source_order
 * @property int $source_race_choice the branch index at that order, 0 when there is no branch
 * @property int $source_cond_id
 * @property int $source_cond_type
 * @property int $source_cond_value
 * @property int $source_cond_value_2
 * @property int $source_target_type
 * @property int $source_race_type
 * @property string|null $source_race_name the race string the document printed, kept only when no slot row carries it
 * @property bool $global_unreleased the source marks this race not released on Global
 * @property string $source_url
 * @property string|null $snapshot_path
 * @property Carbon|null $fetched_at
 * @property string|null $source_timezone
 * @property bool $is_manual
 * @property-read Umamusume $umamusume
 * @property-read RaceCatalogSlot|null $raceCatalogSlot
 */
#[Table('trainee_goals')]
#[Fillable([
    'umamusume_id', 'race_catalog_slot_id', 'goal_type', 'required_placement', 'fan_threshold',
    'career_year', 'turn', 'source_order', 'source_race_choice', 'source_cond_id', 'source_cond_type',
    'source_cond_value', 'source_cond_value_2', 'source_target_type', 'source_race_type',
    'source_race_name', 'global_unreleased', 'source_url', 'snapshot_path', 'fetched_at',
    'source_timezone', 'is_manual',
])]
class TraineeGoal extends Model
{
    /** @use HasFactory<TraineeGoalFactory> */
    use HasFactory;

    /**
     * The requirement as the client words it, or null when this tool cannot state it.
     *
     * A null here is not "no requirement": it is an objective whose figure the captures do not
     * cover, and the caller prints that as its own sentence rather than as a blank. The three arms
     * below are the only placings confirmed that way, and they are the single list of them: `2` and
     * `4` exist in the export too (18 rows on the Global roster) and fall through to null, because
     * "Place top 2" is a client string no capture states (D-20).
     */
    public function requirementPhrase(): ?string
    {
        return match ($this->goal_type) {
            // The forms the Rice Shower and Japanese Derby captures print. The client's "Place top 1"
            // variant for the same figure is recorded in SCREEN_SPEC §7 rather than silently chosen
            // between.
            TraineeGoalType::Placement => match ($this->required_placement) {
                1 => 'Place 1st',
                3 => 'Place top 3',
                5 => 'Place top 5',
                default => null,
            },
            TraineeGoalType::Participate => 'Participate',
            // The client's own goal banner, read off a [Global] capture: "Earn 3000 fans"
            // (screenshot-notes/01:15, repeated at :41). No thousands separator, because the banner
            // prints none. The second-hand guide phrasing "Have at least 5000 fans" describes the same
            // row and is recorded in SCREEN_SPEC §7, not used here: §5 allows the client's words.
            TraineeGoalType::Fan => 'Earn '.$this->fan_threshold.' fans',
            // The three final rounds are the scenario's obligation, not the trainee's, and the
            // Mandatory races group already renders them from race_catalog_slots.is_mandatory.
            TraineeGoalType::FinalRound, TraineeGoalType::Undecoded => null,
        };
    }

    /**
     * Whether this row states a requirement the Trainer can read.
     */
    public function isDecoded(): bool
    {
        return $this->requirementPhrase() !== null;
    }

    /**
     * The deadline turn the career counts within its year, which is the number the calendar prints.
     *
     * The source numbers turns from the start of the career (1..78); every other turn the Cockpit shows
     * is within-year, because `race_catalog_slots.turn` is. Printing the absolute figure beside a
     * mandatory row's within-year figure would read as two different clocks.
     */
    public function turnWithinYear(): int
    {
        return (($this->turn - 1) % TrainingRun::TURNS_PER_YEAR) + 1;
    }

    /**
     * This goal's place in the one ordering `RaceCatalogSlot::reachedBy()` owns.
     *
     * The calendar indexes a row as `year × 24 + within-year turn`, and the source's absolute turn is
     * `(year − 1) × 24 + within-year turn`, so the two differ by exactly one career year. Adding the
     * constant here is what lets a goal ask the same question a slot does without a second copy of the
     * arithmetic (`ADR-0015`).
     */
    public function turnIndex(): int
    {
        return $this->turn + TrainingRun::TURNS_PER_YEAR;
    }

    /**
     * The career-year word, from the calendar's own list rather than a second one.
     */
    public function yearLabel(): string
    {
        return RaceCatalogSlot::labelForYear($this->career_year);
    }

    /**
     * Whether the career position in `$next` has reached this objective's deadline.
     *
     * @param  array{year: int, turn: int}|null  $next
     */
    public function isAtOrBeforeTurn(?array $next): bool
    {
        return RaceCatalogSlot::reachedBy($this->turnIndex(), $next);
    }

    /**
     * @return BelongsTo<Umamusume, $this>
     */
    public function umamusume(): BelongsTo
    {
        return $this->belongsTo(Umamusume::class);
    }

    /**
     * @return BelongsTo<RaceCatalogSlot, $this>
     */
    public function raceCatalogSlot(): BelongsTo
    {
        return $this->belongsTo(RaceCatalogSlot::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'goal_type' => TraineeGoalType::class,
            'required_placement' => 'integer',
            'fan_threshold' => 'integer',
            'career_year' => 'integer',
            'turn' => 'integer',
            'source_order' => 'integer',
            'source_race_choice' => 'integer',
            'source_cond_id' => 'integer',
            'source_cond_type' => 'integer',
            'source_cond_value' => 'integer',
            'source_cond_value_2' => 'integer',
            'source_target_type' => 'integer',
            'source_race_type' => 'integer',
            'global_unreleased' => 'boolean',
            'is_manual' => 'boolean',
            'fetched_at' => 'datetime',
        ];
    }
}
