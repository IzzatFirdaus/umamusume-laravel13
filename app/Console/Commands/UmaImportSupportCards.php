<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DataPipeline\PipelineRunner;
use Illuminate\Console\Command;

/**
 * Imports the support-card catalogue and its effect dictionary from the committed source bodies,
 * with zero network (ADR-0014; PRD FR-B).
 *
 * **This is `PipelineRunner` fed from `database/seeders/data/`, not a second importer.** Each source
 * goes through the same runner `uma:fetch` and `uma:reparse` call, with the same config entry, so the
 * parsers, the store actions, the provenance stamps and the `is_manual` stop sign are the shipping
 * code rather than a command-only copy that can drift from them.
 *
 * The gap it closes is that neither existing command reaches a committed body. `uma:fetch` needs the
 * network, and `uma:reparse` replays `storage/app/private/snapshots`, which is gitignored by design,
 * so on a fresh clone it answers "No snapshot stored for 'gametora-support-cards' yet. Run uma:fetch
 * first." A Trainer who wants the deck picker populated without a fetch had `migrate --seed`, which
 * rebuilds every table to get at two. This command imports the two support datasets and nothing else.
 *
 * **Idempotent, and that is the point of the counts it prints.** Both store actions upsert on the
 * export's own id (`support_id`, then the dictionary's `id`), so a second run reports updates with
 * zero creates rather than colliding with the unique index (PRD FR-B-5). A card flagged `is_manual`
 * is skipped, never overwritten (PRD FR-B-4); the effect dictionary has no such column and says so in
 * `StoreSupportEffects`.
 *
 * **Provenance records the pinned URL, because that is the document the committed body is.** For a
 * manifest-resolved source `uma:fetch` stamps the URL it actually requested, which may carry a newer
 * hash than the pin. Here the body is the committed file, whose first eight sha256 hex digits equal
 * the hash in its own name, so the pin is a true statement about it and `ADR-0003` R3's provenance
 * holds. `uma:reparse` faces the same approximation and documents it in its own class comment.
 */
class UmaImportSupportCards extends Command
{
    /**
     * The declared source keys this command imports, in dependency order: the dictionary after the
     * cards it names.
     *
     * @var list<string>
     */
    private const SOURCES = ['gametora-support-cards', 'gametora-support-effects'];

    protected $signature = 'uma:import:support-cards';

    protected $description = 'Import support cards and the effect dictionary from the committed bodies, zero network';

    public function handle(PipelineRunner $pipeline): int
    {
        /** @var array<string, array<string, mixed>> $sources */
        $sources = config('uma.sources', []);

        $failed = false;

        foreach (self::SOURCES as $key) {
            if (! isset($sources[$key])) {
                $this->error("Source '{$key}' is not declared in config(\"uma.sources\").");
                $failed = true;

                continue;
            }

            if (! $this->importOne($key, $sources[$key], $pipeline)) {
                $failed = true;
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $sourceConfig
     */
    private function importOne(string $key, array $sourceConfig, PipelineRunner $pipeline): bool
    {
        $file = $sourceConfig['seed_file'] ?? null;

        if (! is_string($file) || $file === '') {
            $this->error("Source '{$key}' declares no seed_file, so nothing was imported.");

            return false;
        }

        $relative = 'database/seeders/data/'.$file;
        $path = database_path('seeders/data/'.$file);

        if (! is_file($path)) {
            // A missing body is reported and the other source still runs: a partial import is
            // recoverable by re-running this command, while an aborted one leaves the Trainer with
            // cards and no dictionary to label them with. Same decision `SourceDocumentSeeder` makes.
            $this->error("Source '{$key}' names seed_file '{$relative}', which is not present.");

            return false;
        }

        $body = file_get_contents($path);

        if ($body === false || $body === '') {
            $this->error("Source '{$key}' body '{$relative}' is unreadable or empty.");

            return false;
        }

        $counts = $pipeline->run($key, $sourceConfig, $body, $relative);

        $this->info(sprintf(
            "'%s' from %s: %d created, %d updated, %d skipped (manual), %d to review.",
            $key,
            $relative,
            $counts['created'],
            $counts['updated'],
            $counts['skipped'],
            $counts['review'],
        ));

        return true;
    }
}
