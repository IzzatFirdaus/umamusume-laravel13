<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\TransferRequest;
use App\Models\Veteran;
use Illuminate\Support\Facades\DB;

/**
 * Completes a transfer request: marks it completed, deletes the Veteran, records the reward.
 *
 * The reward is recorded on the request for audit (no wallet/ledger exists yet).
 * The Veteran is permanently deleted (cascades from veterans.training_run_id).
 */
final class CompleteTransferRequest
{
    /**
     * @param  array{star_pieces?: int, monies?: int, support_points?: int}  $reward
     */
    public function handle(TransferRequest $transfer, array $reward = []): TransferRequest
    {
        return DB::transaction(function () use ($transfer, $reward): TransferRequest {
            $transfer->loadMissing('veteran');

            if (! $transfer->isOffered()) {
                throw new \RuntimeException('Only offered transfer requests can be completed.');
            }

            // Merge the new reward with any existing reward on the request
            $mergedReward = array_merge(
                (array) $transfer->reward,
                $reward
            );

            // Record the reward on the request before deleting the veteran
            $transfer->update([
                'status' => TransferRequest::STATUS_COMPLETED,
                'reward' => $mergedReward !== [] ? $mergedReward : null,
                'completed_at' => now(),
            ]);

            // Delete the veteran (the transfer_request keeps veteran_id for audit via nullOnDelete)
            $transfer->veteran->delete();

            return $transfer->fresh();
        });
    }
}
