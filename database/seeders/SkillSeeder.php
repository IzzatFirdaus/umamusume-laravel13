<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Skill;
use App\Services\DataPipeline\NameNormalizer;
use Illuminate\Database\Seeder;

/**
 * Development skill rows, named from the same field the import reads.
 *
 * These used to be `Illustrative Speed Skill` and `Illustrative Recovery Skill`. Those self-labelled as
 * placeholders in this file, but the label did not survive the trip to the screen: `runs/show` feeds this
 * table straight into the skill select, so a Trainer recording what their trainee actually learned was
 * choosing from invented strings (D-20, D-76, gate G-16).
 *
 * **What changed on 2026-09-29, and why the list is nine now.** `ADR-0011` measured the source document
 * and found the client string is `name_en` while `enname` is a literal rendering of the Japanese — the two
 * differ on 535 of the 623 `[Global]` rows. Ten names came from a publisher's skill list under D-210, and
 * three of them are not what the client prints: `Come What May, See Ya Later!` is **`Come What May`**
 * (`Prepared to Die` is the rendering), `Playtime's Over` is **`Playtime's Over!`** with the trailing
 * mark, and **`Traightaways` has no counterpart in the source under either field** — the nearest real
 * family is `Straightaway Adept`, `Straightaway Acceleration` and the `… Straightaways ◎/○` distance
 * skills. It is dropped rather than swapped for one of those, because choosing between them would be
 * guessing at a provenance nobody has evidenced, which is the same move `KI-5` was filed against.
 *
 * Every name below is now the `name_en` of a row that is available on `[Global]`, with the export id in
 * the comment. That is the point of the correction, beyond the copy: the seeder and the importer agree on
 * the field, so `StoreSkills` adopts these rows by name and stamps their `export_id` instead of growing a
 * second row for the same skill.
 *
 * What is deliberately absent: `sp_cost` is left null and `type` unset. The cost of each of these is in
 * the source and belongs to the import, which also carries the provenance columns — a hand-entered 180
 * here would be a number the screen cannot trace. `is_unique` stays at its default for the same reason:
 * seven of these nine are ordinary skills and `564 Escapades` is a unique skill (rarity 5), and the
 * column that says which is set by the import, not by a seeder's recollection.
 */
class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $normalizer = app(NameNormalizer::class);

        // name => the source row it is the client name of. Ids are the export's own (`skills.id`),
        // recorded so a later reader can check the string against the document rather than against this file.
        $skills = [
            'Gourmand',              // 201351, rarity 2
            'Unstoppable',           // 202371, rarity 2
            'Up-Tempo',              // 200722, rarity 1
            'Come What May',          // 201701, rarity 2 — was "Come What May, See Ya Later!"
            'In Body and Mind',       // 200511, rarity 2
            'Homestretch Haste',      // 200512, rarity 1
            'Professor of Curvature', // 200331, rarity 2
            "Playtime's Over!",       // 201661, rarity 1 — was missing the trailing mark
            '564 Escapades',         // 110071, rarity 5 (a unique skill)
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

        // `updateOrCreate` adds and updates; it never removes. Without this the retired names survive a
        // re-seed and keep appearing in the run-detail select — the invented strings from the first
        // version, and `Traightaways`, which the source does not carry under either name field. On a
        // fresh install these are no-ops; on a development database they are the whole fix.
        Skill::query()
            ->where('name', 'like', 'Illustrative %')
            ->orWhere('name', 'Come What May, See Ya Later!')
            ->orWhere('name', 'Playtime\'s Over')
            ->orWhere('name', 'Traightaways')
            ->delete();
    }
}
