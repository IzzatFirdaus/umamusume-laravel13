<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Reference tables for the Database hub
|--------------------------------------------------------------------------
|
| The three areas the Database screen lists that have no table of their own (SCREEN-023,
| `SCR-SYS-008` / `SCR-SYS-009` / `SCR-SYS-010`). They are reference views, not data this
| tool fetched: every row is transcribed from `docs/UMAMUSUME_REFERENCE.md` and carries the
| doc section it came from, so a reader can re-check the row against its source.
|
| **Why config and not a table.** The D17 slice established two read patterns for this hub:
| DB-derived (`RaceCatalogSlot`) and config-derived (`config/scenarios.php`). These three
| views are the second kind. A migration would add a table whose only writer would be this
| file, which `AGENTS.md` §11 does not authorise for a read-only reference screen.
|
| **Why the export is not the read path.** The three exports the guide cites DO exist on this
| machine, under `research-scratch/data/json/` (`factors.json`, `items.json`, `events__*.json`;
| their file dates are 2026-09-27 and 2026-09-28), but that directory is gitignored: nothing
| guarantees a fresh worktree holds them, no runtime code reads them, and `AGENTS.md` §11 keeps a
| fetched body out of the database. So the rows below are transcribed. Where a figure was checked
| against those files, the table's own `recheck` says what the 2026-10-08 read found and what it
| could not confirm; where a figure is the guide's own reading, the row says that instead.
|
| **Provenance.** Each row's `state` is one of the four `ProvenanceBadge` states. The mapping
| applied when authoring these rows:
|   confirmed  the doc backs the value with an official notice URL or an S-file
|   estimated  the doc backs it only with a third-party guide, or marks the contents unread
|   unknown    the doc marks the value unverified
|   calculated no row here is derived by this app; the state is reserved
| A row's `source_title` is the `title` the badge carries, naming what the row rests on.
|
| **Copy discipline.** Row text is the doc's own field, shortened to a clause where the doc
| runs long; nothing is paraphrased into a new claim, and no string here is invented.
|
*/

