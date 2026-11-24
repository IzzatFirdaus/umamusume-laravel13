<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

it('skips Langfuse when disabled', function (): void {
    Config::set('langfuse.enabled', false);

    $exitCode = Artisan::call('telemetry:diagnose --provider=langfuse');

    expect($exitCode)->toBe(0);
});

it('skips Langfuse when credentials are absent', function (): void {
    Config::set('langfuse.enabled', true);
    Config::set('langfuse.public_key', '');
    Config::set('langfuse.secret_key', '');

    $exitCode = Artisan::call('telemetry:diagnose --provider=langfuse');

    expect($exitCode)->toBe(1);
});

it('reports Langfuse auth check result via mocked HTTP', function (): void {
    Config::set('langfuse.enabled', true);
    Config::set('langfuse.base_url', 'https://jp.cloud.langfuse.com');
    Config::set('langfuse.public_key', 'pk-test');
    Config::set('langfuse.secret_key', 'sk-test');

    Http::fake([
        'jp.cloud.langfuse.com/api/public/projects' => Http::response(['data' => []], 200),
    ]);

    $exitCode = Artisan::call('telemetry:diagnose --provider=langfuse');

    expect($exitCode)->toBe(0);
});

it('reports Langfuse auth failure via mocked HTTP', function (): void {
    Config::set('langfuse.enabled', true);
    Config::set('langfuse.base_url', 'https://jp.cloud.langfuse.com');
    Config::set('langfuse.public_key', 'pk-test');
    Config::set('langfuse.secret_key', 'sk-test');

    Http::fake([
        'jp.cloud.langfuse.com/api/public/projects' => Http::response([], 401),
    ]);

    $exitCode = Artisan::call('telemetry:diagnose --provider=langfuse');

    expect($exitCode)->toBe(1);
});

it('reports Langfuse ingestion result when --test-ingestion is passed', function (): void {
    Config::set('langfuse.enabled', true);
    Config::set('langfuse.base_url', 'https://jp.cloud.langfuse.com');
    Config::set('langfuse.public_key', 'pk-test');
    Config::set('langfuse.secret_key', 'sk-test');

    Http::fake([
        'jp.cloud.langfuse.com/api/public/projects' => Http::response(['data' => []], 200),
        'jp.cloud.langfuse.com/api/public/otel/v1/traces' => Http::response([], 200),
    ]);

    $exitCode = Artisan::call('telemetry:diagnose --provider=langfuse --test-ingestion');

    expect($exitCode)->toBe(0);
});

it('reports Langfuse ingestion failure when --test-ingestion is passed', function (): void {
    Config::set('langfuse.enabled', true);
    Config::set('langfuse.base_url', 'https://jp.cloud.langfuse.com');
    Config::set('langfuse.public_key', 'pk-test');
    Config::set('langfuse.secret_key', 'sk-test');

    Http::fake([
        'jp.cloud.langfuse.com/api/public/projects' => Http::response(['data' => []], 200),
        'jp.cloud.langfuse.com/api/public/otel/v1/traces' => Http::response([], 500),
    ]);

    $exitCode = Artisan::call('telemetry:diagnose --provider=langfuse --test-ingestion');

    expect($exitCode)->toBe(1);
});

it('skips Latitude when disabled', function (): void {
    Config::set('latitude.enabled', false);

    $exitCode = Artisan::call('telemetry:diagnose --provider=latitude');

    expect($exitCode)->toBe(0);
});

it('skips Latitude when credentials are absent', function (): void {
    Config::set('latitude.enabled', true);
    Config::set('latitude.api_key', '');
    Config::set('latitude.project_slug', '');

    $exitCode = Artisan::call('telemetry:diagnose --provider=latitude');

    expect($exitCode)->toBe(1);
});

it('reports Latitude auth+ingestion result via mocked HTTP', function (): void {
    Config::set('latitude.enabled', true);
    Config::set('latitude.endpoint', 'https://ingest.latitude.so/v1/traces');
    Config::set('latitude.api_key', 'test-key');
    Config::set('latitude.project_slug', 'test-project');

    Http::fake([
        'ingest.latitude.so/v1/traces' => Http::response([], 200),
    ]);

    $exitCode = Artisan::call('telemetry:diagnose --provider=latitude');

    expect($exitCode)->toBe(0);
});

it('reports Latitude failure via mocked HTTP', function (): void {
    Config::set('latitude.enabled', true);
    Config::set('latitude.endpoint', 'https://ingest.latitude.so/v1/traces');
    Config::set('latitude.api_key', 'test-key');
    Config::set('latitude.project_slug', 'test-project');

    Http::fake([
        'ingest.latitude.so/v1/traces' => Http::response([], 401),
    ]);

    $exitCode = Artisan::call('telemetry:diagnose --provider=latitude');

    expect($exitCode)->toBe(1);
});

it('rejects invalid provider option', function (): void {
    $exitCode = Artisan::call('telemetry:diagnose --provider=invalid');

    expect($exitCode)->not->toBe(0);
});
