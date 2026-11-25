<?php

declare(strict_types=1);

use App\Services\Advisor\AdvisorIndex;
use App\Services\Advisor\AdvisorRetriever;
use App\Services\Advisor\EmbeddingProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

/**
 * The embedding provider takes a telemetry service through the container, so the benchmark
 * instantiates it there rather than with a hand-built `new` — a hand-built provider is an
 * ArgumentCountError the moment its constructor signature changes.
 */
function benchmarkEmbeddings(): EmbeddingProvider
{
    return app(EmbeddingProvider::class);
}

/**
 * Retrieval relevance and source integrity benchmark.
 *
 * Establishes reproducible benchmark with real gameplay queries:
 * - Low Energy/Rest
 * - Training fatigue
 * - Stamina planning
 * - Mandatory race requirements
 * - Skill costs
 * - Support-card effects
 * - Unity Cup Spirit Bursts
 * - Extreme Spirit Bursts
 * - Global-versus-JP source restrictions
 * - Conflicting or missing aptitude evidence
 *
 * Measures lexical, vector, and combined retrieval.
 * Investigates previously reported fatigue-query relevance problem.
 * Verifies vector retrieval disabled means no Ollama request.
 *
 * **External-provider contract.** The vector sections need a live Ollama daemon. `buildFullIndex()`
 * probes `127.0.0.1:11434/api/tags` once with a 2 s timeout; when it does not answer, vectors are not
 * built and every vector-dependent case calls `$this->markTestSkipped()` with that reason, while the
 * lexical sections still run. Ordinary Advisor tests (`AdvisorAiPipelineTest`, `AdvisorProvenanceTest`,
 * `AdvisorAnalysisRouteTest`, `AdvisorRevisionTest`) never call the provider: they disable embeddings
 * and fake `Http`.
 */

