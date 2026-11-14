<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for creating/updating Transfer Requests.
 *
 * Validates the reward structure {star_pieces?, monies?, support_points?}
 * where each is a non-negative integer.
 */
class TransferRequestRequest extends FormRequest
{
    /**
     * A refused request goes back to the Veteran detail screen.
     *
     * @var string
     */
    protected $redirectRoute = 'veterans.show';

    public function authorize(): bool
    {
        return true; // local-only tool; no auth surface (ARCHITECTURE §8)
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reward' => ['nullable', 'array'],
            'reward.star_pieces' => ['nullable', 'integer', 'min:0'],
            'reward.monies' => ['nullable', 'integer', 'min:0'],
            'reward.support_points' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reward.array' => 'The reward must be an object.',
            'reward.star_pieces.integer' => 'Star Pieces must be a whole number.',
            'reward.star_pieces.min' => 'Star Pieces cannot be negative.',
            'reward.monies.integer' => 'Monies must be a whole number.',
            'reward.monies.min' => 'Monies cannot be negative.',
            'reward.support_points.integer' => 'Support Points must be a whole number.',
            'reward.support_points.min' => 'Support Points cannot be negative.',
        ];
    }

    /**
     * @return array{star_pieces?: int, monies?: int, support_points?: int}
     */
    public function reward(): array
    {
        /** @var array{star_pieces?: int, monies?: int, support_points?: int}|null $reward */
        $reward = $this->validated('reward');

        return $reward ?? [];
    }
}
