<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\LangfuseTraceService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Langfuse OTLP trace middleware.
 *
 * Wraps each API request in an OTLP span and exports it to Langfuse via
 * `LangfuseTraceService`, so ordinary TrainerDesk activity appears in Langfuse
 * alongside AI generations and the synthetic heartbeat.
 *
 * Opt-in per PRD NFR-1 amendment (2026-11-21): `config('langfuse.enabled')`
 * defaults to false. When disabled, this middleware passes straight through.
 *
 * Telemetry never blocks the request: export failures are logged and
 * swallowed, and the response is always returned unchanged.
 *
 * @see https://langfuse.com/docs/opentelemetry/get-started
 */
class LangfuseTraceMiddleware
{
    public function __construct(
        private readonly LangfuseTraceService $langfuse,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('langfuse.enabled')) {
            return $next($request);
        }

        $start = $this->nanoTime();
        $traceId = $request->attributes->get('telemetry.trace_id');
        if (! $traceId) {
            $traceId = bin2hex(random_bytes(16));
            $request->attributes->set('telemetry.trace_id', $traceId);
        }

        try {
            /** @var Response $response */
            $response = $next($request);
        } catch (\Throwable $e) {
            $statusCode = $e instanceof HttpExceptionInterface
                ? $e->getStatusCode()
                : 500;

            $this->langfuse->trace([
                'name' => sprintf('%s %s', $request->method(), $request->path()),
                'traceId' => $traceId,
                'spanId' => bin2hex(random_bytes(8)),
                'kind' => 2, // SPAN_KIND_SERVER
                'startTimeUnixNano' => $start,
                'endTimeUnixNano' => $this->nanoTime(),
                'attributes' => [
                    ['key' => 'http.method', 'value' => ['stringValue' => $request->method()]],
                    ['key' => 'http.url', 'value' => ['stringValue' => $request->fullUrl()]],
                    ['key' => 'http.route', 'value' => ['stringValue' => $request->route()?->getName() ?? $request->path()]],
                    ['key' => 'http.status_code', 'value' => ['intValue' => (string) $statusCode]],
                    ['key' => 'http.target', 'value' => ['stringValue' => $request->path()]],
                    ['key' => 'network.peer.ip', 'value' => ['stringValue' => $request->getClientIp() ?? '']],
                    ['key' => 'langfuse.name', 'value' => ['stringValue' => sprintf('%s %s', $request->method(), $request->path())]],
                    ['key' => 'langfuse.type', 'value' => ['stringValue' => 'span']],
                    ['key' => 'langfuse.level', 'value' => ['stringValue' => 'error']],
                    ['key' => 'exception.type', 'value' => ['stringValue' => get_class($e)]],
                    ['key' => 'exception.message', 'value' => ['stringValue' => $e->getMessage()]],
                ],
            ]);

            throw $e;
        }

        $durationNs = $this->nanoTime() - $start;

        $this->langfuse->trace([
            'name' => sprintf('%s %s', $request->method(), $request->path()),
            'traceId' => $traceId,
            'spanId' => bin2hex(random_bytes(8)),
            'kind' => 2, // SPAN_KIND_SERVER
            'startTimeUnixNano' => $start,
            'endTimeUnixNano' => $this->nanoTime(),
            'attributes' => [
                ['key' => 'http.method', 'value' => ['stringValue' => $request->method()]],
                ['key' => 'http.url', 'value' => ['stringValue' => $request->fullUrl()]],
                ['key' => 'http.route', 'value' => ['stringValue' => $request->route()?->getName() ?? $request->path()]],
                ['key' => 'http.status_code', 'value' => ['intValue' => (string) $response->getStatusCode()]],
                ['key' => 'http.target', 'value' => ['stringValue' => $request->path()]],
                ['key' => 'network.peer.ip', 'value' => ['stringValue' => $request->getClientIp() ?? '']],
                ['key' => 'langfuse.name', 'value' => ['stringValue' => sprintf('%s %s', $request->method(), $request->path())]],
                ['key' => 'langfuse.type', 'value' => ['stringValue' => 'span']],
            ],
        ]);

        return $response;
    }

    private function nanoTime(): int
    {
        $now = new \DateTimeImmutable;

        return (int) $now->format('U') * 1_000_000_000 + (int) $now->format('u') * 1_000;
    }
}
