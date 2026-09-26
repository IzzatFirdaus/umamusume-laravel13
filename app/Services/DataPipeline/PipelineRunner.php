<?php

declare(strict_types=1);

namespace App\Services\DataPipeline;

use App\Actions\PromoteMatchedRecord;
use App\Enums\CandidateStatus;
use App\Enums\MatchTier;
use App\Models\MatchCandidate;
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
    ) {}

    /**
     * @param  array{url: string, parser: class-string<SourceParser>, timezone?: string|null}  $sourceConfig
     * @return array{updated: int, created: int, skipped: int, review: int}
     */
    public function run(string $sourceKey, array $sourceConfig, string $body, ?string $snapshotPath): array
    {
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
