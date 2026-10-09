<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Enums\TurnEventType;
use App\Http\Controllers\Controller;
use App\Models\RaceEntry;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\TurnEvent;
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

        return Inertia::render('Career/Timeline', [
            'run' => $this->runSection($run),
            'entries' => $entries,
            'filters' => $this->filterSection($entries),
            'empty' => $entries === [] ? 'No turns have been recorded for this run, so there is no timeline to read yet. The first turn on the run record screen becomes the first row here.' : null,
        ]);
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