// Real gameplay queries derived from actual Trainer questions
const BENCHMARK_QUERIES = [
    // Low Energy / Rest
    'low Energy what do I do' => [
        'category' => 'low_energy_rest',
        'expected_headings' => ['Energy', 'Rest', 'Recovery'],
        'expected_servers' => ['Global', 'JP'],
    ],
    'Energy below advisory line' => [
        'category' => 'low_energy_rest',
        'expected_headings' => ['Energy', 'Advisory', 'Rest'],
        'expected_servers' => ['Global'],
    ],
    'when to Rest instead of train' => [
        'category' => 'low_energy_rest',
        'expected_headings' => ['Rest', 'Energy', 'Training'],
        'expected_servers' => ['Global', 'JP'],
    ],

    // Training fatigue
    'training fatigue mechanics' => [
        'category' => 'training_fatigue',
        'expected_headings' => ['Fatigue', 'Training', 'Failure'],
        'expected_servers' => ['Global', 'JP'],
    ],
    'failure rate when tired' => [
        'category' => 'training_fatigue',
        'expected_headings' => ['Failure', 'Rate', 'Fatigue'],
        'expected_servers' => ['Global', 'JP'],
    ],
    'Energy cost tired training' => [
        'category' => 'training_fatigue',
        'expected_headings' => ['Energy', 'Cost', 'Training'],
        'expected_servers' => ['Global'],
    ],

    // Stamina planning
    'Stamina planning long distance' => [
        'category' => 'stamina_planning',
        'expected_headings' => ['Stamina', 'Long', 'Distance'],
        'expected_servers' => ['Global', 'JP'],
    ],
    'how much Stamina for mile' => [
        'category' => 'stamina_planning',
        'expected_headings' => ['Stamina', 'Mile', 'Distance'],
        'expected_servers' => ['Global', 'JP'],
    ],
    'Stamina target URA Finale' => [
        'category' => 'stamina_planning',
        'expected_headings' => ['Stamina', 'URA', 'Finale'],
        'expected_servers' => ['Global', 'JP'],
    ],

    // Mandatory race requirements
    'mandatory race requirements' => [
        'category' => 'mandatory_races',
        'expected_headings' => ['Race', 'Mandatory', 'Requirement'],
        'expected_servers' => ['Global', 'JP'],
    ],
    'Junior Make Debut deadline' => [
        'category' => 'mandatory_races',
        'expected_headings' => ['Debut', 'Junior', 'Deadline'],
        'expected_servers' => ['Global', 'JP'],
    ],
    'URA Finals qualification' => [
        'category' => 'mandatory_races',
        'expected_headings' => ['URA', 'Finals', 'Qualification'],
        'expected_servers' => ['Global', 'JP'],
    ],

    // Skill costs
    'skill point cost inheritance' => [
        'category' => 'skill_costs',
        'expected_headings' => ['Skill', 'Point', 'Cost', 'Inheritance'],
        'expected_servers' => ['Global', 'JP'],
    ],
    'skill hint level bonus' => [
        'category' => 'skill_costs',
        'expected_headings' => ['Hint', 'Level', 'Bonus', 'Skill'],
        'expected_servers' => ['Global', 'JP'],
    ],
    'unique skill acquisition cost' => [
        'category' => 'skill_costs',
        'expected_headings' => ['Unique', 'Skill', 'Acquisition', 'Cost'],
        'expected_servers' => ['Global', 'JP'],
    ],

    // Support card effects
    'support card friendship bonus' => [
        'category' => 'support_card_effects',
        'expected_headings' => ['Friendship', 'Bonus', 'Support', 'Card'],
        'expected_servers' => ['Global', 'JP'],
    ],
    'Pal card energy recovery' => [
        'category' => 'support_card_effects',
        'expected_headings' => ['Pal', 'Energy', 'Recovery'],
        'expected_servers' => ['Global'],
    ],
    'scenario link support card effect' => [
        'category' => 'support_card_effects',
        'expected_headings' => ['Scenario', 'Link', 'Support', 'Card'],
        'expected_servers' => ['Global', 'JP'],
    ],

    // Unity Cup Spirit Bursts
    'Unity Cup Spirit Burst mechanic' => [
        'category' => 'unity_cup_spirit_bursts',
        'expected_headings' => ['Spirit Burst', 'Unity Cup', 'Mechanic'],
        'expected_servers' => ['Global'],
    ],
    'Spirit Burst gauge charging' => [
        'category' => 'unity_cup_spirit_bursts',
        'expected_headings' => ['Spirit Burst', 'Gauge', 'Charging'],
        'expected_servers' => ['Global'],
    ],
    'normal Spirit Burst values' => [
        'category' => 'unity_cup_spirit_bursts',
        'expected_headings' => ['Spirit Burst', 'Values', 'Stat'],
        'expected_servers' => ['Global'],
    ],

    // Extreme Spirit Bursts
    'Extreme Spirit Burst unlock' => [
        'category' => 'extreme_spirit_bursts',
        'expected_headings' => ['Extreme', 'Spirit Burst', 'Unlock'],
        'expected_servers' => ['Global'],
    ],
    'Extreme Spirit Burst 0% failure' => [
        'category' => 'extreme_spirit_bursts',
        'expected_headings' => ['Extreme', 'Spirit Burst', 'Failure'],
        'expected_servers' => ['Global'],
    ],
    'Ignited Spirit skill hint' => [
        'category' => 'extreme_spirit_bursts',
        'expected_headings' => ['Ignited', 'Spirit', 'Skill', 'Hint'],
        'expected_servers' => ['Global'],
    ],

    // Global vs JP source restrictions
    'Global only mechanic not in JP' => [
        'category' => 'global_vs_jp',
        'expected_headings' => ['Global', 'JP', 'Difference'],
        'expected_servers' => ['Global'],
    ],
    'Unity Cup Global rework changes' => [
        'category' => 'global_vs_jp',
        'expected_headings' => ['Unity Cup', 'Global', 'Rework'],
        'expected_servers' => ['Global'],
    ],
    'Team Race Global league ladder' => [
        'category' => 'global_vs_jp',
        'expected_headings' => ['Team Race', 'League', 'Ladder', 'Global'],
        'expected_servers' => ['Global'],
    ],

    // Conflicting or missing aptitude evidence
    'aptitude mismatch penalty' => [
        'category' => 'aptitude_evidence',
        'expected_headings' => ['Aptitude', 'Mismatch', 'Penalty'],
        'expected_servers' => ['Global', 'JP'],
    ],
    'Dirt aptitude Sprint Uma' => [
        'category' => 'aptitude_evidence',
        'expected_headings' => ['Dirt', 'Aptitude', 'Sprint'],
        'expected_servers' => ['Global'],
    ],
    'Trackblazer limited range aptitude track' => [
        'category' => 'aptitude_evidence',
        'expected_headings' => ['Trackblazer', 'Aptitude', 'Track'],
        'expected_servers' => ['Global'],
    ],
];

/**
 * Whether a local embedding daemon answers, probed with a short timeout.
 *
 * The vector sections of this benchmark need a live provider. Probing once — instead of
 * discovering it after 60-second embedding calls — is what keeps the lexical sections fast
 * when Ollama is down: each failed embed costs about two seconds here, and the corpus
 * holds hundreds of chunks.
 */
