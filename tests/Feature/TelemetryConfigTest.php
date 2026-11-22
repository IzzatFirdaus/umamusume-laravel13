<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;

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
