<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DataPipeline\PipelineRunner;
use App\Services\DataPipeline\SourceFetcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Entry point of the data-fetching engine (PRD FR-B-1). Runs per source:
 * atomic lock -> fetch + snapshot (SourceFetcher) -> parse -> match -> promote
 * or review (PipelineRunner). A failed fetch never mutates existing rows.
 */
class UmaFetch extends Command
{
    protected $signature = 'uma:fetch {source? : Key from config("uma.sources"); omit to fetch all declared sources}';

    protected $description = 'Fetch declared sources, snapshot, parse, cross-reference, and promote or queue for review';

    public function handle(SourceFetcher $fetcher, PipelineRunner $pipeline): int
    {
        /** @var array<string, array<string, mixed>> $sources */
        $sources = config('uma.sources', []);

        if ($sources === []) {
            $this->error('No sources declared in config("uma.sources"). See PRD OQ-2.');

            return self::FAILURE;
        }

        $requested = $this->argument('source');
        $keys = $requested !== null ? [(string) $requested] : array_keys($sources);

        foreach ($keys as $key) {
            if (! isset($sources[$key])) {
                $this->error("Unknown source '{$key}'. Declared: ".implode(', ', array_keys($sources)));

                return self::FAILURE;
            }

            /*
             * An asset entry has no parser and no document, so there is nothing here to run. It is
             * declared in this array only so the SSRF allowlist covers the host it serves
             * (ADR-0021 Decision 4), and `uma:fetch-art` is its reader. "Has a parser" is the same
             * discriminator SourceDocumentSeeder:56 already uses, so one rule decides both.
             */
            if (! isset($sources[$key]['parser'])) {
                if ($requested !== null) {
                    $this->error("'{$key}' declares no parser; an asset host is read by uma:fetch-art, not uma:fetch.");

                    return self::FAILURE;
                }

                $this->line("'{$key}' is an asset host with no document to parse; skipped.");

                continue;
            }

            $this->fetchOne($key, $sources[$key], $fetcher, $pipeline);
        }

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $sourceConfig
     */
    private function fetchOne(string $key, array $sourceConfig, SourceFetcher $fetcher, PipelineRunner $pipeline): void
    {
        $lock = Cache::lock("uma-fetch:{$key}", (int) config('uma.fetch.lock_timeout', 60));

        if (! $lock->get()) {
            $this->warn("Source '{$key}' is already being fetched; skipping.");

            return;
        }

        try {
            $fetched = $fetcher->fetch($key, $sourceConfig);

            if ($fetched === null) {
                $this->error("Fetch failed for '{$key}'. Existing data untouched (NFR-2).");

                return;
            }

            if ($fetched['unchanged']) {
                $this->info("'{$key}' unchanged since last snapshot; nothing written.");

                return;
            }

            if ($fetched['manifest_fallback']) {
                $this->warn("  Manifest unavailable for '{$key}'; using the pinned URL, whose document may be stale (KI-24).");
            }

            // Provenance records the URL actually fetched, not the one declared. For a
            // manifest-resolved source the two differ, and "which document did this come from" is
            // the question a later reader has to be able to answer from the row alone.
            $counts = $pipeline->run(
                $key,
                [...$sourceConfig, 'url' => $fetched['url']],
                $fetched['body'],
                $fetched['snapshot_path'],
            );

            $this->info(sprintf(
                "'%s': %d updated, %d created, %d skipped (manual or unresolved), %d to review.",
                $key,
                $counts['updated'],
                $counts['created'],
                $counts['skipped'],
                $counts['review'],
            ));
        } finally {
            $lock->release();
        }
    }
}