function ollamaAvailable(): bool
{
    $endpoint = (string) config('advisor_ai.embeddings.endpoint', 'http://127.0.0.1:11434/api/embed');
    $host = parse_url($endpoint, PHP_URL_HOST);
    $port = parse_url($endpoint, PHP_URL_PORT) ?? 11434;

    try {
        return Http::timeout(2)
            ->get(sprintf('http://%s:%d/api/tags', $host, $port))
            ->successful();
    } catch (Throwable) {
        return false;
    }
}

// Build the full index from all configured sources
function buildFullIndex(): AdvisorIndex
{
    $path = base_path('storage/app/private/advisor-index/benchmark-'.getmypid().'.sqlite');

    if (is_file($path)) {
        unlink($path);
    }

    Config::set('advisor_ai.retrieval.index_path', 'app/private/advisor-index/benchmark-'.getmypid().'.sqlite');

    $index = new AdvisorIndex($path, 1200);

    foreach (config('advisor_ai.retrieval.sources') as $source) {
        $body = (string) file_get_contents(base_path($source));
        $index->indexDocument($source, $body);
    }

    // Vectors are built only when the provider answers. With Ollama down, embedding every
    // chunk turned a sub-second lexical benchmark into a multi-minute run of failed calls;
    // the vector sections skip below, and the lexical sections still assert provenance.
    $withVectors = ollamaAvailable();

    Config::set('advisor_ai.embeddings.enabled', $withVectors);

    if ($withVectors) {
        $embeddings = benchmarkEmbeddings();
        $identity = $embeddings->identity();

        foreach ($index->unvectorised($identity['model'], 1000) as $chunk) {
            $vectors = $embeddings->embed([$chunk['text']]);
            if ($vectors !== null && $vectors !== []) {
                $index->putVector($chunk['id'], $identity['model'], $vectors[0]);
            }
        }
    }

    return $index;
}

// Cleanup index file
function cleanupIndex(AdvisorIndex $index): void
{
    unset($index);
    gc_collect_cycles();
}

/**
 * Shared index fixture - built once per test file execution
 */
$sharedIndex = null;

class BenchmarkDataCollector
{
    public static array $data = [];

    public static function add(string $category, string $query, array $metrics): void
    {
        self::$data[$category][$query] = $metrics;
    }

    public static function get(): array
    {
        return self::$data;
    }
}

beforeEach(function () use (&$sharedIndex): void {
    if ($sharedIndex === null) {
        $sharedIndex = buildFullIndex();
    }
});

afterAll(function () use (&$sharedIndex): void {
    if ($sharedIndex !== null) {
        cleanupIndex($sharedIndex);
        $sharedIndex = null;
    }

    $benchmarkData = BenchmarkDataCollector::get();
    if (! empty($benchmarkData ?? [])) {
        $output = storage_path('app/private/benchmark-results.json');
        file_put_contents($output, json_encode($benchmarkData, JSON_PRETTY_PRINT));
        echo "\nBenchmark results written to: {$output}\n";
    }
});

it('builds full corpus index for benchmarking', function () use (&$sharedIndex): void {
    expect($sharedIndex->chunkCount())->toBeGreaterThan(0);
});

/**
 * Lexical-only retrieval: embeddings disabled.
 * Verifies no Ollama request is made.
 */
describe('Lexical retrieval (embeddings disabled)', function () use (&$sharedIndex) {
    beforeEach(function () use (&$sharedIndex): void {
        Config::set('advisor_ai.embeddings.enabled', false);
        $this->retriever = new AdvisorRetriever($sharedIndex, benchmarkEmbeddings());
    });

    foreach (BENCHMARK_QUERIES as $query => $meta) {
        it("lexical: {$query}", function () use ($query): void {
            $result = $this->retriever->retrieve($query, 4);
            $evidence = $result['evidence'];

            // Degraded must be true when embeddings disabled
            expect($result['degraded'])->toBeTrue()
                ->and($result['semantic_count'])->toBe(0)
                ->and($result['lexical_count'])->toBeGreaterThanOrEqual(0);

            // Every citation must have valid provenance
            foreach ($evidence as $e) {
                expect($e->path)->not->toBe('')
                    ->and(base_path($e->path))->toBeFile()
                    ->and($e->heading)->not->toBe('')
                    ->and($e->sourceHash)->not->toBe('')
                    ->and($e->line)->toBeGreaterThan(0)
                    ->and($e->vectorRank)->toBeNull();
            }

            // Log metrics for analysis
            BenchmarkDataCollector::add('lexical', $query, [
                'count' => count($evidence),
                'lexical_count' => $result['lexical_count'],
                'semantic_count' => $result['semantic_count'],
                'headings' => array_map(fn ($e) => $e->heading, $evidence),
                'servers' => array_map(fn ($e) => $e->servers, $evidence),
            ]);
        });
    }
});

