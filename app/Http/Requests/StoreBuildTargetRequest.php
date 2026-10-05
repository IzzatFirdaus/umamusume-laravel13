<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\BuildPurpose;
use App\Models\Advisor\BuildTargetPayload;
use App\Models\TrainingRun;
use App\Services\ScenarioCaps;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validates the build target a Trainer enters for one run (FR-F-1, `ADR-0020` §2, `SCREEN-005`).
 *
 * Two owners are reused rather than restated. The value vocabularies come from
 * `BuildTargetPayload`'s constants, because the payload is what reads them back and a second copy
 * here would let a stored row fail its own reader. The stat ceilings come from `ScenarioCaps::forRun`,
 * which is the single owner of that arithmetic (`ADR-0015`): the same call the stat band renders
 * from, so a target the form accepts is a target the run's own ceiling display agrees with. The
 * defect `ADR-0015` closes was exactly a Trainer seeing a reachable cap the form rejected.
 *
 * The ceiling is checked in `withValidator` rather than as a per-field `max` rule because it depends
 * on the run, and a rule string cannot carry a value that varies per request. The message cites the
 * bound it enforced (D-56), so a refusal says what number was too high rather than only that
 * something was.
 *
 * `purpose` is required and is a `BuildPurpose` case: a target with no purpose is a stat list with
 * no reason behind it, which is the thing the screen exists to prevent.
 */
class StoreBuildTargetRequest extends FormRequest
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
        /** @var list<string> $order */
        $order = config('scenarios.stat_order');

        $rules = [
            'purpose' => ['required', 'string', Rule::in(self::purposes())],
            'distance' => ['required', 'string', Rule::in(BuildTargetPayload::DISTANCE_BANDS)],
            'surface' => ['required', 'string', Rule::in(BuildTargetPayload::SURFACES)],
            'style' => ['required', 'string', Rule::in(BuildTargetPayload::STYLES)],
            // Key-restricted to the stat matrix, so a hand-made POST cannot store a sixth stat that
            // the advisor would then have no ceiling and no target row for.
            'targets' => ['required', 'array:'.implode(',', $order)],
            'skill_priorities' => ['present', 'array'],
            'skill_priorities.*' => ['string', 'max:255'],
        ];

        // Each stat is required by name rather than through `targets.*`. The `array:` rule above
        // forbids a key outside the matrix but does NOT require the listed ones to be present, so a
        // four-stat target validated cleanly and then read back with a missing key. Naming them here
        // also means the refusal points at the stat that is missing instead of at the whole map.
        foreach ($order as $stat) {
            $rules["targets.{$stat}"] = ['required', 'integer', 'min:0'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'targets.array' => 'A target lists exactly the five stats and nothing else.',
            'targets.*.required' => 'Enter a target for every stat, or leave the whole target unset.',
            'skill_priorities.present' => 'Send the priority list, even when it is empty.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $run = $this->route('run');

            if (! $run instanceof TrainingRun) {
                return;
            }

            $targets = (array) $this->input('targets');

            foreach (ScenarioCaps::forRun($run) as $stat => $cap) {
                $value = $targets[$stat] ?? null;

                if (! is_numeric($value)) {
                    continue; // the `required` rule above already reports this
                }

                if ((int) $value > $cap) {
                    // D-56: a range error cites the bound. The bound is the run's own scenario
                    // ceiling, which is why it is named here rather than left to the form's markup.
                    $validator->errors()->add("targets.{$stat}", "{$stat} must be between 0 and {$cap}.");
                }
            }
        });
    }

    /**
     * The target as the `build_target` column stores it, in the stat matrix's own order.
     *
     * The order is rebuilt from config rather than taken from the request so a payload's key order
     * is a property of the matrix and not of whatever order the form happened to post in.
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $validated = $this->validated();

        /** @var list<string> $order */
        $order = config('scenarios.stat_order');

        /** @var array<string, mixed> $posted */
        $posted = (array) $validated['targets'];

        $targets = [];

        foreach ($order as $stat) {
            $targets[$stat] = (int) $posted[$stat];
        }

        return [
            'purpose' => (string) $validated['purpose'],
            'distance' => (string) $validated['distance'],
            'surface' => (string) $validated['surface'],
            'style' => (string) $validated['style'],
            'targets' => $targets,
            'skill_priorities' => array_values(array_map(
                static fn (mixed $name): string => (string) $name,
                (array) ($validated['skill_priorities'] ?? []),
            )),
        ];
    }

    /**
     * @return list<string>
     */
    private static function purposes(): array
    {
        return array_map(
            static fn (BuildPurpose $case): string => $case->value,
            BuildPurpose::cases(),
        );
    }
}
