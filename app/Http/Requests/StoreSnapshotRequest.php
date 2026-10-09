<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CareerPhase;
use App\Enums\CareerYear;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * What a snapshot records, and what it is allowed to leave out.
 *
 * A snapshot is read off a client that is partway through a career, so partial is the normal case and
 * not an error: the Trainer enters what they can see and leaves the rest, and the review states the
 * difference. What the run cannot exist without is the trainee (the schema's own non-nullable key),
 * the scenario, and the position - a snapshot without a position is a new career that has not
 * admitted to being one, the same refusal `StoreTrainingRunRequest` makes.
 *
 * The position arrives as the three things the client spells - year, month and half - and never as a
 * turn number, because a Trainer reading "Senior Year, Early October" off their client should not
 * have to count turns to enter it; `CreateSnapshotRun` derives the index through the calendar that
 * owns the arithmetic.
 */
class StoreSnapshotRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'umamusume_id' => ['required', 'integer', Rule::exists('umamusume', 'id')],
            'scenario' => ['required', 'string', Rule::in(array_keys(config('scenarios.scenarios')))],
            'career_year' => ['required', 'integer', Rule::in(array_column(CareerYear::cases(), 'value'))],
            'career_month' => ['required', 'integer', 'min:1', 'max:12'],
            'career_phase' => ['required', 'string', Rule::in(array_column(CareerPhase::cases(), 'value'))],
            'speed' => ['nullable', 'integer', 'min:0'],
            'stamina' => ['nullable', 'integer', 'min:0'],
            'power' => ['nullable', 'integer', 'min:0'],
            'guts' => ['nullable', 'integer', 'min:0'],
            'wit' => ['nullable', 'integer', 'min:0'],
            'energy' => ['nullable', 'integer', 'min:0', 'max:100'],
            'fans' => ['nullable', 'integer', 'min:0'],
            'skill_points' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
