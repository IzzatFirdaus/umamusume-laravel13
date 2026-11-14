<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\TransferRequest;
use App\Models\Veteran;
use Illuminate\Support\Facades\DB;

/**
 * Offers a Veteran for transfer (creates an offered TransferRequest).
 *
 * The request records the offered reward {star_pieces?, monies?, support_points?}
 * for audit. No wallet/ledger exists yet, so the reward is not paid out at this
 * stage. A Veteran can have at most one offered request at a time (enforced by
 * unique constraint on transfer_requests.veteran_id where status = 'offered').
 */
final class OfferTransferRequest
{
    /**
     * @param  array{star_pieces?: int, monies?: int, support_points?: int}  $reward
     */
    public function handle(Veteran $veteran, array $reward = []): TransferRequest
    {
        return DB::transaction(function () use ($veteran, $reward): TransferRequest {
            $veteran->load('transferRequest');

            if ($veteran->transferRequest?->isOffered()) {
                throw new \RuntimeException('This Veteran already has an active transfer offer.');
            }

            return TransferRequest::create([
                'veteran_id' => $veteran->id,
                'status' => TransferRequest::STATUS_OFFERED,
                'reward' => $reward !== [] ? $reward : null,
            ]);
        });
    }
}
