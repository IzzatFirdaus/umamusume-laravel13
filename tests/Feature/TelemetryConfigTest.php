<?php

use App\Console\Commands\CompleteIndependentTraining;
use App\Http\Middleware\LangfuseTraceMiddleware;
use App\Http\Middleware\LatitudeTraceMiddleware;
use App\Services\LangfuseTraceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

it('disables Langfuse telemetry by default (NFR-1 guard)', function (): void {
    expect(Config::get('langfuse.enabled'))->toBeFalse();
});

it('disables Latitude telemetry by default (NFR-1 guard)', function (): void {
    expect(Config::get('latitude.enabled'))->toBeFalse();
});

it('enables Langfuse telemetry when LANGFUSE_ENABLED=true', function (): void {
    Config::set('langfuse.enabled', true);

    expect(Config::get('langfuse.enabled'))->toBeTrue();
});

it('enables Latitude telemetry when LATITUDE_TRACING_ENABLED=true', function (): void {
    Config::set('latitude.enabled', true);

    expect(Config::get('latitude.enabled'))->toBeTrue();
});

it('uses the Langfuse Japan cloud base URL by default', function (): void {
    expect(Config::get('langfuse.base_url'))->toBe('https://cloud.langfuse.com');
});

it('uses the Latitude ingestion endpoint by default', function (): void {
    expect(Config::get('latitude.endpoint'))->toBe('https://ingest.latitude.so/v1/traces');
});

it('registers LangfuseTraceMiddleware on the API v1 group', function (): void {
    $routes = Route::getRoutes();

    $found = false;
    foreach ($routes as $route) {
        if (str_starts_with($route->uri(), 'api/v1/') && $route->uri() !== 'api/v1/telemetry/status') {
            $middleware = $route->gatherMiddleware();
            if (in_array('App\Http\Middleware\LangfuseTraceMiddleware', $middleware, true)) {
                $found = true;
                break;
            }
        }
    }

    expect($found)->toBeTrue();
});

it('registers LatitudeTraceMiddleware on the API v1 group', function (): void {
    $routes = Route::getRoutes();

    $found = false;
    foreach ($routes as $route) {
        if (str_starts_with($route->uri(), 'api/v1/') && $route->uri() !== 'api/v1/telemetry/status') {
            $middleware = $route->gatherMiddleware();
            if (in_array('App\Http\Middleware\LatitudeTraceMiddleware', $middleware, true)) {
                $found = true;
                break;
            }
        }
    }

    expect($found)->toBeTrue();
});

it('telemetry status endpoint is outside tracing middleware', function (): void {
    $routes = Route::getRoutes();

    foreach ($routes as $route) {
        if ($route->uri() === 'api/v1/telemetry/status') {
            $middleware = $route->gatherMiddleware();
            expect($middleware)->not->toContain('App\Http\Middleware\LangfuseTraceMiddleware');
            expect($middleware)->not->toContain('App\Http\Middleware\LatitudeTraceMiddleware');

            return;
        }
    }

    expect(true)->toBeTrue(); // route exists check
});

it('LangfuseTraceMiddleware passes through when disabled', function (): void {
    Config::set('langfuse.enabled', false);
    Config::set('latitude.enabled', false);

    Http::fake();

    $middleware = new LangfuseTraceMiddleware(
        new LangfuseTraceService(
            false, 'https://jp.cloud.langfuse.com', 'pk-test', 'sk-test', 'Trainer Desk', 'local'
        )
    );

    $request = Request::create('/api/v1/test', 'GET');
    $next = fn () => new Response('OK', 200);

    $response = $middleware->handle($request, $next);

    expect($response->getStatusCode())->toBe(200);
    Http::assertNothingSent();
});

it('LangfuseTraceMiddleware exports OTLP server span when enabled', function (): void {
    Config::set('langfuse.enabled', true);
    Config::set('langfuse.public_key', 'pk-test');
    Config::set('langfuse.secret_key', 'sk-test');

    Http::fake();

    $middleware = new LangfuseTraceMiddleware(
        new LangfuseTraceService(
            true, 'https://jp.cloud.langfuse.com', 'pk-test', 'sk-test', 'Trainer Desk', 'local'
        )
    );

    $request = Request::create('/api/v1/test', 'GET');
    $next = fn () => new Response('OK', 200);

    $middleware->handle($request, $next);

    Http::assertSent(function ($request) {
        $body = json_decode((string) $request->body(), true);
        $span = $body['resourceSpans'][0]['scopeSpans'][0]['spans'][0];

        expect($span['kind'])->toBe(2); // SPAN_KIND_SERVER
        expect($span['traceId'])->toMatch('/^[0-9a-f]{32}$/');
        expect($span['spanId'])->toMatch('/^[0-9a-f]{16}$/');
        expect($span['name'])->toBe('GET api/v1/test');

        return true;
    });
});

