<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Enums\TurnEventType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInheritanceEventRequest;
use App\Models\Legacy\LegacySelectionPayload;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\TurnEvent;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The Inheritance Event, `SCR-CAR-015` (SCREEN-013, plan §8 D12). Record-only inheritance tracking.
 *
 * **Two clearly separate regions.** Predicted (sourced probabilities, never a computed total) and
 * Observed (what the Trainer entered, always Confirmed). Each has its own heading and badge
 * (Estimated vs Confirmed) per the antislop-ui rule and the screen-spec correction row.
 *
 * **Predicted section.** Parent and grandparent Sparks from the run's `legacy_selection`. The
 * "expected inheritance" / factor outlook is NOT computed: it renders `N/A` with a `title` citing
 * `ADR-0020` §3. A sourced star-roll odds table (UMAMUSUME_REFERENCE.md §1.5.3) may be shown as
 * Estimated, never summed into a single number.
 *
 * **Observed section.** A form to record the inspiration result the Trainer saw in the game
 * (which Sparks activated, previous inheritance), posted through the existing TurnEvent mechanism
 * with `event_type = Inheritance`. The write reuses no new route or Form Request beyond what the
 * TurnEvent model already accepts.
 *
 * **Milestone timeline.** Three fixed inheritance moments (REFERENCE §1.5.1): career start,
 * Classic Early April, Senior Early April. Glyphs: ● completed, ◉ current, ○ upcoming, × missed.
 * No hard-coded "Classic April" — the labels come from the run's turn/year position.
 *
 * **Nothing computes an outcome.** No Affinity payout, no Spark roll chance, no stat projection,
 * no recommended combination (ADR-0020 §3, PRD §6 non-goal 3).
 */
class InheritanceEventController extends Controller
{
    /**
     * The three inheritance milestones, in order (REFERENCE §1.5.1).
     * Career start = turn 1. Classic Early April = turn 31 (position 7 of year 2).
     * Senior Early April = turn 55 (position 7 of year 3).
     *
     * @var list<array{key: string, label: string, turn: int}>
     */
    private const MILESTONES = [
        ['key' => 'career_start', 'label' => 'Career Start', 'turn' => 1],
        ['key' => 'classic_april', 'label' => 'Classic Early April', 'turn' => 31],
        ['key' => 'senior_april', 'label' => 'Senior Early April', 'turn' => 55],
    ];

    public function show(TrainingRun $run): Response
    {
        $run->load(['umamusume', 'turnEntries', 'turnEvents', 'inheritanceParentA', 'inheritanceParentB']);

        $legacy = $run->legacySelection();
        $observedEvents = $this->observedEvents($run);
        $milestones = $this->milestoneStatus($run);
        $starRollTable = $this->starRollTable();

        $parentNames = [
            $run->inheritanceParentA?->name,
            $run->inheritanceParentB?->name,
        ];

        return Inertia::render('Career/InheritanceEvent', [
            'run' => $this->runSection($run),
            'legacy' => $legacy ? $this->legacySection($legacy, $parentNames) : null,
            'predicted' => $this->predictedSection($legacy, $starRollTable),
            'observed' => $this->observedSection($run, $observedEvents),
            'milestones' => $milestones,
            'write' => $this->writeSection($run),
            'empty' => $this->emptyState($run, $legacy),
        ]);
    }

