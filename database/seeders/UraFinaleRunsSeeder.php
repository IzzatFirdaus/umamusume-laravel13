<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\ImportHistoricalRun;
use App\Domain\Career\CareerPosition;
use App\Enums\MoodTier;
use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Enums\SkillAcquisition;
use App\Models\TrainingRun;
use App\Services\CareerCalendar;
use Illuminate\Database\Seeder;

/**
 * Imports the eight URA Finale career runs recorded in the committed tracker
 * (docs/Umamusume_Progress_Tracker_Runs.md, runs 1, 2, 3, 4, 11, 14, 15, 16).
 *
 * Each run lands as one training_runs row with a single turn_entry holding the
 * snapshot the tracker records, its run_skills roster, and race_entries only
 * for the race days the tracker actually resolves (Junior Make Debut and the
 * URA Finale slots). Skills resolve by exact catalog name; the tracker's
 * tier-two glyph (â¦¾) maps to the catalog's â—Ž row and falls back to Ã— when a
 * skill has no â—Ž row (Corner Adept, Corner Acceleration).
 *
 * Idempotent by import_source: a run already carrying its tracker label is
 * skipped, so re-running the seeder does not duplicate rows.
 *
 * @see docs/imports/ura-finale-runs.md for the full mapping and the omitted
 *      content (deck loadouts, growth, aptitude, fans totals) and why.
 */
class UraFinaleRunsSeeder extends Seeder
{
    private const string SOURCE_FILE = 'docs/Umamusume_Progress_Tracker_Runs.md';

    /**
     * Catalog ids for the four race slots a run can resolve a result for.
     */
    private const int JUNIOR_DEBUT_SLOT = 1;

    private const int URA_QUALIFIER_SLOT = 405;

    private const int URA_SEMIFINAL_SLOT = 406;

    private const int URA_FINAL_SLOT = 410;

    /**
     * Catalog ids for non-URA races that exist in race_catalog_slots on this tree.
     * Races without a catalog slot (Negishi S., February S., Elm S., Hanshin
     * Daishoten, etc.) are recorded in `notes` only, not as race_entries.
     */
    private const int KIKUKA_SHO_CLASSIC_SLOT = 180;

    private const int ARIMA_KINEN_CLASSIC_SLOT = 215;

    private const int JBC_SPRINT_SENIOR_SLOT = 375;

    private const int ARIMA_KINEN_SENIOR_SLOT = 400;