return [

    'source_doc' => 'docs/UMAMUSUME_REFERENCE.md',

    /*
    | The Events view: the recurring event types from §4.4. Cadence is the doc's own reading
    | of the local event exports; it is not a schedule and does not rot the way a live
    | calendar would, which is why the live/upcoming instances (§4.1 to §4.3) are not here.
    */
    'events' => [
        'source' => [
            'section' => '§4.4 Recurring event types',
            'anchor' => '2026-10-02',
        ],
        'recheck' => [
            'text' => 'Official news index for each server, then diff the date columns of the local event exports. The full procedure is the reference guide §4.4 "How to re-check" line.',
            'url' => 'https://umamusume.jp/news/?t=game',
        ],
        'rows' => [
            [
                'name' => 'Champions Meeting',
                'servers' => ['JP', 'Global'],
                'cadence' => 'JP editions span 6 days and are 31 to 61 days apart in 2026; Global editions span 6 to 7 days and are 20 to 24 days apart.',
                'mechanics' => 'A three-round league: pick a league, then run preliminaries and a final with a team of three trained Umamusume.',
                'state' => 'confirmed',
                'source_title' => 'Official notices for both servers; cadence computed from the local champions-meeting exports.',
            ],
            [
                'name' => 'Story event',
                'servers' => ['JP', 'Global'],
                'cadence' => 'JP editions run 11 to 17 days and start 29 to 62 days apart; the Global export is too short to state a cadence.',
                'mechanics' => 'Career playthroughs bank Event Points for a reward track, a bingo-style card and an event-exclusive support card.',
                'state' => 'confirmed',
                'source_title' => 'Official notices for both servers; cadence from the local story-event exports.',
            ],
            [
                'name' => 'Legend Race',
                'servers' => ['JP', 'Global'],
                'cadence' => 'Monthly, each edition running about six days.',
                'mechanics' => 'Two back-to-back limited stages, each against its own preset rival line-up.',
                'state' => 'confirmed',
                'source_title' => 'Official JP notice; the Global schedule is corroborated by the local export and a lower-tier listing.',
            ],
            [
                'name' => 'Special event (Spark and item research)',
                'servers' => ['JP'],
                'cadence' => 'One observed window of about seven days; the opening time is not restated in the notices.',
                'mechanics' => 'A time-boxed objective chain that pays a progression currency, opened by an advance notice and closed by an end notice.',
                'state' => 'confirmed',
                'source_title' => 'Official JP notices, advance and closing; the cadence is a single observation, not a pattern.',
            ],
            [
                'name' => 'Masters Challenge',
                'servers' => ['JP'],
                'cadence' => 'A long window; the observed edition ran at least five weeks.',
                'mechanics' => 'Replayable limited-time courses paying Carats and Crystal Shards, with expired races due for the Archives.',
                'state' => 'confirmed',
                'source_title' => 'Official JP notice; the difficulty tiers and payouts are from a guide.',
            ],
            [
                'name' => 'Season pass',
                'servers' => ['JP'],
                'cadence' => 'Periodic; the cycle boundaries are not in any data export.',
                'mechanics' => 'A reward track fed by training activity, with a free tier and a once-per-period paid tier.',
                'state' => 'unknown',
                'source_title' => 'The doc marks the structure, tiers and rewards unverified; they are published in-app only, and the web notice states only that the pass was updated.',
            ],
            [
                'name' => 'Real-world race campaign',
                'servers' => ['JP'],
                'cadence' => 'Follows the Japanese G1 calendar; five mission windows plus five mail gifts in phase one alone.',
                'mechanics' => 'Home-screen limited missions and mail gifts tied to the real-world G1 calendar, plus a commemorative Scout and store campaign.',
                'state' => 'confirmed',
                'source_title' => 'Official JP notices for the campaign phases and the partner commemoration.',
            ],
            [
                'name' => 'Anniversary campaign ladder',
                'servers' => ['JP'],
                'cadence' => 'Numbered phases per anniversary; the 5.5th ran its third phase from 2026-09-11.',
                'mechanics' => 'Numbered phases with daily access gifts, a select Scout and pass content.',
                'state' => 'confirmed',
                'source_title' => 'Official JP notice; the phase numbering is the client notice\'s own.',
            ],
            [
                'name' => 'Versus race schedule notice',
                'servers' => ['JP'],
                'cadence' => 'Seasonal; the observed notice covered September to February.',
                'mechanics' => 'A notice listing the upcoming versus-race calendar; the contents were not read.',
                'state' => 'estimated',
                'source_title' => 'The doc confirms the publication type but states the contents were not read.',
            ],
            [
                'name' => 'Story-unlock campaign',
                'servers' => ['JP', 'Global'],
                'cadence' => 'Runs exactly as long as the paired debut Scout window.',
                'mechanics' => 'Episodes 1 to 4 unlocked without the normal conditions; unwatched episodes re-lock.',
                'state' => 'confirmed',
                'source_title' => 'Official notices for both servers, paired with each debut Scout.',
            ],
        ],
    ],

    /*
    | The Shop Items view: consumables and currencies from §1.6.10, plus the two currency
    | items the section describes in prose. The Trackblazer Pro Shop is NOT repeated here; the
    | controller reads it from `config/scenarios.php` and the view renders it as its own block.
    */
    'shop_items' => [
        'source' => [
            'section' => '§1.6.10 Consumables, and where each is actually spent',
            'anchor' => '2026-09-27',
        ],
        'recheck' => [
            'text' => 'Names are the guide\'s. Checked against the local item export on 2026-10-08: four of the ten match a client name there exactly, the other six are the guide\'s grouped labels.',
            'url' => 'https://game8.co/games/Umamusume-Pretty-Derby/archives/538152',
        ],
        'rows' => [
            [
                'name' => 'Alarm Clock',
                'effect' => 'Lets you try again on a Career goal race',
                'spend_site' => 'Career',
                'kind' => 'consumable',
                'state' => 'confirmed',
                'source_title' => 'Client effect string, from the reference guide §1.6.10 item list.',
                'detail' => 'Retries a missed mandatory placing, and a lost Unity Cup team race since the 2026-07-01 rework. Also paid as a league-selection participation reward.',
            ],
            [
                'name' => 'Toughness 30',
                'effect' => 'Restores 30 TP',
                'spend_site' => 'Compensation',
                'kind' => 'consumable',
                'state' => 'confirmed',
                'source_title' => 'Client effect string; what TP governs is sourced to a guide the doc marks stale.',
                'detail' => 'TP is the cost of starting a Career, not the per-turn Energy bar. What the two abbreviations expand to is still not a client string.',
            ],
            [
                'name' => 'Pleasing Parfait',
                'effect' => "Raises a runner's mood to Great",
                'spend_site' => 'Team Trials and Daily Races',
                'kind' => 'consumable',
                'state' => 'confirmed',
                'source_title' => 'Client effect string and spend site, from the reference guide §1.6.10.',
                'detail' => 'One of the four items guides routinely place in Champions Meeting, which publishes no such item path.',
            ],
            [
                'name' => 'Sunshine Doll / Rainfall Doll',
                'effect' => 'Sets the weather to Sunny and the footing to Firm, or to Rain and Heavy',
                'spend_site' => 'Team Trials and Daily Races',
                'kind' => 'consumable',
                'state' => 'confirmed',
                'source_title' => 'Client effect strings and spend site, from the reference guide §1.6.10.',
                'detail' => 'Choosing Rain makes Soft-or-Heavy footing near-mandatory, so the weather and the footing are one decision rather than two.',
            ],
            [
                'name' => 'Inner Post Raffle Ball / Outer Post Raffle Ball',
                'effect' => 'Draws an inner bracket (1 to 3) or an outer gate (6 to 8)',
                'spend_site' => 'Team Trials and Daily Races',
                'kind' => 'consumable',
                'state' => 'confirmed',
                'source_title' => 'Client effect strings and spend site, from the reference guide §1.6.10.',
                'detail' => 'Gate position is separate from the slow-start penalty mechanics.',
            ],
            [
                'name' => 'Books of Hints (four tiers)',
                'effect' => "Raises a skill's hint level",
                'spend_site' => 'Before a run, on the deck screen',
                'kind' => 'consumable',
                'state' => 'confirmed',
                'source_title' => 'Client effect string; the four tiers and their costs are in the reference guide §1.4.4.',
                'detail' => 'Book of Hints, Rare Book of Hints, Textbook of Hints and Rare Textbook of Hints. Costs on an unenhanced card to Lv3 are 12 / 6 / 30 of the three materials.',
            ],
            [
                'name' => 'Rainbow / Gold Uncap Crystal',
                'effect' => 'Uncaps an SSR or SR Support Card',
                'spend_site' => 'Card leveling',
                'kind' => 'consumable',
                'state' => 'confirmed',
                'source_title' => 'Client effect string; Global only, from the reference guide §1.6.10.',
                'detail' => 'The JP client replaced this model on 2025-10-07, so the item path is a Global-only surface.',
            ],
            [
                'name' => 'Rainbow / Gold Crystal Shard',
                'effect' => 'Exchange material for uncap crystals and tickets',
                'spend_site' => 'Racing Carnival shop',
                'kind' => 'consumable',
                'state' => 'confirmed',
                'source_title' => 'Client effect string; the Carnival prices are from a guide, in the reference guide §1.6.5.',
                'detail' => 'The Racing Carnival shop prices Rainbow Crystal Shards at 30,000 Carnival Pts and Gold at 15,000, with Scout and Support tickets at 6,000.',
            ],
            [
                'name' => 'Rainbow / Gold / Silver Cleat',
                'effect' => 'A shop currency from duplicate SSR support cards',
                'spend_site' => 'Cleat Exchange',
                'kind' => 'currency',
                'state' => 'confirmed',
                'source_title' => 'Client name and exchange, from the reference guide §1.6.10.',
                'detail' => 'One duplicate SSR support card converts to 10 Rainbow Cleats. Spent on Scout Tickets, the SR+ Guaranteed Make Debut ticket, Dream Glimmer and Winner\'s Sashes. A currency, not a training buff.',
            ],
            [
                'name' => 'Goddess Statue',
                'effect' => 'A currency exchanged for Trainee Star Pieces',
                'spend_site' => 'Statue Exchange',
                'kind' => 'currency',
                'state' => 'confirmed',
                'source_title' => 'Client name and exchange, from the reference guide §1.6.10.',
                'detail' => 'Exchanged on an escalating rate, 650 Star Pieces to max a trainee. The client states statues cannot unlock an unscouted Trainee, and they grant no buff.',
            ],
        ],
    ],

    /*
    | The Sparks view: the Spark categories from §1.5.2 and the star-roll odds from §1.5.3.
    | The counts were re-read on 2026-10-08 against `research-scratch/data/json/factors.json`,
    | whose buckets are `blue` 5, `pink` 10, `skill` 452, `race` 37, `scenario` 34 and `other`
    | 336, for 874 records. Five of the six figures this view prints are those cells exactly. The
    | sixth, 268, is the guide's own reading of part of `other`: 336 minus 268 is the 68 it calls
    | counted but not described. So the 806 the six rows sum to is the guide's described total,
    | not the export's 874, and the view says which one it is printing.
    */
    'sparks' => [
        'source' => [
            'section' => '§1.5.2',
            'anchor' => '2026-09-27',
        ],
        'recheck' => [
            'text' => 'Counts are the guide\'s. Checked against the local Spark export on 2026-10-08: five of the six match it cell for cell, 806 is the described total and the export holds 874.',
            'url' => 'https://game8.co/games/Umamusume-Pretty-Derby/archives/536822',
        ],
        'categories' => [
            [
                'category' => 'Stat',
                'jp_name' => '青因子',
                'global_name' => 'Blue Sparks',
                'effect' => 'One stat up +5, +12, or +21 at career start for 1, 2, or 3 stars.',
                'records' => 5,
                'state' => 'confirmed',
                'source_title' => 'Category and effect from the reference guide §1.5.2; the record count is the guide\'s own count.',
            ],
            [
                'category' => 'Aptitude',
                'jp_name' => '赤因子',
                'global_name' => 'Pink Sparks',
                'effect' => 'Track, distance or strategy: 1 star is +1 grade, 4 is +2, 7 is +3, 10 to 18 is +4.',
                'records' => 10,
                'state' => 'confirmed',
                'source_title' => 'Category and effect from the reference guide §1.5.2; the record count is the guide\'s own count.',
            ],
            [
                'category' => 'Unique skill',
                'jp_name' => '緑因子 / 固有因子',
                'global_name' => 'Green Sparks',
                'effect' => "Carries one character's own unique skill as 1 to 3 hint levels.",
                'records' => 268,
                'state' => 'confirmed',
                'source_title' => 'Category and effect from the reference guide §1.5.2; the record count is the guide\'s own count.',
            ],
            [
                'category' => 'Skill',
                'jp_name' => '白因子（スキル因子）',
                'global_name' => 'White Sparks, skill',
                'effect' => 'A mid-run hint of level +1 to +5, or a small stat top-up if the skill is already learned.',
                'records' => 452,
                'state' => 'confirmed',
                'source_title' => 'Category and effect from the reference guide §1.5.2; the record count is the guide\'s own count.',
            ],
            [
                'category' => 'Competition',
                'jp_name' => '白因子（レース因子）',
                'global_name' => 'White Sparks, competition',
                'effect' => 'From top-grade (G1) wins; 3, 6, or 9 stat per event, sometimes a hint.',
                'records' => 37,
                'state' => 'confirmed',
                'source_title' => 'Category and effect from the reference guide §1.5.2; the record count is the guide\'s own count.',
            ],
            [
                'category' => 'Scenario',
                'jp_name' => 'シナリオ因子',
                'global_name' => 'Scenario Sparks',
                'effect' => "From clearing a scenario's final conditions; about 10 to 30 per stat, over 200 when stacked.",
                'records' => 34,
                'state' => 'confirmed',
                'source_title' => 'Category and effect from the reference guide §1.5.2; the record count is the guide\'s own count.',
            ],
        ],
        'roll_odds' => [
            [
                'band' => 'Below 600',
                'one_star' => 'about 90%',
                'two_stars' => 'about 10%',
                'three_stars' => '0%',
            ],
            [
                'band' => '600 to 1100',
                'one_star' => 'about 50%',
                'two_stars' => 'about 45%',
                'three_stars' => 'about 6%',
            ],
            [
                'band' => 'Above 1100',
                'one_star' => 'about 20%',
                'two_stars' => 'about 70%',
                'three_stars' => 'about 10%',
            ],
        ],
        'roll_odds_note' => 'The star count is rolled from the run\'s results, never chosen. Crossing 1100 buys a roughly one-in-ten roll at three stars, not a three-star Spark, and below 600 the three-star branch is 0% rather than unlikely.',
    ],

];
