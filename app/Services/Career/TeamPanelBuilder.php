<?php

declare(strict_types=1);

namespace App\Services\Career;

use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Enums\TraineeGoalType;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\TraineeGoal;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Illuminate\Support\Collection;

/**
 * The career-goals panel and the team arm, extracted from `CockpitController::careerGoalSections()`,
 * `::traineeGoalRows()`, `::objectiveMet()`, `::happyMeekRows()` and `::teamSection()` (plan §3.1a,
 * F1 4.3).
 *
 * **Two clusters in one service.** The goal ladder (`careerGoalSections` and the two readers it owns)
 * answers `SCR-CAR-014`; the team arm (`teamSection` and the three surfaces it composes) answers the
 * Unity Cup panel (E3, `SCR-CAR-019`). Both were read only from `scenarioSection()` and their own
 * helpers, so they move together and the controller keeps the call sites. `obligationSlots()` does
 * **not** move: the decision rail reads it too (`ADR-0015`), so it stays on `CockpitController` and
 * the service reads the same position through the run rather than the collection.
 *
 * **The helpers travel with their only caller.** `traineeGoalRows()` and `objectiveMet()` are read
 * only from `careerGoalSections()`; `teamCoverageSection()`, `teamTournamentSection()` and
 * `burstBandFor()` are read only from `teamSection()`. None is a second surface for a fact another
 * screen owns, so none stays behind. Every method's own docblock, moved with its body, states the
 * contract at length.
 *
 * **Nothing here predicts.** The goal ladder prints derived states and an integer pair, never a bar,
 * a percentage or a score (`PRD.md` §6.11, ux-laws §9 item 6); the team arm prints the stored matrix
 * and the run's own entered counters (`ADR-0016`, `ADR-0020` §3).
 *
 * @phpstan-type TeamSectionShape array{
 *     rank: string|null,
 *     facility_level: int|null,
 *     ladder: list<array{rank: string, level: int}>,
 *     stat_grades: list<array{key: string, label: string, grade: null}>,
 *     roster: list<array{teammate: string, state: string, stateLabel: string}>,
 *     race_guidance: int|null,
 *     race_entries: list<array{title: string|null, tier: string|null, circles: int|null, placement: int|null}>,
 *     bands: list<array{total: string, reward: string}>,
 *     payout_timing: string|null,
 *     special_training: array{energy_penalty_removed: bool|null, wit_burst_energy_bonus: int|null},
 *     team_name: string|null,
 *     team_motto: string|null,
 *     team_league_placement: int|null,
 *     team_preseason_wins: int|null,
 *     unity_trainings_count: int|null,
 *     spirit_bursts_count: int|null,
 *     extreme_bursts_count: int|null,
 *     combined_bursts: int|null,
 *     burst_band: string|null
 * }
 */
