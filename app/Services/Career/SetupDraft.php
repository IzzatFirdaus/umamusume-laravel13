<?php

declare(strict_types=1);

namespace App\Services\Career;

use App\Models\TrainingRun;
use App\Services\ScenarioCaps;

/**
 * The career setup wizard's draft (SCREEN-002 to SCREEN-008, `docs/proposals/frontend-development-plan.md` §8).
 *
 * **Why a session and not a run.** The wizard needs somewhere to hold a scenario before a trainee
 * exists and a trainee before a build target exists, and the obvious answer, an early-created run,
 * is the one this repo cannot afford: `RunStatus` has no draft case (`App\Enums\RunStatus` is Active,
 * Completed, Retired, and inventing a fourth needs owner approval), and an `Active` run is *live* in
 * two places already shipped. The Dashboard resumes the newest `Active` run
 * (`DashboardController::activeCareer()`), and the Legacy Lab builder opens on any `Active` run
 * (`LegacyController::builder()` aborts unless the status is Active). So a run created at step 1 and
 * abandoned at step 3 would leave a phantom career on the front door and a phantom inheritance target
 * in the Lab. `Retired` means "abandoned before completion", which is a statement about a career that
 * happened, not one that was intended. A session value leaves nothing behind, which is the property the
 * run cannot have.
 *
 * **What that costs, stated plainly.** Two tabs share one session, so they share one draft and the
 * last write wins: one Trainer, one setup in progress. A session that expires or a different browser
 * loses the draft, and the wizard starts again. Both are accepted for a six-step local flow.
 *
 * The draft is a bag of entered facts and nothing else. It computes no reachability and no
 * recommendation, and it never writes to the database: the run is created once, at Preflight
 * (`ADR-0020` §3).
 */
final class SetupDraft
{
    public const SESSION_KEY = 'career.setup';

    /**
     * The step 1 and step 2 choices, always with both keys present so a reader never tests for a
     * missing array offset.
     *
     * This stays the two-key view it was when only those two keys existed, because SCREEN-002 and
     * SCREEN-003's tests pin that shape. The later steps' keys are read through `buildTarget()`,
     * `legacySelection()`, `legacyParents()` and `deck()`, each with its own stable default, so the
     * rule this method exists for (no caller testing for a missing offset) holds for every key in the
     * bag rather than only for the first two.
     *
     * @return array{scenario: string|null, umamusume_id: int|null}
     */
    public static function read(): array
    {
        $raw = self::bag();

        $scenario = $raw['scenario'] ?? null;
        $traineeId = $raw['umamusume_id'] ?? null;

        return [
            // A draft must name a scenario the matrix actually composes. A stale key left by a config
            // change reads as no scenario rather than as a scenario that renders a blank card.
            'scenario' => is_string($scenario)
                && array_key_exists($scenario, (array) config('scenarios.scenarios'))
                ? $scenario
                : null,
            'umamusume_id' => is_int($traineeId) ? $traineeId : null,
        ];
    }

    /**
     * Whether this draft has ever carried a trainee key, including one cleared to null.
     *
     * Key presence distinguishes "never chosen" from "explicitly cleared". `umamusume_id` is written only
     * by a deliberate selection (`TraineeSelectController::store()` or its `carry()` entry point) or an
     * explicit clear; never write it as a default. Read raw like `buildTarget()`, so `read()`'s pinned
     * two-key shape above does not change.
     */
    public static function traineeRecorded(): bool
    {
        return array_key_exists('umamusume_id', self::bag());
    }

    /**
     * Whether this draft has ever carried a scenario key, including one cleared to null.
     *
     * `read()` always returns both step-1 keys, so it cannot tell "no choice recorded" from
     * "explicitly cleared", and only the second may let the Default-scenario preference stand in.
     * Read raw like `buildTarget()` for the same reason: the two-key contract above stays pinned.
     *
     * The `scenario` key is written only by an explicit selection or an explicit clear; never write
     * it as a default.
     */
    public static function scenarioRecorded(): bool
    {
        return array_key_exists('scenario', self::bag());
    }

