<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Enums\RaceEntryStatus;
use App\Http\Controllers\Controller;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Services\RaceFacts;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The Race Decision, `SCR-CAR-013` (SCREEN-011, plan §8 D10). Whether and where to race, at the turn
 * being decided.
 *
 * **Everything here is a catalogue fact, an entered value, or a named absence.** The card's fact list
 * is built from `race_catalog_slots` columns and from what the Trainer has recorded on
 * `race_entries`; a brief field the corpus does not hold renders `N/A` with the reason, never a
 * default (`AGENTS.md` §5, D-220). Four of the brief's ten fields are in that class, and each says
 * which column it would need.
 *
 * **Nothing predicts the race.** The brief asks for an estimated win probability and a
 * LOW/MEDIUM/HIGH risk indicator with `< 10%` / `10-30%` / `> 30%` thresholds; all three are held on
 * `ADR-0016`, which is an OPEN QUESTION rather than a permission, and `PRD.md` §6.11 is unamended by
 * it. `readiness` is therefore `N/A` with a `title` naming the blocker, and no percentage is computed,
 * stored or rendered anywhere in this slice. The screen-spec's correction row asks for
 * Excellent/Good/Borderline/Poor bands instead of fake precision. A band describes preparation and a
 * percentage predicts an outcome, so they are not the same claim, and whether a descriptive band is
 * permitted here is a ruling the owner owes rather than one `ADR-0016` has made (`SCREEN_SPEC.md`
 * SCR-CAR-013, "The held figure"). Nothing is computed either way, so the row prints `N/A`.
 *
 * **A mandatory race is a career obligation, not a Goal.** `is_mandatory` is true on seven rows: the
 * Junior Make Debut, the qualifier, the semifinal and the four scenario finals. `79ffad5` withdrew it
 * as the *pennant* input, because the client's red banner means "this character's objective" and the
 * Goal sets differ per trainee (`docs/scenarios/09-global-race-calendar.md`). This screen renders it
 * as the different fact it is, and says "mandatory", never "goal".
 */