final class TeamPanelBuilder
{
    /**
     * The career goals panel's own sections: the mandatory races this scenario's calendar records,
     * and the two modules this repository holds no data for (plan §9 E2, SCREEN-014).
     *
     * A row is either a reading with a state, or an unrecorded value whose `absence` says why it is
     * absent. The state vocabulary is the brief's four (`completed`, `current`, `upcoming`,
     * `missed`), and the calendar position is never re-derived here: `RaceCatalogSlot::isAtOrBeforeTurn()`
     * is the same owner the race strip above already reads (ADR-0015), so the two regions cannot
     * disagree about whether a turn has arrived.
     *
     * Two rows are the same thing wearing different shapes, and that is the point of stating it: the
     * finale block carries no turn in the catalogue, so its position is the first instant of the
     * fourth year and it can be `current` but can never be `missed`, and it raises no alert because
     * the alert this screen is allowed to make is about a *recorded* turn that has arrived with
     * nothing logged. A window length appears nowhere in the corpus, so none is invented.
     *
     * @return array{objectives: list<array<string, mixed>>, alerts: list<array<string, mixed>>}
     */
    public function careerGoalSections(TrainingRun $run): array
    {
        $next = $run->hasScenario() ? $run->nextTurnToPlay() : null;

        /** @var Collection<int, RaceCatalogSlot> $slots */
        $slots = RaceCatalogSlot::query()
            ->forScenario($run->scenarioKey())
            ->where('is_mandatory', true)
            ->orderBy('year')
            ->orderBy('turn')
            ->orderBy('sort_order')
            ->get();

        // A hand-entered race has no slot id, so it cannot settle a mandatory row. Reading the
        // foreign key rather than the relation is the same choice the race strip makes, for the same
        // reason: a deleted turn must not delete its race, so the column is the fact that survives.
        $entered = $run->raceEntries->pluck('race_catalog_slot_id')->filter();

        $rows = [];
        $alerts = [];

        foreach ($slots as $slot) {
            $recorded = $entered->contains($slot->id);
            $turn = $slot->turnNumber();
            $reached = $slot->isAtOrBeforeTurn($next);

            $state = match (true) {
                $recorded => 'completed',
                $next === null => 'upcoming',
                $turn !== null && $turn === $next['turn'] && (int) $slot->year === (int) $next['year'] => 'current',
                $turn === null => $reached ? 'current' : 'upcoming',
                $reached => 'missed',
                default => 'upcoming',
            };

            // A race the run already has needs no door back to the picker.
            $rows[] = [
                'label' => (string) $slot->title,
                'state' => $state,
                'year_label' => $slot->yearLabel(),
                'turn' => $turn,
                'race_url' => $recorded ? null : route('runs.races.decision', $run),
                'absence' => $turn === null
                    ? 'The calendar records no turn for this race, only its career year, so no turn is printed.'
                    : null,
            ];

            // A race alerts only in the career year the run is standing in. `isAtOrBeforeTurn()` is an
            // ordinal test, so an unrecorded Junior race stays "reached" for the rest of the career and a
            // Senior-year run would banner its first-year debut every turn. The ladder row keeps its
            // `missed` state; only the alert is scoped, because the alert claims a race is still due.
            if (! $recorded && $reached && $turn !== null && $run->status === RunStatus::Active
                && (int) $slot->year === (int) $next['year']) {
                $alerts[] = [
                    'level' => 'Critical',
                    'text' => $slot->title.' is due and not recorded',
                    'detail' => 'The calendar puts it at '.$slot->yearLabel().' turn '.$turn
                        .', and the next turn to play is '.$next['turn'].'.',
                ];
            }
        }

        /** @var array{rows: list<array<string, mixed>>, alerts: list<array<string, mixed>>, summary: string|null, empty_note: string|null} $trainee */
        $trainee = $this->traineeGoalRows($run, $next, $slots);

        $objectives = [
            [
                'group' => 'Trainee goals',
                'rows' => $trainee['rows'],
                'summary' => $trainee['summary'],
                'empty_note' => $trainee['empty_note'],
            ],
            [
                'group' => 'Mandatory races',
                'rows' => $rows,
                'summary' => null,
                'empty_note' => null,
            ],
        ];

        // Happy Meek is URA's own mechanic, so it composes for URA alone. The switch that turned
        // `career_goals` on for Unity Cup and the Grand Concert must not hand those cockpits a module
        // whose rows are all named absences of a mechanic their scenario does not have. Same guard
        // shape the `trackblazer` composers use, and the panel reads the group list as data, so no
        // scenario name reaches a component (gate G-33).
        if ($run->scenarioKey() === 'ura_finale') {
            $objectives[] = [
                'group' => 'Happy Meek',
                'rows' => $this->happyMeekRows(),
                'summary' => null,
                'empty_note' => null,
            ];
        }

        return [
            'objectives' => $objectives,
            // The trainee's own overdue objectives lead, the way every alert here leads the groups.
            'alerts' => [...$trainee['alerts'], ...$alerts],
        ];
    }

