<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AliasLanguage;
use App\Enums\ReleaseStatus;
use App\Models\Umamusume;
use App\Models\UmamusumeAlias;
use App\Services\DataPipeline\NameNormalizer;
use Illuminate\Database\Seeder;

/**
 * Illustrative development data (PRD OQ-2 pending): two well-known Umamusume so the
 * catalog UI and Alias-tier matching have something to render on a fresh install.
 *
 * This class stays deliberately small. The engine-owned roster — 68 promoted
 * trainees with their aptitudes, debut dates, `external_ref` and provenance — is built
 * by `UmamusumeRosterSeeder` from the committed source body, and the two rows below
 * exist to be *adopted* by it: both names are in that document, so the roster seeder
 * matches them on the first pass and fills in what this file cannot know.
 *
 * Nothing here carries provenance, and nothing here should: a `data_sources` row is
 * what the Provenance panel reads, and this file has no source to attribute. Any fact
 * that needs a citation belongs to the roster seeder, not to this one.
 */
class UmamusumeSeeder extends Seeder
{
    public function run(): void
    {
        $normalizer = app(NameNormalizer::class);

        $characters = [
            [
                'slug' => 'special-week',
                'name' => 'Special Week',
                'name_ja' => 'スペシャルウィーク',
                'release_status' => ReleaseStatus::GlobalReleased,
            ],
            [
                'slug' => 'tokai-teio',
                'name' => 'Tokai Teio',
                'name_ja' => 'トウカイテイオー',
                'release_status' => ReleaseStatus::GlobalReleased,
            ],
        ];

        foreach ($characters as $character) {
            $umamusume = Umamusume::updateOrCreate(
                ['slug' => $character['slug']],
                [
                    ...$character,
                    'match_key' => $normalizer->normalize($character['name']),
                    'is_manual' => false,
                ],
            );

            UmamusumeAlias::firstOrCreate([
                'umamusume_id' => $umamusume->id,
                'alias' => $character['name_ja'],
                'language' => AliasLanguage::Japanese->value,
            ]);
        }
    }
}
