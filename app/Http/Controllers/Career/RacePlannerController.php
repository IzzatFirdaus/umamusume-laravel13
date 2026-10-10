<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Enums\RaceEntryStatus;
use App\Http\Controllers\Controller;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Services\RaceFacts;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The Scenario Race Planner, `SCR-CAR-023` (SCREEN-017, plan §9 E5). The whole calendar, grouped, with
 * a side-by-side comparison of the races the Trainer picks.
 *
 * **Race facts only.** Every value here is a `race_catalog_slots` column, a value the Trainer entered,
 * or a named absence. The brief asks for a win probability, an expected risk, a recommendation block
 * and the sentence "No critical training deadline will be missed"; all four are held or unsourced, so
 * `held` carries the first two as `N/A` with the `ADR-0016` blocker named, the recommendation block is
 * not built, and the deadline section states the next obligation and its turn rather than a promise
 * about the whole career.
 *
 * **The groups are a partition, and each states its own rule.** `is_mandatory` and
 * `RaceCatalogSlot::isAtOrBeforeTurn()` are the two facts the partition reads — the position
 * comparison is that method's, never re-derived here (`ADR-0015`), so this screen and the run race
 * strip cannot disagree about whether a turn has arrived.
 *
 * **Nothing here knows how a scenario works** (gate G-33, `D-240`). The Grade Points cell is filled
 * from `TrainingRun::gradePointsForTier()`, which returns null unless the run's own scenario entry
 * defines a `grade_point_by_grade` table, so the row is driven by config presence and not by a
 * scenario name. A fifth scenario that defines the table fills the cell with no edit here.
 */
class RacePlannerController extends Controller
{
    /**
     * How many rows a position group lists before it truncates.
     *
     * The calendar is 410 rows, and this screen reads it whole to partition it, but it does not ship
     * it whole: a full list is ~1.6 MB of props, and `php -S` is one process, so that payload is paid
     * on every request. Eight is the ceiling E4's rival list already uses for the same reason
     * (Miller's Law, plan §13), and the truncation is named with the full count and a pointer at the
     * Race Database screen, which holds the whole calendar.
     *
     * ponytail: a flat cap, not a window. Upgrade to a career-year filter or a paginator when a
     * Trainer needs to browse a whole year here rather than decide the next few races.
     */
    private const GROUP_LIMIT = 8;

    /**
     * The comparison's rows, in the order they print. One list for every race, which is what makes the
     * columns comparable at all (design-2.0 §46): the same property in the same row for each one.
     *
     * @var list<array{key: string, label: string}>
     */
    private const COMPARISON_ROWS = [
        ['key' => 'grade', 'label' => 'Grade'],
        ['key' => 'distance', 'label' => 'Distance'],
        ['key' => 'distance_band', 'label' => 'Distance band'],
        ['key' => 'surface', 'label' => 'Surface'],
        ['key' => 'fan_gate', 'label' => 'Fan gate'],
        ['key' => 'fan_gain', 'label' => 'Fan gain'],
        ['key' => 'reward', 'label' => 'Reward'],
        ['key' => 'skill_points', 'label' => 'Skill Points'],
        ['key' => 'grade_points', 'label' => 'Grade Points'],
        ['key' => 'shop_coins', 'label' => 'Shop Coins'],
    ];

    /**
     * The three properties the target is compared on, in the order they print.
     *
     * @var list<array{key: string, label: string}>
     */
    private const ALIGNMENT_ROWS = [
        ['key' => 'distance', 'label' => 'Distance band'],
        ['key' => 'surface', 'label' => 'Surface'],
        ['key' => 'style', 'label' => 'Running style'],
    ];

