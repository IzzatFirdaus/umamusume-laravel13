<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\TransferRequest;
use Illuminate\Support\Facades\DB;

/**
 * Withdraws an offered transfer request: marks it withdrawn, Veteran stays.
 */
final class WithdrawTransferRequest
{
    public function handle(TransferRequest $transfer): TransferRequest
    {
        if (! $transfer->isOffered()) {
            throw new \RuntimeException('Only offered transfer requests can be withdrawn.');
        }

        return DB::transaction(function () use ($transfer): TransferRequest {
            $transfer->update([
                'status' => TransferRequest::STATUS_WITHDRAWN,
            ]);

            return $transfer->fresh();
        });
    }
}
