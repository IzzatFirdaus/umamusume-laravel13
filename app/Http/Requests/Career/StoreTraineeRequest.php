<?php

declare(strict_types=1);

namespace App\Http\Requests\Career;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The trainee choice of the career setup wizard (`SCREEN-003`, PRD FR-A-1, `ADR-0020` §1).
 *
 * `AGENTS.md` §7 asks for a Form Request in front of every write, and this is a write, so the id is not
 * read straight off the request into `SetupDraft`. The rule is `exists` rather than `integer` because a
 * draft naming a trainee who is not in the catalog cannot be resolved by the later steps, and the Trainer
 * would only find out at Preflight. An id that exists is enough: this screen lists Global trainees, and a
 * Japan-only trainee's row is a real one that the run screen already knows how to state.
 *
 * `present` rather than `required`, with `nullable` beside it: an id that is present and null is the
 * deliberate clear of the step-2 choice, while a request that omits the key is malformed and still
 * refuses. `character_card_id` stays plain `nullable` because a single-form trainee has nothing to
 * choose, so an absent card key is a complete answer rather than a defect.
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
            'umamusume_id' => ['present', 'nullable', 'integer', 'exists:umamusume,id'],
            /*
             * R3-06: which costume form the career starts on is a choice the Trainer makes at this step, and
             * a form is valid only among the submitted trainee's own. The join is carried in the exists rule
             * for the same reason `StoreTrainingRunRequest` carries it: a mismatched pair becomes a refusal at
             * the boundary rather than a run row that contradicts itself. Optional, because a single-form
             * trainee has nothing to choose and a null here is a complete answer, not a missing field.
             */
            'character_card_id' => ['nullable', 'integer', Rule::exists('character_cards', 'id')
                ->where('umamusume_id', $this->input('umamusume_id'))],
            /*
             * The trainee profile's own primary advances the wizard, so it asks for the next step by
             * name. The allowlist is one value on purpose: the roster's card click and the catalog's
             * carry door keep the read-back redirect, and no other destination is offered.
             */
            'next' => ['nullable', 'in:target'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'umamusume_id.present' => 'Choose a trainee to continue.',
            'umamusume_id.exists' => 'That trainee is not in this catalog, so the career could not be built on her.',
            'character_card_id.exists' => 'That costume form does not belong to the chosen trainee, so it cannot start her career.',
        ];
    }
}
