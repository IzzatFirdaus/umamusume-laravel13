<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\SkillAcquisition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates a batch of run-skill status writes (PRD FR-C-3). Rows are
 * upserted per skill, so one entry per skill is enough; absent skills keep
 * their previous status.
 */
class StoreRunSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'skills' => ['present', 'array'],
            'skills.*.skill_id' => ['required', 'integer', Rule::exists('skills', 'id')],
            'skills.*.status' => ['required', Rule::enum(SkillAcquisition::class)],
            'skills.*.turn_acquired' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @param  array{status: string, turn_acquired?: int|null}  $entry
     */
    public function acquisitionFor(array $entry): SkillAcquisition
    {
        return SkillAcquisition::from($entry['status']);
    }
}
