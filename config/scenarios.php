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

    /*
    | The source the matrix's numbers were read against, and whether that source is marked stale.
    | The header above names GameTora `scenarios.json` as the caps source; this key exists so the
    | Dashboard's data-status badge (SCREEN-001, design-2.0 §48) can print it rather than the
    | controller carrying a literal. `stale` is off until a source is marked stale the way
    | `config('advisor')` marks its own.
    */
    'source' => 'GameTora scenarios.json',
    'stale' => false,

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
        'career_goals' => 'Career goals',
    ],

    /*
    | The order objective classes outrank one another on the Cockpit's decision rail (plan §9a FD-3).
    |
    | Read at the top level and not per scenario, so a fifth scenario inherits the same order and names no
    | class of its own (gate G-33, D-240). A class a scenario's matrix does not compose simply produces no
    | row, which is why this list is one flat order rather than four.
    |
    | Ratified by the owner on 2026-10-10 (ruling D-3): `mandatory_race > grade_checkpoint > fan_gate >
    | scenario_milestone > finale`. `screen-spec-2.0.md:1574-1582` ranks survival above mandatory objectives
    | above scenario objectives above build targets, and the two tiers above this list — the advisory Energy
    | line and the run's own obligations — are computed by `NextDecision`, not ordered here. Nothing here is
    | a game rule and no surface presents it as one.
    */
    'objective_priority' => [
        'mandatory_race',
        'grade_checkpoint',
        'fan_gate',
        'scenario_milestone',
        'finale',
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
                'career_goals' => true,
            ],
            'scenario_links' => ['Aoi Kiryuin'],
            'facility_level_source' => 'repetition',
            'notes' => 'Defined by absence: no team system, no shop, no scenario currency. '
                .'This is the minimal strip, and adding another scenario widget here would state something false.',

            /*
            | The three Unique Skill fan gates, in the order the Senior year meets them. Sourced in
            | `docs/scenarios/01-ura-finale.md:80-83` and its Senior Year event table, and again in
            | `docs/UMAMUSUME_REFERENCE.md` §2.2.1. They were placed here rather than in a component
            | because a figure a component owns is a second owner of a scenario fact (`AGENTS.md` §7),
            | and the catalogue cannot hold them: `race_catalog_slots.fans_needed` peaks at 25,000
            | (measured on the dev file), and these three are career events rather than races.
            |
            | Both figures travel because the guides print both and name neither as this trainee's:
            | no column records a turf or dirt preference, so a single threshold would be a guess. The
            | two `N/A` states that follow from that are the read path's, not a key here.
            |
            | No `turn` key exists, and that is sourced too: the guides date these by month and
            | `docs/URA-Finale-plan.md:251` records that no turn offset per event is published.
            */
            'fan_gates' => [
                [
                    'label' => 'Valentine\'s Day',
                    'year_label' => 'Senior',
                    'timing' => 'early February',
                    'turf' => 60000,
                    'dirt' => 40000,
                    'condition' => null,
                    'effect' => 'Unique Skill level up',
                ],
                [
                    'label' => 'Fan Fest',
                    'year_label' => 'Senior',
                    'timing' => 'early April',
                    'turf' => 70000,
                    'dirt' => 60000,
                    'condition' => 'a three-bar friendship with Director Akikawa',
                    'effect' => 'Unique Skill level up, and a mood rise',
                ],
                [
                    'label' => 'Holiday Season',
                    'year_label' => 'Senior',
                    'timing' => 'late December',
                    'turf' => 120000,
                    'dirt' => 80000,
                    'condition' => null,
                    'effect' => 'Unique Skill level up',
                ],
            ],

            /*
            | The duel with Happy Meek, as one run experiences it. `docs/UMAMUSUME_REFERENCE.md` §2.2.1
            | is the source for every figure; `docs/URA-Finale-plan.md:243-259` is the list of what no
            | source answers, and the three nulls below are three items off that list. They stay null
            | rather than holding a placeholder a later slice fills in with an estimate.
            |
            | `hints` keys each stat to the client name of the skill a hint points at. `Racing Spirit:
            | Mood` exists in the catalogue and is deliberately absent here: a duel is fought on a stat,
            | and the sixth skill belongs to no stat the Trainer can pick.
            */
            'duel' => [
                'options_rule' => 'three options: two random stats and the stat the duel was fought on',
                'win' => [
                    'gains' => 'stat gains, and one tier on the cap of the stat dueled',
                    'sp' => 30,
                    'hint' => true,
                ],
                'lose' => [
                    'gains' => 'minor stat gains',
                    'sp' => 15,
                    'hint' => false,
                ],
                'escalation_wins' => 6,
                'escalation_reward' => [
                    'skill' => 'Past My Limits',
                    'sp_minimum' => 200,
                    'spark' => 'a chance at an enhanced spark, which carries a stat bonus and a skill hint into inheritance',
                ],
                'stat_gain_values' => null,
                'win_chance' => null,
                'spark_rate' => null,
                'hints' => [
                    'Speed' => 'Racing Spirit: Speed',
                    'Stamina' => 'Racing Spirit: Stamina',
                    'Power' => 'Racing Spirit: Power',
                    'Guts' => 'Racing Spirit: Guts',
                    'Wit' => 'Racing Spirit: Wit',
                ],
            ],

            /*
            | The published targets a finale-ready trainee is measured against, from
            | `docs/UMAMUSUME_REFERENCE.md` §2.2.1's beginner line. `speed_to_win` keeps the source's
            | own hedge in words: the guide says "around 800", so the value travels with the qualifier
            | rather than being rounded into a requirement it was never stated as.
            */
            'finale_targets' => [
                'speed_to_win' => ['approx' => 800, 'phrasing' => 'around 800 Speed, or an A rank'],
                'stamina_by_distance' => ['Sprint' => 400, 'Mile' => 500, 'Medium' => 600, 'Long' => 700],
            ],

            // No source in this repository describes this scenario in the card metadata's words.
            'metadata' => null,
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
                // docs/scenarios/02-unity-cup.md:153 and SCENARIO-PUBLISHER-REFERENCES.md:658, :1028 —
                // the client shows a countdown under the objective timer, and the two anchors are the
                // ends of the six-month gap. No source publishes the career turn a round falls on
                // (SCENARIO-PUBLISHER-REFERENCES.md:1186 records that as UNVERIFIED), so the panel
                // prints the months and no turn.
                'month_anchors' => ['Late June', 'Late December'],
                /*
                | One race per category, and the category list includes Dirt, which is a surface rather
                | than a distance band: `docs/scenarios/02-unity-cup.md:149` states the five and corrects
                | an earlier "5-person sub-team" line in the same file, and
                | SCENARIO-PUBLISHER-REFERENCES.md:1028 repeats it. This is deliberately NOT
                | `BuildTargetPayload::DISTANCE_BANDS` (four, no Dirt): keying a team race off that list
                | would drop a category the guides name.
                */
                'categories' => ['Sprint', 'Mile', 'Medium', 'Long', 'Dirt'],
                'field_per_category' => 3,
                // 02-unity-cup.md:149 with references:1028 — clearing the set pays a rise on every stat
                // sized by performance. The size itself is published nowhere, so no figure rides here.
                'clear_all_reward' => 'an increase to all stats, sized by performance',
                'opponent_count' => 3,
                'opponents' => [
                    // Keyed `tier`, not by the attribute the JP wikis rank characters
                    // by: the lore gate bans that word, and "which of three teams is
                    // hardest to beat" is not that attribute.
                    //
                    // Uncorroborated as measured 2026-10-11, and left in place rather than deleted: the
                    // corpus names only "Turf Queens" (docs/scenarios/02-unity-cup.md:163,
                    // SCENARIO-PUBLISHER-REFERENCES.md:1324), and neither "Elite Team Ares" nor "Novice
                    // Squad" appears in any document this repository holds. Nothing renders these three
                    // names. The Cockpit's tournament block prints the published shape and the projection
                    // absence instead, so an untraced name cannot reach a Trainer. Clearing or sourcing
                    // this row is the owner's call, not a slice's.
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
            /*
            | The burst-count reward bands, transcribed from docs/scenarios/02-unity-cup.md:213-218
            | ("Exclusive Skills from Spirit Burst Count", corrected to post-rework values, the
            | white/gold framing superseding the withdrawn tier names). Whether Extreme Bursts count
            | toward the threshold is a live publisher disagreement (run report §8.2), so the panel
            | renders the caveat beside the table and never places this run in a band: the count the
            | bands read is not recorded here, and D-223 keeps it that way.
            */
            'spirit_burst_bands' => [
                ['total' => '4–6', 'reward' => 'White hint Lv1, +10 matching stat, +10 SP'],
                ['total' => '7–9', 'reward' => 'White hint Lv3, +20 matching stat, +20 SP'],
                ['total' => '10–12', 'reward' => 'Gold hint Lv1, +30 matching stat, +30 SP'],
                ['total' => '13+', 'reward' => 'Gold hint Lv3, +40 matching stat, +40 SP'],
            ],
            // 02-unity-cup.md:220 — the gold versions arrive from the scripted event in Senior
            // Year, late November. "Late November" is the calendar vocabulary the run screens use.
            'burst_payout_timing' => 'Senior Year, Late November',
            'team_rank_ladder' => [
                ['ranks' => ['G', 'F'], 'level' => 1],
                ['ranks' => ['D', 'E'], 'level' => 2],
                ['ranks' => ['B', 'C'], 'level' => 3],
                ['ranks' => ['A'], 'level' => 4],
                ['ranks' => ['S'], 'level' => 5],
            ],
            'notes' => 'Facility level is a team property, so a level chip must name its cause (D-222). '
                .'Rank S+ sits above S and grants a second hint rather than a higher facility.',
            /*
            | The card's own metadata, recorded for Unity Cup alone. Every line below restates a fact
            | this entry already holds (the team race block, the rank ladder, the burst bands); none is
            | a new claim, and the other three scenarios carry null because no source in this repository
            | describes them in these words. `ruleset` names the scenario's rule family, not a version:
            | no source defines a Global ruleset version, so no version number is invented.
            */
            'metadata' => [
                'ruleset' => 'Team',
                'summary' => 'A team career: facility level follows the team rank, a team race arrives every six months, and Spirit Bursts from teammates grant exclusive skills.',
                'planning_focus' => 'Wit carries the scenario\'s own cap bonus; team rank and Spirit Burst counts decide the exclusive-skill payout.',
                'key_mechanics' => [
                    'A team race every six months against one of three NPC teams, strongest to weakest; a loss lowers league rank.',
                    'Facility level is a team property, set by the team rank from G through S (D-222).',
                    'Spirit Bursts from teammates charge and spend, and the burst count sets the exclusive-skill reward band.',
                ],
            ],
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
            /*
            | The placement modifiers that scale a grade's first-place figure down. Transcribed
            | from docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md lines 104-112 (uma.guide,
            | page last updated 2026-04-29, so that master's line 50): 1st 100%, 2nd 60%, 3rd 40%,
            | 4th-5th 20%, 6th and lower 10%.
            |
            | It is one publisher's table. The other Trackblazer section in the same master names no
            | ratio at all: line 355 says lower placements "scale down proportionally" and stops
            | there, which is the gap `RaceEntry::gradePointsFor()` refuses to price (its comment at
            | lines 156-166, and the withholding rule in `TrainingRun::gradeEarnedFor()`).
            |
            | So these figures answer one question only: what a race that has not been run yet would
            | still pay at a stated placement. No earned total in this tool reads them, and closing
            | that half is an owner decision about the read path, not a config edit.
            */
            'grade_point_placement_modifiers' => ['1st' => 100, '2nd' => 60, '3rd' => 40, '4th' => 20, '5th' => 20, '6th' => 10],
            /*
            | The two bounds the route planner prints its floor against. The plan names the pair
            | ("floor (2nd-3rd)") at docs/Trackblazer-plan.md:536, and a band reads as two figures
            | rather than as one number pretending to be certain.
            */
            'planning' => [
                'floor_placements' => ['2nd', '3rd'],
                'density_threshold' => 3,
                'calendar_window' => 3,
                /*
                | The runway is the screen's main occupant while the plan is still ahead of the Trainer,
                | and folds back once the first checkpoint is behind them: the periods already judged have
                | no plan left to make. Compared against the period the Trainer reports as live, so this
                | is the number that decides the fold and not a turn count inferred from a date (D-270).
                */
                'collapses_after_period' => 2,
            ],
            /*
            | The year-end gates: the Late December assessment that levels up the Unique Skill.
            | Fans are published by two sources in the same master and they agree exactly —
            | docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md lines 306-310 (uma.guide) and the
            | GameWith row at line 1235 both print 5,000 / 60,000 / 120,000.
            |
            | The Director's bond does not agree with itself: line 310 says 19 / 31 / 51 while the
            | GameWith row at line 1235 says 15 / 30 / 50, and docs/Trackblazer-plan.md:361 rules only
            | that the uma.guide figures are single-source. Both pairs are therefore printed and
            | neither is settled here, the way KI-15 keeps two published tracks unchosen rather than
            | picking a denominator. No column stores this run's bond reading either way.
            */
            'year_end_gates' => [
                ['label' => 'Junior', 'timing' => 'Late December, Junior Year', 'fans' => 5000, 'bond_uma_guide' => 19, 'bond_game_with' => 15],
                ['label' => 'Classic', 'timing' => 'Late December, Classic Year', 'fans' => 60000, 'bond_uma_guide' => 31, 'bond_game_with' => 30],
                ['label' => 'Senior', 'timing' => 'Late December, Senior Year', 'fans' => 120000, 'bond_uma_guide' => 51, 'bond_game_with' => 50],
            ],
            /*
            | Rival races appear on their own schedule, which is the whole reason a plan cannot hold
            | one: docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md line 194 says they "Appear
            | randomly starting Early August, Junior Year". `ADR-0016` keeps the rows as facts, so the
            | surface says the sentence and lists nothing.
            */
            'rival_races' => ['first_offered' => 'Early August, Junior Year', 'scheduling' => 'random'],
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
            /*
            | The publisher's own "Worth" ratings, keyed by the catalogue item they belong to.
            |
            | Transcribed from docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md lines 199-222
            | (uma.guide's "Shop Item Priorities"), whose table names items by cost and effect rather
            | than by client name. A key exists here only where a config row's cost and effect both
            | match a publisher row exactly, so the pairing is forced rather than inferred: a rating
            | beside the wrong item is worse than no rating. Items the publisher names that this
            | catalogue does not carry (the +65 Energy item, both bond items, the shuffle reset, the
            | Skin Outbreak heal, Fast Learner, Charming) get no key, and the row prints no rating.
            |
            | These are one guide's opinions, not game rules, which is why every one of them travels
            | as `estimated` with the source in its title. They are not this tool's recommendation:
            | the shop arm's own sentence, "no row is recommended", stays true because the tool
            | recommends nothing and reports what one publisher said.
            */
            'shop_worth' => [
                'Speed Notepad' => 'Low priority filler only',
                'Speed Manual' => 'Must Buy',
                'Speed Scroll' => 'Must Buy',
                'Vita 20' => 'Must Buy',
                'Vita 40' => 'Must Buy',
                'Royal Kale Juice' => 'Must Buy, paired with a Mood item to offset the downside',
                'Energy Drink MAX EX' => 'Generally Skip',
                'Plain Cupcake' => 'Must Buy, and the +1 has priority',
                'Berry Sweet Cupcake' => 'Must Buy',
                'Coaching Megaphone' => 'Skip',
                'Motivating Megaphone' => 'Only if the 60% ones are lacking',
                'Empowering Megaphone' => 'Must Buy',
                'Ankle Weights' => 'Must Buy for your priority stat',
                'Good-Luck Charm' => 'Must Buy',
                'Artisan Cleat Hammer' => 'Must Buy',
                'Master Cleat Hammer' => 'Must Buy',
                'Glow Sticks' => 'Situational',
                'Speed Training Application' => 'Only with coin surplus',
                'Miracle Cure' => 'Optional, worth holding one',
            ],
            'shop_worth_source' => 'docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md lines 199-222 (uma.guide\'s own "Worth" ratings; the master dates the page 2026-04-29).',
            /*
            | The Summer reserve is the publisher's Tip at line 273, in its own words. The tool
            | derives no reserve figure of its own and prints none beside it: a number this build
            | invented would read as a game rule.
            */
            'shop_reserve' => ['label' => 'Keep at least 100 coins banked heading into Summer', 'coins' => 100],
            'shop_reserve_source' => 'docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md line 273 (uma.guide\'s Tip), which also warns the shop resets on Summer\'s first turn.',
            /*
            | Race Fatigue keys on the length of the race chain and on nothing else. The odds table is
            | transcribed from docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md lines 231-236
            | (uma.guide), which is the only publisher in this corpus that prints figures at all; the
            | row is grouped under the chain length the publisher labels it with, so the surface reads a
            | row rather than choosing a column.
            |
            | `caveats` are the two questions that table leaves open, worded at
            | docs/Trackblazer-plan.md:283-284. They travel as display copy because a tooltip is not a
            | caveat: a Trainer who never hovers never reads them.
            |
            | `hide_after` is the publisher's own limit at line 238 and it names no year, which is
            | caveat two. `final_races_pay_coins` is line 299 (the Climax races pay no coins).
            */
            'race_fatigue' => [
                'hide_after' => 'late_december',
                'final_races_pay_coins' => false,
                'chain_labels' => ['1 race', '2 races', '3 races', '4+ races'],
                'odds' => [
                    '1 race' => [
                        'Mood Down (1+ Energy)' => '0%',
                        'Mood Down (0 Energy)' => '15%',
                        'Skin Outbreak (1+ Energy)' => '0%',
                        'Skin Outbreak (0 Energy)' => '4%',
                        '3 random stats −10' => '0%',
                    ],
                    '2 races' => [
                        'Mood Down (1+ Energy)' => '0%',
                        'Mood Down (0 Energy)' => '33%',
                        'Skin Outbreak (1+ Energy)' => '0%',
                        'Skin Outbreak (0 Energy)' => '8%',
                        '3 random stats −10' => '0%',
                    ],
                    '3 races' => [
                        'Mood Down (1+ Energy)' => '60%',
                        'Mood Down (0 Energy)' => '90%+',
                        'Skin Outbreak (1+ Energy)' => '15%',
                        'Skin Outbreak (0 Energy)' => '25%',
                        '3 random stats −10' => '0%',
                    ],
                    '4+ races' => [
                        'Mood Down (1+ Energy)' => '100%',
                        'Mood Down (0 Energy)' => '100%',
                        'Skin Outbreak (1+ Energy)' => '33%',
                        'Skin Outbreak (0 Energy)' => '33%',
                        '3 random stats −10' => '40%',
                    ],
                ],
                'caveats' => [
                    'The publisher does not say whether "3 races" counts the race about to run or only the races already run, so this surface prints the row its own label names and settles nothing.',
                    'Race fatigue cannot occur after Late December, and no source says which year\'s Late December closes it.',
                ],
                'recovery_source' => 'docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md lines 242-244 (uma.guide).',
                'recovery_events' => [
                    ['when' => 'Classic, Early February', 'effect' => 'Mood +1'],
                    ['when' => 'Classic, Early March', 'effect' => 'Energy +20'],
                    ['when' => 'Classic, Late September', 'effect' => 'Mood +1'],
                    ['when' => 'Senior, Late June', 'effect' => 'Energy +20'],
                    ['when' => 'Senior, Late October', 'effect' => 'Mood +1'],
                    ['when' => 'Senior, Late December', 'effect' => 'Energy +30'],
                ],
            ],
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
            // No source in this repository describes this scenario in the card metadata's words.
            'metadata' => null,
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
            /*
            | This scenario offers the turn rail a Performance observation field. It is deliberately NOT a
            | `panels` key. `panels` is the list the ScenarioPanel shell renders, and this scenario composes
            | none of them — that absence is what the baseline strip and gate G-41 assert, so switching a
            | panel on to reveal a rail input would hand the shell an ON panel with no renderer. A turn-rail
            | input is not a panel, so it carries its own flag and the panel map stays as the shell reads it.
            |
            | Only the flag lives here, never the five type names: those are the domain enum's
            | (App\Enums\PerformanceType), read from Cygames' `[Global]` notice 905, which prints "There are
            | five types of Performance: Dance, Passion, Vocals, Visuals, and Composure". Repeating them in
            | config would be a second source for one vocabulary. What the flag does not buy is any number:
            | no starting value, no per-turn amount, no cost and no cap is stored or implied here.
            */
            'performance_input' => true,
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
            | The rendering switch the two screens above read, and the only one. documented above is a
            | provenance marker and stays unread by app code, so it cannot double as the badge condition
            | without inventing a meaning for it: that key flipped to true on 2026-10-05 when the mechanics
            | read landed, and no screen reads it, so nothing today proves the value means what a badge
            | would need it to mean.
            |
            | true is the owner's own words for this scenario: the PRODUCT CORRECTION table marks it
            | "PARTIALLY DOCUMENTED", and the notes below are why, the client strings behind the panels
            | are still third-party renderings, so the panels render off and only the baseline strip plus
            | the published caps is drawn. The word "partially" is what the badge prints, so the flag
            | names what is printed rather than a state the screen has to interpret.
            |
            | Absent from every other entry, which is false: a scenario with no marker renders no badge.
            | Setting false here instead of omitting it would be the same output, and omission is the
            | shape of "nothing to say" this matrix already uses.
            */
            'partially_documented' => true,

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
            | surfaces was the owner's call, and on 2026-10-08 the owner ruled yes: Our Grand Concert is to gain
            | its own surfaces as part of Trainer Desk 2.0. The ruling authorises the experience, not the
            | invention of data, so the `❌ UNVERIFIED` boundary in docs/scenarios/07 stays authoritative for
            | everything unmeasured, and a surface that needs a value no source publishes stays unbuilt rather
            | than filled with a plausible number. D-241's named acceptance case is now a described scenario:
            | that wording is the gate registry's to amend, not a slice's.
            */
            /*
            | The one line the baseline strip prints where a panel would be, and the only scenario that
            | declares one. It is display copy on the config entry rather than a sentence in a component
            | because the fact it states is this scenario's own: a fifth scenario that leaves every panel
            | off supplies its own line here or none, and the shell reads whichever it finds (D-240, gate
            | G-33). `panel_absence_title` is the strip's `title`, and it names the file the reason is
            | recorded in.
            |
            | The sentence says the true reason the panels are off, which is not that the mechanics are
            | unread: `docs/scenarios/07-grand-concert.md` holds them, refilled 2026-10-05. What no source
            | here measures is the text on the client's own screens, so no panel can be drawn from a guide
            | without inventing the words above it (D-165, D-241).
            */
            'panel_absence' => 'The mechanics for this scenario are sourced, but no capture of the client\'s own screens exists yet, so no panel is drawn.',
            'panel_absence_title' => 'Sourced in docs/scenarios/07-grand-concert.md. The client\'s own screen text is not measured in this build.',
            'notes' => 'Live on `[Global]` since 2026-07-22 and caps Speed highest of any scenario here. '
                .'Mechanics sourced 2026-10-05 by a primary read; every panel stays off because the client '
                .'strings behind them are still third-party renderings, so the strip renders baseline plus the '
                .'published caps (D-241, gate G-41).',
            // No source in this repository describes this scenario in the card metadata's words.
            'metadata' => null,
        ],

    ],

];
