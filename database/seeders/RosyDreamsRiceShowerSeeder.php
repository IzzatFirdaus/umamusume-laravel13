<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\ImportHistoricalRun;
use App\Domain\Career\CareerPosition;
use App\Enums\CareerPhase;
use App\Enums\CareerYear;
use App\Enums\MoodTier;
use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Enums\SkillAcquisition;
use App\Models\TrainingRun;
use App\Services\CareerCalendar;
use Illuminate\Database\Seeder;

/**
 * Imports the one mid-career Unity Cup run recorded in the committed client-frame report
 * (docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md, 68 `[Global]` frames captured 2026-10-03).
 *
 * Where the URA seed reads a tracker export of finished careers, this reads a career standing in the
 * middle of itself: Senior Early October, turn 67 of 72, with the finals five turns away. That is why
 * it carries the fields the URA rows leave null — the stored `career_position`, the team tally, the
 * per-stat caps and the growth row — because the report's whole subject is the state a Trainer can
 * read off the client partway through.
 *
 * Idempotent by `import_source`, and additive: a run already carrying the label is skipped, so
 * re-running does not duplicate rows.
 *
 * What is deliberately absent, and why:
 *  - `energy` is null. §1.7 states the client never prints an Energy number, only a bar and a warning,
 *    so any figure here would be invented. The bar's low band is carried in `notes` as words instead.
 *  - `condition` is null. The frames give the race going (Soft) and the stablehand's verdict, not the
 *    turn's condition tile.
 *  - `current_objective_index` is null. The report lists three cleared goals and one active one; it
 *    does not number them, so the index is unknown rather than implied.
 *  - Wit's 1800 cap has no column. `training_runs` holds `cap_speed` through `cap_guts` only, so the
 *    fifth figure stays in the report and out of the row.
 *  - The six-card deck (§1.4) is not imported. `ImportHistoricalRun` writes no deck slots, the same
 *    omission the URA seed records in its own docblock.
 *
 * @see UraFinaleRunsSeeder for the shared write path this mirrors
 */
class RosyDreamsRiceShowerSeeder extends Seeder
{
    private const string SOURCE_FILE = 'docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md';

    private const string RUN_LABEL = 'Rosy Dreams Rice Shower';

    /**
     * Catalog ids the report names, resolved against the seeded rows.
     *
     * Trainee 30 is Rice Shower and card 33 is her `[Rosy Dreams]` entry; the eleven skills are the
     * ten chips on her Details screen plus Corner Recovery ○, which the Learn screen marks Obtained
     * past the visible list (§1.3). The race slots are the year-3 (Senior) instances of the five
     * races the report names, so the goal list and the calendar agree on the same rows.
     */
    private const int UMAMUSUME_ID = 30;

    private const int CHARACTER_CARD_ID = 33;

    /**
     * Senior Early October, the moment the report reads off the client. The turn index is derived
     * from this triple through `CareerCalendar` rather than typed, so the row and the calendar
     * cannot disagree — 67 of 72, which is also the report's own "5 turns left".
     */
    private const int TURN_MONTH = 10;

    private const int NIKKEI_SHO_SLOT = 264;

    private const int TENNO_SHO_SPRING_SLOT = 280;

    private const int TAKARAZUKA_KINEN_SLOT = 310;

    private const int KYOTO_DAISHOTEN_SLOT = 355;

    private const int ARIMA_KINEN_SLOT = 400;

    /**
     * The live stat line (§1.2), the unspent Skill Points, and the mood the client spells GREAT.
     *
     * @var array{speed: int, stamina: int, power: int, guts: int, wit: int}
     */
    private const array STATS = [
        'speed' => 607,
        'stamina' => 577,
        'power' => 549,
        'guts' => 736,
        'wit' => 397,
    ];

    /**
     * Owned skills, all ten visible chips plus the eleventh the Learn screen confirms (§1.3).
     *
     * @var list<array{0: int, 1: SkillAcquisition}>
     */
    private const array SKILLS = [
        [47, SkillAcquisition::Acquired],   // Blue Rose Closer (unique, Lvl 3)
        [31, SkillAcquisition::Acquired],   // Red Shift/LP1211-M
        [142, SkillAcquisition::Acquired],  // Corner Adept ○
        [147, SkillAcquisition::Acquired],  // Swinging Maestro
        [174, SkillAcquisition::Acquired],  // Nimble Navigator
        [217, SkillAcquisition::Acquired],  // Steadfast
        [219, SkillAcquisition::Acquired],  // Deep Breaths
        [223, SkillAcquisition::Acquired],  // Extra Tank
        [311, SkillAcquisition::Acquired],  // Pace Chaser Straightaways ○
        [315, SkillAcquisition::Acquired],  // Shrewd Step
        [148, SkillAcquisition::Acquired],  // Corner Recovery ○ (Learn screen, past the visible ten)
    ];

