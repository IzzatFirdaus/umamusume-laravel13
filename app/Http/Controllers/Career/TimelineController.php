<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Domain\Career\CareerPosition;
use App\Enums\CareerPhase;
use App\Enums\TurnEventType;
use App\Http\Controllers\Controller;
use App\Models\RaceEntry;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\TurnEvent;
use App\Services\CareerCalendar;
use App\Services\Scenario\FinaleReader;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The Career Timeline, `SCR-CAR-017` (SCREEN-018, plan §8 D14). A read screen that flattens the
 * run's `turn_entries`, race log entries tied to a turn, and event log entries into one ordered
 * list the page renders as a vertical rail.
 *
 * **Stored facts and entered values only.** No derived score, no readiness band, no advisor
 * confidence: the planner is rerun at each decision screen and the timeline is the thin audit
 * the Trainer can read afterwards. ADR-0003 §"Store the end-of-turn total, not the delta" sets the
 * shape: each row's END OF TURN block is the row's own absolute values, and BEFORE is the previous
 * logged turn (null on turn 1). There is no separate EXPECTED block, because the schema stores the
 * actual; the audit's prose is "Recorded as entered", never a fabricated projection.
 *
 * **Corrections are a flag and, where the history holds a pair, a disclosure (ADR-0027 D-1b, D-1c).**
 * Two shapes exist and both must read as one turn, never as two gameplay turns:
 *
 * - An in-place edit (`runs.turns.update` today) mutates `updated_at` and leaves no prior values, so
 *   the row carries `corrected: true` and a deterministic `correction_id` ("turn-N").
 * - An appended correction writes a second row at the same turn pointed at by `corrected_from` /
 *   `superseded_by` (D-1b's partial unique index permits it). The rail must walk the *live* rows —
 *   the ones nothing supersedes — or a corrected turn prints twice and the career reads one turn
 *   longer than it was. Each superseded row travels beside its live row in `revisions`, so the prior
 *   reading is disclosed on the row it belongs to rather than as a turn of its own.
 *
 * **No `×` glyph comes from missed deadlines today.** The matrix's deadlines (Phase E) are not
 * in this slice's data layer, so the only rows with `×` are `Failure` events the Trainer
 * recorded — which is the discrete state the schema holds. Future E slices may add missed
 * `scheduled` rows without changing this contract.
 */
class TimelineController extends Controller
{
    public function show(TrainingRun $run): Response
    {
        // Two collections the controller maps over, with the per-row BEFORE read off the previous
        // row's stat block in the same map so the audit never re-queries the run.
        //
        // `turnEntries` is loaded whole on purpose: the rail walks the live rows (ADR-0027), but a
        // correction's superseded row is still needed to disclose what it replaced. One load serves
        // both, partitioned in `buildEntries()`, so the revision list costs no second query.
        $run->load([
            'umamusume',
            'turnEntries',
            'raceEntries.raceCatalogSlot',
            'raceEntries.scenarioSlot',
            'raceEntries.turnEntry',
            'turnEvents',
        ]);

        $entries = $this->buildEntries($run);
        $origin = $this->originSection($run);

        return Inertia::render('Career/Timeline', [
            'run' => $this->runSection($run),
            'origin' => $origin,
            'entries' => $entries,
            'filters' => $this->filterSection($entries),
            'empty' => $entries === [] ? $this->emptyMessage($run, $origin) : null,
            // DM-1: the grid is exactly `CareerCalendar::TURNS_PER_CAREER` turns and the finale block
            // sits outside it. Both travel so the rail can group by phase and print the finale without
            // numbering a row 73; the reader is `FinaleReader`, the same one the Cockpit and the Result
            // read, so the three screens cannot disagree about whether the block arrived.
            'total_turns' => CareerCalendar::TURNS_PER_CAREER,
            'finale' => FinaleReader::forRun($run),
        ]);
    }

    /**
     * Where the rail opens: the run's own career position, never a synthesized turn 1.
     *
     * A snapshot's rail starts at the position it was imported at, read from the run's stored position.
     * A new-career run starts at its first logged turn, which is the head of its own rail; the run's
     * `careerPosition()` accessor answers with the *latest* turn, which is the wrong end for an origin,
     * so the first turn is read directly. Rows are never renumbered: this is the head of the rail, not
     * a row in it.
     *
     * @return array{year_label: string, month_label: string, turn: int, imported: bool}|null
     */
    private function originSection(TrainingRun $run): ?array
    {
        $position = $run->hasImportedPosition()
            ? $run->careerPosition()
            : $this->firstLoggedPosition($run);

        if ($position === null) {
            return null;
        }

        return [
            'year_label' => $position->year->label(),
            'month_label' => ($position->phase === CareerPhase::Early ? 'Early ' : 'Late ')
                .CareerCalendar::monthLabel($position->month),
            'turn' => $position->turnIndex,
            'imported' => $run->hasImportedPosition(),
        ];
    }

    private function firstLoggedPosition(TrainingRun $run): ?CareerPosition
    {
        // The head of the rail is the first *live* turn (ADR-0027). A run whose turn 1 was corrected
        // by append still opens at turn 1 either way, because a correction keeps its turn number; the
        // live read is used so this answer cannot drift from the rows the rail prints.
        $first = $run->turnEntries
            ->whereNull('superseded_by')
            ->sortBy('turn')
            ->first();

        if ($first === null) {
            return null;
        }

        $turn = (int) $first->turn;

        if ($turn < 1 || $turn > CareerCalendar::TURNS_PER_CAREER) {
            return null;
        }

        return CareerPosition::fromTurnIndex($turn);
    }

    /**
     * The empty rail's own sentence. A snapshot that has logged nothing opens at the position it was
     * imported at, and says so rather than claiming there is nothing to read.
     *
     * @param  array{year_label: string, month_label: string, turn: int, imported: bool}|null  $origin
     */
    private function emptyMessage(TrainingRun $run, ?array $origin): string
    {
        if ($origin !== null && $run->hasImportedPosition()) {
            return "This run was imported at {$origin['year_label']}, {$origin['month_label']}, turn "
                ."{$origin['turn']}. No turns have been logged since, so the rail opens there.";
        }

        return 'No turns have been recorded for this run, so there is no timeline to read yet. '
            .'The first turn you record from the action grid becomes the first row here.';
    }

    /**
     * @return array{id: int, trainee: string, trainee_ja: string|null, scenario_label: string, status_label: string, run_url: string, cockpit_url: string}
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
            // R7-32 (review finding 2): the undo label reads `latestLoggedTurn`, which falls back to
            // this position when a run has logged nothing. A snapshot whose core stats were Unknown
            // writes no TurnEntry, so without this the Timeline said `Undo turn 1` on a run the Races
            // screen and TrainingDetail both called turn 67. One run, one answer.
            'career_position' => $run->careerPosition()?->toArray(),
            'run_url' => route('runs.cockpit', $run),
            'cockpit_url' => route('runs.cockpit', $run),
        ];
    }

    /**
     * The flat list the page renders: one row per live TurnEntry, plus a row per RaceEntry and per
     * TurnEvent that point at the same turn. Oldest turn first; within a turn, the turn row
     * leads and its events follow in storage order.
     *
     * Walks the live rows only (ADR-0027 D-1b/D-1c). `$run->turnEntries` is the append-only history and
     * still holds every superseded row; folding those into the rail would print one corrected turn as
     * two gameplay turns and stretch a 72-turn career past its own grid. Superseded rows are collected
     * per turn into `revisions` instead, so the prior reading stays reachable from the row that replaced
     * it.
     *
     * @return list<array<string, mixed>>
     */
    private function buildEntries(TrainingRun $run): array
    {
        $history = $run->turnEntries->sortBy('turn')->values();
        $turns = $history->whereNull('superseded_by')->values();

        // Superseded rows grouped by the turn they held, oldest first, so a turn corrected twice
        // discloses both prior readings in the order they were recorded.
        /** @var array<int, list<TurnEntry>> $revisionsByTurn */
        $revisionsByTurn = [];

        foreach ($history->whereNotNull('superseded_by') as $superseded) {
            $revisionsByTurn[(int) $superseded->turn][] = $superseded;
        }

        /** @var array<int, TurnEntry|null> $byTurn */
        $byTurn = [];

        foreach ($turns as $index => $turn) {
            $byTurn[$turn->turn] = $index === 0 ? null : $turns[$index - 1];
        }

        $entries = [];

        foreach ($turns as $turn) {
            $previous = $byTurn[$turn->turn] ?? null;
            $revisions = $revisionsByTurn[(int) $turn->turn] ?? [];
            $entries[] = $this->turnRow($run, $turn, $previous, $revisions);

            // A race is linked by `turn_entry_id`, and a correction appends a *new* row, so a race
            // recorded against the row a correction replaced points at an id the live row does not
            // hold. Collected by turn, over the live row and the rows it supersedes, or the race
            // quietly drops off the rail when its turn is corrected.
            $rowIds = array_merge(
                [$turn->id],
                array_map(static fn (TurnEntry $revision): int => $revision->id, $revisions),
            );

            foreach ($run->raceEntries->whereIn('turn_entry_id', $rowIds) as $race) {
                $entries[] = $this->raceRow($run, $turn->turn, $race);
            }

            foreach ($run->turnEvents->where('turn', $turn->turn) as $event) {
                $entries[] = $this->eventRow($run, $turn, $event);
            }
        }

        // R7-83: a race the Trainer recorded without tying it to a turn still belongs to the auditable
        // record. The loop above walks turns and pulls each turn's races, so a race with no
        // `turn_entry_id` could never appear — the Races screen's manual form allows exactly that link
        // to be left null. These land after the last turn, at the run's own latest position, rather than
        // claiming a turn the run never held.
        //
        // The latest *live* turn, not the latest row in the history: a superseded row is not where the
        // run stands (ADR-0027).
        $latestTurn = $turns->isEmpty() ? 1 : (int) $turns->last()->turn;

        foreach ($run->raceEntries->whereNull('turn_entry_id') as $race) {
            $entries[] = $this->raceRow($run, $latestTurn, $race);
        }

        // The HFW's Position column: the calendar's own label for the row's turn, read back through
        // `CareerPosition` so the page never re-derives year/phase arithmetic. A row outside the
        // 72-turn grid is the finale block.
        foreach ($entries as $index => $entry) {
            $turn = (int) $entry['turn'];
            $entries[$index]['position'] = $this->positionLabel($turn);
        }

        return $entries;
    }

    /**
     * The row's calendar position as "Junior · Late Jun", or the finale block's own name.
     */
    private function positionLabel(int $turn): string
    {
        if ($turn < 1 || $turn > CareerCalendar::TURNS_PER_CAREER) {
            return 'After the grid';
        }

        $position = CareerPosition::fromTurnIndex($turn);
        $year = str_replace(' Year', '', $position->year->label());

        return $year.' · '.$position->phase->value.' '.substr(CareerCalendar::monthLabel($position->month), 0, 3);
    }

    /**
     * The turn's own row: BEFORE = previous-turn stats (or null), ACTUAL = the stored end-of-turn
     * values. One block, not an EXPECTED/ACTUAL pair: `ADR-0003` stores the absolute end-of-turn reading,
     * so the two were always the same numbers printed twice.
     *
     * `revisions` is the ADR-0027 D-1c disclosure: the rows this turn's live row supersedes, if the
     * correction was appended rather than written in place. Empty in the common case, which is the
     * honest answer — an in-place edit keeps no prior values, and inventing a "before" for it would
     * print a reading the tool never held.
     *
     * @param  list<TurnEntry>  $revisions
     * @return array<string, mixed>
     */
    private function turnRow(TrainingRun $run, TurnEntry $turn, ?TurnEntry $previous, array $revisions = []): array
    {
        $corrected = $revisions !== [] || (
            $turn->updated_at !== null
            && $turn->created_at !== null
            && $turn->updated_at->gt($turn->created_at)
        );

        return [
            'key' => 'turn-'.$turn->id,
            'kind' => 'turn',
            'kind_label' => 'Turn',
            'glyph' => '●',
            'turn' => $turn->turn,
            'action_label' => 'Turn recorded',
            'before' => $previous === null ? null : $this->stats($previous),
            'actual' => $this->stats($turn),
            // R6-12: this row's own Energy reading, resolved by the model's single owner
            // (`TurnEntry::energyReading()`) rather than re-derived here. The page read a key the
            // server never sent, so every Energy cell printed N/A over a reading the turn carried.
            // The band logic is not recomputed in the controller; the model already resolves
            // `band` / `exact` / `none` and `text` is what a screen prints.
            'energy_reading' => $turn->energyReading(),
            'mood' => $turn->mood?->value,
            'facilities' => $this->facilities($turn),
            'failure_rate' => $turn->failure_rate,
            'preview_gains' => $this->previewGains($turn),
            'result' => 'Recorded as entered',
            'corrected' => $corrected,
            'correction_id' => $corrected ? 'turn-'.$turn->turn : null,
            // ADR-0027 D-1c: what this turn replaced, newest-correction-last. Each revision carries
            // its own stat block so the page can print the prior reading without a second request,
            // and its own id so the disclosure is one row per superseded entry rather than a count.
            'revisions' => array_map(fn (TurnEntry $revision): array => [
                'id' => $revision->id,
                'stats' => $this->stats($revision),
                'energy_reading' => $revision->energyReading(),
                'mood' => $revision->mood?->value,
                'recorded_at' => $revision->created_at?->toIso8601String(),
            ], $revisions),
            // `C6b.2`: the turn-correction door, so the Timeline row reaches the same write the
            // Cockpit's `Career tools` disclosure used to host. The cockpit query link stays for the
            // screens that still read `?edit_turn=`.
            'edit_url' => route('runs.turns.edit', [$run, $turn]),
            'decision_url' => route('runs.cockpit', $run).'?edit_turn='.$turn->id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function raceRow(TrainingRun $run, int $turnNumber, RaceEntry $race): array
    {
        $title = $race->raceCatalogSlot->title ?? $race->scenarioSlot->title ?? 'a race with no calendar row';

        return [
            'key' => 'race-'.$race->id,
            'kind' => 'race',
            'kind_label' => 'Race',
            'glyph' => '●',
            'turn' => $turnNumber,
            'action_label' => $race->tierKey() !== null ? "Race: {$title} ({$race->tierKey()})" : "Race: {$title}",
            'before' => null,
            'actual' => null,
            // R6-12: a race row carries no reading of its own, and borrowing a neighbour's would put a
            // turn's figure on a row that recorded nothing. Null travels so the page prints one named
            // absence rather than a figure from the row above.
            'energy_reading' => null,
            'mood' => null,
            // The separator is the plain hyphen `EventCard.vue` spells the same state with: the em
            // dash is banned from shipped copy (AGENTS.md §5, R-02), and this string reaches the rail
            // verbatim.
            'result' => $race->status->label().($race->placement !== null ? ' - '.$race->placementOrdinal() : ''),
            'corrected' => false,
            'correction_id' => null,
            'decision_url' => route('runs.races.decision', $run),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function eventRow(TrainingRun $run, TurnEntry $turn, TurnEvent $event): array
    {
        $kind = $this->eventKind($event);
        $outcome = $event->origin_note !== null && $event->origin_note !== '' ? $event->origin_note : null;
        $recorded = $outcome !== null || (is_array($event->deltas) && $event->deltas !== []);

        return [
            'key' => 'event-'.$event->id,
            'kind' => $kind,
            'kind_label' => $this->eventLabel($kind),
            'glyph' => $kind === 'failure' ? '×' : '●',
            'turn' => $turn->turn,
            'action_label' => $this->eventActionLabel($event),
            'before' => null,
            'actual' => null,
            // R6-12: an event row carries no reading of its own, and borrowing a neighbour's would put a
            // turn's figure on a row that recorded nothing. Null travels so the page prints one named
            // absence rather than a figure from the row above.
            'energy_reading' => null,
            'mood' => null,
            'result' => $recorded
                ? ($outcome ?? 'Outcome recorded')
                : 'Outcome incomplete - choose manually',
            'corrected' => false,
            'correction_id' => null,
            'decision_url' => $this->eventDecisionUrl($run, $kind),
        ];
    }

    private function eventKind(TurnEvent $event): string
    {
        return match ($event->event_type) {
            TurnEventType::Inheritance => 'inheritance',
            TurnEventType::Failure => 'failure',
            TurnEventType::Character,
            TurnEventType::SupportCard,
            TurnEventType::Group,
            TurnEventType::Scenario => 'event',
        };
    }

    private function eventLabel(string $kind): string
    {
        return match ($kind) {
            'event' => 'Event',
            'inheritance' => 'Inheritance',
            'failure' => 'Failure',
            default => 'Event',
        };
    }

    private function eventActionLabel(TurnEvent $event): string
    {
        $name = $event->source_name;
        $choice = $event->choice_label;

        if ($choice !== null && $choice !== '') {
            return "{$name}: {$choice}";
        }

        return $name;
    }

    private function eventDecisionUrl(TrainingRun $run, string $kind): string
    {
        return match ($kind) {
            'inheritance' => route('runs.inheritance', $run),
            'failure' => route('runs.cockpit', $run),
            default => route('runs.events.decision', $run),
        };
    }

    /**
     * @return array<string, int|null>
     */
    private function stats(TurnEntry $turn): array
    {
        return [
            'speed' => $turn->speed,
            'stamina' => $turn->stamina,
            'power' => $turn->power,
            'guts' => $turn->guts,
            'wit' => $turn->wit,
            'sp' => $turn->sp,
            'energy' => $turn->energy,
            'fans' => $turn->fans,
        ];
    }

    /**
     * The facility levels this turn recorded, labelled for display, or null when it recorded none.
     *
     * Only the levels that were read are returned: a Trainer who checked the Speed ladder and nothing
     * else leaves the other four absent, and a zero would claim a facility at its floor. Null rather
     * than an empty list so the renderer prints one named absence instead of five.
     *
     * @return list<array{label: string, level: int}>|null
     */
    private function facilities(TurnEntry $turn): ?array
    {
        $levels = [
            'Speed' => $turn->facility_speed,
            'Stamina' => $turn->facility_stamina,
            'Power' => $turn->facility_power,
            'Guts' => $turn->facility_guts,
            'Wit' => $turn->facility_wit,
        ];

        $rows = [];

        foreach ($levels as $label => $level) {
            if ($level !== null) {
                $rows[] = ['label' => $label, 'level' => (int) $level];
            }
        }

        return $rows === [] ? null : $rows;
    }

    /**
     * The preview row the client printed for this turn. `TurnEntry::previewGainRows()` owns the labels and
     * the order, because the Cockpit's recorded-action card has to print the same reading the Timeline does
     * and two copies of one label map is how two screens drift apart.
     *
     * @return list<array{label: string, value: int}>|null
     */
    private function previewGains(TurnEntry $turn): ?array
    {
        return $turn->previewGainRows();
    }

    /**
     * Chip list of kinds the controller found, in the order the page renders.
     *
     * @param  list<array<string, mixed>>  $entries
     * @return array{available: list<string>, active: list<string>}
     */
    private function filterSection(array $entries): array
    {
        $available = [];

        foreach ($entries as $entry) {
            $label = $entry['kind_label'];
            if (! in_array($label, $available, true)) {
                $available[] = $label;
            }
        }

        return ['available' => $available, 'active' => []];
    }
}
