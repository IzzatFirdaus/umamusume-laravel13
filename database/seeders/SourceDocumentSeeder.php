<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\DataPipeline\PipelineRunner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Rebuilds every reference table from the committed source bodies, offline.
 *
 * **This is `PipelineRunner` with the network removed, not a reimplementation.**
 * Each source is handed to the same runner `uma:fetch` and `uma:reparse` use, with
 * the same source config, so the parsers, the store actions, the provenance stamps,
 * the `is_manual` stop signs, the card-grain and profile-grain ref resolution and
 * the review-queue branch are all the shipping code rather than a seed-only copy that
 * can drift. Every table except `umamusume` is populated from here, which is why
 * `UmamusumeRosterSeeder` is the only seeder with its own import logic.
 *
 * **Order is the config's order and it is load-bearing.** `gametora-characters` runs
 * first so the trainees exist before the card and profile sources resolve their
 * `external_ref`; measured on the committed bodies, cards resolve 68 of 68 refs and
 * profiles 98 of 105, and every one of the 7 misses is a trainee the document names
 * that this catalogue does not track. `gametora-skills` is independent and is placed
 * where it is only because it is cheap to run once.
 *
 * **Skills adopt the illustrative rows.** `SkillSeeder` inserts nine names with no
 * `export_id`, and `StoreSkills::find()` matches on the display name exactly once
 * while `export_id IS NULL`. That is the existing, tested path for turning a
 * hand-written row into an attributed one, so `SkillSeeder` must stay ahead of this
 * class in `DatabaseSeeder` for those nine rows to be adopted rather than duplicated.
 *
 * Idempotent by construction: every store action upserts on the source's own
 * identity, so a second `migrate --seed` updates rather than duplicates. A row the
 * Trainer flagged `is_manual` is skipped by the same guard the live fetch uses.
 */
class SourceDocumentSeeder extends Seeder
{
    use ReadsCommittedSource;

    public function run(): void
    {
        $runner = app(PipelineRunner::class);
        $sources = config('uma.sources');

        if (! is_array($sources)) {
            return;
        }

        foreach ($sources as $sourceKey => $sourceConfig) {
            if (! is_array($sourceConfig) || ! isset($sourceConfig['parser'])) {
                continue;
            }

            // The character source is imported by UmamusumeRosterSeeder, which has to
            // decide what "no match" means. Routing it here would file the whole roster
            // as pending review candidates.
            if ($sourceKey === UmamusumeRosterSeeder::SOURCE_KEY) {
                continue;
            }

            $committed = $this->readCommittedSource($sourceKey, $sourceConfig);

            if ($committed === null) {
                continue;
            }

            /*
             * One unimportable source must not cost the Trainer the other four.
             *
             * `ScenarioSlotSeeder` sets this precedent for the tier-label extraction (R76):
             * a missing input is reported out loud and seeding continues, because a partial
             * catalogue is recoverable by re-running the command while a half-written seed
             * leaves the database in a state nobody can reason about. So this catches rather
             * than propagates, and says which source failed and why.
             */
            try {
                $counts = $runner->run($sourceKey, $sourceConfig, $committed['body'], $committed['relative']);
            } catch (Throwable $e) {
                Log::error(
                    "Seeding source '{$sourceKey}' from '{$committed['relative']}' failed and was skipped: "
                    .$e->getMessage(),
                    ['exception' => $e],
                );
                $this->command?->warn(sprintf('  %s: FAILED (%s) — skipped', $sourceKey, $e->getMessage()));

                continue;
            }

            $this->command?->info(sprintf(
                '  %s: %d created, %d updated, %d skipped, %d to review',
                $sourceKey,
                $counts['created'],
                $counts['updated'],
                $counts['skipped'],
                $counts['review'],
            ));
        }
    }
}
