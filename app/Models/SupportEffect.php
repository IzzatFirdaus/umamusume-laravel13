<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SupportEffectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One support effect as the client defines it (ADR-0014).
 *
 * A dictionary row, not a user row: `effects` on `SupportCard` stores `[effect_id, v1 … v11]` anchors
 * and this table names them, so the UI can say "Friendship Bonus" without hardcoding a label per id.
 *
 * `calc` is present on only 4 of the 35 records. That absence is the fact: UMAMUSUME_REFERENCE.md
 * §1.4.8 records that exactly the effects which declare `calc` combine multiplicatively, so null here
 * means "a flat amount or percentage" and must not be filled in with the word `flat`.
 *
 * @property int $id
 * @property int $effect_id the source's own effect id, the join target for `SupportCard::$effects`
 * @property string $name_en
 * @property string|null $name_ja
 * @property string|null $calc `mult` or `add`, or null on the 31 records that declare neither
 * @property string|null $symbol `percent`, `none` or `level` — the source's word, not a glyph
 * @property string|null $description_en
 * @property string $source_url
 * @property Carbon $fetched_at
 */
#[Fillable(['effect_id', 'name_en', 'name_ja', 'calc', 'symbol', 'description_en', 'source_url', 'fetched_at'])]
class SupportEffect extends Model
{
    /** @use HasFactory<SupportEffectFactory> */
    use HasFactory;

    public $timestamps = false;

    /** The export's `calc` vocabulary. `flat` is deliberately absent: the source never sends it. */
    public const CALC_MODES = ['mult', 'add'];

    /** The export's `symbol` vocabulary, which is a word rather than a glyph. */
    public const SYMBOLS = ['percent', 'none', 'level'];

    protected function casts(): array
    {
        return [
            'effect_id' => 'integer',
            'fetched_at' => 'datetime',
        ];
    }
}
