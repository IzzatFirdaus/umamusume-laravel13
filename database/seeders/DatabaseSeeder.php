<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Order is load-bearing and each step exists for a reason a later one depends on.
 *
 *   UmamusumeSeeder        two illustrative trainees, so a brand-new install has
 *                          something to render before any document is read
 *   UmamusumeRosterSeeder  the committed roster — creates the rows whose
 *                          `external_ref` the card and profile sources resolve against
 *   SkillSeeder            nine illustrative skill names, carried so
 *                          SourceDocumentSeeder can adopt them by name
 *   SourceDocumentSeeder   every committed body through PipelineRunner; must follow
 *                          the roster, and must follow SkillSeeder for that adoption
 *   ScenarioSlotSeeder     URA finale goal races joined from three committed exports
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UmamusumeSeeder::class,
            UmamusumeRosterSeeder::class,
            SkillSeeder::class,
            SourceDocumentSeeder::class,
            ScenarioSlotSeeder::class,
        ]);
    }
}
