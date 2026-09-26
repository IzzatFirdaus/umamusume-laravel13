<?php

declare(strict_types=1);

namespace App\Services\DataPipeline;

use App\Enums\MatchTier;
use App\Models\Umamusume;
use App\Models\UmamusumeAlias;

/**
 * Decides which catalog row a fetched name refers to (PRD FR-B-3). Never
 * writes; callers route the returned tier (Exact/Alias promote, Fuzzy/None
 * go to review).
 */
final class CrossReferenceMatcher
{
    public function __construct(private readonly NameNormalizer $normalizer) {}

    /**
     * Tier order is the contract: Exact (match_key equality) then Alias
     * (case-insensitive alias hit) are auto-promotable; Fuzzy carries the best
     * above-threshold suggestion with its similar_text percentage; None means
     * no catalog row is even close.
     *
     * @return array{tier: MatchTier, umamusume: Umamusume|null, confidence: float|null}
     */
    public function match(string $proposedName): array
    {
        $matchKey = $this->normalizer->normalize($proposedName);

        $exact = Umamusume::where('match_key', $matchKey)->first();

        if ($exact !== null) {
            return ['tier' => MatchTier::Exact, 'umamusume' => $exact, 'confidence' => 1.0];
        }

        $aliasHit = UmamusumeAlias::with('umamusume')
            ->whereRaw('lower(alias) = lower(?)', [$proposedName])
            ->first();

        if ($aliasHit?->umamusume !== null) {
            return ['tier' => MatchTier::Alias, 'umamusume' => $aliasHit->umamusume, 'confidence' => 1.0];
        }

        $fuzzy = $this->bestFuzzyMatch($matchKey);

        if ($fuzzy !== null) {
            return ['tier' => MatchTier::Fuzzy, 'umamusume' => $fuzzy['umamusume'], 'confidence' => $fuzzy['confidence']];
        }

        return ['tier' => MatchTier::None, 'umamusume' => null, 'confidence' => null];
    }

    public function matchKey(string $name): string
    {
        return $this->normalizer->normalize($name);
    }

    /**
     * ponytail: O(n) scan of all match_keys per unmatched record; ceiling ~10k catalog rows
     * on a local tool. Upgrade path: SQLite FTS5 or trigram index if the catalog outgrows that.
     *
     * @return array{umamusume: Umamusume, confidence: float}|null
     */
    private function bestFuzzyMatch(string $matchKey): ?array
    {
        if ($matchKey === '') {
            return null;
        }

        $threshold = (float) config('uma.match.fuzzy_threshold', 85);
        $bestId = null;
        $bestPercent = 0.0;

        Umamusume::whereNotNull('match_key')
            ->select(['id', 'match_key'])
            ->chunkById(500, function ($candidates) use ($matchKey, $threshold, &$bestId, &$bestPercent): void {
                foreach ($candidates as $candidate) {
                    $percent = 0.0;
                    similar_text($matchKey, (string) $candidate->match_key, $percent);

                    if ($percent >= $threshold && $percent > $bestPercent) {
                        $bestPercent = $percent;
                        $bestId = $candidate->id;
                    }
                }
            });

        if ($bestId === null) {
            return null;
        }

        return ['umamusume' => Umamusume::findOrFail($bestId), 'confidence' => round($bestPercent, 2)];
    }
}
