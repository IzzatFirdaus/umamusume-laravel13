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
 * Engine-owned facts arrive only through uma:fetch with provenance, never from here.
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