    /**
     * @param  array<string, mixed>  $patch
     * @return array{scenario: string|null, umamusume_id: int|null}
     */
    public static function write(array $patch): array
    {
        // Merged against the raw session bag, not against `read()`, which narrows to the two
        // first-step keys. Going through `read()` would drop a `build_target` step 3 stored the
        // moment step 1 or 2 writes again, and a Trainer who goes back to re-pick a scenario would
        // silently lose the target. `read()` still validates on the way out, so nothing stale this
        // merge keeps is ever handed to a page.
        session([self::SESSION_KEY => array_merge((array) session(self::SESSION_KEY, []), $patch)]);

        return self::read();
    }

    /**
     * The build target step 3 stored, or null before one is entered.
     *
     * Read raw from the bag rather than through `read()`, whose two-key contract SCREEN-002 and
     * SCREEN-003's tests pin. The shape is `StoreBuildTargetRequest::payload()`'s, validated on the
     * way in; a raw read means a later config change can never make this step throw, it can only
     * make the page render what was actually entered.
     *
     * @return array<string, mixed>|null
     */
    public static function buildTarget(): ?array
    {
        return self::stored('build_target');
    }

    /**
     * The costume form step 2 stored, or null when the Trainer chose none.
     *
     * Read raw like the later steps' keys rather than through `read()`, whose two-key shape SCREEN-002 and
     * SCREEN-003's tests pin. A form is a fact the run carries (`training_runs.character_card_id`), so a
     * draft naming one has to survive every later step's write, which is why `write()` merges rather than
     * replaces.
     */
    public static function characterCardId(): ?int
    {
        $id = self::bag()['character_card_id'] ?? null;

        return is_int($id) ? $id : null;
    }

    /**
     * The ancestry step 4 stored, or null before one is entered.
     *
     * The same raw-read style as `buildTarget()`, and the same reason. The shape is
     * `LegacySelectionPayload::toArray()` exactly — `{ legacies, affinity }` and nothing else — because
     * Preflight writes this value into `training_runs.legacy_selection`, where
     * `TrainingRun::legacySelection()` runs it back through `fromArray()` and refuses an unknown key. A
     * draft that carried one extra convenience field would be a payload the run cannot store.
     *
     * @return array<string, mixed>|null
     */
    public static function legacySelection(): ?array
    {
        $selection = self::stored('legacy_selection');

        return is_array($selection) ? $selection : null;
    }

    /**
     * The two library rows the ancestry step picked, as Veteran ids in slot order, or `[null, null]`
     * before a pick.
     *
     * They are a draft key of their own rather than a field inside `legacy_selection` for the reason
     * that method states: the payload has no slot for a parent's identity, because the run carries it in
     * `training_runs.inheritance_parent_a_id` and `_b_id` (`ADR-0010` Decision). A career with no run row
     * has nowhere else to put it, so the draft holds it and Preflight writes it to those two columns
     * alongside the json — the same pair `LegacyController::update()` writes together for a live run.
     *
     * @return array{0: int|null, 1: int|null}
     */
    public static function legacyParents(): array
    {
        /** @var list<mixed> $raw */
        $raw = (array) (self::bag()['legacy_parents'] ?? []);

        return [
            is_int($raw[0] ?? null) ? $raw[0] : null,
            is_int($raw[1] ?? null) ? $raw[1] : null,
        ];
    }

    /**
     * The deck step 5 stored, or null before one is entered.
     *
     * Six rows in position order, each `{ position, support_card_id, ownership }`, and
     * `support_card_id` null where the Trainer left the slot on "Not equipped". It is a draft shape, not
     * a table shape: `deck_slots` holds one row per equipped card, flag included (`ADR-0023`, D3), so the
     * empty slots exist only here, until Preflight creates the rows it can create.
     *
     * @return list<array{position: int, support_card_id: int|null, ownership: string|null}>|null
     */
    public static function deck(): ?array
    {
        /** @var list<mixed> $raw */
        $raw = (array) (self::bag()['deck'] ?? []);

        $slots = [];

        foreach ($raw as $slot) {
            // A row that is not a record is not a slot: a bag edited by hand or left by an older shape
            // must not reach the page as a half-row the slot loop then indexes blindly.
            if (! is_array($slot)) {
                continue;
            }

            $slots[] = [
                'position' => (int) ($slot['position'] ?? 0),
                'support_card_id' => isset($slot['support_card_id']) ? (int) $slot['support_card_id'] : null,
                'ownership' => isset($slot['ownership']) && is_string($slot['ownership']) ? $slot['ownership'] : null,
            ];
        }

        return $slots === [] ? null : $slots;
    }

