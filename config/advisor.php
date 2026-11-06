<?php

declare(strict_types=1);

/*
 * The declared constant set the Trainer Advisor reads (FR-F-2, `ADR-0001` §2, `ADR-0020` §2).
 *
 * Stored here and never hardcoded in the engine, because `ADR-0001` §2's replacement rule is that
 * the constants a number came from are visible wherever the number appears. Each entry carries its
 * `source` and the date that source was read, so a surface can print the date of the data behind a
 * suggestion without a second lookup (`ADR-0001` §7).
 *
 * Four constants are loaded. `ADR-0001`'s wider set — mood multipliers and the rest — is recorded
 * in that decision but deliberately absent here: those figures only matter to yield prediction,
 * which v1 does not do, and a constant nothing reads is a number waiting to be mistaken for an
 * input (YAGNI).
 *
 * `session_cost` is a RANGE and stays one. It scales with a training level this tool does not
 * track, so a single point would be invented precision; a surface renders the two ends rather than
 * picking one. `advisory_threshold` is qualitative in its source — the only Energy line any source
 * states — which is why the engine has exactly two bands and no third.
 *
 * `verified_at` on `session_cost` is 2023-02-25 and that is not a typo: it is the publication date
 * of the only source that states the range, and `UMAMUSUME_REFERENCE.md` marks it stale at 90 days.
 * Copying a fresher date onto a stale figure would be the one thing this file exists to prevent.
 */

return [
    'version' => 'v1',

    'constants' => [
        'rest_recovery' => [
            // §13.3: this constant used to hold `30` with `confidence => 'current'`, so the advisor
            // printed the FAILURE tier as the recovery figure. `UMAMUSUME_REFERENCE.md:190` states the
            // three tiers: +50 modal, +70 big success, +30 failure (sometimes carrying a stayed-up-late
            // penalty). The modal figure is the one a forecast uses; the other two are named beside it
            // so the copy can say which is which instead of quoting the worst case as the norm.
            'value' => 50,
            'tiers' => [
                'modal' => 50,
                'big_success' => 70,
                'failure' => 30,
            ],
            'source' => 'GameWith 257617 (n=3833), Kamigame 147307607101602682 (n=100)',
            'verified_at' => '2026-11-02',
            'confidence' => 'current',
        ],

        'session_cost' => [
            // [low, high]: the ends of the range, low first, which is the order a surface prints.
            'value' => [17, 28],
            'source' => 'GameWith, 2023-02-25',
            'verified_at' => '2023-02-25',
            'confidence' => 'stale',
        ],

        'wit_cost' => [
            'value' => 0,
            'source' => 'Game8, 2026-09-10',
            'verified_at' => '2026-11-02',
            'confidence' => 'current',
        ],

        'advisory_threshold' => [
            'value' => 50,
            'source' => 'qualitative only',
            'verified_at' => '2026-11-02',
            'confidence' => 'current',
        ],
    ],
];