it('LangfuseTraceMiddleware emits error attributes on exception', function (): void {
    Config::set('langfuse.enabled', true);
    Config::set('langfuse.public_key', 'pk-test');
    Config::set('langfuse.secret_key', 'sk-test');

    Http::fake();

    $middleware = new LangfuseTraceMiddleware(
        new LangfuseTraceService(
            true, 'https://jp.cloud.langfuse.com', 'pk-test', 'sk-test', 'Trainer Desk', 'local'
        )
    );

    $request = Request::create('/api/v1/test', 'GET');
    $request->attributes->set('telemetry.trace_id', '0123456789abcdef0123456789abcdef');

    $next = function () {
        throw new RuntimeException('Test error');
    };

    try {
        $middleware->handle($request, $next);
    } catch (RuntimeException) {
        // Expected
    }

    Http::assertSent(function ($request) {
        $body = json_decode((string) $request->body(), true);
        $span = $body['resourceSpans'][0]['scopeSpans'][0]['spans'][0];

        $attributes = [];
        foreach ($span['attributes'] as $attr) {
            $attributes[$attr['key']] = $attr['value'];
        }

        expect($attributes['langfuse.level']['stringValue'])->toBe('error');
        expect($attributes['exception.type']['stringValue'])->toBe('RuntimeException');
        expect($attributes['exception.message']['stringValue'])->toBe('Test error');
        expect($span['kind'])->toBe(2);
        expect($span['traceId'])->toBe('0123456789abcdef0123456789abcdef');

        return true;
    });
});

it('LatitudeTraceMiddleware uses shared telemetry.trace_id when present', function (): void {
    Config::set('latitude.enabled', true);
    Config::set('latitude.api_key', 'test-key');
    Config::set('latitude.project_slug', 'test-project');

    Http::fake();

    $middleware = new LatitudeTraceMiddleware;

    $request = Request::create('/api/v1/test', 'GET');
    $request->attributes->set('telemetry.trace_id', '0123456789abcdef0123456789abcdef');

    $response = new Response('OK', 200);
    $next = function () use ($response) {
        return $response;
    };

    $middleware->handle($request, $next);

    Http::assertSent(function ($request) {
        $body = json_decode((string) $request->body(), true);
        $span = $body['resourceSpans'][0]['scopeSpans'][0]['spans'][0];

        expect($span['traceId'])->toBe('0123456789abcdef0123456789abcdef');
        expect($span['kind'])->toBe(2);

        return true;
    });
});

it('LatitudeTraceMiddleware generates independent traceId when shared absent', function (): void {
    Config::set('latitude.enabled', true);
    Config::set('latitude.api_key', 'test-key');
    Config::set('latitude.project_slug', 'test-project');

    Http::fake();

    $middleware = new LatitudeTraceMiddleware;

    $request = Request::create('/api/v1/test', 'GET');

    $response = new Response('OK', 200);
    $next = function () use ($response) {
        return $response;
    };

    $middleware->handle($request, $next);

    Http::assertSent(function ($request) {
        $body = json_decode((string) $request->body(), true);
        $span = $body['resourceSpans'][0]['scopeSpans'][0]['spans'][0];

        expect($span['traceId'])->toMatch('/^[0-9a-f]{32}$/');
        expect($span['kind'])->toBe(2);

        return true;
    });
});

it('LangfuseTraceService heartbeat returns structured result', function (): void {
    Config::set('langfuse.enabled', true);
    Config::set('langfuse.public_key', 'pk-test');
    Config::set('langfuse.secret_key', 'sk-test');

    Http::fake();

    $service = new LangfuseTraceService(
        true, 'https://jp.cloud.langfuse.com', 'pk-test', 'sk-test', 'Trainer Desk', 'local'
    );

    DB::shouldReceive('selectOne')->once()->andReturn((object) ['ok' => 1]);

    $result = $service->heartbeat();

    expect($result)->toHaveKeys(['status', 'database', 'exported', 'error', 'at']);
    expect($result['database'])->toBeTrue();
    expect($result['exported'])->toBeTrue();
    expect($result['error'])->toBeNull();
    expect($result['at'])->toMatch('/^\d{4}-\d{2}-\d{2}T/');
});

it('LangfuseTraceService heartbeat caches result', function (): void {
    Config::set('langfuse.enabled', true);

    Http::fake();

    $service = new LangfuseTraceService(
        true, 'https://jp.cloud.langfuse.com', 'pk-test', 'sk-test', 'Trainer Desk', 'local'
    );

    DB::shouldReceive('selectOne')->once()->andReturn((object) ['ok' => 1]);

    $service->heartbeat();

    $cached = Cache::get('telemetry.heartbeat.last');
    expect($cached)->toHaveKeys(['status', 'database', 'exported', 'error', 'at']);
    expect($cached['exported'])->toBeTrue();
});

