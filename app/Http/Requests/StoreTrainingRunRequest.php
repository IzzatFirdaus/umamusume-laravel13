<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\RunMode;
use App\Enums\RunStatus;
use App\Models\RaceEntry;
use App\Models\TrainingRun;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
             * A card is a valid choice only if it is one of the submitted trainee's
             * forms. Carrying that join in the exists rule makes a mismatched pair a
             * validation error at the boundary instead of a row that contradicts
             * itself, so the combobox cannot be coaxed into naming a card its
             * run's trainee does not own. Reading umamusume_id with input() keeps the
             * join strict: when the trainee is absent or invalid the where falls to a
             * null/never-matching comparison, never one a card could silently pass.
             */
            'character_card_id' => ['nullable', 'integer', Rule::exists('character_cards', 'id')
                ->where('umamusume_id', $this->input('umamusume_id'))],
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
            'mode' => ['nullable', Rule::enum(RunMode::class)],
            'inheritance_parent_a_id' => ['nullable', 'integer', Rule::exists('umamusume', 'id')],
            'inheritance_parent_b_id' => ['nullable', 'integer', Rule::exists('umamusume', 'id')],
            'notes' => ['nullable', 'string', 'max:5000'],
            /*
             * The trainee's rarity (1..3) and potential level (1..5), as the Trainer reads them.
             * Entered, never derived: a run may name only a trainee and no card, and the potential
             * level belongs to the trainee rather than the card, so neither can be computed from
             * the card layer. Null is the honest "not stated" (D-220).
             */
            'trainee_rarity' => ['nullable', 'integer', 'between:1,3'],
            'potential_level' => ['nullable', 'integer', 'between:1,5'],
            /*
             * The trainee's growth-rate row as the Trainer reads it off the card, keyed by the stat
             * matrix so a hand-made POST cannot store a sixth stat. Entered, never derived: no
             * catalogue holds a growth figure (the trainee filter omits one for that reason), so a
             * stored bag is the only source. Sparse: a stat with no growth is simply absent, and a
             * run that stated none stores null rather than an invented row of zeroes (D-220).
             */
            'growth_rate' => ['nullable', 'array:'.implode(',', self::stats())],
            'growth_rate.*' => ['nullable', 'integer', 'between:0,30'],
            /*
             * The run's own ceilings as the Trainer composed them, keyed by the stat matrix so a
             * hand-made POST cannot store a sixth stat. Entered, never derived: `ScenarioCaps` owns
             * the scenario's ceiling, but the run's real caps sit above it once the card's sparks and
             * any support-card Max-Stat layer are counted, and neither input lives here. Sparse, and
             * null rather than an invented row of zeroes when the run stated none (D-220). No upper
             * bound: the composed ceiling is the Trainer's own arithmetic, not this tool's.
             */
            'stat_ceilings' => ['nullable', 'array:'.implode(',', self::stats())],
            'stat_ceilings.*' => ['nullable', 'integer', 'min:0'],
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
            // The shop rotation countdown, as the Trainer reads it. The scenario's own
            // `shop.rotation_turns` is the upper bound and the model guard applies it,
            // because the bound lives in config and a static rule here would duplicate
            // it with a number that can drift.
            'shop_resets_in' => ['nullable', 'integer', 'min:0'],
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

        /*
         * A run created before the mode column existed is a new career, and so is one whose form
         * predates the snapshot entry point: the default lives here rather than in the schema so a
         * writer that omits the field gets the same run a pre-snapshot writer got, and the schema
         * keeps its own null for a row no request ever touched.
         */
        $this->merge(['mode' => $this->input('mode', RunMode::NewCareer->value)]);
    }

    /**
     * The one cross-field rule the two modes need: a snapshot names where it stands, because a
     * snapshot without a position is not a snapshot - it is a new career that has not admitted to
     * being one, and every surface reading its turn numbers would then derive Junior Early January
     * from a run the Trainer says is halfway through Senior Year.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->enum('mode', RunMode::class) !== RunMode::Snapshot) {
                return;
            }

            if ($this->input('career_position') === null) {
                $validator->errors()->add(
                    'career_position',
                    'A snapshot must name where the career stands, because a snapshot without a position is a new career that has not admitted to being one.',
                );
            }
        });
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

    /**
     * The stat matrix, the only keys a run-level stat bag may carry.
     *
     * @return list<string>
     */
    public static function stats(): array
    {
        return array_values((array) config('scenarios.stat_order'));
    }
}
