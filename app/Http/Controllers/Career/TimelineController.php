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
 * shape: each row's AFTER block is the row's own absolute values, and BEFORE is the previous
 * logged turn (null on turn 1). EXPECTED === ACTUAL because the schema stores the actual; the
 * audit's prose is "Recorded as entered", never a fabricated projection.
 *
 * **Corrections are a flag, not a separate row.** `runs.turns.update` mutates `updated_at` but
 * the model has no edit log column, so a corrected row carries `corrected: true` and a deterministic
 * `correction_id` ("turn-N") the page reads as an "Updated" indicator. A separate corrections
 * log is the destination; this slice ships the smallest honest version, acknowledges it in the
 * row, and the limit becomes its own future task.
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
        $first = $run->turnEntries->sortBy('turn')->first();

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
            'run_url' => route('runs.cockpit', $run),
            'cockpit_url' => route('runs.cockpit', $run),
        ];
    }

    /**
     * The flat list the page renders: one row per TurnEntry, plus a row per RaceEntry and per
     * TurnEvent that point at the same turn. Oldest turn first; within a turn, the turn row
     * leads and its events follow in storage order.
     *
     * @return list<array<string, mixed>>
     */
    private function buildEntries(TrainingRun $run): array
    {
        $turns = $run->turnEntries->sortBy('turn')->values();

        /** @var array<int, TurnEntry|null> $byTurn */
        $byTurn = [];

        foreach ($turns as $index => $turn) {
            $byTurn[$turn->turn] = $index === 0 ? null : $turns[$index - 1];
        }

        $entries = [];

        foreach ($turns as $turn) {
            $previous = $byTurn[$turn->turn] ?? null;
            $entries[] = $this->turnRow($run, $turn, $previous);

            foreach ($run->raceEntries->where('turn_entry_id', $turn->id) as $race) {
                $entries[] = $this->raceRow($run, $turn, $race);
            }

            foreach ($run->turnEvents->where('turn', $turn->turn) as $event) {
                $entries[] = $this->eventRow($run, $turn, $event);
            }
        }

        return $entries;
    }

    /**
     * The turn's own row: BEFORE = previous-turn stats (or null), AFTER = stored values,
     * EXPECTED === ACTUAL (ADR-0003 stores absolute end-of-turn values).
     *
     * @return array<string, mixed>
     */
    private function turnRow(TrainingRun $run, TurnEntry $turn, ?TurnEntry $previous): array
    {
        $corrected = $turn->updated_at !== null
            && $turn->created_at !== null
            && $turn->updated_at->gt($turn->created_at);

        return [
            'key' => 'turn-'.$turn->id,
            'kind' => 'turn',
            'kind_label' => 'Turn',
            'glyph' => '●',
            'turn' => $turn->turn,
            'action_label' => 'Turn recorded',
            'before' => $previous === null ? null : $this->stats($previous),
            'after' => $this->stats($turn),
            'expected' => $this->stats($turn),
            'actual' => $this->stats($turn),
            'mood' => $turn->mood?->value,
            'facilities' => $this->facilities($turn),
            'failure_rate' => $turn->failure_rate,
            'preview_gains' => $this->previewGains($turn),
            'result' => 'Recorded as entered',
            'corrected' => $corrected,
            'correction_id' => $corrected ? 'turn-'.$turn->turn : null,
            'decision_url' => route('runs.cockpit', $run).'?edit_turn='.$turn->id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function raceRow(TrainingRun $run, TurnEntry $turn, RaceEntry $race): array
    {
        $title = $race->raceCatalogSlot->title ?? $race->scenarioSlot->title ?? 'a race with no calendar row';

        return [
            'key' => 'race-'.$race->id,
            'kind' => 'race',
            'kind_label' => 'Race',
            'glyph' => '●',
            'turn' => $turn->turn,
            'action_label' => $race->tierKey() !== null ? "Race: {$title} ({$race->tierKey()})" : "Race: {$title}",
            'before' => null,
            'after' => null,
            'expected' => null,
            'actual' => null,
            'mood' => null,
            'result' => $race->status->label().($race->placement !== null ? ' — '.$race->placementOrdinal() : ''),
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
            'after' => null,
            'expected' => null,
            'actual' => null,
            'mood' => null,
            'result' => $recorded
                ? ($outcome ?? 'Outcome recorded')
                : 'Outcome incomplete — choose manually',
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
     * The preview row the client printed for this turn, labelled and ordered the way the entry form
     * asks for it. Null when the Trainer read none of it, which is the ordinary case for a turn logged
     * from the rail. A member stored without a figure is dropped rather than shown as zero: zero is a
     * preview of no change, which is a reading, and an absent one is not.
     *
     * @return list<array{label: string, value: int}>|null
     */
    private function previewGains(TurnEntry $turn): ?array
    {
        $gains = $turn->preview_gains;

        if (! is_array($gains) || $gains === []) {
            return null;
        }

        $labels = [
            'speed' => 'Speed',
            'stamina' => 'Stamina',
            'power' => 'Power',
            'guts' => 'Guts',
            'wit' => 'Wit',
            'sp' => 'Skill Points',
        ];

        $rows = [];

        foreach ($labels as $key => $label) {
            if (isset($gains[$key])) {
                $rows[] = ['label' => $label, 'value' => (int) $gains[$key]];
            }
        }

        return $rows === [] ? null : $rows;
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
