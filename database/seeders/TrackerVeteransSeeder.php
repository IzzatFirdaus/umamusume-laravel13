<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\RecordVeteran;
use App\Enums\MoodTier;
use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Enums\SkillAcquisition;
use App\Models\TrainingRun;
use App\Models\Veteran;
use Illuminate\Database\Seeder;

/**
 * Files the careers that ended in the committed tracker as Veterans (PRD FR-G-1,
 * ADR-0020 §3).
 *
 * Routing is on the tracker's "Status / result" column, not on presence in the file:
 * runs 3, 4, 10, 12, 14, 15 and 16 print a career end (a finale finish, a won URA
 * Final, or an Arima Kinen DNF that closes the career), so each lands as one
 * training_runs row with status Completed plus the veterans row that puts it in the
 * library. The ten in-progress and race-plan-only snapshots (runs 1, 2, 5 to 9, 11,
 * 13, 17) have no graduation record and are deliberately untouched: no run row, no
 * Veteran row.
 *
 * The five runs the URA import already defines (3, 4, 14, 15, 16) are read from
 * UraFinaleRunsSeeder::RUNS and written through that seeder's own import path, so
 * the two seeders share one writer and one set of catalog ids rather than drifting
 * apart. Runs 10 and 12 were never imported anywhere; their career-end snapshots are
 * defined here in the same shape, keyed on the same label format so a run that
 * exists from either importer is never created twice.
 *
 * **The Legacy bucket is empty, and that is the finding, not an omission.** The
 * prompt expected runs 14 to 16 to expose an ancestry tree. They do not: the tracker
 * prints no Legacy Select block anywhere in the file (a search for parent, ancestor,
 * grandparent, spark-star or affinity lines over docs/Umamusume_Progress_Tracker_Runs.md
 * answers nothing for all seventeen runs), and the one ancestor the file names, Biwa
 * Hayahide as a Spark source on run 8, sits in a race-plan-only snapshot this seeder
 * does not import. Following the task's own rule, no diagram is built where the
 * source is silent: legacy_selection stays null on every Veteran run (null is the
 * "never opened that screen" disclosure `TrainingRun::legacySelection()` documents;
 * an empty payload would claim a screen state the client cannot reach), and each
 * Veteran row carries the gap in its notes.
 *
 * Idempotent two ways, on the same key the URA import uses: a run whose
 * import_source label already exists is not re-created, and a completed run that
 * already has a library row is not filed again, so re-running adds nothing and
 * touches nothing. The seeder is not registered in DatabaseSeeder: a fresh install
 * does not silently write Trainer history.
 *
 * @see docs/imports/ura-finale-runs.md for the shared import mapping and skill-tier rules.
 */
class TrackerVeteransSeeder extends Seeder
{
    private const string SOURCE_FILE = 'docs/Umamusume_Progress_Tracker_Runs.md';

    /**
     * Tracker runs whose snapshot shows the career ended, in file order.
     *
     * @var list<int>
     */
    private const array VETERAN_LABELS = [3, 4, 10, 12, 14, 15, 16];

    /**
     * Catalog ids for the race days runs 10 and 12 resolved. The debut and the
     * finale slots are the ids UraFinaleRunsSeeder already pins; these four are
     * read from the committed race catalogue on this tree.
     */
    private const int JUNIOR_DEBUT_SLOT = 1;

    private const int KIKUKA_SHO_CLASSIC_SLOT = 180;

    private const int ARIMA_KINEN_CLASSIC_SLOT = 215;

    private const int JBC_SPRINT_SENIOR_SLOT = 375;

    private const int ARIMA_KINEN_SENIOR_SLOT = 400;

