<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Latitude OTLP trace middleware.
 *
 * Wraps each API request in an OTLP trace and exports it to Latitude's
 * ingestion endpoint (`https://ingest.latitude.so/v1/traces`) via
 * `config('latitude.endpoint')`, authenticated with `LATITUDE_API_KEY` and
 * scoped by `LATITUDE_PROJECT`.
 *
 * Opt-in per PRD NFR-1 amendment (2026-11-21): `config('latitude.enabled')`
 * defaults to false. When disabled, this middleware passes straight through.
 *
 * Telemetry never blocks the request: export failures are logged and
 * swallowed, and the response is always returned unchanged.
 *
 * @see https://github.com/latitude-dev/latitude-llm/blob/development/docs/telemetry/otel-exporter.md
 */
class LatitudeTraceMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('latitude.enabled')) {
            return $next($request);
        }

        /** @var array{0: int, 1: int} $start */
        $start = hrtime(false);

        /** @var Response $response */
        $response = $next($request);

        /** @var array{0: int, 1: int} $end */
        $end = hrtime(false);
        $durationNs = (int) ($end[0] * 1_000_000_000 + $end[1]) - (int) ($start[0] * 1_000_000_000 + $start[1]);

        $traceId = $request->attributes->get('telemetry.trace_id') ?? $this->generateTraceId();

        $this->exportTrace($request, $response, $durationNs, $traceId);

        return $response;
    }

    /**
     * Export an OTLP span to Latitude.
     */
    private function exportTrace(Request $request, Response $response, int $durationNs, string $traceId): void
    {
        $spanId = $this->generateSpanId();

        $resourceSpans = [
            [
                'resource' => [
                    'attributes' => [
                        ['key' => 'service.name', 'value' => ['stringValue' => 'Trainer Desk']],
                        ['key' => 'latitude.project', 'value' => ['stringValue' => config('latitude.project_slug') ?? '']],
                    ],
                ],
                'scopeSpans' => [
                    [
                        'scope' => [
                            'name' => 'latitude.middleware',
                            'version' => '1.0.0',
                        ],
                        'spans' => [
                            [
                                'traceId' => $traceId,
                                'spanId' => $spanId,
                                'name' => sprintf('%s %s', $request->method(), $request->path()),
                                'kind' => 2, // SPAN_KIND_SERVER
                                'startTimeUnixNano' => (string) ($this->nanoTime() - $durationNs),
                                'endTimeUnixNano' => (string) $this->nanoTime(),
                                'attributes' => [
                                    ['key' => 'http.method', 'value' => ['stringValue' => $request->method()]],
                                    ['key' => 'http.url', 'value' => ['stringValue' => $request->fullUrl()]],
                                    ['key' => 'http.route', 'value' => ['stringValue' => $request->route()?->getName() ?? $request->path()]],
                                    ['key' => 'http.status_code', 'value' => ['intValue' => (string) $response->getStatusCode()]],
                                    ['key' => 'http.target', 'value' => ['stringValue' => $request->path()]],
                                    ['key' => 'network.peer.ip', 'value' => ['stringValue' => $request->getClientIp() ?? '']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        try {
            /** @var PendingRequest $http */
            $http = Http::timeout(5.0)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.config('latitude.api_key'),
                    'X-Latitude-Project' => config('latitude.project_slug'),
                ])
                ->post(config('latitude.endpoint'), [
                    'resourceSpans' => $resourceSpans,
                ]);
        } catch (\Throwable $e) {
            Log::warning('Latitude trace export failed: '.$e->getMessage());
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
     */
    private function nanoTime(): int
    {
        $now = new \DateTimeImmutable;

        return (int) $now->format('U') * 1_000_000_000 + (int) $now->format('u') * 1_000;
    }
}
