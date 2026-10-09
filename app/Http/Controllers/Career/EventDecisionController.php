<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Enums\TurnEventType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTurnEventRequest;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\TurnEvent;
use App\Services\ScenarioCaps;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The Event Decision, `SCR-CAR-014` (SCREEN-012, plan §8 D11). Records an event choice and its
 * observed outcome over the existing TurnEvent mechanism.
 *
 * **No event catalog exists**, so "known outcomes per choice" are derived from the run's own
 * recorded TurnEvents: choices seen before with their recorded notes, grouped by (source type,
 * event name). A choice whose row carries no recorded outcome is flagged incomplete and the
 * screen prints the manual-choice warning. Nothing is invented from the corpus.
 *
 * **The advisor refuses.** `TrainerAdvisor` ranks training actions only and holds no event
 * advice, so the advisor section renders "No recommendation available" with that reason, and
 * the Trainer's override is the default path, never an escape hatch. No option is preselected.
 *
 * **Current career state sits alongside** the event, in the same shape the Cockpit prints it:
 * five stats against their targets and caps from `ScenarioCaps::forRun()`, plus Energy, Mood,
 * Fans and Skill Points.
 */
class EventDecisionController extends Controller
{
    /** @var list<TurnEventType> */
    private const CHOICE_SOURCES = [
        TurnEventType::Character,
        TurnEventType::SupportCard,
        TurnEventType::Group,
        TurnEventType::Scenario,
    ];

    public function show(TrainingRun $run): Response
    {
        $run->load(['umamusume', 'turnEntries', 'turnEvents']);

        $latest = $run->turnEntries->sortByDesc('turn')->first();

        return Inertia::render('Career/EventDecision', [
            'run' => $this->runSection($run),
            'state' => $this->stateSection($run, $latest),
            'events' => $this->eventRows($run),
            'known' => $this->knownOutcomes($run),
            'advisor' => $this->advisorSection(),
            'write' => $this->writeSection($run),
            'empty' => $this->emptyState($run),
        ]);
    }

    public function store(TrainingRun $run, StoreTurnEventRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        TurnEvent::create([
            'training_run_id' => $run->id,
            'turn' => $validated['turn'],
            'event_type' => TurnEventType::from($validated['event_type']),
            'source_name' => $validated['source_name'],
            'choice_index' => $validated['choice_index'] ?? null,
            'choice_label' => $validated['choice_label'],
            'support_card_name' => $validated['support_card_name'] ?? null,
            'bond_delta' => $validated['bond_delta'] ?? null,
            'origin_note' => $validated['origin_note'] ?? null,
        ]);

        return redirect()->route('runs.events.decision', $run)->with('status', 'Event choice recorded.');
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
     * The five stats and the four meta values, the same shape `CockpitController` prints
     * (D8): entered current, entered target, and the scenario ceiling from the one owner.
     *
     * @return array{stats: list<array{key: string, label: string, current: int|null, target: int|null, cap: int}>, meta: list<array{key: string, label: string, value: int|string|null}>}
     */
    private function stateSection(TrainingRun $run, ?TurnEntry $latest): array
    {
        /** @var list<string> $order */
        $order = config('scenarios.stat_order');
        $caps = ScenarioCaps::forRun($run);
        $target = $run->buildTarget();

        $stats = [];

        foreach ($order as $stat) {
            $stats[] = [
                'key' => $stat,
                'label' => $stat,
                'current' => $this->currentStat($latest, $stat),
                'target' => $target?->targets[$stat] ?? null,
                'cap' => $caps[$stat],
            ];
        }

        $values = [
            'energy' => $latest?->energy,
            'mood' => $latest?->mood?->value,
            'fans' => $latest?->fans,
            'skill_points' => $latest?->sp,
        ];

        $meta = [];

        foreach ([['key' => 'energy', 'label' => 'Energy'], ['key' => 'mood', 'label' => 'Mood'], ['key' => 'fans', 'label' => 'Fans'], ['key' => 'skill_points', 'label' => 'Skill Points']] as $row) {
            $meta[] = [...$row, 'value' => $values[$row['key']]];
        }

        return ['stats' => $stats, 'meta' => $meta];
    }

    private function currentStat(?TurnEntry $latest, string $stat): ?int
    {
        if ($latest === null) {
            return null;
        }

        return match ($stat) {
            'Speed' => $latest->speed,
            'Stamina' => $latest->stamina,
            'Power' => $latest->power,
            'Guts' => $latest->guts,
            'Wit' => $latest->wit,
            default => null,
        };
    }

    /**
     * The recorded choice events, newest first, with the recorded outcome and its flag.
     * Failure and Inheritance rows are excluded: those screens own them.
     *
     * @return list<array{id: int, turn: int, event_type: string, source_label: string, source_name: string, choice_index: int|null, choice_label: string|null, support_card_name: string|null, bond_delta: int|null, origin_note: string|null, outcome_recorded: bool}>
     */
    private function eventRows(TrainingRun $run): array
    {
        return $run->turnEvents
            ->filter(fn (TurnEvent $event): bool => in_array($event->event_type, self::CHOICE_SOURCES, true))
            ->sortByDesc('turn')
            ->map(fn (TurnEvent $event): array => [
                'id' => $event->id,
                'turn' => $event->turn,
                'event_type' => $event->event_type->value,
                'source_label' => $this->sourceLabel($event->event_type),
                'source_name' => $event->source_name,
                'choice_index' => $event->choice_index,
                'choice_label' => $event->choice_label,
                'support_card_name' => $event->support_card_name,
                'bond_delta' => $event->bond_delta,
                'origin_note' => $event->origin_note,
                'outcome_recorded' => $this->outcomeRecorded($event),
            ])
            ->values()
            ->all();
    }

    /**
     * The known outcomes, grouped by (event type, event name), first occurrence oldest first.
     * Each choice carries its recorded outcome text or a flag that none was recorded.
     *
     * @return list<array{event_type: string, source_label: string, source_name: string, choices: list<array{label: string, outcome: string|null, outcome_recorded: bool}>}>
     */
    private function knownOutcomes(TrainingRun $run): array
    {
        $groups = [];

        foreach ($run->turnEvents->sortBy('turn') as $event) {
            if (! in_array($event->event_type, self::CHOICE_SOURCES, true)) {
                continue;
            }

            $key = $event->event_type->value.'|'.$event->source_name;

            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'event_type' => $event->event_type->value,
                    'source_label' => $this->sourceLabel($event->event_type),
                    'source_name' => $event->source_name,
                    'choices' => [],
                ];
            }

            $label = $event->choice_label ?? 'Unnamed choice';

            if (isset($groups[$key]['choices'][$label])) {
                continue;
            }

            $groups[$key]['choices'][$label] = [
                'label' => $label,
                'outcome' => $event->origin_note !== null && $event->origin_note !== '' ? $event->origin_note : null,
                'outcome_recorded' => $this->outcomeRecorded($event),
            ];
        }

