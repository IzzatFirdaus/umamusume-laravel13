<?php

declare(strict_types=1);

return [

    /*
     * IANA timezone used to render datetimes for the Trainer. Storage is always UTC
     * (Pre-Mortem §4.2: never hardcode a locale zone in config/app.php).
     */
    'display_timezone' => env('UMA_DISPLAY_TIMEZONE', 'UTC'),

    'fetch' => [
        // Descriptive UA identifying this local tool; sources are allowlisted below.
        'user_agent' => env('UMA_FETCH_USER_AGENT', 'UmamusumeTrainerCompanion/0.2 (personal local tool)'),
        'retry_times' => 2,
        'lock_timeout' => 120,
    ],

    'match' => [
        // Fuzzy tier threshold (percent); below it a record goes to review as None.
        'fuzzy_threshold' => 85,
    ],

    'cache' => [
        'ttl' => 900,
    ],

    /*
     * Deliberately empty: adding a source requires a robots.txt/rate-limit review
     * and one parser class per source (PRD OQ-2, AGENTS.md Data Engineer rules).
     *
     * Shape:
     *   'key' => [
     *       'url' => 'https://example.org/list',
     *       'parser' => \App\Services\DataPipeline\Parsers\ExampleParser::class,
     *       'delay_ms' => 1000,
     *       'timeout_s' => 15,
     *       'timezone' => 'Asia/Tokyo', // source's announcement zone, recorded on provenance
     *   ],
     */
    'sources' => [],

];
