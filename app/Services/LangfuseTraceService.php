<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Langfuse telemetry client for LLM call traces.
 *
 * Opt-in per PRD NFR-1 amendment (2026-11-21): `config('langfuse.enabled')`
 * defaults to false. All methods are no-ops when disabled.
 *
 * Sends OTLP traces directly to Langfuse's public ingestion endpoint
 * (`/api/public/otel/v1/traces`) using Basic Auth over the public/secret key
 * pair, matching Langfuse's documented OTLP integration.
 *
 * @see https://langfuse.com/integrations/native/opentelemetry
 */
class LangfuseTraceService
{
    public function __construct(
        private readonly bool $enabled,
        private readonly string $baseUrl,
        private readonly string $publicKey,
        private readonly string $secretKey,
        private readonly string $serviceName,
        private readonly string $environment,
    ) {}

    /**
     * Create a generation trace for an LLM call.
     *
     * @param  array{name: string, model: string, input: mixed, output?: mixed,
     *   usage?: array{input: int, output: int, total: int}, userId?: string|null,
     *   sessionId?: string|null, tags?: list<string>} $params
     */
    public function generation(array $params): void
    {
        if (! $this->enabled) {
            return;
        }

        $traceId = $this->generateTraceId();
        $spanId = $this->generateSpanId();
        $timestamp = (new \DateTimeImmutable)->format(\DateTimeInterface::ATOM);

        $resourceSpans = [
            [
                'resource' => [
                    'attributes' => [
                        ['key' => 'service.name', 'value' => ['stringValue' => $this->serviceName]],
                        ['key' => 'langfuse.sdk.name', 'value' => ['stringValue' => 'laravel-langfuse']],
                        ['key' => 'langfuse.sdk.version', 'value' => ['stringValue' => '1.0.0']],
                        ['key' => 'langfuse.publicKey', 'value' => ['stringValue' => $this->publicKey]],
                        ['key' => 'langfuse.environment', 'value' => ['stringValue' => $this->environment]],
                        ['key' => 'langfuse.version', 'value' => ['stringValue' => $this->serviceName]],
                    ],
                ],
                'scopeSpans' => [
                    [
                        'scope' => [
                            'name' => 'langfuse',
                            'version' => '1.0.0',
                        ],
                        'spans' => [
                            [
                                'traceId' => $traceId,
                                'spanId' => $spanId,
                                'name' => $params['name'],
                                'kind' => 3, // SPAN_KIND_INTERNAL
                                'startTimeUnixNano' => (string) $this->nanoTime(),
                                'endTimeUnixNano' => (string) $this->nanoTime(),
                                'attributes' => array_merge([
                                    ['key' => 'gen_ai.system', 'value' => ['stringValue' => 'openai']],
                                    ['key' => 'gen_ai.request.model', 'value' => ['stringValue' => $params['model']]],
                                    ['key' => 'langfuse.name', 'value' => ['stringValue' => $params['name']]],
                                    ['key' => 'langfuse.type', 'value' => ['stringValue' => 'generation']],
                                    ['key' => 'langfuse.trace_id', 'value' => ['stringValue' => $traceId]],
                                ], $this->usageAttributes($params['usage'] ?? null)),
                                'events' => array_filter([
                                    $this->inputEvent($params['input'], $timestamp),
                                    $this->outputEvent($params['output'] ?? null, $timestamp),
                                ]),
                                $this->userIdAttribute($params['userId'] ?? null),
                                $this->sessionIdAttribute($params['sessionId'] ?? null),
                                $this->tagsAttribute($params['tags'] ?? []),
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->export($resourceSpans);
    }

    /**
     * Flush any pending traces (no-op for direct HTTP export).
     */
    public function flush(): void {}

    /**
     * Build OTLP usage attributes if usage data is present.
     */
    private function usageAttributes(?array $usage): array
    {
        if (! $usage) {
            return [];
        }

        return [
            ['key' => 'llm.usage.prompt_tokens', 'value' => ['intValue' => (string) ($usage['input'] ?? 0)]],
            ['key' => 'llm.usage.completion_tokens', 'value' => ['intValue' => (string) ($usage['output'] ?? 0)]],
            ['key' => 'llm.usage.total_tokens', 'value' => ['intValue' => (string) ($usage['total'] ?? 0)]],
        ];
    }

    /**
     * Build the langfuse.user.id attribute when a user id is provided.
     */
    private function userIdAttribute(?string $userId): array
    {
        if (! $userId) {
            return [];
        }

        return [
            [
                'key' => 'langfuse.user.id',
                'value' => ['stringValue' => $userId],
            ],
        ];
    }

    /**
     * Build the langfuse.session.id attribute when a session id is provided.
     */
    private function sessionIdAttribute(?string $sessionId): array
    {
        if (! $sessionId) {
            return [];
        }

        return [
            [
                'key' => 'langfuse.session.id',
                'value' => ['stringValue' => $sessionId],
            ],
        ];
    }

    /**
     * Build the langfuse.tags attribute when tags are present.
     */
    private function tagsAttribute(array $tags): array
    {
        if (! $tags) {
            return [];
        }

        return [
            [
                'key' => 'langfuse.tags',
                'value' => ['arrayValue' => ['values' => array_map(
                    fn (string $tag) => ['stringValue' => $tag],
                    $tags
                )]],
            ],
        ];
    }

    /**
     * Build an input event if input data is present.
     */
    private function inputEvent(mixed $input, string $timestamp): ?array
    {
        if ($input === null || $input === '') {
            return null;
        }

        return [
            'timeUnixNano' => (string) $this->nanoTime(),
            'name' => 'langfuse.input',
            'attributes' => [
                [
                    'key' => 'input.value',
                    'value' => ['stringValue' => is_scalar($input) ? (string) $input : json_encode($input)],
                ],
            ],
        ];
    }

    /**
     * Build an output event if output data is present.
     */
    private function outputEvent(mixed $output, string $timestamp): ?array
    {
        if ($output === null || $output === '') {
            return null;
        }

        return [
            'timeUnixNano' => (string) $this->nanoTime(),
            'name' => 'langfuse.output',
            'attributes' => [
                [
                    'key' => 'output.value',
                    'value' => ['stringValue' => is_scalar($output) ? (string) $output : json_encode($output)],
                ],
            ],
        ];
    }

    /**
     * Export OTLP traces to Langfuse.
     */
    private function export(array $resourceSpans): void
    {
        $url = rtrim($this->baseUrl, '/').'/api/public/otel/v1/traces';

        $auth = base64_encode(sprintf('%s:%s', $this->publicKey, $this->secretKey));

        try {
            /** @var PendingRequest $request */
            $request = Http::post($url, [
                'resourceSpans' => $resourceSpans,
            ]);
            $request->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic '.$auth,
                'x-langfuse-ingestion-version' => '4',
            ]);
            $request->timeout(2.0);
        } catch (\Throwable $e) {
            // Telemetry must never break the application. Log and swallow.
            \Log::warning('Langfuse trace export failed: '.$e->getMessage());
        }
    }

    /**
     * Generate a random 32-hex-char trace id.
     */
    private function generateTraceId(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Generate a random 16-hex-char span id.
     */
    private function generateSpanId(): string
    {
        return bin2hex(random_bytes(8));
    }

    /**
     * Current time in OTLP nanoseconds.
     */
    private function nanoTime(): int
    {
        /** @var array{0: int, 1: int} $time */
        $time = hrtime(true);

        return (int) ($time[0] * 1_000_000_000 + $time[1]);
    }
}