    /**
     * The steps whose screens this session has opened, as the step bar's completed marking reads
     * them (owner ruling 2026-10-13: "completed" means visited, not written to the draft - a
     * Trainer who opens a step and leaves without saving still made the visit, and the bar should
     * say so). It rides the raw bag like `caps` and `deck` do, so `read()`'s two-key shape stays
     * pinned and `reset()` clears it with the draft.
     *
     * @return list<int>
     */
    public static function visited(): array
    {
        /** @var list<mixed> $raw */
        $raw = (array) (self::bag()['visited'] ?? []);

        return array_values(array_filter($raw, is_int(...)));
    }

    /**
     * The three choices the wizard carries forward, for the strip that states them on every step.
     *
     * One entry per choice, always present, so a caller renders three fixed positions rather than
     * testing for missing keys. `value` is null when the choice has not been made, which the strip
     * renders as "not chosen"; `url` is the step that makes the choice, so a filled entry links to
     * where it can be changed.
     *
     * **The values are the labels the pages already show, not raw keys.** A scenario is named by the
     * same `label` its card carries, and the target by the style and distance the Trainer picked,
     * because the strip is a reminder rather than a second place to learn the schema. The trainee's
     * name comes from the same row the run will reference.
     *
     * **A null is "not chosen", not an absence.** This is a draft state, not a record missing a
     * value: nothing failed and no source is silent. DESIGN.md section 5's absence vocabulary (an
     * `N/A` carrying a reason) governs values the record does not carry, so it does not apply here,
     * and the strip says "not chosen" in muted text rather than presenting a disclosure.
     *
     * @return list<array{label: string, value: string|null, url: string|null}>
     */
    public static function summary(): array
    {
        $choice = self::read();

        $scenarioLabel = null;

        if ($choice['scenario'] !== null) {
            $scenario = (array) config('scenarios.scenarios.'.$choice['scenario'], []);
            $label = $scenario['label'] ?? null;
            $scenarioLabel = is_string($label) && $label !== '' ? $label : (string) $choice['scenario'];
        }

        $traineeName = null;

        if ($choice['umamusume_id'] !== null) {
            $traineeName = \App\Models\Umamusume::query()
                ->whereKey($choice['umamusume_id'])
                ->value('name');
            $traineeName = is_string($traineeName) ? $traineeName : null;
        }

        $target = self::buildTarget();
        $targetSummary = null;

        if (is_array($target)) {
            $parts = array_filter([
                is_string($target['style'] ?? null) ? $target['style'] : null,
                is_string($target['distance'] ?? null) ? $target['distance'] : null,
            ], is_string(...));

            $targetSummary = $parts === [] ? null : implode(' · ', $parts);
        }

        return [
            [
                'label' => 'Scenario',
                'value' => $scenarioLabel,
                'url' => $scenarioLabel === null ? null : '/career/setup/scenario',
            ],
            [
                'label' => 'Trainee',
                'value' => $traineeName,
                'url' => $traineeName === null ? null : '/career/setup/trainee',
            ],
            [
                'label' => 'Target',
                'value' => $targetSummary,
                'url' => $targetSummary === null ? null : '/career/setup/target',
            ],
        ];
    }

    /**
     * The steps that have data stored in the draft (slice 4.1 three-state fix).
     * Only steps with actual saved data show "done".
     *
     * @return list<int>
     */
    public static function saved(): array
    {
        $bag = self::bag();
        $saved = [];

        // Step 1: scenario
        if (isset($bag['scenario']) && is_string($bag['scenario']) && $bag['scenario'] !== '') {
            $saved[] = 1;
        }

        // Step 2: trainee (umamusume_id)
        if (isset($bag['umamusume_id']) && is_int($bag['umamusume_id'])) {
            $saved[] = 2;
        }

        // Step 3: build target (caps)
        if (isset($bag['caps']) && is_array($bag['caps']) && !empty($bag['caps'])) {
            $saved[] = 3;
        }

        // Step 4: legacy selection
        if (isset($bag['legacy_selection']) && is_array($bag['legacy_selection']) && !empty($bag['legacy_selection'])) {
            $saved[] = 4;
        }

        // Step 5: deck
        if (isset($bag['deck']) && is_array($bag['deck']) && !empty($bag['deck'])) {
            $saved[] = 5;
        }

        // Step 6: preflight - always saved when run is created
        // This is handled by the preflight step itself

        return array_values(array_filter($saved, is_int(...)));
    }

