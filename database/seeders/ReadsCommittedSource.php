<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Support\Facades\Log;

/**
 * Reads a source's committed body out of `database/seeders/data/`.
 *
 * One loader for every seed source, so the "is it there?" decision and the "what
 * path do we stamp as provenance?" answer cannot drift apart between them — those
 * two are the same string, and a seeder that computed them separately would eventually
 * write a `snapshot_path` that does not resolve.
 *
 * Fetched snapshots live in `storage/app/private/snapshots`, which is gitignored by
 * design (`ARCHITECTURE-ESSENTIALS.md`). This deliberately does not read them: seeding
 * has to work on a fresh clone with no network and no local snapshot history, so the
 * committed copy is the only body it will ever accept.
 */
trait ReadsCommittedSource
{
    /**
     * @param  array<string, mixed>  $sourceConfig
     * @return array{body: string, path: string, relative: string}|null Null when the
     *                                                                  source declares no `seed_file`, or declares one that is not committed.
     */
    protected function readCommittedSource(string $sourceKey, array $sourceConfig): ?array
    {
        $file = $sourceConfig['seed_file'] ?? null;

        if (! is_string($file) || $file === '') {
            Log::warning(
                "Source '{$sourceKey}' declares no seed_file, so seeding skips it. A fresh "
                .'database would leave its tables empty until uma:fetch runs.'
            );

            return null;
        }

        $relative = 'database/seeders/data/'.$file;
        $path = database_path('seeders/data/'.$file);

        if (! is_file($path)) {
            Log::warning(
                "Source '{$sourceKey}' names seed_file '{$relative}', which is not present, so "
                .'seeding skips it and its tables stay empty until uma:fetch runs.'
            );

            return null;
        }

        $body = file_get_contents($path);

        if ($body === false || $body === '') {
            Log::warning("Source '{$sourceKey}' seed_file '{$relative}' is unreadable or empty.");

            return null;
        }

        return ['body' => $body, 'path' => $path, 'relative' => $relative];
    }
}
