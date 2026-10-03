<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Enum display labels
    |--------------------------------------------------------------------------
    |
    | Keyed by case NAME, never by backing value: the value is the machine token
    | that option fields, route params and the ?status= filter must keep
    | submitting. Every case of every enum rendered in a view is listed, so the
    | full set of labels is readable from this file alone.
    |
    */

    'release_status' => [
        'GlobalReleased' => 'Released (Global)',
        'GlobalAnnounced' => 'Announced (Global)',
        'JapanOnly' => 'Japan only',
    ],

    'run_status' => [
        'Active' => 'Active',
        'Completed' => 'Completed',
        'Retired' => 'Retired',
    ],

    'skill_acquisition' => [
        'Suggested' => 'Starting',
        'Acquired' => 'Acquired',
        'Skipped' => 'Skipped',
    ],

    'alias_language' => [
        'Japanese' => 'Japanese',
        'English' => 'English',
        'Romanized' => 'Romanized',
    ],

    'match_tier' => [
        'Exact' => 'Exact',
        'Alias' => 'Alias',
        'Fuzzy' => 'Fuzzy',
        'None' => 'None',
    ],

    'card_rarity' => [
        'OneStar' => 'One star',
        'TwoStar' => 'Two stars',
        'ThreeStar' => 'Three stars',
    ],

    /*
    |--------------------------------------------------------------------------
    | JP to Global game-term map
    |--------------------------------------------------------------------------
    |
    | Reference data for copy that is not on screen yet: this tool has no Scouts,
    | Spark or running-style surface in Phase 1 (PRD §6), so nothing here is
    | wired into a view. Each row is the [Global] English term as recorded in
    | docs/UMAMUSUME_REFERENCE.md §1.7 and §6. Entries the reference could not
    | source to a client string or an official notice are deliberately absent
    | rather than guessed; the reasons are noted below.
    |
    */

    'terms' => [
        'scouts' => 'Scouts',
        'pull_currency' => 'Carats',
        'inheritance_trait' => 'Spark',
        'finished_character' => 'Veteran Umamusume',
        'trainee' => 'Trainee Umamusume',
        'team_pvp' => 'Team Trials',

        // Support-card types. 賢さ keys as `intelligence` in the export but the
        // player-facing label is Wit; the reference logs that conflict.
        'card_speed' => 'Speed',
        'card_stamina' => 'Stamina',
        'card_power' => 'Power',
        'card_guts' => 'Guts',
        'card_wit' => 'Wit',
        'card_group' => 'Group',

        // Sourced to game8.co, not to a captured [Global] client string; the
        // export keys this type from the JP 友人 term instead. Treat as
        // community-standard wording, not as an in-game label.
        'card_pal' => 'Pal',

        'style_front_runner' => 'Front Runner',
        'style_pace_chaser' => 'Pace Chaser',
        'style_late_surger' => 'Late Surger',
        'style_end_closer' => 'End Closer',

        // All five confirmed against the [Global] client Mood panel.
        'mood_great' => 'GREAT',
        'mood_good' => 'GOOD',
        'mood_normal' => 'NORMAL',
        'mood_bad' => 'BAD',
        'mood_awful' => 'AWFUL',

        // Omitted on purpose:
        //   inheritance_system: the draft proposed "Inspiration", which the
        //     reference sources only to a >90-day-old community wiki. Its
        //     client-sourced [Global] word for that screen is "Legacy".
        //   idle_training: "Independent Training" is a client string but the
        //     reference records it as a [JP] mode whose [Global] release is
        //     unconfirmed, so it is not a [Global] equivalent yet.
    ],

];
