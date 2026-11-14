<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TransferRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A Trainer's offer to transfer a Veteran Umamusume (Global notice 1157, 2026-11-05).
 *
 * Lifecycle: offered -> completed | withdrawn.
 * - offered: the Veteran is listed for transfer, reward not yet claimed
 * - completed: transfer executed, Veteran deleted, reward recorded
 * - withdrawn: offer cancelled, Veteran remains in library
 *
 * The reward is recorded as a JSON blob {star_pieces?, monies?, support_points?}
 * because no wallet/ledger exists in this build yet. A completed request keeps
 * the reward for audit; it is not paid out.
 *
 * @property int $id
 * @property int $veteran_id
 * @property 'offered'|'completed'|'withdrawn' $status
 * @property array<array-key, mixed>|null $reward {star_pieces?, monies?, support_points?}
 * @property Carbon|null $completed_at
 * @property Carbon|null $withdrawn_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Veteran $veteran
 */
#[Fillable(['veteran_id', 'status', 'reward', 'completed_at', 'withdrawn_at'])]
class TransferRequest extends Model
{
    /** @use HasFactory<TransferRequestFactory> */
    use HasFactory;

    public const STATUS_OFFERED = 'offered';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_WITHDRAWN = 'withdrawn';

    protected static function booted(): void
    {
        static::saving(function (self $request): void {
            $status = $request->status;

            if ($status === self::STATUS_COMPLETED && $request->completed_at === null) {
                $request->completed_at = Carbon::now();
            }

            if ($status === self::STATUS_WITHDRAWN && $request->withdrawn_at === null) {
                $request->withdrawn_at = Carbon::now();
            }
        });
    }

    /**
     * @return BelongsTo<Veteran, $this>
     */
    public function veteran(): BelongsTo
    {
        return $this->belongsTo(Veteran::class);
    }

    protected function casts(): array
    {
        return [
            'reward' => 'array',
            'completed_at' => 'datetime',
            'withdrawn_at' => 'datetime',
        ];
    }

    public function isOffered(): bool
    {
        return $this->status === self::STATUS_OFFERED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isWithdrawn(): bool
    {
        return $this->status === self::STATUS_WITHDRAWN;
    }
}
