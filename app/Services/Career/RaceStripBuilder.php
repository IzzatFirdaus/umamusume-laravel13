<?php

declare(strict_types=1);

namespace App\Services\Career;

use App\Enums\TraineeGoalType;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\TraineeGoal;
use App\Models\TrainingRun;
use Illuminate\Support\Collection;

/**
 * The Cockpit's run-scoped race strip (D14a), extracted from `CockpitController::raceStripSection()`
 * and `::stripRow()` (plan §3.1a, F1).
 *
 * **Three regions, one race per region.** A race the run already entered appears under Run and
 * nowhere else, so a finished race can never read as something still to come. Ahead is therefore
 * strictly future *and* unrecorded, which is also what lets its rows carry no state field at all.
 *
 * **This Turn counts; it does not list.** The seed catalogue holds eleven rows at Senior turn 19,
 * and a narrow column that prints eleven names is the wall this slice exists to remove. The option
 * list, the facts and the fan-gap arithmetic are the Race Decision screen's (`SCR-CAR-013`), so the
 * strip gives the position, how many races the calendar offers there, and what this run has already
 * entered against them.
 *
 * **The mandatory slots arrive already read.** `obligationSlots()` stays on `CockpitController`
 * because the decision rail reads it too, and `ADR-0015`'s point is that unifying the reader is not
 * unifying the call: both readers pass the same career position, so the strip receives the collection
 * rather than asking again.
 *
 * **No goal races.** `trainee_goals` has never been migrated (KI-34), `race_catalog_slots` has no
 * trainee-bearing column, and `scenario_slots` holds `goal_race` rows for `ura_finale` alone. A
 * per-row `goal` flag would print a claim the tool cannot source, so the region states the absence
 * once and the rows have no such key. `is_mandatory` is a career obligation, never a Goal
 * (`79ffad5`).
 *
 * *Dated correction 2026-10-13 (`ADR-0026`):* the paragraph above described the state before the
 * ladder was fetched, and its last sentence still holds - `is_mandatory` is not a Goal. What changed
 * is the first clause: `trainee_goals` is migrated, sourced per character, and every strip row whose
 * calendar slot is one of this trainee's objectives now carries a `goal` line stating the requirement.
 * The obligation prints **beside** a gate rather than in place of it: a fan-locked goal is still
 * locked, and a recorded goal keeps its finish state and still shows the mark. The absence sentence
 * stays for a scenario or trainee with no ladder to read.
 *
 * **Nothing here predicts.** No readiness band, no win figure, no percentage, no distance band
 * derived from metres and no race fact beyond the stored title and tier (`ADR-0016`, `ADR-0020` §3).
 */