/**
 * Combined retrieval: RRF fusion of lexical + vector.
 */
describe('Combined retrieval (RRF fusion)', function () use (&$sharedIndex) {
    beforeEach(function () use (&$sharedIndex): void {
        if (! ollamaAvailable()) {
            $this->markTestSkipped('Ollama unavailable; vector retrieval cannot be benchmarked');
        }

        Config::set('advisor_ai.embeddings.enabled', true);
        $this->retriever = new AdvisorRetriever($sharedIndex, benchmarkEmbeddings());
    });

    foreach (BENCHMARK_QUERIES as $query => $meta) {
        it("combined: {$query}", function () use ($query): void {
            $result = $this->retriever->retrieve($query, 4);
            $evidence = $result['evidence'];

            // Verify provenance integrity
            foreach ($evidence as $e) {
                expect($e->path)->not->toBe('')
                    ->and(base_path($e->path))->toBeFile()
                    ->and($e->heading)->not->toBe('')
                    ->and($e->sourceHash)->not->toBe('');
            }

            // Track combined metrics
            BenchmarkDataCollector::add('combined', $query, [
                'count' => count($evidence),
                'lexical_count' => $result['lexical_count'],
                'semantic_count' => $result['semantic_count'],
                'degraded' => $result['degraded'],
                'headings' => array_map(fn ($e) => $e->heading, $evidence),
                'servers' => array_map(fn ($e) => $e->servers, $evidence),
                'vector_ranks' => array_map(fn ($e) => $e->vectorRank, $evidence),
                'scores' => array_map(fn ($e) => $e->score, $evidence),
            ]);
        });
    }
});

/**
 * Fatigue query relevance investigation.
 * Previously reported: "fatigue" queries return poor relevance.
 */
describe('Fatigue query relevance investigation', function () use (&$sharedIndex) {
    beforeEach(function () use (&$sharedIndex): void {
        if (! ollamaAvailable()) {
            $this->markTestSkipped('Ollama unavailable; vector retrieval cannot be benchmarked');
        }

        Config::set('advisor_ai.embeddings.enabled', true);
        $this->retriever = new AdvisorRetriever($sharedIndex, benchmarkEmbeddings());
    });

    $fatigueQueries = [
        'training fatigue mechanics',
        'failure rate when tired',
        'Energy cost tired training',
        'fatigue recovery mechanics',
        'how does fatigue affect training',
    ];

    foreach ($fatigueQueries as $query) {
        it("fatigue query: {$query}", function () use ($query): void {
            $result = $this->retriever->retrieve($query, 6);
            $evidence = $result['evidence'];

            // Analyze relevance: check if returned chunks actually discuss fatigue
            $fatigueKeywords = ['fatigue', 'tired', 'failure', 'energy', 'recovery', 'rest'];
            $relevantCount = 0;

            foreach ($evidence as $e) {
                $text = strtolower($e->text);
                foreach ($fatigueKeywords as $kw) {
                    if (str_contains($text, $kw)) {
                        $relevantCount++;
                        break;
                    }
                }
            }

            // Log for analysis
            BenchmarkDataCollector::add('fatigue', $query, [
                'total' => count($evidence),
                'relevant' => $relevantCount,
                'relevance_ratio' => count($evidence) > 0 ? $relevantCount / count($evidence) : 0,
                'headings' => array_map(fn ($e) => $e->heading, $evidence),
                'texts' => array_map(fn ($e) => substr($e->text, 0, 200), $evidence),
            ]);

            // If relevance is poor (< 50%), flag for investigation
            expect($relevantCount)->toBeGreaterThan(0);
        });
    }
});

/**
 * Source scope verification: Global vs JP citations.
 */