    /**
     * Public so the test can materialise the FK prerequisites (umamusume, cards,
     * skills, race slots) straight from this table instead of duplicating ids.
     *
     * @var array<int, array{umamusume_id: int, character_card_id: int, turn: int, stats: array{speed: int, stamina: int, power: int, guts: int, wit: int}, sp: int|null, energy: int|null, condition: string|null, mood: string|null, status: RunStatus, skills: list<array{0: int, 1: SkillAcquisition}>, races: list<array{slot: int, status: RaceEntryStatus, placement: int|null}>, notes: string}>
     */
    public const array RUNS = [
        1 => [
            'umamusume_id' => 14,
            'character_card_id' => 19,
            'turn' => 48,
            'stats' => ['speed' => 474, 'stamina' => 454, 'power' => 405, 'guts' => 236, 'wit' => 231],
            'sp' => 267,
            'energy' => 80,
            'condition' => null,
            'mood' => MoodTier::Bad->value,
            'status' => RunStatus::Active,
            'skills' => [
                [16, SkillAcquisition::Acquired], // CorazÃ³n â˜† Ardiente (Unique Burst)
                [51, SkillAcquisition::Suggested],
                [35, SkillAcquisition::Suggested],
                [38, SkillAcquisition::Suggested],
                [139, SkillAcquisition::Suggested], // Long Shot â—‹
                [138, SkillAcquisition::Suggested], // Long Shot â—Ž
                [151, SkillAcquisition::Suggested],
                [5, SkillAcquisition::Suggested],
                [6, SkillAcquisition::Suggested],
                [181, SkillAcquisition::Suggested],
                [185, SkillAcquisition::Suggested],
                [189, SkillAcquisition::Acquired],
                [199, SkillAcquisition::Suggested],
                [219, SkillAcquisition::Suggested],
                [229, SkillAcquisition::Suggested],
                [241, SkillAcquisition::Suggested],
                [277, SkillAcquisition::Acquired],
                [281, SkillAcquisition::Suggested],
                [311, SkillAcquisition::Acquired], // Pace Chaser Straightaways â—‹
                [310, SkillAcquisition::Suggested], // Pace Chaser Straightaways â—Ž
                [318, SkillAcquisition::Suggested],
                [356, SkillAcquisition::Suggested], // Late Surger Savvy â—‹
                [355, SkillAcquisition::Suggested], // Late Surger Savvy â—Ž
            ],
            'races' => [
                ['slot' => self::JUNIOR_DEBUT_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 1],
            ],
            'notes' => 'Snapshot at CLASSIC LATE DEC (turn 48), Mid Cross-pick "Takarazuka Kinen" with race day white. '
                .'Goals panel records Junior Make Debut 1st, Kyodo News Hai 1st, NHK Mile C. 2nd, Japanese Derby 2nd, '
                .'Mainichi Okan 1st, Takarazuka Kinen 1st; Japan C., Arima Kinen and the three URA Finale goals unrun. '
                .'Internal contradiction in the tracker keeps turn at 48: the plan box says 9 turns remain before '
                .'Takarazuka while the goals panel already shows it won. Growth: Speed +20, Wit +10. Aptitudes: '
                .'Turf A, Dirt B, Sprint F, Mile A, Medium A, Long A, Front E, Pace A, Late A, End B.',
        ],
        2 => [
            'umamusume_id' => 2,
            'character_card_id' => 4,
            'turn' => 44,
            'stats' => ['speed' => 500, 'stamina' => 249, 'power' => 380, 'guts' => 167, 'wit' => 247],
            'sp' => 19,
            'energy' => 60,
            'condition' => 'Hot Topic',
            'mood' => MoodTier::Great->value,
            'status' => RunStatus::Active,
            'skills' => [
                [30, SkillAcquisition::Acquired], // Sky-High Teio Step (Unique Burst)
                [35, SkillAcquisition::Suggested],
                [387, SkillAcquisition::Suggested],
                [166, SkillAcquisition::Suggested],
                [176, SkillAcquisition::Acquired],
                [185, SkillAcquisition::Acquired],
                [279, SkillAcquisition::Acquired],
                [300, SkillAcquisition::Suggested], // Front Runner Straightaways â—‹
                [302, SkillAcquisition::Suggested], // Front Runner Corners â—‹
                [314, SkillAcquisition::Suggested],
                [315, SkillAcquisition::Acquired],
                [346, SkillAcquisition::Suggested],
                [360, SkillAcquisition::Suggested],
            ],
            'races' => [
                ['slot' => self::JUNIOR_DEBUT_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 1],
            ],
            'notes' => 'Snapshot at year-2 Late October / Kikuka Sho race day (turn 44), 0 laps left. Goals: Junior Make '
                .'Debut 1st, Wakagoma S. 1st, Satsuki Sho 1st, Japanese Derby 4th; Kikuka Sho and URA Finale unrun. '
                .'One skill row lost its name at a page break (SP 120, declined) and is not attached. Growth: '
                .'Speed +20, Stamina +10. Aptitudes: Turf A, Dirt G, Sprint F, Mile D, Medium A, Long B, Front C, '
                .'Pace A, Late B, End E.',
        ],
        3 => [
            'umamusume_id' => 4,
            'character_card_id' => 6,
            'turn' => 75,
            'stats' => ['speed' => 785, 'stamina' => 344, 'power' => 753, 'guts' => 321, 'wit' => 628],
            'sp' => 34,
            'energy' => 25,
            'condition' => null,
            'mood' => MoodTier::Great->value,
            'status' => RunStatus::Completed,
            'skills' => [
                [31, SkillAcquisition::Acquired], // Red Shift/LP1211-M (Unique Burst)
                [34, SkillAcquisition::Suggested],
                [10, SkillAcquisition::Suggested],
                [7, SkillAcquisition::Acquired],
                [38, SkillAcquisition::Acquired],
                [105, SkillAcquisition::Acquired], // Spring Runner â—‹
                [182, SkillAcquisition::Acquired],
                [273, SkillAcquisition::Acquired], // Medium Straightaways â—‹
                [139, SkillAcquisition::Suggested], // Long Shot â—‹
                [151, SkillAcquisition::Acquired],
                [168, SkillAcquisition::Suggested],
                [179, SkillAcquisition::Acquired],
                [208, SkillAcquisition::Suggested],
                [238, SkillAcquisition::Suggested],
                [263, SkillAcquisition::Acquired],
                [265, SkillAcquisition::Suggested],
                [300, SkillAcquisition::Suggested], // Front Runner Straightaways â—‹
                [299, SkillAcquisition::Suggested], // Front Runner Straightaways â—Ž
                [352, SkillAcquisition::Suggested], // Front Runner Savvy â—‹
            ],
            'races' => [
                ['slot' => self::JUNIOR_DEBUT_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 1],
                ['slot' => self::URA_QUALIFIER_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 1],
                ['slot' => self::URA_SEMIFINAL_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 1],
                ['slot' => self::URA_FINAL_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 1],
            ],
            'notes' => 'Completed run, URA Finale won 1st in every slot (Qualifier, Semifinal, Final). Goals panel '
                .'records Junior Make Debut 1st, Asahi Hai F.S. 1st, Spring S. 1st, Satsuki Sho 1st, Japanese Derby '
                .'4th, Arima Kinen 12th, Osaka Hai 1st, Yasuda Kinen 1st, Tenno Sho (Autumn) 1st. Growth: Speed +10, '
                .'Wit +20. Aptitudes: Turf A, Dirt D, Sprint B, Mile A, Medium A, Long C, Front A, Pace E, Late F, '
                .'End G.',
        ],
        4 => [
            'umamusume_id' => 4,
            'character_card_id' => 6,
            'turn' => 75,
            'stats' => ['speed' => 715, 'stamina' => 360, 'power' => 579, 'guts' => 274, 'wit' => 365],
            'sp' => 65,
            'energy' => 85,
            'condition' => null,
            'mood' => MoodTier::Good->value,
            'status' => RunStatus::Completed,
            'skills' => [
                [31, SkillAcquisition::Acquired], // Red Shift/LP1211-M (Unique Burst)
                [34, SkillAcquisition::Suggested],
                [35, SkillAcquisition::Suggested],
                [93, SkillAcquisition::Acquired], // Standard Distance â—‹
                [92, SkillAcquisition::Acquired], // Standard Distance â—Ž
                [134, SkillAcquisition::Acquired], // Competitive Spirit â—‹
                [133, SkillAcquisition::Suggested], // Competitive Spirit â—Ž
                [137, SkillAcquisition::Suggested], // Target in Sight â—‹
                [136, SkillAcquisition::Suggested], // Target in Sight â—Ž
                [139, SkillAcquisition::Suggested], // Long Shot â—‹
                [138, SkillAcquisition::Suggested], // Long Shot â—Ž
                [7, SkillAcquisition::Suggested],
                [142, SkillAcquisition::Suggested], // Corner Adept â—‹
                [143, SkillAcquisition::Suggested], // Corner Adept Ã—
                [145, SkillAcquisition::Acquired], // Corner Acceleration â—‹
                [146, SkillAcquisition::Suggested], // Corner Acceleration Ã—
                [151, SkillAcquisition::Acquired],
                [154, SkillAcquisition::Suggested],
                [155, SkillAcquisition::Suggested],
                [161, SkillAcquisition::Acquired], // Focus
                [164, SkillAcquisition::Acquired], // Lay Low
                [6, SkillAcquisition::Suggested],
                [179, SkillAcquisition::Acquired], // Early Lead
                [181, SkillAcquisition::Suggested],
                [182, SkillAcquisition::Suggested],
                [183, SkillAcquisition::Suggested],
                [193, SkillAcquisition::Suggested],
                [263, SkillAcquisition::Suggested],
                [265, SkillAcquisition::Suggested],
                [303, SkillAcquisition::Suggested],
            ],
            'races' => [
                ['slot' => self::JUNIOR_DEBUT_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 2],
                ['slot' => self::URA_QUALIFIER_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 1],
                ['slot' => self::URA_SEMIFINAL_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 2],
                ['slot' => self::URA_FINAL_SLOT, 'status' => RaceEntryStatus::Skipped, 'placement' => null],
            ],
            'notes' => 'Completed run, career ended with an URA Finale DNF (Skipped final slot; Qualifier 1st, '
                .'Semifinal 2nd then did not finish the Final). Goals panel otherwise: Junior Make Debut 2nd, Asahi '
                .'Hai F.S. 3rd, Spring S. 1st, Satsuki Sho 1st, Japanese Derby 4th, Arima Kinen 7th, Osaka Hai 1st, '
                .'Yasuda Kinen 1st, Tenno Sho (Autumn) 1st. Growth: Speed +10, Wit +20. Aptitudes: Turf A, Dirt D, '
                .'Sprint B, Mile A, Medium A, Long C, Front A, Pace E, Late F, End G.',
        ],
        11 => [

            'umamusume_id' => 50,
            'character_card_id' => 43,
            'turn' => 73,
            'stats' => ['speed' => 423, 'stamina' => 276, 'power' => 461, 'guts' => 448, 'wit' => 264],
            'sp' => 4,
            'energy' => 20,
            'condition' => 'CHARMING',
            'mood' => MoodTier::Good->value,
            'status' => RunStatus::Active,
            'skills' => [
                [24, SkillAcquisition::Acquired], // Super-Duper Stoked (Unique Burst)
                [43, SkillAcquisition::Acquired], // âˆ´win Q.E.D.
                [108, SkillAcquisition::Suggested], // Summer Runner â—‹
                [121, SkillAcquisition::Suggested], // Rainy Days â—‹
                [150, SkillAcquisition::Suggested],
                [151, SkillAcquisition::Suggested],
                [164, SkillAcquisition::Acquired], // Lay Low
                [170, SkillAcquisition::Suggested],
                [172, SkillAcquisition::Acquired],
                [6, SkillAcquisition::Acquired],
                [182, SkillAcquisition::Suggested],
                [183, SkillAcquisition::Suggested],
                [199, SkillAcquisition::Acquired],
                [203, SkillAcquisition::Acquired],
                [214, SkillAcquisition::Suggested],
                [230, SkillAcquisition::Suggested],
                [240, SkillAcquisition::Suggested],
                [254, SkillAcquisition::Suggested],
                [308, SkillAcquisition::Suggested],
                [318, SkillAcquisition::Suggested],
                [320, SkillAcquisition::Suggested],
                [330, SkillAcquisition::Acquired],
            ],
            'races' => [
                ['slot' => self::JUNIOR_DEBUT_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 2],
                ['slot' => self::JBC_SPRINT_SENIOR_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 1],
                ['slot' => self::ARIMA_KINEN_SENIOR_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 16],
            ],
            'notes' => 'Active run snapshotted on URA Finale Qualifier race day (turn 73). Goals: Junior Make Debut 2nd, '
                .'Negishi S. 2nd, February S. 4th, Elm S. 3rd, JBC Sprint 1st, Arima Kinen 16th, and the three Earn Fans '
                .'targets 5000/9000/12000 marked done (no team table exists to store them in, kept in this note). URA '
                .'Semifinal and Final unrun. The former "URA Qualifier Entered" row has no source in the tracker and is '
                .'not recorded. Growth: Speed +10, Power +10, Guts +20. Aptitudes: Turf C, Dirt A, Sprint A, Mile A, '
                .'Medium G, Long G, Front G, Pace G, Late A, End B.',
        ],
        14 => [
            'umamusume_id' => 9,
            'character_card_id' => 12,
            'turn' => 75,
            'stats' => ['speed' => 663, 'stamina' => 437, 'power' => 543, 'guts' => 408, 'wit' => 303],
            'sp' => 75,
            'energy' => null,
            'condition' => 'PRACTICE POOR',
            'mood' => null,
            'status' => RunStatus::Completed,
            'skills' => [
                [35, SkillAcquisition::Acquired], // Resplendent Red Ace (Unique Burst)
                [93, SkillAcquisition::Suggested], // Standard Distance â—‹
                [92, SkillAcquisition::Suggested], // Standard Distance â—Ž
                [102, SkillAcquisition::Suggested], // Wet Conditions â—‹
                [111, SkillAcquisition::Suggested], // Fall Runner â—‹
                [132, SkillAcquisition::Suggested], // Maverick â—‹
                [134, SkillAcquisition::Acquired], // Competitive Spirit â—‹
                [133, SkillAcquisition::Suggested], // Competitive Spirit â—Ž
                [144, SkillAcquisition::Suggested],
                [145, SkillAcquisition::Suggested], // Corner Acceleration â—‹
                [153, SkillAcquisition::Acquired], // Straightaway Acceleration
                [163, SkillAcquisition::Suggested],
                [164, SkillAcquisition::Suggested], // Lay Low
                [6, SkillAcquisition::Acquired],
                [181, SkillAcquisition::Acquired],
                [183, SkillAcquisition::Suggested],
                [185, SkillAcquisition::Suggested],
                [187, SkillAcquisition::Suggested],
                [188, SkillAcquisition::Suggested],
                [189, SkillAcquisition::Suggested],
                [3, SkillAcquisition::Acquired],
                [217, SkillAcquisition::Suggested],
                [221, SkillAcquisition::Suggested],
                [267, SkillAcquisition::Acquired],
                [294, SkillAcquisition::Suggested], // Pressure (Global release)
                [302, SkillAcquisition::Acquired], // Front Runner Corners â—‹
                [301, SkillAcquisition::Acquired], // Front Runner Corners â—Ž
                [324, SkillAcquisition::Suggested], // Late Surger Straightaways â—‹
                [344, SkillAcquisition::Suggested],
            ],
            'races' => [
                ['slot' => self::URA_FINAL_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 2],
            ],
            'notes' => 'Completed run whose tracker row shows no goals panel; the terminal line "SHE\'S 2ND PLACE" is '
                .'stored as the URA Finale result (2nd). Qualifier and Semifinal not recorded. Condition cell reads '
                .'PRACTICE POOR verbatim. Growth: Speed +10, Guts +20. Aptitudes: Turf A, Dirt G, Sprint F, Mile A, '
                .'Medium A, Long B, Front A, Pace A, Late E, End G.',
        ],
        15 => [
            'umamusume_id' => 8,
            'character_card_id' => 11,
            'turn' => 75,
            'stats' => ['speed' => 646, 'stamina' => 474, 'power' => 765, 'guts' => 284, 'wit' => 279],
            'sp' => 347,
            'energy' => null,
            'condition' => null,
            'mood' => null,
            'status' => RunStatus::Completed,
            'skills' => [
                [34, SkillAcquisition::Acquired], // Cut and Drive!
                [43, SkillAcquisition::Suggested], // âˆ´win Q.E.D.
                [153, SkillAcquisition::Acquired], // Straightaway Acceleration
                [155, SkillAcquisition::Acquired],
                [161, SkillAcquisition::Acquired], // Focus
                [174, SkillAcquisition::Acquired], // Nimble Navigator
                [6, SkillAcquisition::Acquired],
                [182, SkillAcquisition::Suggested],
                [183, SkillAcquisition::Suggested],
                [193, SkillAcquisition::Suggested],
                [199, SkillAcquisition::Suggested],
                [212, SkillAcquisition::Suggested], // Updrafters
                [318, SkillAcquisition::Suggested],
                [356, SkillAcquisition::Acquired], // Late Surger Savvy â—‹
                [355, SkillAcquisition::Acquired], // Late Surger Savvy â—Ž
                [300, SkillAcquisition::Suggested], // Front Runner Straightaways â—‹
            ],
            'races' => [
                ['slot' => self::URA_FINAL_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 1],
            ],
            'notes' => 'Completed run whose tracker row shows no goals panel; the terminal line "SHE WON 1ST" is '
                .'stored as the URA Finale result (1st). Qualifier and Semifinal not recorded. The "Front Runner '
                .'Straightaways" row lost its tier glyph on this page (cost 117 matches the â—‹ tier) and resolves to '
                .'the ○ row. Growth: Speed +20, Stamina +10. Aptitudes: Turf A, Dirt A, Sprint F, Mile E, Medium A,'
                .'Long B, Front A, Pace C, Late A, End A.',
        ],
        16 => [
            'umamusume_id' => 8,
            'character_card_id' => 11,
            'turn' => 75,
            'stats' => ['speed' => 767, 'stamina' => 410, 'power' => 769, 'guts' => 324, 'wit' => 253],
            'sp' => null,
            'energy' => null,
            'condition' => null,
            'mood' => null,
            'status' => RunStatus::Completed,
            'skills' => [
                [13, SkillAcquisition::Acquired], // Xceleration (Unique Burst)
                [43, SkillAcquisition::Suggested], // âˆ´win Q.E.D.
                [93, SkillAcquisition::Acquired], // Standard Distance â—‹
                [92, SkillAcquisition::Acquired], // Standard Distance â—Ž
                [153, SkillAcquisition::Suggested], // Straightaway Acceleration
                [155, SkillAcquisition::Suggested],
                [163, SkillAcquisition::Suggested],
                [164, SkillAcquisition::Suggested], // Lay Low
                [174, SkillAcquisition::Acquired], // Nimble Navigator
                [6, SkillAcquisition::Acquired],
                [179, SkillAcquisition::Suggested], // Early Lead
                [182, SkillAcquisition::Suggested],
                [183, SkillAcquisition::Acquired], // Final Push
                [193, SkillAcquisition::Suggested],
                [199, SkillAcquisition::Suggested],
                [212, SkillAcquisition::Acquired], // Updrafters
                [217, SkillAcquisition::Acquired], // Steadfast
                [241, SkillAcquisition::Suggested],
                [263, SkillAcquisition::Acquired], // Shifting Gears
                [300, SkillAcquisition::Suggested], // Front Runner Straightaways â—‹
                [302, SkillAcquisition::Suggested], // Front Runner Corners â—‹
                [365, SkillAcquisition::Acquired], // Tail Held High
                [102, SkillAcquisition::Suggested], // Wet Conditions â—‹
            ],
            'races' => [
                ['slot' => self::URA_FINAL_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 1],
            ],
            'notes' => 'Completed run whose tracker row shows no goals panel; the terminal line "VODKA IS 1ST PLACE" '
                .'is stored as the URA Finale result (1st). Qualifier and Semifinal not recorded. The "Front Runner '
                .'Savvy" row lost the space before its â—‹ glyph (cost 99 matches the â—‹ tier) and resolves to the â—‹ '
                .'row. The tracker also lists "Remove Wet Conditions x N/A âœ…", a skill with no catalog row, left '
                .'unattached. Growth: Speed +20, Power +10. Aptitudes: Turf A, Dirt A, Sprint F, Mile D, Medium A,'
                .'Long B, Front A, Pace C, Late E, End A.',
        ],
        5 => [
            'umamusume_id' => 20,
            'character_card_id' => 21,
            'turn' => 31,
            'stats' => ['speed' => 417, 'stamina' => 212, 'power' => 220, 'guts' => 170, 'wit' => 142],
            'sp' => 99,
            'energy' => 50,
            'condition' => null,
            'mood' => MoodTier::Great->value,
            'status' => RunStatus::Active,
            'skills' => [
                [400, SkillAcquisition::Acquired], // Introduction to Physiology Lvl 1 (Unique Burst)
                [34, SkillAcquisition::Acquired], // Cut and Drive! (received from Legacy)
                [35, SkillAcquisition::Suggested], // Resplendent Red Ace (received from Legacy)
                [93, SkillAcquisition::Suggested], // Standard Distance ◎
                [92, SkillAcquisition::Suggested], // Standard Distance ×
                [7, SkillAcquisition::Suggested], // Professor of Curvature
                [142, SkillAcquisition::Suggested], // Corner Adept ◎
                [143, SkillAcquisition::Suggested], // Corner Adept ×
                [6, SkillAcquisition::Suggested], // Homestretch Haste
                [183, SkillAcquisition::Suggested], // Final Push
                [401, SkillAcquisition::Suggested], // Preferred Position
                [402, SkillAcquisition::Suggested], // Standing By
                [219, SkillAcquisition::Suggested], // Deep Breaths
                [403, SkillAcquisition::Suggested], // Tether
                [404, SkillAcquisition::Suggested], // Murmur
                [405, SkillAcquisition::Acquired], // Strategist
                [406, SkillAcquisition::Suggested], // Front Runner Savvy ◎
                [407, SkillAcquisition::Suggested], // Front Runner Savvy ×
            ],
            'races' => [
                ['slot' => self::JUNIOR_DEBUT_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 1],
            ],
            'notes' => 'Snapshot at CLASSIC EARLY APR (turn 31). Condition --, mood GREAT, energy 50, race day NO, goal '
                .'TOP 5, strategy PACE. Results: Junior Make Debut 1st, Yayoi Sho 1st, Satsuki Sho 1st; Japanese Derby, '
                .'Kikuka Sho, Osaka Hai, Takarazuka Kinen, Tenno Sho (Autumn), Arima Kinen unrun. Growth: Speed +20, '
                .'Stamina +10. Aptitudes: Turf A, Sprint G, Mile A, Medium A, Long B, Front E, Pace A, Late B, End F.',
        ],
        6 => [
            'umamusume_id' => 20,
            'character_card_id' => 21,
            'turn' => 54,
            'stats' => ['speed' => 461, 'stamina' => 257, 'power' => 335, 'guts' => 211, 'wit' => 270],
            'sp' => 151,
            'energy' => 60,
            'condition' => 'PRACTICE POOR ○',
            'mood' => MoodTier::Normal->value,
            'status' => RunStatus::Active,
            'skills' => [
                [408, SkillAcquisition::Acquired], // Introduction to Physiology Lvl 2 (Unique Burst)
                [34, SkillAcquisition::Suggested], // Cut and Drive! (Received from Legacy Spark)
                [93, SkillAcquisition::Acquired], // Standard Distance ◎
                [92, SkillAcquisition::Acquired], // Standard Distance ×
                [409, SkillAcquisition::Suggested], // Inner Post Proficiency ◎
                [139, SkillAcquisition::Suggested], // Long Shot ◎
                [142, SkillAcquisition::Acquired], // Corner Adept ◎
                [410, SkillAcquisition::Suggested], // Go With The Flow
                [6, SkillAcquisition::Suggested], // Homestretch Haste
                [411, SkillAcquisition::Suggested], // Fast-Paced
                [185, SkillAcquisition::Suggested], // Stamina to Spare
                [401, SkillAcquisition::Acquired], // Preferred Position
                [199, SkillAcquisition::Suggested], // Masterful Gambit
                [412, SkillAcquisition::Acquired], // Up-Tempo
                [413, SkillAcquisition::Suggested], // Hesitant Late Surgers
                [403, SkillAcquisition::Suggested], // Tether
            ],
            'races' => [
                ['slot' => self::JUNIOR_DEBUT_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 6],
                ['slot' => self::KIKUKA_SHO_CLASSIC_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 3],
            ],
            'notes' => 'Snapshot at SENIOR YEAR LATE MAR (turn 54). Condition PRACTICE POOR ○, mood NORMAL, energy 60, '
                .'race day YES, goal TOP 3, strategy PACE. Results: Junior Make Debut 6th, Yayoi Sho 5th, Satsuki Sho 5th, '
                .'Japanese Derby 1st, Kikuka Sho 3rd; Osaka Hai, Takarazuka Kinen, Tenno Sho (Autumn), Arima Kinen unrun. '
                .'Growth: Speed +20, Guts +10. Aptitudes: Turf A, Sprint G, Mile C, Medium A, Long B, Front E, Pace A, '
                .'Late A, End F.',
        ],
        7 => [
            'umamusume_id' => 14,
            'character_card_id' => 19,
            'turn' => 33,
            'stats' => ['speed' => 265, 'stamina' => 229, 'power' => 212, 'guts' => 129, 'wit' => 182],
            'sp' => 116,
            'energy' => 40,
            'condition' => 'N/A',
            'mood' => MoodTier::Great->value,
            'status' => RunStatus::Active,
            'skills' => [
                [16, SkillAcquisition::Acquired], // Corazón ☆ Ardiente (Unique Burst)
                [35, SkillAcquisition::Suggested], // Resplendent Red Ace (Received from Legacy)
                [414, SkillAcquisition::Suggested], // Certain Victory (Received from Legacy)
                [415, SkillAcquisition::Suggested], // Fall Runner ◎
                [416, SkillAcquisition::Suggested], // Corner Connoisseur
                [145, SkillAcquisition::Suggested], // Corner Acceleration ◎
                [417, SkillAcquisition::Suggested], // Straightaway Adept
                [418, SkillAcquisition::Suggested], // Breath of Fresh Air
                [155, SkillAcquisition::Suggested], // Straightaway Recovery
                [174, SkillAcquisition::Acquired], // Nimble Navigator
                [179, SkillAcquisition::Acquired], // Early Lead
                [185, SkillAcquisition::Suggested], // Stamina to Spare
                [419, SkillAcquisition::Suggested], // Prepared to Pass
                [420, SkillAcquisition::Acquired], // Hawkeye
                [421, SkillAcquisition::Suggested], // Soft Step
                [311, SkillAcquisition::Acquired], // Pace Chaser Straightaways ◎
                [310, SkillAcquisition::Suggested], // Pace Chaser Straightaways ×
            ],
            'races' => [
                ['slot' => self::JUNIOR_DEBUT_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 1],
            ],
            'notes' => 'Snapshot at CLASSIC EARLY MAY (turn 33). Condition N/A, mood GREAT, energy 40, race day YES, goal '
                .'TOP 5, strategy PACE. Results: Junior Make Debut 1st, Kyodo News Hai 5th, NHK Mile C. 2nd; Japanese Derby, '
                .'Mainichi Okan, Takarazuka Kinen, Japan C., Arima Kinen unrun. Growth: Speed +20, Wit +10. Aptitudes: '
                .'Turf A, Sprint F, Mile A, Dirt B, Medium A, Long B, Front C, Pace A, Late A, End C.',
        ],
        8 => [
            'umamusume_id' => 22,
            'character_card_id' => 23,
            'turn' => 44,
            'stats' => ['speed' => 264, 'stamina' => 350, 'power' => 199, 'guts' => 257, 'wit' => 266],
            'sp' => 40,
            'energy' => 60,
            'condition' => null,
            'mood' => MoodTier::Great->value,
            'status' => RunStatus::Active,
            'skills' => [
                [422, SkillAcquisition::Acquired], // 1st Place Kiss☆ Lvl 2 (Unique Burst)
                [43, SkillAcquisition::Suggested], // ∴ Win Q.E.D. (From Spark (Biwa Hayahide))
                [139, SkillAcquisition::Suggested], // Long Shot ◎
                [7, SkillAcquisition::Suggested], // Professor of Curvature
                [142, SkillAcquisition::Suggested], // Corner Adept ◎
                [155, SkillAcquisition::Acquired], // Straightaway Recovery
                [161, SkillAcquisition::Suggested], // Focus
                [410, SkillAcquisition::Acquired], // Go With The Flow
                [6, SkillAcquisition::Acquired], // Homestretch Haste
                [411, SkillAcquisition::Suggested], // Fast-Paced
                [199, SkillAcquisition::Suggested], // Masterful Gambit
                [219, SkillAcquisition::Suggested], // Deep Breaths
                [423, SkillAcquisition::Suggested], // Trick (Front)
                [424, SkillAcquisition::Suggested], // Frenzied Late Surgers
                [300, SkillAcquisition::Suggested], // Front Runner Straightaways ◎
                [302, SkillAcquisition::Suggested], // Front Runner Corners ◎
                [304, SkillAcquisition::Suggested], // Leader's Pride
                [425, SkillAcquisition::Suggested], // Tactical Tweak
            ],
            'races' => [
                ['slot' => self::JUNIOR_DEBUT_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 1],
                ['slot' => self::KIKUKA_SHO_CLASSIC_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 3],
            ],
            'notes' => 'Snapshot at CLASSIC LATE OCT (turn 44). Condition -, mood GREAT, energy 60, race day YES, goal '
                .'3RD, strategy FRONT. Results: Junior Make Debut 1st, Kikuka Sho 3rd; Arima Kinen unrun. Growth: Stamina '
                .'+20, Guts +10. Aptitudes: Turf A, Sprint D, Mile B, Dirt E, Medium A, Long A, Front A, Pace A, Late B, '
                .'End B.',
        ],
        9 => [
            'umamusume_id' => 22,
            'character_card_id' => 23,
            'turn' => 55,
            'stats' => ['speed' => 496, 'stamina' => 347, 'power' => 388, 'guts' => 249, 'wit' => 164],
            'sp' => 37,
            'energy' => 50,
            'condition' => 'PRACTICE PERFECT ○',
            'mood' => MoodTier::Great->value,
            'status' => RunStatus::Active,
            'skills' => [
                [422, SkillAcquisition::Acquired], // 1st Place Kiss☆ Lvl 2 (Unique Burst)
                [139, SkillAcquisition::Acquired], // Long Shot ◎
                [138, SkillAcquisition::Suggested], // Long Shot ×
                [7, SkillAcquisition::Suggested], // Professor of Curvature
                [142, SkillAcquisition::Suggested], // Corner Adept ◎
                [155, SkillAcquisition::Acquired], // Straightaway Recovery
                [161, SkillAcquisition::Acquired], // Focus
                [6, SkillAcquisition::Acquired], // Homestretch Haste
                [411, SkillAcquisition::Suggested], // Fast-Paced
                [185, SkillAcquisition::Suggested], // Stamina to Spare
                [419, SkillAcquisition::Suggested], // Prepared to Pass
                [199, SkillAcquisition::Suggested], // Masterful Gambit
                [219, SkillAcquisition::Acquired], // Deep Breaths
                [426, SkillAcquisition::Acquired], // Long Corners ◎
                [427, SkillAcquisition::Acquired], // Long Corners ×
                [304, SkillAcquisition::Suggested], // Leader's Pride
            ],
            'races' => [
                ['slot' => self::JUNIOR_DEBUT_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 1],
                ['slot' => self::KIKUKA_SHO_CLASSIC_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 3],
                ['slot' => self::ARIMA_KINEN_SENIOR_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 3],
            ],
            'notes' => 'Snapshot at SENIOR YEAR EARLY APR (turn 55). Condition PRACTICE PERFECT ○, mood GREAT, energy 50, '
                .'race day YES, goal RACE, strategy FRONT. Results: Junior Make Debut 1st, Kikuka Sho 3rd, Arima Kinen 3rd, '
                .'Hanshin Daishoten 2nd; Tenno Sho (Spring), Takarazuka Kinen unrun. Growth: Stamina +20, Wit +10. '
                .'Aptitudes: Turf A, Sprint D, Mile B, Dirt E, Medium A, Long B, Front A, Pace A, Late B, End B.',
        ],
        10 => [
            'umamusume_id' => 22,
            'character_card_id' => 23,
            'turn' => 72,
            'stats' => ['speed' => 296, 'stamina' => 391, 'power' => 271, 'guts' => 243, 'wit' => 191],
            'sp' => 83,
            'energy' => 50,
            'condition' => null,
            'mood' => MoodTier::Great->value,
            'status' => RunStatus::Completed,
            'skills' => [
                [422, SkillAcquisition::Acquired], // 1st Place Kiss☆ (Unique Burst)
                [129, SkillAcquisition::Suggested], // Outer Post Proficiency ◎
                [139, SkillAcquisition::Suggested], // Long Shot ◎
                [7, SkillAcquisition::Suggested], // Professor of Curvature
                [142, SkillAcquisition::Acquired], // Corner Adept ◎
                [155, SkillAcquisition::Suggested], // Straightaway Recovery
                [5, SkillAcquisition::Suggested], // In Body and Mind
                [6, SkillAcquisition::Acquired], // Homestretch Haste
                [185, SkillAcquisition::Suggested], // Stamina to Spare
                [219, SkillAcquisition::Suggested], // Deep Breaths
                [424, SkillAcquisition::Acquired], // Frenzied Late Surgers
                [303, SkillAcquisition::Acquired], // Dodging Danger
                [304, SkillAcquisition::Suggested], // Leader's Pride
                [308, SkillAcquisition::Suggested], // Second Wind
                [318, SkillAcquisition::Suggested], // Hydrate
                [332, SkillAcquisition::Suggested], // A Small Breather
                [365, SkillAcquisition::Suggested], // Tail Held High
            ],
            'races' => [
                ['slot' => self::JUNIOR_DEBUT_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 1],
                ['slot' => self::KIKUKA_SHO_CLASSIC_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 3],
                ['slot' => self::ARIMA_KINEN_CLASSIC_SLOT, 'status' => RaceEntryStatus::Skipped, 'placement' => null],
            ],
            'notes' => 'Career ended at SENIOR YEAR LATE DEC, Arima Kinen (turn 72), where the TOP 3 goal ended DNF '
                .'(9,812 fans left at the finish). The header stage cell reads CLASSIC YEAR LATE OCT BRONZE (turn 44) '
                .'while the last race the goals panel resolves sits on the Arima Kinen at turn 72; the turn is kept at '
                .'72 and the stale stage cell is flagged here, not smoothed. Condition cell prints a dash, stored null. '
                .'Energy 50, race day NO, strategy FRONT. Goals: Junior Make Debut 1st, Earn Fans 10000 done (no party or '
                .'team table exists to store fan targets in, kept in this note), Kikuka Sho 3rd, Arima Kinen DNF; Hanshin '
                .'Daishoten, Tenno Sho (Spring), Takarazuka Kinen unrun. Growth: Stamina +20, Guts +10. Aptitudes: Turf A, '
                .'Dirt E, Sprint D, Mile B, Medium A, Long A, Front A, Pace A, Late B, End B.',
        ],
        12 => [
            'umamusume_id' => 50,
            'character_card_id' => 43,
            'turn' => 69,
            'stats' => ['speed' => 485, 'stamina' => 305, 'power' => 404, 'guts' => 314, 'wit' => 264],
            'sp' => 174,
            'energy' => 20,
            'condition' => 'CHARMING',
            'mood' => MoodTier::Good->value,
            'status' => RunStatus::Completed,
            'skills' => [
                [24, SkillAcquisition::Acquired], // Super-Duper Stoked (Unique Burst)
                [43, SkillAcquisition::Suggested], // ∴win Q.E.D. (received from legacy)
                [102, SkillAcquisition::Acquired], // Wet Conditions ○
                [101, SkillAcquisition::Suggested], // Wet Conditions ◎ (the tracker's tier-two glyph)
                [153, SkillAcquisition::Acquired], // Straightaway Acceleration
                [164, SkillAcquisition::Suggested], // Lay Low
                [6, SkillAcquisition::Acquired], // Homestretch Haste
                [182, SkillAcquisition::Suggested], // Unrestrained
                [183, SkillAcquisition::Suggested], // Final Push
                [185, SkillAcquisition::Suggested], // Stamina to Spare
                [199, SkillAcquisition::Acquired], // Masterful Gambit
                [203, SkillAcquisition::Acquired], // Sprinting Gear
                [224, SkillAcquisition::Suggested], // Trick (Front)
                [318, SkillAcquisition::Suggested], // Hydrate
                [330, SkillAcquisition::Acquired], // 1,500,000 CC
            ],
            'races' => [
                ['slot' => self::JUNIOR_DEBUT_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 2],
                ['slot' => self::JBC_SPRINT_SENIOR_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 4],
                ['slot' => self::ARIMA_KINEN_SENIOR_SLOT, 'status' => RaceEntryStatus::Skipped, 'placement' => null],
            ],
            'notes' => 'Career ended: snapshot on the SENIOR YEAR EARLY NOV JBC Sprint race day (turn 69) with the goal '
                .'cell reading MUST 1ST, which the panel answers 4th; the Senior Late December Arima Kinen is recorded '
                .'DNF and closes the career. Goals otherwise: Junior Make Debut 2nd, Earn Fans 5000, 9000 and 12000 all '
                .'done (no party or team table exists to store fan targets in, kept in this note), Negishi S. 1st, '
                .'February S. 2nd, Elm S. 1st; those three races have no race_catalog_slots row on this tree, so their '
                .'results stay in this note. The "Flustered End Corners" row names a skill the catalog does not carry '
                .'(the Global roster has Flustered End Closers) and is left unattached. Condition CHARMING, mood GOOD, '
                .'energy 20, strategy LATE. Growth: Guts +20. Aptitudes: Turf D, Dirt A, Sprint A, Mile A, Medium G, Long '
                .'G, Front G, Pace G, Late A, End B.',
        ],
        13 => [
            'umamusume_id' => 2,
            'character_card_id' => 24,
            'turn' => 56,
            'stats' => ['speed' => 345, 'stamina' => 395, 'power' => 256, 'guts' => 252, 'wit' => 303],
            'sp' => 38,
            'energy' => 30,
            'condition' => 'PRACTICE PERFECT ○',
            'mood' => MoodTier::Normal->value,
            'status' => RunStatus::Completed,
            'skills' => [
                // No skills acquired; Corner Connoisseur 342 and Frenzied End Closers 91 not acquired.
            ],
            'races' => [
                ['slot' => self::JUNIOR_DEBUT_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 1],
                ['slot' => self::KIKUKA_SHO_CLASSIC_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 1],
                ['slot' => self::ARIMA_KINEN_CLASSIC_SLOT, 'status' => RaceEntryStatus::Skipped, 'placement' => null],
            ],
            'notes' => 'Completed run at SENIOR YEAR LATE APR, Tenno Sho (Spring) (turn 56). Condition PRACTICE PERFECT '
                .'○, mood NORMAL, energy 30, race day YES, goal TOP 3, strategy PACE. Results: Junior Make Debut 1st, '
                .'Wakagoma S. 1st, Satsuki Sho 3rd, Japanese Derby 1st, Kikuka Sho 1st, Tenno Sho (Spring) 4th; Japan C. '
                .'DNF, Arima Kinen DNF. Growth: Speed +10, Stamina +10, Guts +10. Aptitudes: Turf A, Sprint F, Mile D, '
                .'Dirt G, Medium A, Long A, Front C, Pace A, Late C, End E.',
        ],
        17 => [
            'umamusume_id' => 25,
            'character_card_id' => 26,
            'turn' => 56,
            'stats' => ['speed' => 396, 'stamina' => 340, 'power' => 368, 'guts' => 400, 'wit' => 254],
            'sp' => 17,
            'energy' => 20,
            'condition' => 'HOT TOPIC',
            'mood' => MoodTier::Good->value,
            'status' => RunStatus::Active,
            'skills' => [
                [43, SkillAcquisition::Acquired], // ∴ Win Q.E.D. Lvl 2 (Unique)
                [102, SkillAcquisition::Acquired], // Wet Conditions ◎ (mangled tier glyph)
                [101, SkillAcquisition::Suggested], // Wet Conditions × (mangled tier glyph)
                [428, SkillAcquisition::Suggested], // Outer Post Proficiency ()
                [429, SkillAcquisition::Acquired], // Straightaway Acceleration
                [430, SkillAcquisition::Suggested], // In Body and Mind
                [6, SkillAcquisition::Acquired], // Homestretch Haste
                [431, SkillAcquisition::Suggested], // Unrestrained
                [432, SkillAcquisition::Suggested], // Final Push
                [433, SkillAcquisition::Suggested], // Stamina to Spare
                [434, SkillAcquisition::Suggested], // Masterful Gambit
                [435, SkillAcquisition::Suggested], // Up-Tempo
                [436, SkillAcquisition::Suggested], // Deep Breaths
                [437, SkillAcquisition::Suggested], // Flustered End Closers
                [438, SkillAcquisition::Suggested], // Hesitant End Closers
                [439, SkillAcquisition::Acquired], // Tactical Tweak
            ],
            'races' => [],
            'notes' => 'Snapshot at SENIOR YEAR LATE APR, Tenno Sho (Spring) (turn 56). Condition HOT TOPIC, mood GOOD, '
                .'energy 20, race day YES, goal TOP 3, strategy --. No race result; the goals panel records only GOAL TOP '
                .'3, RACE DAY? YES. Growth: Wit +20, Guts +10. Aptitudes: Turf A, Sprint F, Mile B, Medium A, Long A, '
                .'Front E, Pace A, Late B, End E.',
        ],
    ];