class RaceDecisionController extends Controller
{
    /**
     * The four career years, for the deadline rows' own year word. `RaceCatalogSlot::YEARS` carries it.
     */
    public function show(TrainingRun $run): Response
    {
        // Three queries: the run with its trainee, its race entries with their slots, and its turns.
        $run->load(['umamusume', 'raceEntries.raceCatalogSlot', 'turnEntries']);

        $next = $run->decisionTurn();
        $races = $this->raceRows($run, $next);

        // F2, plan §9.6 ruling 3: manual free-race entry. The branch is server-resolved from the
        // query string (the same shape `RacePanel` reads), so a mode switch is a navigation and a
        // surviving component instance can never submit the branch it no longer shows.
        $entryMode = in_array(request()->query('entry_mode'), ['calendar', 'manual'], true)
            ? (string) request()->query('entry_mode')
            : 'calendar';

        return Inertia::render('Career/RaceDecision', [
            'run' => $this->runSection($run),
            'nextTurn' => $this->nextTurnSection($next),
            'races' => $races,
            'deadlines' => $this->deadlineRows($run, $next),
            'readiness' => $this->readiness(),
            'entry' => $this->entrySection($run),
            // The door to the Scenario Race Planner (`SCR-CAR-023`, plan §9 E5). A screen reachable
            // only by URL is a defect (D14's finding on `runs.timeline`), and this is the screen whose
            // action grid entry the planner extends, so the link belongs here.
            'planner_url' => route('runs.races.planner', $run),
            'empty' => $this->emptyState($run, $next, count($races)),
            'entry_mode' => $entryMode,
            'manual_slots' => $this->manualSlotRows($run),
            'manual_defaults' => $this->manualDefaults($run, $next),
            'race_statuses' => array_map(static fn (RaceEntryStatus $status): string => $status->value, RaceEntryStatus::cases()),
        ]);
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
     * The turn being decided, in the client's own words, or null when there is none.
     *
     * `decisionTurn()` answers with the run's stored position for a snapshot and with
     * `nextTurnToPlay()` for a new career; both are real states and neither is pointed at a default
     * turn (D-220).
     *
     * @param  array{year: int, turn: int}|null  $next
     * @return array{year_label: string, turn: int, position_label: string}|null
     */
    private function nextTurnSection(?array $next): ?array
    {
        if ($next === null) {
            return null;
        }

        return [
            'year_label' => (RaceCatalogSlot::YEARS[$next['year']] ?? (string) $next['year']).' Year',
            'turn' => $next['turn'],
            'position_label' => ($next['turn'] % 2 === 1 ? 'Early ' : 'Late ').self::MONTHS[intdiv($next['turn'] - 1, 2)],
        ];
    }

    /**
     * The races on this run's calendar at the turn being decided, each as a card.
     *
     * @param  array{year: int, turn: int}|null  $next
     * @return list<array<string, mixed>>
     */
    private function raceRows(TrainingRun $run, ?array $next): array
    {
        // A run that names no scenario has no calendar: `scenarioKey()` resolves to the baseline so
        // the strip has a descriptor to compose, and lending it URA's races would be a fact about a
        // scenario the Trainer never chose (the guard `calendarRaceSlots()` already makes).
        if ($next === null || ! $run->hasScenario()) {
            return [];
        }

        $slots = RaceCatalogSlot::query()
            ->forScenario($run->scenarioKey())
            ->onTurn($next['year'], $next['turn'])
            ->orderBy('sort_order')
            ->get();

        $entries = $run->raceEntries
            ->filter(fn (RaceEntry $entry): bool => $entry->race_catalog_slot_id !== null)
            ->keyBy('race_catalog_slot_id');

        return $slots
            ->map(fn (RaceCatalogSlot $slot): array => $this->raceRow($slot, $entries->get($slot->id)))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function raceRow(RaceCatalogSlot $slot, ?RaceEntry $entry): array
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
            // What the Trainer has already recorded for this slot, or null when they have not
            // reached it. The status is the enum's own value; the word is the enum's, not a label
            // this screen invents.
            'status' => $entry?->status->value,
            'placement' => $entry?->placementOrdinal(),
            'fans_gain' => $entry?->fans_gain,
            'grade_points' => $entry?->grade_points_earned,
        ];
    }