    /**
     * The trainee's own objectives, as the sourced ladder states them and this run has recorded them.
     *
     * Progress is **derived, never stored**: a race objective is met by a completed entry at the same
     * calendar row finishing where the source asked, and a fan objective by the fan figure the run had
     * already reached at or before the deadline turn. There is no write path for either, so no second
     * owner exists for a fact the race record and the turn log already answer.
     *
     * Two rules the panel cannot see from here:
     *   - an objective whose calendar row is scenario-wide mandatory raises **no alert of its own**. The
     *     Mandatory races group alerts on it, and the debut is both facts at once: the export makes it
     *     objective #1 for every trainee while the capture corpus says the client banners no pennant on
     *     it (`RACE-AND-SLICE-RESEARCH.md:287-290`, carried to `SCREEN_SPEC.md` §7). One obligation, one
     *     alert, whichever way that question is settled.
     *   - an objective the source marks unreleased on `[Global]` renders as the source naming a race this
     *     calendar does not carry, and never as a Global obligation.
     *
     * The scenario's three final rounds are deliberately absent. They are stored as `FinalRound` rows and
     * are the obligation `race_catalog_slots.is_mandatory` already prints, so listing them here would show
     * one demand twice.
     *
     * @param  array{year: int, turn: int}|null  $next
     * @param  Collection<int, RaceCatalogSlot>  $mandatory
     * @return array{rows: list<array<string, mixed>>, alerts: list<array<string, mixed>>, summary: string|null, empty_note: string|null}
     */
    private function traineeGoalRows(TrainingRun $run, ?array $next, Collection $mandatory): array
    {
        /** @var Collection<int, TraineeGoal> $goals */
        $goals = TraineeGoal::query()
            ->where('umamusume_id', $run->umamusume_id)
            ->where('goal_type', '!=', TraineeGoalType::FinalRound->value)
            ->with('raceCatalogSlot')
            ->orderBy('career_year')
            ->orderBy('turn')
            ->orderBy('source_order')
            ->orderBy('source_race_choice')
            ->get();

        if ($goals->isEmpty()) {
            return [
                'rows' => [],
                'alerts' => [],
                'summary' => null,
                // The first sentence belongs to the model, because the catalog detail page prints the same
                // absence and two copies is how one absence ends up with two vocabularies. The clause after
                // it is this screen's alone: only here does a Mandatory races group sit below the ladder.
                // `ladderAbsenceReason()` answers with a sentence whenever this branch is the one taken, so
                // the coalesce is for the typechecker, not for a state that can reach here.
                'empty_note' => (TraineeGoal::ladderAbsenceReason($goals) ?? '')
                    .' The mandatory races below are the scenario-wide record.',
            ];
        }

        /** @var Collection<int, Collection<int, RaceEntry>> $finished */
        $finished = $run->raceEntries
            ->filter(fn (RaceEntry $entry): bool => $entry->status === RaceEntryStatus::Completed
                && $entry->race_catalog_slot_id !== null)
            ->groupBy('race_catalog_slot_id');

        $rows = [];
        $alerts = [];
        /** @var array<int, bool> $metByOrder */
        $metByOrder = [];

        foreach ($goals as $goal) {
            $slot = $goal->raceCatalogSlot;
            $met = $this->objectiveMet($goal, $run, $finished);
            $metByOrder[$goal->source_order] = ($metByOrder[$goal->source_order] ?? false) || $met;

            $reached = $goal->isAtOrBeforeTurn($next);

            $state = match (true) {
                $goal->global_unreleased => null,
                $met => 'completed',
                $next === null => 'upcoming',
                $goal->turnWithinYear() === (int) $next['turn']
                    && $goal->career_year === (int) $next['year'] => 'current',
                $reached => 'missed',
                default => 'upcoming',
            };

            $race = $slot !== null ? $slot->title : $goal->source_race_name;
            $phrase = $goal->requirementPhrase();

            $rows[] = [
                // One reader for both surfaces, so the panel and the race strip cannot drift into two
                // vocabularies for one objective (TraineeGoal::obligationLine()).
                'label' => $goal->obligationLine(),
                'state' => $state,
                'year_label' => $goal->yearLabel(),
                'turn' => $goal->turnWithinYear(),
                // A door back to the picker only for a race this calendar carries that the run has not
                // finished. A fan objective is met by training, not by entering something.
                'race_url' => $slot !== null && ! $met && $state !== null
                    ? route('runs.races.decision', $run)
                    : null,
                // One owner for the sentence, shared with the catalog detail ladder (`TraineeGoal::absenceReason()`).
                'absence' => $goal->absenceReason(),
            ];

            // An objective alerts only when the race has not run at all. A recorded entry whose
            // placement was never typed in is unproven, not outstanding, and `is due and not met` claims
            // a door that already closed. Same year scope as the mandatory group above: an ordinal
            // deadline stays passed for the rest of the career, so a Senior run would banner its
            // Classic-year objectives every turn.
            $ranThisRace = $slot !== null && $finished->has((int) $slot->id);

            if ($state !== null && $reached && ! $met && ! $ranThisRace && $run->status === RunStatus::Active
                && ($slot === null || ! $mandatory->contains('id', $slot->id))
                && (int) $goal->career_year === (int) $next['year']) {
                $alerts[] = [
                    'level' => 'Critical',
                    'text' => $race !== null ? $race.' is due and not met' : 'A trainee objective is due and not met',
                    'detail' => 'The source puts it at '.$goal->yearLabel().' year turn '.$goal->turnWithinYear()
                        .', and the next turn to play is '.$next['turn'].'. '
                        .($phrase !== null ? $phrase.' is what it asks for.' : 'What it asks for is not decoded here.'),
                ];
            }
        }

        $obligations = count($metByOrder);
        $cleared = count(array_filter($metByOrder));

        return [
            'rows' => $rows,
            'alerts' => $alerts,
            // Counted by ladder position rather than by row: an alternates branch is one obligation the
            // Trainer answers either way. An integer pair and nothing else - no bar, no percentage, no
            // score (PRD §6.11, and ux-laws §9 item 6).
            'summary' => $obligations > 0 ? 'Goals Achieved '.$cleared.' of '.$obligations : null,
            'empty_note' => null,
        ];
    }