    public function show(TrainingRun $run): Response
    {
        // The run with its trainee, its race entries with their slots, and its turns; then one read of
        // the whole calendar, because the screen's job is the shape of the year rather than one turn of
        // it, and both regions below partition that same read.
        $run->load(['umamusume', 'raceEntries.raceCatalogSlot', 'turnEntries']);

        $next = $run->decisionTurn();
        $empty = $this->calendarAbsence($run);

        // One calendar read for the whole page. The groups and the deadline region partition the same
        // rows, so a second query for the seven mandatory ones would be the same data asked twice.
        $slots = $this->calendarSlots($run);

        return Inertia::render('Career/RacePlanner', [
            'run' => $this->runSection($run),
            'nextTurn' => $next === null ? null : [
                'year_label' => (RaceCatalogSlot::YEARS[$next['year']] ?? (string) $next['year']).' Year',
                'turn' => $next['turn'],
            ],
            'groups' => $empty === null ? $this->groups($run, $slots, $next) : null,
            'comparison' => ['rows' => self::COMPARISON_ROWS],
            'alignment' => $this->alignment($run),
            'held' => $this->held(),
            'deadline' => $this->deadline($run, $slots, $next),
            'entry' => $this->entrySection($run),
            'empty' => $empty,
        ]);
    }

