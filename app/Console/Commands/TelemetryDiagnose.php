<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Diagnose Langfuse and Latitude telemetry connectivity and authentication.
 *
 * Reads effective configuration from config('langfuse.*') / config('latitude.*')
 * and performs bounded, provider-specific probes:
 *
 *   - Langfuse: GET /api/public/projects (read-only auth check),
 *     optionally POST /api/public/otel/v1/traces (synthetic ingestion).
 *   - Latitude: POST to the configured ingest endpoint (auth and ingestion
 *     are the same endpoint; Latitude has no separate read-only auth API).
 *
 * Results are sanitized: no credentials, partial credentials, authorization
 * headers, or request bodies are ever printed. Every probe reports its
 * failure category independently so one provider's outcome cannot be
 * inferred from the other's.
 *
 * Usage:
 *   php artisan telemetry:diagnose
 *   php artisan telemetry:diagnose --provider=langfuse
 *   php artisan telemetry:diagnose --provider=latitude --test-ingestion
 */
class TelemetryDiagnose extends Command
{
    protected $signature = 'telemetry:diagnose
        {--provider= : Limit to a single provider: langfuse or latitude}
        {--test-ingestion : Also perform a synthetic OTLP ingestion test (default: read-only auth checks only)}';

    protected $description = 'Diagnose Langfuse and Latitude telemetry connectivity and authentication';

