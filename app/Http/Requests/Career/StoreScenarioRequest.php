<?php

declare(strict_types=1);

namespace App\Http\Requests\Career;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The scenario choice of the career setup wizard (`SCREEN-002`, PRD FR-C-1, `ADR-0020` §1).
 *
 * The server is the authority on what a scenario is: the accepted set is the keys
 * `config/scenarios.php` composes, the same rule `LegacySearchRequest` applies to its `scenario`
 * filter. A key outside the matrix is refused rather than stored, because `SetupDraft::read()` would
 * then silently drop it and the Trainer would believe a choice had been recorded.
 */
class StoreScenarioRequest extends FormRequest
{
    /**
     * @var string
     */
    protected $redirectRoute = 'career.scenario';

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
            'scenario' => ['required', 'string', Rule::in(array_keys((array) config('scenarios.scenarios')))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'scenario.in' => 'That is not one of the Global scenarios this tool composes.',
            'scenario.required' => 'Choose a scenario to continue.',
        ];
    }
}