    /**
     * Whether this run has met one objective. Read-only, and false where the requirement is unread.
     *
     * @param  Collection<int, Collection<int, RaceEntry>>  $finished
     */
    private function objectiveMet(TraineeGoal $goal, TrainingRun $run, Collection $finished): bool
    {
        if ($goal->goal_type === TraineeGoalType::Fan) {
            $threshold = (int) $goal->fan_threshold;

            // `turn_entries.turn` counts from the start of the career, the same clock the source's deadline
            // turn uses, so the two compare without a conversion.
            $fans = $run->turnEntries
                ->filter(fn (TurnEntry $entry): bool => (int) $entry->turn <= $goal->turn)
                ->max('fans');

            return $threshold > 0 && $fans !== null && (int) $fans >= $threshold;
        }

        $slot = $goal->raceCatalogSlot;

        if ($slot === null) {
            return false;
        }

        /** @var Collection<int, RaceEntry> $entries */
        $entries = $finished->get($slot->id, new Collection);

        if ($goal->goal_type === TraineeGoalType::Participate) {
            return $entries->isNotEmpty();
        }

        $placing = $goal->required_placement;

        // An unconfirmed placing figure (2 and 4) cannot be claimed met, because this tool cannot state
        // what would satisfy it. The row still shows its deadline.
        if ($goal->goal_type !== TraineeGoalType::Placement || $placing === null
            || ! in_array($placing, [1, 3, 5], true)) {
            return false;
        }

        return $entries->contains(fn (RaceEntry $entry): bool => $entry->placement !== null
            && (int) $entry->placement <= $placing);
    }

