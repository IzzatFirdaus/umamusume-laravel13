<?php

declare(strict_types=1);

namespace App\Http\Requests\Career;

use App\Enums\RunStatus;
use App\Models\Advisor\BuildTargetPayload;
use App\Models\DeckSlot;
use App\Models\Legacy\LegacySelectionPayload;
use App\Models\Veteran;
use App\Services\Career\SetupDraft;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use InvalidArgumentException;

/**
 * `Start Career` — the wizard's one write (SCREEN-008, PRD FR-A-5, `ADR-0020` §1 and §3).
 *
 * **The preview is not a trust boundary, so this request does not trust it.** The page posts nothing the
 * run is built from: `prepareForValidation()` composes the run's facts out of the session draft
 * (`SetupDraft`), which is the same bag the five steps wrote, and the browser's copy of them is only ever
 * a rendering of that bag. A hand-made POST therefore cannot smuggle in a trainee, a deck or an ancestry
 * that the steps never accepted.
 *
 * **Every step's own rules run again here, not a copy of them.** `withValidator()` hydrates each step's
 * Form Request with its slice of the composed draft and validates it, so the boundary that guards the
 * wizard is the boundary that guarded each step: a scenario removed from `config/scenarios.php`, a support
 * card deleted from the catalogue, a Veteran whose library row is gone, a duplicate card or a parent who
 * is the trainee herself are each refused by the rule set that owns them rather than by a second one that
 * could drift. `StoreDraftDeckRequest`, `StoreDraftLegacyRequest` and `StoreDraftBuildTargetRequest` are
 * the hydrated classes, because their `authorize()` believes a local-only request and their destinations
 * are unreachable from here.
 *
 * A refusal names the section rather than the field: a draft that no longer validates is a *stale* draft,
 * and the Trainer's next action is the step that owns it, which the error message says.
 */
class StartCareerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // local-only tool; no auth surface (ARCHITECTURE §8)
    }

    /**
     * The composed run facts, merged in before validation so the rules read them like any other input.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'umamusume_id' => SetupDraft::read()['umamusume_id'],
            'scenario' => SetupDraft::read()['scenario'],
            'status' => RunStatus::Active->value,
            'build_target' => SetupDraft::buildTarget(),
            'legacy_selection' => SetupDraft::legacySelection(),
            'legacy_parents' => SetupDraft::legacyParents(),
            'deck' => SetupDraft::deck(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Only a trainee and a scenario are required: a run without them is not a career. The three
            // payload sections stay optional on purpose, because the wizard's warnings never block — a
            // Trainer who recorded no ancestors and equipped no cards can still start, and the columns
            // store null rather than an empty payload that its own reader would refuse.
            'umamusume_id' => ['required', 'integer', Rule::exists('umamusume', 'id')],
            'scenario' => ['required', 'string', Rule::in(array_keys((array) config('scenarios.scenarios')))],
            'status' => ['required', Rule::enum(RunStatus::class)],
            // The three payload sections are shape-checked here and rule-checked by their own requests in
            // `withValidator()`; `array` rather than a key list because the payload readers own the keys.
            'build_target' => ['nullable', 'array'],
            'legacy_selection' => ['nullable', 'array'],
            'legacy_parents' => ['nullable', 'array', 'size:2'],
            'legacy_parents.*' => ['nullable', 'integer', Rule::exists('veterans', 'id')],
            'deck' => ['nullable', 'array', 'size:6'],
            'deck.*.position' => ['required', 'integer', Rule::in(DeckSlot::POSITIONS)],
            'deck.*.support_card_id' => ['nullable', 'integer', Rule::exists('support_cards', 'id')],
            'deck.*.ownership' => ['nullable', 'string', Rule::in(DeckSlot::OWNERSHIP)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->rerunStepRules($validator);
        });
    }

    /**
     * The run as `training_runs` stores it, the two parent identities resolved to the trainee each
     * library row points at rather than to the library ids themselves (`ADR-0010` Decision).
     *
     * @return array<string, mixed>
     */
    public function runAttributes(): array
    {
        /** @var array{0: int|null, 1: int|null} $parents */
        $parents = (array) $this->input('legacy_parents');

        return [
            'umamusume_id' => (int) $this->input('umamusume_id'),
            'scenario' => (string) $this->input('scenario'),
            'status' => RunStatus::from((string) $this->input('status')),
            // A section nobody entered writes null, never `[]`: `TrainingRun::legacySelection()` runs the
            // column back through `LegacySelectionPayload::fromArray()`, which refuses an empty payload,
            // so an empty array here would be a run whose own page throws.
            'build_target' => is_array($this->input('build_target')) ? $this->input('build_target') : null,
            'legacy_selection' => is_array($this->input('legacy_selection')) ? $this->input('legacy_selection') : null,
            'inheritance_parent_a_id' => Veteran::traineeId($parents[0] ?? null),
            'inheritance_parent_b_id' => Veteran::traineeId($parents[1] ?? null),
        ];
    }

    /**
     * The six positions as `deck_slots` rows: position to card id, the blanks left out the way the
     * run-scoped deck write leaves them (a blank is a cleared slot, not a row).
     *
     * @return array<int, int>
     */
    public function deckByPosition(): array
    {
        $slots = (array) $this->input('deck');
        $byPosition = [];

        foreach ($slots as $slot) {
            if (! is_array($slot) || $slot['support_card_id'] === null) {
                continue;
            }

            $byPosition[(int) $slot['position']] = (int) $slot['support_card_id'];
        }

        return $byPosition;
    }

    /**
     * Validate the composed draft with the rules that own each section, and report a failure against the
     * section rather than the field.
     *
     * The child request is hydrated with its own slice (`createFrom`) and given this request's container,
     * so its rules and its cross-field assertions read the draft's values exactly as they read a form post.
     */
    private function rerunStepRules(Validator $validator): void
    {
        $deck = [];

        foreach ((array) $this->input('deck') as $slot) {
            if (! is_array($slot) || $slot['support_card_id'] === null) {
                continue; // a cleared position is a gap, and the deck rule drops it the same way
            }

            $deck[(int) $slot['position']] = [
                'support_card_id' => $slot['support_card_id'],
                'ownership' => $slot['ownership'],
            ];
        }

        $this->rerun(StoreDraftDeckRequest::class, 'deck', ['deck' => $deck], $validator);

        if (is_array($this->input('legacy_selection'))) {
            $this->rerun(StoreDraftLegacyRequest::class, 'legacy_selection', (array) $this->input('legacy_selection'), $validator);
        }

        if (is_array($this->input('build_target'))) {
            $this->rerun(StoreDraftBuildTargetRequest::class, 'build_target', (array) $this->input('build_target'), $validator);
        }

        // Rules do not see a key they were not told about, so the payload readers are the second gate: a
        // stored payload that has drifted out of the shape its own reader accepts is refused here rather
        // than written to a column that would read back as nothing.
        $this->assertReadable('legacy_selection', fn (): mixed => $this->input('legacy_selection') === null
            ? null
            : LegacySelectionPayload::fromArray((array) $this->input('legacy_selection')), $validator);
        $this->assertReadable('build_target', fn (): mixed => $this->input('build_target') === null
            ? null
            : BuildTargetPayload::fromArray((array) $this->input('build_target')), $validator);
    }

    /**
     * @param  callable(): mixed  $read
     */
    private function assertReadable(string $section, callable $read, Validator $validator): void
    {
        try {
            $read();
        } catch (InvalidArgumentException $exception) {
            $validator->errors()->add($section, $exception->getMessage().' Open the step that entered it and save it again.');
        }
    }

    /**
     * @param  class-string<FormRequest>  $request
     * @param  array<string, mixed>  $slice
     */
    private function rerun(string $request, string $section, array $slice, Validator $validator): void
    {
        // Build a fully-independent child request rather than `createFrom()`, which
        // shallow-copies Symfony ParameterBags by reference. A shared bag means the
        // child's `replace()` wipes this request's own merged keys (status, scenario,
        // build_target, legacy_parents, deck, umamusume_id) between validation passing
        // and `runAttributes()` reading them, producing the enum backing-value error
        // `"" is not a valid backing value for RunStatus`. Using `::create()` guarantees
        // fresh ParameterBags for every source (query, request, json, attributes),
        // so the child's input mutation never touches the parent.
        $server = $this->server->all();
        // The parent is an Inertia JSON PUT, so its $server carries HTTP_CONTENT_TYPE
        // (and friends) = application/json. Passing those through to `::create()` makes
        // the child resolve as JSON (`isJson()` true), so `getInputSource()` returns
        // the $json ParameterBag, which `::create()` never populates from the $parameters
        // array (that only writes the $request form bag). Stripping every content-type
        // header from $server forces the created child to resolve as form-urlencoded,
        // so `getInputSource()` returns the form bag that IS populated with $slice.
        foreach (['CONTENT_TYPE', 'HTTP_CONTENT_TYPE', 'HTTP_ACCEPT', 'HTTP_X_INERTIA'] as $header) {
            unset($server[$header]);
        }
        $child = $request::create(
            $this->fullUrl(),
            $this->method(),
            $slice,
            $this->cookies->all(),
            $this->files->all(),
            $server
        );
        if ($this->hasSession()) {
            $child->setLaravelSession($this->session());
        }
        // Defense-in-depth: force Content-Type to form-urlencoded so that any code path
        // reading headers (vs isJson()) still sees form. Stripping the JSON-related
        // $server keys above already makes isJson() false, this guards any Symfony/
        // Laravel framework sub-path that keys off the header directly.
        $child->headers->set('Content-Type', 'application/x-www-form-urlencoded');
        $child->setContainer($this->container);
        $child->setRedirector($this->container->make(Redirector::class));

        try {
            $child->validateResolved();
        } catch (ValidationException $exception) {
            /** @var array<string, list<string>> $errors */
            $errors = $exception->errors();
            $first = $errors === [] ? [] : reset($errors);

            $validator->errors()->add(
                $section,
                'This section no longer validates against the options the tool holds: '
                .($first[0] ?? 'see the step.')
                .' Open the step that entered it and save it again.'
            );
        }
    }
}
