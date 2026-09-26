<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DataSourceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Provenance for one fact the engine wrote: URL, fetch time, snapshot,
 * confidence, source timezone (PRD FR-A-4, AGENTS.md: a fact without
 * provenance is deleted, not stored).
 *
 * @property int $id
 * @property int $umamusume_id
 * @property string $url
 * @property string $source_key
 * @property Carbon $fetched_at
 * @property string|null $snapshot_path
 * @property float|null $confidence
 * @property string|null $source_timezone
 * @property-read Umamusume $umamusume
 */
#[Fillable(['umamusume_id', 'url', 'source_key', 'fetched_at', 'snapshot_path', 'confidence', 'source_timezone'])]
class DataSource extends Model
{
    /** @use HasFactory<DataSourceFactory> */
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
            'fetched_at' => 'datetime',
            'confidence' => 'float',
        ];
    }
}