    /**
     * The four fields SCREEN-014 names for Happy Meek, each an unrecorded reading with its own reason.
     *
     * Nothing stores a level, so there is nothing to badge as Confirmed and no write route to record
     * one through; the brief forbids a migration, so the module is read-only and says why. The last
     * field is not merely unrecorded but unsourced, which is a different sentence and gets one: the
     * rewards themselves are published, and the duel panel is where they read.
     *
     * @return list<array<string, mixed>>
     */
    private function happyMeekRows(): array
    {
        return [
            [
                'label' => 'Current level',
                'state' => null,
                'year_label' => null,
                'turn' => null,
                'race_url' => null,
                'absence' => 'No column stores a Happy Meek level and no route writes one, so this run'
                    .' records none. A Trainer-entered level needs a schema proposal first.',
            ],
            [
                'label' => 'Duel availability',
                'state' => null,
                'year_label' => null,
                'turn' => null,
                'race_url' => null,
                'absence' => 'Nothing stored or configured says whether a duel is open for this run,'
                    .' so the screen does not claim one way or the other.',
            ],
            [
                'label' => 'Potential reward',
                'state' => null,
                'year_label' => null,
                'turn' => null,
                'race_url' => null,
                // Corrected 2026-10-11: this row used to say no source publishes a Happy Meek reward
                // table. One does, and the duel panel now prints it. What stays unrecorded is this
                // run's own reward, and that is a different sentence.
                'absence' => 'No column stores a reward for this run, so nothing is owed to it. The published '
                    .'duel rewards are a scenario rule, and they read on the duel panel rather than here.',
            ],
            [
                'label' => 'Final-race contribution',
                'state' => null,
                'year_label' => null,
                'turn' => null,
                'race_url' => null,
                'absence' => 'No source states how a Happy Meek level changes the finale, so nothing is'
                    .' computed here. This is held computation, not a missing reading.',
            ],
        ];
    }

    /**
     * The team system's facts (E3, plan §9.1): what the run recorded through the team payloads, what
     * config states, and nothing else - the brief's remaining Team Info fields have no writer and stay
     * absences the panel names. The key set is uniform for every scenario so one renderer can draw all
     * of them without branching.
     *
     * Each recorded half is gated the way the run record screen's own payloads are: the ladder on
     * `composesPanel('team_rank_ladder')`, the roster on `spirit_bursts` widget membership
     * (D-221, gate G-34), the race entries on `composesPanel('team_race')`. The rank letter is the
     * one reading a payload already holds; per-stat team grades have no writer, so all five rows
     * are absences and stay that way until a source records one.
     *
     * @return TeamSectionShape
     */
    public function teamSection(TrainingRun $run): array
    {
        $ladder = [];

        if ($run->composesPanel('team_rank_ladder')) {
            foreach ((array) config('scenarios.scenarios.'.$run->scenarioKey().'.team_rank_ladder') as $rung) {
                foreach ((array) ($rung['ranks'] ?? []) as $letter) {
                    $ladder[] = ['rank' => (string) $letter, 'level' => (int) $rung['level']];
                }
            }
        }

        $roster = [];

        if (in_array('spirit_bursts', (array) config('scenarios.scenarios.'.$run->scenarioKey().'.widgets', []), true)) {
            foreach ($run->spiritBurstRoster() as $row) {
                $roster[] = [
                    'teammate' => $row['teammate'],
                    'state' => $row['state']->value,
                    'stateLabel' => $row['state']->label(),
                ];
            }
        }

        $entries = [];

        if ($run->composesPanel('team_race')) {
            foreach ($run->raceEntries as $entry) {
                $slot = $entry->scenarioSlot;

                if ($slot === null || $slot->kind !== 'team_race') {
                    continue;
                }

                $entries[] = [
                    'title' => $slot->title,
                    'tier' => $slot->tier,
                    'circles' => $entry->circles,
                    'placement' => $entry->placement,
                ];
            }
        }

        $def = $run->hasScenario() ? (array) config('scenarios.scenarios.'.$run->scenarioKey(), []) : [];
        $rank = $run->latestTeamRank()?->rank;

        /** @var list<string> $order */
        $order = config('scenarios.stat_order');

        // One gate for the burst machine's reference rows: the bands and the payout timing are the
        // same system's facts, so a matrix that declares the timing without the widget gets neither
        // rather than a sentence that has no table to sit beside.
        $composesBursts = in_array('spirit_bursts', (array) config('scenarios.scenarios.'.$run->scenarioKey().'.widgets', []), true);

        // A missing margin reads as an absence, never as zero: "aim for at least 0 circles" would
        // state a margin the matrix never declared.
        $margin = $run->composesPanel('team_race')
            ? config('scenarios.scenarios.'.$run->scenarioKey().'.team_race.circles_guidance')
            : null;

        // The combined Spirit + Extreme total the band table reads, only when both tallies are
        // recorded: an unrecorded one is not a zero (D-220), so a run that stated one of the two
        // gets no combined figure and the panel keeps its honest absence rather than half a count.
        $combined = ($run->spirit_bursts_count !== null && $run->extreme_bursts_count !== null)
            ? $run->spirit_bursts_count + $run->extreme_bursts_count
            : null;

        return [
            'rank' => $rank,
            'facility_level' => $run->facilityLevel($rank),
            'ladder' => $ladder,
            // No column records a team stat grade, so every row is a named absence rather than a
            // zero (D-220); the labels ride config so the renderer never invents one.
            'stat_grades' => array_map(
                static fn (string $stat): array => ['key' => $stat, 'label' => $stat, 'grade' => null],
                $order
            ),
            'roster' => $roster,
            'race_guidance' => is_numeric($margin) ? (int) $margin : null,
            'race_entries' => $entries,
            'bands' => $composesBursts
                ? array_values((array) config('scenarios.scenarios.'.$run->scenarioKey().'.spirit_burst_bands', []))
                : [],
            'payout_timing' => $composesBursts && isset($def['burst_payout_timing'])
                ? (string) $def['burst_payout_timing']
                : null,
            'special_training' => [
                'energy_penalty_removed' => array_key_exists('team_training_energy_penalty', $def)
                    ? $def['team_training_energy_penalty'] === false
                    : null,
                'wit_burst_energy_bonus' => isset($def['wit_burst_energy_bonus'])
                    ? (int) $def['wit_burst_energy_bonus']
                    : null,
            ],
            // The team's own identity and the Unity Cup counters (A6.6, A6.7), as the Trainer
            // entered them. Null until stated, so the panel names the absence rather than a zero.
            'team_name' => $run->team_name,
            'team_motto' => $run->team_motto,
            'team_league_placement' => $run->team_league_placement,
            'team_preseason_wins' => $run->team_preseason_wins,
            'unity_trainings_count' => $run->unity_trainings_count,
            'spirit_bursts_count' => $run->spirit_bursts_count,
            'extreme_bursts_count' => $run->extreme_bursts_count,
            'combined_bursts' => $combined,
            // The band's own `total` label, so the panel marks the row it already renders rather
            // than carrying a second copy of the bounds.
            'burst_band' => $this->burstBandFor($combined, $run->scenarioKey()),
            // G4's two team surfaces. Both are null unless the matrix turns `team_race` on, so the
            // three scenarios without a team layer draw neither and the section keeps one key set
            // across all four (gate G-33).
            'coverage' => $this->teamCoverageSection($run),
            'tournament' => $this->teamTournamentSection($run, $def),
        ];
    }

