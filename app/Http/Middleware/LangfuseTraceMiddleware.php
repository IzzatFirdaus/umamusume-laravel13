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
 * Delivery is deferred to `terminate()`: the span is exported after the
 * response has been sent, so a slow or unreachable collector costs the caller
 * nothing. The 5s export timeout therefore bounds background work only, and
 * failures are still logged and swallowed without touching the response.
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
            // Stash the failure for terminate(); re-throwing keeps the normal
            // exception handling (and status code) intact.
            $request->attributes->set('telemetry.langfuse.context', [
                'trace_id' => $traceId,
                'start_ns' => $start,
                'status_code' => $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500,
                'exception' => $e,
            ]);

            throw $e;
        }

        $request->attributes->set('telemetry.langfuse.context', [
            'trace_id' => $traceId,
            'start_ns' => $start,
            'status_code' => $response->getStatusCode(),
            'exception' => null,
        ]);

        return $response;
    }

    /**
     * Export after the response is sent. Laravel calls this only when the
     * request is handled by the HTTP kernel (not in tests that call handle()
     * directly), which is exactly the production path we want to keep fast.
     */
    public function terminate(Request $request, Response $response): void
    {
        if (! config('langfuse.enabled')) {
            return;
        }

        $context = $request->attributes->get('telemetry.langfuse.context');
        if (! is_array($context)) {
            return;
        }

        $exception = $context['exception'];
        $attributes = [
            ['key' => 'http.method', 'value' => ['stringValue' => $request->method()]],
            ['key' => 'http.url', 'value' => ['stringValue' => $request->fullUrl()]],
            ['key' => 'http.route', 'value' => ['stringValue' => $request->route()?->getName() ?? $request->path()]],
            ['key' => 'http.status_code', 'value' => ['intValue' => (string) $context['status_code']]],
            ['key' => 'http.target', 'value' => ['stringValue' => $request->path()]],
            ['key' => 'network.peer.ip', 'value' => ['stringValue' => $request->getClientIp() ?? '']],
            ['key' => 'langfuse.name', 'value' => ['stringValue' => sprintf('%s %s', $request->method(), $request->path())]],
            ['key' => 'langfuse.type', 'value' => ['stringValue' => 'span']],
        ];

        if ($exception instanceof \Throwable) {
            $attributes[] = ['key' => 'langfuse.level', 'value' => ['stringValue' => 'error']];
            $attributes[] = ['key' => 'exception.type', 'value' => ['stringValue' => get_class($exception)]];
            $attributes[] = ['key' => 'exception.message', 'value' => ['stringValue' => $exception->getMessage()]];
        }

        $this->langfuse->trace([
            'name' => sprintf('%s %s', $request->method(), $request->path()),
            'traceId' => $context['trace_id'],
            'spanId' => bin2hex(random_bytes(8)),
            'kind' => 2, // SPAN_KIND_SERVER
            'startTimeUnixNano' => $context['start_ns'],
            'endTimeUnixNano' => $this->nanoTime(),
            'attributes' => $attributes,
        ]);
    }

    private function nanoTime(): int
    {
        $now = new \DateTimeImmutable;

        return (int) $now->format('U') * 1_000_000_000 + (int) $now->format('u') * 1_000;
    }
}
