<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CardRarity;
use Database\Factories\CharacterCardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One costume card that exists on [Global] (PRD FR-A-6, ADR-0008).
 *
 * The trainee is modelled once, on `umamusume`. This row holds only what differs
 * between her forms: the client title, the rarity, and when [Global] shipped it.
 *
 * @property int $id
 * @property int $card_id the source's own card id
 * @property int $umamusume_id
 * @property string $title verbatim [Global] client string, brackets included
 * @property CardRarity $rarity
 * @property Carbon $global_release_date
 * @property bool $is_debut_form
 * @property bool $unconfirmed not confirmed by two sources; hidden by default
 * @property string $source_url
 * @property string|null $snapshot_path
 * @property Carbon|null $fetched_at
 * @property string|null $source_timezone
 * @property bool $is_manual the Trainer's own correction; the engine's stop sign (FR-B-4)
 * @property list<int>|null $skills_innate the source's own export ids, as the document lists them
 * @property list<int>|null $skills_unique one **or two** ids: 22 cards carry two uniques (KI-33)
 * @property list<int>|null $skills_awakening the ids her awakening levels grant
 * @property list<int>|null $skills_event the ids her events grant
 * @property list<array{new: int, old: int}>|null $skills_evo the evolved/base id pairs the source
 *                                                            publishes, `new` replacing `old`
 * @property-read Umamusume $umamusume
 */
#[Table('character_cards')]
#[Fillable(['card_id', 'umamusume_id', 'title', 'rarity', 'global_release_date', 'is_debut_form', 'unconfirmed', 'source_url', 'snapshot_path', 'fetched_at', 'source_timezone', 'is_manual', 'skills_innate', 'skills_unique', 'skills_awakening', 'skills_event', 'skills_evo'])]
class CharacterCard extends Model
{
    /** @use HasFactory<CharacterCardFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Umamusume, $this>
     */
    public function umamusume(): BelongsTo
    {
        return $this->belongsTo(Umamusume::class);
    }

    protected function casts(): array
    {
        return [
            'rarity' => CardRarity::class,
            'global_release_date' => 'date',
            'fetched_at' => 'datetime',
            'is_debut_form' => 'boolean',
            'unconfirmed' => 'boolean',
            'is_manual' => 'boolean',
            // A null column stays null through this cast — Eloquent does not hand back `[]` for a
            // null attribute. The pre-populate reads that as "nothing to seed" rather than letting
            // a foreach decide, and `TrainingRunTest` pins the case.
            'skills_innate' => 'array',
            'skills_unique' => 'array',
            'skills_awakening' => 'array',
            'skills_event' => 'array',
            'skills_evo' => 'array',
        ];
    }
}