    /**
     * The team race's published shape, one row per category, and the reading this run does not have.
     *
     * Five categories, Dirt among them: `docs/scenarios/02-unity-cup.md:149` states the set and corrects
     * an earlier line in the same file, and `SCENARIO-PUBLISHER-REFERENCES.md:1028` repeats it. That is
     * why the rows are not `BuildTargetPayload::DISTANCE_BANDS` — four bands and no Dirt would drop a
     * category of the race it describes, and a board missing a fifth of the thing is worse than no board.
     *
     * The ask is published; who this run fields in a category is not stored anywhere. No column carries a
     * distance for a team race row (`scenario_slots` has none), and the run's individual `race_entries`
     * are a different fact, so they are not counted here as an answer to this question. Each row states
     * the ask with no value beside it and no coverage state is claimed.
     *
     * @return array<string, mixed>|null
     */
    private function teamCoverageSection(TrainingRun $run): ?array
    {
        /** @var array<string, mixed> $race */
        $race = (array) config('scenarios.scenarios.'.$run->scenarioKey().'.team_race', []);

        if (! $run->composesPanel('team_race') || $race === []) {
            return null;
        }

        /** @var list<string> $categories */
        $categories = (array) ($race['categories'] ?? []);
        $asks = number_format((int) ($race['field_per_category'] ?? 0)).' fielded';

        $rows = [];

        foreach ($categories as $category) {
            $rows[] = [
                'category' => $category,
                'asks' => $asks,
                'fielded' => null,
            ];
        }

        return [
            'rows' => $rows,
            'badge' => 'confirmed',
            'source_title' => 'The five categories and the number fielded in each come from '
                .'a guide transcribed into the scenario\'s own settings.',
            'absence_title' => 'No column records who this run fields in any category, so every row '
                .'states the ask with no reading beside it and no coverage state is claimed.',
        ];
    }

