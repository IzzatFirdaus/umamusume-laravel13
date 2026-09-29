<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UmamusumeProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * The trainee profile block: Japanese name, both voice actors, birthday, height and
 * three sizes, as the `characters` document states them.
 *
 * One row per trainee, so this is the one table in the catalog that hangs off `umamusume` at
 * 1:1 rather than 1:N. It carries its own provenance because `umamusume` carries none, which is
 * the whole reason it is a sibling table rather than six columns (see the migration).
 *
 * **Nothing here is ever rendered as a claim about the game.** Every field is a source statement
 * with a null where the source is silent, and the view's job is to say so in words rather than to
 * draw a blank (D-220). The one place a null would be most tempting to paper over is the birthday:
 * `birth_year` is the only part this document ever omits, and a missing year must read as a
 * missing year, not as a January 1st.
 *
 * @property int $id
 * @property int $umamusume_id
 * @property string|null $name_ja
 * @property string|null $va_ja the romanised Japanese voice actor; the field the character page shows
 * @property string|null $va_en the English dub voice actor; null on 10 of the 135 roster rows
 * @property int|null $birth_year
 * @property int|null $birth_month
 * @property int|null $birth_day
 * @property int|null $height centimetres
 * @property int|null $three_sizes_b
 * @property int|null $three_sizes_h
 * @property int|null $three_sizes_w
 * @property string $source_url
 * @property string|null $snapshot_path
 * @property Carbon|null $fetched_at
 * @property string|null $source_timezone
 * @property bool $is_manual
 * @property-read Umamusume $umamusume
 */
#[Table('umamusume_profiles')]
#[Fillable([
    'umamusume_id',
    'name_ja',
    'va_ja',
    'va_en',
    'birth_year',
    'birth_month',
    'birth_day',
    'height',
    'three_sizes_b',
    'three_sizes_h',
    'three_sizes_w',
    'source_url',
    'snapshot_path',
    'fetched_at',
    'source_timezone',
    'is_manual',
])]
class UmamusumeProfile extends Model
{
    /** @use HasFactory<UmamusumeProfileFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Umamusume, $this>
     */
    public function umamusume(): BelongsTo
    {
        return $this->belongsTo(Umamusume::class);
    }

    /**
     * The birthday, or null when the source is silent about any part of it.
     *
     * Returning a `Carbon` here would have been tidier and wrong twice over: it would invent a
     * year for the rows whose `birth_year` is null, and it would put a date arithmetic path on a
     * field that has no timezone and is never used as a date. The view formats the three parts
     * itself and can therefore say "no year published" rather than print a date it made up.
     */
    public function hasFullBirthday(): bool
    {
        return $this->birth_year !== null && $this->birth_month !== null && $this->birth_day !== null;
    }

    /**
     * True when the document carried a `three_sizes` object at all.
     *
     * Checked as "all three parts" rather than "b is set" because the object is one field: a
     * partially present one would be a source defect this parser has never seen, and treating it
     * as present would render a partial measurement as a whole one.
     */
    public function hasThreeSizes(): bool
    {
        return $this->three_sizes_b !== null && $this->three_sizes_h !== null && $this->three_sizes_w !== null;
    }

    protected function casts(): array
    {
        return [
            'birth_year' => 'integer',
            'birth_month' => 'integer',
            'birth_day' => 'integer',
            'height' => 'integer',
            'three_sizes_b' => 'integer',
            'three_sizes_h' => 'integer',
            'three_sizes_w' => 'integer',
            'is_manual' => 'boolean',
            'fetched_at' => 'datetime',
        ];
    }
}
