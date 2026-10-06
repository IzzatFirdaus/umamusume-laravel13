<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DeckSlotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * One of the six cards a Trainer equipped for a run (ADR-0014).
 *
 * Position 6 is the friend slot. The role belongs to the slot, never to the card: the deck editor
 * labels the sixth position "Friends" and any card may occupy it, so a stat card parked there is a
 * legal deck (UMAMUSUME_REFERENCE.md §1.4.5, ADR-0005 correction 1, carried into ADR-0014).
 *
 * @property int $id
 * @property int $training_run_id
 * @property int $support_card_id
 * @property int $slot_position one to six: guarded on saving and by the column's CHECK
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read TrainingRun $trainingRun
 * @property-read SupportCard $supportCard
 */
#[Fillable(['training_run_id', 'support_card_id', 'slot_position'])]
class DeckSlot extends Model
{
    /** @use HasFactory<DeckSlotFactory> */
    use HasFactory;

    public const MIN_POSITION = 1;

    public const MAX_POSITION = 6;

    /**
     * The one to six positions a deck has, as a list rather than a range call, because the Form
     * Request rule, this guard and the picker's loop all read it and must not each spell it out.
     *
     * @var list<int>
     */
    public const POSITIONS = [self::MIN_POSITION, self::MIN_POSITION + 1, self::MIN_POSITION + 2, self::MIN_POSITION + 3, self::MIN_POSITION + 4, self::MAX_POSITION];

    /**
     * The two ways a Trainer can hold the card in a slot.
     *
     * `deck_slots` has no column for this and adding one is the owner's call (`ADR-0014`), which is why
     * the run-scoped deck builder prints the flag it can read and says the rest out loud. The setup
     * wizard's draft does carry it, because a session key needs no migration, so this is the one place
     * the pair of words is written down: the rule that refuses a value outside it, the six slot rows the
     * page renders and `components/support/SupportSlot.vue`'s two-button group all read it from here.
     *
     * @var list<string>
     */
    public const OWNERSHIP = ['OWNED', 'RENTED'];

    protected function casts(): array
    {
        return [
            'slot_position' => 'integer',
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
     * @return BelongsTo<SupportCard, $this>
     */
    public function supportCard(): BelongsTo
    {
        return $this->belongsTo(SupportCard::class);
    }

    /**
     * Reject a position outside the deck instead of trusting the column's CHECK.
     *
     * The migration declares `slot_position BETWEEN 1 AND 6`, and that declaration is kept as
     * documentation and as enforcement on any engine that honours it. It is not the gate here:
     * SQLite accepts an out-of-range value into the column without complaint in this build, so a
     * constraint whose only enforcement is the DDL would let a future writer place a card at position
     * 7 and pass every test in the suite. This hook is the layer that actually holds, and it holds for
     * every path into the table, including factories, which bypass `#[Fillable]` entirely.
     *
     * A mis-aimed position is a programming error rather than user input, so it raises
     * `InvalidArgumentException` rather than a validation exception: the Form Request is what answers a
     * Trainer, and this is what answers a bug.
     */
    protected static function booted(): void
    {
        static::saving(function (self $slot): void {
            if (! in_array($slot->slot_position, self::POSITIONS, true)) {
                throw new InvalidArgumentException(
                    "Deck slot position {$slot->slot_position} is outside the one to six a run has."
                );
            }
        });
    }

    public function isFriendSlot(): bool
    {
        return $this->slot_position === self::MAX_POSITION;
    }
}
