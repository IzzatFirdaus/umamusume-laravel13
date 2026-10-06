<?php

declare(strict_types=1);

namespace App\Http\Requests\Career;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The trainee choice of the career setup wizard (`SCREEN-003`, PRD FR-A-1, `ADR-0020` §1).
 *
 * `AGENTS.md` §7 asks for a Form Request in front of every write, and this is a write, so the id is not
 * read straight off the request into `SetupDraft`. The rule is `exists` rather than `integer` because a
 * draft naming a trainee who is not in the catalog cannot be resolved by the later steps, and the Trainer
 * would only find out at Preflight. An id that exists is enough: this screen lists Global trainees, and a
 * Japan-only trainee's row is a real one that the run screen already knows how to state.
 */
class StoreTraineeRequest extends FormRequest
{
    /**
     * @var string
     */
    protected $redirectRoute = 'career.trainee';

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
            'umamusume_id' => ['required', 'integer', 'exists:umamusume,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'umamusume_id.required' => 'Choose a trainee to continue.',
            'umamusume_id.exists' => 'That trainee is not in this catalog, so the career could not be built on her.',
        ];
    }
}
