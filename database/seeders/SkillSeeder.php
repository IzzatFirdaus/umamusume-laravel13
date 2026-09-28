<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Skill;
use App\Services\DataPipeline\NameNormalizer;
use Illuminate\Database\Seeder;

/**
 * Development skill rows, now carrying real `[Global]` names.
 *
 * These used to be `Illustrative Speed Skill` and `Illustrative Recovery Skill`.
 * Those self-labelled as placeholders in this file, but the label did not survive the
 * trip to the screen: `runs/show` feeds this table straight into the skill select, so
 * a Trainer recording what their trainee actually learned was choosing from invented
 * strings (D-20, D-76, gate G-16).
 *
 * The names come from the evidenced pool in `docs/design-research/CONSTRAINTS.md`
 * D-210, sourced there from Game8. Nothing here is composed: no name is altered,
 * shortened or "corrected" on the way in, which is why `Traightaways` and
 * `Come What May, See Ya Later!` are spelled as D-210 records them.
 *
 * What is deliberately absent: `sp_cost` is left null and `type` unset. D-210 evidences
 * the names and nothing else, so attaching the old invented 120 and 60 to real skills
 * would have been the worse failure — a fabricated number wearing a credible name
 * (D-227). Cost, type and uniqueness arrive with the fetch engine and its provenance
 * columns (PRD FR-D). `is_unique` is a non-nullable boolean with no consumer in any
 * view or query, so its default here asserts nothing anyone can see.
 */
class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $normalizer = app(NameNormalizer::class);

        $skills = [
            'Gourmand',
            'Unstoppable',
            'Up-Tempo',
            'Come What May, See Ya Later!',
            'In Body and Mind',
            'Homestretch Haste',
            'Professor of Curvature',
            'Traightaways',
            "Playtime's Over",
            '564 Escapades',
        ];

        foreach ($skills as $name) {
            Skill::updateOrCreate(
                ['name' => $name],
                [
                    'match_key' => $normalizer->normalize($name),
                    'sp_cost' => null,
                    'is_unique' => false,
                ],
            );
        }

        // `updateOrCreate` adds and updates; it never removes. Without this the two
        // invented names survive a re-seed and keep appearing in the run-detail select,
        // which is the exact thing this change is meant to clear — on a fresh install
        // it is a no-op, and on the development database it is the whole fix.
        Skill::query()->where('name', 'like', 'Illustrative %')->delete();
    }
}
