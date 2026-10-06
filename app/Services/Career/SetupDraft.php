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
     * The draft, always with both keys present so a reader never tests for a missing array offset.
     *
     * @return array{scenario: string|null, umamusume_id: int|null}
     */
    public static function read(): array
    {
        /** @var array<string, mixed> $raw */
        $raw = (array) session(self::SESSION_KEY, []);

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
        /** @var array<string, mixed> $raw */
        $raw = (array) session(self::SESSION_KEY, []);

        $target = $raw['build_target'] ?? null;

        return is_array($target) ? $target : null;
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
