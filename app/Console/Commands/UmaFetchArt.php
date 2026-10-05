<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DataPipeline\ArtworkMirror;
use Illuminate\Console\Command;

/**
 * Fills the local artwork mirror `ADR-0021` authorizes: one polite pass over the ids the catalog
 * already stores, writing the files a Trainer's screens would otherwise have to render without.
 *
 * It is deliberately not part of `uma:fetch`. That command walks document sources and parses them;
 * this one walks binary paths and stores bytes, and the two differ in everything a source entry
 * needs except the allowlist, the delay and the timeout. Nothing here is scheduled
 * (`PRD.md` OQ-3 is open for `uma:fetch` and this command is manual by the same reasoning), and a
 * second run costs nothing because files already on disk are skipped unless `--refetch` says
 * otherwise.
 */
class UmaFetchArt extends Command
{
    protected $signature = 'uma:fetch-art
        {--kind= : One artwork set to mirror; omit for every set config declares}
        {--refetch : Request files the mirror already holds}
        {--dry-run : Report what would be requested and send nothing}';

    protected $description = 'Mirror id-addressable catalog artwork into storage/app/private/artwork (gitignored)';

    public function handle(ArtworkMirror $mirror): int
    {
        $declared = $mirror->kinds();

        if ($declared === []) {
            $this->error('No artwork paths are declared in config("uma.sources.gametora-artwork.paths").');

            return self::FAILURE;
        }

        $requested = $this->option('kind');
        $kinds = is_string($requested) && $requested !== '' ? [$requested] : $declared;

        $unknown = array_values(array_diff($kinds, $declared));

        if ($unknown !== []) {
            $this->error('Unknown artwork kind '.implode(', ', $unknown).'. Declared: '.implode(', ', $declared));

            return self::FAILURE;
        }

        foreach ($kinds as $kind) {
            /** @var string $kind */
            $counts = $mirror->mirror(
                $kind,
                skipExisting: ! (bool) $this->option('refetch'),
                dryRun: (bool) $this->option('dry-run'),
            );

            $verb = (bool) $this->option('dry-run') ? 'would request' : 'wrote';

            $this->line(sprintf(
                '%s: %d ids, %s %d, already on disk %d, unresolved %d%s',
                $kind,
                $counts['ids'],
                $verb,
                $counts['fetched'],
                $counts['already'],
                $counts['unresolved'],
                $counts['bytes'] > 0 ? ', '.round($counts['bytes'] / 1024 / 1024, 1).' MB' : '',
            ));

            if ($counts['unresolved'] > 0) {
                // Not a failure: the mirror is partial by nature and every file this pass could not
                // resolve leaves its slot rendering the text-only row. The status code is in the log.
                $this->warn('  Unresolved ids stay unmirrored; see the log for each status.');
            }
        }

        if (! (bool) $this->option('dry-run')) {
            $this->info('Files under storage/app/private/artwork, audit trail in artwork/manifest.json. Manual only: do not schedule this.');
        }

        return self::SUCCESS;
    }
}