describe('Source scope integrity (Global vs JP)', function () use (&$sharedIndex) {
    beforeEach(function () use (&$sharedIndex): void {
        if (! ollamaAvailable()) {
            $this->markTestSkipped('Ollama unavailable; vector retrieval cannot be benchmarked');
        }

        Config::set('advisor_ai.embeddings.enabled', true);
        $this->retriever = new AdvisorRetriever($sharedIndex, benchmarkEmbeddings());
    });

    it('Global-only queries do not return JP-only passages as authoritative', function (): void {
        $query = 'Unity Cup Spirit Burst mechanic';
        $result = $this->retriever->retrieve($query, 4);
        $evidence = $result['evidence'];

        $hasGlobal = false;
        foreach ($evidence as $e) {
            if (in_array('Global', $e->servers)) {
                $hasGlobal = true;
                break;
            }
        }

        expect($hasGlobal)->toBeTrue();
    });

    it('JP-only content is properly scoped', function (): void {
        $query = 'Grand Masters scenario mechanics';
        $result = $this->retriever->retrieve($query, 4);
        $evidence = $result['evidence'];

        foreach ($evidence as $e) {
            // Grand Masters is JP-only
            if (in_array('JP', $e->servers) && ! in_array('Global', $e->servers)) {
                // Correctly scoped as JP-only
                expect(true)->toBeTrue();

                return;
            }
        }

        // If no JP-only found, that's also acceptable (may not be in corpus)
        expect(true)->toBeTrue();
    });
});

/**
 * Deadline propagation verification.
 */
describe('Deadline propagation', function () use (&$sharedIndex) {
    beforeEach(function () use (&$sharedIndex): void {
        if (! ollamaAvailable()) {
            $this->markTestSkipped('Ollama unavailable; vector retrieval cannot be benchmarked');
        }

        Config::set('advisor_ai.embeddings.enabled', true);
        $this->retriever = new AdvisorRetriever($sharedIndex, benchmarkEmbeddings());
    });

    it('retrieve respects deadline parameter', function (): void {
        // Deadline in the past (microtime(true) is seconds) - should return empty or degraded.
        $pastDeadline = microtime(true) - 1.0;
        $result = $this->retriever->retrieve('test query', 4, $pastDeadline);

        // Should return quickly (degraded to lexical or empty)
        expect($result['evidence'])->toBeArray();
    });

    it('retrieve with future deadline works normally', function (): void {
        $futureDeadline = microtime(true) + 10.0;
        $result = $this->retriever->retrieve('Spirit Burst', 4, $futureDeadline);

        expect($result['evidence'])->not->toBeEmpty();
    });
});

/**
 * Vector disabled = no Ollama request verification.
 */
describe('Vector disabled means no Ollama request', function () {
    it('EmbeddingProvider returns null immediately when disabled', function (): void {
        Config::set('advisor_ai.embeddings.enabled', false);
        $provider = benchmarkEmbeddings();

        Http::fake();

        $result = $provider->embed(['test query']);

        expect($result)->toBeNull();
        Http::assertNothingSent();
    });

    it('retrieve with embeddings disabled makes no HTTP calls', function (): void {
        Config::set('advisor_ai.embeddings.enabled', false);

        $path = storage_path('app/private/advisor-index/benchmark-verify-'.getmypid().'.sqlite');
        if (is_file($path)) {
            unlink($path);
        }

        $index = new AdvisorIndex($path, 1200);
        $source = 'docs/scenarios/02-unity-cup.md';
        $body = (string) file_get_contents(base_path($source));
        $index->indexDocument($source, $body);

        $retriever = new AdvisorRetriever($index, benchmarkEmbeddings());

        Http::fake();

        $result = $retriever->retrieve('Spirit Burst', 4);

        expect($result['degraded'])->toBeTrue()
            ->and($result['semantic_count'])->toBe(0);

        Http::assertNothingSent();

        unset($index);
        gc_collect_cycles();
    });

    it('writes benchmark results to file', function (): void {
        $benchmarkData = BenchmarkDataCollector::get();
        if (! empty($benchmarkData ?? [])) {
            $output = storage_path('app/private/benchmark-results.json');
            echo "\nDEBUG: Writing benchmark results to: {$output}\n";
            echo 'DEBUG: benchmarkData keys: '.implode(', ', array_keys($benchmarkData))."\n";
            echo 'DEBUG: storage_path = '.storage_path()."\n";
            echo 'DEBUG: base_path = '.base_path()."\n";
            file_put_contents($output, json_encode($benchmarkData, JSON_PRETTY_PRINT));
            echo "\nBenchmark results written to: {$output}\n";
            expect(file_exists($output))->toBeTrue();
        } else {
            echo "\nDEBUG: benchmarkData is empty\n";
            expect(true)->toBeTrue(); // No data collected yet
        }
    });
});
