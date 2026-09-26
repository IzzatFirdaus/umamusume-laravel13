<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CandidateStatus;
use App\Enums\MatchTier;
use Database\Factories\MatchCandidateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A fetched record the matcher could not confidently place; holds the full
 * parsed payload so promotion never re-fetches (PRD FR-B-3, US-5).
 *
 * @property int $id
 * @property string $source_key
 * @property string|null $external_ref
 * @property string $proposed_name
 * @property string|null $proposed_name_ja
 * @property string|null $proposed_match_key
 * @property int|null $suggested_umamusume_id
 * @property MatchTier $match_tier
 * @property CandidateStatus $status
 * @property array<string, mixed> $payload
 * @property Carbon $created_by_fetch_at
 * @property-read Umamusume|null $suggestedUmamusume
 */
#[Fillable(['source_key', 'external_ref', 'proposed_name', 'proposed_name_ja', 'proposed_match_key', 'suggested_umamusume_id', 'match_tier', 'status', 'payload', 'created_by_fetch_at'])]
class MatchCandidate extends Model
{
    /** @use HasFactory<MatchCandidateFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Umamusume, $this>
     */
    public function suggestedUmamusume(): BelongsTo
    {
        return $this->belongsTo(Umamusume::class, 'suggested_umamusume_id');
    }

    protected function casts(): array
    {
        return [
            'match_tier' => MatchTier::class,
            'status' => CandidateStatus::class,
            'payload' => 'array',
            'created_by_fetch_at' => 'datetime',
        ];
    }
}