    /**
     * The mandatory races this run's scenario obliges, in calendar order, with what the run has done
     * about each.
     *
     * Read from the catalogue rather than from the run's entries, because a deadline that has not been
     * reached yet has no entry and is exactly the thing this screen exists to show. The state comes
     * from the entry when there is one, so a cleared obligation says so.
     *
     * @param  array{year: int, turn: int}|null  $next
     * @return list<array<string, mixed>>
     */
    private function deadlineRows(TrainingRun $run, ?array $next): array
    {
        if (! $run->hasScenario()) {
            return [];
        }

        $slots = RaceCatalogSlot::query()
            ->forScenario($run->scenarioKey())
            ->where('is_mandatory', true)
            ->orderBy('year')
            ->orderBy('turn')
            ->get();

        $entries = $run->raceEntries->keyBy('race_catalog_slot_id');

        $rows = [];
        $marked = false;

        foreach ($slots as $slot) {
            $entry = $entries->get($slot->id);
            $cleared = $entry?->status === RaceEntryStatus::Completed;

            $rows[] = [
                'id' => $slot->id,
                'title' => $slot->title,
                'year_label' => $slot->yearLabel(),
                'turn' => $slot->turnNumber(),
                'state' => $this->deadlineState($entry),
                // The first obligation the run has not cleared is the one in front of it. One row
                // carries the marker, so the region never becomes a wall of emphasis (Von Restorff).
                'is_next' => ! $marked && ! $cleared,
                'reached' => $slot->isAtOrBeforeTurn($next),
            ];

            $marked = $marked || ! $cleared;
        }

        return $rows;
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
     * The one figure the brief asks for that this tool must not state.
     *
     * @return array{label: string, title: string}
     */
    private function readiness(): array
    {
        return [
            'label' => 'N/A',
            'title' => 'Held. Race prediction is blocked on the requirement data `ADR-0016` measures, and that decision is an open question rather than a permission: `PRD.md` §6.11 stands unamended. No readiness band is computed here.',
        ];
    }

    /**
     * What the drawer's form needs to enter a race: the route it posts to, and the run's logged turns
     * for the optional link.
     *
     * Only `entry_mode`, `race_catalog_slot_id` and `status` are required by `StoreRaceEntryRequest`;
     * the turn link is the one optional field this screen offers, because KI-17 exists to tie a race
     * to the turn it was run on and the Trainer knows that at decision time. A finish, a fan gain and
     * a placement are outcomes rather than decisions, so they are recorded on the run screen's race
     * panel, which is where the run's own history is kept.
     *
     * @return array<string, mixed>
     */
    private function entrySection(TrainingRun $run): array
    {
        return [
            // F2, plan §9.6 ruling 3: manual free-race entry lands on Race Decision, posting
            // `entry_mode=manual` to `runs.races.store`. The same action URL handles both the
            // calendar and the manual branch; the form selects which.
            'action' => route('runs.races.store', $run),
            'turns' => $run->turnEntries
                ->sortBy('turn')
                ->map(static fn (TurnEntry $turn): array => ['id' => $turn->id, 'turn' => $turn->turn])
                ->values()
                ->all(),
        ];
    }

    /**
     * The run's own hand-entered races, as the manual branch's "already entered" list.
     *
     * `scenario_slots` of kind `free_race` are the rows the manual path writes; the calendar path
     * reads `race_catalog_slots`. The two lists are disjoint, and this one is what the manual arm
     * shows so a Trainer can see what they have already recorded by hand.
     *
     * @return list<array{id: int, title: string}>
     */
    private function manualSlotRows(TrainingRun $run): array
    {
        if (! $run->hasScenario()) {
            return [];
        }

        return ScenarioSlot::query()
            ->where('scenario_key', $run->scenarioKey())
            ->where('kind', 'free_race')
            ->orderBy('sort_order')
            ->get()
            ->map(static fn (ScenarioSlot $slot): array => ['id' => $slot->id, 'title' => $slot->title])
            ->values()
            ->all();
    }

    /**
     * What a blank manual form pre-fills: the month and half of the turn being decided, so a Trainer
     * entering a free race at turn N gets turn N's month and half. The values are what
     * `StoreRaceEntryRequest` requires (`month` 1-12, `half` Early/Late); `tier` is nullable and
     * starts empty. With no turn to read, both are blank and the Trainer picks.
     *
     * @param  array{year: int, turn: int}|null  $next
     * @return array{month: string, half: string, tier: string}
     */
    private function manualDefaults(TrainingRun $run, ?array $next): array
    {
        $turn = $next['turn'] ?? $run->turnEntries->sortByDesc('turn')->first()?->turn;

        if ($turn === null) {
            return ['month' => '', 'half' => '', 'tier' => ''];
        }

        return [
            'month' => (string) (intdiv($turn - 1, 2) + 1),
            'half' => $turn % 2 === 1 ? 'Early' : 'Late',
            'tier' => '',
        ];
    }

    /**
     * The sentence the page shows in place of a race list, or null when there is one.
     *
     * @param  array{year: int, turn: int}|null  $next
     */
    private function emptyState(TrainingRun $run, ?array $next, int $raceCount): ?string
    {
        if ($raceCount > 0) {
            return null;
        }

        if (! $run->hasScenario()) {
            return 'This run names no scenario, so it has no race calendar and no races to decide between. Choose a scenario on the Cockpit header and the calendar appears.';
        }

        if ($next === null) {
            return $run->turnEntries->isEmpty()
                ? 'No turn has been logged yet, so there is no turn to decide about. Record the first turn through Training, then this screen opens on the next one.'
                : 'Every turn of this career has been played, so there is no next race to decide about. The Career Timeline at the bottom of the Cockpit has the full history.';
        }

        return 'No race on this scenario\'s calendar falls at this turn. The race calendar on the Cockpit header shows which turns carry one.';
    }

    /** The twelve month names, as the client's own Early/Late halves divide them. */
    private const MONTHS = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ];
}