    public function handle(): int
    {
        $requested = $this->option('provider');
        $testIngestion = (bool) $this->option('test-ingestion');

        $providers = [];
        if ($requested !== null) {
            try {
                $providers[] = $this->normalizeProvider($requested);
            } catch (\InvalidArgumentException $e) {
                $this->error($e->getMessage());

                return self::FAILURE;
            }
        } else {
            $providers = ['langfuse', 'latitude'];
        }

        $this->info('TrainerDesk telemetry diagnostic');
        $this->newLine();

        $allOk = true;

        foreach ($providers as $provider) {
            $allOk &= $this->diagnoseProvider($provider, $testIngestion);
            $this->newLine();
        }

        $this->newLine();
        $this->line($allOk
            ? '<fg=green;options=bold>All probed providers reachable and authenticated.</>'
            : '<fg=yellow;options=bold>One or more providers failed. Review the failure categories above.</>');

        return $allOk ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Normalise and validate the provider option value.
     *
     * @throws \InvalidArgumentException
     */
    private function normalizeProvider(string $value): string
    {
        $value = strtolower(trim($value));

        if (! in_array($value, ['langfuse', 'latitude'], true)) {
            throw new \InvalidArgumentException("Invalid provider '{$value}'. Use 'langfuse' or 'latitude'.");
        }

        return $value;
    }

    /**
     * Run a full diagnostic for one provider.
     *
     * @return bool true when the provider's primary check succeeded
     */
    private function diagnoseProvider(string $provider, bool $testIngestion): bool
    {
        $this->line("<fg=cyan;options=bold>=== {$provider} ===</>");

        if ($provider === 'langfuse') {
            return $this->diagnoseLangfuse($testIngestion);
        }

        return $this->diagnoseLatitude($testIngestion);
    }

    /**
     * Diagnose Langfuse connectivity and authentication.
     */
    private function diagnoseLangfuse(bool $testIngestion): bool
    {
        $enabled = (bool) config('langfuse.enabled');
        $baseUrl = rtrim((string) config('langfuse.base_url'), '/');
        $publicKey = (string) config('langfuse.public_key');
        $secretKey = (string) config('langfuse.secret_key');

        $this->line('Enabled: '.var_export($enabled, true));
        $this->line('Endpoint: '.$baseUrl);
        $this->line('Region: '.$this->regionFromUrl($baseUrl));
        $this->line('Credentials present: '.var_export($publicKey !== '' && $secretKey !== '', true));

        if (! $enabled) {
            $this->line('Failure category: disabled (opt-in; LANGFUSE_ENABLED=false).');
            $this->line('Result: skipped (telemetry disabled).');

            return true;
        }

        if ($publicKey === '' || $secretKey === '') {
            $this->line('Failure category: misconfigured (missing public_key or secret_key).');
            $this->line('Result: skipped (credentials absent).');

            return false;
        }

        // Read-only authentication check: GET /api/public/projects.
        $auth = $this->probe(
            'auth',
            'GET',
            $baseUrl.'/api/public/projects',
            ['Authorization' => 'Basic '.base64_encode($publicKey.':'.$secretKey)],
            [],
        );

        $this->reportProbe($auth);

        $authOk = $auth['status'] !== null && $auth['status'] >= 200 && $auth['status'] < 300;

        if (! $authOk) {
            $this->line('Result: authentication failed — cannot proceed to ingestion test.');

            return false;
        }

        if (! $testIngestion) {
            $this->line('Result: authenticated (read-only check passed).');
            $this->line('Hint: pass --test-ingestion to verify OTLP trace ingestion.');

            return true;
        }

        // Synthetic ingestion test: POST /api/public/otel/v1/traces.
        $traceId = bin2hex(random_bytes(16));
        $ingest = $this->probe(
            'ingestion',
            'POST',
            $baseUrl.'/api/public/otel/v1/traces',
            [
                'Authorization' => 'Basic '.base64_encode($publicKey.':'.$secretKey),
                'Content-Type' => 'application/json',
                'x-langfuse-ingestion-version' => '4',
            ],
            $this->otelPayload($traceId, 'telemetry.diagnose.ingestion'),
        );

        $this->reportProbe($ingest);

        $ingestOk = $ingest['status'] !== null && $ingest['status'] >= 200 && $ingest['status'] < 300;

        if ($ingestOk) {
            $this->line('Result: ingestion accepted (trace id: '.substr($traceId, 0, 8).'…).');
        } else {
            $this->line('Result: ingestion rejected or failed.');
        }

        return $ingestOk;
    }

    /**
     * Diagnose Latitude connectivity and authentication.
     */
    private function diagnoseLatitude(bool $testIngestion): bool
    {
        $enabled = (bool) config('latitude.enabled');
        $endpoint = (string) config('latitude.endpoint');
        $apiKey = (string) config('latitude.api_key');
        $project = (string) config('latitude.project_slug');

        $this->line('Enabled: '.var_export($enabled, true));
        $this->line('Endpoint: '.$endpoint);
        $this->line('Project: '.$project);
        $this->line('Credentials present: '.var_export($apiKey !== '' && $project !== '', true));

        if (! $enabled) {
            $this->line('Failure category: disabled (opt-in; LATITUDE_TRACING_ENABLED=false).');
            $this->line('Result: skipped (telemetry disabled).');

            return true;
        }

        if ($apiKey === '' || $project === '') {
            $this->line('Failure category: misconfigured (missing api_key or project_slug).');
            $this->line('Result: skipped (credentials absent).');

            return false;
        }

        // Latitude has no separate read-only auth endpoint: the ingest endpoint
        // IS the authentication boundary. A minimal OTLP span is the probe.
        $traceId = bin2hex(random_bytes(16));
        $probe = $this->probe(
            'auth+ingestion',
            'POST',
            $endpoint,
            [
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
                'X-Latitude-Project' => $project,
            ],
            $this->otelPayload($traceId, 'telemetry.diagnose.latitude'),
        );

        $this->reportProbe($probe);

        $ok = $probe['status'] !== null && $probe['status'] >= 200 && $probe['status'] < 300;

        if ($ok) {
            $this->line('Result: authenticated and ingestion accepted (trace id: '.substr($traceId, 0, 8).'…).');
        } else {
            $this->line('Result: authentication or ingestion failed.');
        }

        return $ok;
    }

    /**
     * Perform a bounded HTTP probe and classify the failure.
     *
     * @param  string  $method  GET or POST
     * @param  array<string, string>  $headers
     * @param  array<string, mixed>  $body
     * @return array{label: string, method: string, url: string, status: int|null, elapsed_ms: int, body: string, ok: bool, category: string, exception?: string}
     */
    private function probe(string $label, string $method, string $url, array $headers, array $body): array
    {
        $start = microtime(true);

        try {
            $client = Http::timeout(8.0)->withHeaders($headers);

            $response = $method === 'GET'
                ? $client->get($url)
                : $client->post($url, $body);

            $elapsed = (int) round((microtime(true) - $start) * 1000);
            $status = $response->status();
            $bodyStr = (string) $response->body();

            return [
                'label' => $label,
                'method' => $method,
                'url' => $this->sanitizeUrl($url),
                'status' => $status,
                'elapsed_ms' => $elapsed,
                'body' => substr($bodyStr, 0, 300),
                'ok' => $response->successful(),
                'category' => $this->classifyHttp($status, null),
            ];
        } catch (\Throwable $e) {
            $elapsed = (int) round((microtime(true) - $start) * 1000);

            return [
                'label' => $label,
                'method' => $method,
                'url' => $this->sanitizeUrl($url),
                'status' => null,
                'elapsed_ms' => $elapsed,
                'body' => 'EXCEPTION: '.$e->getMessage(),
                'ok' => false,
                'category' => $this->classifyHttp(null, $e),
                'exception' => get_class($e),
            ];
        }
    }

    /**
     * Report a single probe result in a compact, sanitized line.
     *
     * @param  array{label: string, method: string, url: string, status: int|null, elapsed_ms: int, body: string, ok: bool, category: string, exception?: string}  $probe
     */
    private function reportProbe(array $probe): void
    {
        $status = $probe['status'] ?? 'null';
        $this->line(sprintf(
            '  %s: %s %s -> status=%s (%d ms) [category=%s]',
            $probe['label'],
            $probe['method'],
            $probe['url'],
            $status,
            $probe['elapsed_ms'],
            $probe['category'],
        ));

        if ($probe['ok']) {
            $this->line('    body: '.$probe['body']);
        } elseif ($probe['status'] !== null) {
            $this->line('    body: '.$probe['body']);
        } else {
            $this->line('    error: '.$probe['body']);
        }
    }

    /**
     * Derive a human-readable region hint from the base URL.
     */
    private function regionFromUrl(string $url): string
    {
        if (str_contains($url, 'jp.cloud.langfuse.com')) {
            return 'Japan (jp.cloud.langfuse.com)';
        }
        if (str_contains($url, 'cloud.langfuse.com')) {
            return 'Global (cloud.langfuse.com)';
        }
        if (str_contains($url, 'localhost') || str_contains($url, '127.0.0.1')) {
            return 'Local';
        }

        return 'Other';
    }

    /**
     * Sanitise a URL for display: strip any embedded credentials.
     */
    private function sanitizeUrl(string $url): string
    {
        return preg_replace('/^[^:]+:\/\/[^@]+@/', '***:***@', $url);
    }

    /**
     * Classify an HTTP status code or exception into a failure category.
     *
     * @return string One of: dns, connection, tls, timeout, authentication,
     *                authorization, payload_rejection, ingestion_failure, success, unknown
     */
    private function classifyHttp(?int $status, ?\Throwable $exception): string
    {
        if ($exception !== null) {
            $message = $e = $exception->getMessage();
            $class = get_class($exception);

            if (str_contains($class, 'ConnectionException') || str_contains($message, 'Connection refused') || str_contains($message, 'connect')) {
                return 'connection';
            }
            if (str_contains($class, 'SslException') || str_contains($message, 'SSL') || str_contains($message, 'TLS')) {
                return 'tls';
            }
            if (str_contains($class, 'ConnectException') || str_contains($message, 'DNS') || str_contains($message, 'could not resolve') || str_contains($message, 'name resolution')) {
                return 'dns';
            }
            if (str_contains($message, 'timed out') || str_contains($message, 'timeout')) {
                return 'timeout';
            }

            return 'unknown';
        }

        if ($status === null) {
            return 'unknown';
        }

        if ($status >= 200 && $status < 300) {
            return 'success';
        }

        return match ($status) {
            401, 403 => 'authentication',
            400, 422 => 'payload_rejection',
            404 => 'payload_rejection',
            429 => 'ingestion_failure',
            500, 502, 503, 504 => 'ingestion_failure',
            default => 'unknown',
        };
    }

    /**
     * Build a minimal OTLP trace payload for ingestion probes.
     *
     * @return array{resourceSpans: list<array{resource: array{attributes: list<array{key: string, value: array{stringValue: string}}>}, scopeSpans: list<array{scope: array{name: string, version: string}, spans: list<array<string, mixed>>}>}>}
     */
    private function otelPayload(string $traceId, string $spanName): array
    {
        $nowNs = (string) ((int) (new \DateTimeImmutable)->format('U') * 1_000_000_000 + (int) (new \DateTimeImmutable)->format('u') * 1_000);

        return [
            'resourceSpans' => [
                [
                    'resource' => [
                        'attributes' => [
                            ['key' => 'service.name', 'value' => ['stringValue' => 'Trainer Desk']],
                        ],
                    ],
                    'scopeSpans' => [
                        [
                            'scope' => [
                                'name' => 'telemetry.diagnose',
                                'version' => '1.0.0',
                            ],
                            'spans' => [
                                [
                                    'traceId' => $traceId,
                                    'spanId' => bin2hex(random_bytes(8)),
                                    'name' => $spanName,
                                    'kind' => 2, // SPAN_KIND_SERVER
                                    'startTimeUnixNano' => $nowNs,
                                    'endTimeUnixNano' => $nowNs,
                                    'attributes' => [
                                        ['key' => 'langfuse.name', 'value' => ['stringValue' => $spanName]],
                                        ['key' => 'langfuse.type', 'value' => ['stringValue' => 'span']],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