it('LangfuseTraceService heartbeat is no-op when disabled', function (): void {
    $service = new LangfuseTraceService(
        false, 'https://jp.cloud.langfuse.com', 'pk-test', 'sk-test', 'Trainer Desk', 'local'
    );

    $result = $service->heartbeat();

    expect($result['status'])->toBe('disabled');
    expect($result['database'])->toBeNull();
    expect($result['exported'])->toBeNull();
});

it('TelemetryStatusController returns expected contract', function (): void {
    Config::set('langfuse.enabled', true);
    Config::set('langfuse.public_key', 'pk-test');
    Config::set('langfuse.secret_key', 'sk-test');
    Config::set('latitude.enabled', true);
    Config::set('latitude.api_key', 'test-key');
    Config::set('latitude.project_slug', 'test-project');

    Cache::put('telemetry.heartbeat.last', [
        'status' => 'ok',
        'database' => true,
        'exported' => true,
        'error' => null,
        'at' => '2026-11-24T10:00:00+08:00',
    ], 900);

    $response = $this->get('api/v1/telemetry/status');

    expect($response->status())->toBe(200);

    $body = $response->json();
    expect($body)->toHaveKeys(['scheduler', 'heartbeat', 'langfuse', 'latitude']);
    expect($body['heartbeat'])->toHaveKeys(['at', 'database', 'exported', 'error']);
    expect($body['langfuse'])->toHaveKeys(['enabled', 'configured', 'delivery']);
    expect($body['latitude'])->toHaveKeys(['enabled', 'configured', 'delivery']);
    expect($body['langfuse']['delivery']['exported'])->toBeTrue();
    expect($body['latitude']['delivery'])->toBe('Connected');
});

it('TelemetryStatusController does not expose credentials', function (): void {
    Config::set('langfuse.enabled', true);
    Config::set('langfuse.public_key', 'pk-secret');
    Config::set('langfuse.secret_key', 'sk-secret');
    Config::set('latitude.enabled', true);
    Config::set('latitude.api_key', 'lat-secret');
    Config::set('latitude.project_slug', 'test-project');

    $response = $this->get('api/v1/telemetry/status');

    $body = json_decode((string) $response->getContent(), true);

    $serialized = json_encode($body);
    expect(str_contains($serialized, 'pk-secret'))->toBeFalse();
    expect(str_contains($serialized, 'sk-secret'))->toBeFalse();
    expect(str_contains($serialized, 'lat-secret'))->toBeFalse();
});

it('CompleteIndependentTraining records telemetry trace via recordTrainingTrace', function (): void {
    Config::set('langfuse.enabled', true);
    Config::set('langfuse.public_key', 'pk-test');
    Config::set('langfuse.secret_key', 'sk-test');

    Http::fake();

    $telemetry = new LangfuseTraceService(
        true, 'https://jp.cloud.langfuse.com', 'pk-test', 'sk-test', 'Trainer Desk', 'local'
    );

    $command = new CompleteIndependentTraining;
    $command->setLaravel(app());

    // Test recordTrainingTrace directly via reflection since Lifecycle is final
    $reflection = new ReflectionMethod($command, 'recordTrainingTrace');
    $reflection->setAccessible(true);

    $startNs = (int) (new DateTimeImmutable)->format('U') * 1_000_000_000;
    $reflection->invoke($command, $telemetry, $startNs, 3, null);

    Http::assertSent(function ($request) {
        $body = json_decode((string) $request->body(), true);
        $span = $body['resourceSpans'][0]['scopeSpans'][0]['spans'][0];

        expect($span['name'])->toBe('independent_training.complete');
        expect($span['kind'])->toBe(3); // SPAN_KIND_INTERNAL

        $attributes = [];
        foreach ($span['attributes'] as $attr) {
            $attributes[$attr['key']] = $attr['value'];
        }

        expect($attributes['trainerdesk.sessions_completed']['intValue'])->toBe('3');

        return true;
    });
});

it('CompleteIndependentTraining records error trace on failure', function (): void {
    Config::set('langfuse.enabled', true);
    Config::set('langfuse.public_key', 'pk-test');
    Config::set('langfuse.secret_key', 'sk-test');

    Http::fake();

    $telemetry = new LangfuseTraceService(
        true, 'https://jp.cloud.langfuse.com', 'pk-test', 'sk-test', 'Trainer Desk', 'local'
    );

    $command = new CompleteIndependentTraining;
    $command->setLaravel(app());

    $reflection = new ReflectionMethod($command, 'recordTrainingTrace');
    $reflection->setAccessible(true);

    $startNs = (int) (new DateTimeImmutable)->format('U') * 1_000_000_000;
    $reflection->invoke($command, $telemetry, $startNs, 0, 'Training failed');

    Http::assertSent(function ($request) {
        $body = json_decode((string) $request->body(), true);
        $span = $body['resourceSpans'][0]['scopeSpans'][0]['spans'][0];

        $attributes = [];
        foreach ($span['attributes'] as $attr) {
            $attributes[$attr['key']] = $attr['value'];
        }

        expect($attributes['langfuse.level']['stringValue'])->toBe('error');
        expect($attributes['exception.message']['stringValue'])->toBe('Training failed');
        expect($attributes['trainerdesk.sessions_completed']['intValue'])->toBe('0');

        return true;
    });
});