    public function run(): void
    {
        foreach (self::RUNS as $label => $run) {
            $this->importRun((string) $label, $run);
        }
    }

    /**
     * Writes one tracker run keyed on its import_source label, skipping a run that
     * already carries it.
     *
     * Public for the same reason RUNS is: `TrackerVeteransSeeder` files the five
     * completed careers this table defines without duplicating the write path, so
     * one import body is maintained here.
     *
     * @param  array{umamusume_id: int, character_card_id: int, turn: int, stats: array{speed: int, stamina: int, power: int, guts: int, wit: int}, sp: int|null, energy: int|null, condition: string|null, mood: string|null, status: RunStatus, skills: list<array{0: int, 1: SkillAcquisition}>, races: list<array{slot: int, status: RaceEntryStatus, placement: int|null}>, notes: string}  $run
     */
    public function importRun(string $label, array $run): void
    {
        $source = self::SOURCE_FILE.' (Run '.$label.')';

        if (TrainingRun::where('import_source', $source)->exists()) {
            return;
        }

        $model = app(ImportHistoricalRun::class)->handle(
            [
                'umamusume_id' => $run['umamusume_id'],
                'character_card_id' => $run['character_card_id'],
                'scenario' => 'ura_finale',
                'status' => $run['status']->value,
                'notes' => $run['notes'],
            ],
            [
                [
                    'turn' => $run['turn'],
                    'speed' => $run['stats']['speed'],
                    'stamina' => $run['stats']['stamina'],
                    'power' => $run['stats']['power'],
                    'guts' => $run['stats']['guts'],
                    'wit' => $run['stats']['wit'],
                    'sp' => $run['sp'] === null ? null : (string) $run['sp'],
                    'condition' => $run['condition'],
                    'energy' => $run['energy'] === null ? null : (string) $run['energy'],
                    'mood' => $run['mood'],
                    'fans' => null,
                ],
            ],
            $source
        );

        // The position the Career Stage header fixes; derived from the turn number so
        // the row and the calendar cannot disagree. The Finale block (turn 73-75)
        // sits outside the 1-72 grid, so those runs keep career_position null and
        // derive from max(turn) like any other run without a stored position.
        if ($run['turn'] <= CareerCalendar::TURNS_PER_CAREER) {
            $position = CareerPosition::fromTurnIndex($run['turn']);
            $model->update([
                'career_position' => $position,
                'career_position_source' => 'imported',
            ]);
        } else {
            $model->update([
                'career_position_source' => 'imported',
            ]);
        }

        foreach ($run['races'] as $race) {
            $model->raceEntries()->create([
                'race_catalog_slot_id' => $race['slot'],
                'status' => $race['status']->value,
                'placement' => $race['placement'],
            ]);
        }

        foreach ($run['skills'] as [$skillId, $skillStatus]) {
            $model->skills()->syncWithoutDetaching([
                $skillId => [
                    'status' => $skillStatus->value,
                    'turn_acquired' => null,
                ],
            ]);
        }
    }
}