final class RaceStripBuilder
{
    /**
     * @param  Collection<int, RaceCatalogSlot>  $obligationSlots  the scenario's mandatory slots, read by `CockpitController::obligationSlots()`
     * @return array{shown: bool, notice: string|null, run: list<array<string, mixed>>, this_turn: array{turn: int, year_label: string, offered: int, recorded: list<array<string, mixed>>}|null, ahead: list<array<string, mixed>>, absences: array{turn_link: string, goal: string}, empty: array{run: string|null, this_turn: string|null, ahead: string|null}, links: array{decision_url: string, run_url: string}}
     */
    public function raceStripSection(TrainingRun $run, Collection $obligationSlots): array
    {
        $links = [
            'decision_url' => route('runs.races.decision', $run),
            'run_url' => route('runs.cockpit', $run),
        ];
        $absences = [
            'turn_link' => 'The Trainer has not tied this race to a logged turn (KI-17), so the turn is not known rather than zero.',
            // Retired by ADR-0026: the trainee's own goal ladder is fetched per character. The strip marks
            // an obligation on the row it belongs to and says where the whole ladder reads.
            'goal' => 'A race marked Goal here is one this trainee\'s own ladder asks of her; the full ladder,'
                .' including the objectives that name no race on this calendar, reads under Trainee goals in'
                .' the Scenario panel (ADR-0026). A race missing the mark is not a race nobody asked of her.',
        ];

        if (! $run->composesPanel('race_calendar')) {
            return [
                'shown' => false,
                'notice' => 'This scenario composes no race calendar, so there is nothing to orient on here. The scenario\'s own panels say what it does compose.',
                'run' => [],
                'this_turn' => null,
                'ahead' => [],
                'absences' => $absences,
                'empty' => ['run' => null, 'this_turn' => null, 'ahead' => null],
                'links' => $links,
            ];
        }

        $next = $run->hasScenario() ? $run->nextTurnToPlay() : null;

        // The trainee's own obligations, keyed on the calendar row each one names (PRD FR-A-10,
        // ADR-0026). One query for the whole region, not one per row: this strip prints three regions and
        // a hundred races, and an N+1 here is a defect rather than a trade-off. Objectives with no slot
        // (a fan gate, an unread one, a race Global has not shipped) carry no key, because there is no
        // calendar row to mark; they still read in the Trainee goals group. That group runs its own query
        // in the same request and both are indexed on (umamusume_id, career_year, turn) - two bounded
        // reads, not one per row.
        /** @var array<int, string> $obligations */
        $obligations = TraineeGoal::query()
            ->where('umamusume_id', $run->umamusume_id)
            ->where('goal_type', '!=', TraineeGoalType::FinalRound->value)
            ->whereNotNull('race_catalog_slot_id')
            ->with('raceCatalogSlot')
            ->get()
            ->mapWithKeys(fn (TraineeGoal $goal): array => [(int) $goal->race_catalog_slot_id => $goal->obligationLine()])
            ->all();

        // The run's own history, oldest logged turn first and unlinked rows last. An array key sorts
        // element-wise, which is how the tie-break on the entry id rides along with the turn. The sort
        // reads the foreign key rather than the relation: the column is `nullable()->nullOnDelete()`
        // (a deleted turn must not delete its race), and testing it is the same answer the relation
        // would give without depending on how the analyzer models a `belongsTo`.
        /** @var list<array<string, mixed>> $recorded */
        $recorded = $run->raceEntries
            ->sortBy(fn (RaceEntry $entry): array => [$entry->turn_entry_id === null ? PHP_INT_MAX : $entry->turnEntry->turn, $entry->id])
            ->map(fn (RaceEntry $entry): array => $this->stripRow($entry, $obligations))
            ->values()
            ->all();

        /** @var Collection<int, RaceCatalogSlot> $offered */
        $offered = $next === null
            ? collect()
            : RaceCatalogSlot::query()
                ->forScenario($run->scenarioKey())
                ->onTurn($next['year'], $next['turn'])
                ->orderBy('sort_order')
                ->get();

        $slotIds = $offered->pluck('id');

        // Null when there is nothing to show, which covers both a run with no turn being decided and
        // a turn the calendar carries no race on. The region's own sentence says which (`empty`).
        $thisTurn = ($next === null || $offered->isEmpty()) ? null : [
            'turn' => $next['turn'],
            'year_label' => RaceCatalogSlot::YEARS[$next['year']] ?? (string) $next['year'],
            'offered' => $offered->count(),
            'recorded' => $run->raceEntries
                ->filter(fn (RaceEntry $entry): bool => $entry->race_catalog_slot_id !== null
                    && $slotIds->contains($entry->race_catalog_slot_id))
                ->map(fn (RaceEntry $entry): array => $this->stripRow($entry, $obligations))
                ->values()
                ->all(),
        ];

        $enteredIds = $run->raceEntries->pluck('race_catalog_slot_id')->filter();

        /** @var list<array<string, mixed>> $ahead */
        $ahead = $next === null ? [] : $obligationSlots
            ->reject(fn (RaceCatalogSlot $slot): bool => $slot->isAtOrBeforeTurn($next)
                || $enteredIds->contains($slot->id))
            ->map(fn (RaceCatalogSlot $slot): array => [
                'id' => $slot->id,
                'title' => $slot->title,
                'tier' => $slot->tier,
                'year_label' => $slot->yearLabel(),
                'turn' => $slot->turnNumber(),
                'goal' => $obligations[$slot->id] ?? null,
            ])
            ->values()
            ->all();

        return [
            'shown' => true,
            'notice' => null,
            'run' => $recorded,
            'this_turn' => $thisTurn,
            'ahead' => $ahead,
            'absences' => $absences,
            'empty' => [
                'run' => $recorded === []
                    ? 'No race is recorded for this run yet, so there is no history to read here. Record the turn from the action grid, or open Race Decision to enter one at the turn you are in.'
                    : null,
                'this_turn' => match (true) {
                    $next === null => 'This run has no turn being decided, so there is no turn to read a race against. The Race planner draws the whole calendar.',
                    $offered->isEmpty() => 'No race on this scenario\'s calendar falls at turn '.$next['turn'].'. Open Race Decision to see the turns that do carry one.',
                    default => null,
                },
                'ahead' => match (true) {
                    $next === null => 'Nothing can be placed ahead of a run with no turn being decided.',
                    $ahead === [] => 'No mandatory race is still to come on the calendar this run can read. Obligations already entered are listed under Run, and Race Decision keeps the full obligation list.',
                    default => null,
                },
            ],
            'links' => $links,
        ];
    }

    /**
     * One recorded race, as the strip reads it: the store's own words and figures, nothing derived.
     *
     * `id` is the calendar slot the entry points at, or null for a race the Trainer typed in, which
     * has no catalogue row by definition. `title` falls back exactly as the run record screen's race
     * panel does, so one vocabulary owns the case where neither slot is there.
     *
     * @param  array<int, string>  $obligations  calendar slot id => the trainee's own objective line
     * @return array<string, mixed>
     */
    public function stripRow(RaceEntry $entry, array $obligations): array
    {
        return [
            'id' => $entry->race_catalog_slot_id,
            'entry_id' => $entry->id,
            'title' => $entry->raceCatalogSlot->title ?? $entry->scenarioSlot->title ?? 'a race with no calendar row',
            'tier' => $entry->tierKey(),
            'status' => $entry->status->value,
            'status_label' => $entry->status->label(),
            'placement' => $entry->placement === null ? null : $entry->placementOrdinal(),
            'turn' => $entry->turnEntry?->turn,
            // The obligation prints beside the finish, not instead of it: a race the run has already run
            // keeps its status and still says what the ladder asked (plan §5.2).
            'goal' => $entry->race_catalog_slot_id === null ? null : ($obligations[$entry->race_catalog_slot_id] ?? null),
        ];
    }
}
