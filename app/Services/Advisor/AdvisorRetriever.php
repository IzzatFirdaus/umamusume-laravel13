<?php

declare(strict_types=1);

namespace App\Services\Advisor;

use Illuminate\Support\Facades\Log;

/**
 * Turns a question about a career into evidence, with provenance (Advisor AI slice, Phase C).
 *
 * **Three retrieval layers, in the order that matters.** Exact structured lookup is the caller's job
 * before this class runs — `ScenarioCaps`, `config('uma.fan_ladder')` and the catalog models answer
 * identifier questions better than any text search. This class owns the second and third: lexical
 * search over the FTS index, then local vector search when embeddings are available.
 *
 * **Fusion is a rank merge, not a score comparison.** A BM25 score and a cosine similarity are not
 * the same unit and adding them would be arithmetic on unlike quantities. A chunk found by both
 * moves up; a chunk found by one keeps its own evidence. Ties fall to the lexical result, because an
 * exact term match is the stronger signal for a mechanics corpus full of proper nouns.
 *
 * **Ranking coverage is reported, never assumed.** `degraded` says whether vectors contributed, so a
 * surface can state that semantic retrieval was unavailable instead of implying the four passages
 * returned are all that exist.
 */
final class AdvisorRetriever
{
    /**
     * The reciprocal-rank-fusion constant.
     *
     * 60 is the value from the original RRF paper (Cormack et al., 2009) and the default in most
     * implementations. It controls how quickly rank position decays: a larger k flattens the
     * contribution of the top hits, a smaller one sharpens it. Nothing here is tuned to this corpus,
     * which is deliberate — a constant fitted to six documents would not transfer, and an untuned
     * standard value is honest about being a default.
     */
    private const RRF_K = 60;

    public function __construct(
        private readonly AdvisorIndex $index,
        private readonly EmbeddingProvider $embeddings,
    ) {}

    /**
     * Finds evidence for a query, bounded by the configured passage count.
     *
     * @param  ?float  $deadline  Absolute microtime deadline; when passed, semantic retrieval
     *                            is skipped if the deadline has already passed.
     * @return array{evidence: list<Evidence>, degraded: bool, lexical_count: int, semantic_count: int}
     */
    public function retrieve(string $query, int $limit = 4, ?float $deadline = null): array
    {
        $lexical = $this->index->search($query, max($limit * 2, 6));
        $degraded = true;
        $semanticCount = 0;
        $merged = [];

        foreach ($lexical as $rank => $row) {
            $merged[$row['id']] = [
                'row' => $row,
                'rrf' => 1.0 / (self::RRF_K + $rank + 1),
                'lexicalRank' => $rank,
                'vectorRank' => null,
            ];
        }

        $vectorRows = $this->semantic($query, max($limit * 2, 6), $deadline);

        if ($vectorRows !== null) {
            $degraded = false;
            $semanticCount = count($vectorRows);

            foreach ($vectorRows as $rank => $row) {
                $id = $row['chunk']['id'];

                if (isset($merged[$id])) {
                    // Found by both layers: the reciprocal ranks add, which is exactly the boost a
                    // consensus hit deserves and nothing more.
                    $merged[$id]['rrf'] += 1.0 / (self::RRF_K + $rank + 1);
                    $merged[$id]['vectorRank'] = $rank;

                    continue;
                }

                $merged[$id] = [
                    'row' => [
                        'id' => $id,
                        'path' => $row['chunk']['path'],
                        'heading' => $row['chunk']['heading'],
                        'line' => $row['chunk']['line'],
                        'servers' => $row['chunk']['servers'],
                        'text' => $row['chunk']['text'],
                        'score' => 0.0,
                    ],
                    'rrf' => 1.0 / (self::RRF_K + $rank + 1),
                    'lexicalRank' => null,
                    'vectorRank' => $rank,
                ];
            }
        }

        uasort(
            $merged,
            static fn (array $a, array $b): int => $b['rrf'] <=> $a['rrf'],
        );

        $evidence = [];

        foreach (array_slice($merged, 0, $limit, true) as $id => $entry) {
            $evidence[] = new Evidence(
                id: (string) $id,
                path: $entry['row']['path'],
                heading: $entry['row']['heading'],
                text: $entry['row']['text'],
                servers: $entry['row']['servers'],
                sourceHash: $this->index->indexedHash($entry['row']['path']) ?? '',
                line: $entry['row']['line'],
                score: (float) $entry['rrf'],
                vectorRank: $entry['vectorRank'],
            );
        }

        return [
            'evidence' => $evidence,
            'degraded' => $degraded,
            'lexical_count' => count($lexical),
            'semantic_count' => $semanticCount,
        ];
    }

    /**
     * Vector search, or null when it cannot run.
     *
     * @param  ?float  $deadline  Absolute microtime deadline; returns null immediately if passed.
     * @return list<array{chunk: array{id: string, path: string, heading: string, line: int, servers: list<string>, text: string}, score: float}>|null
     */
    private function semantic(string $query, int $limit, ?float $deadline = null): ?array
    {
        // Deadline check before any blocking I/O.
        if ($deadline !== null && microtime(true) >= $deadline) {
            return null;
        }

        $identity = $this->embeddings->identity();
        $vectors = $this->index->vectors($identity['model']);

        if ($vectors === []) {
            return null;
        }

        $queryVectors = $this->embeddings->embed([$query], $deadline);

        if ($queryVectors === null) {
            return null;
        }

        $queryVector = $queryVectors[0];
        $scored = [];

        foreach ($vectors as $chunkId => $vector) {
            // A dimension mismatch means the stored vector came from a different model build. It is
            // skipped rather than truncated, because comparing the first 512 of 768 values would
            // produce a plausible-looking number with no meaning.
            if (count($vector) !== count($queryVector)) {
                continue;
            }

            $scored[$chunkId] = $this->cosine($queryVector, $vector);
        }

        if ($scored === []) {
            return null;
        }

        arsort($scored);

        $rows = [];

        foreach (array_slice($scored, 0, $limit, true) as $chunkId => $score) {
            $chunk = $this->index->chunkById((string) $chunkId);

            if ($chunk === null) {
                Log::warning("Advisor index: vector for missing chunk [{$chunkId}].");

                continue;
            }

            $rows[] = ['chunk' => $chunk, 'score' => (float) $score];
        }

        return $rows;
    }

    /**
     * @param  list<float>  $a
     * @param  list<float>  $b
     */
    private function cosine(array $a, array $b): float
    {
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($a as $i => $value) {
            $other = $b[$i] ?? 0.0;
            $dot += $value * $other;
            $normA += $value * $value;
            $normB += $other * $other;
        }

        if ($normA <= 0.0 || $normB <= 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    /**
     * The index this retriever reads, so a caller can report its health without a second instance.
     */
    public function index(): AdvisorIndex
    {
        return $this->index;
    }

    /**
     * The embedding provider this retriever uses.
     */
    public function embeddings(): EmbeddingProvider
    {
        return $this->embeddings;
    }
}
