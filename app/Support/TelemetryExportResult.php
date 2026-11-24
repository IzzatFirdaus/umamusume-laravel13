<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Normalized result of a single telemetry export attempt to one provider.
 *
 * Status categories:
 *   disabled               telemetry opt-in off; no attempt made
 *   skipped                enabled but misconfigured (missing credentials)
 *   attempted              HTTP request dispatched; outcome not yet known
 *   accepted               provider returned 2xx and accepted all spans
 *   partially_rejected     HTTP 200 with OTLP partialSuccess.rejectedSpans > 0,
 *                          or HTTP 207 Multi-Status
 *   rejected               provider returned a non-retryable 4xx (excluding auth
 *                          and rate limiting) — complete rejection
 *   auth_failure           401/403 — credentials rejected
 *   rate_limit             429 — throttled; retryable per OTLP spec
 *   transient_error        5xx (500/502/503/504) — transient server error; retryable
 *   connection_failure     DNS / connect / TLS failure
 *   timeout                request exceeded its timeout
 *   independently_verified diagnostic probe independently confirmed acceptance
 *
 * OTLP/HTTP response rules (https://opentelemetry.io/docs/specs/otlp/):
 *   - Full success: HTTP 200, partial_success unset / rejectedSpans == 0.
 *   - Partial success: HTTP 200, partialSuccess.rejectedSpans > 0. Do NOT retry.
 *   - Accepted-with-warning: partialSuccess present, rejectedSpans == 0,
 *     non-empty errorMessage — still classified as accepted (delivery succeeded).
 *   - 429/503 throttling: retryable. 400 bad data: do not retry.
 *
 * Fail-open: this value object only records the outcome; it never throws.
 */
final class TelemetryExportResult
{
    public const string DISABLED = 'disabled';

    public const string SKIPPED = 'skipped';

    public const string ATTEMPTED = 'attempted';

    public const string ACCEPTED = 'accepted';

    public const string PARTIALLY_REJECTED = 'partially_rejected';

    public const string REJECTED = 'rejected';

    public const string AUTH_FAILURE = 'auth_failure';

    public const string RATE_LIMIT = 'rate_limit';

    public const string TRANSIENT_ERROR = 'transient_error';

    public const string CONNECTION_FAILURE = 'connection_failure';

    public const string TIMEOUT = 'timeout';

    public const string INDEPENDENTLY_VERIFIED = 'independently_verified';

    /** @return array<string, true> */
    public static function acceptedStatuses(): array
    {
        return [
            self::ACCEPTED => true,
            self::INDEPENDENTLY_VERIFIED => true,
        ];
    }

    /** @return array<string, true> */
    public static function failureStatuses(): array
    {
        return [
            self::REJECTED => true,
            self::PARTIALLY_REJECTED => true,
            self::AUTH_FAILURE => true,
            self::RATE_LIMIT => true,
            self::TRANSIENT_ERROR => true,
            self::CONNECTION_FAILURE => true,
            self::TIMEOUT => true,
        ];
    }

    public function __construct(
        public readonly string $status,
        public readonly ?int $httpStatus = null,
        public readonly ?int $elapsedMs = null,
        public readonly ?string $error = null,
    ) {}

    public static function disabled(): self
    {
        return new self(self::DISABLED);
    }

    public static function skipped(): self
    {
        return new self(self::SKIPPED);
    }

    public static function attempted(?int $httpStatus = null): self
    {
        return new self(self::ATTEMPTED, $httpStatus);
    }

    public static function accepted(int $httpStatus, ?int $elapsedMs = null): self
    {
        return new self(self::ACCEPTED, $httpStatus, $elapsedMs);
    }

    public static function partiallyRejected(int $httpStatus, ?int $elapsedMs = null): self
    {
        return new self(self::PARTIALLY_REJECTED, $httpStatus, $elapsedMs);
    }

    public static function rejected(int $httpStatus, ?int $elapsedMs = null): self
    {
        return new self(self::REJECTED, $httpStatus, $elapsedMs);
    }

    public static function authFailure(int $httpStatus, ?int $elapsedMs = null): self
    {
        return new self(self::AUTH_FAILURE, $httpStatus, $elapsedMs);
    }

    public static function rateLimit(int $httpStatus, ?int $elapsedMs = null): self
    {
        return new self(self::RATE_LIMIT, $httpStatus, $elapsedMs);
    }

    public static function transientError(int $httpStatus, ?int $elapsedMs = null): self
    {
        return new self(self::TRANSIENT_ERROR, $httpStatus, $elapsedMs);
    }

    public static function connectionFailure(?string $error = null, ?int $elapsedMs = null): self
    {
        return new self(self::CONNECTION_FAILURE, null, $elapsedMs, $error);
    }

    public static function timeout(?int $elapsedMs = null, ?string $error = null): self
    {
        return new self(self::TIMEOUT, null, $elapsedMs, $error);
    }

    public static function independentlyVerified(int $httpStatus, ?int $elapsedMs = null): self
    {
        return new self(self::INDEPENDENTLY_VERIFIED, $httpStatus, $elapsedMs);
    }

    /**
     * Classify a successful HTTP response into a status category, inspecting the
     * response body for OTLP partial-success signals.
     *
     * @param  string|null  $body  raw response body (JSON or protobuf; only JSON
     *                             is inspected for partialSuccess)
     */
    public static function fromHttp(int $httpStatus, ?string $body = null, ?int $elapsedMs = null): self
    {
        if ($httpStatus >= 200 && $httpStatus < 300) {
            if ($httpStatus === 207) {
                return self::partiallyRejected($httpStatus, $elapsedMs);
            }

            // OTLP partial success: HTTP 200 with partialSuccess.rejectedSpans > 0.
            // rejectedSpans is int64, serialized as a string in JSON.
            $rejected = self::rejectedSpansFromBody($body);
            if ($rejected !== null && $rejected > 0) {
                return self::partiallyRejected($httpStatus, $elapsedMs);
            }

            return self::accepted($httpStatus, $elapsedMs);
        }

        return match ($httpStatus) {
            401, 403 => self::authFailure($httpStatus, $elapsedMs),
            429 => self::rateLimit($httpStatus, $elapsedMs),
            500, 502, 503, 504 => self::transientError($httpStatus, $elapsedMs),
            default => self::rejected($httpStatus, $elapsedMs),
        };
    }

    /**
     * Extract the rejected-item count from an OTLP JSON response body.
     *
     * Returns null when the body is not a parseable OTLP JSON response or carries
     * no rejection count. Protobuf (binary) bodies are intentionally not parsed.
     *
     * @return int|null null when no rejection count can be determined
     */
    private static function rejectedSpansFromBody(?string $body): ?int
    {
        if ($body === null || $body === '') {
            return null;
        }

        $decoded = json_decode($body, true);
        if (! is_array($decoded)) {
            return null;
        }

        $partial = $decoded['partialSuccess'] ?? null;
        if (! is_array($partial)) {
            return null;
        }

        $raw = $partial['rejectedSpans'] ?? null;
        if ($raw === null || $raw === '') {
            return null;
        }

        if (is_int($raw)) {
            return $raw;
        }

        if (is_string($raw) && ctype_digit($raw)) {
            return (int) $raw;
        }

        return null;
    }

    /**
     * Classify a thrown transport exception into a status category.
     */
    public static function fromThrowable(\Throwable $e, ?int $elapsedMs = null): self
    {
        $message = $e->getMessage();
        $class = get_class($e);

        if (str_contains($class, 'ConnectException')
            || str_contains($class, 'ConnectionException')
            || str_contains($message, 'Connection refused')
            || str_contains($message, 'connect')
            || str_contains($message, 'DNS')
            || str_contains($message, 'could not resolve')
            || str_contains($message, 'name resolution')
            || str_contains($class, 'SslException')
            || str_contains($message, 'SSL')
            || str_contains($message, 'TLS')) {
            return self::connectionFailure($message, $elapsedMs);
        }

        if (str_contains($message, 'timed out') || str_contains($message, 'timeout')) {
            return self::timeout($elapsedMs, $message);
        }

        return new self(self::REJECTED, null, $elapsedMs, $message);
    }

    public function isAccepted(): bool
    {
        return isset(self::acceptedStatuses()[$this->status]);
    }

    public function isFailure(): bool
    {
        return isset(self::failureStatuses()[$this->status]);
    }

    /**
     * @return array{status: string, http_status: int|null, elapsed_ms: int|null, error: string|null}
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'http_status' => $this->httpStatus,
            'elapsed_ms' => $this->elapsedMs,
            'error' => $this->error,
        ];
    }
}
