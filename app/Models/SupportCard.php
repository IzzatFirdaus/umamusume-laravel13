<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SupportCardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportCard extends Model
{
    /** @use HasFactory<SupportCardFactory> */
    use HasFactory;

    protected $fillable = [
        'support_id',
        'char_id',
        'name',
        'name_ja',
        'title_en',
        'title_ja',
        'rarity',
        'type',
        'release_jp',
        'release_global',
        'effects',
        'source_url',
        'fetched_at',
        'is_manual',
    ];

    protected function casts(): array
    {
        return [
            'support_id' => 'integer',
            'rarity' => 'integer',
            'release_jp' => 'date',
            'release_global' => 'date',
            'effects' => 'array',
            'fetched_at' => 'datetime',
            'is_manual' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Umamusume, $this>
     */
    public function umamusume(): BelongsTo
    {
        return $this->belongsTo(Umamusume::class, 'char_id');
    }

    /**
     * @return HasMany<DeckSlot, $this>
     */
    public function deckSlots(): HasMany
    {
        return $this->hasMany(DeckSlot::class);
    }

    public function isFriendType(): bool
    {
        return $this->type === 'friend';
    }

    public function isGroupType(): bool
    {
        return $this->type === 'group';
    }

    public function rarityLabel(): string
    {
        return match ($this->rarity) {
            1 => 'R',
            2 => 'SR',
            3 => 'SSR',
            default => (string) $this->rarity,
        };
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'speed' => 'Speed',
            'stamina' => 'Stamina',
            'power' => 'Power',
            'guts' => 'Guts',
            'intelligence' => 'Wit',
            'friend' => 'Pal',
            'group' => 'Group',
            default => ucfirst($this->type),
        };
    }
}
