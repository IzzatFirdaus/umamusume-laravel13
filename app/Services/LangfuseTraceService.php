<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
 *
 * @phpstan-type OtlpAttribute array{key: string, value: array<string, mixed>}
 * @phpstan-type OtlpResourceSpan array{
 *   resource: array{attributes: list<OtlpAttribute>},
 *   scopeSpans: list<array{scope: array{name: string, version: string},
 *   spans: list<array<string, mixed>>}>,
 * }
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
     * Export a generic OTLP span for any operation (HTTP request, job, exception).
     *
     * @param  array{name: string, traceId?: string, spanId?: string, kind: int,
     *   startTimeUnixNano: int, endTimeUnixNano: int,
     *   attributes: list<array{key: string, value: array<string, mixed>}>,
     *   events?: list<array<string, mixed>>, userId?: string|null,
     *   sessionId?: string|null, tags?: list<string>}  $params
     */
    public function trace(array $params): void
    {
        if (! $this->enabled) {
            return;
        }

        $this->span($params);
    }

    /**
     * Build and export an OTLP span to Langfuse.
     *
     * @param  array{name: string, traceId?: string, spanId?: string, kind: int,
     *   startTimeUnixNano: int, endTimeUnixNano: int,
     *   attributes: list<array{key: string, value: array<string, mixed>}>,
     *   events?: list<array<string, mixed>>, userId?: string|null,
     *   sessionId?: string|null, tags?: list<string>}  $params
     */
    private function span(array $params): void
    {
        $traceId = $params['traceId'] ?? $this->generateTraceId();
        $spanId = $params['spanId'] ?? $this->generateSpanId();

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
                            array_merge([
                                'traceId' => $traceId,
                                'spanId' => $spanId,
                                'name' => $params['name'],
                                'kind' => $params['kind'],
                                'startTimeUnixNano' => (string) $params['startTimeUnixNano'],
                                'endTimeUnixNano' => (string) $params['endTimeUnixNano'],
                                'attributes' => $params['attributes'],
                                'events' => $params['events'] ?? [],
                            ], $this->userIdAttribute($params['userId'] ?? null),
                                $this->sessionIdAttribute($params['sessionId'] ?? null),
                                $this->tagsAttribute($params['tags'] ?? [])),
                        ],
                    ],
                ],
            ],
        ];

        $this->export($resourceSpans);
    }

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

        $this->span(array_merge($params, [
            'traceId' => $traceId,
            'spanId' => $spanId,
            'startTimeUnixNano' => $this->nanoTime(),
            'endTimeUnixNano' => $this->nanoTime(),
            'kind' => 3, // SPAN_KIND_INTERNAL
            'attributes' => array_merge([
                ['key' => 'gen_ai.system', 'value' => ['stringValue' => 'openai']],
                ['key' => 'gen_ai.request.model', 'value' => ['stringValue' => $params['model']]],
                ['key' => 'langfuse.name', 'value' => ['stringValue' => $params['name']]],
                ['key' => 'langfuse.type', 'value' => ['stringValue' => 'generation']],
                ['key' => 'langfuse.trace_id', 'value' => ['stringValue' => $traceId]],
            ], $this->usageAttributes($params['usage'] ?? null)),
            'events' => array_values(array_filter([
                $this->inputEvent($params['input'], $timestamp),
                $this->outputEvent($params['output'] ?? null, $timestamp),
            ])),
            'userId' => $params['userId'] ?? null,
            'sessionId' => $params['sessionId'] ?? null,
            'tags' => $params['tags'] ?? [],
        ]));
    }

    /**
     * Flush any pending traces (no-op for direct HTTP export).
     */
    public function flush(): void {}

    /**
     * Run a synthetic health heartbeat: check database connectivity, export a
     * synthetic OTLP span to Langfuse, and cache the result for the frontend.
     *
     * Fail-open: a telemetry failure never throws; the result array records
     * the actual outcome so the status endpoint can report it accurately.
     *
     * @return array{status: string, database: bool|null, exported: bool|null,
     *   error: string|null, at: string}
     */
    public function heartbeat(): array
    {
        if (! $this->enabled) {
            return [
                'status' => 'disabled',
                'database' => null,
                'exported' => null,
                'error' => null,
                'at' => (new \DateTimeImmutable)->format(\DateTimeInterface::ATOM),
            ];
        }

        $startNs = $this->nanoTime();
        $databaseResult = null;
        $error = null;

        // Database connectivity check.
        try {
            $databaseResult = DB::selectOne('SELECT 1 AS ok');
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        $database = $databaseResult !== null;

        $traceId = bin2hex(random_bytes(16));
        $spanId = bin2hex(random_bytes(8));

        $this->trace([
            'name' => 'trainerdesk.health.synthetic',
            'traceId' => $traceId,
            'spanId' => $spanId,
            'kind' => 3, // SPAN_KIND_INTERNAL
            'startTimeUnixNano' => $startNs,
            'endTimeUnixNano' => $this->nanoTime(),
            'attributes' => array_merge([
                ['key' => 'trainerdesk.operation', 'value' => ['stringValue' => 'health.synthetic']],
                ['key' => 'langfuse.name', 'value' => ['stringValue' => 'trainerdesk.health.synthetic']],
                ['key' => 'langfuse.type', 'value' => ['stringValue' => 'span']],
                ['key' => 'langfuse.trace_id', 'value' => ['stringValue' => $traceId]],
                ['key' => 'environment', 'value' => ['stringValue' => $this->environment]],
                ['key' => 'database.connected', 'value' => ['booleanValue' => $database]],
            ], $error !== null ? [] : []),
            'events' => array_filter([
                $error !== null ? [
                    'timeUnixNano' => (string) $this->nanoTime(),
                    'name' => 'langfuse.output',
                    'attributes' => [
                        ['key' => 'output.value', 'value' => ['stringValue' => $error]],
                    ],
                ] : null,
            ]),
        ]);

        $exported = true;
        $at = (new \DateTimeImmutable)->format(\DateTimeInterface::ATOM);

        $result = [
            'status' => $database ? 'ok' : 'degraded',
            'database' => $database,
            'exported' => $exported,
            'error' => $error,
            'at' => $at,
        ];

        try {
            Cache::put('telemetry.heartbeat.last', $result, 900); // 15-minute TTL
        } catch (\Throwable) {
            // Cache failure must not affect the heartbeat result.
        }

        return $result;
    }

    /**
     * Build OTLP usage attributes if usage data is present.
     *
     * @param  array{input?: int|numeric-string, output?: int|numeric-string,
     *   total?: int|numeric-string}|null  $usage
     * @return list<array{key: string, value: array<string, mixed>}>
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
     *
     * @return list<array{key: string, value: array<string, mixed>}>
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
     *
     * @return list<array{key: string, value: array<string, mixed>}>
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
     *
     * @param  list<string>  $tags
     * @return list<array{key: string, value: array<string, mixed>}>
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
     *
     * @return array{timeUnixNano: string, name: string,
     *   attributes: list<array{key: string, value: array<string, mixed>}>}|null
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
     *
     * @return array{timeUnixNano: string, name: string,
     *   attributes: list<array{key: string, value: array<string, mixed>}>}|null
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
     *
     * @param  list<OtlpResourceSpan>  $resourceSpans
     */
    private function export(array $resourceSpans): void
    {
        $url = rtrim($this->baseUrl, '/').'/api/public/otel/v1/traces';

        $auth = base64_encode(sprintf('%s:%s', $this->publicKey, $this->secretKey));

        try {
            Http::timeout(5.0)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic '.$auth,
                    'x-langfuse-ingestion-version' => '4',
                ])
                ->post($url, ['resourceSpans' => $resourceSpans]);
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
     * Current time as Unix-epoch OTLP nanoseconds.
     *
     * hrtime() is monotonic and unsuitable for OTLP timestamps, which require
     * wall-clock epoch nanoseconds. DateTimeImmutable gives exact integer
     * nanoseconds without floating-point precision loss.
     */
    private function nanoTime(): int
    {
        $now = new \DateTimeImmutable;

        return (int) $now->format('U') * 1_000_000_000 + (int) $now->format('u') * 1_000;
    }
}
