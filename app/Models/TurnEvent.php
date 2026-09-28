<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TurnEventType;
use App\Models\TurnEvents\NpcFriendshipPayload;
use App\Models\TurnEvents\SpiritBurstPayload;
use Database\Factories\TurnEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One event a Trainer resolved during a run, whatever fired it (ADR-0003
 * turn_events). `deltas` records what was observed, never what the run's
 * absolute state was — turn_entries keeps that.
 *
 * @property int $id
 * @property int $training_run_id
 * @property int $turn
 * @property TurnEventType $event_type
 * @property string $source_name
 * @property int|null $choice_index
 * @property string|null $choice_label
 * @property array<string, mixed>|null $deltas
 * @property string|null $support_card_name
 * @property int|null $bond_delta
 * @property string|null $origin_note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read TrainingRun $trainingRun
 */
#[Table('turn_events')]
#[Fillable([
    'training_run_id', 'turn', 'event_type', 'source_name', 'choice_index',
    'choice_label', 'deltas', 'support_card_name', 'bond_delta', 'origin_note',
])]
class TurnEvent extends Model
{
    /** @use HasFactory<TurnEventFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<TrainingRun, $this>
     */
    public function trainingRun(): BelongsTo
    {
        return $this->belongsTo(TrainingRun::class);
    }

    /**
     * The friendship bars this event recorded, when it recorded any (D-226).
     */
    public function friendshipPayload(): ?NpcFriendshipPayload
    {
        $deltas = $this->deltas;

        if (! is_array($deltas) || ! NpcFriendshipPayload::matches($deltas)) {
            return null;
        }

        return NpcFriendshipPayload::fromArray($deltas);
    }

    /**
     * The Spirit Burst state this event recorded, when it recorded one (D-223).
     */
    public function burstPayload(): ?SpiritBurstPayload
    {
        $deltas = $this->deltas;

        if (! is_array($deltas) || ! SpiritBurstPayload::matches($deltas)) {
            return null;
        }

        return SpiritBurstPayload::fromArray($deltas);
    }

    /**
     * Validate a payload on the way in, not on the way out.
     *
     * `deltas` is a json column, so any key set can be written to it and a typo becomes
     * a row that reads as nothing. A failure event already writes its own shape
     * (`penalty_kind`, `recorded`) and is not one of the two typed payloads, so it is
     * left alone here rather than folded into a union neither class owns.
     */
    protected static function booted(): void
    {
        static::saving(function (self $event): void {
            $deltas = $event->deltas;

            if (! is_array($deltas)) {
                return;
            }

            if (NpcFriendshipPayload::matches($deltas)) {
                $event->deltas = NpcFriendshipPayload::fromArray($deltas)->toArray();

                return;
            }

            if (SpiritBurstPayload::matches($deltas)) {
                $event->deltas = SpiritBurstPayload::fromArray($deltas)->toArray();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'turn' => 'integer',
            'event_type' => TurnEventType::class,
            'choice_index' => 'integer',
            'deltas' => 'array',
            'bond_delta' => 'integer',
        ];
    }
}
