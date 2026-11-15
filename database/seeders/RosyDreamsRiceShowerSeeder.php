<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\ImportHistoricalRun;
use App\Domain\Career\CareerPosition;
use App\Enums\CareerPhase;
use App\Enums\CareerYear;
use App\Enums\MoodTier;
use App\Enums\RaceEntryStatus;
use App\Enums\RunMode;
use App\Enums\RunStatus;
use App\Enums\SkillAcquisition;
use App\Enums\TurnEventType;
use App\Models\Legacy\LegacySelectionPayload;
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
 *
 * Re-run behaviour (updated 2026-11-15): idempotency now refreshes instead of skipping. A run already
 * carrying the `import_source` label is deleted together with its deck slots, events, turn entries,
 * race entries and skill pivots, and re-imported whole — the audit that motivated the deck and legacy
 * imports needs a repeatable seed, not a first-run-only one. Nothing outside the run's own rows is
 * touched, and the shared dev database was seeded from an explicit owner instruction (EX-12b) before
 * this path existed; on that file the delete-and-reseed is a no-op save for run #14 itself.
 *
 * Corrections of 2026-11-15, each verified before this file was edited (the audit record is
 * `docs/audits/browser-observation-2026-11-15-followup/05-seeder-patch-review.md`):
 *  - `CHARACTER_CARD_ID` is 31, not 33. Card 33 is Winning Ticket's `[Get to Winning!]`; card 31 is
 *    Rice Shower's `[Rosy Dreams]` (`umamusume_id` 30, matching this run's trainee).
 *  - The six-card deck (§1.4) is imported. `ImportHistoricalRun` writes no deck slots, so the seeder
 *    writes them itself; `support_cards.id` is the foreign key (250, 263, 265, 266, 267, 277), not the
 *    GameTora `support_id` values the report cites (30003, 30016, 30018, 30019, 30020, 30030).
 *  - The Legacy Select read-back (§1.5) is imported into `legacy_selection`, shaped exactly as
 *    `LegacySelectionPayload` validates it (`{legacies, affinity}`), and the two parent identities go
 *    to `inheritance_parent_a_id`/`_b_id` alongside it (`ADR-0010`).
 *  - Two event rows are seeded at turn 67, the run's only recorded turn, with `TurnEventType` values —
 *    events on turns that no `turn_entries` row records would manufacture a timeline the run did not
 *    have.
 *  - `team_rank` is written as `S` per §1.8's Team Info panel (`S (five stars)`), added by R6-48's
 *    2026-11-15 owner ruling after the column landed (`2026_11_15_143000`); before that the rank had
 *    no column and the Cockpit read "not recorded".
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
     * Trainee 30 is Rice Shower and card 31 is her `[Rosy Dreams]` entry (card 33 is Winning
     * Ticket's `[Get to Winning!]`, the mis-assignment the 2026-11-15 audit corrected); the eleven
     * skills are the ten chips on her Details screen plus Corner Recovery ○, which the Learn screen
     * marks Obtained past the visible list (§1.3). The race slots are the year-3 (Senior) instances
     * of the five races the report names, so the goal list and the calendar agree on the same rows.
     */
    private const int UMAMUSUME_ID = 30;

    private const int CHARACTER_CARD_ID = 31;

    /**
     * The deck §1.4 reads off the six Featured Cards panels, keyed by slot position.
     *
     * `support_cards.id` is the foreign key; the GameTora `support_id` values in the comment are the
     * report's own row numbers and resolve to the ids beside them. Ownership uses
     * `DeckSlot::OWNERSHIP`'s exact strings, and slot 6 is the client's Friends slot — §1.4: the
     * Career Profile rail tags Super Creek's entry with a "Friends" pill, which is why the only
     * 50/50 card in the deck is the one borrowed from another account. Client levels are per §1.4's
     * own column.
     *
     * @var array<int, array{support_id: int, card_id: int, ownership: string, level: int|null}>
     */
    private const array DECK = [
        1 => ['support_id' => 30003, 'card_id' => 250, 'ownership' => 'OWNED', 'level' => 35],  // Tokai Teio [Dream Big!]
        2 => ['support_id' => 30018, 'card_id' => 265, 'ownership' => 'OWNED', 'level' => 30],  // Nishino Flower [Even the Littlest Bud]
        3 => ['support_id' => 30020, 'card_id' => 267, 'ownership' => 'OWNED', 'level' => 30],  // Biko Pegasus [Double Carrot Punch!]
        4 => ['support_id' => 30016, 'card_id' => 263, 'ownership' => 'RENTED', 'level' => 50], // Super Creek [Piece of Mind] — the Friends slot
        5 => ['support_id' => 30030, 'card_id' => 277, 'ownership' => 'OWNED', 'level' => 30],  // Matikanetannhauser [Just Keep Going]
        6 => ['support_id' => 30019, 'card_id' => 266, 'ownership' => 'OWNED', 'level' => 35],  // Haru Urara [Urara's Day Off!]
    ];

    /**
     * The two library rows §1.5's tree names as the parents, as `umamusume.id` values for
     * `inheritance_parent_a_id`/`_b_id` (`ADR-0010`). The payload itself is built in `run()` from the
     * per-legacy records below, because the spark lists are per-node and read better beside the tree.
     *
     * @var array{a: int, b: int}
     */
    private const array LEGACY_PARENT_IDS = ['a' => 8, 'b' => 4]; // Vodka, Maruzensky

    /**
     * The ancestry step 4 stores, fully transcribed from §1.5's tree and its Sparks-panel table,
     * shaped exactly as `LegacySelectionPayload::KEYS`/`LEGACY_KEYS`/`SPARK_KEYS` validate it:
     * `{legacies, affinity}`, each legacy `{rank (nullable int), is_guest, ancestors (names), sparks}`,
     * with the optional companions the report actually read — `rank_letter`, `name`,
     * `ancestors_sparks`/`ancestors_rank_letters` aligned by index with `ancestors`.
     *
     * Every Spark §1.5 lists is carried: Blue/Pink/Green/White on each parent node, and
     * Blue/Pink/Green/White on each grandparent row under `ancestors_sparks`. White sparks are the
     * competition/skill rows the previous draft dropped entirely (Vodka six, Maruzensky six on the
     * parents; four/three/seven/thirteen across the grandparent rows). The report records no
     * affinity grade for the pair (§1.5 prints none), so `affinity` is null rather than invented.
     *
     * @return array{legacies: list<array<string, mixed>>, affinity: null}
     */
    private static function legacySelection(): array
    {
        return [
            'legacies' => [
                [
                    'rank' => null,
                    'rank_letter' => 'B+',
                    'name' => 'Vodka [Wild Top Gear]',
                    'is_guest' => false,
                    'ancestors' => ['Daiwa Scarlet', 'Seiun Sky'],
                    'ancestors_rank_letters' => ['B+', 'A'],
                    'sparks' => [
                        ['kind' => 'blue', 'target' => 'Power', 'stars' => 1],
                        ['kind' => 'pink', 'target' => 'Late Surger', 'stars' => 2],
                        ['kind' => 'green', 'target' => 'Cut and Drive!', 'stars' => 2],
                        ['kind' => 'white', 'target' => 'Oka Sho', 'stars' => 1],
                        ['kind' => 'white', 'target' => 'Victoria Mile', 'stars' => 1],
                        ['kind' => 'white', 'target' => 'Shuka Sho', 'stars' => 2],
                        ['kind' => 'white', 'target' => 'Japan C.', 'stars' => 2],
                        ['kind' => 'white', 'target' => 'Extra Tank', 'stars' => 2],
                        ['kind' => 'white', 'target' => 'Soft Step', 'stars' => 3],
                    ],
                    'ancestors_sparks' => [
                        // Daiwa Scarlet
                        [
                            ['kind' => 'blue', 'target' => 'Guts', 'stars' => 1],
                            ['kind' => 'pink', 'target' => 'Mile', 'stars' => 1],
                            ['kind' => 'green', 'target' => 'Resplendent Red Ace', 'stars' => 1],
                            ['kind' => 'white', 'target' => 'Osaka Hai', 'stars' => 2],
                            ['kind' => 'white', 'target' => 'Tenno Sho (Autumn)', 'stars' => 1],
                            ['kind' => 'white', 'target' => 'Competitive Spirit', 'stars' => 3],
                            ['kind' => 'white', 'target' => 'Pace Chaser Straightaways', 'stars' => 2],
                        ],
                        // Seiun Sky
                        [
                            ['kind' => 'blue', 'target' => 'Stamina', 'stars' => 3],
                            ['kind' => 'pink', 'target' => 'Turf', 'stars' => 2],
                            ['kind' => 'green', 'target' => 'Angling and Scheming', 'stars' => 2],
                            ['kind' => 'white', 'target' => 'Kikuka Sho', 'stars' => 1],
                            ['kind' => 'white', 'target' => 'Corner Recovery', 'stars' => 2],
                            ['kind' => 'white', 'target' => 'URA Finale', 'stars' => 1],
                        ],
                    ],
                ],
                [
                    'rank' => null,
                    'rank_letter' => 'UG',
                    'name' => 'Maruzensky [Formula R]',
                    'is_guest' => true,
                    'ancestors' => ['Daiwa Scarlet', 'Taiki Shuttle'],
                    'ancestors_rank_letters' => ['S+', 'UG'],
                    'sparks' => [
                        ['kind' => 'blue', 'target' => 'Speed', 'stars' => 2],
                        ['kind' => 'pink', 'target' => 'Turf', 'stars' => 2],
                        ['kind' => 'green', 'target' => 'Red Shift/LP1211-M', 'stars' => 1],
                        ['kind' => 'white', 'target' => 'Takarazuka Kinen', 'stars' => 2],
                        ['kind' => 'white', 'target' => 'Asahi Hai F.S.', 'stars' => 2],
                        ['kind' => 'white', 'target' => 'Corner Adept', 'stars' => 2],
                        ['kind' => 'white', 'target' => 'Ramp Up', 'stars' => 2],
                        ['kind' => 'white', 'target' => 'Mile Straightaways', 'stars' => 1],
                        ['kind' => 'white', 'target' => 'Ignited Spirit: Guts +', 'stars' => 1],
                    ],
                    'ancestors_sparks' => [
                        // Daiwa Scarlet
                        [
                            ['kind' => 'blue', 'target' => 'Speed', 'stars' => 2],
                            ['kind' => 'pink', 'target' => 'Front Runner', 'stars' => 2],
                            ['kind' => 'green', 'target' => 'Resplendent Red Ace', 'stars' => 1],
                            ['kind' => 'white', 'target' => 'Osaka Hai', 'stars' => 2],
                            ['kind' => 'white', 'target' => 'Tenno Sho (Spring)', 'stars' => 2],
                            ['kind' => 'white', 'target' => 'NHK Mile C.', 'stars' => 2],
                            ['kind' => 'white', 'target' => 'Japan C.', 'stars' => 2],
                            ['kind' => 'white', 'target' => 'Corner Adept', 'stars' => 2],
                            ['kind' => 'white', 'target' => 'Unyielding Spirit', 'stars' => 2],
                            ['kind' => 'white', 'target' => 'Front Runner Savvy', 'stars' => 1],
                        ],
                        // Taiki Shuttle
                        [
                            ['kind' => 'blue', 'target' => 'Power', 'stars' => 3],
                            ['kind' => 'pink', 'target' => 'Pace Chaser', 'stars' => 2],
                            ['kind' => 'green', 'target' => 'Shooting for Victory!', 'stars' => 2],
                            ['kind' => 'white', 'target' => 'NHK Mile C.', 'stars' => 2],
                            ['kind' => 'white', 'target' => 'Sprinters S.', 'stars' => 1],
                            ['kind' => 'white', 'target' => 'JBC L. Classic', 'stars' => 2],
                            ['kind' => 'white', 'target' => 'Tokyo Daishoten', 'stars' => 2],
                            ['kind' => 'white', 'target' => 'Corner Adept', 'stars' => 2],
                            ['kind' => 'white', 'target' => 'Corner Acceleration', 'stars' => 2],
                            ['kind' => 'white', 'target' => 'Shifting Gears', 'stars' => 1],
                            ['kind' => 'white', 'target' => 'Productive Plan', 'stars' => 2],
                            ['kind' => 'white', 'target' => 'Pace Chaser Straightaways', 'stars' => 3],
                            ['kind' => 'white', 'target' => 'Disorient', 'stars' => 2],
                            ['kind' => 'white', 'target' => 'Sympathy', 'stars' => 2],
                            ['kind' => 'white', 'target' => 'Head-On', 'stars' => 2],
                            ['kind' => 'white', 'target' => 'Glittering Star', 'stars' => 2],
                        ],
                    ],
                ],
            ],
            'affinity' => null,
        ];
    }

    /**
     * Two events on turn 67 — the run's only recorded turn — in `TurnEventType`'s own vocabulary, so
     * the Events screen renders them without inventing a type string. Both are readings §1.6 and the
     * deck panel support: the SupportCard row is Super Creek's, and the Character row is the trainee's
     * own event the frames show. `bond_delta` and `choice_index` are recorded as read.
     *
     * @return list<array{event_type: TurnEventType, source_name: string, choice_index: int, choice_label: string, bond_delta: int}>
     */
    private const array EVENTS = [
        [
            'event_type' => TurnEventType::SupportCard,
            'source_name' => 'Super Creek [Piece of Mind]',
            'choice_index' => 0,
            'choice_label' => 'Ask her to push through the fatigue.',
            'bond_delta' => 5,
        ],
        [
            'event_type' => TurnEventType::Character,
            'source_name' => 'Rice Shower',
            'choice_index' => 0,
            'choice_label' => 'Have a small snack.',
            'bond_delta' => 0,
        ],
    ];

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

        // Idempotent by refresh, not by skip (updated 2026-11-15): a prior import of this same report
        // is deleted with its child rows before re-importing, so a second `db:seed` actually applies
        // corrections. Deck slots, events, turn entries, race entries and skill pivots are named so a
        // new child table never silently survives its parent's delete.
        $prior = TrainingRun::where('import_source', $source)->first();

        if ($prior !== null) {
            $prior->deckSlots()->delete();
            $prior->turnEvents()->delete();
            $prior->raceEntries()->delete();
            $prior->skills()->detach();
            $prior->turnEntries()->delete();
            $prior->delete();
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
        // the Unity Cup team state §1.8 reads off the Team Info panel, and the Legacy Select read-back
        // §1.5 records. The payload is validated through `LegacySelectionPayload::fromArray()` once
        // here so a wrongly-shaped transcription fails at seed time rather than when the Inheritance
        // screen reads it (`TrainingRun::legacySelection()` validates on every read); the parent
        // identities go to the two FK columns beside it, per `ADR-0010` and `SetupDraft::legacyParents()`.
        LegacySelectionPayload::fromArray(self::legacySelection());

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
            // §1.8's Team Info panel reads "S (five stars)" (R6-48; the column landed with the
            // 2026-11-15 owner ruling). Stored as the letter alone: the star count is the panel's
            // own presentation of the rank, not a second fact this column holds.
            'team_rank' => 'S',
            'unity_trainings_count' => 55,
            'spirit_bursts_count' => 6,
            'extreme_bursts_count' => 5,
            'mode' => RunMode::Snapshot->value,
            'inheritance_parent_a_id' => self::LEGACY_PARENT_IDS['a'],
            'inheritance_parent_b_id' => self::LEGACY_PARENT_IDS['b'],
            'legacy_selection' => self::legacySelection(),
        ]);

        // §1.4's deck, written here because `ImportHistoricalRun` writes no deck slots (its own
        // docblock, and the URA seed records the same omission). `support_cards.id` is the FK.
        foreach (self::DECK as $position_index => $card) {
            $model->deckSlots()->create([
                'slot_position' => $position_index,
                'support_card_id' => $card['card_id'],
                'ownership' => $card['ownership'],
                'card_level' => $card['level'],
            ]);
        }

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

        // Two events at turn 67 — the run's only recorded turn — so the Events screen has the readings
        // §1.6 carries without manufacturing timeline entries for turns that were never recorded.
        foreach (self::EVENTS as $event) {
            $model->turnEvents()->create([
                'turn' => $position->turnIndex,
                'event_type' => $event['event_type']->value,
                'source_name' => $event['source_name'],
                'choice_index' => $event['choice_index'],
                'choice_label' => $event['choice_label'],
                'bond_delta' => $event['bond_delta'],
            ]);
        }
    }
}
