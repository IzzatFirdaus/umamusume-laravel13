<?php

declare(strict_types=1);

namespace App\Services\Career;

use App\Models\TrainingRun;

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
     * The ancestry step 4 stored, or null before one is entered.
     *
     * The same raw-read style as `buildTarget()`, and the same reason. The shape is
     * `LegacySelectionPayload::toArray()` exactly — `{ legacies, affinity }` and nothing else — because
     * Preflight writes this value into `training_runs.legacy_selection`, where
     * `TrainingRun::legacySelection()` runs it back through `fromArray()` and refuses an unknown key. A
     * draft that carried one extra convenience field would be a payload the run cannot store.
     *
     * @return array{legacies: list<array<string, mixed>>, affinity: string|null}|null
     */
    public static function legacySelection(): ?array
    {
        /** @var array<string, mixed>|null $selection */
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
     * The draft scenario as an unsaved model, so a step can ask the ceiling owner about a career that
     * does not exist in the database yet.
     *
     * `ScenarioCaps::forRun()` reads only `hasScenario()` and `scenarioKey()`
     * (`app/Services/ScenarioCaps.php:81-100`), so it needs no persisted row. That is what lets the
     * Build Target step clamp its inputs against the chosen scenario before Preflight creates the run,
     * without either weakening the clamp or inventing a draft row. Null when no scenario is chosen:
     * `forRun(null)` then refuses to lend any scenario's cap bonus, which is the correct answer for a
     * career with no scenario.
     */
    public static function planningRun(): ?TrainingRun
    {
        $draft = self::read();

        if ($draft['scenario'] === null) {
            return null;
        }

        return new TrainingRun(['scenario' => $draft['scenario']]);
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
