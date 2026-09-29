<?php

declare(strict_types=1);
use App\Services\DataPipeline\Parsers\GametoraCharacterParser;
use App\Services\DataPipeline\Parsers\GametoraRaceCatalogParser;
use App\Services\DataPipeline\Parsers\GametoraSkillsParser;

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
     * Adding a source requires a robots.txt/rate-limit review and one parser class
     * per source (PRD OQ-2, AGENTS.md Data Engineer rules).
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
    'sources' => [
        /*
         * Owner approved this entry on 2026-09-27 with conservative politeness
         * defaults rather than a live robots.txt check, which was outside that
         * session's network scope. The dataset is a static JSON document, so one
         * request per fetch plus the per-source lock and cache TTL bound the load.
         *
         * The hash in the URL is a cache-busting token published in
         * https://gametora.com/data/manifests/umamusume.json ; it rotates when the
         * source republishes, so a stale hash surfaces as a fetch failure and not
         * as silently old data.
         */
        'gametora-characters' => [
            'url' => 'https://gametora.com/data/umamusume/character-cards.679f7c2e.json',
            'parser' => GametoraCharacterParser::class,
            'delay_ms' => 1000,
            'timeout_s' => 15,
            'timezone' => 'Asia/Tokyo',
        ],

        /*
         * Career race catalogue: one row per race per turn, feeding
         * `race_catalog_slots` (ADR-0003 Amendment R3, which assigns reference data
         * to the fetch engine rather than to a seeder).
         *
         * Owner gate, stated rather than assumed: the review this entry needs is
         * the same one that approved `gametora-characters` above, and the reasons
         * are the same ones, not a fresh judgement.
         *   - Same host, same publisher, same path shape as the approved entry.
         *   - Same response class: a static JSON document. No HTML, no JS, no
         *     crawler directives to observe, no crawl budget consumed.
         *   - Same politeness class: one request per fetch, and the per-source
         *     lock, delay_ms and cache TTL already bound the load.
         *   - Same cache-busting hash convention, so a stale hash fails loudly
         *     instead of serving old data.
         *
         * Timezone is Asia/Tokyo because the dataset is published by the same
         * Japanese operator; the race calendar itself carries no wall-clock times,
         * so the zone is recorded for provenance and is not used in any date
         * arithmetic. `docs/scenarios/09-global-race-calendar.md` is the reading of
         * this dataset that was checked against [Global] client captures.
         */
        'gametora-race-catalog' => [
            'url' => 'https://gametora.com/data/umamusume/race_instances.294424fc.json',
            'parser' => GametoraRaceCatalogParser::class,
            'delay_ms' => 1000,
            'timeout_s' => 15,
            'timezone' => 'Asia/Tokyo',
        ],

        /*
         * The skill catalogue (PRD FR-D-1 as amended 2026-09-29; ADR-0011).
         *
         * Owner approval, stated rather than assumed: the same review that approved
         * `gametora-characters` above, re-used the way `gametora-race-catalog` re-used it.
         * Same host, same publisher, one static JSON document, no HTML, no JS, no crawler
         * directive to observe, no crawl budget consumed, and the politeness bounds already
         * set by `delay_ms`, the per-source lock and the cache TTL.
         *
         * One difference is recorded instead of glossed: this source issues **two** requests
         * per fetch, because the document URL is resolved through `manifest` first. That is
         * the response to KI-24 — a pinned hash does not fail when it goes stale, it answers
         * `200` with the superseded document, which is the opposite of what the comment above
         * this block has been claiming since 2026-09-27.
         *
         * `url` stays declared as the fallback, because `uma:reparse` runs with zero network
         * and a manifest outage must not turn into a source with no address at all. The hash
         * read out of the manifest body is matched against `^[a-f0-9]{8}$` before it reaches a
         * request, so the one value this file does not control can carry neither a path nor a
         * host, and the SSRF floor (CONSTRAINTS.md: no fetch URL outside this allowlist) holds
         * for a resolved URL exactly as it does for a pinned one.
         */
        'gametora-skills' => [
            'url' => 'https://gametora.com/data/umamusume/skills.609afe88.json',
            'manifest' => [
                'url' => 'https://gametora.com/data/manifests/umamusume.json',
                'base' => 'https://gametora.com/data/umamusume/',
                'key' => 'skills',
            ],
            'parser' => GametoraSkillsParser::class,
            'delay_ms' => 1000,
            'timeout_s' => 15,
            'timezone' => 'Asia/Tokyo',
        ],
    ],

];
