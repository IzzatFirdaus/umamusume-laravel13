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
|   panels      docs/scenarios/01..06, reconciled in
|               docs/design-research/SCENARIO-DIFFERENCES.md
|   matrix      docs/design-research/CONSTRAINTS.md §10n
|
| Do not collapse cap_bonus into a flat cap. The bar renders base plus bonus
| as separate labelled terms (DESIGN.md §6.22), and one JP scenario carries a
| negative bonus, so the value is provably a delta rather than a ceiling.
*/

return [

    // Display order and labels. Global client strings only.
    'stat_order' => ['Speed', 'Stamina', 'Power', 'Guts', 'Wit'],

    // The 1200 line is a game mechanic where training gains halve, not an
    // application limit. It is drawn as its own marker on every bar.
    'base_cap' => 1200,

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
        'step' => 150,
        'labels' => ['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S', 'SS'],
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
                . 'This is the minimal strip, and adding another scenario widget here would state something false.',
        ],

        'unity_cup' => [
            'label' => 'Unity Cup',
            'live_on_global' => '2025-11-06',
            'cap_bonus' => ['Speed' => 100, 'Stamina' => 100, 'Power' => 100, 'Guts' => 100, 'Wit' => 600],
            'widgets' => ['turn', 'energy', 'fans', 'team_rank', 'spirit_bursts'],
            'steps' => ['facility', 'training', 'outcome', 'skill'],
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
            'spirit_burst_states' => ['chargeable', 'charged', 'held', 'spent', 'extreme_ready', 'extreme_spent'],
            'team_rank_ladder' => [
                ['ranks' => ['G', 'F'], 'level' => 1],
                ['ranks' => ['D', 'E'], 'level' => 2],
                ['ranks' => ['B', 'C'], 'level' => 3],
                ['ranks' => ['A'], 'level' => 4],
                ['ranks' => ['S'], 'level' => 5],
            ],
            'notes' => 'Facility level is a team property, so a level chip must name its cause (D-222). '
                . 'Rank S+ sits above S and grants a second hint rather than a higher facility.',
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
            'grade_point_by_grade' => ['G1' => 100, 'G2' => 80, 'G3' => 60, 'OP' => 40, 'Pre-OP' => 20],
            'shop_coins_by_placement' => ['1st' => 100, '2nd' => 60, '3rd' => 60, '4th' => 30, '5th' => 30, '6th' => 0],
            'shop' => ['rotation_turns' => 6, 'max_copies_per_item' => 5, 'locked_until_debut' => true],
            'race_fatigue' => ['hide_after' => 'late_december', 'final_races_pay_coins' => false],
            'finale' => ['kind' => 'points_league', 'races' => 3],
            'notes' => 'No mandatory race goals, so the race calendar is absent rather than empty, and no '
                . 'Scenario Link character exists here. Racing is the strategy in this scenario and is '
                . 'discouraged in Unity Cup, so any race advisory must be scenario-gated (D-225).',
        ],

        'our_grand_concert' => [
            'label' => 'Our Grand Concert',
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
            'facility_level_source' => null,
            'documented' => false,
            'notes' => 'Live on Global and caps Speed highest of any scenario here, but no mechanics guide is '
                . 'held for it. Baseline strip plus published caps only: every panel is off, because an '
                . 'undescribed scenario must render as absence rather than as a guess (D-241, gate G-41).',
        ],

    ],

];
