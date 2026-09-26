<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates one turn entry (PRD FR-C-2). Turn numbers are unique per run;
 * stat bounds come from the legacy planner's confirmed cap, not a guess.
 */
class StoreTurnEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Stat bounds 0..1200 from legacy planner MAX_STAT_VALUE (PRD FR-C-2, Pre-Mortem §4.3).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $run = $this->route('run');
        $turn = $this->route('turn');

        return [
            'turn' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('turn_entries', 'turn')
                    ->where(fn ($q) => $q->where('training_run_id', $run?->id))
                    ->ignore($turn),
            ],
            'speed' => ['required', 'integer', 'between:0,1200'],
            'stamina' => ['required', 'integer', 'between:0,1200'],
            'power' => ['required', 'integer', 'between:0,1200'],
            'guts' => ['required', 'integer', 'between:0,1200'],
            'wit' => ['required', 'integer', 'between:0,1200'],
            'sp' => ['nullable', 'integer', 'between:0,1200'],
            'condition' => ['nullable', 'string', 'max:255'],
        ];
    }
}