    /**
     * What a team race is made of, as far as the sources carry it, and the one question they do not.
     *
     * Cadence and anchors are published (`docs/scenarios/02-unity-cup.md:153`, references `:658` and
     * `:1028`). The turn a round falls on is not: references `:1186` records the per-round turn count as
     * unverified, so the block names the months and leaves `next` empty rather than deriving a turn from
     * a calendar the run does not hold.
     *
     * **The matrix's three opponent names are not read, and none reaches the page.** Measured 2026-10-11:
     * the corpus holds "Turf Queens" and "The Apex" (`02-unity-cup.md:163`, references `:1324`) and
     * nothing for the other two names the config carries. A row rendered from that list would be a client
     * fact with no client source behind it (`AGENTS.md` §5), so the block states the published shape and
     * the absence of a projection, and the names stay in config. Clearing that row is the owner's call,
     * not a slice's.
     *
     * The displayed label avoids the wiki-side gloss for a stat that the ban governs; the key that
     * carries the absence is named for the question it answers.
     *
     * @param  array<string, mixed>  $def
     * @return array<string, mixed>|null
     */
    private function teamTournamentSection(TrainingRun $run, array $def): ?array
    {
        /** @var array<string, mixed> $race */
        $race = (array) ($def['team_race'] ?? []);

        if (! $run->composesPanel('team_race') || $race === []) {
            return null;
        }

        /** @var list<string> $anchors */
        $anchors = (array) ($race['month_anchors'] ?? []);
        $months = isset($race['occurs_every_months']) ? (int) $race['occurs_every_months'] : null;

        return [
            'cadence_months' => $months,
            'month_anchors' => array_map(static fn ($month): string => (string) $month, $anchors),
            'category_count' => count((array) ($race['categories'] ?? [])),
            'clear_all_reward' => isset($race['clear_all_reward']) ? (string) $race['clear_all_reward'] : null,
            'badge' => 'confirmed',
            'source_title' => 'The cadence and the reward come from the scenario\'s own settings, '
                .'transcribed from the scenario guides.',
            'projection' => null,
            'projection_title' => 'No source says how strong each opponent team is. The guides name which '
                .'teams to avoid before a rank is high enough and stop there, so no side of any team race '
                .'can be called favoured and no chance is printed.',
            'next' => null,
            'next_title' => 'A team race arrives every '.$months.' months, at '
                .implode(' and ', $anchors).', and no source publishes the career turn a round falls on, '
                .'so no turn is printed here.',
            'risks' => [
                'No published source ranks the three teams, so the screen calls no side of a team race easier.',
                'No column records who this run fields in each category, so the board states the ask and not the run.',
            ],
        ];
    }

    /**
     * The burst-count band a combined Spirit + Extreme total falls in, as the band's own `total`
     * label (`4–6`, `13+`), or null when the total is unknown or no band contains it.
     *
     * The band labels are display strings from `config('scenarios.scenarios.*.spirit_burst_bands')`,
     * and they are the only bound the matrix carries, so they are parsed here rather than duplicated
     * in config. A label this parse does not recognise matches nothing, which keeps the panel's
     * absence rather than inventing a placement.
     */
    private function burstBandFor(?int $combined, string $scenarioKey): ?string
    {
        if ($combined === null) {
            return null;
        }

        foreach ((array) config('scenarios.scenarios.'.$scenarioKey.'.spirit_burst_bands', []) as $band) {
            $total = (string) ($band['total'] ?? '');

            if (preg_match('/^(\d+)(?:–(\d+))?\+?$/u', $total, $matches) === 1) {
                $low = (int) $matches[1];
                // The upper bound's group is optional and absent from `$matches` when the label is
                // open-ended (`13+`), which is what makes the band unbounded rather than empty.
                $high = isset($matches[2]) ? (int) $matches[2] : null;

                if ($combined >= $low && ($high === null || $combined <= $high)) {
                    return $total;
                }
            }
        }

        return null;
    }
}
