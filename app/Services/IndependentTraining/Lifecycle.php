<?php

declare(strict_types=1);

namespace App\Services\IndependentTraining;

use App\Enums\IndependentTrainingPolicy;
use App\Enums\RunStatus;
use App\Models\IndependentTrainingRun;
use App\Models\TrainingRun;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * The Independent Training lifecycle (§1.1.7): start a 50-minute idle window, end it by completion
 * or abandonment, and let the scheduler close expired windows.
 *
 * The mode is one-at-a-time — "You cannot make another training session. You must give up/delete the
 * current one" (Game8, 2026-06-30) — and the run plays itself out with the app closed, so completion
 * is driven by a scheduled tick (`CompleteIndependentTraining`) rather than a browser-side timer.
 */
final class Lifecycle
{
    public const DURATION_MINUTES = 50;

    /**
     * Start a session, fixing the three inputs §1.1.7 names. `rotation` null means the scenario's
     * target races only; `priority_skills` is capped at ten.
     *
     * @param  array{policy: string, priority_skills?: list<int>, rotation?: mixed|null}  $config
     */
    public function start(TrainingRun $run, array $config): IndependentTrainingRun
    {
        $policy = IndependentTrainingPolicy::tryFrom($config['policy']);

        if ($policy === null) {
            throw new InvalidArgumentException('Unknown Independent Training policy.');
        }

        $priority = $config['priority_skills'] ?? [];

        if (count($priority) > IndependentTrainingRun::MAX_PRIORITY_SKILLS) {
            throw new InvalidArgumentException('At most 10 priority skills may be fixed.');
        }

        // The one-at-a-time rule is enforced HERE as well as by the unique index, because the index
        // says only 'one overlay per run' while this says 'nobody else's window may be open either'.
        // `canStart()` had no production caller: it was covered by a passing feature test and reached
        // by nothing, so the rule lived in a docblock. A caller that skips the request-level check
        // (a seeder, a test helper, a future console command) now gets a refusal, not a second session.
        if (! $this->canStart($run)) {
            throw new InvalidArgumentException('Another Independent Training session is already open, or this run already carries one.');
        }

        return IndependentTrainingRun::create([
            'training_run_id' => $run->id,
            'policy' => $policy,
            'priority_skills' => $priority === [] ? null : $priority,
            'rotation' => $config['rotation'] ?? null,
            'started_at' => now(),
            'completes_at' => now()->addMinutes(self::DURATION_MINUTES),
        ]);
    }

    /**
     * §1.1.7's one-at-a-time rule: a run already carrying an idle overlay cannot start a second, and
     * no run can start while another session's window is open. The 20-per-day cap is the DailyCap
     * service's concern, layered in Slice 2.5, not this class's.
     */
    public function canStart(TrainingRun $run): bool
    {
        if ($run->isIndependent()) {
            return false;
        }

        return ! IndependentTrainingRun::query()
            ->whereNull('completed_at')
            ->whereNull('abandoned_at')
            ->where('completes_at', '>', now())
            ->exists();
    }

    /**
     * End a session by completion: run the simulation, stamp the finish, and mark the run Completed.
     * Idempotent — a run the scheduler already closed, or one the Trainer abandoned, is not re-stamped.
     */
    public function complete(IndependentTrainingRun $itr): TrainingRun
    {
        // One transaction and one conditional stamp. The pair of writes is the defect: the overlay and
        // the parent run are two rows, and a failure between them left the overlay stamped complete
        // while the career stayed Active — a state nothing ever re-scans, because `completeExpired()`
        // only looks at rows still holding both nulls. The guard reads the in-memory model, which is
        // not a lock: two overlapping scheduler ticks could both pass it. So the stamp is the guard,
        // and it is taken with a conditional UPDATE whose affected-row count says whether this caller
        // is the one that ended the session.
        return DB::transaction(function () use ($itr): TrainingRun {
            $claimed = IndependentTrainingRun::query()
                ->whereKey($itr->getKey())
                ->whereNull('completed_at')
                ->whereNull('abandoned_at')
                ->update([
                    'completed_at' => now(),
                    'result' => (new Simulator)->run($itr),
                    'updated_at' => now(),
                ]);

            if ($claimed === 0) {
                // Somebody else already ended it. Answer with the run as it stands rather than
                // re-stamping, which is what the idempotency rule promises the caller.
                return $itr->trainingRun()->firstOrFail();
            }

            $itr->refresh();

            $itr->trainingRun()->update(['status' => RunStatus::Completed]);

            return $itr->trainingRun()->firstOrFail();
        });
    }

    /**
     * End a session by abandonment: stamp the give-up and mark the run Retired. Idempotent.
     */
    public function abandon(IndependentTrainingRun $itr): void
    {
        // Same shape as `complete()` and for the same reason: two rows, one transaction, and a
        // conditional stamp so the racing tick cannot retire a session this caller already ended.
        DB::transaction(function () use ($itr): void {
            $claimed = IndependentTrainingRun::query()
                ->whereKey($itr->getKey())
                ->whereNull('completed_at')
                ->whereNull('abandoned_at')
                ->update(['abandoned_at' => now(), 'updated_at' => now()]);

            if ($claimed === 0) {
                return;
            }

            $itr->refresh();

            $itr->trainingRun()->update(['status' => RunStatus::Retired]);
        });
    }

    /**
     * Close every session whose 50-minute window has passed and which no ending has stamped yet.
     *
     * @return int the number of sessions completed
     */
    public function completeExpired(): int
    {
        $expired = IndependentTrainingRun::query()
            ->whereNull('completed_at')
            ->whereNull('abandoned_at')
            ->where('completes_at', '<=', now())
            ->get();

        foreach ($expired as $itr) {
            $this->complete($itr);
        }

        return $expired->count();
    }
}