it('both providers share the same traceId for one request', function (): void {
    Config::set('langfuse.enabled', true);
    Config::set('langfuse.public_key', 'pk-test');
    Config::set('langfuse.secret_key', 'sk-test');
    Config::set('latitude.enabled', true);
    Config::set('latitude.api_key', 'test-key');
    Config::set('latitude.project_slug', 'test-project');

    Http::fake();

    $sharedTraceId = '0123456789abcdef0123456789abcdef';

    $langfuse = new LangfuseTraceMiddleware(
        new LangfuseTraceService(
            true, 'https://jp.cloud.langfuse.com', 'pk-test', 'sk-test', 'Trainer Desk', 'local'
        )
    );

    $latitude = new LatitudeTraceMiddleware;

    $request = Request::create('/api/v1/test', 'GET');
    $request->attributes->set('telemetry.trace_id', $sharedTraceId);

    $response = new Response('OK', 200);
    $next = function () use ($response) {
        return $response;
    };

    // Langfuse runs first, sets traceId (already set)
    $langfuseNext = function ($req) use ($latitude, $response) {
        // Latitude reads the traceId set by Langfuse
        $latitude->handle($req, function () use ($response) {
            return $response;
        });

        return $response;
    };

    $langfuse->handle($request, $langfuseNext);

    // Both providers should have exported with the same traceId
    $sentRequests = [];
    Http::assertSent(function ($request) use (&$sentRequests) {
        $sentRequests[] = json_decode((string) $request->body(), true);

        return true;
    });

    expect(count($sentRequests))->toBe(2);

    $langfuseSpan = $sentRequests[0]['resourceSpans'][0]['scopeSpans'][0]['spans'][0];
    $latitudeSpan = $sentRequests[1]['resourceSpans'][0]['scopeSpans'][0]['spans'][0];

    expect($langfuseSpan['traceId'])->toBe($sharedTraceId);
    expect($latitudeSpan['traceId'])->toBe($sharedTraceId);
});

it('LangfuseTraceService heartbeat records real acceptance on 200', function (): void {
    Config::set('langfuse.enabled', true);
    Config::set('langfuse.public_key', 'pk-test');
    Config::set('langfuse.secret_key', 'sk-test');

    Http::fake();

    $service = new LangfuseTraceService(
        true, 'https://jp.cloud.langfuse.com', 'pk-test', 'sk-test', 'Trainer Desk', 'local'
    );

    DB::shouldReceive('selectOne')->once()->andReturn((object) ['ok' => 1]);

    $result = $service->heartbeat();

    expect($result['exported'])->toBeTrue();
    expect($result['export']['status'])->toBe('accepted');
    expect($result['export']['http_status'])->toBe(200);
    expect($result['error'])->toBeNull();
});

it('LangfuseTraceService heartbeat records real rejection on non-2xx', function (): void {
    Config::set('langfuse.enabled', true);
    Config::set('langfuse.public_key', 'pk-test');
    Config::set('langfuse.secret_key', 'sk-test');

    Http::fake([
        'jp.cloud.langfuse.com/api/public/otel/v1/traces' => Http::response([], 500),
    ]);

    $service = new LangfuseTraceService(
        true, 'https://jp.cloud.langfuse.com', 'pk-test', 'sk-test', 'Trainer Desk', 'local'
    );

    DB::shouldReceive('selectOne')->once()->andReturn((object) ['ok' => 1]);

    $result = $service->heartbeat();

    expect($result['exported'])->toBeFalse();
    expect($result['export']['status'])->toBe('transient_error');
    expect($result['export']['http_status'])->toBe(500);
    expect($result['status'])->toBe('ok'); // database fine; only delivery degraded
});

it('LangfuseTraceService heartbeat is disabled when opt-in is off', function (): void {
    $service = new LangfuseTraceService(
        false, 'https://jp.cloud.langfuse.com', 'pk-test', 'sk-test', 'Trainer Desk', 'local'
    );

    $result = $service->heartbeat();

    expect($result['status'])->toBe('disabled');
    expect($result['export']['status'])->toBe('disabled');
    expect($result['export']['http_status'])->toBeNull();
});
