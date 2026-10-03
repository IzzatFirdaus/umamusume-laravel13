<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\PromoteMatchedRecord;
use App\Enums\ReleaseStatus;
use App\Models\MatchCandidate;
use App\Services\DataPipeline\CrossReferenceMatcher;
use App\Services\DataPipeline\Parsers\GametoraCharacterParser;
use Illuminate\Database\Seeder;

/**
 * Builds the trainee roster from the committed `gametora-characters` body.
 *
 * **Why this exists rather than a `PipelineRunner` call.** `uma:fetch` sends an
 * unmatched character to the review queue (FR-B-3), which is right for a live
 * publication: the Trainer is asked to confirm each new arrival. On a fresh
 * database *every* row is unmatched — the table is empty — so replaying the fetch
 * rule through a seed would file all 135 as pending candidates and populate nothing.
 * A seeder has a different job: it has to leave the same end state a completed
 * import would have left. So the matcher is still consulted, but only to find the
 * row a re-run should update; "no match" means *create*, not *review*.
 *
 * **Scope is the document's own answer, not this class's.** `gametora-characters`
 * publishes `release_status` per trainee and this is a Global-client tool (PRD
 * FR-A-1), so `GlobalReleased` rows are promoted and `JapanOnly` rows are filed as
 * pending candidates — which is exactly what a Trainer running `uma:fetch` against
 * a populated roster would end up with. Measured on the committed body: 68 of 135
 * promoted, 67 queued. Promoting all 135 instead is a one-line change to
 * `inScope()` below, and is the only thing that would need to change.
 *
 * **Provenance is unchanged.** Rows land through `PromoteMatchedRecord`, so every
 * trainee still gets its `data_sources` row — the detail page's Provenance panel
 * reads that table and not `umamusume`, which carries no provenance columns of its
 * own. `snapshot_path` points at the committed body, so the panel names a file that
 * is actually in the repository.
 *
 * `ADR-0003` Amendment R3 assigns reference data to the fetch engine rather than to a
 * seeder. This class deliberately overrides that, at the owner's request: the fetched
 * snapshots are gitignored, so without a committed body a rebuild has no data at all
 * and the reference tables are empty until someone re-runs the network. The override
 * is a *second* path, not a replacement — `uma:fetch` and `uma:reparse` still work and
 * still own anything newer than the committed document.
 */
class UmamusumeRosterSeeder extends Seeder
{
    use ReadsCommittedSource;

    public const SOURCE_KEY = 'gametora-characters';

    public function run(): void
    {
        $config = config('uma.sources.'.self::SOURCE_KEY);

        if (! is_array($config)) {
            return;
        }

        $committed = $this->readCommittedSource(self::SOURCE_KEY, $config);

        if ($committed === null) {
            return;
        }

        $records = app(GametoraCharacterParser::class)->parse($committed['body']);

        $matcher = app(CrossReferenceMatcher::class);
        $promote = app(PromoteMatchedRecord::class);

        $counts = ['promoted' => 0, 'updated' => 0, 'queued' => 0, 'skipped' => 0];

        foreach ($records as $record) {
            $match = $matcher->match($record['name']);

            if (! $this->inScope($record)) {
                // The triple below is the unique index `2026_10_01_124051` enforces, so it is the identity
                // this row is keyed by. Creating instead of upserting meant a second `db:seed` collided with
                // its own first run, aborted here, and left the three seeders after this one unrun (KI-56).
                // `status` is deliberately absent: a re-run must not reopen a candidate the Trainer already
                // decided, and the column's own default files a new one as Pending.
                MatchCandidate::updateOrCreate([
                    'source_key' => self::SOURCE_KEY,
                    'external_ref' => $record['external_ref'] ?? null,
                    'proposed_match_key' => $matcher->matchKey($record['name']),
                ], [
                    'proposed_name' => $record['name'],
                    'proposed_name_ja' => $record['name_ja'] ?? null,
                    'suggested_umamusume_id' => $match['umamusume']?->id,
                    'match_tier' => $match['tier']->value,
                    'payload' => [...$record, 'url' => $config['url']],
                    'created_by_fetch_at' => now(),
                ]);
                $counts['queued']++;

                continue;
            }

            $result = $promote->handle(
                record: $record,
                existing: $match['umamusume'],
                sourceKey: self::SOURCE_KEY,
                url: $config['url'],
                snapshotPath: $committed['relative'],
                confidence: $match['confidence'],
                sourceTimezone: $config['timezone'] ?? null,
            );

            if ($result['skipped']) {
                $counts['skipped']++;
            } elseif ($result['created']) {
                $counts['promoted']++;
            } else {
                $counts['updated']++;
            }
        }

        $this->command?->info(sprintf(
            '  %s: %d promoted, %d updated, %d queued for review, %d skipped',
            self::SOURCE_KEY,
            $counts['promoted'],
            $counts['updated'],
            $counts['queued'],
            $counts['skipped'],
        ));
    }

    /**
     * Whether the source itself says this trainee is playable on the Global client.
     *
     * @param  array<string, mixed>  $record
     */
    private function inScope(array $record): bool
    {
        return ReleaseStatus::tryFrom((string) ($record['release_status'] ?? ''))
            !== ReleaseStatus::JapanOnly;
    }
}
