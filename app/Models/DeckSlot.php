<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DeckSlotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeckSlot extends Model
{
    /** @use HasFactory<DeckSlotFactory> */
    use HasFactory;

    protected $fillable = [
        'training_run_id',
        'support_card_id',
        'slot_position',
    ];

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

    public function isFriendSlot(): bool
    {
        return $this->slot_position === 6;
    }
}
