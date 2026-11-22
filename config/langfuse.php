<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Langfuse Telemetry (Trainer's personal account: GitHub IzzatFirdaus)
    |--------------------------------------------------------------------------
    |
    | Opt-in per PRD NFR-1 amendment (2026-11-21). Defaults to off so the
    | loopback-only, no-telemetry product contract is preserved until the
    | Trainer explicitly opts in.
    */

    'enabled' => env('LANGFUSE_ENABLED', false),

    'service_name' => env('LANGFUSE_SERVICE_NAME', 'Trainer Desk'),

    'environment' => env('LANGFUSE_TRACING_ENVIRONMENT', 'local'),

    'base_url' => env('LANGFUSE_BASE_URL', 'https://cloud.langfuse.com'),

    'public_key' => env('LANGFUSE_PUBLIC_KEY'),

    'secret_key' => env('LANGFUSE_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | User/session tracing scope
    |--------------------------------------------------------------------------
    */

    'user_tracing' => env('LANGFUSE_USER_TRACING', false),

    'session_tracing' => env('LANGFUSE_SESSION_TRACING', false),

];
