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
     * The form always ships one row nobody has filled in — that is how a Trainer adds the next
     * skill without a second submit. An empty picker is not a missing skill, so the row goes before
     * validation rather than turning every honest save into a `required` failure.
     *
     * `array_values` re-indexes, so a submit whose spare row was row 0 still validates its rows as
     * a list and `syncSkills` walks what actually arrived.
     */
    protected function prepareForValidation(): void
    {
        $skills = $this->input('skills');

        if (! is_array($skills)) {
            return;
        }

        $this->merge([
            'skills' => array_values(array_filter(
                $skills,
                static fn (mixed $row): bool => is_array($row) && ($row['skill_id'] ?? '') !== '',
            )),
        ]);
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
