<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\MoodTier;
use App\Enums\PerformanceType;
use App\Models\TrainingRun;
use App\Services\ScenarioCaps;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates one turn entry (PRD FR-C-2). Turn numbers are unique per run; a stat's ceiling
 * is the run's own scenario ceiling, read from the same matrix the stat band renders
 * (ADR-0015).
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
     * Each stat is bounded by the run's own scenario ceiling, not a flat number
     * (ADR-0015, superseding ADR-0002's flat framing and implementing ADR-0003 decision 6).
     *
     * The bound was 0..1200 for every stat since the legacy planner's MAX_STAT_VALUE, while
     * the matrix has always carried the bonus each scenario adds to that base - the figure
     * `x-stat-band` prints as the cap. So the form rejected numbers the same screen showed
     * as reachable. A run with no scenario keeps 1200: it claims no scenario's bonus, and
     * `ScenarioCaps::forRun()` says why in one place rather than here.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $run = $this->route('run');
        $turn = $this->route('turn');
        $staged = $this->input('stage') !== null;
        $caps = ScenarioCaps::forRun($run instanceof TrainingRun ? $run : null);

        return [
            'turn' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('turn_entries', 'turn')
                    ->where(fn ($q) => $q->where('training_run_id', $run?->id))
                    ->ignore($turn),
            ],
            'speed' => ['required', 'integer', 'between:0,'.$caps['Speed']],
            'stamina' => ['required', 'integer', 'between:0,'.$caps['Stamina']],
            'power' => ['required', 'integer', 'between:0,'.$caps['Power']],
            'guts' => ['required', 'integer', 'between:0,'.$caps['Guts']],
            'wit' => ['required', 'integer', 'between:0,'.$caps['Wit']],
            // No source in the corpus puts a ceiling on skill points, so the upper bound is
            // absent rather than invented (ADR-0015). The non-negative floor stays, and the
            // absence is stated here so a future 99999 report lands on a decision, not a gap.
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
            // A Performance observation (Our Grand Concert). Optional even on a staged submit,
            // unlike the rail's other keys: the escape hatch has no Performance field, and a turn
            // the Trainer did not read the resource off has nothing to record, so `nullable` is the
            // honest modifier rather than `requiredIf`. `array:type,delta` refuses a third key
            // instead of dropping it, which is the strictness `PerformancePayload` applies one layer
            // down; the pair travels together or not at all.
            'performance' => ['nullable', 'array:type,delta'],
            'performance.type' => ['required_with:performance', Rule::enum(PerformanceType::class)],
            // Zero is refused here as well as in the payload, so a Trainer gets a field error
            // rather than the exception the model raises on save.
            'performance.delta' => ['required_with:performance', 'integer', 'not_in:0'],
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
            'performance.delta.not_in' => 'A Performance change of zero is not a change.',
        ];
    }
}
