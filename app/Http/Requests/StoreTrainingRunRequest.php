<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\RunStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates run creation and updates (PRD FR-C-1, C-4).
 */
class StoreTrainingRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // local-only tool; no auth surface (ARCHITECTURE §8)
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $run = $this->route('run');

        return [
            'umamusume_id' => ['required', 'integer', Rule::exists('umamusume', 'id')],
            'scenario' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(RunStatus::class)],
            'inheritance_parent_a_id' => ['nullable', 'integer', Rule::exists('umamusume', 'id')],
            'inheritance_parent_b_id' => ['nullable', 'integer', Rule::exists('umamusume', 'id')],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
