<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\BuildPurpose;
use App\Http\Requests\Concerns\ValidatesEnteredCaps;
use App\Models\Advisor\BuildTargetPayload;
use App\Models\TrainingRun;
use App\Services\Career\SetupDraft;
use App\Services\ScenarioCaps;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validates the build target a Trainer enters (FR-F-1, `ADR-0020` §2, `SCREEN-005`).
 *
 * One rule set for both entry points: the run-scoped write (`runs.build-target.update`, C1) and the
 * setup wizard's step 3 draft write (`career.target.store`, D4, through `StoreDraftBuildTargetRequest`,
 * which only changes the destination and the redirect). The ceiling resolves against the route's run
 * when there is one and against the draft's scenario when there is not.
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
 * **A target may be entered with the ceilings that make it legal** (`ADR-0025`). The four `cap_*` keys
 * ride this same save, validated by `ValidatesEnteredCaps` (the rule set the identity form shares) and
 * clamped against the ceilings as posted rather than as stored, so one save of a 1325 ceiling and a 1325
 * Speed target is accepted instead of answering its own bound. A target remains all or nothing; ceilings
 * are its own optional fields, and an unset ceiling keeps the scenario's answer.
 *
 * **The whole target is optional, and all or nothing.** A wholly unset target is a real state: the
 * form's own message has always offered it ("or leave the whole target unset"), the wizard's model
 * supports it (Preflight warns about a missing target rather than blocking), and `training_runs`
 * stores null for it. Until D4 every field was `required`, so the branch the message described could
 * not be reached and an empty save answered nine errors. Now `isEmptyTarget()` decides: when nothing
 * carries a value, no field is required and `payload()` answers null; the moment one carries a value
 * the target is a real one, and then every field is required, `purpose` included, because a target
 * with no purpose is a stat list with no reason behind it.
 */
class StoreBuildTargetRequest extends FormRequest
{
    use ValidatesEnteredCaps;

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

        // All or nothing: a wholly unset target requires nothing, and any value makes every field
        // required. See the class docblock.
        $presence = $this->isEmptyTarget() ? 'nullable' : 'required';

        $rules = [
            'purpose' => [$presence, 'string', Rule::in(self::purposes())],
            'distance' => [$presence, 'string', Rule::in(BuildTargetPayload::DISTANCE_BANDS)],
            'surface' => [$presence, 'string', Rule::in(BuildTargetPayload::SURFACES)],
            'style' => [$presence, 'string', Rule::in(BuildTargetPayload::STYLES)],
            // Key-restricted to the stat matrix, so a hand-made POST cannot store a sixth stat that
            // the advisor would then have no ceiling and no target row for.
            'targets' => [$presence, 'array:'.implode(',', $order)],
            'skill_priorities' => ['present', 'array'],
            'skill_priorities.*' => ['string', 'max:255'],
        ];

        // Each stat is required by name rather than through `targets.*`. The `array:` rule above
        // forbids a key outside the matrix but does NOT require the listed ones to be present, so a
        // four-stat target validated cleanly and then read back with a missing key. Naming them here
        // also means the refusal points at the stat that is missing instead of at the whole map.
        foreach ($order as $stat) {
            $rules["targets.{$stat}"] = [$presence, 'integer', 'min:0'];
        }

        // The four entered ceilings ride the same save as the target that needs them (`ADR-0025`), so a
        // Trainer who reads 1325 off the client can state it in the same breath as a 1325 target. They
        // are not part of the all-or-nothing target above: a run may hold ceilings and no target.
        return array_merge($rules, $this->enteredCapRules());
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
            // The clamp target is the run when the request is run-scoped (`runs.build-target.update`,
            // C1) and the setup draft's scenario when it is the wizard's step 3
            // (`career.target.store`, D4, through `StoreDraftBuildTargetRequest`). One rule set, two
            // entry points: `SetupDraft::planningRun()` returns a saved=false `TrainingRun` carrying
            // the draft's scenario, and `ScenarioCaps::forRun()` reads only `hasScenario()` and
            // `scenarioKey()`, so a career that does not exist yet is clamped against the same
            // ceiling a saved one is. A draft with no scenario yet resolves to null, and
            // `forRun(null)` answers with the base cap and no bonus rather than no clamp at all.
            $run = $this->route('run');

            if (! $run instanceof TrainingRun) {
                $run = SetupDraft::planningRun();
            }

            // Ceilings entered in this same save bound the targets entered with them. Reading the stored
            // run instead would refuse a 1325 target beside the 1325 ceiling that makes it legal, because
            // the ceiling on screen has not been written yet (`ADR-0025`).
            $bound = ScenarioCaps::forRun($this->ceilingView($run));

            $targets = (array) $this->input('targets');

            foreach ($bound as $stat => $cap) {
                $value = $targets[$stat] ?? null;

                if (! is_numeric($value)) {
                    continue; // the `required` rule above already reports this
                }

                if ((int) $value > $cap) {
                    // D-56: a range error cites the bound. The bound is the run's own ceiling, which is
                    // why it is named here rather than left to the form's markup.
                    $validator->errors()->add("targets.{$stat}", "{$stat} must be between 0 and {$cap}.");
                }
            }

            $this->assertCapsCoverTheRecordedStats($validator, $run);
        });
    }

    /**
     * Whether the whole target is unset: no purpose, no band, no numbers.
     *
     * The state the form's own message offers, and the state the wizard's model already supports. It
     * is read before validation, so `rules()` can require nothing from an empty target and `payload()`
     * can answer null rather than an invented list of zeroes.
     */
    public function isEmptyTarget(): bool
    {
        foreach (['purpose', 'distance', 'surface', 'style'] as $field) {
            if ($this->filled($field)) {
                return false;
            }
        }

        foreach ((array) $this->input('targets', []) as $value) {
            if ($value !== null && $value !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * The target as the `build_target` column stores it, in the stat matrix's own order, or null when
     * the whole target is unset.
     *
     * The order is rebuilt from config rather than taken from the request so a payload's key order
     * is a property of the matrix and not of whatever order the form happened to post in.
     *
     * @return array<string, mixed>|null
     */
    public function payload(): ?array
    {
        if ($this->isEmptyTarget()) {
            return null;
        }

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
