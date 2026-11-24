<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\IndependentTraining\Lifecycle;
use App\Services\LangfuseTraceService;
use Illuminate\Console\Command;

/**
 * The scheduler tick that plays out Independent Training (§1.1.7): the mode runs with the app closed,
 * so completion is a scheduled close of every session whose 50-minute window has passed, not a
 * browser-side timer.
 */
class CompleteIndependentTraining extends Command
{
    protected $signature = 'independent-training:complete';

    protected $description = 'Complete Independent Training sessions whose 50-minute window has passed';

    public function handle(Lifecycle $lifecycle, LangfuseTraceService $telemetry): int
    {
        $startNs = $this->nanoTime();

        try {
            $completed = $lifecycle->completeExpired();

            $this->info("Completed {$completed} independent training session(s).");

            $this->recordTrainingTrace($telemetry, $startNs, $completed, null);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->recordTrainingTrace($telemetry, $startNs, 0, $e->getMessage());

            throw $e;
        }
    }

    /**
     * Record an Independent Training completion trace span to Langfuse.
     *
     * Fail-open: a telemetry failure must never affect the command result.
     */
    private function recordTrainingTrace(LangfuseTraceService $telemetry, int $startNs, int $completed, ?string $error): void
    {
        try {
            $telemetry->trace([
                'name' => 'independent_training.complete',
                'kind' => 3, // SPAN_KIND_INTERNAL
                'startTimeUnixNano' => $startNs,
                'endTimeUnixNano' => $this->nanoTime(),
                'attributes' => array_merge([
                    ['key' => 'trainerdesk.operation', 'value' => ['stringValue' => 'independent_training.complete']],
                    ['key' => 'trainerdesk.sessions_completed', 'value' => ['intValue' => (string) $completed]],
                    ['key' => 'langfuse.name', 'value' => ['stringValue' => 'independent_training.complete']],
                    ['key' => 'langfuse.type', 'value' => ['stringValue' => 'span']],
                ], $error ? [
                    ['key' => 'langfuse.level', 'value' => ['stringValue' => 'error']],
                    ['key' => 'exception.message', 'value' => ['stringValue' => $error]],
                ] : []),
            ]);
        } catch (\Throwable) {
            // Telemetry must never break the application.
        }
    }

    /**
     * Current time as Unix-epoch OTLP nanoseconds.
     */
    private function nanoTime(): int
    {
        $now = new \DateTimeImmutable;

        return (int) $now->format('U') * 1_000_000_000 + (int) $now->format('u') * 1_000;
    }
}