    /**
     * Record that a step's screen was opened. Idempotent per step; a non-integer already in the
     * bag is dropped on the way out rather than cast, the same rule `caps()` applies.
     */
    public static function markVisited(int $step): void
    {
        $visited = self::visited();

        if (in_array($step, $visited, true)) {
            return;
        }

        session([self::SESSION_KEY => array_merge(self::bag(), ['visited' => [...$visited, $step]])]);
    }

    /**
     * The raw session bag, unnormalised.
     *
     * @return array<string, mixed>
     */
    private static function bag(): array
    {
        /** @var array<string, mixed> $raw */
        $raw = (array) session(self::SESSION_KEY, []);

        return $raw;
    }

    /**
     * One stored key, or null when the draft has never carried it.
     *
     * @return array<string, mixed>|null
     */
    private static function stored(string $key): ?array
    {
        $value = self::bag()[$key] ?? null;

        return is_array($value) ? $value : null;
    }

    public static function reset(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * The draft scenario, and any ceilings the target step stored, as an unsaved model, so a step can
     * ask the ceiling owner about a career that does not exist in the database yet.
     *
     * `ScenarioCaps::forRun()` reads `hasScenario()` and `scenarioKey()` for the scenario's own
     * arithmetic and the four `cap_*` columns for anything entered (`ADR-0025`), so it needs no
     * persisted row. That is what lets the Build Target step clamp its inputs against the chosen
     * scenario and against the ceilings entered beside it before Preflight creates the run, without
     * either weakening the clamp or inventing a draft row.
     *
     * Null only when the draft carries neither a scenario nor a ceiling. A scenario-less draft that
     * holds ceilings returns the model anyway, because an entered ceiling applies to a run that names
     * no scenario: `forRun()` answers the base cap for it (no bonus is lent to a choice nobody made)
     * and then overlays the entered figure. Returning null there would drop the Trainer's own reading
     * from the page's bound and from the clamp — the defect the browser verification caught.
     */
    public static function planningRun(): ?TrainingRun
    {
        $draft = self::read();
        $caps = self::caps();

        if ($draft['scenario'] === null && $caps === []) {
            return null;
        }

        return new TrainingRun(array_merge(
            $draft['scenario'] === null ? [] : ['scenario' => $draft['scenario']],
            $caps,
        ));
    }

    /**
     * The ceilings step 3 stored, keyed by the columns the run will hold them in, or `[]` before any.
     *
     * A draft key of its own rather than a field inside `build_target`, for the reason `legacyParents()`
     * states: `BuildTargetPayload::assertKeys()` refuses a seventh key, and a ceiling is a column on the
     * run (`ADR-0025`), not part of the target the advisor reads. A stored figure that is not an integer
     * is dropped rather than cast, so a hand-edited bag cannot reach a page as a half-number.
     *
     * @return array<string, int|null>
     */
    public static function caps(): array
    {
        /** @var array<string, mixed> $raw */
        $raw = (array) (self::bag()['caps'] ?? []);

        $caps = [];

        foreach (ScenarioCaps::ENTERED_CAPS as $column) {
            if (! array_key_exists($column, $raw)) {
                continue;
            }

            $value = $raw[$column];
            $caps[$column] = is_int($value) ? $value : null;
        }

        return $caps;
    }

    /**
     * The chosen scenario's label from the matrix, or null when the draft has no scenario. Never a
     * storage key (D-240), and never the baseline's name standing in for a choice nobody made
     * (`TrainingRun::scenarioKey()`'s fallback is a composition device, not a fact).
     */
    public static function scenarioLabel(): ?string
    {
        $scenario = self::read()['scenario'];

        if ($scenario === null) {
            return null;
        }

        $label = config('scenarios.scenarios.'.$scenario.'.label');

        return is_string($label) ? $label : $scenario;
    }
}
