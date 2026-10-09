<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\RunStatus;
use App\Models\Legacy\LegacySelectionPayload;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use App\Models\Veteran;
use App\Services\Career\SetupDraft;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * The "Confirm Inheritance" write (D-260, D-268, `ADR-0010`).
 *
 * This is the writer `LegacySelectionSchemaTest` said did not exist, and the gap `SCREEN_SPEC.md`
 * §7-3 records: the column was stored, typed and validated on the way in, and nothing could reach it.
 * It writes the `legacy_selection` json column through `LegacySelectionPayload::fromArray()`, so the
 * shape the Trainer is shown is the shape the model's own reader accepts. A hand-made POST that
 * misspells a key is refused here with the key named, rather than stored as a row that reads as
 * nothing.
 *
 * **What this deliberately does not accept**, because `ADR-0010` bans it and the request is where a
 * hand-made POST would smuggle it in:
 *
 * - No field that asks *whether* a Spark would fire, what an Affinity grade is worth, or what a
 *   configuration would yield. The payload has no slot for any of them, and adding one is an ADR.
 * - `legacy_id` per node is accepted and used **only** to resolve the `umamusume` name and the Spark
 *   list that a Veteran already carries in its own run. It is a lookup key, not a stored fact: the
 *   payload stores the name and the Sparks the run recorded, never the library id, so a payload read
 *   back outside this app is still the read-back D-268 describes.
 *
 * Every figure is nullable on purpose. A Trainer who has read the rank of one parent and not the other
 * has a real half-filled screen, and rejecting it would push them to record a guess. The reference
 * corpus prices Spark payouts (`REFERENCE` §1.5.2, §1.5.3) and D-270 keeps unpriced figures out of
 * the tool; none of it is computed here.
 */
class StoreLegacySelectionRequest extends FormRequest
{
    /**
     * The lifecycle rule, enforced here rather than in the controller.
     *
     * `authorize()` runs before `rules()`, so a write aimed at a `Completed` or `Retired` run 404s
     * instead of redirecting with a field error. That ordering is the point: with the guard in the
     * controller the Form Request would answer first, and a Trainer posting to a finished run would
     * be told "legacies is required" — advice about a screen that should not be reachable at all.
     * `ADR-0010` Consequences §3 is where this rule is recorded, and `RecordVeteran` applies the same
     * lifecycle at the other end of the run's life.
     */
    public function authorize(): bool
    {
        $run = $this->route('run');

        // A missing or wrong-typed `run` is the router's problem, not this request's: 404 rather than
        // a 403, because there is no authorization surface here to refuse (`ARCHITECTURE.md` §8).
        abort_unless($run instanceof TrainingRun, 404);

        abort_unless($run->status === RunStatus::Active, 404);

        return true;
    }

