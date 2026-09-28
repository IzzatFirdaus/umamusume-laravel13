<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\RunStatus;
use App\Models\RaceEntry;
use App\Models\TrainingRun;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates run creation and updates (PRD FR-C-1, C-4).
 */
class StoreTrainingRunRequest extends FormRequest
{
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
            'umamusume_id' => ['required', 'integer', Rule::exists('umamusume', 'id')],
            /*
             * The scenario is validated against the composition matrix's keys rather
             * than stored as an unconstrained name, because every scenario-aware
             * component resolves from those keys and a value outside them renders a
             * strip the run has no mechanic for. `config/scenarios.php` stays the
             * single source of truth: adding a fifth scenario needs a config entry
             * and no request change (D-240, owner ruling 2026-09-27 — validation at
             * the boundary instead of a migration to a foreign key).
             */
            'scenario' => ['nullable', 'string', Rule::in(self::scenarios())],
            'status' => ['required', Rule::enum(RunStatus::class)],
            'inheritance_parent_a_id' => ['nullable', 'integer', Rule::exists('umamusume', 'id')],
            'inheritance_parent_b_id' => ['nullable', 'integer', Rule::exists('umamusume', 'id')],
            'notes' => ['nullable', 'string', 'max:5000'],
            /*
             * The Grade Point period the Trainer reports as live (US-10, ADR-0003).
             * Entered, never derived (D-270): nothing in the corpus names a formula
             * that puts a career in a period, so "null until set" is the honest state
             * and the meter says so rather than guessing. It is also only meaningful
             * on a run whose scenario composes the panel, which is read from the
             * composition matrix through the run's own scenario, including the one
             * this same request is about to write.
             */
            'current_objective_index' => [
                'nullable',
                'integer',
                Rule::in(range(1, RaceEntry::MAX_OBJECTIVE_INDEX)),
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value === null) {
                        return;
                    }

                    $run = $this->route('run');
                    $scenario = $this->input('scenario', $run instanceof TrainingRun ? $run->scenario : null);

                    if ($scenario === null || $scenario === ''
                        || config('scenarios.scenarios.'.$scenario.'.panels.grade_objectives') !== true) {
                        $fail('This scenario has no Grade Point periods, so there is no period to report.');
                    }
                },
            ],
        ];
    }

    /**
     * An empty scenario is the same statement as no scenario: the run renders the
     * baseline strip. Normalising it to null here means one representation reaches
     * the model, so `whereNull('scenario')` finds unset runs rather than missing
     * the empty-string ones.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('scenario')) && trim($this->input('scenario')) === '') {
            $this->merge(['scenario' => null]);
        }
    }

    /**
     * Every scenario slug the UI may compose from.
     *
     * @return list<string>
     */
    public static function scenarios(): array
    {
        return array_keys(config('scenarios.scenarios'));
    }
}
