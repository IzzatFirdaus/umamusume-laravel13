<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Scenario composition matrix
|--------------------------------------------------------------------------
|
| Single source of truth for what the UI renders per training scenario.
| Components read this; none of them branch on a scenario name. Adding a
| fifth scenario means adding one entry here and no component edits, which
| is the requirement behind CONSTRAINTS.md D-240 and gate G-33.
|
| Cited requirements: PRD.md FR-C / US-3 / US-4 (training runs, turns,
| skills) and US-10 (race calendar and fan gating, promoted to P1).
|
| Provenance for every number below:
|   caps        GameTora `scenarios.json`, field `stats`, a per-stat bonus
|               over a 1200 base. Verified 2026-09-27 against three Global
|               prose sources. `hard_caps` is a separate engine ceiling.
|   panels      docs/scenarios/01, 02, 03 and 07, reconciled in
|               docs/research-scratch/DESIGN-CORPUS.md (the SCENARIO-DIFFERENCES source)
|   matrix      docs/research-scratch/DESIGN-CORPUS.md, CONSTRAINTS section, §10n
|
| Do not collapse cap_bonus into a flat cap. The bar renders base plus bonus
| as separate labelled terms (DESIGN.md §6.22), and one JP scenario carries a
| negative bonus, so the value is provably a delta rather than a ceiling.
*/

