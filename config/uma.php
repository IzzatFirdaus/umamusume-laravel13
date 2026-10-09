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
         * request per fetch plus the per-source lock, `delay_ms` and the retry ceiling bound the load.
         * (`cache.ttl` is a read cache in front of the catalog pages and never touches this path; C-3
         * struck it from the five notes here that credited it with bounding fetch load.)
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
         *     lock, delay_ms and the retry ceiling already bound the load.
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
            /*
             * KI-67. The pin above matched the manifest when this entry was reviewed and was
             * withdrawn at the publisher's next republish — the exact stale-pin failure the
             * `ADR-0011` scope note predicted: `race_instances.294424fc.json` now answers 404,
             * so a pinned-only entry cannot refresh the career calendar at all. The resolution
             * route is the one already approved for `gametora-skills` above, re-used rather than
             * re-argued: the hash comes from the publisher's manifest per run, the pin stays as
             * the documented fallback (`uma:reparse` runs with zero network and a manifest
             * outage must not leave the source addressless), and the hash is shape-checked
             * before it reaches a URL.
             *
             * Like skills, this source now issues two requests per fetch, inside the same
             * politeness bounds (`delay_ms` is waited per request, not per fetch).
             */
            'manifest' => [
                'url' => 'https://gametora.com/data/manifests/umamusume.json',
                'base' => 'https://gametora.com/data/umamusume/',
                'key' => 'race_instances',
            ],
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
         * set by `delay_ms`, the per-source lock and the retry ceiling.
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
         * the per-source lock and the retry ceiling.
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
         * politeness bounds already set by `delay_ms`, the per-source lock and the retry ceiling. The
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

        /*
         * An ASSET host, not a document source: no `parser`, no `seed_file`, no manifest. That
         * missing `parser` is the whole discriminator, the same one SourceDocumentSeeder:56
         * already uses, so `uma:fetch` steps over this entry and `uma:fetch-art` is the only
         * reader of it (`ADR-0021`, accepted 2026-10-05, which authorizes the layer).
         *
         * It lives in `uma.sources` rather than in a key of its own because ARCHITECTURE §8's
         * rule is that every host this tool requests is one this array names, and
         * SourceFetcher::allowedHosts() derives that set from these entries. An asset host
         * declared anywhere else would either break the rule or break the redirect check.
         *
         * `url` is a directory prefix, not a document. A requested path is this base plus one
         * of the `paths` shapes with `{id}` replaced by an integer read from a catalog column
         * (`character_cards.card_id`, `support_cards.support_id`) and never by a request value.
         * Each shape was resolved by hand on 2026-10-05 and is recorded, with the sizes it
         * answers at, in `ADR-0021`'s Context table.
         *
         * Politeness, stated because there is no policy to read: `media.gametora.com` serves no
         * robots.txt at all (it answers 404), and `gametora.com/robots.txt` disallows only
         * /404, /500, /patron-zone, /cdn-cgi/, /loc/ and one event landing page, which covers
         * none of these paths. With no publisher rule to obey the bound is this tool's own: one
         * request per file with `delay_ms` in front of each, `uma.fetch.retry_times` at a flat
         * 500 ms, files already on disk skipped unless `--refetch`, and never a scheduled or
         * repeated run. A miss answers 404 with 27,150 bytes of HTML, so `SourceFetcher`'s
         * status check is what decides whether bytes are usable; nothing here treats a
         * non-empty body as success.
         */
        'gametora-artwork' => [
            'url' => 'https://media.gametora.com/umamusume/',
            'delay_ms' => 1000,
            'timeout_s' => 20,
            'paths' => [
                'card_portrait' => 'characters/portrait/trainee/256/{id}.png',
                'support_thumb' => 'supports/full/small/{id}.png',
            ],
        ],
    ],

    /*
     * Skill-planner constants (SCREEN plan D13). Versioned constants live in config, never
     * hardcoded (ADR-0001 §2's rule for the advisor's arithmetic, applied to the same class of
     * number here), and each carries its source beside it.
     */
    'skills' => [

        /*
         * The hint discount ladder, read off the [Global] Learn screen on 2026-10-03: the client
         * prints `Hint Lvl N` above `NN% OFF`. `UMAMUSUME_REFERENCE.md` §1.1.4 carries the read,
         * `docs/research-scratch/SKILLS-MECHANICS.md` §2.4 the caption table, and
         * `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` §2.1 the frame evidence. `Lv Max` is a
         * caption, not a level number. Displayed cost is base x (1 - discount) floored to the
         * integer, which the sampled bases reconcile (130 -> 104/91/84, 160 -> 144/128/112/96,
         * 180 -> 162, 200 -> 180/140/130).
         */
        'hint_discount' => [
            ['level' => 'Hint Lvl 1', 'percent' => 10],
            ['level' => 'Hint Lvl 2', 'percent' => 20],
            ['level' => 'Hint Lvl 3', 'percent' => 30],
            ['level' => 'Hint Lvl 4', 'percent' => 35],
            ['level' => 'Hint Lvl Max', 'percent' => 40],
        ],

        /*
         * The number maps the race-fit comparison is allowed to read. `distance_type` is decoded
         * 1..4 against the client's own band tags and `ground_type` 1 and 2 are pinned by the
         * client's own skill copy, both in `UMAMUSUME_REFERENCE.md` §1.2. The `running_style`
         * numbers have no sourced label map in this repository (PRD OQ-5), so no style comparison
         * is made anywhere below them.
         */
        'fit_distance_type' => [1 => 'Sprint', 2 => 'Mile', 3 => 'Medium', 4 => 'Long'],
        'fit_surface_type' => [1 => 'Turf', 2 => 'Dirt'],

        /*
         * The one prerequisite pair a source publishes: the gold "Burning Spirit" skills name an
         * "Ignited Spirit" skill as their prerequisite (run report §8.1, game8.co 2026-09-23 and
         * game8.jp 2026-10-02). Whether learning the gold suppresses or replaces the white in a
         * race is not established on either side, so the planner states the prerequisite and
         * leaves that question Unknown.
         */
        'gold_prerequisites' => ['Burning Spirit' => 'Ignited Spirit'],
    ],

    /*
     * Veteran-library constants (`SCREEN-020`, plan §8's D16).
     */
    'veteran' => [

        /*
         * The tag vocabulary the Save Veteran screen offers, group by group. `screen-spec-2.0` §24 lists
         * these eighteen as *suggestions* the Trainer toggles, and custom tags are allowed beside them, so
         * this is the picker's content, not an allow-list: `StoreVeteranRequest` bounds a tag's length and
         * shape and never its membership.
         *
         * It is grouped rather than flat because the page renders each group under its own label, and a
         * page that hardcoded the grouping would hold a second copy of the brief's vocabulary — the same
         * reason `config/scenarios.php` is the only place a scenario name enters the layout. Before this,
         * the pieces lived split across `lang/en/uma.php`'s `terms` and the two `skills.fit_*` maps above,
         * with no key holding the list at all.
         *
         * `ListVeterans` searches these through the `tags` json column, so a new entry here is searchable
         * the moment it is offered, with no query change.
         *
         * @var array<string, list<string>>
         */
        'suggested_tags' => [
            'Stat' => ['Speed', 'Stamina', 'Power', 'Guts', 'Wit'],
            'Distance' => ['Sprint', 'Mile', 'Medium', 'Long'],
            'Surface' => ['Turf', 'Dirt'],
            'Running style' => ['Front Runner', 'Pace Chaser', 'Late Surger', 'End Closer'],
            'Record kind' => ['Skill', 'Race', 'Scenario'],
        ],
    ],

    /*
     * The fan ladder, as the bands a run can hold: a floor, the threshold that ends the band, and the
     * class the client shows inside it.
     *
     * `docs/audits/rice-shower-unity-cup-ux-walk.md:392-393` records one band and only one: a run at
     * 209,245 fans holding the class `Star`, with the **Top Star** threshold at 240,000 and a
     * 30,755-fan gap to it. That is the band below 240,000, and it is the only one any source this
     * repository reads names - no tier below Star and no tier above Top Star is stated anywhere, so the
     * ladder has one entry and `FanLadder` answers null past its top rather than inventing a tier.
     *
     * @var list<array{class: string, from: int, to: int}>
     */
    'fan_ladder' => [
        ['class' => 'Star', 'from' => 0, 'to' => 240000],
    ],

];
