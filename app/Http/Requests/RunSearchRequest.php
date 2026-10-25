<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\RunStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The Careers list's one filter row (HFW runs-index annotation 1: filters are a single inline form
 * row). Every field is optional, and an empty value is "no filter chosen", the same answer as
 * omitting the key, so the three empties become null and pass as nullable rather than failing their
 * `in` lists as the empty string.
 *
 * `all` is this surface's token for the unfiltered list; it joins each accepted vocabulary
 * explicitly instead of being one of the values the rule would otherwise reject.
 */
class RunSearchRequest extends FormRequest
{
    public const ALL = 'all';

    /**
     * @var string
     */
    protected $redirectRoute = 'runs.index';

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
            'search' => ['nullable', 'string', 'max:100'],
            'scenario' => ['nullable', Rule::in([
                self::ALL,
                ...array_keys((array) config('scenarios.scenarios')),
            ])],
            'status' => ['nullable', Rule::in([
                self::ALL,
                ...array_map(static fn (RunStatus $case): string => $case->value, RunStatus::cases()),
            ])],
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['search', 'scenario', 'status'] as $key) {
            if ($this->input($key) === '') {
                $this->merge([$key => null]);
            }
        }
    }
}
