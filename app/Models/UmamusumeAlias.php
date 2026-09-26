<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AliasLanguage;
use Database\Factories\UmamusumeAliasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Alternate surface form for one Umamusume; the Alias match tier reads this
 * table (PRD FR-A-2).
 *
 * @property int $id
 * @property int $umamusume_id
 * @property string $alias
 * @property AliasLanguage $language
 * @property-read Umamusume $umamusume
 */
#[Fillable(['umamusume_id', 'alias', 'language'])]
class UmamusumeAlias extends Model
{
    /** @use HasFactory<UmamusumeAliasFactory> */
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
            'language' => AliasLanguage::class,
        ];
    }
}