    /**
     * The two career-end snapshots the URA import does not carry, in the same shape
     * as UraFinaleRunsSeeder::RUNS so one import path writes both.
     *
     * @var array<int, array{umamusume_id: int, character_card_id: int, turn: int, stats: array{speed: int, stamina: int, power: int, guts: int, wit: int}, sp: int|null, energy: int|null, condition: string|null, mood: string|null, status: RunStatus, skills: list<array{0: int, 1: SkillAcquisition}>, races: list<array{slot: int, status: RaceEntryStatus, placement: int|null}>, notes: string}>
     */
    public const array CAREER_END_RUNS = [
        10 => [
            'umamusume_id' => 24,
            'character_card_id' => 29,
            'turn' => 48,
            'stats' => ['speed' => 296, 'stamina' => 391, 'power' => 271, 'guts' => 243, 'wit' => 191],
            'sp' => 83,
            'energy' => 5,
            'condition' => null,
            'mood' => MoodTier::Great->value,
            'status' => RunStatus::Completed,
            'skills' => [
                [18, SkillAcquisition::Acquired], // 1st Place Kiss☆ (Unique Burst)
                [129, SkillAcquisition::Suggested], // Outer Post Proficiency ○
                [139, SkillAcquisition::Suggested], // Long Shot ○
                [7, SkillAcquisition::Suggested], // Professor of Curvature
                [142, SkillAcquisition::Acquired], // Corner Adept ○
                [155, SkillAcquisition::Suggested], // Straightaway Recovery
                [5, SkillAcquisition::Suggested], // In Body and Mind
                [6, SkillAcquisition::Acquired], // Homestretch Haste
                [185, SkillAcquisition::Suggested], // Stamina to Spare
                [219, SkillAcquisition::Suggested], // Deep Breaths
                [228, SkillAcquisition::Acquired], // Frenzied Late Surgers
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
            'notes' => 'Career ended: the plan cell reads END CAREER at the Classic Late December Arima Kinen '
                .'(9,812 fans left at the finish) where the TOP 3 goal ended DNF. The header stage cell reads CLASSIC '
                .'YEAR LATE OCT BRONZE (turn 44) while the last race the goals panel resolves sits on the Classic '
                .'Arima Kinen at turn 48; the turn is kept at 48 and the stale stage cell is flagged here, not '
                .'smoothed. Goals: Junior Make Debut 1st, Earn Fans 10000 done (no party or team table exists to '
                .'store fan targets in, kept in this note), Kikuka Sho 3rd, Arima Kinen DNF; Hanshin Daishoten, '
                .'Tenno Sho (Spring), Takarazuka Kinen and the two Senior goals unrun. Condition cell prints a dash, '
                .'stored null. Energy 5, race day no, strategy FRONT. Growth: Stamina +20, Guts +10. Aptitudes: '
                .'Turf A, Dirt E, Sprint D, Mile B, Medium A, Long A, Front A, Pace A, Late B, End B. No Legacy '
                .'Select block on this page: parents, grandparents, Sparks and stars unrecorded.',
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
            'notes' => 'Career ended: snapshot on the Senior Early November JBC Sprint race day (turn 69) with the '
                .'goal cell reading MUST 1ST, which the panel answers 4th; the Senior Late December Arima Kinen is '
                .'recorded DNF and closes the career. Goals otherwise: Junior Make Debut 2nd, Earn Fans 5000, 9000 '
                .'and 12000 all done (no party or team table exists to store fan targets in, kept in this note), '
                .'Negishi S. 1st, February S. 2nd, Elm S. 1st; those three races have no race_catalog_slots row on '
                .'this tree, so their results stay in this note. The "Flustered End Corners" row names a skill the '
                .'catalog does not carry (the Global roster has Flustered End Closers) and is left unattached. '
                .'Condition CHARMING, mood GOOD, energy 20, strategy LATE. Growth: Guts +20. Aptitudes: Turf D, '
                .'Dirt A, Sprint A, Mile A, Medium G, Long G, Front G, Pace G, Late A, End B. No Legacy Select '
                .'block on this page: parents, grandparents, Sparks and stars unrecorded.',
        ],
    ];

    /**
     * What ended each career, the library row's first sentence.
     *
     * @var array<int, string>
     */
    private const array CAREER_OUTCOMES = [
        3 => 'Career ended, URA Finale Finals won 1st (Qualifier, Semifinal and Final all 1st).',
        4 => 'Career ended, URA Finale Final DNF after a Qualifier 1st and a Semifinal 2nd.',
        10 => 'Career ended at the Classic Arima Kinen, DNF.',
        12 => 'Career ended at the Senior Arima Kinen, DNF, after the JBC Sprint came 4th.',
        14 => 'Career ended, URA Finale Finals 2nd place.',
        15 => 'Career ended, URA Finale Finals won 1st.',
        16 => 'Career finished, Vodka 1st place.',
    ];

    /**
     * The gap this seeder records rather than fills: no run in the tracker carries a
     * Legacy Select read-back, so the diagram stays empty for all of them.
     */
    private const string LEGACY_GAP = 'Legacy ancestry unrecorded: the tracker prints no Legacy Select block for '
        .'this run, so the two parents, the four grandparents, their Sparks and their star ranks are unknown and '
        .'the diagram is left empty.';

    /**
     * The seven Veteran definitions, label keyed: the five the URA import defines
     * and the two career-end snapshots defined here.
     *
     * Public so the test can materialise the FK prerequisites (umamusume, cards,
     * skills, race slots) straight from the merged table instead of duplicating ids.
     *
     * @return array<int, array{umamusume_id: int, character_card_id: int, turn: int, stats: array{speed: int, stamina: int, power: int, guts: int, wit: int}, sp: int|null, energy: int|null, condition: string|null, mood: string|null, status: RunStatus, skills: list<array{0: int, 1: SkillAcquisition}>, races: list<array{slot: int, status: RaceEntryStatus, placement: int|null}>, notes: string}>
     */
    public static function veteranDefinitions(): array
    {
        $merged = [];

        foreach (self::VETERAN_LABELS as $label) {
            /** @var array{umamusume_id: int, character_card_id: int, turn: int, stats: array{speed: int, stamina: int, power: int, guts: int, wit: int}, sp: int|null, energy: int|null, condition: string|null, mood: string|null, status: RunStatus, skills: list<array{0: int, 1: SkillAcquisition}>, races: list<array{slot: int, status: RaceEntryStatus, placement: int|null}>, notes: string} $definition */
            $definition = self::CAREER_END_RUNS[$label] ?? UraFinaleRunsSeeder::RUNS[$label];

            $merged[$label] = $definition;
        }

        return $merged;
    }

    public function run(): void
    {
        $ura = new UraFinaleRunsSeeder;
        $definitions = self::veteranDefinitions();

        foreach ($definitions as $label => $definition) {
            // Shared write path: creates the run through ImportHistoricalRun with its
            // one snapshot turn, races and skills, and skips a run whose label already
            // exists from either seeder.
            $ura->importRun((string) $label, $definition);

            $run = TrainingRun::where('import_source', self::SOURCE_FILE.' (Run '.$label.')')->sole();

            if (Veteran::where('training_run_id', $run->id)->exists()) {
                continue;
            }

            // The label is the same key the run side uses, seen one table over: this
            // row was filed from that tracker run, and a re-run finds it here first.
            app(RecordVeteran::class)->handle($run, [], $this->veteranNote($label));
        }
    }

    private function veteranNote(int $label): string
    {
        return self::CAREER_OUTCOMES[$label].' '.self::LEGACY_GAP;
    }
}