    public function store(StoreInheritanceEventRequest $request, TrainingRun $run): RedirectResponse
    {
        $validated = $request->validated();

        TurnEvent::create([
            'training_run_id' => $run->id,
            'turn' => $validated['turn'],
            'event_type' => TurnEventType::Inheritance,
            'source_name' => $validated['source_name'],
            'choice_label' => $validated['choice_label'],
            'deltas' => $validated['deltas'],
            'origin_note' => $validated['origin_note'],
        ]);

        return redirect()->route('runs.inheritance', $run)->with('status', 'Inheritance event recorded.');
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
            'run_url' => route('runs.show', $run),
            'cockpit_url' => route('runs.cockpit', $run),
        ];
    }

    /**
     * The legacy selection payload, flattened for the predicted section.
     *
     * @param  array{0: string|null, 1: string|null}  $parentNames
     * @return array{affinity: string|null, parents: list<array{slot: string, label: string, name: string|null, rank: int|null, is_guest: bool, ancestors: list<array{slot: string, name: string|null}>, sparks: list<array{kind: string, kind_label: string, target: string|null, stars: int|null}>, spark_counts: list<array{kind: string, kind_label: string, count: int}>}>}
     */
    private function legacySection(LegacySelectionPayload $legacy, array $parentNames): array
    {
        $sparkKindLabels = LegacySelectionPayload::sparkKindLabels();

        $parents = [];
        foreach ($legacy->legacies as $index => $legacyData) {
            $slot = $index === 0 ? 'parent_a' : 'parent_b';
            $label = $index === 0 ? 'Parent A' : 'Parent B';
            $name = $parentNames[$index] ?? null;

            $sparks = [];
            foreach ($legacyData['sparks'] as $spark) {
                $sparks[] = [
                    'kind' => $spark['kind'],
                    'kind_label' => $sparkKindLabels[$spark['kind']] ?? ucfirst($spark['kind']),
                    'target' => $spark['target'],
                    'stars' => $spark['stars'],
                ];
            }

            $sparkCounts = [];
            foreach (['blue', 'pink', 'green', 'white', 'scenario'] as $kind) {
                $count = count(array_filter($legacyData['sparks'], fn (array $s): bool => $s['kind'] === $kind));
                if ($count > 0) {
                    $sparkCounts[] = [
                        'kind' => $kind,
                        'kind_label' => $sparkKindLabels[$kind] ?? ucfirst($kind),
                        'count' => $count,
                    ];
                }
            }

            $parents[] = [
                'slot' => $slot,
                'label' => $label,
                'name' => $name,
                'rank' => $legacyData['rank'] ?? null,
                'is_guest' => $legacyData['is_guest'],
                'ancestors' => array_map(
                    fn (array $a): array => ['slot' => $a['slot'] ?? '', 'name' => $a['name'] ?? null],
                    $legacyData['ancestors']
                ),
                'sparks' => $sparks,
                'spark_counts' => $sparkCounts,
            ];
        }

        return [
            'affinity' => $legacy->affinity,
            'parents' => $parents,
        ];
    }

    /**
     * The predicted section: parent/grandparent Sparks from legacy_selection, plus the
     * "expected inheritance" which is explicitly NOT computed (ADR-0020 §3).
     *
     * @param  list<array{stat_range: string, one_star: string, two_star: string, three_star: string}>  $starRollTable
     * @return array{predicted_sparks: list<array{kind: string, kind_label: string, total_count: int}>, expected_inheritance: array{label: string, title: string}, star_roll_table: list<array<string, string>>}
     */
    private function predictedSection(?LegacySelectionPayload $legacy, array $starRollTable): array
    {
        $predictedSparks = [];
        $sparkKindLabels = LegacySelectionPayload::sparkKindLabels();

        if ($legacy !== null) {
            $allSparks = [];
            foreach ($legacy->legacies as $legacyData) {
                foreach ($legacyData['sparks'] as $spark) {
                    $allSparks[] = $spark;
                }
            }

            foreach (['blue', 'pink', 'green', 'white', 'scenario'] as $kind) {
                $kindSparks = array_filter($allSparks, fn (array $s): bool => $s['kind'] === $kind);
                if ($kindSparks !== []) {
                    $predictedSparks[] = [
                        'kind' => $kind,
                        'kind_label' => $sparkKindLabels[$kind] ?? ucfirst($kind),
                        'total_count' => count($kindSparks),
                    ];
                }
            }
        }

        return [
            'predicted_sparks' => $predictedSparks,
            'expected_inheritance' => [
                'label' => 'N/A',
                'title' => 'Not computed. Inheritance outcome computation is banned by ADR-0020 §3. This tool records what the Trainer enters; it does not derive an outcome.',
            ],
            'star_roll_table' => $starRollTable,
        ];
    }

    /**
     * The sourced star-roll odds table from UMAMUSUME_REFERENCE.md §1.5.3.
     * Shown as Estimated, never summed.
     *
     * @return list<array{stat_range: string, one_star: string, two_star: string, three_star: string}>
     */
    private function starRollTable(): array
    {
        return [
            ['stat_range' => 'Below 600', 'one_star' => '~90%', 'two_star' => '~10%', 'three_star' => '0%'],
            ['stat_range' => '600–1100', 'one_star' => '~50%', 'two_star' => '~45%', 'three_star' => '~6%'],
            ['stat_range' => 'Above 1100', 'one_star' => '~20%', 'two_star' => '~70%', 'three_star' => '~10%'],
        ];
    }

    /**
     * Observed Inheritance events recorded via TurnEvent.
     *
     * @return list<array{id: int, turn: int, source_name: string, choice_label: string|null, deltas: array<string, mixed>|null, origin_note: string|null}>
     */
    private function observedEvents(TrainingRun $run): array
    {
        return $run->turnEvents
            ->filter(fn (TurnEvent $event): bool => $event->event_type === TurnEventType::Inheritance)
            ->sortBy('turn')
            ->map(fn (TurnEvent $event): array => [
                'id' => $event->id,
                'turn' => $event->turn,
                'source_name' => $event->source_name,
                'choice_label' => $event->choice_label,
                'deltas' => $event->deltas,
                'origin_note' => $event->origin_note,
            ])
            ->values()
            ->all();
    }

    /**
     * The observed section: recorded events plus the write form.
     *
     * @param  list<array{id: int, turn: int, source_name: string, choice_label: string|null, deltas: array<string, mixed>|null, origin_note: string|null}>  $events
     * @return array{events: list<array<string, mixed>>, provenance: string}
     */
    private function observedSection(TrainingRun $run, array $events): array
    {
        return [
            'events' => $events,
            'provenance' => $events !== []
                ? 'Confirmed — entered by the Trainer'
                : 'No inheritance events recorded yet.',
        ];
    }

    /**
     * The three inheritance milestones with their status glyphs.
     *
     * Glyphs: ● completed, ◉ current, ○ upcoming, × missed
     * Status derives from the run's current turn position relative to each milestone's turn.
     *
     * @return list<array{key: string, label: string, glyph: string, status: string, title: string}>
     */
    private function milestoneStatus(TrainingRun $run): array
    {
        $turnCount = $run->turnEntries->count();
        // The turn being decided is the next one to play
        $nextTurn = $run->nextTurnNumber();

        $milestones = [];

        foreach (self::MILESTONES as $milestone) {
            $milestoneTurn = $milestone['turn'];

            $status = 'upcoming';
            $glyph = '○';
            $title = 'Upcoming inheritance event.';

            if ($nextTurn > $milestoneTurn) {
                $status = 'completed';
                $glyph = '●';
                $title = 'Completed inheritance event.';
            } elseif ($nextTurn === $milestoneTurn) {
                $status = 'current';
                $glyph = '◉';
                $title = 'Current inheritance event.';
            }

            $milestones[] = [
                'key' => $milestone['key'],
                'label' => $milestone['label'],
                'glyph' => $glyph,
                'status' => $status,
                'title' => $title,
            ];
        }

        return $milestones;
    }

    /**
     * The write section: the route and minimal fields for recording an observed inheritance event.
     *
     * @return array{action: string, turns: list<array{id: int, turn: int}>, sources: list<string>}
     */
    private function writeSection(TrainingRun $run): array
    {
        $turns = $run->turnEntries
            ->sortBy('turn')
            ->map(fn (TurnEntry $turn): array => ['id' => $turn->id, 'turn' => $turn->turn])
            ->values()
            ->all();

        return [
            'action' => route('runs.inheritance.store', $run),
            'turns' => $turns,
            'sources' => ['Inspiration Event', 'Classic April', 'Senior April', 'Golden Event', 'Other'],
        ];
    }

    /**
     * Empty state when no legacy selection exists for this run.
     */
    private function emptyState(TrainingRun $run, ?LegacySelectionPayload $legacy): ?string
    {
        if ($legacy === null) {
            return 'No Legacy configuration recorded for this run. Enter the parents and their Sparks on the Legacy Select screen (wizard step 4) or the Legacy Lab, then return here to track inheritance events.';
        }

        return null;
    }
}
