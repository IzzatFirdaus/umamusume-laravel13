<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RosterDistance;
use Database\Factories\RosterMemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One of the up-to-twenty characters on a Unity Cup team roster (D01).
 *
 * §1.9 of `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md`: every member carries a stat line, a grade
 * and aptitudes in the client; the trainee and the deck's characters resolve to catalogue rows while
 * the rest arrive as Guest R cards tagged `[Tracen Academy]` and do not all exist in this tool's
 * catalogue. So `umamusume_id` is nullable and `name` is the one required field — a row with no
 * identity is not a member.
 *
 * **A recorded roster, never a derived one.** The Cockpit's roster readout used to read the six
 * `deck_slots` and call the result "the derived team roster". That was a different fact: the deck is
 * six support cards the Trainer equipped, and the roster is the twenty characters who train alongside
 * the trainee. The deck remains where it was (`ADR-0014`) and the two are not merged here.
 *
 * `is_trainee` marks the row that is the trainee herself, because §1.8's ACE row names her as the Long
 * ACE and the client's `20 / 20` counts her. `is_ace` marks the one member the Team tab crowns in her
 * distance column; the client shows exactly one ACE per column, which the Form Request enforces.
 *
 * Every figure is nullable because an unrecorded member is an absence, not a zero (D-220). The
 * `saving` guard below enforces the 20-member ceiling and the roster's own rules at the model
 * boundary, since SQLite renders no SQL for a `->check()` column modifier (the same note
 * `create_support_cards_and_support_effects_tables` records).
 *
 * @property int $id
 * @property int $training_run_id
 * @property int|null $umamusume_id
 * @property int|null $support_card_id
 * @property string $name
 * @property string $origin
 * @property string|null $grade
 * @property int|null $speed
 * @property int|null $stamina
 * @property int|null $power
 * @property int|null $guts
 * @property int|null $wit
 * @property int|null $speed_cap
 * @property int|null $stamina_cap
 * @property int|null $power_cap
 * @property int|null $guts_cap
 * @property int|null $wit_cap
 * @property string|null $aptitude_turf
 * @property string|null $aptitude_dirt
 * @property string|null $aptitude_sprint
 * @property string|null $aptitude_mile
 * @property string|null $aptitude_medium
 * @property string|null $aptitude_long
 * @property string|null $aptitude_front_runner
 * @property string|null $aptitude_pace_chaser
 * @property string|null $aptitude_late_surger
 * @property string|null $aptitude_end_closer
 * @property RosterDistance|null $distance_assignment
 * @property bool $is_ace
 * @property bool $is_trainee
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read TrainingRun $trainingRun
 * @property-read Umamusume|null $umamusume
 * @property-read SupportCard|null $supportCard
 */
#[Fillable([
    'training_run_id',
    'umamusume_id',
    'support_card_id',
    'name',
    'origin',
    'grade',
    'speed',
    'stamina',
    'power',
    'guts',
    'wit',
    'speed_cap',
    'stamina_cap',
    'power_cap',
    'guts_cap',
    'wit_cap',
    'aptitude_turf',
    'aptitude_dirt',
    'aptitude_sprint',
    'aptitude_mile',
    'aptitude_medium',
    'aptitude_long',
    'aptitude_front_runner',
    'aptitude_pace_chaser',
    'aptitude_late_surger',
    'aptitude_end_closer',
    'distance_assignment',
    'is_ace',
    'is_trainee',
    'position',
])]
class RosterMember extends Model
{
    /** @use HasFactory<RosterMemberFactory> */
    use HasFactory;

    /**
     * The client's Team Info panel reads `Members: 20 / 20` (§1.8), so the ceiling is the client's own
     * and not this tool's invention.
     */
    public const MAX_MEMBERS = 20;

    /**
     * One member per distance column may be crowned ACE. §1.8 reads one ACE per column off the Team
     * tab's own layout; a second would be a shape the client does not show.
     */
    public const ACES_PER_DISTANCE = 1;

    /**
     * Where a member came from (UC3.2). §1.9 separates three kinds of teammate: the deck's six
     * characters join as themselves, the trainee is one of the twenty, and the rest arrive as Guest
     * R cards tagged `[Tracen Academy]`. `unknown` is the honest arm for a member the Trainer added
     * by name without recording where she came from — the default, because an origin nobody
     * observed is an absence (D-220), not a guess.
     */
    public const ORIGINS = ['trainee', 'support', 'guest', 'unknown'];

    /**
     * The ten observed aptitude axes (UC3.4), reusing `Umamusume::APTITUDE_AXES` keys verbatim so a
     * reader can map an observed letter onto the catalogue's committed default mechanically. The
     * two are different facts: the catalogue is what the source publishes, the observation is what
     * the client showed, and §1.2's erratum D8 is the standing example of the two disagreeing
     * without either side being wrong.
     */
    public const APTITUDE_COLUMNS = [
        'aptitude_turf', 'aptitude_dirt', 'aptitude_sprint', 'aptitude_mile', 'aptitude_medium',
        'aptitude_long', 'aptitude_front_runner', 'aptitude_pace_chaser', 'aptitude_late_surger',
        'aptitude_end_closer',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $member): void {
            $run = $member->trainingRun;

            // A new row would push the roster past the client's own ceiling. Counted against the
            // saved rows only, so editing an existing member never counts herself twice.
            if (! $member->exists && $run->rosterMembers()->count() >= self::MAX_MEMBERS) {
                throw new \RuntimeException(
                    'A Unity Cup roster holds at most '.self::MAX_MEMBERS.' members; run ['.$run->id.'] is full.',
                );
            }

            // One ACE per distance column. Checked among the other rows, so a member may be crowned
            // in her own column without colliding with herself.
            if ($member->is_ace && $member->distance_assignment !== null) {
                $clash = $run->rosterMembers()
                    ->whereKeyNot($member->getKey())
                    ->where('distance_assignment', $member->distance_assignment->value)
                    ->where('is_ace', true)
                    ->exists();

                if ($clash === true) {
                    throw new \RuntimeException(
                        'The '.$member->distance_assignment->value.' column already has its ACE; '
                        .'one per column is the shape the client shows.',
                    );
                }
            }
        });
    }

    /**
     * @return BelongsTo<TrainingRun, $this>
     */
    public function trainingRun(): BelongsTo
    {
        return $this->belongsTo(TrainingRun::class);
    }

    /**
     * @return BelongsTo<Umamusume, $this>
     */
    public function umamusume(): BelongsTo
    {
        return $this->belongsTo(Umamusume::class);
    }

    /**
     * The exact support card a support-derived member joined through (UC3.2). Character identity is
     * not card identity — Tokai Teio `[Dream Big!]` and any other Teio card are different rows — so
     * the link points at the card, never at the character name. Nullable: guests and the trainee
     * have none, and a card whose catalogue row is deleted leaves the member's recorded stat line
     * behind (`nullOnDelete`).
     *
     * @return BelongsTo<SupportCard, $this>
     */
    public function supportCard(): BelongsTo
    {
        return $this->belongsTo(SupportCard::class);
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'distance_assignment' => RosterDistance::class,
            'is_ace' => 'boolean',
            'is_trainee' => 'boolean',
        ];
    }
}
