<?php

declare(strict_types=1);

use App\Models\RaceCatalogSlot;
use Database\Seeders\RaceEnrichmentSeeder;
use Database\Seeders\SourceDocumentSeeder;

/*
 * Functional item 7: a fresh clone seeds race tracks, fan curves and rewards with no network.
 * The committed bodies exist, and EnrichRaces already parses them; what was missing was an offline
 * path from those bodies into race_catalog_slots. This pins that path through the seeder, so a
 * migrate:fresh --seed on a machine with no snapshot history still produces enriched rows.
 */
it('enriches race slots from committed bodies with no snapshot or network', function (): void {
    $this->seed(SourceDocumentSeeder::class);
    $this->seed(RaceEnrichmentSeeder::class);

    $enriched = RaceCatalogSlot::query()
        ->whereNotNull('track_name')
        ->orWhereNotNull('reward_summary')
        ->get();

    expect($enriched)->not->toBeEmpty()
        ->and($enriched->first()->fetched_at)->not->toBeNull()
        ->and($enriched->first()->source_url)->toStartWith('https://');
});
