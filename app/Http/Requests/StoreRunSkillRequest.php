<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ReleaseStatus;
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
            // Scoped to the rows a Global Trainer can meet, using the same scope every read path
            // starts from (ADR-0011 §2). Unscoped, this accepted any id in the table, so a skill the
            // form can never offer could be pinned to a run by posting its id. The predicate is
            // repeated here rather than shared with the controller on purpose: a `Rule::exists`
            // cannot call a model scope, and a constraint extracted for two callers would be one
            // more place for the two to drift apart.
            'skills.*.skill_id' => [
                'required',
                'integer',
                Rule::exists('skills', 'id')->where(
                    fn ($query) => $query
                        ->where('release_status', ReleaseStatus::GlobalReleased->value)
                        ->where('name_is_client', true),
                ),
            ],
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