return [

    /*
    | The date this matrix was last checked against its sources. The header above
    | states the same date and the three Global prose sources the caps were read
    | against; this key exists so the Dashboard's data-status badge (SCREEN-001,
    | design-2.0 §48) can print it instead of the controller carrying a literal.
    | It is a verification date, not an "updated" one: nothing here was fetched
    | on it.
    */
    'verified_at' => '2026-09-27',

    // Display order and labels. Global client strings only.
    'stat_order' => ['Speed', 'Stamina', 'Power', 'Guts', 'Wit'],

    /*
    | What each `widgets[]` entry is called on screen. Beside the widget lists
    | rather than inside them so the composition matrix stays a list of keys, and
    | here rather than in a view so a fifth scenario's widget is named once
    | (D-240, gate G-33). The Dashboard reads this to label the scenario's primary
    | resource; the run page's strip owns its own presentation labels.
    */
    'widget_labels' => [
        'turn' => 'Turn',
        'energy' => 'Energy',
        'fans' => 'Fans',
        'team_rank' => 'Team Rank',
        'spirit_bursts' => 'Spirit Bursts',
        'grade_points' => 'Grade Points',
        'shop_coins' => 'Shop Coins',
    ],

    /*
    | What each `panels` key is called on screen, for the Scenario Selection cards
    | (SCREEN-002). A panel that is off is simply not printed, so a scenario never
    | claims a system it does not compose, and a fifth scenario names its own panels
    | here rather than in a component (D-240, gate G-33).
    */
    'panel_labels' => [
        'race_calendar' => 'Race calendar',
        'team_race' => 'Team races',
        'grade_objectives' => 'Grade Point objectives',
        'shop' => 'Pro Shop',
        'epithet_routes' => 'Epithet routes',
        'team_rank_ladder' => 'Team Rank ladder',
    ],

    // The 1200 line is a game mechanic where training gains halve, not an
    // application limit. It is drawn as its own marker on every bar.
    'base_cap' => 1200,

    /*
    | The strip a run renders when it names no scenario. URA Finale is that
    | baseline because it is the only Global scenario with every panel off by
    | definition, so "no scenario" cannot be mistaken for "a scenario with fewer
    | features" (owner ruling 2026-09-27). Named here rather than in a view so the
    | baseline is a fact about the matrix, not a literal repeated in a template.
    | Our Grand Concert renders the same strip, from its own entry, on purpose.
    */
    'baseline' => 'ura_finale',

    // scenarios.json `hard_caps`, all four Global scenarios. This is the
    // validation ceiling adopted by ADR-0002 option B.
    'hard_cap' => 2000,

    /*
    | Local grade banding. NOT the client's scale: no source in this repository
    | defines a stat grade, so the boundaries are printed next to the badge and
    | labelled provisional (CONSTRAINTS.md D-256, gate G-46). Sourcing them is a
    | blocking item recorded in DESIGN.md §11.3.
    */
    'grade_banding' => [
        'step' => 50,
        'labels' => ['G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'S+', 'SS'],
        'provisional' => true,
    ],

    'scenarios' => [

        'ura_finale' => [
            'label' => 'URA Finale',
            'live_on_global' => '2025-06-26',
            'cap_bonus' => ['Speed' => 200, 'Stamina' => 200, 'Power' => 200, 'Guts' => 200, 'Wit' => 200],
            'widgets' => ['turn', 'energy', 'fans'],
            'steps' => ['training', 'outcome', 'skill'],
            'panels' => [
                'race_calendar' => true,
                'team_race' => false,
                'grade_objectives' => false,
                'shop' => false,
                'epithet_routes' => false,
                'team_rank_ladder' => false,
            ],
            'scenario_links' => ['Aoi Kiryuin'],
            'facility_level_source' => 'repetition',
            'notes' => 'Defined by absence: no team system, no shop, no scenario currency. '
                .'This is the minimal strip, and adding another scenario widget here would state something false.',
        ],

        'unity_cup' => [
            'label' => 'Unity Cup',
            'live_on_global' => '2025-11-06',
            'cap_bonus' => ['Speed' => 100, 'Stamina' => 100, 'Power' => 100, 'Guts' => 100, 'Wit' => 600],
            'widgets' => ['turn', 'energy', 'fans', 'team_rank', 'spirit_bursts'],
            'steps' => ['facility', 'training', 'team_race', 'outcome', 'skill'],
            'panels' => [
                'race_calendar' => true,
                'team_race' => true,
                'grade_objectives' => false,
                'shop' => false,
                'epithet_routes' => false,
                'team_rank_ladder' => true,
            ],
            'scenario_links' => [
                'Taiki Shuttle', 'Rice Shower', 'Haru Urara', 'Matikanefukukitaru', 'Riko Kashimoto',
            ],
            'facility_level_source' => 'team_rank',
            // Post 2026-07-01: the extra energy cost on team training was removed.
            'team_training_energy_penalty' => false,
            'wit_burst_energy_bonus' => 5,
            /*
            | Team Race opponent selection (docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md
            | and 02-unity-cup.md). Three NPC teams, strongest to weakest, and
            | beating a stronger one moves league rank further.
            |
            | `circles_per_category` is deliberately absent. The circle estimate is
            | the client's own display before you commit; the tool records what the
            | Trainer saw and never computes it (Planner Rule 1, D-225). A number
            | here would be the tool doing the client's arithmetic.
            */
            'team_race' => [
                'occurs_every_months' => 6,
                'opponent_count' => 3,
                'opponents' => [
                    // Keyed `tier`, not by the attribute the JP wikis rank characters
                    // by: the lore gate bans that word, and "which of three teams is
                    // hardest to beat" is not that attribute.
                    ['name' => 'Elite Team Ares', 'tier' => 'strongest'],
                    ['name' => 'The Turf Queens', 'tier' => 'middle'],
                    ['name' => 'Novice Squad', 'tier' => 'weakest'],
                ],
                // publisher master, section "Unity Cup Team Races" — aim for at least 3 circles as a safety margin, because
                // losing decreases league rank. A margin, not a win condition.
                'circles_guidance' => 3,
                'loss_lowers_league_rank' => true,
                // publisher master, section "July 1, 2026 Update" — the 2026-07-01 rework added a retry with an Alarm Clock
                // item; older guidance treats a loss as permanent and is wrong.
                'loss_retryable_with_alarm_clock' => true,
            ],
            'spirit_burst_states' => ['chargeable', 'charged', 'held', 'spent', 'extreme_ready', 'extreme_spent'],
            'team_rank_ladder' => [
                ['ranks' => ['G', 'F'], 'level' => 1],
                ['ranks' => ['D', 'E'], 'level' => 2],
                ['ranks' => ['B', 'C'], 'level' => 3],
                ['ranks' => ['A'], 'level' => 4],
                ['ranks' => ['S'], 'level' => 5],
            ],
            'notes' => 'Facility level is a team property, so a level chip must name its cause (D-222). '
                .'Rank S+ sits above S and grants a second hint rather than a higher facility.',
        ],

        'trackblazer' => [
            'label' => 'Trackblazer',
            'live_on_global' => '2026-03-12',
            'cap_bonus' => ['Speed' => 0, 'Stamina' => 700, 'Power' => 0, 'Guts' => 0, 'Wit' => 300],
            'widgets' => ['turn', 'energy', 'fans', 'grade_points', 'shop_coins'],
            'steps' => ['training', 'shop', 'outcome', 'skill'],
            'panels' => [
                'race_calendar' => false,
                'team_race' => false,
                'grade_objectives' => true,
                'shop' => true,
                'epithet_routes' => true,
                'team_rank_ladder' => false,
            ],
            'scenario_links' => [],
            'facility_level_source' => 'repetition_plus_purchases',
            /*
            | Grade Point objectives are aptitude-conditional. Rendering the
            | standard track for a dirt-leaning trainee shows the wrong target,
            | which is the gap raised in the mechanics audit.
            */
            'grade_objectives' => [
                'standard' => ['Junior' => 60, 'Classic' => 300, 'Senior' => 300],
                'dirt_leaning' => ['Junior' => 30, 'Classic' => 200, 'Senior' => 300],
                'limited_turf_range' => ['Junior' => 60, 'Classic' => 200, 'Senior' => 300],
            ],
            'grade_points_surplus_carries_over' => false,
            /*
            | Display names for the four Grade Point objectives. Kept beside the
            | numbers above rather than inside them so the figures stay exactly as
            | D-232 states them (debut, then 60, +300, +300) and can be diffed
            | against the source without a translation layer in the way.
            |
            | "End of <year>" is our wording for the client's end-of-year assessment;
            | the client names the year, not a deadline, so the phrase says when the
            | figure is judged rather than inventing a game term.
            */
            'grade_objective_labels' => [
                'debut' => 'Debut race',
                'Junior' => 'End of Junior Year',
                'Classic' => 'End of Classic Year',
                'Senior' => 'End of Senior Year',
            ],
            'grade_point_by_grade' => ['G1' => 100, 'G2' => 80, 'G3' => 60, 'OP' => 40, 'Pre-OP' => 20],
            'shop_coins_by_placement' => ['1st' => 100, '2nd' => 60, '3rd' => 60, '4th' => 30, '5th' => 30, '6th' => 0],
            'shop' => ['rotation_turns' => 6, 'max_copies_per_item' => 5, 'locked_until_debut' => true],
            /*
            | The static catalogue behind the shop step.
            |
            | Every cost and effect below is transcribed from
            | docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md section "Full Shop Item List"
            | (GameTora, 2026). Nothing here is invented and nothing is a
            | placeholder: a row is either a real client item at its real price or
            | it is absent from the list.
            |
            | `sale` and `limited` are per-offer flags, not per-item ones. The
            | rotation is not modelled by this build, so no row carries a flag and
            | the client will not show one either — the flags are supported by the
            | component and exercised by a test, but until a rotation exists there
            | is nothing honest to flag. Inventing "Berry Sweet Cupcake is on sale"
            | would state a state of a lineup this tool cannot see.
            |
            | This is a subset of the published list, chosen to span the categories
            | (stats, energy, mood, training effects, races, facility). It is not
            | the whole catalogue, and the step says so where it renders.
            |
            | One item name trips the `lore-code` gate: "Good-Luck Charm" contains
            | "Luck", which is banned because the JP wikis use it for a stat the
            | client does not have. Here it is a verbatim client item name kept as
            | source data, which C-4 puts outside the copy ban — the same carve-out
            | skill and race names get. It is kept spelled as the client spells it
            | rather than renamed to satisfy a grep.
            */
            'shop_items' => [
                ['name' => 'Speed Notepad', 'cost' => 10, 'effect' => '+3 Speed'],
                ['name' => 'Speed Manual', 'cost' => 15, 'effect' => '+7 Speed'],
                ['name' => 'Speed Scroll', 'cost' => 30, 'effect' => '+15 Speed'],
                ['name' => 'Vita 20', 'cost' => 35, 'effect' => 'Energy +20'],
                ['name' => 'Vita 40', 'cost' => 55, 'effect' => 'Energy +40'],
                ['name' => 'Royal Kale Juice', 'cost' => 70, 'effect' => 'Energy +100, Mood −1'],
                ['name' => 'Energy Drink MAX EX', 'cost' => 50, 'effect' => 'Max Energy +8'],
                ['name' => 'Plain Cupcake', 'cost' => 30, 'effect' => 'Mood +1'],
                ['name' => 'Berry Sweet Cupcake', 'cost' => 55, 'effect' => 'Mood +2'],
                ['name' => 'Coaching Megaphone', 'cost' => 40, 'effect' => 'Training bonus +20% for 4 turns'],
                ['name' => 'Motivating Megaphone', 'cost' => 55, 'effect' => 'Training bonus +40% for 3 turns'],
                ['name' => 'Empowering Megaphone', 'cost' => 70, 'effect' => 'Training bonus +60% for 2 turns'],
                // publisher master, section "Full Shop Item List" — there is no Wit version. The tool must not offer one.
                ['name' => 'Ankle Weights', 'cost' => 50, 'effect' => '+50% training bonus for that stat, +20% Energy cost, 1 turn'],
                ['name' => 'Good-Luck Charm', 'cost' => 40, 'effect' => 'Training failure rate 0% for 1 turn'],
                ['name' => 'Artisan Cleat Hammer', 'cost' => 25, 'effect' => 'Race bonus +20%, 1 turn'],
                ['name' => 'Master Cleat Hammer', 'cost' => 40, 'effect' => 'Race bonus +35%, 1 turn'],
                ['name' => 'Glow Sticks', 'cost' => 15, 'effect' => 'Race fan gain +50%, 1 turn'],
                ['name' => 'Speed Training Application', 'cost' => 150, 'effect' => 'Speed facility level +1 for the run'],
                ['name' => 'Miracle Cure', 'cost' => 40, 'effect' => 'Heals all negative conditions'],
            ],
            'race_fatigue' => ['hide_after' => 'late_december', 'final_races_pay_coins' => false],
            /*
            | Epithet routes, transcribed from
            | docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md section "Epithets (Race Route Bonuses)" (
            | uma.guide, dated after the 2026-07-01 rework). Each row states which
            | condition the tool can actually evaluate:
            |
            |  - `races` are named races. A row is complete when every one appears among
            |    the run's entered completed races, by the slot title the Trainer entered
            |    the race against. `mode` is 'all' (default) or 'any'.
            |  - `epithets` are prerequisite epithets from this same table, resolved in
            |    list order.
            |  - `aggregate` marks a condition this build cannot evaluate from entered
            |    titles at all ("win 5 Dirt races", "all unique Mile Turf G1s", "both QEII
            |    Cups"). The surface has no dirt flag and no race-tag list to read, so the
            |    checklist renders the sentence and the word `unverifiable` rather than
            |    implying the route is unmet (D-220, D-256).
            |
            | Nothing here is invented: a row is either a published requirement in the
            | guide's own words or it is absent.
            */
            'epithet_routes' => [
                ['route' => 'Tiara Route', 'epithet' => 'Lady', 'races' => ['Oka Sho', 'Japanese Oaks', 'Shuka Sho'], 'reward' => '+10 to 2 random stats'],
                ['route' => 'Tiara Route', 'epithet' => 'Heroine', 'epithets' => ['Lady'], 'races' => ['Queen Elizabeth II Cup (Classic)'], 'reward' => '+10 to 2 random stats'],
                ['route' => 'Tiara Route', 'epithet' => 'Goddess', 'epithets' => ['Lady'], 'races' => ['Victoria Mile', 'Hanshin Juvenile Fillies'], 'aggregate' => 'both QEII Cups', 'reward' => '+15 to 2 random stats'],
                /*
                 * The source states the reward as a hint toward `Mile Straightaways` and names no tier
                 * (`docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Epithets (Race Route Bonuses)"), while `[Global]` holds two client
                 * rows of that name, `Mile Straightaways ◎` (201031) and `Mile Straightaways ○` (201032).
                 * Naming one of them would be guessing at a provenance, which is how `Traightaways` was
                 * handled at D-210 rather than swapped for a plausible neighbour. So the row states the
                 * family it hints and says the tier is not published, and
                 * `tests/Feature/EpithetRewardNamesTest.php` refuses any reward that names a skill the
                 * catalogue cannot point at.
                 */
                ['route' => 'Tiara Route', 'epithet' => 'Mile a Minute', 'aggregate' => 'win all unique Mile Turf G1s', 'reward' => 'Mile Straightaways hint +1 (tier not stated by the source)'],
                ['route' => 'Classic Route', 'epithet' => 'Stunning', 'races' => ['Satsuki Sho', 'Japanese Derby', 'Kikuka Sho'], 'reward' => '+10 to 2 random stats'],
                ['route' => 'Classic Route', 'epithet' => 'Incredible', 'epithets' => ['Stunning'], 'races' => ['Japan Cup (Classic)', 'Arima Kinen (Classic)'], 'mode' => 'any', 'reward' => '+15 to 2 random stats'],
                ['route' => 'Classic Route', 'epithet' => 'Phenomenal', 'epithets' => ['Stunning'], 'aggregate' => 'win 2 of Tenno Sho Spring, Takarazuka Kinen, Japan Cup, Tenno Sho Autumn, Osaka Hai, Arima Kinen', 'reward' => '+15 to 2 random stats'],
                ['route' => 'Sprint/Mile Route', 'epithet' => 'Breakneck Miler', 'races' => ['NHK Mile Cup', 'Yasuda Kinen', 'Mile Championship'], 'reward' => '+15 to 2 random stats'],
                ['route' => 'Sprint/Mile Route', 'epithet' => 'Sprint Go-Getter', 'races' => ['Takamatsunomiya Kinen', 'Sprinters Stakes'], 'reward' => '+10 to 2 random stats'],
                ['route' => 'Sprint/Mile Route', 'epithet' => 'Sprint Speedster', 'aggregate' => 'win all four of the sprint and mile races above', 'reward' => '+15 to 2 random stats'],
                ['route' => 'Spring/Autumn Route', 'epithet' => 'Spring Champion', 'races' => ['Osaka Hai', 'Tenno Sho Spring', 'Takarazuka Kinen'], 'reward' => '+10 to 2 random stats'],
                ['route' => 'Spring/Autumn Route', 'epithet' => 'Fall Champion', 'races' => ['Tenno Sho Autumn', 'Japan Cup (Senior)', 'Arima Kinen (Senior)'], 'reward' => '+10 to 2 random stats'],
                ['route' => 'Spring/Autumn Route', 'epithet' => 'Shield Bearer', 'races' => ['Tenno Sho Spring', 'Tenno Sho Autumn'], 'reward' => '+10 to 2 random stats'],
                ['route' => 'Spring/Autumn Route', 'epithet' => 'Legendary', 'aggregate' => 'Lady or Stunning, plus both Champion epithets', 'reward' => 'Homestretch Haste hint +1'],
                ['route' => 'Dirt Route', 'epithet' => 'Dirty Work', 'aggregate' => 'win 5 dirt races', 'reward' => '+5 to 2 stats'],
                ['route' => 'Dirt Route', 'epithet' => 'Playing Dirty', 'aggregate' => 'win 10 dirt races', 'reward' => '+10 to 2 stats'],
                ['route' => 'Dirt Route', 'epithet' => 'Eat My Dust', 'aggregate' => 'win 15 dirt races', 'reward' => '+10 to 2 stats'],
                ['route' => 'Dirt Route', 'epithet' => 'Dirt G1 Achiever', 'aggregate' => 'win 3 dirt G1s', 'reward' => '+10 to 2 stats'],
                ['route' => 'Dirt Route', 'epithet' => 'Dirt G1 Star', 'aggregate' => 'win 4 dirt G1s', 'reward' => '+10 to 2 stats'],
            ],
            'finale' => ['kind' => 'points_league', 'races' => 3],
            'notes' => 'No mandatory race goals, so the race calendar is absent rather than empty, and no '
                .'Scenario Link character exists here. Racing is the strategy in this scenario and is '
                .'discouraged in Unity Cup, so any race advisory must be scenario-gated (D-225).',
        ],

        'our_grand_concert' => [
            'label' => 'Our Grand Concert',
            // Cygames' notice 899 opens "As of 10:00 p.m., Jul 22, 2026 (UTC)", the same instant the
            // export's start_en decodes to, so this is a dated hour and not only a dated day.
            'live_on_global' => '2026-07-22',
            'cap_bonus' => ['Speed' => 400, 'Stamina' => 100, 'Power' => 100, 'Guts' => 300, 'Wit' => 100],
            'widgets' => ['turn', 'energy', 'fans'],
            'steps' => ['training', 'outcome', 'skill'],
            'panels' => [
                'race_calendar' => false,
                'team_race' => false,
                'grade_objectives' => false,
                'shop' => false,
                'epithet_routes' => false,
                'team_rank_ladder' => false,
            ],
            'scenario_links' => [
                'Smart Falcon', 'Agnes Tachyon', 'Silence Suzuka', 'Mihono Bourbon', 'Light Hello',
            ],
            /*
            | Sourced 2026-10-05: GameTora's own sentence is "the level of the training facilities in Grand
            | Live will rise depending on how often you train at it. All facilities start at level 1 and level
            | up every four times you use them ... until level 5." Same rule as URA, tier B, one page.
            */
            'facility_level_source' => 'repetition',
            /*
            | `documented` is a provenance marker, not a rendering switch: nothing in app/, resources/ or
            | tests/ reads it. It asserted that no mechanics guide was held here, and after the 2026-10-05
            | primary read that sentence was false, so it moved. D-241 and gate G-41 are unaffected by the
            | value itself: what they govern is the panels and widgets below, which stay off.
            */
            'documented' => true,
            /*
            | Mechanics are extracted and live in docs/scenarios/07-grand-concert.md, with the raw read in
            | docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md section
            | "Our Grand Concert: the 2026-10-05 primary read" and the changelog in docs/UMAMUSUME_REFERENCE.md
            | §2.8. Shape: a five-type resource earned in training and spent in the Lesson menu on Live
            | Techniques and Songs; a Hype gauge that decides four Promotional Lives held every six months from
            | late December of Junior Year and then the finale; 23 songs; song-count thresholds at 16 and 18
            | decide the finale's hints. Panels still render off, and no resource chip, live marker or goal
            | calendar is invented from a guide, because none has a component and no English name for any of it
            | is a measured client string (§7 rows 48 to 52 of the reference guide). Whether this scenario gains
            | surfaces is the owner's call, and D-241's named acceptance case is now a described scenario: that
            | wording is the gate registry's to amend, not a slice's.
            */
            'notes' => 'Live on `[Global]` since 2026-07-22 and caps Speed highest of any scenario here. '
                .'Mechanics sourced 2026-10-05 by a primary read; every panel stays off because the client '
                .'strings behind them are still third-party renderings, so the strip renders baseline plus the '
                .'published caps (D-241, gate G-41).',
        ],

    ],

];