    /**
     * This run's whole calendar, in one query, ordered the way the page reads it.
     *
     * A run that names no scenario gets the same shape rather than a null: `TrainingRun::calendarRaceSlots()`
     * answers the same question with the same `1 = 0` guard, so the two cannot drift on what "no calendar"
     * looks like to a caller.
     *
     * @return Collection<int, RaceCatalogSlot>
     */
    private function calendarSlots(TrainingRun $run): Collection
    {
        if (! $run->hasScenario()) {
            return RaceCatalogSlot::query()->whereRaw('1 = 0')->get();
        }

        return RaceCatalogSlot::query()
            ->forScenario($run->scenarioKey())
            ->orderBy('year')
            ->orderBy('turn')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @return array{id: int, trainee: string, trainee_ja: string|null, scenario_label: string, status_label: string, run_url: string}
     */
    private function runSection(TrainingRun $run): array
    {
        return [
            'id' => $run->id,
            'trainee' => $run->umamusume->name,
            'trainee_ja' => $run->umamusume->name_ja,
            'scenario_label' => $run->hasScenario()
                ? (string) config('scenarios.scenarios.'.$run->scenarioKey().'.label', $run->scenarioKey())
                : 'No scenario set',
            'status_label' => $run->status->label(),
            'run_url' => route('runs.cockpit', $run),
        ];
    }

    /**
     * Why there is no calendar at all, or null when there is one.
     *
     * A run that names no scenario has no calendar: `scenarioKey()` resolves to the baseline so the
     * strip has a descriptor to compose, and lending it URA's races would be a fact about a scenario
     * the Trainer never chose (`TrainingRun::calendarRaceSlots()` makes the same guard).
     */
    private function calendarAbsence(TrainingRun $run): ?string
    {
        if ($run->hasScenario()) {
            return null;
        }

        return 'This run names no scenario, so it has no race calendar to plan against. Choose a scenario on the Cockpit and the calendar appears.';
    }

    /**
     * The calendar, partitioned by obligation and by whether its turn has arrived.
     *
     * @param  Collection<int, RaceCatalogSlot>  $slots
     * @param  array{year: int, turn: int}|null  $next
     * @return array<string, array<string, mixed>>
     */
    private function groups(TrainingRun $run, Collection $slots, ?array $next): array
    {
        $entries = $run->raceEntries
            ->filter(fn (RaceEntry $entry): bool => $entry->race_catalog_slot_id !== null)
            ->keyBy('race_catalog_slot_id');

        $mandatory = [];
        $upcoming = [];
        $optional = [];

        foreach ($slots as $slot) {
            $entry = $entries->get($slot->id);
            $row = $this->raceRow($run, $slot, $entry);

            if ($slot->is_mandatory) {
                $mandatory[] = $row;

                continue;
            }

            // No position to compare from, so neither position group can say anything: each names the
            // missing turn rather than guessing that a race is "ahead".
            if ($next === null) {
                continue;
            }

            if (! $slot->isAtOrBeforeTurn($next)) {
                $upcoming[] = $row;

                continue;
            }

            // Reached and unrecorded. A reached race the run has already entered is left out: the
            // run screen's race panel owns the history, and the same row in two groups reads as two
            // races.
            if ($entry === null) {
                $optional[] = $row;
            }
        }

        $positionAbsence = 'No turn has been logged yet, so the planner cannot say which races are ahead of this run. Record the first turn from the Cockpit and the groups fill in.';

        return [
            'mandatory' => $this->group(
                'mandatory',
                'Mandatory races',
                'The races this scenario obliges whichever trainee is running. A mandatory race is a career rule; it is not the same claim as a Goal, which is per-trainee and has no source here.',
                $mandatory,
                'No mandatory race is on this scenario\'s calendar, so there is no obligation to plan around. The calendar is the shared career schedule plus the scenario\'s own final; the Race Database screen shows what it holds.',
            ),
            'upcoming' => $this->group(
                'upcoming',
                'Upcoming races',
                'Races this scenario does not oblige whose turn has not arrived yet.',
                $upcoming,
                $next === null ? $positionAbsence : 'Every race this scenario does not oblige has already been reached. The optional group holds the ones still unrecorded.',
            ),
            'optional' => $this->group(
                'optional',
                'Optional races',
                'Races this scenario does not oblige whose turn has arrived, with nothing recorded against them.',
                $optional,
                $next === null ? $positionAbsence : 'Every race whose turn has arrived has been recorded. The Cockpit race strip lists what this run recorded.',
            ),
            /*
             * No column marks a rival race. The corpus says rival appearance is random and gated on the
             * trainee's aptitude array (`docs/scenarios/03-trackblazer.md`, and `DESIGN-CORPUS.md`'s
             * note that a rival at a distance she is D-rated in is impossible), so this tool cannot
             * say which races are rival races. The group states that rather than listing races the
             * client might never mark.
             */
            'rival' => $this->group(
                'rival',
                'Rival races',
                'Races the client marks with a rival. No catalogue column carries the marker.',
                [],
                'No column marks a rival race. Rival appearance is random and bounded by the trainee\'s aptitude, so this tool cannot say which races are rival races; the client shows the marker in its own race list.',
            ),
        ];
    }

    /**
     * One group's shape. The rows are capped at `GROUP_LIMIT` and the loss is named rather than
     * silent: a list that quietly stops at eight reads as a complete calendar that happens to hold
     * eight races.
     *
     * @param  list<array<string, mixed>>  $races
     * @return array{key: string, label: string, definition: string, total: int, truncated: string|null, empty: string|null, races: list<array<string, mixed>>}
     */
    private function group(string $key, string $label, string $definition, array $races, string $empty): array
    {
        $total = count($races);

        return [
            'key' => $key,
            'label' => $label,
            'definition' => $definition,
            'total' => $total,
            'truncated' => $total > self::GROUP_LIMIT
                ? sprintf(
                    'First %d of %d, in calendar order. The Race Database screen holds the full calendar.',
                    self::GROUP_LIMIT,
                    $total,
                )
                : null,
            'empty' => $total === 0 ? $empty : null,
            'races' => array_slice($races, 0, self::GROUP_LIMIT),
        ];
    }

    /**
     * One race, in the shape `components/career/RaceCard.vue` reads (D10's card, reused rather than
     * re-built), plus the two cell lists the comparison and alignment tables print.
     *
     * @return array<string, mixed>
     */
    private function raceRow(TrainingRun $run, RaceCatalogSlot $slot, ?RaceEntry $entry): array
    {
        return [
            'id' => $slot->id,
            'title' => $slot->title,
            'tier' => $slot->tier,
            'year_label' => $slot->yearLabel(),
            'turn' => $slot->turnNumber(),
            'fans_needed' => $slot->hasFanGate() ? $slot->fans_needed : null,
            'maiden_gated' => $slot->hasMaidenGate(),
            'is_mandatory' => $slot->is_mandatory,
            'is_special' => $slot->is_special_race,
            'facts' => RaceFacts::forSlot($slot),
            'status' => $entry?->status->value,
            'placement' => $entry?->placementOrdinal(),
            'fans_gain' => $entry?->fans_gain,
            'grade_points' => $entry?->grade_points_earned,
            'comparison' => $this->comparisonCells($run, $slot),
            'alignment' => $this->alignmentCells($run, $slot),
        ];
    }

    /**
     * The reward-comparison cells for one race, in `COMPARISON_ROWS` order.
     *
     * Five of the ten rows have a column behind them. Fan gain, Reward and Skill Points do not, and
     * say so. Grade Points is the run's own config table (`TrainingRun::gradePointsForTier()`, which
     * is null unless this scenario defines one), and Shop Coins are paid by placement, which is an
     * outcome rather than a fact about the race.
     *
     * @return list<array{key: string, label: string, value: string|null, title: string|null}>
     */
    private function comparisonCells(TrainingRun $run, RaceCatalogSlot $slot): array
    {
        $gradePoints = $slot->tier === null ? null : $run->gradePointsForTier($slot->tier);

        $values = [
            'grade' => [
                $slot->tier,
                'This catalogue row carries no tier label.',
                null,
            ],
            'distance' => [
                $slot->distance === null ? null : $slot->distanceLabel(),
                'This catalogue row carries no distance, so the client decides it from the trainee\'s most-run types.',
                null,
            ],
            'distance_band' => [
                $slot->distance_band,
                'This catalogue row carries no distance band.',
                'Band names are the export\'s own. The corpus disagrees with itself at the 1400 m line (Game8 calls it Sprint, the export calls it Mile), so the band is printed as the export states it and no band is derived from a metre count.',
            ],
            'surface' => [
                $slot->surface,
                'This catalogue row carries no surface.',
                null,
            ],
            'fan_gate' => [
                $slot->hasFanGate() ? number_format((int) $slot->fans_needed) : null,
                'This race carries no fan gate, so it asks for no fan count to enter.',
                null,
            ],
            'fan_gain' => [
                $slot->fanPayout(),
                'Payout not recorded for this race. The row carries a payout curve id and no per-placement payout is stored against it.',
                null,
            ],
            'reward' => [
                null,
                'No column holds a race reward, and no source in this repository states one per race.',
                null,
            ],
            'skill_points' => [
                null,
                'No column holds a skill-point payout. The Trainer records what the client paid with the turn, on Training.',
                null,
            ],
            'grade_points' => [
                $gradePoints === null ? null : number_format($gradePoints),
                'This run\'s scenario defines no Grade Point table, or this race\'s grade is not in it, so the payout cannot be read off. Grade Points are a Trackblazer currency.',
                null,
            ],
            'shop_coins' => [
                null,
                'Shop Coins are paid by placement, which is the outcome of a race rather than a fact about it. The client decides it when the race is run.',
                null,
            ],
        ];

        return array_map(
            static fn (array $row): array => [
                'key' => $row['key'],
                'label' => $row['label'],
                'value' => $values[$row['key']][0],
                'title' => $values[$row['key']][0] === null ? $values[$row['key']][1] : $values[$row['key']][2],
            ],
            self::COMPARISON_ROWS,
        );
    }

    /**
     * The target comparison for one race, in `ALIGNMENT_ROWS` order.
     *
     * Plain words, never a score or a percentage: a target is either met by this race, not met by it,
     * or not recorded on one side or the other. A running style is the trainee's aptitude and no
     * column holds a per-race one, so that row is always an absence rather than a guess.
     *
     * @return list<array{key: string, label: string, value: string, title: string}>
     */
    private function alignmentCells(TrainingRun $run, RaceCatalogSlot $slot): array
    {
        $target = $run->buildTarget();

        $compare = function (?string $wanted, ?string $actual, string $missing): array {
            if ($wanted === null) {
                return [
                    'Not recorded',
                    'No build target is recorded for this run, so there is nothing to compare against. The target is entered in the setup wizard.',
                ];
            }

            if ($actual === null) {
                return ['Not recorded', $missing];
            }

            return [
                $wanted === $actual ? 'Matches' : 'Does not match',
                "Target: {$wanted}. This race: {$actual}.",
            ];
        };

        $cells = [
            'distance' => $compare($target?->distance, $slot->distance_band, 'This catalogue row carries no distance band, so its band cannot be compared with the target.'),
            'surface' => $compare($target?->surface, $slot->surface, 'This catalogue row carries no surface, so it cannot be compared with the target.'),
            'style' => [
                'Not recorded',
                'A running style is the trainee\'s aptitude, not a property of the race, and no column holds a per-race one. The target\'s own style is on the Build Target screen.',
            ],
        ];

        return array_map(
            static fn (array $row): array => [
                'key' => $row['key'],
                'label' => $row['label'],
                'value' => $cells[$row['key']][0],
                'title' => $cells[$row['key']][1],
            ],
            self::ALIGNMENT_ROWS,
        );
    }

    /**
     * Whether the run has a target to compare against, and the row headers the table prints.
     *
     * @return array{recorded: bool, absence: string|null, rows: list<array{key: string, label: string}>}
     */
    private function alignment(TrainingRun $run): array
    {
        return [
            'recorded' => $run->buildTarget() !== null,
            'absence' => $run->buildTarget() === null
                ? 'No build target is recorded for this run, so every alignment cell reads "Not recorded". The target is entered in the setup wizard, step 3.'
                : null,
            'rows' => self::ALIGNMENT_ROWS,
        ];
    }

    /**
     * The two figures the brief asks for that this tool must not state.
     *
     * @return array{win_probability: array{label: string, title: string}, expected_risk: array{label: string, title: string}}
     */
    private function held(): array
    {
        $blocker = 'Held. Race prediction is blocked on the requirement data `ADR-0016` measures, and that decision is an open question rather than a permission: `PRD.md` §6.11 stands unamended.';

        return [
            'win_probability' => [
                'label' => 'N/A',
                'title' => $blocker.' No win figure is computed here.',
            ],
            'expected_risk' => [
                'label' => 'N/A',
                'title' => $blocker.' No expected risk is computed here, and no risk band is printed either.',
            ],
        ];
    }

    /**
     * The next mandatory race the run has not cleared, and the Level 1 alert when its turn has arrived.
     *
     * The brief's "No critical training deadline will be missed" is a promise about the whole career,
     * which this tool cannot make: it states the next obligation and its turn instead.
     *
     * @param  Collection<int, RaceCatalogSlot>  $slots  the run's calendar, in calendar order
     * @param  array{year: int, turn: int}|null  $next
     * @return array{next: array<string, mixed>|null, alert: array{text: string, detail: string}|null}
     */
    private function deadline(TrainingRun $run, Collection $slots, ?array $next): array
    {
        if (! $run->hasScenario()) {
            return ['next' => null, 'alert' => null];
        }

        $entries = $run->raceEntries->keyBy('race_catalog_slot_id');

        /** @var RaceCatalogSlot|null $pending */
        $pending = null;
        $row = null;

        foreach ($slots as $slot) {
            if (! $slot->is_mandatory) {
                continue;
            }

            $entry = $entries->get($slot->id);

            if ($entry?->status === RaceEntryStatus::Completed) {
                continue;
            }

            $pending = $slot;
            $row = [
                'id' => $slot->id,
                'title' => $slot->title,
                'year_label' => $slot->yearLabel(),
                'turn' => $slot->turnNumber(),
                'state' => $this->deadlineState($entry),
            ];

            break;
        }

        if ($pending === null || $row === null) {
            return ['next' => null, 'alert' => null];
        }

        // The Level 1 item is raised by the calendar, not by the brief: it appears when the turn this
        // obligation sits on has been reached and the run has recorded nothing against it.
        $alert = $pending->isAtOrBeforeTurn($next)
            ? [
                'text' => "Critical: {$pending->title} is due and not recorded",
                'detail' => $row['turn'] === null
                    ? "{$row['year_label']}, the post-December block."
                    : "{$row['year_label']} year, turn {$row['turn']}.",
            ]
            : null;

        return ['next' => $row, 'alert' => $alert];
    }

    private function deadlineState(?RaceEntry $entry): string
    {
        return match ($entry?->status) {
            RaceEntryStatus::Completed => 'Cleared',
            RaceEntryStatus::Entered => 'Entered',
            RaceEntryStatus::Skipped => 'Skipped',
            RaceEntryStatus::NotOffered => 'Not offered',
            null => 'Not recorded yet',
        };
    }

    /**
     * What the cards' drawer needs to enter a race: the route it posts to, and the run's logged turns
     * for the optional link. The write is the existing `runs.races.store` and its own Form Request, so
     * no second race-write route exists.
     *
     * @return array<string, mixed>
     */
    private function entrySection(TrainingRun $run): array
    {
        return [
            'action' => route('runs.races.store', $run),
            'turns' => $run->turnEntries
                ->sortBy('turn')
                ->map(static fn (TurnEntry $turn): array => ['id' => $turn->id, 'turn' => $turn->turn])
                ->values()
                ->all(),
        ];
    }
}
