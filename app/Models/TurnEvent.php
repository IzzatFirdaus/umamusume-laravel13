<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TurnEventType;
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
