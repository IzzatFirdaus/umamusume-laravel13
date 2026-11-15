<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\TransferRequest;
use App\Services\Legacy\VeteranRow;
use Illuminate\Support\Facades\DB;

/**
 * Completes a transfer request: marks it completed, stamps who was transferred, deletes the
 * Veteran, records the reward.
 *
 * The reward is recorded on the request for audit (no wallet/ledger exists yet).
 *
 * **Why the label.** The FK's `nullOnDelete` nulls `veteran_id` the moment the Veteran row goes,
 * which is the audit half a finished transfer needs most: without it the surviving row can no
 * longer say WHO was transferred, and the board falls back to a literal "Removed Veteran"
 * (measured 2026-11-15; the comment this replaced claimed the id survives, and it does not).
 * `VeteranRow::label()` is the same wording every Veteran surface already prints, read before
 * the delete.
 *
 * **Why the Veteran read is guarded.** A completion can reach an already-deleted Veteran (the
 * FK nulls the link but the row's `veteran` relation resolves to null), so the stamp and the
 * delete are each skipped when the relation no longer resolves and the status write still lands.
 */
final class CompleteTransferRequest
{
    /**
     * @param  array{star_pieces?: int, monies?: int, support_points?: int}  $reward
     */
    public function handle(TransferRequest $transfer, array $reward = []): TransferRequest
    {
        return DB::transaction(function () use ($transfer, $reward): TransferRequest {
            $transfer->loadMissing('veteran.trainingRun.umamusume');

            if (! $transfer->isOffered()) {
                throw new \RuntimeException('Only offered transfer requests can be completed.');
            }

            // Merge the new reward with any existing reward on the request
            $mergedReward = array_merge(
                (array) $transfer->reward,
                $reward
            );

            $veteran = $transfer->veteran;

            // Record the reward AND the trainee's label before deleting the Veteran: with the row
            // gone `veteran_id` nulls out and there is nothing left to resolve the name from.
            $transfer->update([
                'status' => TransferRequest::STATUS_COMPLETED,
                'reward' => $mergedReward !== [] ? $mergedReward : null,
                'completed_at' => now(),
                'veteran_label' => $veteran?->trainingRun !== null
                    ? VeteranRow::label($veteran->trainingRun)
                    : $transfer->veteran_label,
            ]);

            $veteran?->delete();

            // The instance it just wrote, not a `fresh()` re-read: a fresh read couples this
            // action to a schema shape (the audit row's survival), which is exactly the coupling
            // that made completing a transfer fatal on the pre-correction dev database.
            return $transfer;
        });
    }
}
