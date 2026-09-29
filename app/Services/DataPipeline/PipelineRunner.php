<?php

declare(strict_types=1);

namespace App\Services\DataPipeline;

use App\Actions\PromoteMatchedRecord;
use App\Actions\StoreRaceCatalogSlots;
use App\Actions\StoreSkills;
use App\Enums\CandidateStatus;
use App\Enums\MatchTier;
use App\Models\MatchCandidate;
use App\Services\DataPipeline\Contracts\RaceCatalogSourceParser;
use App\Services\DataPipeline\Contracts\SkillSourceParser;
use App\Services\DataPipeline\Contracts\SourceParser;
use Illuminate\Support\Facades\Cache;

/**
 * Parse -> normalize -> match -> promote|review stage runner (PRD FR-B-2).
 * Shared by uma:fetch (network) and uma:reparse (snapshots only).
 */
final class PipelineRunner
{
    public function __construct(
        private readonly CrossReferenceMatcher $matcher,
        private readonly PromoteMatchedRecord $promote,
        private readonly StoreRaceCatalogSlots $storeRaceCatalog,
        private readonly StoreSkills $storeSkills,
    ) {}

    /**
     * @param  array{url: string, parser: class-string, timezone?: string|null}  $sourceConfig
     *                                                                                          `parser` is one of the three declared contracts — `SourceParser` for anything a
     *                                                                                          Trainer's rows get cross-referenced against, `RaceCatalogSourceParser` and
     *                                                                                          `SkillSourceParser` for reference data that is not a display name. Which one decides
     *                                                                                          the route, so no source key is ever special-cased here.
     * @return array{updated: int, created: int, skipped: int, review: int}
     */
    public function run(string $sourceKey, array $sourceConfig, string $body, ?string $snapshotPath): array
    {
        /** @var class-string $parserClass */
        $parserClass = $sourceConfig['parser'];

        /*
         * Reference data that is not a display name does not get cross-referenced.
         *
         * Without this branch a race-catalogue source would fall into the loop
         * below, where every one of its 410 rows fails to match an Umamusume and is
         * filed as a pending review candidate — turning the review queue into 410
         * races. The parser's own contract is what distinguishes the two kinds, so
         * nothing here keys off a source name.
         *
         * Both uma:fetch and uma:reparse arrive through this method, so the branch
         * is written once.
         */
        if (is_a($parserClass, RaceCatalogSourceParser::class, true)) {
            /** @var RaceCatalogSourceParser $parser */
            $parser = app($parserClass);
            $stored = $this->storeRaceCatalog->handle(
                $parser->parse($body),
                $sourceConfig['url'],
                $snapshotPath,
                $sourceConfig['timezone'] ?? null,
            );

            return [...$stored, 'review' => 0];
        }

        /*
         * Skills take the same route for the same reason, and the flood this one prevents is
         * the bigger one: read as `SourceParser` records, all 1,910 rows would fail to match an
         * Umamusume and land in `match_candidates` as pending review rows — a review queue
         * holding every skill in the game, filed as a character nobody could identify.
         *
         * Two branches of the same eight lines is duplication, and it stays because each writer
         * owns a different grain: a race row collapses on (scenario, year, month, half, title),
         * a skill row on the source's own id. A shared abstraction would be a third thing to
         * read before either could be understood, and it would have no second implementation
         * to justify it.
         */
        if (is_a($parserClass, SkillSourceParser::class, true)) {
            /** @var SkillSourceParser $parser */
            $parser = app($parserClass);
            $stored = $this->storeSkills->handle(
                $parser->parse($body),
                $sourceConfig['url'],
                $snapshotPath,
                $sourceConfig['timezone'] ?? null,
            );

            return [...$stored, 'review' => 0];
        }

        /** @var SourceParser $parser */
        $parser = app($sourceConfig['parser']);

        $counts = ['updated' => 0, 'created' => 0, 'skipped' => 0, 'review' => 0];

        foreach ($parser->parse($body) as $record) {
            $match = $this->matcher->match($record['name']);

            if ($match['tier'] === MatchTier::Exact || $match['tier'] === MatchTier::Alias) {
                $result = $this->promote->handle(
                    record: $record,
                    existing: $match['umamusume'],
                    sourceKey: $sourceKey,
                    url: $sourceConfig['url'],
                    snapshotPath: $snapshotPath,
                    confidence: $match['confidence'],
                    sourceTimezone: $sourceConfig['timezone'] ?? null,
                );

                if ($result['skipped']) {
                    $counts['skipped']++;
                } elseif ($result['created']) {
                    $counts['created']++;
                } else {
                    $counts['updated']++;
                }

                continue;
            }

            MatchCandidate::create([
                'source_key' => $sourceKey,
                'external_ref' => $record['external_ref'] ?? null,
                'proposed_name' => $record['name'],
                'proposed_name_ja' => $record['name_ja'] ?? null,
                'proposed_match_key' => $this->matcher->matchKey($record['name']),
                'suggested_umamusume_id' => $match['umamusume']?->id,
                'match_tier' => $match['tier']->value,
                'status' => CandidateStatus::Pending->value,
                'payload' => [...$record, 'url' => $sourceConfig['url']],
                'created_by_fetch_at' => now(),
            ]);

            $counts['review']++;
        }

        if ($counts['created'] > 0 || $counts['updated'] > 0) {
            if (! Cache::add('catalog:version', 0, 3600)) {
                Cache::increment('catalog:version');
            }
        }

        return $counts;
    }
}
