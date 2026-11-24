<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Advisor\AdvisorIndex;
use App\Services\Advisor\EmbeddingProvider;
use App\Services\LangfuseTraceService;
use Illuminate\Console\Command;

/**
 * Builds or refreshes the Advisor's local corpus index (Advisor AI slice, Phase D).
 *
 * Zero network for the lexical half: it reads tracked markdown from this repository. The vector half
 * talks to Ollama on loopback and is skipped, with a stated reason, when that daemon or its model is
 * unavailable — an index without vectors is still a working lexical index, and refusing to build one
 * because a local model is not loaded would make the whole feature depend on an optional component.
 *
 * `--vectors` is the opt-in: a full pass over the six configured documents measured about 25 minutes
 * of embedding on this machine, so it does not belong in an ordinary run and is never triggered by a
 * page render.
 */
class UmaAdvisorIndex extends Command
{
    protected $signature = 'uma:advisor-index
        {--vectors : Also compute and store local embeddings (slow; requires Ollama)}
        {--fresh : Delete the index file first and rebuild from nothing}
        {--status : Report what the index holds without changing it}';

    protected $description = 'Build the Advisor corpus index from the documented mechanics corpus';

    public function handle(AdvisorIndex $index, EmbeddingProvider $embeddings, LangfuseTraceService $telemetry): int
    {
        $startNs = $this->nanoTime();

        try {
            $path = storage_path((string) config('advisor_ai.retrieval.index_path'));

            if ((bool) $this->option('fresh') && is_file($path) && ! unlink($path)) {
                $this->error("Cannot delete the index at {$path}.");

                return self::FAILURE;
            }

            if ((bool) $this->option('status')) {
                return $this->report($index);
            }

            /** @var list<string> $sources */
            $sources = (array) config('advisor_ai.retrieval.sources');
            $written = 0;
            $skipped = 0;

            foreach ($sources as $relative) {
                $absolute = base_path($relative);

                if (! is_file($absolute)) {
                    $this->warn("Missing source, not indexed: {$relative}");

                    continue;
                }

                $body = (string) file_get_contents($absolute);

                if ($index->indexDocument($relative, $body)) {
                    $written++;
                    $this->line("indexed  {$relative}");
                } else {
                    $skipped++;
                    $this->line("unchanged {$relative}");
                }
            }

            $this->info("Documents: {$written} indexed, {$skipped} unchanged. Chunks: {$index->chunkCount()}.");

            if ((bool) $this->option('vectors')) {
                return $this->buildVectors($index, $embeddings, $telemetry, $startNs);
            }

            $this->line('Vectors: not requested. Pass --vectors to compute them.');

            $this->recordIndexTrace($telemetry, $startNs, $written, null);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->recordIndexTrace($telemetry, $startNs, 0, $e->getMessage());

            throw $e;
        }
    }

    /**
     * Embeds every chunk that has no vector for the configured model yet.
     *
     * Incremental by construction: `unvectorised()` returns only the gaps, so re-running after a
     * document edit embeds the new passages and leaves the rest alone.
     */
    private function buildVectors(AdvisorIndex $index, EmbeddingProvider $embeddings, LangfuseTraceService $telemetry, int $startNs): int
    {
        $identity = $embeddings->identity();

        if (! $embeddings->available()) {
            $this->warn('Ollama or the configured embedding model is unavailable; vectors were not built.');

            return self::SUCCESS;
        }

        $batch = max(1, (int) config('advisor_ai.embeddings.batch'));
        $total = 0;

        while (true) {
            $pending = $index->unvectorised($identity['model'], $batch);

            if ($pending === []) {
                break;
            }

            $vectors = $embeddings->embed(array_map(
                static fn (array $chunk): string => $chunk['heading']."\n\n".$chunk['text'],
                $pending,
            ));

            if ($vectors === null) {
                $this->warn('Embedding request failed mid-run; stopping with the vectors already stored.');

                break;
            }

            foreach ($pending as $offset => $chunk) {
                $index->putVector($chunk['id'], $identity['model'], $vectors[$offset]);
                $total++;
            }

            $this->line("embedded {$total} chunk(s)");
        }

        $this->info("Vectors stored: {$total} using {$identity['model']}.");

        $this->recordIndexTrace($telemetry, $startNs, $total, null);

        return self::SUCCESS;
    }

    /**
     * Reports what the index holds, so a stale or empty index is visible before anyone reads it as
     * an absence of knowledge.
     */
    private function report(AdvisorIndex $index): int
    {
        $path = storage_path((string) config('advisor_ai.retrieval.index_path'));

        if (! is_file($path)) {
            $this->warn("No index at {$path}. Run uma:advisor-index to build it.");

            return self::SUCCESS;
        }

        $this->line('Index: '.$path);
        $this->line('Chunks: '.$index->chunkCount());

        /** @var list<string> $sources */
        $sources = (array) config('advisor_ai.retrieval.sources');

        foreach ($sources as $relative) {
            $absolute = base_path($relative);
            $onDisk = is_file($absolute) ? hash('sha256', (string) file_get_contents($absolute)) : null;
            $indexed = $index->indexedHash($relative);

            $state = match (true) {
                $onDisk === null => 'missing on disk',
                $indexed === null => 'never indexed',
                $onDisk === $indexed => 'current',
                default => 'STALE - re-run uma:advisor-index',
            };

            $this->line(sprintf('  %-16s %s', $state, $relative));
        }

        return self::SUCCESS;
    }

    /**
     * Record an Advisor indexing trace span to Langfuse.
     *
     * Fail-open: a telemetry failure must never affect the command result.
     */
    private function recordIndexTrace(LangfuseTraceService $telemetry, int $startNs, int $documents, ?string $error): void
    {
        try {
            $telemetry->trace([
                'name' => 'advisor.index',
                'kind' => 3, // SPAN_KIND_INTERNAL
                'startTimeUnixNano' => $startNs,
                'endTimeUnixNano' => $this->nanoTime(),
                'attributes' => array_merge([
                    ['key' => 'trainerdesk.operation', 'value' => ['stringValue' => 'advisor.index']],
                    ['key' => 'trainerdesk.documents_indexed', 'value' => ['intValue' => (string) $documents]],
                    ['key' => 'langfuse.name', 'value' => ['stringValue' => 'advisor.index']],
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
