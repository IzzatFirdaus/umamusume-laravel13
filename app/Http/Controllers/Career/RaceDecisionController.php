<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Enums\RaceEntryStatus;
use App\Http\Controllers\Controller;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The Race Decision, `SCR-CAR-012` (SCREEN-011, plan §8 D10). Whether and where to race, at the turn
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
 * Excellent/Good/Borderline/Poor bands instead of fake precision; bands are a prediction of the same
 * shape and are equally held, so they are named as absent rather than invented.
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

        $next = $run->nextTurnToPlay();
        $races = $this->raceRows($run, $next);

        return Inertia::render('Career/RaceDecision', [
            'run' => $this->runSection($run),
            'nextTurn' => $this->nextTurnSection($next),
            'races' => $races,
            'deadlines' => $this->deadlineRows($run, $next),
            'readiness' => $this->readiness(),
            'entry' => $this->entrySection($run),
            'empty' => $this->emptyState($run, $next, count($races)),
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
            'run_url' => route('runs.show', $run),
        ];
    }

    /**
     * The turn being decided, in the client's own words, or null when there is none.
     *
     * `nextTurnToPlay()` returns null for a run that has logged nothing and for one past its last
     * turn; both are real states and neither is pointed at a default turn (D-220).
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
            'facts' => $this->facts($slot),
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
     * The brief's field list for one race, present or absent, each absence carrying its reason.
     *
     * Built server-side rather than in the component, so the reason a figure is missing travels with
     * the figure and one place owns the wording (`LegacyController`'s Spark-chance cell made the same
     * call).
     *
     * @return list<array{key: string, label: string, value: string|null, title: string|null}>
     */
    private function facts(RaceCatalogSlot $slot): array
    {
        return [
            $this->fact('grade', 'Grade', $slot->tier, 'This catalogue row carries no tier label.'),
            $this->fact(
                'distance',
                'Distance',
                $slot->distance === null ? null : $slot->distanceLabel(),
                'This catalogue row carries no distance, so the client decides it from the trainee\'s most-run types.',
            ),
            $this->fact(
                'distance_band',
                'Distance band',
                $slot->distance_band,
                'This catalogue row carries no distance band.',
                $slot->distance_band === null ? null : 'Band names are the export\'s own. The corpus disagrees with itself at the 1400 m line (Game8 calls it Sprint, the export calls it Mile), so the band is printed as the export states it and no band is derived from a metre count.',
            ),
            $this->fact('surface', 'Surface', $slot->surface, 'This catalogue row carries no surface.'),
            $this->fact(
                'running_style',
                'Running style',
                null,
                'A running style is the trainee\'s aptitude, not a property of the race, and no column here holds a per-race one. The trainee\'s own style letters are on her profile.',
            ),
            $this->fact(
                'fan_gain',
                'Fan gain',
                null,
                'No column holds a fan payout. This row carries the payout curve id the export publishes'
                    .($slot->fans_gain_curve === null ? ' (none on this row)' : " ({$slot->fans_gain_curve})")
                    .', and no payout table for those curves is stored, so the figure cannot be read off it.',
            ),
            $this->fact('reward', 'Reward', null, 'No column holds a race reward, and no source in this repository states one per race.'),
            $this->fact('skill_points', 'Skill Points', null, 'No column holds a skill-point payout. The Trainer records what the client paid, on the run screen.'),
            $this->fact('scenario_reward', 'Scenario reward', null, 'No column holds a scenario reward, and the scenario panels that would state one are not built.'),
            $this->fact(
                'win_probability',
                'Estimated win probability',
                null,
                'Held: race prediction is blocked on the requirement data `ADR-0016` measures, which is an open question and not a permission. `PRD.md` §6.11 stands unamended, so this tool computes and prints no win figure.',
            ),
        ];
    }

    /**
     * @return array{key: string, label: string, value: string|null, title: string|null}
     */
    private function fact(string $key, string $label, ?string $value, string $absentTitle, ?string $presentTitle = null): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'value' => $value,
            'title' => $value === null ? $absentTitle : $presentTitle,
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
                'reached' => $next !== null
                    && ($slot->year < $next['year'] || ($slot->year === $next['year'] && ($slot->turn ?? 0) <= $next['turn'])),
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
            'action' => route('runs.races.store', $run),
            'turns' => $run->turnEntries
                ->sortBy('turn')
                ->map(static fn (TurnEntry $turn): array => ['id' => $turn->id, 'turn' => $turn->turn])
                ->values()
                ->all(),
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
            return 'This run names no scenario, so it has no race calendar and no races to decide between. Choose a scenario on the run screen and the calendar appears.';
        }

        if ($next === null) {
            return $run->turnEntries->isEmpty()
                ? 'No turn has been logged yet, so there is no turn to decide about. Record the first turn on the run screen and this screen will open on the next one.'
                : 'Every turn of this career has been played, so there is no next race to decide about. The run record screen has the full history.';
        }

        return 'No race on this scenario\'s calendar falls at this turn. The race calendar on the run screen shows which turns carry one.';
    }

    /** The twelve month names, as the client's own Early/Late halves divide them. */
    private const MONTHS = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ];
}