        return array_map(
            static fn (array $group): array => [...$group, 'choices' => array_values($group['choices'])],
            array_values($groups),
        );
    }

    private function outcomeRecorded(TurnEvent $event): bool
    {
        return ($event->origin_note !== null && $event->origin_note !== '')
            || (is_array($event->deltas) && $event->deltas !== []);
    }

    private function sourceLabel(TurnEventType $type): string
    {
        return match ($type) {
            TurnEventType::SupportCard => 'Support Card',
            TurnEventType::Character => 'Character',
            TurnEventType::Group => 'Group',
            TurnEventType::Scenario => 'Scenario',
            default => $type->value,
        };
    }

    /**
     * The refusal, printed verbatim with the reason (design-2.0 §36). Nothing is recommended
     * because the engine holds no event advice; the Trainer chooses and records.
     *
     * @return array{recommendation: null, band: null, reasons: list<string>, alternative: null, risk: null}
     */
    private function advisorSection(): array
    {
        return [
            'recommendation' => null,
            'band' => null,
            'reasons' => ['TrainerAdvisor holds no event advice; the choice is yours to make.'],
            'alternative' => null,
            'risk' => null,
        ];
    }

    /**
     * @return array{action: string, turns: list<array{id: int, turn: int}>, sources: list<array{value: string, label: string}>}
     */
    private function writeSection(TrainingRun $run): array
    {
        return [
            'action' => route('runs.events.store', $run),
            'turns' => $run->turnEntries
                ->sortBy('turn')
                ->map(static fn (TurnEntry $turn): array => ['id' => $turn->id, 'turn' => $turn->turn])
                ->values()
                ->all(),
            'sources' => [
                ['value' => 'Character', 'label' => 'Character'],
                ['value' => 'SupportCard', 'label' => 'Support Card'],
                ['value' => 'Group', 'label' => 'Group'],
                ['value' => 'Scenario', 'label' => 'Scenario'],
            ],
        ];
    }

    private function emptyState(TrainingRun $run): ?string
    {
        $hasChoiceEvent = $run->turnEvents->contains(
            fn (TurnEvent $event): bool => in_array($event->event_type, self::CHOICE_SOURCES, true)
        );

        return $hasChoiceEvent
            ? null
            : 'No event choices recorded yet for this run. Use the record form below to store what fired and what you chose; each entry becomes a known outcome next time the same event appears.';
    }
}
