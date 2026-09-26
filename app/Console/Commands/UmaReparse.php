<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DataPipeline\PipelineRunner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Replays parse -> match -> promote from the newest stored snapshot with zero
 * network (ARCHITECTURE §6). The repair tool when a parser is fixed but the
 * source body is already on disk.
 */
class UmaReparse extends Command
{
    protected $signature = 'uma:reparse {source : Key from config("uma.sources")}';

    protected $description = 'Re-run parse -> match -> promote from the newest stored snapshot, zero network';

    public function handle(PipelineRunner $pipeline): int
    {
        $key = (string) $this->argument('source');

        /** @var array<string, array<string, mixed>> $sources */
        $sources = config('uma.sources', []);

        if (! isset($sources[$key])) {
            $this->error("Unknown source '{$key}'.");

            return self::FAILURE;
        }

        $directory = "snapshots/{$key}";

        if (! Storage::disk('local')->exists($directory)) {
            $this->error("No snapshot stored for '{$key}' yet. Run uma:fetch first.");

            return self::FAILURE;
        }

        $newest = collect(Storage::disk('local')->allFiles($directory))
            ->filter(fn (string $path): bool => str_ends_with($path, '.html'))
            ->sortBy(fn (string $path): int => Storage::disk('local')->lastModified($path))
            ->last();

        if ($newest === null) {
            $this->error("No snapshot files under {$directory}.");

            return self::FAILURE;
        }

        $snapshot = $newest;
        $body = (string) Storage::disk('local')->get($snapshot);

        $counts = $pipeline->run($key, $sources[$key], $body, $snapshot);

        $this->info(sprintf(
            "'%s' reparsed from %s: %d updated, %d created, %d skipped (manual), %d to review.",
            $key,
            $snapshot,
            $counts['updated'],
            $counts['created'],
            $counts['skipped'],
            $counts['review'],
        ));

        return self::SUCCESS;
    }
}
