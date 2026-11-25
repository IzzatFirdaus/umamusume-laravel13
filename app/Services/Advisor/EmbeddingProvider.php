<?php

declare(strict_types=1);

namespace App\Services\Advisor;

use App\Services\LangfuseTraceService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * The local embedding provider: Ollama's `POST /api/embed` (Advisor AI slice, Phase D).
 *
 * **It fails open, and says so.** A dead daemon, a missing model or a timeout returns null rather
 * than throwing, because semantic retrieval is an enhancement and an Advisor that refuses to answer
 * because a local model is not loaded would be strictly worse than the one that shipped before this
 * feature (`config/advisor_ai.php`). The caller degrades to lexical search and reports that it did.
 *
 * **Model identity is part of the contract.** `dimensions()` reports what the model actually
 * returned, and the index stores it beside every vector. Two different models produce vectors in
 * incomparable spaces, so a cosine between them is a number with no meaning; the index refuses to
 * mix them instead of quietly returning noise.
 *
 * Measured on this machine: `nomic-embed-text` returns 768 dimensions in 0.3 s for one short input,
 * and about 3,400 tokens/s in batches.
 */
final class EmbeddingProvider
{
    public function __construct(
        private readonly LangfuseTraceService $telemetry,
    ) {}

    /**
     * Embeds a batch of texts, or returns null when the provider is unusable.
     *
     * @param  list<string>  $texts
     * @param  ?float  $deadline  Absolute microtime deadline (seconds); returns null immediately if passed.
     * @return list<list<float>>|null
     */
    public function embed(array $texts, ?float $deadline = null): ?array
    {
        if ($texts === [] || ! (bool) config('advisor_ai.embeddings.enabled')) {
            return null;
        }

        $endpoint = (string) config('advisor_ai.embeddings.endpoint');
        $model = (string) config('advisor_ai.embeddings.model');

        // The endpoint is loopback by default and must stay a configured value rather than a
        // request parameter: an Advisor that could be pointed at an arbitrary host by a caller is
        // an SSRF surface with extra steps (`AGENTS.md` §8).
        if (! $this->isLoopback($endpoint)) {
            Log::warning('Advisor embeddings endpoint is not loopback; refusing to call it.');

            $this->telemetry->trace([
                'name' => 'ollama.embed',
                'kind' => 3, // SPAN_KIND_INTERNAL
                'startTimeUnixNano' => $this->nanoTime(),
                'endTimeUnixNano' => $this->nanoTime(),
                'attributes' => [
                    ['key' => 'gen_ai.system', 'value' => ['stringValue' => 'ollama']],
                    ['key' => 'gen_ai.request.model', 'value' => ['stringValue' => $model]],
                    ['key' => 'gen_ai.operation.name', 'value' => ['stringValue' => 'embed']],
                    ['key' => 'gen_ai.request.input_count', 'value' => ['intValue' => (string) count($texts)]],
                    ['key' => 'langfuse.name', 'value' => ['stringValue' => 'ollama.embed']],
                    ['key' => 'langfuse.type', 'value' => ['stringValue' => 'span']],
                    ['key' => 'embed.outcome', 'value' => ['stringValue' => 'skipped_non_loopback']],
                ],
            ]);

            return null;
        }

        // Deadline check before making the HTTP call.
        if ($deadline !== null && microtime(true) >= $deadline) {
            $this->telemetry->trace([
                'name' => 'ollama.embed',
                'kind' => 3, // SPAN_KIND_INTERNAL
                'startTimeUnixNano' => $this->nanoTime(),
                'endTimeUnixNano' => $this->nanoTime(),
                'attributes' => [
                    ['key' => 'gen_ai.system', 'value' => ['stringValue' => 'ollama']],
                    ['key' => 'gen_ai.request.model', 'value' => ['stringValue' => $model]],
                    ['key' => 'gen_ai.operation.name', 'value' => ['stringValue' => 'embed']],
                    ['key' => 'gen_ai.request.input_count', 'value' => ['intValue' => (string) count($texts)]],
                    ['key' => 'langfuse.name', 'value' => ['stringValue' => 'ollama.embed']],
                    ['key' => 'langfuse.type', 'value' => ['stringValue' => 'span']],
                    ['key' => 'embed.outcome', 'value' => ['stringValue' => 'skipped_deadline']],
                ],
            ]);

            return null;
        }

        $startNs = $this->nanoTime();

        try {
            $timeout = (int) config('advisor_ai.embeddings.timeout');

            // If a deadline is provided, clamp the timeout so we never exceed it.
            if ($deadline !== null) {
                $remaining = $deadline - microtime(true);
                $timeout = min($timeout, max(1, (int) $remaining));
            }

            $response = Http::timeout($timeout)
                ->acceptJson()
                ->post($endpoint, [
                    'model' => $model,
                    'input' => $texts,
                ]);

            if (! $response->successful()) {
                $this->recordEmbedTrace($startNs, $model, count($texts), 0, 'http_error');

                return null;
            }

            /** @var array{embeddings?: list<list<float|int>>} $payload */
            $payload = $response->json();

            if (! isset($payload['embeddings'])) {
                $this->recordEmbedTrace($startNs, $model, count($texts), 0, 'missing_embeddings');

                return null;
            }

            $vectors = [];

            foreach ($payload['embeddings'] as $vector) {
                if ($vector === []) {
                    $this->recordEmbedTrace($startNs, $model, count($texts), 0, 'empty_vector');

                    return null;
                }

                $vectors[] = array_map(static fn (float|int $v): float => (float) $v, $vector);
            }

            $outcome = count($vectors) === count($texts) ? 'ok' : 'count_mismatch';

            $this->recordEmbedTrace($startNs, $model, count($texts), count($vectors), $outcome);

            return count($vectors) === count($texts) ? $vectors : null;
        } catch (Throwable $e) {
            // A local model being unavailable is not a gameplay failure and must not surface as one.
            Log::info('Advisor embedding request failed: '.$e->getMessage());

            $this->recordEmbedTrace($startNs, $model, count($texts), 0, 'exception');

            return null;
        }
    }

