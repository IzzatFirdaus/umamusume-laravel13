<?php

declare(strict_types=1);

namespace App\Services\DataPipeline;

use App\Actions\PromoteMatchedRecord;
use App\Actions\StoreCharacterCards;
use App\Actions\StoreRaceCatalogSlots;
use App\Enums\CandidateStatus;
use App\Enums\MatchTier;
use App\Models\MatchCandidate;
use App\Services\DataPipeline\Contracts\CharacterCardSourceParser;
use App\Services\DataPipeline\Contracts\RaceCatalogSourceParser;
use App\Services\DataPipeline\Contracts\SourceParser;
use Illuminate\Support\Facades\Cache;

/**
 * Parse -> normalize -> match -> promote|review stage runner (PRD FR-B-2).
 * Shared by uma:fetch (network) and uma:reparse (snapshots only).
 *
 * Reference rows that carry their own identity — a race catalogue, a card — route
 * past the match stage instead, on their parser's contract. See `run()`'s two
 * `is_a()` branches.
 */
final class PipelineRunner
{
    public function __construct(
        private readonly CrossReferenceMatcher $matcher,
        private readonly PromoteMatchedRecord $promote,
        private readonly StoreRaceCatalogSlots $storeRaceCatalog,
        private readonly StoreCharacterCards $storeCharacterCards,
    ) {}

    /**
     * @param  array{url: string, parser: class-string, timezone?: string|null}  $sourceConfig
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
         * races. The parser's own contract is what distinguishes the kinds, so
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
         * Cards are the third kind (ADR-0008), and they leave the match stage for the
         * same reason: a card's identity is the source's own `card_id`, and the
         * trainee it belongs to is named by a char ref that resolves through
         * `umamusume.external_ref` rather than inferred from a string. FR-B-3's queue
         * exists because names are ambiguous between servers; nothing here is
         * ambiguous, so nothing goes to review — and card records carry no `name` key
         * at all, which is what the loop below reads first.
         *
         * The three contracts — `SourceParser`, `RaceCatalogSourceParser`,
         * `CharacterCardSourceParser` — are siblings, not subtypes: none extends
         * another, so no one `class-string<T>` names all three. That is why
         * `run()`'s `@param` types `parser` as a bare `class-string` and each branch
         * narrows it to the contract it calls.
         */
        if (is_a($parserClass, CharacterCardSourceParser::class, true)) {
            /** @var CharacterCardSourceParser $parser */
            $parser = app($parserClass);
            $stored = $this->storeCharacterCards->handle(
                $parser->parse($body),
                $sourceConfig['url'],
                $snapshotPath,
                $sourceConfig['timezone'] ?? null,
            );

            /*
             * The bump below feeds CatalogController::cached(), which keys the page
             * group on `catalog:version` and nothing else. The race-catalogue branch
             * returns before reaching it and correctly so — race rows are not in the
             * catalog list. Card rows are, so a fetch that landed cards without this
             * would leave the page serving the ids it cached before they existed.
             */
            if ($stored['created'] + $stored['updated'] > 0) {
                if (! Cache::add('catalog:version', 0, 3600)) {
                    Cache::increment('catalog:version');
                }
            }

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