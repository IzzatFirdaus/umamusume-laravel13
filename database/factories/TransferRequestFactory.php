<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TransferRequest;
use App\Models\Veteran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransferRequest>
 */
class TransferRequestFactory extends Factory
{
    protected $model = TransferRequest::class;

    /**
     * An offered request for a factory-built Veteran, with no reward recorded: the lifecycle's
     * live state, and the one the `veterans.show` door branches on. Use the model's own status
     * constants for the other two states; the `booted()` guard stamps `completed_at` /
     * `withdrawn_at` from `status` on save.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'veteran_id' => Veteran::factory(),
            'status' => TransferRequest::STATUS_OFFERED,
            'reward' => null,
        ];
    }
}