    /**
     * The three cleared goals, the race scheduled on this turn, and the active Arima Kinen goal.
     *
     * Two of the cleared goals read "top 3" in the report rather than a place, so their placement is
     * an absence; only Tenno Sho (Spring) is stated as 1st (§1.6).
     *
     * @var list<array{slot: int, status: RaceEntryStatus, placement: int|null}>
     */
    private const array RACES = [
        ['slot' => self::NIKKEI_SHO_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => null],
        ['slot' => self::TENNO_SHO_SPRING_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => 1],
        ['slot' => self::TAKARAZUKA_KINEN_SLOT, 'status' => RaceEntryStatus::Completed, 'placement' => null],
        ['slot' => self::KYOTO_DAISHOTEN_SLOT, 'status' => RaceEntryStatus::Entered, 'placement' => null],
        ['slot' => self::ARIMA_KINEN_SLOT, 'status' => RaceEntryStatus::Entered, 'placement' => null],
    ];

    public function run(): void
    {
        $source = self::SOURCE_FILE.' (Run '.self::RUN_LABEL.')';

        if (TrainingRun::where('import_source', $source)->exists()) {
            return;
        }

        // The calendar yields a turn index from a position and the position needs that index, so a
        // placeholder turn is passed once to read the real one back; the constructor only bounds it.
        $turn = CareerCalendar::positionToTurnIndex(
            new CareerPosition(CareerYear::Senior, self::TURN_MONTH, CareerPhase::Early, 1),
        );

        $position = new CareerPosition(
            year: CareerYear::Senior,
            month: self::TURN_MONTH,
            phase: CareerPhase::Early,
            turnIndex: $turn,
            // "5 turns left, 5 turns until the Unity Cup" — the countdown the client prints (§1.1).
            scenarioCountdown: 5,
        );

        $model = app(ImportHistoricalRun::class)->handle(
            [
                'umamusume_id' => self::UMAMUSUME_ID,
                'character_card_id' => self::CHARACTER_CARD_ID,
                'scenario' => 'unity_cup',
                'status' => RunStatus::Active->value,
                'notes' => 'Unity Cup, [Rosy Dreams] Rice Shower, read off 68 client frames on 2026-10-03. '
                    .'Senior Year Early October, turn 67 of 72, five turns until the finals. Fans 209,245 '
                    .'against a Top Star ladder of 240,000. Energy reads low on the bar and the client is '
                    .'printing its injury warning, but no number is shown, so none is stored. Team Blue Bloom '
                    .'sits at Rank S with 55 Unity Trainings and 11 combined bursts.',
            ],
            [
                [
                    'turn' => $position->turnIndex,
                    'speed' => self::STATS['speed'],
                    'stamina' => self::STATS['stamina'],
                    'power' => self::STATS['power'],
                    'guts' => self::STATS['guts'],
                    'wit' => self::STATS['wit'],
                    'sp' => '173',
                    'condition' => null,
                    'energy' => null,
                    'mood' => MoodTier::Great->value,
                    'fans' => '209245',
                ],
            ],
            $source
        );

        // The columns ImportHistoricalRun does not touch: the position the report is organised around,
        // and the Unity Cup team state §1.8 reads off the Team Info panel.
        $model->update([
            'career_position' => $position,
            'career_position_source' => 'imported',
            'trainee_rarity' => 3,
            'potential_level' => 2,
            'growth_rate' => ['speed' => 0, 'stamina' => 10, 'power' => 0, 'guts' => 20, 'wit' => 0],
            'cap_speed' => 1325,
            'cap_stamina' => 1322,
            'cap_power' => 1368,
            'cap_guts' => 1308,
            'team_name' => 'Blue Bloom',
            'team_motto' => 'Dreaming Big',
            'team_league_placement' => 8,
            'team_preseason_wins' => 4,
            'unity_trainings_count' => 55,
            'spirit_bursts_count' => 6,
            'extreme_bursts_count' => 5,
        ]);

        foreach (self::RACES as $race) {
            $model->raceEntries()->create([
                'race_catalog_slot_id' => $race['slot'],
                'status' => $race['status']->value,
                'placement' => $race['placement'],
            ]);
        }

        foreach (self::SKILLS as [$skillId, $skillStatus]) {
            $model->skills()->syncWithoutDetaching([
                $skillId => [
                    'status' => $skillStatus->value,
                    'turn_acquired' => null,
                ],
            ]);
        }
    }
}