    /**
     * A refused write goes back to the builder for the same run, not to the bare route name.
     *
     * `$redirectRoute` alone cannot say which run: `legacy.builder` is parameterised by `{run}`, and
     * the framework's default builder throws `UrlGenerationException` for a route missing a required
     * parameter — a 500 on a validation error, which is the worst possible answer to "you left a
     * field out". Naming the run here is also the right destination rather than merely a safe one: the
     * Trainer needs the graph back with their own entered values on it, which is the whole of
     * `AGENTS.md` §5's "a refusal says what was wrong" and WCAG 3.3.7's no-re-ask rule.
     */
    public function getRedirectUrl(): string
    {
        $run = $this->route('run');

        return $run instanceof TrainingRun
            ? route('legacy.builder', $run)
            : route('legacy.index');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'affinity' => ['nullable', 'string', Rule::in(LegacySelectionPayload::AFFINITY_GRADES)],
            'legacies' => ['present', 'array', 'max:'.LegacySelectionPayload::MAX_ANCESTORS],
            'legacies.*.legacy_id' => ['nullable', 'integer', 'exists:veterans,id'],
            // Unbounded on purpose, and this is the one place a reader may expect a `max` and not find
            // one. `ADR-0010` Consequences §3 refuses to cap a Legacy's own rank: the corpus states that
            // three stars guarantees a unique-skill Spark (§1.5.1) and states no ceiling on the
            // character's own count, so a `max` here would be a number the sources do not carry. A
            // negative rank is the only value that cannot be what a Trainer read.
            'legacies.*.rank' => ['nullable', 'integer', 'min:0'],
            // The letter rank the client prints, from the payload's own small vocabulary. The numeric
            // `rank` is the star count; this is the letter beside it, and neither is computed.
            'legacies.*.rank_letter' => ['nullable', 'string', Rule::in(LegacySelectionPayload::RANK_LETTERS)],
            'legacies.*.is_guest' => ['required', 'boolean'],
            'legacies.*.ancestors' => ['present', 'array', 'max:'.LegacySelectionPayload::MAX_ANCESTORS],
            // The grandparents' own Sparks, one list per ancestor. Optional and nullable: a names-only
            // ancestor is a real state, and a blank trailing row is discarded in `payload()` the same
            // way a parent's blank Spark is.
            'legacies.*.ancestors_sparks' => ['nullable', 'array'],
            'legacies.*.ancestors_sparks.*' => ['nullable', 'array'],
            'legacies.*.ancestors_sparks.*.*.kind' => ['required', 'string', Rule::in(LegacySelectionPayload::SPARK_KINDS)],
            'legacies.*.ancestors_sparks.*.*.target' => ['present', 'nullable', 'string', 'max:255'],
            'legacies.*.ancestors_sparks.*.*.stars' => ['nullable', 'integer', 'min:1', 'max:'.LegacySelectionPayload::MAX_SPARK_STARS],
            'legacies.*.sparks' => ['present', 'array'],
            'legacies.*.sparks.*.kind' => ['required', 'string', Rule::in(LegacySelectionPayload::SPARK_KINDS)],
            // The Spark's target category, a transient form field: it is validated here and never stored,
            // because the payload keeps the target string verbatim (`ADR-0010`) and the category exists so
            // a write can refuse `Stat -> Late Surger` at the boundary rather than only in the UI.
            'legacies.*.sparks.*.category' => ['nullable', 'string', Rule::in(LegacySelectionPayload::SPARK_CATEGORIES)],
            // `target` is the Spark's own subject as the client renders it: a stat name, an aptitude,
            // or a skill. The client spells those and this tool never rewrites them, so it is bounded
            // as free text and read back verbatim.
            'legacies.*.sparks.*.target' => ['present', 'nullable', 'string', 'max:255'],
            'legacies.*.sparks.*.stars' => ['nullable', 'integer', 'min:1', 'max:'.LegacySelectionPayload::MAX_SPARK_STARS],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'legacies.max' => 'A run names at most two Legacies, one per parent slot.',
            'legacies.*.ancestors.max' => 'Each parent names at most two ancestors of her own.',
            'legacies.*.sparks.*.stars.max' => 'A Spark carries one to three stars; three is the ceiling (REFERENCE §1.5.3).',
            'legacies.*.sparks.*.kind.in' => 'A Spark is Blue, Pink, Green, White or Scenario.',
        ];
    }

    /**
     * The two cross-field rules the shape needs and a per-field string cannot express, both
     * `ADR-0010` cases.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var list<array<string, mixed>> $legacies */
            $legacies = (array) $this->input('legacies', []);

            // `assertParentsDiffer()` is a whole-list rule, so it runs once rather than per entry.
            $this->assertParentsDiffer($validator, $legacies);

            foreach ($legacies as $index => $legacy) {
                $this->assertNoOwnTrainee($validator, (int) $index, $legacy);

                /** @var list<array<string, mixed>> $sparks */
                $sparks = (array) ($legacy['sparks'] ?? []);

                foreach ($sparks as $sparkIndex => $spark) {
                    $this->assertTargetMatchesCategory($validator, (int) $index, (int) $sparkIndex, $spark);
                }
            }
        });
    }

    /**
     * A Spark's target must belong to the category the Trainer picked, at the write boundary.
     *
     * The category is the form's own control and is not stored; a hand-made POST with no category keeps
     * the free-text target the rules already allow. When a category is posted, `Stat` is checked against
     * the stat matrix and `Aptitude` against the ten client dimensions. `Skill` is left as the verbatim
     * name the resolver reads from the skills catalogue (`ADR-0011`); the UI offers only catalogue rows,
     * and the stored target is the string the Trainer read, not an id.
     *
     * @param  array<string, mixed>  $spark
     */
    private function assertTargetMatchesCategory(Validator $validator, int $index, int $sparkIndex, array $spark): void
    {
        $category = $spark['category'] ?? null;
        $target = $spark['target'] ?? null;

        if (! is_string($category) || ! in_array($category, LegacySelectionPayload::SPARK_CATEGORIES, true)) {
            return;
        }

        if (! is_string($target) || $target === '') {
            return;
        }

        $allowed = match ($category) {
            'Stat' => array_values((array) config('scenarios.stat_order')),
            'Aptitude' => LegacySelectionPayload::SPARK_APTITUDES,
            default => null,
        };

        if ($allowed === null) {
            return;
        }

        if (! in_array($target, $allowed, true)) {
            $validator->errors()->add(
                "legacies.{$index}.sparks.{$sparkIndex}.target",
                "A {$category} Spark names one of ".implode(', ', $allowed)."; [{$target}] is not.",
            );
        }
    }

    /**
     * The payload exactly as the `legacy_selection` column stores it.
     *
     * The typed reader owns the accepted values, so this builds the array and hands it straight to
     * `fromArray()` rather than restating the vocabulary a second time here: one reader means a stored
     * row cannot fail its own validator (`StoreBuildTargetRequest::payload()` is the precedent, and
     * `BuildTargetPayload` its subject). A hand-made POST that got past the rules above therefore
     * still throws here, and the exception names the offending entry.
     *
     * @return array{legacies: list<array<string, mixed>>, affinity: string|null}
     */
    public function payload(): array
    {
        /** @var array{legacies?: mixed, affinity?: mixed} $validated */
        $validated = $this->validated();

        $legacies = [];

        /** @var list<array<string, mixed>> $posted */
        $posted = (array) ($validated['legacies'] ?? []);

        foreach ($posted as $legacy) {
            $sparks = [];

            /** @var list<array<string, mixed>> $postedSparks */
            $postedSparks = (array) ($legacy['sparks'] ?? []);

            foreach ($postedSparks as $spark) {
                $target = $spark['target'] === null || $spark['target'] === '' ? null : (string) $spark['target'];
                $stars = isset($spark['stars']) ? (int) $spark['stars'] : null;

                // The editor appends an empty trailing row on `Add Spark`, and a blank row left between two
                // real ones is the same draft row. A Spark carries at least a target or a star count, so a
                // row with neither is discarded here rather than stored as the bare kind the audit found on
                // the Preflight contract. This is the write boundary; the reader still holds a half-read
                // Spark, which is a real state (`ADR-0010` Consequences §3).
                if ($target === null && $stars === null) {
                    continue;
                }

                $sparks[] = ['kind' => (string) $spark['kind'], 'target' => $target, 'stars' => $stars];
            }

            // Ancestors are names, not ids (`ADR-0010` Consequences §2): a Trainer's grandparents
            // are frequently absent from the local catalogue, and an id column would be a second,
            // emptier version of the same fact.
            $ancestors = array_values(array_map(
                static fn (mixed $name): mixed => $name === null || $name === '' ? null : (string) $name,
                (array) ($legacy['ancestors'] ?? []),
            ));

            // The grandparents' Sparks, aligned one list per ancestor. A blank trailing row is
            // discarded here exactly as a parent's is, so an empty list is the same absence.
            /** @var list<mixed> $postedAncestorSparks */
            $postedAncestorSparks = array_values((array) ($legacy['ancestors_sparks'] ?? []));

            $ancestorSparks = [];

            foreach (array_keys($ancestors) as $ancestorIndex) {
                $list = [];

                foreach ((array) ($postedAncestorSparks[$ancestorIndex] ?? []) as $spark) {
                    $target = $spark['target'] === null || $spark['target'] === '' ? null : (string) $spark['target'];
                    $stars = isset($spark['stars']) ? (int) $spark['stars'] : null;

                    if ($target === null && $stars === null) {
                        continue;
                    }

                    $list[] = ['kind' => (string) $spark['kind'], 'target' => $target, 'stars' => $stars];
                }

                $ancestorSparks[] = $list;
            }

            $legacies[] = [
                'rank' => isset($legacy['rank']) ? (int) $legacy['rank'] : null,
                'rank_letter' => isset($legacy['rank_letter']) && $legacy['rank_letter'] !== ''
                    ? (string) $legacy['rank_letter']
                    : null,
                'is_guest' => (bool) $legacy['is_guest'],
                'ancestors' => $ancestors,
                'ancestors_sparks' => $ancestorSparks,
                'sparks' => $sparks,
            ];
        }

        $payload = LegacySelectionPayload::fromArray([
            'legacies' => $legacies,
            'affinity' => $validated['affinity'] ?? null,
        ]);

        return $payload->toArray();
    }

    /**
     * A parent may not be the trainee herself: the client refuses it (`REFERENCE` §1.5.4) and the tool
     * refuses it rather than storing a configuration the game would reject.
     *
     * @param  array<string, mixed>  $legacy
     */
    private function assertNoOwnTrainee(Validator $validator, int $index, array $legacy): void
    {
        $ownName = $this->ownTraineeName();

        if ($ownName === null || ! isset($legacy['legacy_id'])) {
            return;
        }

        $parent = Veteran::query()->with('trainingRun.umamusume')->find($legacy['legacy_id']);

        // `umamusume()` is a non-nullable `BelongsTo` on both models, so neither side is nullable and
        // a `?->` here would be dead code rather than defensiveness. The `null` case that does exist
        // is the absent Veteran: a `legacy_id` that no longer resolves, which the `exists` rule
        // catches separately but which must not become a type error on the way there.
        if ($parent === null) {
            return;
        }

        if ($parent->trainingRun->umamusume->name === $ownName) {
            $validator->errors()->add(
                "legacies.{$index}.legacy_id",
                'A run may not name its own trainee as a parent (REFERENCE §1.5.4).',
            );
        }
    }

    /**
     * The trainee a picked parent may not be, or null when nothing names one yet.
     *
     * The run's own trainee on the run-scoped write, and the setup draft's chosen trainee on the wizard's
     * ancestry step (`StoreDraftLegacyRequest`, which writes the same payload shape to the session). Both
     * entry points enforce one rule, the way `StoreBuildTargetRequest` clamps against the route's run when
     * there is one and against `SetupDraft::planningRun()` when there is not: a draft that let a Trainer
     * name the trainee as her own parent while the run screen refused it would store a choice the client
     * rejects, and Preflight would land it on the run with nothing to refuse it.
     */
    private function ownTraineeName(): ?string
    {
        $run = $this->route('run');

        if ($run instanceof TrainingRun) {
            return $run->umamusume->name;
        }

        $traineeId = SetupDraft::read()['umamusume_id'];

        return $traineeId === null ? null : Umamusume::query()->find($traineeId)?->name;
    }

    /**
     * The two parents must be different Umamusume (`REFERENCE` §1.5.4). The same library row twice is
     * the clearest case, so it is refused; two different Veterans of the same trainee is the
     * compatibility-zeroing case the client allows, and it is stored rather than policed, because
     * deciding what a repeat is worth is exactly the computation this slice bans.
     *
     * @param  list<array<string, mixed>>  $legacies
     */
    private function assertParentsDiffer(Validator $validator, array $legacies): void
    {
        $seen = [];

        foreach ($legacies as $index => $legacy) {
            if (! isset($legacy['legacy_id'])) {
                continue;
            }

            $id = (int) $legacy['legacy_id'];

            if (isset($seen[$id])) {
                $validator->errors()->add(
                    "legacies.{$index}.legacy_id",
                    'The two parents must be different Umamusume (REFERENCE §1.5.4).',
                );
            }

            $seen[$id] = true;
        }
    }
}
