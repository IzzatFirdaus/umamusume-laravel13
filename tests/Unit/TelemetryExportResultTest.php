<?php

use App\Support\TelemetryExportResult;

it('classifies 2xx responses as accepted', function (): void {
    expect(TelemetryExportResult::fromHttp(200)->status)->toBe(TelemetryExportResult::ACCEPTED);
    expect(TelemetryExportResult::fromHttp(299)->status)->toBe(TelemetryExportResult::ACCEPTED);
});

it('classifies 207 as partially rejected', function (): void {
    expect(TelemetryExportResult::fromHttp(207)->status)->toBe(TelemetryExportResult::PARTIALLY_REJECTED);
});

it('classifies 401/403 as auth failures', function (): void {
    expect(TelemetryExportResult::fromHttp(401)->status)->toBe(TelemetryExportResult::AUTH_FAILURE);
    expect(TelemetryExportResult::fromHttp(403)->status)->toBe(TelemetryExportResult::AUTH_FAILURE);
});

it('classifies non-auth non-rate-limit 4xx as rejected', function (): void {
    expect(TelemetryExportResult::fromHttp(400)->status)->toBe(TelemetryExportResult::REJECTED);
    expect(TelemetryExportResult::fromHttp(422)->status)->toBe(TelemetryExportResult::REJECTED);
    expect(TelemetryExportResult::fromHttp(404)->status)->toBe(TelemetryExportResult::REJECTED);
});

it('classifies 429 as rate limiting', function (): void {
    expect(TelemetryExportResult::fromHttp(429)->status)->toBe(TelemetryExportResult::RATE_LIMIT);
});

it('classifies transient 5xx as transient error', function (): void {
    expect(TelemetryExportResult::fromHttp(500)->status)->toBe(TelemetryExportResult::TRANSIENT_ERROR);
    expect(TelemetryExportResult::fromHttp(502)->status)->toBe(TelemetryExportResult::TRANSIENT_ERROR);
    expect(TelemetryExportResult::fromHttp(503)->status)->toBe(TelemetryExportResult::TRANSIENT_ERROR);
    expect(TelemetryExportResult::fromHttp(504)->status)->toBe(TelemetryExportResult::TRANSIENT_ERROR);
});

it('inspects the body for OTLP partial rejection on HTTP 200', function (): void {
    // rejectedSpans as a string (Protobuf JSON serialises int64 as a string).
    expect(
        TelemetryExportResult::fromHttp(200, '{"partialSuccess":{"rejectedSpans":"3"}}')->status
    )->toBe(TelemetryExportResult::PARTIALLY_REJECTED);

    // rejectedSpans as an integer.
    expect(
        TelemetryExportResult::fromHttp(200, '{"partialSuccess":{"rejectedSpans":2}}')->status
    )->toBe(TelemetryExportResult::PARTIALLY_REJECTED);
});

it('treats HTTP 200 with zero rejections as accepted even with a warning', function (): void {
    // accepted-with-warning: partialSuccess present, rejectedSpans == 0,
    // non-empty errorMessage — delivery still succeeded.
    expect(
        TelemetryExportResult::fromHttp(
            200,
            '{"partialSuccess":{"rejectedSpans":0,"errorMessage":"span limit warning"}}'
        )->status
    )->toBe(TelemetryExportResult::ACCEPTED);

    // partialSuccess null / absent → accepted.
    expect(TelemetryExportResult::fromHttp(200, '{"partialSuccess":null}')->status)
        ->toBe(TelemetryExportResult::ACCEPTED);
    expect(TelemetryExportResult::fromHttp(200, '{}')->status)->toBe(TelemetryExportResult::ACCEPTED);
    expect(TelemetryExportResult::fromHttp(200, '{"data":[]}')->status)->toBe(TelemetryExportResult::ACCEPTED);
});

it('does not misclassify non-JSON or protobuf bodies as partial rejection', function (): void {
    expect(TelemetryExportResult::fromHttp(200, "\x0a\x0b binary protobuf")->status)
        ->toBe(TelemetryExportResult::ACCEPTED);
    expect(TelemetryExportResult::fromHttp(200, 'not json at all')->status)
        ->toBe(TelemetryExportResult::ACCEPTED);
});

it('classifies transport exceptions into connection failure or timeout', function (): void {
    $connection = new RuntimeException('Connection refused');
    $dns = new RuntimeException('could not resolve host');
    $tls = new RuntimeException('SSL handshake failed');
    $timeout = new RuntimeException('timed out');
    $generic = new RuntimeException('boom');

    expect(TelemetryExportResult::fromThrowable($connection)->status)->toBe(TelemetryExportResult::CONNECTION_FAILURE);
    expect(TelemetryExportResult::fromThrowable($dns)->status)->toBe(TelemetryExportResult::CONNECTION_FAILURE);
    expect(TelemetryExportResult::fromThrowable($tls)->status)->toBe(TelemetryExportResult::CONNECTION_FAILURE);
    expect(TelemetryExportResult::fromThrowable($timeout)->status)->toBe(TelemetryExportResult::TIMEOUT);
    expect(TelemetryExportResult::fromThrowable($generic)->status)->toBe(TelemetryExportResult::REJECTED);
    expect(TelemetryExportResult::fromThrowable($generic)->error)->toBe('boom');
});

it('provides named constructors for skipped, disabled, attempted, independently verified', function (): void {
    expect(TelemetryExportResult::disabled()->status)->toBe(TelemetryExportResult::DISABLED);
    expect(TelemetryExportResult::skipped()->status)->toBe(TelemetryExportResult::SKIPPED);
    expect(TelemetryExportResult::attempted()->status)->toBe(TelemetryExportResult::ATTEMPTED);
    expect(TelemetryExportResult::independentlyVerified(200)->status)->toBe(TelemetryExportResult::INDEPENDENTLY_VERIFIED);
});

it('isAccepted is true only for accepted and independently verified', function (): void {
    expect(TelemetryExportResult::accepted(200)->isAccepted())->toBeTrue();
    expect(TelemetryExportResult::independentlyVerified(200)->isAccepted())->toBeTrue();
    expect(TelemetryExportResult::disabled()->isAccepted())->toBeFalse();
    expect(TelemetryExportResult::skipped()->isAccepted())->toBeFalse();
    expect(TelemetryExportResult::rejected(500)->isAccepted())->toBeFalse();
    expect(TelemetryExportResult::fromHttp(401)->isAccepted())->toBeFalse();
});

it('isFailure is true for rejected, auth failure, rate limit, transient error, connection failure, timeout', function (): void {
    expect(TelemetryExportResult::rejected(400)->isFailure())->toBeTrue();
    expect(TelemetryExportResult::fromHttp(401)->isFailure())->toBeTrue();
    expect(TelemetryExportResult::fromHttp(429)->isFailure())->toBeTrue();
    expect(TelemetryExportResult::fromHttp(503)->isFailure())->toBeTrue();
    expect(TelemetryExportResult::connectionFailure(null)->isFailure())->toBeTrue();
    expect(TelemetryExportResult::timeout()->isFailure())->toBeTrue();
    expect(TelemetryExportResult::partiallyRejected(200)->isFailure())->toBeTrue();
    expect(TelemetryExportResult::accepted(200)->isFailure())->toBeFalse();
    expect(TelemetryExportResult::disabled()->isFailure())->toBeFalse();
});

it('serializes to a snake-cased array', function (): void {
    $result = TelemetryExportResult::fromHttp(502, null, 450);

    expect($result->toArray())->toBe([
        'status' => 'transient_error',
        'http_status' => 502,
        'elapsed_ms' => 450,
        'error' => null,
    ]);
});
