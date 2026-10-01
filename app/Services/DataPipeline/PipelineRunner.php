<?php

declare(strict_types=1);

namespace App\Services\DataPipeline;

use App\Actions\PromoteMatchedRecord;
use App\Actions\StoreCharacterCards;
use App\Actions\StoreCharacterProfiles;
use App\Actions\StoreRaceCatalogSlots;
use App\Actions\StoreSkills;
use App\Actions\StoreSupportCards;
use App\Actions\StoreSupportEffects;
use App\Enums\CandidateStatus;
use App\Enums\MatchTier;
use App\Models\MatchCandidate;
use App\Services\DataPipeline\Contracts\CharacterCardSourceParser;
use App\Services\DataPipeline\Contracts\ProfileSourceParser;
use App\Services\DataPipeline\Contracts\RaceCatalogSourceParser;
use App\Services\DataPipeline\Contracts\SkillSourceParser;
use App\Services\DataPipeline\Contracts\SourceParser;
use App\Services\DataPipeline\Contracts\SupportCardSourceParser;
use App\Services\DataPipeline\Contracts\SupportEffectSourceParser;
use Illuminate\Support\Facades\Cache;

/**
 * Parse -> normalize -> match -> promote|review stage runner (PRD FR-B-2).
 * Shared by uma:fetch (network) and uma:reparse (snapshots only).
 *
 * Reference rows that carry their own identity route past the match stage instead, on their parser's
 * contract: a race catalogue, a skill, a card, a trainee profile, a support card, a support effect.
 * See `run()`'s six `is_a()` branches.
 */
final class PipelineRunner
{
    public function __construct(
        private readonly CrossReferenceMatcher $matcher,
        private readonly PromoteMatchedRecord $promote,
        private readonly StoreRaceCatalogSlots $storeRaceCatalog,
        private readonly StoreSkills $storeSkills,
        private readonly StoreCharacterCards $storeCharacterCards,
        private readonly StoreCharacterProfiles $storeCharacterProfiles,
        private readonly StoreSupportCards $storeSupportCards,
        private readonly StoreSupportEffects $storeSupportEffects,
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
         * Skills take the same route for the same reason, and the flood this one prevents is
         * the bigger one: read as `SourceParser` records, all 1,910 rows would fail to match an
         * Umamusume and land in `match_candidates` as pending review rows — a review queue
         * holding every skill in the game, filed as a character nobody could identify.
         *
         * Three branches of the same eight lines is duplication, and it stays because each
         * writer owns a different grain: a race row collapses on (scenario, year, month, half,
         * title), a skill row on the source's own id, a card row on its `card_id`. A shared
         * abstraction would be a third thing to read before any of them could be understood, and
         * it would have no second implementation to justify it.
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

        /*
         * Cards are the fourth routed kind (ADR-0008), and they leave the match stage for the
         * same reason: a card's identity is the source's own `card_id`, and the
         * trainee it belongs to is named by a char ref that resolves through
         * `umamusume.external_ref` rather than inferred from a string. FR-B-3's queue
         * exists because names are ambiguous between servers; nothing here is
         * ambiguous, so nothing goes to review — and card records carry no `name` key
         * at all, which is what the loop below reads first.
         *
         * The seven contracts this method can meet — `SourceParser`,
         * `RaceCatalogSourceParser`, `SkillSourceParser`, `CharacterCardSourceParser`,
         * `ProfileSourceParser`, `SupportCardSourceParser`, `SupportEffectSourceParser` — are
         * siblings, not subtypes: none extends another, so no one `class-string<T>` names all
         * of them. That is why `run()`'s `@param` types `parser` as a bare `class-string` and
         * each branch narrows it to the contract it calls.
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

        /*
         * Profiles are the fourth kind: one row per trainee, keyed by the source's own
         * `char_id`, joining the same way cards do and for the same reason. A profile row has no
         * display name either — it has a Japanese name, a romanised one and two voice actors —
         * so without this branch every one of its 163 rows would fail to match and be filed as a
         * pending review candidate, turning the review queue into 163 trainees that are already
         * in the catalog.
         *
         * It sits after the card branch because it shares the card branch's ref resolution and
         * nothing else: a profile is not a card, and a card is not a profile, so neither store
         * action is reachable from the other's contract.
         */
        if (is_a($parserClass, ProfileSourceParser::class, true)) {
            /** @var ProfileSourceParser $parser */
            $parser = app($parserClass);
            $stored = $this->storeCharacterProfiles->handle(
                $parser->parse($body),
                $sourceConfig['url'],
                $snapshotPath,
                $sourceConfig['timezone'] ?? null,
            );

            return [...$stored, 'review' => 0];
        }

        /*
         * Support cards leave the match stage for the same reason the four above do (ADR-0014): a
         * card's identity is the export's own `support_id`, and the export publishes no single display
         * name to cross-reference, only a character name and an epithet that `SupportCard::displayName()`
         * composes at read time. Read as `SourceParser` records, all 559 rows would fail to name an
         * Umamusume and land in `match_candidates` as a review queue holding the whole support-card
         * catalogue.
         *
         * `catalog:version` is not bumped, unlike the card branch above. That key feeds
         * CatalogController::cached(), which pages the trainable roster; support cards are not in that
         * list, so a fetch that landed 559 of them has to invalidate nothing.
         *
         * `$snapshotPath` and `$timezone` are not passed down: `support_cards` has neither column, so
         * the two provenance fields it does have are the pair `StoreSupportCards` stamps. ADR-0003
         * Amendment R3's four are two here, and that is a schema gap rather than this branch's call.
         */
        if (is_a($parserClass, SupportCardSourceParser::class, true)) {
            /** @var SupportCardSourceParser $parser */
            $parser = app($parserClass);
            $stored = $this->storeSupportCards->handle($parser->parse($body), $sourceConfig['url']);

            return [...$stored, 'review' => 0];
        }

        /*
         * The effect dictionary is the last routed kind, and it is separate from the card branch above
         * for the reason `CharacterCardSourceParser` is separate from `SkillSourceParser`: one row per
         * effect keyed on the export's `id`, no cross-reference, a different table and a different
         * writer. Its records carry no `name` key either, which is what the fall-through loop reads
         * first.
         */
        if (is_a($parserClass, SupportEffectSourceParser::class, true)) {
            /** @var SupportEffectSourceParser $parser */
            $parser = app($parserClass);
            $stored = $this->storeSupportEffects->handle($parser->parse($body), $sourceConfig['url']);

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

            $matchKey = $this->matcher->matchKey($record['name']);

            $candidate = MatchCandidate::updateOrCreate(
                [
                    'source_key' => $sourceKey,
                    'external_ref' => $record['external_ref'] ?? null,
                    'proposed_match_key' => $matchKey,
                ],
                [
                    'proposed_name' => $record['name'],
                    'proposed_name_ja' => $record['name_ja'] ?? null,
                    'suggested_umamusume_id' => $match['umamusume']?->id,
                    'match_tier' => $match['tier']->value,
                    'status' => CandidateStatus::Pending->value,
                    'payload' => [...$record, 'url' => $sourceConfig['url']],
                    'created_by_fetch_at' => now(),
                ]
            );

            if ($candidate->wasRecentlyCreated) {
                $counts['review']++;
            }
        }

        if ($counts['created'] > 0 || $counts['updated'] > 0) {
            if (! Cache::add('catalog:version', 0, 3600)) {
                Cache::increment('catalog:version');
            }
        }

        return $counts;
    }
}
