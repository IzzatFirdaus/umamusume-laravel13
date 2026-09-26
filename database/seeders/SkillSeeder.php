<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Skill;
use App\Services\DataPipeline\NameNormalizer;
use Illuminate\Database\Seeder;

/**
 * Illustrative development data only. Skill facts arrive through the fetch engine
 * with provenance (PRD FR-D), never from this seeder in production use.
 */
class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $normalizer = app(NameNormalizer::class);

        $skills = [
            ['name' => 'Illustrative Speed Skill', 'sp_cost' => 120],
            ['name' => 'Illustrative Recovery Skill', 'sp_cost' => 60],
        ];

        foreach ($skills as $skill) {
            Skill::updateOrCreate(
                ['name' => $skill['name']],
                [
                    'match_key' => $normalizer->normalize($skill['name']),
                    'sp_cost' => $skill['sp_cost'],
                    'is_unique' => false,
                ],
            );
        }
    }
}
