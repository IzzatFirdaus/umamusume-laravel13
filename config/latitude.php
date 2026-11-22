<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Latitude Telemetry (Trainer's personal account: GitHub IzzatFirdaus)
    |--------------------------------------------------------------------------
    |
    | Opt-in per PRD NFR-1 amendment (2026-11-21). Defaults to off so the
    | loopback-only, no-telemetry product contract is preserved until the
    | Trainer explicitly opts in.
    */

    'enabled' => env('LATITUDE_TRACING_ENABLED', false),

    'endpoint' => env('LATITUDE_ENDPOINT', 'https://ingest.latitude.so/v1/traces'),

    'api_key' => env('LATITUDE_API_KEY'),

    'project_slug' => env('LATITUDE_PROJECT'),

];