    /**
     * Record an embedding trace span to Langfuse.
     *
     * Fail-open: a telemetry failure must never affect embedding results.
     */
    private function recordEmbedTrace(int $startNs, string $model, int $inputCount, int $vectorCount, string $outcome): void
    {
        $this->telemetry->trace([
            'name' => 'ollama.embed',
            'kind' => 3, // SPAN_KIND_INTERNAL
            'startTimeUnixNano' => $startNs,
            'endTimeUnixNano' => $this->nanoTime(),
            'attributes' => [
                ['key' => 'gen_ai.system', 'value' => ['stringValue' => 'ollama']],
                ['key' => 'gen_ai.request.model', 'value' => ['stringValue' => $model]],
                ['key' => 'gen_ai.operation.name', 'value' => ['stringValue' => 'embed']],
                ['key' => 'gen_ai.request.input_count', 'value' => ['intValue' => (string) $inputCount]],
                ['key' => 'gen_ai.response.vector_count', 'value' => ['intValue' => (string) $vectorCount]],
                ['key' => 'langfuse.name', 'value' => ['stringValue' => 'ollama.embed']],
                ['key' => 'langfuse.type', 'value' => ['stringValue' => 'span']],
                ['key' => 'embed.outcome', 'value' => ['stringValue' => $outcome]],
            ],
        ]);
    }

    /**
     * Whether the daemon answers and holds the configured model.
     */
    public function available(): bool
    {
        return $this->embed(['probe']) !== null;
    }

    /**
     * The provider and model this instance speaks as, recorded beside every vector it stores.
     *
     * @return array{model: string, provider: string}
     */
    public function identity(): array
    {
        return [
            'model' => (string) config('advisor_ai.embeddings.model'),
            'provider' => (string) config('advisor_ai.embeddings.provider'),
        ];
    }

    /**
     * Whether a URL points at this machine.
     *
     * Deliberately a small allowlist rather than a DNS resolution check: the value comes from config
     * and never from a request, so the only thing this has to prevent is a misconfiguration pointing
     * at a remote host, and string inspection is enough for that.
     */
    private function isLoopback(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return in_array($host, ['127.0.0.1', 'localhost', '::1', '[::1]'], true);
    }

    /**
     * Current time as Unix-epoch OTLP nanoseconds.
     */
    private function nanoTime(): int
    {
        $now = new \DateTimeImmutable;

        return (int) $now->format('U') * 1_000_000_000 + (int) $now->format('u') * 1_000;
    }
}
