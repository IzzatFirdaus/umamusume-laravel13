<?php

declare(strict_types=1);
use App\Services\DataPipeline\Parsers\GametoraCharacterCardParser;
use App\Services\DataPipeline\Parsers\GametoraCharacterParser;
use App\Services\DataPipeline\Parsers\GametoraCharacterProfileParser;
use App\Services\DataPipeline\Parsers\GametoraRaceCatalogParser;
use App\Services\DataPipeline\Parsers\GametoraSkillsParser;
use App\Services\DataPipeline\Parsers\GametoraSupportCardParser;
use App\Services\DataPipeline\Parsers\GametoraSupportEffectParser;

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
     *       'seed_file' => 'example.json', // committed body under database/seeders/data/
     *   ],
     *
     * `seed_file` names the committed copy of this source's body, relative to
     * database/seeders/data/, so `migrate --seed` can rebuild the catalogue offline.
     * Fetched snapshots live in storage/app/private/snapshots and are gitignored by
     * design, so without this key a fresh clone has no body to seed from and the only
     * way to populate the reference tables is the network. Two sources may share one
     * file when the publisher ships one document at two grains.
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
            'url' => 'https://gametora.com/data/umamusume/character-cards.e9e9ee6d.json',
            'parser' => GametoraCharacterParser::class,
            'delay_ms' => 1000,
            'timeout_s' => 15,
            'timezone' => 'Asia/Tokyo',
            'seed_file' => 'gametora-characters.e9e9ee6d.json',
        ],

        /*
         * The same document as gametora-characters, at card grain (FR-A-6, ADR-0008).
         *
         * Declared as its own source because the engine's contract is one parser per
         * source, and a trainee's rows and her cards have different identities: one
         * cross-references on name through the review queue, the other keys on the
         * source's own card id and never needs a guess. Keeping them apart is what
         * lets each stay independently lockable and idempotent under FR-B-5.
         *
         * Cost, stated: the same ~250 kB is fetched twice per full `uma:fetch`, one
         * second apart, against the host the owner approved on 2026-09-27. The robots
         * and rate-limit question AGENTS.md escalation 5 raises for this host stays
         * formally unanswered, exactly as the note above records it.
         *
         * Order matters: the character source is listed first, so its trainees exist
         * by the time the card source resolves char refs through external_ref.
         *
         * Hash: re-read from the manifest above on 2026-09-29, and `character-cards`
         * had moved from `679f7c2e` to `e9e9ee6d`. This entry and
         * `gametora-characters` above carry the same one, so the two grains of one
         * document cannot be read from two different revisions.
         *
         * KI-24, measured 2026-09-29: the withdrawn `679f7c2e` document still answers
         * 200 with its old content (251,242 bytes, 105 card records against 107 here),
         * so a stale hash serves silent stale data, not the loud failure the other two
         * notes in this file claim.
         */
        'gametora-character-cards' => [
            'url' => 'https://gametora.com/data/umamusume/character-cards.e9e9ee6d.json',
            'parser' => GametoraCharacterCardParser::class,
            'delay_ms' => 1000,
            'timeout_s' => 15,
            'timezone' => 'Asia/Tokyo',
            // Same body as `gametora-characters` above, read at the other grain — hence the
            // same committed file rather than a second copy of it.
            'seed_file' => 'gametora-characters.e9e9ee6d.json',
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
            'seed_file' => 'race_instances.json',
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
            'seed_file' => 'skills.609afe88.json',
        ],
        /*
         * The trainee profile block: Japanese name, voice actor, birthday, height and three
         * sizes — the "basic information" a character page shows.
         *
         * A **fourth document, not another grain of the card one.** Measured 2026-09-30 against
         * the body: 163 rows keyed by `char_id`, carrying `jp_name`, `va_ja` / `va_en`,
         * `birth_year` / `birth_month` / `birth_day`, `height` and a `three_sizes` object. It
         * holds no aptitude, no rarity, no release date and no stat array, so it overlaps the
         * card document on nothing but the trainee id. `ADR-0012` names it as unaddressed by any
         * of its three decisions, and the owner confirmed on 2026-09-30 that "basic information"
         * means this block rather than the card document's stat arrays.
         *
         * Owner approval, stated rather than assumed: the same review that approved
         * `gametora-characters` on 2026-09-27, re-used the way `gametora-skills` re-used it. Same
         * host, same publisher, one static JSON document, no HTML, no JS, no crawler directive to
         * observe, no crawl budget consumed, and the politeness bounds already set by `delay_ms`,
         * the per-source lock and the cache TTL.
         *
         * **Resolves through the manifest, so it issues two requests per fetch** — the same
         * response to KI-24 that `gametora-skills` records. KI-24 measured that a pinned
         * cache-busting hash does not fail when it goes stale: the withdrawn `679f7c2e` document
         * still answered 200 with its old content, so a pinned URL serves silent stale data.
         * `url` stays declared as the documented fallback for the same reason.
         *
         * Robots and rate limit: the question AGENTS.md escalation 5 raises for this host is
         * still formally unanswered, exactly as the `gametora-characters` note records. What is
         * bounded is the load: two requests per full `uma:fetch`, one second apart, against a
         * static JSON document, with no crawl of the site itself. Re-measured 2026-09-30: the
         * manifest answers 200 to this tool's own user agent
         * (`UmamusumeTrainerCompanion/0.2`), so no browser-UA substitution was needed or used.
         *
         * `characters` is a near-miss of names in that same manifest, and the keys are not
         * interchangeable: `characters_extended` (934 rows) carries only `char_id`, `name_en` and
         * `name_ja`; `character_profiles` and `char_profiles` (173 rows each) carry only
         * `char_id` and the four localised long-form texts. None of them is this block, and none
         * is declared here. `meta/char_profile_art` (170 rows) is an art-existence index of
         * per-character booleans with no asset path and no card grain; it is named in the report
         * rather than declared, because `ADR-0012` Decision 2 defers images and a discovered art
         * source is a re-decision rather than a detail.
         *
         * Order: after the character and card sources, because every row resolves its trainee
         * through `umamusume.external_ref` and needs her to exist first.
         */
        'gametora-character-profiles' => [
            'url' => 'https://gametora.com/data/umamusume/characters.c6676539.json',
            'manifest' => [
                'url' => 'https://gametora.com/data/manifests/umamusume.json',
                'base' => 'https://gametora.com/data/umamusume/',
                'key' => 'characters',
            ],
            'parser' => GametoraCharacterProfileParser::class,
            'delay_ms' => 1000,
            'timeout_s' => 15,
            'timezone' => 'Asia/Tokyo',
            'seed_file' => 'characters.c6676539.json',
        ],

        /*
         * The support-card catalogue and its effect dictionary (ADR-0014, owner ruling 2026-09-30,
         * which lifted `PRD.md` §6.9 for reference data and run linkage while leaving collection
         * tracking out of scope).
         *
         * Two entries because the publisher ships two documents and the engine's contract is one parser
         * per source: the card rows are 559 records keyed on `support_id`, the dictionary 35 records
         * keyed on `id`. They are declared adjacently, dictionary second, because `support_cards.effects`
         * holds the ids the dictionary names and a Trainer reading a half-imported deck panel is better
         * served by a missing card than by an anchor that resolves to no label. There is no foreign key
         * between the two tables, so neither order is required by the schema; this one is required by
         * nothing but legibility, and it is stated so a later reader does not read a dependency into it.
         *
         * Owner approval, stated rather than assumed: the same review that approved
         * `gametora-characters` on 2026-09-27, re-used the way `gametora-skills` and
         * `gametora-character-profiles` re-used it. Same host, same publisher, two static JSON
         * documents, no HTML, no JS, no crawler directive to observe, no crawl budget consumed, and the
         * politeness bounds already set by `delay_ms`, the per-source lock and the cache TTL. The
         * robots.txt and rate-limit question AGENTS.md escalation 5 raises for this host is still
         * formally unanswered, exactly as those entries record it.
         *
         * Both resolve through the manifest, so each issues two requests per fetch, and both keep a
         * pinned `url` as the documented fallback. That is the KI-24 reading, not a repeat of the
         * claim this file made before KI-24 measured it: a stale cache-busting hash answers `200` with
         * the superseded document, so a pin alone serves silent stale data.
         *
         * The hashes below were read out of `https://gametora.com/data/manifests/umamusume.json` as
         * captured in `research-scratch/data/json/manifest.live.json` (keys `support-cards` and
         * `support_effects`), and each committed body is byte-identical to the revision its pin names:
         * the first eight hex digits of the file's own sha256 are `88dea522` and `ca447e53`, the same
         * convention `skills.609afe88.json` and `gametora-characters.e9e9ee6d.json` follow. Note the
         * manifest's own key spelling differs between the two (`support-cards` hyphenated,
         * `support_effects` underscored); `SourceFetcher::fromManifest()` builds the file name from the
         * key, so the difference is load-bearing and neither entry may tidy it.
         *
         * Timezone is Asia/Tokyo for provenance only, as with every other entry here. This document
         * publishes `release` and `release_en` as `Y-m-d` strings rather than the epochs the scenario
         * document carries, so the parser passes the dates through and no zone is applied to them.
         */
        'gametora-support-cards' => [
            'url' => 'https://gametora.com/data/umamusume/support-cards.88dea522.json',
            'manifest' => [
                'url' => 'https://gametora.com/data/manifests/umamusume.json',
                'base' => 'https://gametora.com/data/umamusume/',
                'key' => 'support-cards',
            ],
            'parser' => GametoraSupportCardParser::class,
            'delay_ms' => 1000,
            'timeout_s' => 15,
            'timezone' => 'Asia/Tokyo',
            'seed_file' => 'support-cards.88dea522.json',
        ],

        /*
         * The support-effect dictionary: 35 rows naming the effect ids that appear at position 0 of
         * every anchor vector on `support_cards.effects`.
         *
         * Approval, manifest resolution, politeness and timezone are as stated on
         * `gametora-support-cards` immediately above; that comment is the one that reasons about this
         * host, and repeating it per entry is how a note like KI-24 ends up corrected in three files and
         * wrong in one.
         */
        'gametora-support-effects' => [
            'url' => 'https://gametora.com/data/umamusume/support_effects.ca447e53.json',
            'manifest' => [
                'url' => 'https://gametora.com/data/manifests/umamusume.json',
                'base' => 'https://gametora.com/data/umamusume/',
                'key' => 'support_effects',
            ],
            'parser' => GametoraSupportEffectParser::class,
            'delay_ms' => 1000,
            'timeout_s' => 15,
            'timezone' => 'Asia/Tokyo',
            'seed_file' => 'support_effects.ca447e53.json',
        ],
    ],

];
