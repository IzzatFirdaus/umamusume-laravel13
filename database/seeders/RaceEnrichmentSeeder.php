<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

/**
 * The offline consumer for the four race-enrichment bodies (functional item 7).
 *
 * `SourceDocumentSeeder` deliberately skips parsers that implement
 * `RaceEnrichmentSourceParser`: their records are not catalogue rows, they are lookup maps joined
 * by `uma:enrich-races` into the `race_catalog_slots` those other sources create. Without this
 * seeder a fresh clone had the bodies on disk and no path from them to the columns, so tracks,
 * fan figures and rewards stayed null until someone ran a networked fetch.
 *
 * Must run after `SourceDocumentSeeder`, which creates the slots to enrich.
 */
class RaceEnrichmentSeeder extends Seeder
{
    public function run(): void
    {
        $status = Artisan::call('uma:enrich-races', ['--from-seed' => true]);

        if ($status !== 0) {
            throw new \RuntimeException('Offline race enrichment failed: '.Artisan::output());
        }
    }
}
