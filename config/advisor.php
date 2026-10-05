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
            'value' => 30,
            'source' => 'UMAMUSUME_REFERENCE.md §1.1.5 (GameWith, 2026-09-25)',
            'verified_at' => '2026-09-25',
            'confidence' => 'current',
        ],

        'session_cost' => [
            // [low, high]: the ends of the range, low first, which is the order a surface prints.
            'value' => [17, 28],
            'source' => 'UMAMUSUME_REFERENCE.md §1.1.6 (GameWith, 2023-02-25)',
            'verified_at' => '2023-02-25',
            'confidence' => 'stale',
        ],

        'wit_cost' => [
            'value' => 0,
            'source' => 'UMAMUSUME_REFERENCE.md §1.1.1 (Game8, 2026-09-10)',
            'verified_at' => '2026-09-10',
            'confidence' => 'current',
        ],

        'advisory_threshold' => [
            'value' => 50,
            'source' => 'UMAMUSUME_REFERENCE.md §1.1.5 (qualitative only)',
            'verified_at' => '2026-09-25',
            'confidence' => 'current',
        ],
    ],
];
