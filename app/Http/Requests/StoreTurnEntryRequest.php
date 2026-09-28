<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\MoodTier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates one turn entry (PRD FR-C-2). Turn numbers are unique per run;
 * stat bounds come from the legacy planner's confirmed cap, not a guess.
 *
 * The guided rail posts five keys the raw form never sends: `stage`, `previewed`,
 * `choice`, `outcome` and `penalty_kind`. They are all conditional on `stage` being
 * present, so the escape hatch keeps posting exactly the eight fields it always posted
 * and stays valid (D-53: reachable, not the default). `stage` is what distinguishes the
 * two, not a second endpoint: adding a route would have meant touching `routes/web.php`,
 * which is outside this slice's scope.
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
        $staged = $this->input('stage') !== null;

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
            'sp' => ['nullable', 'integer', 'min:0'],
            'condition' => ['nullable', 'string', 'max:255'],
            // Energy is 0..100 (ADR-0001); mood is the client's five tiers, not
            // free text. The 0..1200 stat bound above is unchanged here:
            // ADR-0003 decision 6 replaces it with the scenario's own hard_cap,
            // which is a separate change and not folded into this one.
            'energy' => ['nullable', 'integer', 'between:0,100'],
            'mood' => ['nullable', Rule::enum(MoodTier::class)],
            'fans' => ['nullable', 'integer', 'min:0'],
            // The rail's own fields. `choice` and `outcome` are only demanded on a
            // staged submit, because the escape hatch has no step to choose and no
            // outcome to declare: it writes the row the Trainer typed.
            'stage' => ['nullable', Rule::in(['preview', 'confirm'])],
            'choice' => [Rule::requiredIf(fn (): bool => $staged), 'nullable', 'string', 'max:60'],
            'outcome' => [Rule::requiredIf(fn (): bool => $staged), 'nullable', Rule::in(['Success', 'Failure'])],
            'penalty_kind' => [
                Rule::requiredIf(fn (): bool => $this->input('outcome') === 'Failure'),
                'nullable',
                Rule::in(['energy', 'mood', 'stat']),
            ],
            // A UX guard, not a security control: this tool has no auth surface (NFR-1),
            // so nothing stops a hand-made POST from setting it. What it buys is that the
            // browser cannot reach a write without the preview having been rendered, which
            // is D-51's "always" enforced server-side instead of with a script.
            'previewed' => [Rule::requiredIf(fn (): bool => $this->input('stage') === 'confirm'), 'nullable', 'in:1'],
        ];
    }

    /**
     * Errors return to the step that caused them with the reason in the Trainer's words
     * (D-56). A generic "the field is required" on the preview gate would read as a
     * broken button.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'previewed.required' => 'Preview the turn before confirming it.',
            'choice.required' => 'Choose what this turn did.',
            'outcome.required' => 'Say whether the turn succeeded or failed.',
            'penalty_kind.required' => 'A failure needs its penalty kind: Energy, Mood, or a stat.',
        ];
    }
}
